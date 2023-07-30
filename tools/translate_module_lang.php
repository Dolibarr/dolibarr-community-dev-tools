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


	var_dump($moduleLangFileManager->translations);

		// Load all language files of the qualified module
		if (isset($module->langfiles) && is_array($module->langfiles)) {
			foreach ($module->langfiles as $domain) {
				$langs->load($domain);
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

