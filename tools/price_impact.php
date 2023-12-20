<?php
require_once __DIR__ . '/inc/__tools_header.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

$devToolScriptName =  'UrlReplace';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('devcommunitytools'.$devToolScriptName));

// Load translation files required by the page

$action = GETPOST('action', 'aZ09');
$ref = GETPOST("ref");
$priceimpact = GETPOST("priceimpact");
$backtopage = GETPOST('backtopage', 'alpha');
$updateproductprices = GETPOST("updateproductprices");
$update_status = GETPOST("update_status");
$logManager = new devCommunityTools\LogManager();
$form = new Form($db);
$token = '';
if(!function_exists('newToken')){
	$token = $_SESSION['newtoken'];
}
else{
	$token = newToken();
}


if($action == 'confirm_massive_update'){

	if(!empty($updateproductprices)) {
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."product WHERE ref LIKE '".$db->escape($ref)."%'";
		$db->begin();
		$resql = $db->query($sql);
		if ($resql) {
			while ($obj = $db->fetch_object($resql)) {
				$tempvariation = floatval($priceimpact);
				if (empty($priceimpact)) {
					$sql3 = " UPDATE " . MAIN_DB_PREFIX . "product_price SET price = (".$db->escape($tempvariation)."), price_ttc = price * (1 + (tva_tx/100)) WHERE fk_product = " . (int)$obj->rowid . ";";
				} else
					$sql3 = " UPDATE " . MAIN_DB_PREFIX . "product_price SET price = price + (".$db->escape($tempvariation)."), price_ttc = price * (1 + (tva_tx/100)) WHERE fk_product = " . (int)$obj->rowid . ";";
				$resql3 = $db->query($sql3);
				if(!$resql3){
					$logManager->addError($db->lasterror());
					break;
				}
			}
		}
		else{
			$logManager->addError($db->lasterror());
		}
	}

	if(!empty($conf->variants->enabled)) {
		$sql3 = "UPDATE " . MAIN_DB_PREFIX . "product_attribute_combination SET " . MAIN_DB_PREFIX . "product_attribute_combination.variation_price = ".$db->escape($priceimpact)." WHERE " . MAIN_DB_PREFIX . "product_attribute_combination.fk_product_child IN (SELECT rowid FROM " . MAIN_DB_PREFIX . "product WHERE ref LIKE '" . $db->escape($ref). "%');";
		$resql3 = $db->query($sql3);
		if (!$resql3) {
			$logManager->addError($db->lasterror());
		}
	}

	if(!empty($logManager->getErrors())){
		$db->rollback();
	}
	else{
		$db->commit();
		header("Location: ".$_SERVER["PHP_SELF"]."?update_status=done");
	}
}

llxHeader("", $langs->trans("PriceimpactonvariantsArea"));

if($update_status == "done" && empty($action)){
	setEventMessage($langs->trans("UpdateDone"));
}


if($action == 'search_variants'){

	$formconfirm = '';
	if(empty($ref)){
		$logManager->addError($langs->trans("EmptyRefError"));
	}

	if(empty($priceimpact)){
		$logManager->addError($langs->trans("EmptyPriceImpact"));
	}

	$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."product WHERE ref LIKE '".$ref."%';";
	$resql = $db->query($sql);
	if($resql){
		$message = $resql->num_rows." ".$langs->trans('VariantsFound').".";
		$message .= "</br>".$langs->trans("UpdateConfirmAsk");
		if(!empty($updateproductprices)) {
			$message .= "</br>".$langs->trans("UpdatePriceActivated");
		}
		$message .= "</br>".$langs->trans("Caution");
		$message .= "</br>".$langs->trans("CautionAdvice");
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?ref='.$ref.'&priceimpact='.$priceimpact.'&updateproductprices='.$updateproductprices, $langs->trans('ConfirmVariantsModification'), $message, 'confirm_massive_update', '', 0, 1,300);
		print $formconfirm;
	}
	else{
		$logManager->addError($db->lasterror());
	}
}


// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : dol_buildpath('/devcommunitytools/admin/tools.php', 1)).'">'.$langs->trans("BackToToolsList").'</a>';
print load_fiche_titre($langs->trans("PriceimpactonvariantsArea"), $linkback, 'title_setup');

print '<div class="info">';
print $langs->trans("PriceImpactInfo1");
print $langs->trans("PriceImpactInfo2");
print $langs->trans("PriceImpactInfo3");
print '</div>';

print '<div class="fichecenter">';


print '<fieldset>';
print '<legend>'.$langs->trans("PriceimpactonvariantsArea").'</legend>';
print '<form action="" method="POST">';
print '<input name="action" value="search_variants" hidden/>';
print '<input name="token" value="'.$token.'" hidden/>';

print '<table class="border centpercent">';
print '<tbody>';

print '<tr>';
print '<td class="titlefieldcreate">';
print '<label for="ref">'.$langs->trans("ReferenceSearch").'</label>';
print '</td>';

print '<td>';
print '<input name="ref" type="text" value="'.$ref.'" required/>';
print '</td>';
print '</tr>';

print '<tr>';
print '<td class="titlefieldcreate">';
print '<label for="ref">'.$langs->trans("NewPriceImpact").'</label>';

print '<td>';
print '<input name="priceimpact" type="number" value="'.$priceimpact.'" step="0.01" required/>';
print '</td>';
print '</tr>';

print '<tr>';
print '<td class="titlefieldcreate">';
print '<label for="updateproductprices">'.$langs->trans("UpdatePriceOnUpdate").'</label>';
print '</td>';

print '<td>';
print '<input type="checkbox" name="updateproductprices" checked/>';
print '</td>';
print '</tr>';

print '<tr>';
print '<td></td>';
print '<td>';
print '<button type="submit" class="butAction"> Modifier </button>';
print '</td>';
print '</tr>';
print '</tbody>';
print '</table>';
print '</form>';
print '</fieldset>';

print '</div>';
print $logManager->output(true);

// End of page
llxFooter();
$db->close();