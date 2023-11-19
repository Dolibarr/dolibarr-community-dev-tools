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
$changeDomaineToo = GETPOST('change-domain-too', 'int');
$useRollback = GETPOST('use-rollback', 'int');

$error = 0;



/*
 * Actions
 */

$logManager = new devCommunityTools\LogManager();

if($action = 'replaceUrl'){
	__processUrlReplace($oldUrl, $newUrl, $logManager);

	if(!empty($changeDomaineToo)){
		__processUrlReplace($oldUrl, $newUrl, $logManager, true);
	}
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
print '<input type="url" name="old-url" placeholder="'.$langs->trans('OldUrl').'" value="'.dol_escape_htmltag($oldUrl).'" required >';
print '<input type="url" name="new-url" placeholder="'.$langs->trans('NewUrl').'" value="'.dol_escape_htmltag($newUrl).'" required >';
print '<button type="submit">'.$langs->trans('Submit').'</button>';

print '<br/><label><input type="checkbox" name="change-domain-too"  value="1" '.($changeDomaineToo?' checked ':'').' > '.$langs->trans('ChangeDomainToo').'</label>';
print '<br/><label><input type="checkbox" name="use-rollback"  value="1" '.($useRollback?' checked ':'').' > '.$langs->trans('UseRollback').'</label>';


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
function __processUrlReplace($oldUrl, $newUrl, $logManager, $replaceDomaineOnly = false, $use_rollback = true) {
	global $db, $langs;
	require_once DOL_DOCUMENT_ROOT . '/core/class/validate.class.php';
	$validate = new Validate($db, $langs);

	if (empty($oldUrl) || !$validate->isUrl($oldUrl)) {
		$logManager->addError($validate->error);
		return false;
	}

	if (empty($newUrl) || !$validate->isUrl($newUrl)) {
		$logManager->addError($validate->error);
		return false;
	}

	if ($replaceDomaineOnly) {
		$parseOldUrl = parse_url($oldUrl);
		$parseNewUrl = parse_url($newUrl);
		$oldUrl = $parseOldUrl['host'];
		$newUrl = $parseNewUrl['host'];
	}


	$logManager->addLog($langs->trans('ReplaceUrlXByY', $oldUrl, $newUrl));
	$db->begin();
	$tables = array('c_email_templates' => array('content'), 'user' => array('signature'), 'mailing' => array('sujet', 'body'));

	if ($use_rollback) {
		$db->begin();
	}


	foreach ($tables as $tableName => $cols) {
		$tableName = MAIN_DB_PREFIX . $tableName;
		$sqlShowTable = "SHOW TABLES LIKE '" . $db->escape($tableName) . "' ";
		$resST = $db->query($sqlShowTable);
		if ($resST && $db->num_rows($resST) > 0) {
			foreach ($cols as $col) {
				$sql = "UPDATE `" . $db->escape($tableName) . "` SET `" . $db->escape($col) . "` = REPLACE(`" . $db->escape($col) . "`,'" . $db->escape($oldUrl) . "' ,'" . $db->escape($newUrl) . "');";
				$resCol = $db->query($sql);
				if (!$sql) {
					$logManager->addError($tableName . " :  " . $col . " UPDATE ERROR " . $db->error());
					$db->rollback();
				} else {
					$num = $db->affected_rows($resCol);
					$logManager->addSuccess($tableName . " :  " . $col . " => " . $num);
				}
			}
		} else {
			$logManager->addError("Error : " . $sqlShowTable . " " . $db->error());
		}
	}

	if (!empty($logManager->getErrors()) && $use_rollback) {
		$db->rollback();
		$logManager->addError($langs->trans("UsedRollback"));
	} else {
		$db->commit();
		$logManager->addError($langs->trans("Commited"));
	}
}
