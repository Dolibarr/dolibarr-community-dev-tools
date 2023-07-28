<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 John Botella <john.botella@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    devcommunitytools/admin/setup.php
 * \ingroup devcommunitytools
 * \brief   DevCommunityTools setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/devcommunitytools.lib.php';

// Translations
$langs->loadLangs(array("admin", "devcommunitytools@devcommunitytools"));

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('devcommunitytoolssetup','devcommunitytools', 'globalsetup'));

// Access control
if (!$user->admin) {
	accessforbidden();
}

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
$page_name = "DevCommunityToolsSetup";
$arrayofjs = array(
	'devcommunitytools/js/devtools.js'
);

$arrayofcss = array(
	'devcommunitytools/css/devtools.css'
);

llxHeader('', $langs->trans($page_name), $help_url, '', 0, 0, $arrayofjs, $arrayofcss);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = devcommunitytoolsAdminPrepareHead();
print dol_get_fiche_head($head, 'tools', $langs->trans($page_name), -1, "devcommunitytools@devcommunitytools");



if(!empty(checkDevToolsAccess(false))){
	print '<div class="error" ><h4>'.$langs->trans('DevToolsNeedsDevEnvironmentToAllowUsage').'</h4><p style="font-weight: normal;">'.$langs->transnoentities('DevToolsNeedsDevEnvironmentToAllowUsageDesc').'</p></div>';
}else {

	$toolList = array();

	$toolList[] = array(
		'title' => 'langsTrad',
		'desc' => 'langsTradDesc',
		'file' => 'devcommunitytools/tools/langs_trad.php',
		'icon' => ''
	);

	$toolList[] = array(
		'title' => 'zlangsTrad',
		'desc' => 'langsTradDesc',
		'file' => 'devcommunitytools/tools/langs_trad.php',
		'icon' => ''
	);


	$toolList[] = array(
		'title' => 'alangsTrad',
		'desc' => 'langsTradDesc',
		'file' => 'devcommunitytools/tools/langs_trad.php',
		'icon' => ''
	);


	devToolsListSortByItemChildArrayKey($toolList,'title');


	print '<div class="dev-tools-search-container"><input name="search_dev_tools" value="" id="search-dev-tools-form-input" class="dev-tools-search-input"   placeholder="'.$langs->trans('Search').'" autocomplete="off"></div>';


	print '<div class="box-flex-container" >';
	foreach($toolList as $toolItem){

		$item = new stdClass();
		$item->title = !empty($toolItem['title'])?$langs->trans($toolItem['title']):$langs->trans('TitleMissing');
		$item->icon  = !empty($toolItem['icon'])?$toolItem['icon']:'fa-tools';
		$item->url   = !empty($toolItem['file'])?dol_buildpath($toolItem['file'], 1):'';


		print '<div class="box-flex-item">';
		print '	<div class="box-flex-item-with-margin">';
		print '		<div class="info-box ">';
		print '			<span class="info-box-icon bg-infobox-project">';
		print '				<i class="fa fa-tools"></i>';
		print '			</span>';
		print '			<div class="info-box-content">';
		print '				<div class="info-box-title" title="'.dol_escape_htmltag($item->title).'"><a href="'.$item->url.'" >'.$item->title.'</a></div>';
		print '				<div class="info-box-lines">';

		print '				<div class="info-box-line"><a href="'.$item->url.'" >'.$langs->trans('Use').'</a></div>';


		print '				</div><!-- /.info-box-lines -->';
		print '			</div><!-- /.info-box-content -->';
		print '		</div>';
		print '	</div>';
		print '</div>';
	}

	for ($i = 1; $i <= 10; $i++) {
		print  '<div class="box-flex-item filler"></div>';
	}

	print '</div>';
}

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
