<?php require_once __DIR__ . '/inc/__tools_header.php';

$devToolScriptName =  'YOUR_PAGE_NAME';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('devcommunitytools'.$devToolScriptName));

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');	// Used by actions_setmoduleoptions.inc.php

$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');


$error = 0;



/*
 * Actions
 */





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




/*
 * End Script View
 */


// Page end
print dol_get_fiche_end();


require_once __DIR__.'/inc/__tools_footer.php';