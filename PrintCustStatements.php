<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
include('includes/htmlMimeMail.php');

$ViewTopic = 'ARReports';
$BookMark = 'CustomerStatements';

$Title = _('Print Customer Statements');

// if this file is called from another script, we set the required POST variables from the GET
// We call this file from SelectCustomer.php when a customer is selected and we want a statement printed

if (isset($_POST['PrintPDF'])) {
	$PaperSize = 'A4_Landscape';
}

if (isset($_GET['PrintPDF'])) {
	$FromCust = $_GET['FromCust'];
	$ToCust = $_GET['ToCust'];
	$PrintPDF = $_GET['PrintPDF'];
	$_POST['FromCust'] = $FromCust;
	$_POST['ToCust'] = $ToCust;
	$_POST['PrintPDF'] = $PrintPDF;
	$PaperSize = 'A4_Landscape';
}

if (isset($_GET['FromCust'])) {
	$_POST['FromCust'] = $_GET['FromCust'];
}

if (isset($_GET['ToCust'])) {
	$_POST['ToCust'] = $_GET['ToCust'];
}

if (isset($_GET['EmailOrPrint'])) {
	$_POST['EmailOrPrint'] = $_GET['EmailOrPrint'];
}

if (isset($_POST['PrintPDF']) and isset($_POST['FromCust']) and $_POST['FromCust'] != '') {
	$_POST['FromCust'] = mb_strtoupper($_POST['FromCust']);

	if (!isset($_POST['ToCust'])) {
		$_POST['ToCust'] = $_POST['FromCust'];
	} else {
		$_POST['ToCust'] = mb_strtoupper($_POST['ToCust']);
	}

	/* Do a quick tidy up to settle any transactions that should have been settled at the time of allocation but for whatever reason weren't */
	$ErrMsg = _('There was a problem settling the old transactions.');
	$DbgMsg = _('The SQL used to settle outstanding transactions was');
	$SQL = "UPDATE debtortrans SET settled=1
			WHERE ABS(debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc)<0.009";
	$SettleAsNec = DB_query($SQL, $ErrMsg, $DbgMsg);

	/*Figure out who all the customers in this range are */
	$ErrMsg = _('There was a problem retrieving the customer information for the statements from the database');
	$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtorsmaster.address6,
				debtorsmaster.lastpaid,
				debtorsmaster.lastpaiddate,
				currencies.currency,
				currencies.decimalplaces AS currdecimalplaces,
				paymentterms.terms
			FROM debtorsmaster INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
			INNER JOIN paymentterms
				ON debtorsmaster.paymentterms=paymentterms.termsindicator
			WHERE debtorsmaster.debtorno >='" . stripslashes($_POST['FromCust']) . "'
			AND debtorsmaster.debtorno <='" . stripslashes($_POST['ToCust']) . "'
			ORDER BY debtorsmaster.debtorno";
	$StatementResults = DB_query($SQL, $ErrMsg);

	if (DB_Num_Rows($StatementResults) == 0) {
		$Title = _('Print Statements') . ' - ' . _('No Customers Found');
		require('includes/header.php');
		echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . _('Print Customer Account Statements') . '</p>';
		prnMsg(_('There were no Customers matching your selection of ') . $_POST['FromCust'] . ' - ' . $_POST['ToCust'] . '.', 'error');
		include('includes/footer.php');
		exit();
	}
	//Start the statement if there are any in the range and we are printing the whole lot
	if ($_POST['EmailOrPrint'] == 'print') {
		include('includes/PDFStarter.php');
		$PDF->addInfo('Title', _('Customer Statements'));
		$PDF->addInfo('Subject', _('Statements from') . ' ' . $_POST['FromCust'] . ' ' . _('to') . ' ' . $_POST['ToCust']);
		$PageNumber = 1;
	} else {
		$Title = _('Email Customer Statements');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/email.png" title="' . _('Email') . '" alt="" />' . ' ' . _('Emailing Customer Account Statements') . '</p>';

		echo '<table class="selection">
				<thead>
					<tr>
						<th class="text">', _('Account #'), '</th>
						<th class="text">', _('Customer Name'), '</th>
						<th class="text">', _('Recipients'), '</th>
					</tr>
				</thead>';
	}
	$FirstStatement = true;
	// check if the user has set a default bank account for invoices, if not leave it blank
	$SQL = "SELECT bankaccounts.invoice,
					bankaccounts.bankaccountnumber,
					bankaccounts.bankaccountcode
				FROM bankaccounts
				WHERE bankaccounts.invoice = '1'";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() != 1) {
		if (DB_num_rows($Result) == 1) {
			$MyRow = DB_fetch_array($Result);
			$DefaultBankAccountNumber = $MyRow['bankaccountnumber'];
		} else {
			$DefaultBankAccountNumber = '';
		}
	} else {
		$DefaultBankAccountNumber = '';
	}

	while ($StmtHeader = DB_fetch_array($StatementResults)) {

		if (isset($RecipientArray)) {
			unset($RecipientArray);
		}
		$RecipientArray = array();
		$RecipientsResult = DB_query("SELECT email FROM custcontacts WHERE statement=1 AND debtorno='" . $StmtHeader['debtorno'] . "'");
		while ($RecipientRow = DB_fetch_row($RecipientsResult)) {
			if (IsEmailAddress($RecipientRow[0])){
				$RecipientArray[] = $RecipientRow[0];
			}
		}

		if ($_POST['EmailOrPrint'] == 'email') {
			include('includes/PDFStarter.php');
			$PDF->addInfo('Title', $_SESSION['CompanyRecord']['coyname'] . ' - ' . _('Customer Statement'));
			$PDF->addInfo('Subject', _('For customer') . ': ' . $StmtHeader['name']);
			$PageNumber = 1;
		}

		/* Only create the pdf for this customer if
		 * set to print and there are no email addresses defined
		 * OR
		 * set to email and there are email addresses defined.
		 */
		if (($_POST['EmailOrPrint']=='print' and count($RecipientArray)==0) or ($_POST['EmailOrPrint'] == 'email' and count($RecipientArray) > 0)) {

			$line_height = 16;

			/*now get all the outstanding transaction ie Settled=0 */
			$ErrMsg = _('There was a problem retrieving the outstanding transactions for') . ' ' . $StmtHeader['name'] . ' ' . _('from the database') . '.';
			$SQL = "SELECT systypes.typename,
						debtortrans.transno,
						debtortrans.trandate,
						debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst as total,
						debtortrans.alloc,
						debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc as ostdg
					FROM debtortrans INNER JOIN systypes
						ON debtortrans.type=systypes.typeid
					WHERE debtortrans.debtorno='" . $StmtHeader['debtorno'] . "'
					AND debtortrans.settled=0";

			if ($_SESSION['SalesmanLogin'] != '') {
				$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
			}

			$SQL .= " ORDER BY debtortrans.id";

			$OstdgTrans = DB_query($SQL, $ErrMsg);

			$NumberOfRecordsReturned = DB_num_rows($OstdgTrans);

			/*now get all the settled transactions which were allocated this month */
			$ErrMsg = _('There was a problem retrieving the transactions that were settled over the course of the last month for') . ' ' . $StmtHeader['name'] . ' ' . _('from the database');
			if ($_SESSION['Show_Settled_LastMonth'] == 1) {
				$SQL = "SELECT DISTINCT debtortrans.id,
									systypes.typename,
									debtortrans.transno,
									debtortrans.trandate,
									debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst AS total,
									debtortrans.alloc,
									debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc AS ostdg
							FROM debtortrans INNER JOIN systypes
								ON debtortrans.type=systypes.typeid
							INNER JOIN custallocns
								ON (debtortrans.id=custallocns.transid_allocfrom
									OR debtortrans.id=custallocns.transid_allocto)
							WHERE custallocns.datealloc>=ADDDATE(CURRENT_DATE, INTERVAL -1 MONTH)
							AND debtortrans.debtorno='" . $StmtHeader['debtorno'] . "'
							AND debtortrans.settled=1";

				if ($_SESSION['SalesmanLogin'] != '') {
					$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
				}

				$SQL .= " ORDER BY debtortrans.id";

				$SetldTrans = DB_query($SQL, $ErrMsg);
				$NumberOfRecordsReturned += DB_num_rows($SetldTrans);

			}

			if ($NumberOfRecordsReturned >= 1) {

				/* Then there's a statement to print. So print out the statement header from the company record */

				$PageNumber = 1;

				if ($FirstStatement == True) {
					$FirstStatement = False;
				} else {
					$PDF->newPage();
				}

				include('includes/PDFStatementPageHeader.php');

				$Cust_Name = $StmtHeader['name'];
				$Cust_No = $StmtHeader['debtorno'];

				if ($_SESSION['Show_Settled_LastMonth'] == 1) {
					if (DB_num_rows($SetldTrans) >= 1) {

						$FontSize = 12;
						$PDF->addText($Left_Margin + 1, $YPos + 5, $FontSize, _('Settled Transactions'));

						$YPos -= (2 * $line_height);

						$FontSize = 10;

						while ($MyRow = DB_fetch_array($SetldTrans)) {

							$DisplayAlloc = locale_number_format($MyRow['alloc'], $StmtHeader['currdecimalplaces']);
							$DisplayOutstanding = locale_number_format($MyRow['ostdg'], $StmtHeader['currdecimalplaces']);

							$FontSize = 9;

							$LeftOvers = $PDF->addTextWrap($Left_Margin + 1, $YPos, 60, $FontSize, _($MyRow['typename']), 'left');
							$LeftOvers = $PDF->addTextWrap($Left_Margin + 110, $YPos, 50, $FontSize, $MyRow['transno'], 'left');
							$LeftOvers = $PDF->addTextWrap($Left_Margin + 211, $YPos, 55, $FontSize, ConvertSQLDate($MyRow['trandate']), 'left');

							$FontSize = 10;
							if ($MyRow['total'] > 0) {
								$DisplayTotal = locale_number_format($MyRow['total'], $StmtHeader['currdecimalplaces']);
								$LeftOvers = $PDF->addTextWrap($Left_Margin + 300, $YPos, 60, $FontSize, $DisplayTotal, 'right');
							} else {
								$DisplayTotal = locale_number_format(-$MyRow['total'], $StmtHeader['currdecimalplaces']);
								$LeftOvers = $PDF->addTextWrap($Left_Margin + 382, $YPos, 60, $FontSize, $DisplayTotal, 'right');
							}
							$LeftOvers = $PDF->addTextWrap($Left_Margin + 459, $YPos, 60, $FontSize, $DisplayAlloc, 'right');
							$LeftOvers = $PDF->addTextWrap($Left_Margin + 536, $YPos, 60, $FontSize, $DisplayOutstanding, 'right');

							if (($YPos - $line_height) <= $Bottom_Margin) {
								/* head up a new statement page */

								++$PageNumber;
								$PDF->newPage();
								include('includes/PDFStatementPageHeader.php');
							} //end if need a new page headed up

							/*increment a line down for the next line item */
							$YPos -= ($line_height);

						} //end while there transactions settled this month to print out
					}
				} // end of if there are transaction that were settled this month

				if (DB_num_rows($OstdgTrans) >= 1) {

					$YPos -= ($line_height);
					if ($YPos - (2 * $line_height) <= $Bottom_Margin) {
						$PageNumber++;
						$PDF->newPage();
						include('includes/PDFStatementPageHeader.php');
					}
					/*Now the same again for outstanding transactions */

					$FontSize = 12;
					$PDF->addText($Left_Margin + 1, $YPos + 20, $FontSize, _('Outstanding Transactions'));
					$YPos -= $line_height;

					while ($MyRow = DB_fetch_array($OstdgTrans)) {

						$DisplayAlloc = locale_number_format($MyRow['alloc'], $StmtHeader['currdecimalplaces']);
						$DisplayOutstanding = locale_number_format($MyRow['ostdg'], $StmtHeader['currdecimalplaces']);

						$FontSize = 9;
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 1, $YPos, 60, $FontSize, _($MyRow['typename']), 'left');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 110, $YPos, 50, $FontSize, $MyRow['transno'], 'left');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 211, $YPos, 55, $FontSize, ConvertSQLDate($MyRow['trandate']), 'left');

						$FontSize = 10;
						if ($MyRow['total'] > 0) {
							$DisplayTotal = locale_number_format($MyRow['total'], $StmtHeader['currdecimalplaces']);
							$LeftOvers = $PDF->addTextWrap($Left_Margin + 300, $YPos, 55, $FontSize, $DisplayTotal, 'right');
						} else {
							$DisplayTotal = locale_number_format(-$MyRow['total'], $StmtHeader['currdecimalplaces']);
							$LeftOvers = $PDF->addTextWrap($Left_Margin + 382, $YPos, 55, $FontSize, $DisplayTotal, 'right');
						}

						$LeftOvers = $PDF->addTextWrap($Left_Margin + 459, $YPos, 59, $FontSize, $DisplayAlloc, 'right');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 536, $YPos, 60, $FontSize, $DisplayOutstanding, 'right');

						/*Now show also in the remittance advice sectin */
						$FontSize = 8;
						$LeftOvers = $PDF->addTextWrap($Perforation + 10, $YPos, 30, $FontSize, _($MyRow['typename']), 'left');
						$LeftOvers = $PDF->addTextWrap($Perforation + 75, $YPos, 30, $FontSize, $MyRow['transno'], 'left');
						$LeftOvers = $PDF->addTextWrap($Perforation + 90, $YPos, 60, $FontSize, $DisplayOutstanding, 'right');

						if ($YPos - $line_height <= $Bottom_Margin) {
							/* head up a new statement page */

							++$PageNumber;
							$PDF->newPage();
							include('includes/PDFStatementPageHeader.php');
						} //end if need a new page headed up

						/*increment a line down for the next line item */
						$YPos -= ($line_height);

					} //end while there are outstanding transaction to print
				} // end if there are outstanding transaction to print


				/* check to see enough space left to print the totals/footer
				which is made up of 2 ruled lines, the totals/aging another 2 lines
				and details of the last payment made - in all 6 lines */
				if (($YPos - $Bottom_Margin) < (4 * $line_height)) {

					/* head up a new statement page */
					++$PageNumber;
					$PDF->newPage();
					$PDF->newPage();
					include('includes/PDFStatementPageHeader.php');
				}
				/*Now figure out the aged analysis for the customer under review */

				$SQL = "SELECT debtorsmaster.name,
							currencies.currency,
							paymentterms.terms,
							debtorsmaster.creditlimit,
							holdreasons.dissallowinvoices,
							holdreasons.reasondescription,
							SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
							debtortrans.ovdiscount - debtortrans.alloc) AS balance,
							SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >=
								paymentterms.daysbeforedue
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
								debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
							ELSE
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate), paymentterms.dayinfollowingmonth)) >= 0
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
								debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
							END) AS due,
							Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
								AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >=
								(paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
								debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
							ELSE
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate), paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
								debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
							END) AS overdue1,
							Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
								CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
								AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue +
								" . $_SESSION['PastDueDays2'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
								debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
							ELSE
								CASE WHEN (TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate), paymentterms.dayinfollowingmonth))
								>= " . $_SESSION['PastDueDays2'] . ")
								THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
								debtortrans.ovdiscount - debtortrans.alloc
								ELSE 0 END
							END) AS overdue2
						FROM debtorsmaster INNER JOIN paymentterms
							ON debtorsmaster.paymentterms = paymentterms.termsindicator
						INNER JOIN currencies
							ON debtorsmaster.currcode = currencies.currabrev
						INNER JOIN holdreasons
							ON debtorsmaster.holdreason = holdreasons.reasoncode
						INNER JOIN debtortrans
							ON debtorsmaster.debtorno = debtortrans.debtorno
						WHERE
							debtorsmaster.debtorno = '" . $StmtHeader['debtorno'] . "'";

				if ($_SESSION['SalesmanLogin'] != '') {
					$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
				}

				$SQL .= " GROUP BY
							debtorsmaster.name,
							currencies.currency,
							paymentterms.terms,
							paymentterms.daysbeforedue,
							paymentterms.dayinfollowingmonth,
							debtorsmaster.creditlimit,
							holdreasons.dissallowinvoices,
							holdreasons.reasondescription";

				$ErrMsg = 'The customer details could not be retrieved by the SQL because';
				$CustomerResult = DB_query($SQL);

				/*there should be only one record returned ?? */
				$AgedAnalysis = DB_fetch_array($CustomerResult, $db);


				/*Now print out the footer and totals */

				$DisplayDue = locale_number_format($AgedAnalysis['due'] - $AgedAnalysis['overdue1'], $StmtHeader['currdecimalplaces']);
				$DisplayCurrent = locale_number_format($AgedAnalysis['balance'] - $AgedAnalysis['due'], $StmtHeader['currdecimalplaces']);
				$DisplayBalance = locale_number_format($AgedAnalysis['balance'], $StmtHeader['currdecimalplaces']);
				$DisplayOverdue1 = locale_number_format($AgedAnalysis['overdue1'] - $AgedAnalysis['overdue2'], $StmtHeader['currdecimalplaces']);
				$DisplayOverdue2 = locale_number_format($AgedAnalysis['overdue2'], $StmtHeader['currdecimalplaces']);


				$PDF->line($Page_Width - $Right_Margin, $Bottom_Margin + (4 * $line_height), $Left_Margin, $Bottom_Margin + (4 * $line_height));

				$FontSize = 10;


				$PDF->addText($Left_Margin + 75, ($Bottom_Margin + 10) + (3 * $line_height) + 4, $FontSize, _('Current') . ' ');
				$PDF->addText($Left_Margin + 158, ($Bottom_Margin + 10) + (3 * $line_height) + 4, $FontSize, _('Past Due') . ' ');
				$PDF->addText($Left_Margin + 242, ($Bottom_Margin + 10) + (3 * $line_height) + 4, $FontSize, $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . _('days'));
				$PDF->addText($Left_Margin + 315, ($Bottom_Margin + 10) + (3 * $line_height) + 4, $FontSize, _('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('days'));
				$PDF->addText($Left_Margin + 442, ($Bottom_Margin + 10) + (3 * $line_height) + 4, $FontSize, _('Total Balance'));

				$LeftOvers = $PDF->addTextWrap($Left_Margin + 37, $Bottom_Margin + (2 * $line_height) + 8, 70, $FontSize, $DisplayCurrent, 'right');
				$LeftOvers = $PDF->addTextWrap($Left_Margin + 130, $Bottom_Margin + (2 * $line_height) + 8, 70, $FontSize, $DisplayDue, 'right');
				$LeftOvers = $PDF->addTextWrap($Left_Margin + 222, $Bottom_Margin + (2 * $line_height) + 8, 70, $FontSize, $DisplayOverdue1, 'right');

				$LeftOvers = $PDF->addTextWrap($Left_Margin + 305, $Bottom_Margin + (2 * $line_height) + 8, 70, $FontSize, $DisplayOverdue2, 'right');

				$LeftOvers = $PDF->addTextWrap($Left_Margin + 432, $Bottom_Margin + (2 * $line_height) + 8, 70, $FontSize, $DisplayBalance, 'right');


				/*draw a line under the balance info */
				$YPos = $Bottom_Margin + (2 * $line_height);
				$PDF->line($Left_Margin, $YPos, $Perforation, $YPos);


				if (mb_strlen($StmtHeader['lastpaiddate']) > 1 AND $StmtHeader['lastpaid'] != 0) {
					$PDF->addText($Left_Margin + 5, $Bottom_Margin + 13, $FontSize, _('Last payment received') . ' ' . ConvertSQLDate($StmtHeader['lastpaiddate']) . '    ' . _('Amount received was') . ' ' . locale_number_format($StmtHeader['lastpaid'], $StmtHeader['currdecimalplaces']));

				}

				/* Show the bank account details */
				$PDF->addText($Perforation - 250, $Bottom_Margin + 32, $FontSize, _('Please make payments to our account:') . ' ' . $DefaultBankAccountNumber);
				$PDF->addText($Perforation - 250, $Bottom_Margin + 32 - $line_height, $FontSize, _('Quoting your account reference') . ' ' . $StmtHeader['debtorno']);

				/*also show the total due in the remittance section */
				if ($AgedAnalysis['balance'] > 0) {
					/*No point showing a negative balance for payment! */
					$FontSize = 8;
					$LeftOvers = $PDF->addTextWrap($Perforation + 2, $Bottom_Margin + (2 * $line_height) + 8, 40, $FontSize, _('Payment'), 'left');
					$LeftOvers = $PDF->addTextWrap($Page_Width - $Right_Margin - 90, $Bottom_Margin + (2 * $line_height) + 8, 88, $FontSize, $DisplayBalance, 'right');

				}

			}
			/* end of check to see that there were statement transactons to print */
			if ($_POST['EmailOrPrint'] == 'email') {
				$FileName = 'Statement_Account_' . $StmtHeader['debtorno'] . '.pdf';
				$PDF->Output($FileName, 'F');
				$Mail = new htmlMimeMail();
				$Attachment = $Mail->getFile($FileName);
				$Mail->setText(_('Please find a statement or your account attached'));
				$Mail->SetSubject($_SESSION['CompanyRecord']['coyname'] . ' ' . _('Customer Account Statement'));
				$Mail->addAttachment($Attachment, $FileName, 'application/pdf');
				if ($_SESSION['SmtpSetting'] == 0) {
					$Mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
					$Result = $Mail->send($RecipientArray);
				} else {
					$Result = SendmailBySmtp($Mail, array(
						$RecipientArray
					));
				}
				echo '<tr>
						<td>', $StmtHeader['debtorno'], '</td>
						<td>', $StmtHeader['name'], '</td>
						<td>';
				$i = 0;
				foreach ($RecipientArray as $Recipient) {
					if ($i > 0) {
						echo '<br />';
					}
					echo $Recipient;
					$i++;
				}
				echo '</td></tr>';
				unlink($FileName); //delete the temporary file
				$PDF->__destruct();
			}

		}
		/* end if we are printing or if we are emailing and there are recipients to email */

	}
	/* end loop to print statements */

	if (isset($PDF) and $_POST['EmailOrPrint'] == 'print') {
		$PDF->OutputD($_SESSION['DatabaseName'] . '_CustStatements_' . date('Y-m-d') . '.pdf');
		$PDF->__destruct();
	} elseif (!isset($PDF)) {
		$Title = _('Print Statements') . ' - ' . _('No Statements Found');
		if ($_POST['EmailOrPrint'] == 'print') {
			include('includes/header.php');
			echo '<br />
				<br />
				<br />' . prnMsg( _('There were no statements to print'), 'warn');
		} else {
			echo '<br />
				<br />
				<br />' . prnMsg( _('There were no statements to email'), 'warn');
		}
		echo'<br />
				<br />
				<br />';
		include('includes/footer.php');
	}

} else {
	/*The option to print PDF was not hit */

	$Title = _('Select Statements to Print');
	/* Manual links before header.php */
	$ViewTopic = 'ARReports';
	$BookMark = 'CustomerStatements';
	include('includes/header.php');
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . _('Print Customer Account Statements') . '</p>';
	if (!isset($_POST['FromCust']) or $_POST['FromCust'] == '') {

		/*if FromTransNo is not set then show a form to allow input of either a single statement number or a range of statements to be printed. Also get the last statement number created to show the user where the current range is up to */

		$SQL = "SELECT min(debtorno) AS fromcriteria,
						max(debtorno) AS tocriteria
					FROM debtorsmaster";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<table class="selection">';
		echo '<tr>
				<td>', _('Starting Customer statement to print (Customer code)'), '</td>
				<td><input type="text" maxlength="10" size="8" name="FromCust" value="0" /></td></tr>
			<tr>
				<td>', _('Ending Customer statement to print (Customer code)'), '</td>
				<td><input type="text" maxlength="10" size="8" name="ToCust" value="zzzzzz" /></td>
			</tr>
			<tr>
				<td>', _('Print Or Email to flagged customer contacts'), '</td>
				<td><select name="EmailOrPrint">
						<option selected="selected" value="print">', _('Print'), '</option>
						<option value="email">', _('Email to flagged customer contacts'), '</option>
					</select>
				</td>
			</tr>
		</table>';
		echo '<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print (or Email) All Statements in the Range Selected') . '" />
			</div>';
		echo '</form>';
	}
	include('includes/footer.php');

}
/*end of else not PrintPDF */

?>