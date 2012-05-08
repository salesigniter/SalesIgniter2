<style>
	.ui-datepicker-group {
		margin : .3em;
	}

	.ui-datepicker-header {
		padding    : 0;
		text-align : center;
	}

	.ui-datepicker-header span {
		margin : .5em;
	}

	.ui-datepicker .ui-datepicker-prev, .ui-datepicker .ui-datepicker-next {
		top : 0px;
	}

	.ui-datepicker-status {
		margin      : .5em;
		text-align  : center;
		font-weight : bold;
	}

		/*#datePicker { font-size: 1.25em; }
		#datePicker .ui-datepicker-calendar td { font-size: 1.25em; }
		#datePicker .ui-datepicker-start_date { background: #00FF00; }*/
	.ui-datepicker-shipping-day-hover, .ui-datepicker-shipping-day-hover-info {
		background : #F7C8D3;
	}

	#datePicker .ui-state-active {
		background : #CACEE6;
	}
</style>
<form name="new_order" action="<?php echo itw_app_link(tep_get_all_get_params(array('action')) . 'action=saveOrder');?>" method="post">
<div style="text-align:right"><?php
	$saveButton = htmlBase::newElement('button')->usePreset('save')
		->setType('submit')->setName('saveOrder');
	$estimateButton = htmlBase::newElement('button')->usePreset('save')
		->setType('submit')->setName('estimateOrder')
		->attr('toolTip', 'This saves the enquiry details <br>but does NOT reserve any bikes.<br>You can change this later');
	$emailButton = htmlBase::newElement('button')->usePreset('save')
		->setType('submit')->setName('emailEstimate')->setId('emailEstimate');
	$EmailInput = htmlBase::newElement('input')
		->setName('emailInput')
		->setId('emailInput')
		->setLabel('Email:')
		->setLabelPosition('before');

	if (isset($_GET['oID'])){
		if (!isset($_GET['isEstimate'])){
			$saveButton->setText(sysLanguage::get('TEXT_BUTTON_UPDATE_ORDER'));
			$estimateButton->disable();
		}
		else {
			$saveButton->setText(sysLanguage::get('TEXT_BUTTON_SAVE_AS_ORDER'));
			$estimateButton->setText(sysLanguage::get('TEXT_BUTTON_UPDATE_ESTIMATE'));
			$emailButton->setText(sysLanguage::get('TEXT_BUTTON_SEND_ESTIMATE'));
		}
	}
	else {
		$saveButton->setText(sysLanguage::get('TEXT_BUTTON_SAVE_AS_ORDER'));
		$estimateButton->setText(sysLanguage::get('TEXT_BUTTON_SAVE_AS_ESTIMATE'));
	}


	$cancelButton = htmlBase::newElement('button')->usePreset('cancel')
		->setHref(itw_app_link(null, 'orders', 'default'));

	$ResReportButton = htmlBase::newElement('button')
		->addClass('resReports')
		->setText('Reservation Reports')
		->setHref(itw_app_link(null, 'orders', 'default'));

	$infobox = htmlBase::newElement('div');
	$infobox /*->append($saveButton)->append($estimateButton)*/
		->append($ResReportButton)->append($cancelButton);

	EventManager::notify('AdminOrderCreatorAddButton', &$infobox);

	if (isset($_GET['oID'])){
		if (isset($_GET['isEstimate'])){
			$br = htmlBase::newElement('br');
			$infobox->append($br)->append($EmailInput)->append($emailButton);
		}
	}

	echo $infobox->draw();

	?></div>
<br />
<span style="font-size:2em;color:red;line-height:1em;">To add products to order, first enter customer details and click update customer</span>

<div class="ui-widget">
	<div class="customerSection">
		<h2><u><?php echo sysLanguage::get('HEADING_CUSTOMER_INFORMATION');?></u></h2>
		<?php
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/pageBlocks/customerDetails.php')){
			$requireFile = sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/customerDetails.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/base_app/default/pageBlocks/customerDetails.php';
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
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/editProducts.php')){
			$requireFile = sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/editProducts.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/base_app/default/pageBlocks/editProducts.php';
		}

		require($requireFile);
		?>
	</div>
	<br>
	<div class="productSection">
		<h2><u><?php echo sysLanguage::get('HEADING_ORDER_TOTALS');?></u></h2>
		<?php
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/pageBlocks/orderTotals.php')){
			$requireFile = sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/orderTotals.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/base_app/default/pageBlocks/orderTotals.php';
		}

		require($requireFile);
		?>
	</div>
	<br>
	<div class="paymentSection">
		<h2><u><?php echo sysLanguage::get('HEADING_PAYMENT_HISTORY');?></u></h2>
		<?php
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/pageBlocks/paymentHistory.php')){
			$requireFile = sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/paymentHistory.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/base_app/default/pageBlocks/paymentHistory.php';
		}

		require($requireFile);
		?>
	</div>
	<?php if (sysConfig::get('EXTENSION_ORDER_CREATOR_HIDE_ORDER_STATUS') == 'False'){ ?>
	<br>
	<div class="statusSection">
		<h2><u><?php echo sysLanguage::get('HEADING_STATUS_HISTORY');?></u></h2>
		<?php
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/pageBlocks/statusHistory.php')){
			$requireFile = sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/statusHistory.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/base_app/default/pageBlocks/statusHistory.php';
		}

		require($requireFile);
		?>
	</div>
	<?php } ?>
	<br /><br />
	<div class="commentSection">
		<h2><u><?php echo sysLanguage::get('HEADING_COMMENTS');?></u></h2>
		<?php
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/pageBlocks/comments.php')){
			$requireFile = sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/comments.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/base_app/default/pageBlocks/comments.php';
		}

		require($requireFile);
		?>
	</div>
	<?php if (sysConfig::get('EXTENSION_ORDER_CREATOR_SHOW_TRACKING_DATA') == 'True'){ ?>
	<br>
	<div class="trackingSection">
		<h2><u><?php echo sysLanguage::get('HEADING_TRACKING');?></u></h2>
		<?php
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/pageBlocks/tracking.php')){
			$requireFile = sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/tracking.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/base_app/default/pageBlocks/tracking.php';
		}

		require($requireFile);
		?>
	</div>
	<?php } ?>
	<br>
	<div class="statusSection">
		<?php
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/pageBlocks/status.php')){
			$requireFile = sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/status.php';
		}
		else {
			$requireFile = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/base_app/default/pageBlocks/status.php';
		}

		require($requireFile);
		?>
	</div>
</div>
<br />

<div style="text-align:right"><?php
	echo $saveButton->draw() . $estimateButton->draw() . $cancelButton->draw() . '<br>';
	?></div>
</form>

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