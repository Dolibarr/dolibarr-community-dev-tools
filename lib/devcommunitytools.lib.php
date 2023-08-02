<?php
/* Copyright (C) 2023 John Botella <john.botella@atm-consulting.fr>
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
 * \file    devcommunitytools/lib/devcommunitytools.lib.php
 * \ingroup devcommunitytools
 * \brief   Library files with common functions for DevCommunityTools
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function devcommunitytoolsAdminPrepareHead()
{
	global $langs, $conf;

	// global $db;
	// $extrafields = new ExtraFields($db);
	// $extrafields->fetch_name_optionals_label('myobject');

	$langs->load("devcommunitytools@devcommunitytools");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/devcommunitytools/admin/tools.php", 1);
	$head[$h][1] = $langs->trans("Tools");
	$head[$h][2] = 'tools';
	$h++;

	$head[$h][0] = dol_buildpath("/devcommunitytools/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("DevCommunityToolsSettings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/devcommunitytools/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$nbExtrafields = is_countable($extrafields->attributes['myobject']['label']) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/devcommunitytools/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@devcommunitytools:/devcommunitytools/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@devcommunitytools:/devcommunitytools/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'devcommunitytools@devcommunitytools');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'devcommunitytools@devcommunitytools', 'remove');

	return $head;
}

/**
 * check if Dev tools can be used in this environement
 *
 * @param bool $callAccessForbiden if true will trigger an access forbiden en exit instead of returning ;
 * @return bool
 */
function checkDevToolsAccess($callAccessForbiden = true){
	global $dolibarr_main_prod, $user;

	// Do not use dev tool in prod
	$rights = empty($dolibarr_main_prod) && !empty($user->admin);
	if($rights){
		return true;
	}

	if($callAccessForbiden){
		accessForbidden();
	}

	return false;
}

/**
 * @param $array of items to sort
 * @param $key key of item array to use
 * @return bool
 */
function devToolsListSortByItemChildArrayKey(&$array, $key)
{
	return usort($array, function ($a, $b) use ($key) {
		return strnatcmp($a[$key], $b[$key]);
	});
}
