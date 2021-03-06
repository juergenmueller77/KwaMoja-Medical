<?php

include('includes/session.php');
$Title = _('Search Shipments');
include('includes/header.php');
echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
}

if (isset($_GET['ShiptRef'])) {
	$ShiptRef = $_GET['ShiptRef'];
} elseif (isset($_POST['ShiptRef'])) {
	$ShiptRef = $_POST['ShiptRef'];
}

if (isset($_GET['SelectedSupplier'])) {
	$SelectedSupplier = $_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])) {
	$SelectedSupplier = $_POST['SelectedSupplier'];
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}

if (isset($ShiptRef) and $ShiptRef != '') {
	if (!is_numeric($ShiptRef)) {
		echo '<br />';
		prnMsg(_('The Shipment Number entered MUST be numeric'));
		unset($ShiptRef);
	} else {
		echo _('Shipment Number') . ' - ' . $ShiptRef;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo  _('For supplier') . ': ' . stripslashes($SelectedSupplier) . ' ' . _('and') . ' ';
		echo '<input type="hidden" name="SelectedSupplier" value="' . $SelectedSupplier . '" />';
	}
	if (isset($SelectedStockItem)) {
		echo _('for the part') . ': ' . stripslashes($SelectedStockItem) . '.';
		echo '<input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
	}
}

if (isset($_POST['SearchParts'])) {

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		echo '<br />';
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	$SQL = "SELECT stockmaster.stockid,
			description,
			decimalplaces,
			SUM(locstock.quantity) AS qoh,
			units,
			SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
		FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid = locstock.stockid
		INNER JOIN purchorderdetails
			ON stockmaster.stockid=purchorderdetails.itemcode";

	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL .= " WHERE purchorderdetails.shiptref IS NOT NULL
			AND purchorderdetails.shiptref<>0
			AND stockmaster.description " . LIKE . " '" . $SearchString . "'
			AND categoryid='" . $_POST['StockCat'] . "'";

	} elseif ($_POST['StockCode']) {

		$SQL .= " WHERE purchorderdetails.shiptref IS NOT NULL
			AND purchorderdetails.shiptref<>0
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
			AND categoryid='" . $_POST['StockCat'] . "'";

	} elseif (!$_POST['StockCode'] and !$_POST['Keywords']) {
		$SQL .= " WHERE purchorderdetails.shiptref IS NOT NULL
			AND purchorderdetails.shiptref<>0
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";

	}
	$SQL .= "  GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units";

	$ErrMsg = _('No Stock Items were returned from the database because') . ' - ' . DB_error_msg();
	$StockItemsResult = DB_query($SQL, $ErrMsg);

}

if (!isset($ShiptRef) or $ShiptRef == "") {
	echo '<table class="selection"><tr><td>';
	echo _('Shipment Number') . ': <input type="text" name="ShiptRef" maxlength="10" size="10" /> ' . _('Into Stock Location') . ' :<select name="StockLocation"> ';
	if ($_SESSION['RestrictLocations'] == 0) {
		$SQL = "SELECT locationname,
						loccode
					FROM locations";
	} else {
		$SQL = "SELECT locationname,
						loccode
					FROM locations
					INNER JOIN www_users
						ON locations.loccode=www_users.defaultlocation
					WHERE www_users.userid='" . $_SESSION['UserID'] . "'";
	}
	$ResultStkLocs = DB_query($SQL);
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_POST['StockLocation'])) {
			if ($MyRow['loccode'] == $_POST['StockLocation']) {
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		} elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
			$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}

	echo '</select>';
	echo ' <select name="OpenOrClosed">';
	if (isset($_POST['OpenOrClosed']) and $_POST['OpenOrClosed'] == 1) {
		echo '<option selected="selected" value="1">' . _('Closed Shipments Only') . '</option>';
		echo '<option value="0">' . _('Open Shipments Only') . '</option>';
	} else {
		$_POST['OpenOrClosed'] = 0;
		echo '<option value="1">' . _('Closed Shipments Only') . '</option>';
		echo '<option selected="selected" value="0">' . _('Open Shipments Only') . '</option>';
	}
	echo '</select></td></tr></table>';

	echo '<div class="centre">
			<input type="submit" name="SearchShipments" value="' . _('Search Shipments') . '" />
		</div>';
}

$SQL = "SELECT categoryid,
		categorydescription
	FROM stockcategory
	WHERE stocktype<>'D'
	ORDER BY categorydescription";
$Result1 = DB_query($SQL);

echo '<table class="selection">';
echo '<tr>
		<th colspan="5"><h3>' . _('To search for shipments for a specific part use the part selection facilities below') . '</h3></th>
	</tr>
	<tr>
		<td>' . _('Select a stock category') . ':
			<select name="StockCat">';

while ($MyRow1 = DB_fetch_array($Result1)) {
	if (isset($_POST['StockCat']) and $MyRow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}
}
echo '</select></td>
		<td>' . _('Enter text extracts in the') . '<b> ' . _('description') . '</b>:</td>
		<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
	</tr>
	<tr>
		<td></td>
		<td><b>' . _('OR') . ' </b> ' . _('Enter extract of the') . ' <b> ' . _('Stock Code') . '</b>:</td>
		<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>
	</tr>
	</table>';

echo '<div class="centre">
		<input type="submit" name="SearchParts" value="' . _('Search Parts Now') . '" />
		<input type="submit" name="ResetPart" value="' . _('Show All') . '" />
	</div>';

if (isset($StockItemsResult)) {

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Code') . '</th>
					<th class="SortedColumn">' . _('Description') . '</th>
					<th>' . _('On Hand') . '</th>
					<th>' . _('Orders') . '<br />' . _('Outstanding') . '</th>
					<th>' . _('Units') . '</th>
				</tr>
			</thead>';

	$k = 0; //row colour counter
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($StockItemsResult)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		/*
		Code	 Description	On Hand		 Orders Ostdg     Units		 Code	Description 	 On Hand     Orders Ostdg	Units	 */
		printf('<td><input type="submit" name="SelectedStockItem" value="%s" /></td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td></tr>', $MyRow['stockid'], $MyRow['description'], locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), locale_number_format($MyRow['qord'], $MyRow['decimalplaces']), $MyRow['units']);

	}
	//end of while loop

	echo '</tbody>';
	echo '</table>';

}
//end if stock search results to show
else {

	//figure out the SQL required from the inputs available

	if (isset($ShiptRef) and $ShiptRef != "") {
		$SQL = "SELECT shipments.shiptref,
				vessel,
				voyageref,
				suppliers.suppname,
				shipments.eta,
				shipments.closed
			FROM shipments INNER JOIN suppliers
				ON shipments.supplierid = suppliers.supplierid
			WHERE shipments.shiptref='" . $ShiptRef . "'";
	} else {
		$SQL = "SELECT DISTINCT shipments.shiptref, vessel, voyageref, suppliers.suppname, shipments.eta, shipments.closed
			FROM shipments INNER JOIN suppliers
				ON shipments.supplierid = suppliers.supplierid
			INNER JOIN purchorderdetails
				ON purchorderdetails.shiptref=shipments.shiptref
			INNER JOIN purchorders
				ON purchorderdetails.orderno=purchorders.orderno";

		if (isset($SelectedSupplier)) {

			if (isset($SelectedStockItem)) {
				$SQL .= " WHERE purchorderdetails.itemcode='" . $SelectedStockItem . "'
						AND shipments.supplierid='" . $SelectedSupplier . "'
						AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
						AND shipments.closed='" . $_POST['OpenOrClosed'] . "'";
			} else {
				$SQL .= " WHERE shipments.supplierid='" . $SelectedSupplier . "'
					AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
					AND shipments.closed='" . $_POST['OpenOrClosed'] . "'";
			}
		} else { //no supplier selected
			if (isset($SelectedStockItem)) {
				$SQL .= " WHERE purchorderdetails.itemcode='" . $SelectedStockItem . "'
					AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
					AND shipments.closed='" . $_POST['OpenOrClosed'] . "'";
			} else {
				$SQL .= " WHERE purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
					AND shipments.closed='" . $_POST['OpenOrClosed'] . "'";
			}

		} //end selected supplier
	} //end not order number selected

	$ErrMsg = _('No shipments were returned by the SQL because');
	$ShipmentsResult = DB_query($SQL, $ErrMsg);


	if (DB_num_rows($ShipmentsResult) > 0) {
		/*show a table of the shipments returned by the SQL */

		echo '<table width="95%" class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Shipment') . '</th>
						<th class="SortedColumn">' . _('Supplier') . '</th>
						<th class="SortedColumn">' . _('Vessel') . '</th>
						<th class="SortedColumn">' . _('Voyage') . '</th>
						<th class="SortedColumn">' . _('Expected Arrival') . '</th>
					</tr>
				</thead>';
		echo '<tbody>';
		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($ShipmentsResult)) {


			if ($k == 1) {
				/*alternate bgcolour of row for highlighting */
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				++$k;
			}

			$URL_Modify_Shipment = $RootPath . '/Shipments.php?SelectedShipment=' . $MyRow['shiptref'];
			$URL_View_Shipment = $RootPath . '/ShipmentCosting.php?SelectedShipment=' . $MyRow['shiptref'];

			$FormatedETA = ConvertSQLDate($MyRow['eta']);
			/* ShiptRef   Supplier  Vessel  Voyage  ETA */

			if ($MyRow['closed'] == 0) {

				$URL_Close_Shipment = $URL_View_Shipment . '&amp;Close=Yes';

				printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s">' . _('Costing') . '</a></td>
					<td><a href="%s">' . _('Modify') . '</a></td>
					<td><a href="%s"><b>' . _('Close') . '</b></a></td>
					</tr>', $MyRow['shiptref'], $MyRow['suppname'], $MyRow['vessel'], $MyRow['voyageref'], $FormatedETA, $URL_View_Shipment, $URL_Modify_Shipment, $URL_Close_Shipment);

			} else {
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td><a href="%s">' . _('Costing') . '</a></td>
						</tr>', $MyRow['shiptref'], $MyRow['suppname'], $MyRow['vessel'], $MyRow['voyage'], $FormatedETA, $URL_View_Shipment);
			}
		}
		//end of while loop

		echo '</tbody>';
		echo '</table>';
	} // end if shipments to show
}

echo '</form>';
include('includes/footer.php');
?>