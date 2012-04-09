<?php
	$pID_string = $_GET['products_id'];
	$purchaseTypeClass = PurchaseTypeModules::getModule('reservation');
	$purchaseTypeClass->loadProduct($pID_string);
	$purchaseTypeClasses = array();
	$purchaseTypeClasses[] = $purchaseTypeClass;
	$pprTable = Doctrine_Core::getTable('ProductsPayPerRental')->findOneByProductsId($pID_string);
	$insurancePrice = $pprTable->insurance;
	if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GLOBAL_MIN_RENTAL_DAYS') == 'False') {
		$minRentalPeriod = ReservationUtilities::getPeriodTime($pprTable->min_period, $pprTable->min_type) * 60 * 1000;
	} else {
		$minRentalPeriod = (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS') * 24 * 60 * 60 * 1000;
	}
	$maxRentalPeriod = -1;

	if ($pprTable->max_period > 0) {
		$maxRentalPeriod = ReservationUtilities::getPeriodTime($pprTable->max_period, $pprTable->max_type) * 60 * 1000;
	}
	ob_start();
?>

	<?php
      if ($purchaseTypeClass->getDepositAmount() > 0){
		$infoIcon = htmlBase::newElement('icon')
		->setType('info')
		->attr('onclick', 'popupWindow(\'' . itw_app_link('appExt=infoPages&dialog=true', 'show_page', 'ppr_deposit_info') . '\',400,300);')
		->css(array(
			'display' => 'inline-block',
			'cursor' => 'pointer'
		));
	?>
	 <div class="depositText" style="display:block;">
        <?php echo sysLanguage::get('PPR_DEPOSIT_AMOUNT') . ' - '. $currencies->format($purchaseTypeClass->getDepositAmount()) . $infoIcon->draw();?>
	 </div>
		<?php
	}
		?>
	<div class="pricingTable" style="display:block;">
        <?php echo $purchaseTypeClass->getPricingTable();?>
	</div>
<?php
	//this part needs redone
	 if ($maxRentalPeriod > 0){
?>
	<div class="maxPeriod" style="display:block;">
        <?php echo sysLanguage::get('TEXT_MAX') . ' ' . ReservationUtilities::getPeriodType($pprTable->max_type);?>:
        <?php echo $pprTable->max_period . ' '.ReservationUtilities::getPeriodType($pprTable->max_type);?>
	</div>
<?php
}
?>
		<?php
if ($minRentalPeriod > 0){
?>
	<div class="minPeriod" style="display:block;">
		<?php
	if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GLOBAL_MIN_RENTAL_DAYS') == 'False') {
		echo sysLanguage::get('TEXT_MIN') . ' ' . ReservationUtilities::getPeriodType($pprTable->min_type) .': '. $pprTable->min_period.' '.ReservationUtilities::getPeriodType($pprTable->min_type);
	}else{
		echo sysLanguage::get('TEXT_MIN') . ' ' . sysLanguage::get('TEXT_DAYS').': '. sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
	}
	?>
	</div>

<?php
}
?>
	<?php
if ($insurancePrice > 0){
?>
	<div class="insurancePrice" style="display:block;">
		<?php
			$infoIconIns = htmlBase::newElement('a')
			->html(sysLanguage::get('TEXT_INSURANCE'))
			->attr('onclick', 'popupWindow(\'' . itw_app_link('appExt=infoPages&dialog=true', 'show_page', 'Insurance_calendar') . '\',400,300);return false;')
			->css(array(
				'cursor' => 'pointer'
			));
			echo $infoIconIns->draw(). $currencies->format($insurancePrice) ;
		?>
	</div>
<?php
}
?>
    <div class="calendarTable" style="display:block;">
		<?php
				echo ReservationUtilities::getCalendar(array(
					'purchaseTypeClasses' => $purchaseTypeClasses,
					'calanderMonths' => 5
				));
		?>
		<input type="hidden" name="purchase_type" value="reservation">
	</div>

   <?php
	   $pageContents = ob_get_contents();
	   ob_end_clean();

	   $pageTitle = sysLanguage::get('TEXT_CREATE_RESERVATION');

	   $pageButtons = '';
	   if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_CALENDAR_PRODUCT_INFO') == 'False') {
		    $pageButtons .= htmlBase::newElement('button')
		    ->usePreset('back')
		    ->addClass('pprBack')
		    ->setHref(itw_app_link('products_id=' . $pID_string, 'product', 'info'))
		    ->draw();


		   if (isset($_POST['id'])){
			   function traverseAttributePost($arr, &$name){
				   foreach($arr as $k => $v){
					   $name .= '[' . $k . ']';
					   if (is_array($v)){
						   traverseAttributePost($name, $v);
					   }else{
						   return $v;
					   }
				   }
			   }
			   foreach($_POST['id'] as $k => $v){
				   $name = 'id[' . $k . ']';
				   if (is_array($v)){
					   $value = traverseAttributePost($v, &$name);
				   }else{
					   $value = $v;
				   }

				   $pageContents .= htmlBase::newElement('input')
					   ->setType('hidden')
					   ->setName($name)
					   ->val($value)
					   ->draw();
			   }
		   }

		   $pageContents = htmlBase::newElement('form')
			   ->setAction(itw_app_link('action=addCartProduct&products_id=' . $pID_string, 'shoppingCart', 'default'))
			   ->setName('build_reservation')
			   ->setMethod('post')
			   ->html($pageContents)
			   ->draw();

		   $pageContent->set('pageTitle', $pageTitle);
		   $pageContent->set('pageContent', $pageContents);
		   $pageContent->set('pageButtons', $pageButtons);
	   }else{
		   $htmlForm = htmlBase::newElement('form')
			->attr('name', 'build_reservation')
			->attr('action', itw_app_link('action=addCartProduct&products_id=' . $pID_string, 'shoppingCart', 'default'))
			->attr('method', 'post');
		   $htmlDiv = htmlBase::newElement('div')
			->html($pageContents);
		   echo $htmlForm->append($htmlDiv)->draw();
	   }
