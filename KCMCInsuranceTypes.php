<?php

include('includes/session.php');
$Title = _('Insurance Types') . ' / ' . _('Maintenance');
include('includes/header.php');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text" ><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Insurance Types'), '" alt="" />', _('Insurance Type Setup'), '</p>';
echo '<div class="page_help_text">', _('Add/edit/delete Insurance Types'), '</div>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['TypeName']) > 100) {
		$InputError = 1;
		prnMsg(_('The insurance type name description must be 100 characters or less long'), 'error');
	}

	if (mb_strlen($_POST['TypeName']) == 0) {
		$InputError = 1;
		prnMsg(_('The insurnace type name description must contain at least one character'), 'error');
	}

	$CheckSql = "SELECT count(*)
			 FROM insurancetypes
			 WHERE typename = '" . $_POST['TypeName'] . "'";
	$CheckResult = DB_query($CheckSql);
	$CheckRow = DB_fetch_row($CheckResult);
	if ($CheckRow[0] > 0 and !isset($SelectedType)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('You already have an insurance type called') . ' ' . $_POST['TypeName'], 'error');
	}

	if (isset($SelectedType) and $InputError != 1) {

		$SQL = "UPDATE insurancetypes
			SET typename = '" . $_POST['TypeName'] . "'
			WHERE typeid = '" . $SelectedType . "'";

		$Msg = _('The insurance type') . ' ' . $SelectedType . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		// Add new record on submit

		$SQL = "INSERT INTO insurancetypes VALUES (NULL, '" . $_POST['TypeName'] . "')";
		$Msg = _('Customer type') . ' ' . $_POST["TypeName"] . ' ' . _('has been created');

	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');

		unset($SelectedType);
		unset($_POST['typeid']);
		unset($_POST['TypeName']);
	}

} elseif (isset($_GET['delete'])) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	// Prevent delete if saletype exist in customer transactions

	$SQL = "SELECT COUNT(*)
			FROM insuranceco
			WHERE insurancetype='" . $SelectedType . "'";

	$ErrMsg = _('The number of insurance companies using this type could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this type because insurance companies have been created using this type'), 'error');
	} else {

		$SQL = "DELETE FROM insurancetypes WHERE typeid='" . $SelectedType . "'";
		$ErrMsg = _('The Type record could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);
		echo '<br />';
		prnMsg( _('The insurance type has been deleted'), 'success');

		unset($SelectedType);
		unset($_GET['delete']);

	}
}

if (!isset($SelectedType)) {

	$SQL = "SELECT typeid,
					typename
				FROM insurancetypes";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">', _('Type ID'), '</th>
						<th class="SortedColumn">', _('Type Name'), '</th>
					</tr>
				</thead>';

		$k = 0; //row colour counter
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			echo '<td>', $MyRow['typeid'], '</td>
					<td>', $MyRow['typename'], '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['typeid']), '">' . _('Edit') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['typeid']), '&amp;delete=yes" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this Customer Type?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
				</tr>';
		}
		//END WHILE LIST LOOP
		echo '</tbody>';
		echo '</table>';
	}
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	// The user wish to EDIT an existing type
	if (isset($SelectedType) and $SelectedType != '') {

		$SQL = "SELECT typeid,
				   typename
				FROM insurancetypes
				WHERE typeid='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['TypeName'] = $MyRow['typename'];

		echo '<input type="hidden" name="SelectedType" value="', $SelectedType, '" />';
		echo '<table class="selection">';

		echo '<tr>
				<td>', _('Type ID'), ': </td>
				<td>', $SelectedType, '</td>
			</tr>';

	} else {
		echo '<table class="selection">';
	}

	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName'] = '';
	}
	echo '<tr>
			<td>', _('Type Name'), ':</td>
			<td><input type="text" name="TypeName" required="required" maxlength="100" value="', $_POST['TypeName'], '" /></td>
		</tr>';

	echo '</table>'; // close main table

	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Accept'), '" />
		</div>';
	echo '</form>';

} // end if user wish to delete

include('includes/footer.php');
?>