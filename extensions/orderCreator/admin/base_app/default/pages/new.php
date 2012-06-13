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

$SaleModule = $Editor->getSaleModule();
?>
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
<script>
	$(document).ready(function (){
		$('.loadRevision').change(function (){
			js_redirect(js_app_link(js_get_all_get_params() + '&rev=' + $(this).val()));
		});
	});
</script>
<form name="new_order" action="<?php echo itw_app_link(tep_get_all_get_params(array('action')) . 'action=saveOrder');?>" method="post">
<script>
	$(document).ready(function (){
		$('.ApplicationPageMenu .rootItem').click(function (e){
			$('.subMenu:visible').slideUp('fast');
			if ($(this).find('.subMenu').size() > 0){
				e.stopPropagation();
				$(this).find('.subMenu').first().slideDown('fast');
				$(document).one('click', function (){
					$('.subMenu:visible').slideUp('fast');
				});
			}
		});
	});
</script>
<div class="ApplicationPageMenu ui-corner-bottom"><?php
	$Menu = htmlBase::newList();

	$SaveButton = htmlBase::newElement('button')
		->setType('submit')
		->setName('save')
		->val($SaleModule->getCode())
		->usePreset('save')
		->setText('Save');

	$SaveListItem = htmlBase::newElement('li')
		->addClass('rootItem')
		->html($SaveButton->draw());
	$Menu->addItemObj($SaveListItem);

	if ($SaleModule->canConvert()){
		$ConvertSubList = htmlBase::newList();
		$ConvertSubList->addClass('subMenu ui-corner-bottom');
		foreach($SaleModule->getConvertOptions() as $oInfo){
			$ConvertButton = htmlBase::newElement('button')
				->setType('submit')
				->setName('convertTo')
				->val($oInfo['code'])
				->setText('To ' . $oInfo['title']);

			$ConvertItem = htmlBase::newElement('li')
				->addClass('subItem')
				->html($ConvertButton->draw());
			$ConvertSubList->addItemObj($ConvertItem);
		}

		$ConvertIcon = htmlBase::newElement('icon')
			->setType('transferthick-e-w');
		$ConvertListItem = htmlBase::newElement('li')
			->addClass('rootItem')
			->html($ConvertIcon->draw() . '<span>Convert</span>' . $ConvertSubList->draw());
		$Menu->addItemObj($ConvertListItem);
	}

	if ($SaleModule->canPrint()){
		$PrintSubList = htmlBase::newList();
		$PrintSubList->addClass('subMenu ui-corner-bottom');
		foreach($SaleModule->getPrintOptions() as $oInfo){
			$PrintButton = htmlBase::newElement('button')
				->setType('submit')
				->setName('print')
				->val($oInfo['code'])
				->setText($oInfo['title']);

			$PrintItem = htmlBase::newElement('li')
				->addClass('subItem')
				->html($PrintButton->draw());
			$PrintSubList->addItemObj($PrintItem);
		}

		$PrintIcon = htmlBase::newElement('icon')
			->setType('print');
		$PrintListItem = htmlBase::newElement('li')
			->addClass('rootItem')
			->attr('id', 'print')
			->html($PrintIcon->draw() . '<span>Print</span>' . $PrintSubList->draw());
		$Menu->addItemObj($PrintListItem);
	}

	$RevisionIcon = htmlBase::newElement('icon')
		->setType('revision');
	$RevisionListItem = htmlBase::newElement('li')
		->addClass('rootItem')
		->attr('id', 'revision')
		->html($RevisionIcon->draw() . '<span>Revision</span>');
	$Menu->addItemObj($RevisionListItem);

	echo $Menu->draw();
	?></div>
<div class="ui-widget">
	<div class="customerSection">
		<h2><u><?php echo sysLanguage::get('HEADING_CUSTOMER_INFORMATION');?></u></h2>
		<?php
		if (file_exists(sysConfig::get('DIR_FS_CATALOG_TEMPLATES') . sysConfig::get('DIR_WS_TEMPLATES_DEFAULT') . '/extensions/orderCreator/admin/base_app/default/pageBlocks/customerDetails.php')){
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
	<div class="totalsSection">
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
	<?php
	if ($SaleModule->acceptsPayments() === true){
	?>
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
	<?php
	}
	?>
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