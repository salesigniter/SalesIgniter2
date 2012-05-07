<?php
$Qcustomers = Doctrine_Manager::getInstance()
	->getCurrentConnection()
	->fetchAssoc("SELECT p.products_id,r.date_added,r.shipment_date,r.return_date, p.products_name, r.date_added, r.products_barcode FROM rented_products r, products_description p where p.products_id = r.products_id and p.language_id='" . Session::get('languages_id') . "' and r.return_date = '0000-00-00 00:00:00' and customers_id =" . $cID);
$templateParsed = array();
if (sizeof($Qcustomers) < 1){
	$templateParsed[] = '<tr>
       <td colspan="5" class="messageStackError">' . sysLanguage::get('TEXT_INFO_NO_RENTED_QUEUE') . '</td>
      </tr>';
}
else {
	$template = '<tr class="dataTableRow">
       <td class="smallText" align="left">%s</td>
       <td class="smallText" align="left">%s</td>
       <td class="smallText" align="left">%s</td>
       <td class="smallText" align="left">%s</td>
       <td class="smallText" align="left">%s</td>
      </tr>';
	foreach($Qcustomers as $customers){
		$templateParsed[] = sprintf($template,
			$customers['products_id'],
			$customers['products_barcode'],
			'<a href="' . tep_href_link(FILENAME_RENT_INVENTORY_COMMENTS, 'pID=' . $customers['products_id']) . '">' . $customers['products_name'] . '</a>',
			date('Y-m-d', strtotime($customers['date_added'])),
			date('Y-m-d', strtotime($customers['shipment_date']))
		);
	}
}
?>
<div>
	<a class="linkAreaTitle" href="<?php echo itw_app_link('cID=' . $cID, 'rental_queue', 'details');?>"><?php echo sysLanguage::get('TEXT_VIEW_RENTAL_QUEUE');?></a>
</div>
<br />

<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr class="dataTableHeadingRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
		<td class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_ID'); ?></td>
		<td class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_BARCODE'); ?></td>
		<td class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_MOVIE_TITLE'); ?></td>
		<td class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_ADDED_TO_QUEUE');?></td>
		<td class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_SHIPPED');?></td>
	</tr>
	<?php echo implode("\n", $templateParsed);?>
</table>