<?php require_once __DIR__ . '/inc/__tools_header.php';

require_once __DIR__ . '/../class/modulesManager.class.php';
require_once __DIR__ . '/../class/moduleLangFileManager.class.php';

$devToolScriptName =  'TranslateModules';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('devcommunitytools'.$devToolScriptName));

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$moduleName = GETPOST('module', 'aZ09');	// Used by actions_setmoduleoptions.inc.php

$currentLang = GETPOST('used-lang', 'aZ09');
if(empty($currentLang)){
	$currentLang =  $langs->defaultlang;
}

$error = 0;

$logManager = new devCommunityTools\LogManager();
$modulesManager = new \devCommunityTools\ModulesManager($db);
$module = $modulesManager->fetch($moduleName);
$logManager->addError($modulesManager->errors);

/*
 * Actions
 */

if($action = ''){

}


/*
 * View
 */



$help_url = '';
$page_name = $langs->trans("DevCommunityTools").' - '.$langs->trans($devToolScriptName);
$arrayofjs = array(
	'devcommunitytools/js/devtools.js'
);

$arrayofcss = array(
	'devcommunitytools/css/devtools.css'
);

llxHeader('', $page_name, $help_url, '', 0, 0, $arrayofjs, $arrayofcss);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : dol_buildpath('/devcommunitytools/admin/tools.php', 1)).'">'.$langs->trans("BackToToolsList").'</a>';

print load_fiche_titre($page_name, $linkback, 'title_setup');

/*
 * Start Script View
 */

$logManager->output(true,true);

if($module) {
	print '<h3>'.$module->getName().'</h3>';
	print '<p>'.$module->getDesc().'</p>';

	$moduleLangFileManager = new \devCommunityTools\ModuleLangFileManager($module, $langs);
	$moduleLangFileManager->loadTranslations();
	if(!isset($moduleLangFileManager->langsAvailables[$currentLang])){
		$currentLang = $langs->defaultlang;
		if(!isset($moduleLangFileManager->langsAvailables[$langs->defaultlang])){
			$currentLang = array_key_first($moduleLangFileManager->langsAvailables);
		}
	}

	print  '<p>'.$langs->trans('CurrentLangIsX', $currentLang).'</p>';

	$langsStats = array();
	if(!empty($moduleLangFileManager->translations)){

		$allTranslationsFiles = array();
		// Make a list of files available
		foreach ($moduleLangFileManager->translations as $langKey => $translationFiles) {
			$allTranslationsFiles+=array_keys($translationFiles);
		}

		$allTranslationsFiles = array_unique($allTranslationsFiles);

		// prepare stats for langs files
		foreach ($moduleLangFileManager->langsAvailables as $langKey => $langLabel ){

			if($langKey == $currentLang){
				continue;
			}

			$langsStats[$langKey] = array();
			foreach ($allTranslationsFiles as $fileName){
				$langsStats[$langKey][$fileName] = new stdClass();
				$langsStats[$langKey][$fileName]->fileExist = false;
				$langsStats[$langKey][$fileName]->missingTranslations = 0;
				$langsStats[$langKey][$fileName]->additionalsTranslations = 0;
			}
		}

		if(!empty($moduleLangFileManager->translations[$currentLang])){

			foreach ($moduleLangFileManager->translations as $langKey => $translationFiles){
				if($langKey == $currentLang){
					continue;
				}

				foreach ($translationFiles as $translationFileName => $translations){
					$langsStats[$langKey][$translationFileName]->fileExist = true;

					// search missing translation based on comparaison language
					foreach ($moduleLangFileManager->translations[$currentLang][$translationFileName] as $translationKey => $translationValue){
						if(!isset($moduleLangFileManager->translations[$langKey][$translationFileName][$translationKey])){
							$langsStats[$langKey][$translationFileName]->missingTranslations++;
						}
					}

					// compare  lang file with lang used for comparaison
					foreach ($translations as $translationKey => $translationValue){
						if(!isset($moduleLangFileManager->translations[$currentLang][$translationFileName][$translationKey])){
							$langsStats[$langKey][$translationFileName]->additionalsTranslations++;
						}
					}
				}
			}

			var_dump($langsStats);
		}
		else{
			$logManager->addLog($langs->trans('PleaseSelectASourceLangWithTranslationBeforeStart'));
		}

	}
}

$logManager->output(true,true);

/*
 * End Script View
 */


// Page end
print dol_get_fiche_end();


require_once __DIR__.'/inc/__tools_footer.php';

