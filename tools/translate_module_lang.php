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


if($module) {

	$moduleLangFileManager = new \devCommunityTools\ModuleLangFileManager($module, $langs);
	$moduleLangFileManager->loadTranslations();
	if(!isset($moduleLangFileManager->langsAvailables[$currentLang])){
		$currentLang = $langs->defaultlang;
		if(!isset($moduleLangFileManager->langsAvailables[$langs->defaultlang])){
			$currentLang = array_key_first($moduleLangFileManager->langsAvailables);
		}
	}

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
}


/*
 * Actions
 */

if($action == ''){

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

	if($langsStats){
		__display_langs_stats();
	}

	if($action == 'add-missing-translations'){
		__display_add_missing_tranlations_form();
	}
}

$logManager->output(true,true);

/*
 * End Script View
 */


// Page end
print dol_get_fiche_end();


require_once __DIR__.'/inc/__tools_footer.php';

function __display_add_missing_tranlations_form(){
	global $currentLang, $form, $module, $moduleLangFileManager, $langs, $moduleName,$langsStats,$allTranslationsFiles;

	$targetLang = GETPOST('target-lang', 'aZ09');
	$fileName = GETPOST('file-name', 'aZ09');


	if(empty($moduleLangFileManager->translations[$targetLang][$fileName]) || !isset($moduleLangFileManager->translations[$currentLang][$fileName]) ){
		setEventMessage('LangFileNotFound', 'errors');
		return false;
	}

	print '<fieldset>';
	print '<legend>'.$langs->transnoentities('AddMissingTranslationOfXFileFromYLangToZLang', '<b>'.$fileName.'.lang</b>', '<b>'.$currentLang.'</b>', '<b>'.$targetLang.'</b>').'</legend>';

	$postedLang = getPost('trad', 'array');

	print '<form action="'.$_SERVER['PHP_SELF'].'" type="post" >';
	$numInput = 0;
	print '	<input type="hidden" name="module" value="'.dol_escape_htmltag($moduleName).'">';$numInput++;
	print '	<input type="hidden" name="target-lang" value="'.dol_escape_htmltag($targetLang).'">';$numInput++;
	print '	<input type="hidden" name="action" value="add-missing-translations">';$numInput++;
	print '	<input type="hidden" name="file-name" value="'.dol_escape_htmltag($fileName).'">';$numInput++;
	print '	<input type="hidden" name="used-lang" value="'.dol_escape_htmltag($currentLang).'">';$numInput++;

	$numInput++; // for last input submit

	$newTrads = array();
	foreach($moduleLangFileManager->translations[$currentLang][$fileName] as $tradKey => $trad){
		if(isset($moduleLangFileManager->translations[$targetLang][$fileName][$tradKey]) && empty($postedLang[$tradKey])){
			// do not display input trad if already present in target but not if is in current posted values
			continue;
		}

		$newTrad = !empty($postedLang[$tradKey])?$postedLang[$tradKey]:'';
		$newTrads[$tradKey] = $newTrad;
	}

	$maxVars = ini_get('max_input_vars');
	$inputCalc = $numInput + count($newTrads)*2;
	if($maxVars < $inputCalc){
		print '<div class="error" >'.$langs->trans('WarningMaxInputVarsIsBelowNumberOfInputs', $maxVars, $inputCalc).'</div>';
	}


	$sourceFlag = \devCommunityTools\ModuleLangFileManager::getFlag($currentLang);
	$targetFlag = \devCommunityTools\ModuleLangFileManager::getFlag($targetLang);

	print '<table class="noborder " >';
	foreach($newTrads as $tradKey => $newTrad){


		print '<tr class="oddeven">';
		print '	<td>';
		print '	<td>';
		print '		<label class="dev-tool-lang-label" for="source_trad_'.$tradKey.'" >'.$sourceFlag.' '.$tradKey.'</label>';
		print '		<textarea class="dev-tool-lang-textarea" autoresize="1" disabled id="source_trad_'.$tradKey.'" name="source_trad['.$tradKey.']" >'.htmlentities($moduleLangFileManager->translations[$currentLang][$fileName][$tradKey]).'</textarea>';
		print '	</td>';
		print '	<td>';
		print '		<label class="dev-tool-lang-label" for="trad_'.$tradKey.'" >'.$targetFlag.' '.$tradKey.'</label>';
		print '		<textarea class="dev-tool-lang-textarea" autoresize="1" id="trad_'.$tradKey.'" name="trad['.$tradKey.']" >'.htmlentities($newTrad).'</textarea>';
		print '	</td>';
		print '</tr>';
	}

	print '</table>';

	print '<button type="submit" class="button" >'.$langs->trans('Submit').'</button>';

	print '</fieldset>';
}
function __display_langs_stats(){
	global $form, $module, $moduleLangFileManager, $langs, $currentLang, $moduleName,$langsStats,$allTranslationsFiles;

	print '<h3>'.$module->getName().'</h3>';
	print '<p>'.$module->getDesc().'</p>';
	print '<p>'.$langs->trans('CurrentLangIsX', $currentLang).'</p>';


	if(!$langsStats) {
		return ;
	}

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
		$flag = $moduleLangFileManager::getFlag($langKey);
		print '<span class="classfortooltip" title="'.dol_escape_htmltag($countryAssociated).'" >'.$flag.'</span> ';
		print $countryAssociated;
		print '		</td>';


		// Langs files list
		foreach ($allTranslationsFiles as $fileName){
			if(!$langFileStats[$fileName]->fileExist){
				print '<td colspan="2">'.dolGetBadge($langs->trans('FileNotFound'), '','danger').'</td>';
			}else{

				$editUrl = dol_buildpath('devcommunitytools/tools/translate_module_lang.php',1);
				$editUrl.= '?action=add-missing-translations&token='.newToken();
				$editUrl.= '&file-name='.$fileName.'&module='.$moduleName;

				print '<td class="center col-start-border"  >';
				if(empty($langFileStats[$fileName]->missingTranslations)){
					print '<span class="fa fa-check" style="color: dimgrey"></span>';
				}
				else{
					$url = $editUrl.'&target-lang='.$langKey.'&used-lang='.$currentLang.'#edit-lang-file-form';

					$params = array(
						'attr' => array(
							'class' => ' classfortooltip',
							'title' => $langs->trans('MissingTranslations')
						)
					);
					print dolGetBadge('-'.$langFileStats[$fileName]->missingTranslations, '', 'danger', '', $url, $params);
				}

				print '</td>';

				print '<td class="center col-end-border" >';
				if(empty($langFileStats[$fileName]->additionalsTranslations)){
					print '<span class="fa fa-check" style="color: dimgrey"></span>';
				}
				else {
					$url = $editUrl.'&target-lang='.$currentLang.'&used-lang='.$langKey.'#edit-lang-file-form';


						//$targetLang = GETPOST('target-lang', 'aZ09');
						//$fileName = GETPOST('file-name', 'aZ09');
						//$currentLang, $targetLang, $fileName
						//	if($action == 'add-missing-translations'){

					$params = array(
						'attr' => array(
							'class' => ' classfortooltip',
							'title' => $langs->trans('AdditionalTranslations')
						)
					);
					print dolGetBadge('+' . $langFileStats[$fileName]->additionalsTranslations, '', 'primary', '', $url, $params);
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