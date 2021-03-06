<?php

/*PDF page header for inventory valuation report */

$PageNumber++;
/* first time round $PageNumber will only be 1 and page created on initiation of $PDF */
if ($PageNumber > 1) {
	$PDF->newPage();
}

$FontSize = 8;
$YPos = $Page_Height - $Top_Margin;


$PDF->addText($Left_Margin, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);

$YPos -= $line_height;

$FontSize = 10;

$PDF->addText($Left_Margin, $YPos, $FontSize, _('Bill Of Material Listing for Parts Between') . ' ' . $_POST['FromCriteria'] . ' ' . _('and') . ' ' . $_POST['ToCriteria']);



$FontSize = 8;
$PDF->addText($Page_Width - $Right_Margin - 120, $YPos, $FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);

$YPos -= (2 * $line_height);

/*Draw a rectangle to put the headings in     */
$PDF->line($Page_Width - $Right_Margin, $YPos - 5, $Left_Margin, $YPos - 5);
$PDF->line($Page_Width - $Right_Margin, $YPos + $line_height, $Left_Margin, $YPos + $line_height);
$PDF->line($Page_Width - $Right_Margin, $YPos + $line_height, $Page_Width - $Right_Margin, $YPos - 5);
$PDF->line($Left_Margin, $YPos + $line_height, $Left_Margin, $YPos - 5);

/*set up the headings */
$Xpos = $Left_Margin + 1;

$LeftOvers = $PDF->addTextWrap($Xpos, $YPos, 320 - $Left_Margin, $FontSize, _('Component Part/Description'), 'centre');
$LeftOvers = $PDF->addTextWrap(300, $YPos, 60, $FontSize, _('Effective After'), 'centre');
$LeftOvers = $PDF->addTextWrap(348, $YPos, 60, $FontSize, _('Effective To'), 'centre');
$LeftOvers = $PDF->addTextWrap(403, $YPos, 40, $FontSize, _('Locn'), 'centre');
$LeftOvers = $PDF->addTextWrap(435, $YPos, 40, $FontSize, _('Wrk Cntr'), 'centre');
$LeftOvers = $PDF->addTextWrap(500, $YPos, 60, $FontSize, _('Quantity'), 'centre');

$YPos = $YPos - (2 * $line_height);

$FontSize = 10;
?>