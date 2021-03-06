<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

include('includes/DefineSuppTransClass.php');
/* Session started in header.php for password checking and authorisation level check */
include('includes/session.php');
$Title = _('Enter Supplier Invoice Against Goods Received');
include('includes/header.php');

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Dispatch'), '" alt="" />', ' ', $Title, '
	</p>';

$Complete = false;
if (!isset($_SESSION['SuppTrans'])) {
	prnMsg(_('To enter a supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice must be clicked on'), 'info');
	echo '<a href="', $RootPath, '/SelectSupplier.php">', _('Select A Supplier to Enter a Transaction For'), '</a>';
	include('includes/footer.php');
	exit;
	/*It all stops here if there aint no supplier selected and invoice initiated ie $_SESSION['SuppTrans'] started off*/
} //!isset($_SESSION['SuppTrans'])

/*If the user hit the Add to Invoice button then process this first before showing  all GRNs on the invoice
otherwise it wouldnt show the latest additions*/
if (isset($_POST['AddPOToTrans']) and $_POST['AddPOToTrans'] != '') {
	foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) { //loop around temp GRNs array
		if ($_POST['AddPOToTrans'] == $GRNTmp->PONo) {
			$_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp); //copy from  temp GRNs array to entered GRNs array
			$_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo); //remove from temp GRNs array
		} //$_POST['AddPOToTrans'] == $GRNTmp->PONo
	} //$_SESSION['SuppTransTmp']->GRNs as $GRNTmp
} //isset($_POST['AddPOToTrans']) and $_POST['AddPOToTrans'] != ''

if (isset($_POST['AddGRNToTrans'])) {
	/*adding a GRN to the invoice */
	foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
		if (isset($_POST['GRNNo_' . $GRNTmp->GRNNo])) {
			$_POST['GRNNo_' . $GRNTmp->GRNNo] = true;
		} //isset($_POST['GRNNo_' . $GRNTmp->GRNNo])
		else {
			$_POST['GRNNo_' . $GRNTmp->GRNNo] = false;
		}
		$Selected = $_POST['GRNNo_' . $GRNTmp->GRNNo];
		if ($Selected == True) {
			$_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp);
			$_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo);
		} //$Selected == True
	} //$_SESSION['SuppTransTmp']->GRNs as $GRNTmp
} //isset($_POST['AddGRNToTrans'])

if (isset($_POST['ModifyGRN'])) {
	$InputError = False;
	$Hold = False;
	if (filter_number_format($_POST['This_QuantityInv']) >= ($_POST['QtyRecd'] - $_POST['Prev_QuantityInv'])) {
		$Complete = True;
	} //filter_number_format($_POST['This_QuantityInv']) >= ($_POST['QtyRecd'] - $_POST['Prev_QuantityInv'])
	else {
		$Complete = False;
	}

	if (filter_number_format($_POST['This_QuantityInv']) + $_POST['Prev_QuantityInv'] - $_POST['QtyRecd'] > 0) {
		prnMsg(_('The quantity being invoiced is more than the outstanding quantity that was delivered. It is not possible to enter an invoice for a quantity more than was received into stock'), 'warn');
		$InputError = True;
	} //filter_number_format($_POST['This_QuantityInv']) + $_POST['Prev_QuantityInv'] - $_POST['QtyRecd'] > 0
	if (!is_numeric(filter_number_format($_POST['ChgPrice'])) and filter_number_format($_POST['ChgPrice']) < 0) {
		$InputError = True;
		prnMsg(_('The price charged in the suppliers currency is either not numeric or negative') . '. ' . _('The goods received cannot be invoiced at this price'), 'error');
	} //!is_numeric(filter_number_format($_POST['ChgPrice'])) and filter_number_format($_POST['ChgPrice']) < 0
	elseif ($_SESSION['Check_Price_Charged_vs_Order_Price'] == True and $_POST['OrderPrice'] != 0) {
		if (filter_number_format($_POST['ChgPrice']) / $_POST['OrderPrice'] > (1 + ($_SESSION['OverChargeProportion'] / 100))) {
			prnMsg(_('The price being invoiced is more than the purchase order price by more than') . ' ' . $_SESSION['OverChargeProportion'] . '%. ' . _('The system is set up to prohibit this so will put this invoice on hold until it is authorised'), 'warn');
			$Hold = True;
		} //filter_number_format($_POST['ChgPrice']) / $_POST['OrderPrice'] > (1 + ($_SESSION['OverChargeProportion'] / 100))
	} //$_SESSION['Check_Price_Charged_vs_Order_Price'] == True and $_POST['OrderPrice'] != 0

	if ($InputError == False) {
		$_SESSION['SuppTrans']->Modify_GRN_To_Trans($_POST['GRNNumber'], $_POST['PODetailItem'], $_POST['ItemCode'], $_POST['ItemDescription'], $_POST['QtyRecd'], $_POST['Prev_QuantityInv'], filter_number_format($_POST['This_QuantityInv']), $_POST['OrderPrice'], filter_number_format($_POST['ChgPrice']), $Complete, $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo' . $i]]->StdCostUnit, $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo' . $i]]->ShiptRef, $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo' . $i]]->JobRef, $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo' . $i]]->GLCode, $Hold, $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo' . $i]]->SupplierRef);
	} //$InputError == False
} //isset($_POST['ModifyGRN'])

if (isset($_GET['Delete'])) {
	$_SESSION['SuppTransTmp']->Copy_GRN_To_Trans($_SESSION['SuppTrans']->GRNs[$_GET['Delete']]);
	$_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);
} //isset($_GET['Delete'])


/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */

echo '<table class="selection">
		<thead>
			<tr>
				<th colspan="7"><h3>', _('Invoiced Goods Received Selected'), '</h3></th>
			</tr>
			<tr>
				<th class="SortedColumn">', _('Sequence'), ' #</th>
				<th>', _('Supplier\'s Ref'), '</th>
				<th class="SortedColumn">', _('Item Code'), '</th>
				<th class="SortedColumn">', _('Description'), '</th>
				<th>', _('Quantity Charged'), '</th>
				<th>', _('Price Charge in'), ' ', $_SESSION['SuppTrans']->CurrCode, '</th>
				<th>', _('Line Value in'), ' ', $_SESSION['SuppTrans']->CurrCode, '</th>
			</tr>
		</thead>';

$TotalValueCharged = 0;
echo '<tbody>';
foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
	if ($EnteredGRN->ChgPrice > 1) {
		$DisplayPrice = locale_number_format($EnteredGRN->ChgPrice, $_SESSION['SuppTrans']->CurrDecimalPlaces);
	} else {
		$DisplayPrice = locale_number_format($EnteredGRN->ChgPrice, 4);
	}
	echo '<tr>
			<td>', $EnteredGRN->GRNNo, '</td>
			<td>', $EnteredGRN->SupplierRef, '</td>
			<td>', $EnteredGRN->ItemCode, '</td>
			<td>', $EnteredGRN->ItemDescription, '</td>
			<td class="number">', locale_number_format($EnteredGRN->This_QuantityInv, 'Variable') . '</td>
			<td class="number">', $DisplayPrice . '</td>
			<td class="number">', locale_number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv, $_SESSION['SuppTrans']->CurrDecimalPlaces), '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?Modify=', $EnteredGRN->GRNNo, '">', _('Modify'), '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?Delete=', $EnteredGRN->GRNNo, '">', _('Delete'), '</a></td>
		</tr>';

	$TotalValueCharged = $TotalValueCharged + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

} //$_SESSION['SuppTrans']->GRNs as $EnteredGRN

echo '</tbody>
		<tr>
			<td colspan="5" align="right">', _('Total Value of Goods Charged'), ':</td>
			<td class="number"><h4>', locale_number_format($TotalValueCharged, $_SESSION['SuppTrans']->CurrDecimalPlaces), '</h4></td>
		</tr>
	</table>
	<div class="centre">
		<a href="', $RootPath, '/SupplierInvoice.php">', _('Back to Invoice Entry'), '</a>
	</div>';


/* Now get all the outstanding GRNs for this supplier from the database*/

$SQL = "SELECT grnbatch,
				grnno,
				purchorderdetails.orderno,
				purchorderdetails.unitprice,
				grns.itemcode,
				grns.deliverydate,
				grns.itemdescription,
				grns.qtyrecd,
				grns.quantityinv,
				grns.stdcostunit,
				grns.supplierref,
				purchorderdetails.glcode,
				purchorderdetails.shiptref,
				purchorderdetails.jobref,
				purchorderdetails.podetailitem,
				purchorderdetails.assetid,
				stockmaster.decimalplaces
		FROM grns INNER JOIN purchorderdetails
			ON  grns.podetailitem=purchorderdetails.podetailitem
		LEFT JOIN stockmaster ON grns.itemcode=stockmaster.stockid
		WHERE grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "'
		AND grns.qtyrecd - grns.quantityinv > 0
		ORDER BY grns.grnno";
$GRNResults = DB_query($SQL);

if (DB_num_rows($GRNResults) == 0) {
	prnMsg(_('There are no outstanding goods received from') . ' ' . $_SESSION['SuppTrans']->SupplierName . ' ' . _('that have not been invoiced by them') . '<br />' . _('The goods must first be received using the link below to select purchase orders to receive'), 'warn');
	echo '<div class="centre">
			<a href="', $RootPath, '/PO_SelectOSPurchOrder.php?SupplierID=', urlencode($_SESSION['SuppTrans']->SupplierID), '">', _('Select Purchase Orders to Receive'), '</a>
		</div>';
	include('includes/footer.php');
	exit;
} //DB_num_rows($GRNResults) == 0

/*Set up a table to show the GRNs outstanding for selection */
echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (!isset($_SESSION['SuppTransTmp'])) {
	$_SESSION['SuppTransTmp'] = new SuppTrans;
	while ($MyRow = DB_fetch_array($GRNResults)) {
		$GRNAlreadyOnInvoice = False;

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {
			if ($EnteredGRN->GRNNo == $MyRow['grnno']) {
				$GRNAlreadyOnInvoice = True;
			} //$EnteredGRN->GRNNo == $MyRow['grnno']
		} //$_SESSION['SuppTrans']->GRNs as $EnteredGRN
		if ($MyRow['decimalplaces'] == '') {
			$MyRow['decimalplaces'] = 2;
		} //$MyRow['decimalplaces'] == ''
		if ($GRNAlreadyOnInvoice == False) {
			$_SESSION['SuppTransTmp']->Add_GRN_To_Trans($MyRow['grnno'], $MyRow['podetailitem'], $MyRow['itemcode'], $MyRow['itemdescription'], $MyRow['qtyrecd'], $MyRow['quantityinv'], $MyRow['qtyrecd'] - $MyRow['quantityinv'], $MyRow['unitprice'], $MyRow['unitprice'], $Complete, $MyRow['stdcostunit'], $MyRow['shiptref'], $MyRow['jobref'], $MyRow['glcode'], $MyRow['orderno'], $MyRow['assetid'], 0, $MyRow['decimalplaces'], $MyRow['grnbatch'], $MyRow['supplierref']);
		} //$GRNAlreadyOnInvoice == False
	} //$MyRow = DB_fetch_array($GRNResults)
} //!isset($_SESSION['SuppTransTmp'])

if (isset($_GET['Modify'])) {
	$GRNNo = $_GET['Modify'];
	$GRNTmp = $_SESSION['SuppTrans']->GRNs[$GRNNo];

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="10"><h3>', _('GRN Selected For Adding To A Purchase Invoice'), '</h3></th>
			</tr>';
	echo '<tr>
			<th>', _('Sequence'), ' #</th>
			<th>', _('Item'), '</th>
			<th>', _('Qty Outstanding'), '</th>
			<th>', _('Qty Invoiced'), '</th>
			<th>', _('Order Price in'), ' ', $_SESSION['SuppTrans']->CurrCode, '</th>
			<th>', _('Actual Price in'), ' ', $_SESSION['SuppTrans']->CurrCode, '</th>
		</tr>
	</thead>';

	echo '<tbody>
			<tr>
				<td>', $GRNTmp->GRNNo, '</td>
				<td>', $GRNTmp->ItemCode, ' ', $GRNTmp->ItemDescription, '</td>
				<td class="number">', locale_number_format($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv, $GRNTmp->DecimalPlaces), '</td>
				<td><input type="text" class="number" name="This_QuantityInv" value="', locale_number_format($GRNTmp->This_QuantityInv, 'Variable'), '" size="11" required="required" maxlength="10" /></td>
				<td class="number">', locale_number_format($GRNTmp->OrderPrice, $_SESSION['SuppTrans']->CurrDecimalPlaces), '</td>
				<td><input type="text" class="number" name="ChgPrice" value="', locale_number_format($GRNTmp->ChgPrice, $_SESSION['SuppTrans']->CurrDecimalPlaces), '" size="11" required="required" maxlength="10" /></td>
			</tr>
		</tbody>';
	echo '</table>';

	echo '<input type="hidden" name="ShiptRef" value="', $GRNTmp->ShiptRef, '" />';

	echo '<div class="centre">
			<input type="submit" name="ModifyGRN" value="', _('Modify Line'), '" />
		</div>';


	echo '<input type="hidden" name="GRNNumber" value="', $GRNTmp->GRNNo, '" />';
	echo '<input type="hidden" name="ItemCode" value="', $GRNTmp->ItemCode, '" />';
	echo '<input type="hidden" name="ItemDescription" value="', $GRNTmp->ItemDescription, '" />';
	echo '<input type="hidden" name="QtyRecd" value="', $GRNTmp->QtyRecd, '" />';
	echo '<input type="hidden" name="Prev_QuantityInv" value="', $GRNTmp->Prev_QuantityInv, '" />';
	echo '<input type="hidden" name="OrderPrice" value="', $GRNTmp->OrderPrice, '" />';
	echo '<input type="hidden" name="StdCostUnit" value="', $GRNTmp->StdCostUnit, '" />';
	echo '<input type="hidden" name="JobRef" value="', $GRNTmp->JobRef, '" />';
	echo '<input type="hidden" name="GLCode" value="', $GRNTmp->GLCode, '" />';
	echo '<input type="hidden" name="PODetailItem" value="', $GRNTmp->PODetailItem, '" />';
	echo '<input type="hidden" name="AssetID" value="', $GRNTmp->AssetID, '" />';
} //isset($_GET['Modify'])
else {
	if (count($_SESSION['SuppTransTmp']->GRNs) > 0) {
		/*if there are any outstanding GRNs then */
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="10"><h3>', _('Goods Received Yet to be Invoiced From'), ' ', $_SESSION['SuppTrans']->SupplierName, '</h3></th>
					</tr>
					<tr>
						<th class="SortedColumn">', _('Select'), '</th>
						<th class="SortedColumn">', _('Sequence'), ' #</th>
						<th class="SortedColumn">', _('GRN Number'), '</th>
						<th class="SortedColumn">', _('Supplier\'s Ref'), '</th>
						<th class="SortedColumn">', _('Order'), '</th>
						<th class="SortedColumn">', _('Item Code'), '</th>
						<th>', _('Description'), '</th>
						<th>', _('Total Qty Received'), '</th>
						<th>', _('Qty Already Invoiced'), '</th>
						<th>', _('Qty Yet To Invoice'), '</th>
						<th>', _('Order Price in'), ' ', $_SESSION['SuppTrans']->CurrCode, '</th>
						<th>', _('Line Value in'), ' ', $_SESSION['SuppTrans']->CurrCode, '</th>
					</tr>
				</thead>';
		echo '<tbody>';
		$POs = array();
		foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
			$_SESSION['SuppTransTmp']->GRNs[$GRNTmp->GRNNo]->This_QuantityInv = $GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv;

			if (isset($POs[$GRNTmp->PONo]) and $POs[$GRNTmp->PONo] != $GRNTmp->PONo) {
				$POs[$GRNTmp->PONo] = $GRNTmp->PONo;
				echo '<tr>
						<td><input type="submit" name="AddPOToTrans" value="', $GRNTmp->PONo, '" /></td>
						<td colspan="3">', _('Add Whole PO to Invoice'), '</td>
					</tr>';
			} //isset($POs[$GRNTmp->PONo]) and $POs[$GRNTmp->PONo] != $GRNTmp->PONo
			if (isset($_POST['SelectAll'])) {
				echo '<tr>
						<td><input type="checkbox" checked name="GRNNo_', $GRNTmp->GRNNo, '" /></td>';
			} //isset($_POST['SelectAll'])
			else {
				echo '<tr>
						<td><input type="checkbox" name="GRNNo_', $GRNTmp->GRNNo, '" /></td>';
			}
			echo '<td>', $GRNTmp->GRNNo, '</td>
				<td>', $GRNTmp->GRNBatchNo, '</td>
				<td>', $GRNTmp->SupplierRef, '</td>
				<td>', $GRNTmp->PONo, '</td>
				<td>', $GRNTmp->ItemCode, '</td>
				<td>', $GRNTmp->ItemDescription, '</td>
				<td class="number">', locale_number_format($GRNTmp->QtyRecd, $GRNTmp->DecimalPlaces), '</td>
				<td class="number">', locale_number_format($GRNTmp->Prev_QuantityInv, $GRNTmp->DecimalPlaces), '</td>
				<td class="number">', locale_number_format(($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv), $GRNTmp->DecimalPlaces), '</td>
				<td class="number">', locale_number_format($GRNTmp->OrderPrice, $_SESSION['SuppTrans']->CurrDecimalPlaces), '</td>
				<td class="number">', locale_number_format($GRNTmp->OrderPrice * ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv), $_SESSION['SuppTrans']->CurrDecimalPlaces), '</td>
			</tr>';
		} //$_SESSION['SuppTransTmp']->GRNs as $GRNTmp
		echo '</tbody>';
		echo '</table>';
		echo '<div class="centre">
				<input type="submit" name="SelectAll" value="', _('Select All'), '" />
				<input type="submit" name="DeSelectAll" value="', _('Deselect All'), '" />
				<br />
				<input type="submit" name="AddGRNToTrans" value="', _('Add to Invoice'), '" />
			</div>';
	} //count($_SESSION['SuppTransTmp']->GRNs) > 0
}

echo '</form>';
include('includes/footer.php');
?>
