<?php
$cID = $_GET['cID'];
$Customer = Doctrine_Core::getTable('Customers')->find((int) $cID);
$Membership = $Customer->CustomersMembership;
$Plan = $Membership->Membership;

$tableGrid = htmlBase::newElement('newGrid');

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('print')->addClass('printLabelsButton')->setText('Print Labels'),
	htmlBase::newElement('button')->usePreset('process')->addClass('sendButton')->setText('Send Selected')
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => '<input type="checkbox" class="selectAll">'),
		array('text' => sysLanguage::get('TABLE_HEADING_ID')),
		array('text' => sysLanguage::get('TABLE_HEADING_PRIORITY')),
		array('text' => sysLanguage::get('TABLE_HEADING_MOVIE_TITLE')),
		array('text' => sysLanguage::get('TABLE_HEADING_STATUS')),
		array('text' => sysLanguage::get('TABLE_HEADING_BAR_CODE'))
	)
));

$todays_date = date('m/d/Y');
$rowCount=0;
if ($RentalQueue->hasContents() === false){
	$tableGrid->addBodyRow(array(
		'columns' => array(
			array(
				'text' => 'Rental Queue for this customer is Empty',
				'attr' => array(
					'colspan' => 7
				),
				'align' => 'center'
			)
		)
	));
}else{
	$Contents = $RentalQueue->getContents()->getIterator();
	while($Contents->valid()){
		$RentalProduct = $Contents->current();

		$ProductCls = $RentalProduct->getProductClass();
		$ProductTypeCls = $ProductCls->getProductTypeClass();
		$PurchaseTypeCls = $ProductTypeCls->getPurchaseType('membershipRental');
		$InventoryCls = $PurchaseTypeCls->getInventoryClass();

		if ($InventoryCls->getTrackMethod() == 'barcode'){
			$InventoryCls->setInvUnavailableStatus(array(
				'B',
				'O',
				'P',
				'R'
			));

			if ($InventoryCls->hasInventory() === true){
				$invSelectInput = htmlBase::newElement('selectbox')
					->setName('barcode[' . $RentalProduct->getId() . ']')
					->addClass('barcodeMenu');

				foreach($InventoryCls->getInventoryItems() as $bInfo){
					$barcode = $bInfo['barcode'];
					if ($appExtension->isEnabled('inventoryCenters') && isset($bInfo['center_id'])){
						$barcode .= ' ( ' . $invItem['center_name'] . ' )';
					}elseif ($appExtension->isEnabled('multiStore') && isset($bInfo['store_id'])){
						$barcode .= ' ( ' . $bInfo['store_name'] . ' )';
					}

					$invSelectInput->addOption($bInfo['id'], $barcode);
				}

				$invSelect = $invSelectInput->draw();
			}else{
				$invSelect = sysLanguage::get('TEXT_STOCK_OUT');
			}
		}else{
			$invSelect = 'Quantity';
		}

		$tableGrid->addBodyRow(array(
			'columns' => array(
				array('text' => ($InventoryCls->hasInventory() === true ? tep_draw_checkbox_field('queueItem[]', $RentalProduct->getId()) : ''), 'align' => 'center'),
				array('text' => $RentalProduct->getData('product_id')),
				array('text' => $RentalProduct->getPriority(), 'align' => 'center'),
				array('text' => $RentalProduct->getName()),
				array('text' => $PurchaseTypeCls->getAvailabilityName()),
				array('text' => $invSelect)
			)
		));

		$Contents->next();
	}
}

$totalRented = $RentalQueue->count_rented();

$totalCanSend = $Plan->no_of_titles - $totalRented;

$infoTable = '<table cellpadding="3" cellspacing="0" border="0">
     <tr>
      <td class="main" colspan="2">' . sprintf(sysLanguage::get('TEXT_MEMBER_SINCE'), tep_date_short($Membership->membership_date)) . '</td>
     </tr>';

if ($appExtension->isEnabled('inventoryCenters')){
	$centerID = $addressBook->getAddressInventoryCenter($membership->getRentalAddressId());
	$CustomerInvCenter = Doctrine_Manager::getInstance()
		->getCurrentConnection()
		->fetchAssoc('select inventory_center_id, inventory_center_name from products_inventory_centers where inventory_center_id = "' . $centerID . '"');

	$infoTable .= '<tr>
		 <td class="main">Inventory Center:</td>
		 <td class="main">' . $CustomerInvCenter[0]['inventory_center_name'] . '</td>
		</tr>';
}

$infoTable .= '<tr>
	 <td class="main">' . sysLanguage::get('TABLE_HEADING_TITLE_REQUESTING') . ':</td>
	 <td class="main">' . $RentalQueue->countContents() . '</td>
	</tr>
	<tr>
	 <td class="main">' . sysLanguage::get('TABLE_HEADING_PACKAGE_ALLOWED') . ':</td>
	 <td class="main">' . $Plan->no_of_titles . '</td>
	</tr>
	<tr>
	 <td class="main">' . sysLanguage::get('TABLE_HEADING_ITEMS') . ':</td>
	 <td class="main">' . $totalRented . ' ( <a href="' . tep_href_link('return_rentals.php', 'cID=' . $cID) . '">' . sprintf(sysLanguage::get('TEXT_RENTED_QUEUE'), $Customer->customers_firstname . ' ' . $Customer->customers_lastname) .'</a> )</td>
	</tr>
   </table>';
?>
<script>
	var customerId = '<?php echo $_GET['cID'];?>';
</script>
<div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE_DETAILS');?></div>
<br />
<div>
	<?php echo $infoTable;?>
	<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
		<form name="rental_queue_details" action="<?php echo itw_app_link('action=sendRentals&cID=' . $_GET['cID']);?>" method="post">
			<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
		</form>
	</div>
</div>
