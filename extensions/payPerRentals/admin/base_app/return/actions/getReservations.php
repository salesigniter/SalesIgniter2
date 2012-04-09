<?php
$centersEnabled = false;
if ($appExtension->isInstalled('inventoryCenters') && $appExtension->isEnabled('inventoryCenters')){
	$extInventoryCenters = $appExtension->getExtension('inventoryCenters');
	$centersEnabled = true;
	$centersStockMethod = $extInventoryCenters->stockMethod;
	if ($centersStockMethod == 'Store'){
		$extStores = $appExtension->getExtension('multiStore');
		$invCenterArray = $extStores->getStoresArray();
	}else{
		$invCenterArray = $extInventoryCenters->getCentersArray();
	}
}
$Qreservations = Doctrine_Query::create()
	->from('Orders o')
	->leftJoin('o.OrdersAddresses oa')
	->leftJoin('o.OrdersProducts op')
	->leftJoin('op.OrdersProductsReservation opr')
	->leftJoin('opr.ProductsInventoryBarcodes ib')
	->leftJoin('ib.ProductsInventory i')
	->leftJoin('opr.ProductsInventoryQuantity iq')
	->leftJoin('iq.ProductsInventory i2')
	->where('opr.rental_state = ?', 'out')
	->andWhere('oa.address_type = ?', 'delivery')
	->orderBy('opr.end_date');
if (isset($_GET['start_date']) || isset($_GET['end_date'])){
	$Qreservations->andWhere('opr.start_date between "' . $_GET['start_date'] . '" and "' . $_GET['end_date'] . '"');
}
$html = '';
ob_start();

if ($centersEnabled === true){
	if ($centersStockMethod == 'Store'){
		$Qreservations->leftJoin('ib.ProductsInventoryBarcodesToStores b2s')
			->leftJoin('b2s.Stores s');
	}else{
		$Qreservations->leftJoin('ib.ProductsInventoryBarcodesToInventoryCenters b2c')
			->leftJoin('b2c.ProductsInventoryCenters ic');
	}
}

EventManager::notify('OrdersListingBeforeExecute', &$Qreservations);

$Result = $Qreservations->execute();
if ($Result->count() > 0){
	//echo '<pre>';print_r($Result->toArray(true));
	foreach($Result->toArray(true) as $oInfo){
		$orderId = $oInfo['orders_id'];
		$customersName = $oInfo['OrdersAddresses']['delivery']['entry_name'];
		foreach($oInfo['OrdersProducts'] as $opInfo){
			$productName = $opInfo['products_name'];
			foreach($opInfo['OrdersProductsReservation'] as $oprInfo){
				$trackMethod = $oprInfo['track_method'];
				$reservationId = $oprInfo['orders_products_reservations_id'];
				$useCenter = 0;

				$resStart = $oprInfo['start_date']->format(sysLanguage::getDateFormat('short'));
				$resEnd = $oprInfo['end_date']->format(sysLanguage::getDateFormat('short'));

				$padding_days_before = $oprInfo['shipping_days_before'];
				$padding_days_after = $oprInfo['shipping_days_after'];

				$shipOn = $oprInfo['start_date']->modify('-' . $padding_days_before . ' Day')->format(sysLanguage::getDateFormat('short'));
				$dueBack = $oprInfo['end_date']->modify('+' . $padding_days_after . ' Day')->format(sysLanguage::getDateFormat('short'));

				if ($trackMethod == 'barcode'){
					$barcodeInfo = $oprInfo['ProductsInventoryBarcodes'];
					$barcodeId = $barcodeInfo['barcode_id'];
					$invDescription = $barcodeInfo['barcode'];

					if ($centersEnabled === true){
						if (isset($barcodeInfo['ProductsInventory'])){
							$useCenter = $barcodeInfo['ProductsInventory']['use_center'];
						}else{
							$useCenter = 0;
						}
						if ($useCenter == '1'){
							if ($centersStockMethod == 'Store'){
								if (isset($barcodeInfo['ProductsInventoryBarcodesToStores'])){
									$invCenterId = $barcodeInfo['ProductsInventoryBarcodesToStores']['inventory_store_id'];
								}else{
									$invCenterId = 0;
								}
							}else{
								if (isset($barcodeInfo['ProductsInventoryBarcodesToInventoryCenters'])){
									$invCenterId = $barcodeInfo['ProductsInventoryBarcodesToInventoryCenters']['inventory_center_id'];
								}else{
									$invCenterId = 0;
								}
							}
						}
					}
				}elseif ($trackMethod == 'quantity'){
					$quantityInfo = $oprInfo['ProductsInventoryQuantity'];
					$quantityId = $quantityInfo['quantity_id'];
					$invDescription = 'Quantity Tracking';

					if ($centersEnabled === true){
						$useCenter = $quantityInfo['ProductsInventory']['use_center'];
						if ($useCenter == '1'){
							if ($centersStockMethod == 'Store'){
								$invCenterId = $quantityInfo['Stores']['stores_id'];
							}else{
								$invCenterId = $quantityInfo['InventoryCenters']['inventory_center_id'];
							}
						}
					}
				}
				?>
			<tr class="dataTableRow">
				<td class="main" align="center"><?php echo tep_draw_checkbox_field('rental[' . $reservationId . ']', $reservationId);?></td>
				<td class="main"><?php echo $customersName;?></td>
				<td class="main"><?php echo $productName;?></td>
				<td class="main"><?php echo $invDescription;?></td>
				<?php if ($centersEnabled === true){ ?>
				<td class="main"><?php
	    if ($useCenter == '1'){
					$selectBox = htmlBase::newElement('selectbox')
						->setId('inventory_center')
						->setName('inventory_center[' . $reservationId . ']')
						->attr('defaultValue', $invCenterId);
					foreach($invCenterArray as $invInfo){
						if ($centersStockMethod == 'Store'){
							$selectBox->addOption($invInfo['stores_id'], $invInfo['stores_name']);
						}else{
							$selectBox->addOption($invInfo['inventory_center_id'], $invInfo['inventory_center_name']);
						}
					}
					$selectBox->selectOptionByValue($invCenterId);
					echo $selectBox->draw();
				}
					?></td>
				<?php } ?>
				<td class="main"><table cellpadding="2" cellspacing="0" border="0">
					<tr>
						<td class="main"><?php echo 'Ship On: ';?></td>
						<td class="main"><?php echo $shipOn;?></td>
					</tr>
					<tr>
						<td class="main"><?php echo 'Res Start: ';?></td>
						<td class="main"><?php echo $resStart;?></td>
					</tr>
					<tr>
						<td class="main"><?php echo 'Res End: ';?></td>
						<td class="main"><?php echo $resEnd;?></td>
					</tr>
					<tr>
						<td class="main"><?php echo 'Due Back: ';?></td>
						<td class="main"><?php echo $dueBack;?></td>
					</tr>
				</table></td>
				<td class="main"><?php
					$days = $oprInfo['end_date']->diff(new SesDateTime());
 					if ($days->days > 0){
						echo $days->days . ' Days Until Due';
					}elseif ($days->days == 0){
						echo 'Due Today';
					}else{
						echo $days->format('%a Days Late');
					}
					?></td>
				<td class="main" align="center"><?php echo tep_draw_textarea_field('comment[' . $reservationId . ']', true, 30, 2);?></td>
				                 <?php
					if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_MAINTENANCE') == 'False'){
				?>
				<td class="main" align="center"><?php echo tep_draw_checkbox_field('damaged[' . $reservationId . ']', $reservationId);?></td>
				<td class="main" align="center"><?php echo tep_draw_checkbox_field('lost[' . $reservationId . ']', $reservationId);?></td>
				<?php
				}
				?>
			</tr>
				                 <?php
	if (isset($invCenterId)) unset($invCenterId);

				if (isset($oprInfo['Packaged'])){
					foreach($oprInfo['Packaged'] as $opprInfo){
						if ($check['track_method'] == 'barcode'){
							if ($centersEnabled === true){
								$invDescription = $opprInfo['ProductsInventory']['ProductsInventoryBarcodes']['barcode'];
								$invCenterId = $opprInfo['ProductsInventory']['ProductsInventoryBarcodes']['inventory_center_id'];
								$useCenter = ($opprInfo['ProductsInventory']['use_center'] == '1');
							}else{
								$invDescription = $opprInfo['ProductsInventoryBarcodes']['barcode'];
								$useCenter = false;
							}
						}elseif ($check['track_method'] == 'quantity'){
							$invDescription = 'Quantity Tracking';
							if ($centersEnabled === true){
								$invCenterId = $opprInfo['ProductsInventory']['ProductsInventoryQuantity']['inventory_center_id'];
								$useCenter = ($opprInfo['ProductsInventory']['use_center'] == '1');
							}else{
								$useCenter = false;
							}
						}
						$resId = $opprInfo['orders_products_reservations_id'];
						?>
       <tr class="dataTableRow">
        <td class="main" align="center"></td>
						<td class="main" align="center">|_</td>
						<td class="main"><?php echo $opprInfo['OrdersProducts']['products_name'];?></td>
						<td class="main"><?php echo $invDescription;?></td>
						<?php if ($useCenter === true){ ?>
							<td class="main"><?php
	    echo tep_draw_pull_down_menu('inventory_center[' . $resId . ']', $invCenterArray, $invCenterId, 'defaultValue="' . $invCenterId . '" id="inventory_center"');
								?></td>
							<?php }else{ ?>
							<td class="main"></td>
							<?php }
					}?>
					<td class="main"></td>
					<td class="main"></td>
					<td class="main" align="center"><?php echo tep_draw_input_field('comment[' . $resId . ']');?></td>
					<td class="main" align="center"><?php echo tep_draw_checkbox_field('damaged[' . $resId . ']', $resId);?></td>
					<td class="main" align="center"><?php echo tep_draw_checkbox_field('lost[' . $resId . ']', $resId);?></td>
       </tr>
<?php
}
			}
		}
	}
}

$html = ob_get_contents();
ob_end_clean();

EventManager::attachActionResponse($html, 'html');
?>