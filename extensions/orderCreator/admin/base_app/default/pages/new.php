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

	<div>
		<div class="statusComments column" style="vertical-align:top;width: 64%;">
			<?php
			$statusDrop = htmlBase::newElement('selectbox')
				->setName('status')
				->selectOptionByValue($Editor->getCurrentStatus(true));
			foreach($orders_statuses as $sInfo){
				$statusDrop->addOption($sInfo['id'], $sInfo['text']);
			}

			$Fieldset = htmlBase::newFieldsetFormBlock();
			$Fieldset->setLegend('Status And Comments');
			$Fieldset->addBlock('status', sysLanguage::get('ENTRY_STATUS'), array(
				array($statusDrop)
			));
			$Fieldset->addBlock('comments', sysLanguage::get('TABLE_HEADING_COMMENTS'), array(
				array(htmlBase::newTextarea()->setName('comments')->html($Editor->InfoManager->getInfo('admin_comments')))
			));
			$Fieldset->addBlock('notify', sysLanguage::get('ENTRY_NOTIFY_CUSTOMER'), array(
				array(htmlBase::newCheckbox()->setName('notify'))
			));

			$tracking = array(
				array(
					'id'      => 'usps',
					'heading' => sysLanguage::get('TABLE_HEADING_USPS_TRACKING'),
					'link'    => 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='
				),
				array(
					'id'      => 'ups',
					'heading' => sysLanguage::get('TABLE_HEADING_UPS_TRACKING'),
					'link'    => 'http://wwwapps.ups.com/etracking/tracking.cgi?InquiryNumber2=&InquiryNumber3=&InquiryNumber4=&InquiryNumber5=&TypeOfInquiryNumber=T&UPS_HTML_Version=3.0&IATA=us&Lang=en&submit=Track+Package&InquiryNumber1='
				),
				array(
					'id'      => 'fedex',
					'heading' => sysLanguage::get('TABLE_HEADING_FEDEX_TRACKING'),
					'link'    => 'http://www.fedex.com/Tracking?action=track&language=english&cntry_code=us&tracknumbers='
				),
				array(
					'id'      => 'dhl',
					'heading' => sysLanguage::get('TABLE_HEADING_DHL_TRACKING'),
					'link'    => 'http://track.dhl-usa.com/atrknav.asp?action=track&language=english&cntry_code=us&ShipmentNumber='
				)
			);

			$blockRows = array();
			$cols = array();
			$TrackingNumbers = $Editor->InfoManager->getInfo('tracking');
			foreach($tracking as $tracker){
				$inputField = htmlBase::newInput()
					->isMultiple(true)
					->setValue(isset($TrackingNumbers[$tracker['id']]) ? $TrackingNumbers[$tracker['id']] : '')
					->setName($tracker['id'] . '_tracking_number')
					->setLabel($tracker['heading'])
					->setLabelPosition('top')
					->attr(
					array(
						'size'      => 40,
						'maxlength' => 40
					));

				$cols[] = $inputField;
				if (sizeof($cols) > 1){
					$blockRows[] = $cols;
					$cols = array();
				}
			}

			$Fieldset->addBlock('tracking', 'Shipment Tracking', $blockRows);

			echo $Fieldset->draw();
			?>
		</div>
		<div class="totalsSection column" style="vertical-align:top;margin-top: 1em;width: 34%;margin-left: 1%;">
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
	</div>
	<div class="tabbed">
		<ul>
			<?php if ($SaleModule->acceptsPayments() === true){ ?>
			<li><a href="#paymentHistory"><?php echo sysLanguage::get('HEADING_PAYMENT_HISTORY');?></a></li>
			<?php } ?>
			<li><a href="#saleHistory"><?php echo sysLanguage::get('HEADING_STATUS_HISTORY');?></a></li>
		</ul>

		<?php if ($SaleModule->acceptsPayments() === true){ ?>
		<div id="paymentHistory"><?php
			if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'paymentHistory.php')){
				$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'paymentHistory.php';
			}
			else {
				$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'paymentHistory.php';
			}

			require($requireFile);
			?></div>
		<?php } ?>

		<div id="saleHistory"><?php
			if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'statusHistory.php')){
				$requireFile = sysConfig::getDirFsCatalog() . 'clientData/' . $PageBlockPath . 'statusHistory.php';
			}
			else {
				$requireFile = sysConfig::getDirFsCatalog() . $PageBlockPath . 'statusHistory.php';
			}

			require($requireFile);
			?></div>
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
