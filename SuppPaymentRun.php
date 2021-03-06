<?php

class Allocation {
	var $TransID;
	var $Amount;

	function Allocation($TransID, $Amount) {
		$this->TransID = $TransID;
		$this->Amount = $Amount;
	}
}

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
include('includes/GetPaymentMethods.php');


if ((isset($_POST['PrintPDF']) or isset($_POST['PrintPDFAndProcess'])) and isset($_POST['FromCriteria']) and mb_strlen($_POST['FromCriteria']) >= 1 and isset($_POST['ToCriteria']) and mb_strlen($_POST['ToCriteria']) >= 1 and is_numeric(filter_number_format($_POST['ExRate']))) {

	/*then print the report */
	$Title = _('Payment Run - Problem Report');
	$RefCounter = 0;
	include('includes/PDFStarter.php');
	$PDF->addInfo('Title', _('Payment Run Report'));
	$PDF->addInfo('Subject', _('Payment Run') . ' - ' . _('suppliers from') . ' ' . $_POST['FromCriteria'] . ' to ' . $_POST['ToCriteria'] . ' in ' . $_POST['Currency'] . ' ' . _('and Due By') . ' ' . $_POST['AmountsDueBy']);

	$PageNumber = 1;
	$line_height = 12;

	/*Now figure out the invoice less credits due for the Supplier range under review */

	include('includes/PDFPaymentRunPageHeader.php');

	$SQL = "SELECT suppliers.supplierid,
					currencies.decimalplaces AS currdecimalplaces,
					SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
			FROM suppliers INNER JOIN paymentterms
			ON suppliers.paymentterms = paymentterms.termsindicator
			INNER JOIN supptrans
			ON suppliers.supplierid = supptrans.supplierno
			INNER JOIN systypes
			ON systypes.typeid = supptrans.type
			INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
			WHERE supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
			AND supptrans.duedate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "'
			AND supptrans.hold=0
			AND suppliers.currcode = '" . $_POST['Currency'] . "'
			AND supptrans.supplierNo >= '" . $_POST['FromCriteria'] . "'
			AND supptrans.supplierno <= '" . $_POST['ToCriteria'] . "'
			GROUP BY suppliers.supplierid,
					currencies.decimalplaces
			HAVING SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) > 0
			ORDER BY suppliers.supplierid";

	$SuppliersResult = DB_query($SQL);

	$SupplierID = '';
	$TotalPayments = 0;
	$TotalAccumDiffOnExch = 0;


	if (isset($_POST['PrintPDFAndProcess'])) {
		$ProcessResult = DB_Txn_Begin();
	}

	while ($SuppliersToPay = DB_fetch_array($SuppliersResult)) {

		$CurrDecimalPlaces = $SuppliersToPay['currdecimalplaces'];

		$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					systypes.typename,
					paymentterms.terms,
					supptrans.suppreference,
					supptrans.trandate,
					supptrans.rate,
					supptrans.transno,
					supptrans.type,
					(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
					(supptrans.ovamount + supptrans.ovgst ) AS trantotal,
					supptrans.diffonexch,
					supptrans.id
				FROM suppliers INNER JOIN paymentterms
				ON suppliers.paymentterms = paymentterms.termsindicator
				INNER JOIN supptrans
				ON suppliers.supplierid = supptrans.supplierno
				INNER JOIN systypes
				ON systypes.typeid = supptrans.type
				WHERE supptrans.supplierno = '" . $SuppliersToPay['supplierid'] . "'
				AND supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
				AND supptrans.duedate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "'
				AND supptrans.hold=0
				AND suppliers.currcode = '" . $_POST['Currency'] . "'
				AND supptrans.supplierno >= '" . $_POST['FromCriteria'] . "'
				AND supptrans.supplierno <= '" . $_POST['ToCriteria'] . "'
				ORDER BY supptrans.supplierno,
					supptrans.type,
					supptrans.transno";

		$TransResult = DB_query($SQL, '', '', false, false);
		if (DB_error_no() != 0) {
			$Title = _('Payment Run - Problem Report');
			include('includes/header.php');
			prnMsg(_('The details of supplier invoices due could not be retrieved because') . ' - ' . DB_error_msg(), 'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($Debug == 1) {
				echo '<br />' . _('The SQL that failed was') . ' ' . $SQL;
			}
			include('includes/footer.php');
			exit;
		}
		if (DB_num_rows($TransResult) == 0) {
			include('includes/header.php');
			prnMsg(_('There are no outstanding supplier invoices to pay'), 'info');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			include('includes/footer.php');
			exit;
		}

		unset($Allocs);
		$Allocs = array();
		$AllocCounter = 0;

		while ($DetailTrans = DB_fetch_array($TransResult)) {

			if ($DetailTrans['supplierid'] != $SupplierID) {
				/*Need to head up for a new suppliers details */

				if ($SupplierID != '') {
					/*only print the footer if this is not the first pass */
					include('includes/PDFPaymentRun_PymtFooter.php');
				}
				$SupplierID = $DetailTrans['supplierid'];
				$SupplierName = $DetailTrans['suppname'];
				if (isset($_POST['PrintPDFAndProcess'])) {
					$SuppPaymentNo = GetNextTransNo(22);
				}
				$AccumBalance = 0;
				$AccumDiffOnExch = 0;
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 450 - $Left_Margin, $FontSize, $DetailTrans['supplierid'] . ' - ' . $DetailTrans['suppname'] . ' - ' . $DetailTrans['terms'], 'left');

				$YPos -= $line_height;
			}

			$DislayTranDate = ConvertSQLDate($DetailTrans['trandate']);

			$LeftOvers = $PDF->addTextWrap($Left_Margin + 15, $YPos, 340 - $Left_Margin, $FontSize, $DislayTranDate . ' - ' . $DetailTrans['typename'] . ' - ' . $DetailTrans['suppreference'], 'left');

			/*Positive is a favourable */
			$DiffOnExch = ($DetailTrans['balance'] / $DetailTrans['rate']) - ($DetailTrans['balance'] / filter_number_format($_POST['ExRate']));

			$AccumBalance += $DetailTrans['balance'];
			$AccumDiffOnExch += $DiffOnExch;


			if (isset($_POST['PrintPDFAndProcess'])) {

				/*Record the Allocations for later insertion once we have the ID of the payment SuppTrans */

				$Allocs[$AllocCounter] = new Allocation($DetailTrans['id'], $DetailTrans['balance']);
				$AllocCounter++;

				/*Now update the SuppTrans for the allocation made and the fact that it is now settled */

				$SQL = "UPDATE supptrans SET settled = 1,
											alloc = '" . $DetailTrans['trantotal'] . "',
											diffonexch = '" . ($DetailTrans['diffonexch'] + $DiffOnExch) . "'
							WHERE type = '" . $DetailTrans['type'] . "'
							AND transno = '" . $DetailTrans['transno'] . "'";

				$ProcessResult = DB_query($SQL, '', '', false, false);
				if (DB_error_no() != 0) {
					$Title = _('Payment Processing - Problem Report') . '.... ';
					include('includes/header.php');
					prnMsg(_('None of the payments will be processed since updates to the transaction records for') . ' ' . $SupplierName . ' ' . _('could not be processed because') . ' - ' . DB_error_msg(), 'error');
					echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
					if ($Debug == 1) {
						echo '<br />' . _('The SQL that failed was') . $SQL;
					}
					$ProcessResult = DB_Txn_Rollback();
					include('includes/footer.php');
					exit;
				}
			}

			$LeftOvers = $PDF->addTextWrap(340, $YPos, 60, $FontSize, locale_number_format($DetailTrans['balance'], $CurrDecimalPlaces), 'right');
			$LeftOvers = $PDF->addTextWrap(405, $YPos, 60, $FontSize, locale_number_format($DiffOnExch, $_SESSION['CompanyRecord']['decimalplaces']), 'right');

			$YPos -= $line_height;
			if ($YPos < $Bottom_Margin + $line_height) {
				$PageNumber++;
				include('includes/PDFPaymentRunPageHeader.php');
			}
		}
		/*end while there are detail transactions to show */
	}
	/* end while there are suppliers to retrieve transactions for */

	if ($SupplierID != '') {
		/*All the payment processing is in the below file */
		include('includes/PDFPaymentRun_PymtFooter.php');

		$ProcessResult = DB_Txn_Commit();

		if (DB_error_no() != 0) {
			$Title = _('Payment Processing - Problem Report') . '.... ';
			include('includes/header.php');
			prnMsg(_('None of the payments will be processed. Unfortunately, there was a problem committing the changes to the database because') . ' - ' . DB_error_msg(), 'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($Debug == 1) {
				prnMsg(_('The SQL that failed was') . '<br />' . $SQL, 'error');
			}
			$ProcessResult = DB_Txn_Rollback();
			include('includes/footer.php');
			exit;
		}

		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 340 - $Left_Margin, $FontSize, _('Grand Total Payments Due'), 'left');
		$LeftOvers = $PDF->addTextWrap(340, $YPos, 60, $FontSize, locale_number_format($TotalPayments, $CurrDecimalPlaces), 'right');
		$LeftOvers = $PDF->addTextWrap(405, $YPos, 60, $FontSize, locale_number_format($TotalAccumDiffOnExch, $_SESSION['CompanyRecord']['decimalplaces']), 'right');

	}

	$PDF->OutputD($_SESSION['DatabaseName'] . '_Payment_Run_' . Date('Y-m-d_Hms') . '.pdf');
	$PDF->__destruct();

} else {
	/*The option to print PDF was not hit */

	$Title = _('Payment Run');
	include('includes/header.php');

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Supplier Types') . '" alt="" />' . $Title . '</p>';

	if (isset($_POST['Currency']) and !is_numeric(filter_number_format($_POST['ExRate']))) {
		echo '<br />' . _('To process payments for') . ' ' . $_POST['Currency'] . ' ' . _('a numeric exchange rate applicable for purchasing the currency to make the payment with must be entered') . '. ' . _('This rate is used to calculate the difference in exchange and make the necessary postings to the General ledger if linked') . '.';
	}

	/* show form to allow input	*/
	$SQL = "SELECT min(supplierid) AS fromcriteria,
					max(supplierid) AS tocriteria
				FROM suppliers";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';

	if (!isset($_POST['FromCriteria']) or mb_strlen($_POST['FromCriteria']) < 1) {
		$DefaultFromCriteria = $MyRow['fromcriteria'];
	} else {
		$DefaultFromCriteria = $_POST['FromCriteria'];
	}
	if (!isset($_POST['ToCriteria']) or mb_strlen($_POST['ToCriteria']) < 1) {
		$DefaultToCriteria = $MyRow['tocriteria'];
	} else {
		$DefaultToCriteria = $_POST['ToCriteria'];
	}
	echo '<tr>
			<td>' . _('From Supplier Code') . ':</td>
			<td><input type="text" required="required" maxlength="6" size="7" name="FromCriteria" value="' . $DefaultFromCriteria . '" /></td>
		  </tr>';
	echo '<tr>
			<td>' . _('To Supplier Code') . ':</td>
			<td><input type="text" required="required" maxlength="6" size="7" name="ToCriteria" value="' . $DefaultToCriteria . '" /></td>
		 </tr>';


	echo '<tr>
			<td>' . _('For Suppliers Trading in') . ':</td>
			<td><select required="required" name="Currency">';

	$SQL = "SELECT currency, currabrev FROM currencies";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['currabrev'] == $_SESSION['CompanyRecord']['currencydefault']) {
			echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	if (!isset($_POST['ExRate']) or !is_numeric(filter_number_format($_POST['ExRate']))) {
		$DefaultExRate = '1';
	} else {
		$DefaultExRate = filter_number_format($_POST['ExRate']);
	}
	echo '<tr>
			<td>' . _('Exchange Rate') . ':</td>
			<td><input type="text" class="number" name="ExRate" required="required" maxlength="11" size="12" value="' . locale_number_format($DefaultExRate, 'Variable') . '" /></td>
		  </tr>';

	if (!isset($_POST['AmountsDueBy'])) {
		$DefaultDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') + 1, 0, Date('y')));
	} else {
		$DefaultDate = $_POST['AmountsDueBy'];
	}

	echo '<tr>
			<td>' . _('Payments Due To') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="AmountsDueBy" required="required" maxlength="11" size="12" value="' . $DefaultDate . '" /></td>
		  </tr>';

	$SQL = "SELECT bankaccountname, accountcode FROM bankaccounts";

	$AccountsResults = DB_query($SQL, '', '', false, false);

	if (DB_error_no() != 0) {
		echo '<br />' . _('The bank accounts could not be retrieved by the SQL because') . ' - ' . DB_error_msg();
		if ($Debug == 1) {
			echo '<br />' . _('The SQL used to retrieve the bank accounts was') . ':<br />' . $SQL;
		}
		exit;
	}

	echo '<tr>
			<td>' . _('Pay From Account') . ':</td>
			<td><select required="required" name="BankAccount">';

	if (DB_num_rows($AccountsResults) == 0) {
		echo '</select></td>
			</tr>
			</table>
			<p>' . _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected') . '.
			</p>';
		include('includes/footer.php');
		exit;
	} else {
		while ($MyRow = DB_fetch_array($AccountsResults)) {
			/*list the bank account names */

			if (isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']) {
				echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . '</option>';
			}
		}
		echo '</select></td>
				</tr>';
	}

	echo '<tr>
			<td>' . _('Payment Type') . ':</td>
			<td><select required="required" name="PaytType">';

	/* The array PaytTypes is set up in config.php for user modification
	Payment types can be modified by editing that file */

	foreach ($PaytTypes as $PaytID => $PaytType) {

		if (isset($_POST['PaytType']) and $_POST['PaytType'] == $PaytType) {
			echo '<option selected="selected" value="' . $PaytType . '">' . $PaytType . '</option>';
		} else {
			echo '<option value="' . $PaytType . '">' . $PaytType . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	echo '</table>
			<br />
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print PDF Only') . '" />
				<input type="submit" name="PrintPDFAndProcess" value="' . _('Print and Process Payments') . '" />
			</div>';
	echo '</form>';
	include('includes/footer.php');
}
/*end of else not PrintPDF */
?>