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
$module = $modulesManager->fetch($moduleName, false);
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
		sort($allTranslationsFiles);

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

				if(isset($moduleLangFileManager->translations[$currentLang][$fileName])){
					$langsStats[$langKey][$fileName]->missingTranslations = count($moduleLangFileManager->translations[$currentLang][$fileName]);
				}
			}
		}

		if(!empty($moduleLangFileManager->translations[$currentLang])){

			foreach ($moduleLangFileManager->translations as $langKey => $translationFiles){
				if($langKey == $currentLang){
					continue;
				}

				foreach ($translationFiles as $translationFileName => $translations){
					$langsStats[$langKey][$translationFileName]->fileExist = true;
					$langsStats[$langKey][$translationFileName]->missingTranslations = 0;

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
		}
		else{
			$logManager->addLog($langs->trans('PleaseSelectASourceLangWithTranslationBeforeStart'));
		}
	}


	if($langsStats){
		print '<div class="div-table-responsive" >';
		print '<table class="noborder ">';
		$i = 0;
		foreach ($langsStats as $langKey => $langFileStats){

			if($i == 0){

				print '<thead>';
				print '<tr class="liste_titre">';
				print '		<th rowspan="2" >';
				print '			<form action="'.$_SERVER['PHP_SELF'].'" type="get" >';
				print '			<input type="hidden" name="module" value="'.dol_escape_htmltag($moduleName).'">';
				print 			$form->selectArray('used-lang', $moduleLangFileManager->langsAvailables, $currentLang);
				print '			<button type="submit" title="'.$langs->trans('ChangeReferenceLanguage').'"><i class="fa fa-repeat"></i></button>';
				print '			</form >';
				print '		</th>';
				foreach ($allTranslationsFiles as $fileName){
					print '		<th class="center col-start-border col-end-border" colspan="2">'.$fileName.'</th>';
				}
				print '</tr>';

				print '<tr class="liste_titre">';
				foreach ($allTranslationsFiles as $fileName){
					print '		<th class="center col-start-border" >'.$langs->trans('MissingTranslations').'</th>';
					print '		<th class="center col-end-border" >'.$langs->trans('AdditionalTranslations').'</th>';
				}
				print '</tr>';
				print '</thead>';
				print '<tbody>';
			}

			print '<tr class="oddeven" >';

			// Lang and flag
			print '		<td>';
			$countryAssociated = isset($moduleLangFileManager->langsAvailables[$langKey])?$moduleLangFileManager->langsAvailables[$langKey]:$langKey;
			$langCodeArr = explode('_', $langKey);
			$countryCode = strtolower(end($langCodeArr));
			$flag = $langKey;
			if (file_exists(DOL_DOCUMENT_ROOT.'/theme/common/flags/'.$countryCode.'.png')) {
				$flag = ' '.img_picto($countryCode, DOL_URL_ROOT.'/theme/common/flags/'.$countryCode.'.png', '', 1, 0, 1);
			}
			print '<span class="classfortooltip" title="'.dol_escape_htmltag($countryAssociated).'" >'.$flag.'</span> ';
			print $countryAssociated;
			print '		</td>';


			// Langs files list
			foreach ($allTranslationsFiles as $fileName){
				if(!$langFileStats[$fileName]->fileExist){
					print '<td colspan="2">'.dolGetBadge($langs->trans('FileNotFound'), '','danger').'</td>';
				}else{
					print '<td class="center col-start-border"  >';
					if(empty($langFileStats[$fileName]->missingTranslations)){
						print '<span class="fa fa-check" style="color: dimgrey"></span>';
					}
					else{
						$params = array(
							'attr' => array(
								'class' => ' classfortooltip',
								'title' => $langs->trans('MissingTranslations')
							)
						);
						print dolGetBadge('-'.$langFileStats[$fileName]->missingTranslations, '', 'danger', '', '', $params);
					}

					print '</td>';

					print '<td class="center col-end-border" >';
					if(empty($langFileStats[$fileName]->additionalsTranslations)){
						print '<span class="fa fa-check" style="color: dimgrey"></span>';
					}
					else {
						$params = array(
							'attr' => array(
								'class' => ' classfortooltip',
								'title' => $langs->trans('AdditionalTranslations')
							)
						);
						print dolGetBadge('+' . $langFileStats[$fileName]->additionalsTranslations, '', 'primary', '', '', $params);
					}
					print '</td>';
				}
			}

			print '</tr>';
			$i++;
		}

		print '</tbody>';
		print '</table>';
		print '</div>';
	}
}

$logManager->output(true,true);

/*
 * End Script View
 */


// Page end
print dol_get_fiche_end();


require_once __DIR__.'/inc/__tools_footer.php';

