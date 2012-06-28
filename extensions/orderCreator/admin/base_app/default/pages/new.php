<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

$PageBlockPath = 'extensions/orderCreator/admin/base_app/default/pageBlocks/';
?>
<div class="ui-widget">
	<div class="customerSection">
		<h2><u><?php echo sysLanguage::get('HEADING_CUSTOMER_INFORMATION');?></u></h2>
		<?php
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'customerDetails.php')){
			$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'customerDetails.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'customerDetails.php';
		}

		require($requireFile);
		?>
	</div>
	<br>
	<div class="productSection">
		<h2><u><?php echo sysLanguage::get('HEADING_PRODUCTS');?></u></h2>
		<?php
		$contents = EventManager::notifyWithReturn('OrderInfoBeforeProductListingEdit', (isset($oID) ? $oID : null));
		if (!empty($contents)){
			foreach($contents as $content){
				echo $content;
			}
		}
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'editProducts.php')){
			$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'editProducts.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'editProducts.php';
		}

		require($requireFile);
		?>
	</div>
	<br>
	<div class="totalsSection">
		<h2><u><?php echo sysLanguage::get('HEADING_ORDER_TOTALS');?></u></h2>
		<?php
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'orderTotals.php')){
			$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'orderTotals.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'orderTotals.php';
		}

		require($requireFile);
		?>
	</div>
	<?php
	if ($SaleModule->acceptsPayments() === true){
	?>
	<br>
	<div class="paymentSection">
		<h2><u><?php echo sysLanguage::get('HEADING_PAYMENT_HISTORY');?></u></h2>
		<?php
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'paymentHistory.php')){
			$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'paymentHistory.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'paymentHistory.php';
		}

		require($requireFile);
		?>
	</div>
	<?php
	}
	?>
	<?php if (sysConfig::get('EXTENSION_ORDER_CREATOR_HIDE_ORDER_STATUS') == 'False'){ ?>
	<br>
	<div class="statusSection">
		<h2><u><?php echo sysLanguage::get('HEADING_STATUS_HISTORY');?></u></h2>
		<?php
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'statusHistory.php')){
			$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'statusHistory.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'statusHistory.php';
		}

		require($requireFile);
		?>
	</div>
	<?php } ?>
	<br /><br />
	<div class="commentSection">
		<h2><u><?php echo sysLanguage::get('HEADING_COMMENTS');?></u></h2>
		<?php
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'comments.php')){
			$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'comments.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'comments.php';
		}

		require($requireFile);
		?>
	</div>
	<?php if (sysConfig::get('EXTENSION_ORDER_CREATOR_SHOW_TRACKING_DATA') == 'True'){ ?>
	<br>
	<div class="trackingSection">
		<h2><u><?php echo sysLanguage::get('HEADING_TRACKING');?></u></h2>
		<?php
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'tracking.php')){
			$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'tracking.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'tracking.php';
		}

		require($requireFile);
		?>
	</div>
	<?php } ?>
	<br>
	<div class="statusSection">
		<?php
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'status.php')){
			$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'status.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'status.php';
		}

		require($requireFile);
		?>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
	<?php
	if (sysConfig::get('EXTENSION_ORDER_CREATOR_CHOOSE_CUSTOMER_TYPE') == 'True' && !isset($_GET['oID']) && (!isset($_GET['isType']))){
		?>
		$('.customerSection').hide();
		$('.hotelGuest').click(function () {
			$('.customerSection').show();
			$('input[name$="[entry_street_address]"]').parent().parent().hide();
			$('input[name$="[entry_suburb]"]').parent().parent().hide();
			$('input[name$="[entry_city]"]').parent().parent().hide();
			$('input[name$="[entry_postcode]"]').parent().parent().hide();
			$('input[name$="[entry_state]"]').parent().parent().hide();
			$('select[name$="[entry_state]"]').parent().parent().hide();
			$('select[name$="[entry_country]"]').parent().parent().hide();
			$('input[name$="room_number"]').parent().parent().show();
			//$('input[name$="telephone"]').parent().parent().hide();
			$('input[name$="drivers_license"]').parent().parent().hide();
			$('input[name$="passport"]').parent().parent().hide();
			//$('input[name$="email"]').parent().parent().hide();
			$('input[name$="password"]').parent().parent().hide();
			$('.isType').val('hotelGuest');
		});
		$('.walkin').click(function () {
			$('.customerSection').show();
			$('input[name$="[entry_street_address]"]').parent().parent().show();
			$('input[name$="[entry_suburb]"]').parent().parent().show();
			$('input[name$="[entry_city]"]').parent().parent().show();
			$('input[name$="[entry_postcode]"]').parent().parent().show();
			$('input[name$="[entry_state]"]').parent().parent().show();
			$('select[name$="[entry_state]"]').parent().parent().show();
			$('select[name$="[entry_country]"]').parent().parent().show();
			$('input[name$="room_number"]').parent().parent().hide();
			//$('input[name$="telephone"]').parent().parent().show();
			$('input[name$="drivers_license"]').parent().parent().show();
			$('input[name$="passport"]').parent().parent().show();
			//$('input[name$="email"]').parent().parent().show();
			$('input[name$="password"]').parent().parent().show();
			$('.isType').val('walkin');
		});
		<?php
	}
	else {
		?>
		$('.chooseType').hide();
		<?php
		if ($Editor->getRoomNumber() != '' || (isset($_GET['isType']) && $_GET['isType'] == 'hotelGuest')){
			?>
			$('.customerSection').show();
			$('input[name$="[entry_street_address]"]').parent().parent().hide();
			$('input[name$="[entry_suburb]"]').parent().parent().hide();
			$('input[name$="[entry_city]"]').parent().parent().hide();
			$('input[name$="[entry_postcode]"]').parent().parent().hide();
			$('input[name$="[entry_state]"]').parent().parent().hide();
			$('select[name$="[entry_state]"]').parent().parent().hide();
			$('select[name$="[entry_country]"]').parent().parent().hide();
			$('input[name$="room_number"]').parent().parent().show();
			//$('input[name$="telephone"]').parent().parent().hide();
			$('input[name$="drivers_license"]').parent().parent().hide();
			$('input[name$="passport"]').parent().parent().hide();
			//$('input[name$="email"]').parent().parent().hide();
			$('input[name$="password"]').parent().parent().hide();
			<?php
		}
		else {
			?>
			$('.customerSection').show();
			$('input[name$="[entry_street_address]"]').parent().parent().show();
			$('input[name$="[entry_suburb]"]').parent().parent().show();
			$('input[name$="[entry_city]"]').parent().parent().show();
			$('input[name$="[entry_postcode]"]').parent().parent().show();
			$('input[name$="[entry_state]"]').parent().parent().show();
			$('select[name$="[entry_state]"]').parent().parent().show();
			$('select[name$="[entry_country]"]').parent().parent().show();
			$('input[name$="room_number"]').parent().parent().hide();
			//$('input[name$="telephone"]').parent().parent().show();
			$('input[name$="drivers_license"]').parent().parent().show();
			$('input[name$="passport"]').parent().parent().show();
			//$('input[name$="email"]').parent().parent().show();
			$('input[name$="password"]').parent().parent().show();
			<?php
		}
	}
	?>
	});
</script>