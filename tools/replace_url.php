<?php require_once __DIR__ . '/inc/__tools_header.php';

$devToolScriptName =  'UrlReplace';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('devcommunitytools'.$devToolScriptName));

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');	// Used by actions_setmoduleoptions.inc.php

$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');

$oldUrl = GETPOST('old-url', 'alpha');
$newUrl = GETPOST('new-url', 'alpha');

$error = 0;



/*
 * Actions
 */

$logManager = new devCommunityTools\LogManager();

if($action = 'replaceUrl'){
	__processUrlReplace($oldUrl, $newUrl, $logManager);
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

print '<form action="'.$_SERVER['PHP_SELF'].'">';

print '<fieldset >';

print '	<legend>'.$langs->trans('ReplaceUrlInTables').'</legend>';
print '<input type="hidden" name="action" value="replaceUrl" />';
print '<input type="hidden" name="token" value="'.newToken().'" />';
print '<input name="old-url" placeholder="'.$langs->trans('OldUrl').'" value="'.dol_escape_htmltag($oldUrl).'" >';
print '<input name="new-url" placeholder="'.$langs->trans('NewUrl').'" value="'.dol_escape_htmltag($newUrl).'"  >';
print '<button type="submit">'.$langs->trans('Submit').'</button>';

print '</fieldset>';

print '</form>';


$logManager->output(true);

/*
 * End Script View
 */


// Page end
print dol_get_fiche_end();


require_once __DIR__.'/inc/__tools_footer.php';

/**
 * @param string $oldUrl
 * @param string $newUrl
 * @param devCommunityTools\LogManager $logManager
 * @return false|void
 */
function __processUrlReplace($oldUrl, $newUrl, $logManager){
	global $db, $langs;

	require_once DOL_DOCUMENT_ROOT . '/core/class/validate.class.php';
	$validate = new Validate($db, $langs);

	if (empty($oldUrl) || !$validate->isUrl($oldUrl)){
		$logManager->addError($validate->error);
		return false;
	}

	if (empty($newUrl) || !$validate->isUrl($newUrl)){
		$logManager->addError($validate->error);
		return false;
	}

	$tables = array(
		'c_email_templates' => array( 'content'),
		'user' => array( 'signature'),
	);

	foreach ($tables as $tableName => $cols){
		$tableName = MAIN_DB_PREFIX.$tableName;
		$sqlShowTable = "SHOW TABLES LIKE '".$db->escape($tableName)."' ";
		$resST = $db->query($sqlShowTable);
		if($resST && $db->num_rows($resST) > 0) {
			foreach ($cols as $col){
				$sql = "UPDATE `".$db->escape($tableName)."` SET `".$db->escape($col)."` = REPLACE(`".$db->escape($col)."`,'".$db->escape($oldUrl)."' ,'".$db->escape($newUrl)."');";
				$resCol = $db->query($sql);
				if(!$sql){
					$logManager->addError($tableName. " :  ".$col." UPDATE ERROR ".$db->error());
				}else{
					$num = $db->affected_rows($resCol);
					$logManager->addSuccess($tableName. " :  ".$col." => ".$num);
				}
			}
		}
		else{
			$logManager->addError("Error : " .$sqlShowTable. " ". $db->error());
		}
	}
}