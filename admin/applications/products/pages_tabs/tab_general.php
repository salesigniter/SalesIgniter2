<?php
	$ProductStatusEnabled = htmlBase::newElement('radio')
	->setName('products_status')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_AVAILABLE'))
	->setValue('1');
	
	$ProductStatusDisabled = htmlBase::newElement('radio')
	->setName('products_status')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_NOT_AVAILABLE'))
	->setValue('0');
	
	$ProductFeaturedStatusEnabled = htmlBase::newElement('radio')
	->setName('products_featured')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_FEATURED'))
	->setValue('1');
	
	$ProductFeaturedStatusDisabled = htmlBase::newElement('radio')
	->setName('products_featured')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_NON_FEATURED'))
	->setValue('0');
	
	$ProductDateAvailable = htmlBase::newElement('input')
	->setName('products_date_available')
	->addClass('useDatepicker');
	
	$ManufacturerMenu = htmlBase::newElement('selectbox')
	->setName('manufacturers_id');
	
	$Qmanufacturers = Doctrine_Query::create()
	->select('manufacturers_id, manufacturers_name')
	->from('Manufacturers')
	->orderBy('manufacturers_name')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	if ($Qmanufacturers){
		foreach($Qmanufacturers as $manufacturer){
			$ManufacturerMenu->addOption($manufacturer['manufacturers_id'], $manufacturer['manufacturers_name']);
		}
	}
	
	$ProductOnOrder = htmlBase::newElement('checkbox')
	->setId('productOnOrder')
	->setName('products_on_order')
	->setValue('1');

	$ProductDateOrdered = htmlBase::newElement('input')
	->setName('products_date_ordered')
	->addClass('useDatepicker');
	
	$ProductModel = htmlBase::newElement('input')
	->setName('products_model');
	
	$ProductWeight = htmlBase::newElement('input')
	->setName('products_weight');
	
	if ($Product->getId() > 0){
		if ($Product->isActive()){
			$ProductStatusEnabled->setChecked(true);
		}else{
			if (!isset($_GET['pID'])){
				$ProductStatusEnabled->setChecked(true);
			}else{
				$ProductStatusDisabled->setChecked(true);
			}
		}

		$ProductFeaturedStatusEnabled->setChecked($Product->isFeatured());
		$ProductFeaturedStatusDisabled->setChecked(!$Product->isFeatured());

		$ProductOnOrder->setChecked($Product->isOnOrder());
		$ProductDateAvailable->setValue($Product->getDateAvailable()->format('Y-m-d'));
		$ManufacturerMenu->selectOptionByValue($Product->getManufacturer());
		$ProductDateOrdered->setValue($Product->getDateOrdered()->format('Y-m-d'));
		$ProductModel->setValue($Product->getModel());
		$ProductWeight->setValue($Product->getWeight());
	}
?>
 <table cellpadding="3" cellspacing="0" border="0">
  <tr>
   <td class="main"><?php echo sysLanguage::get('TEXT_PRODUCTS_STATUS'); ?></td>
   <td class="main"><?php echo $ProductStatusEnabled->draw() . $ProductStatusDisabled->draw(); ?></td>
  </tr>
  <tr>
   <td class="main"><?php echo sysLanguage::get('TEXT_PRODUCTS_FEATURED'); ?></td>
   <td class="main"><?php echo $ProductFeaturedStatusEnabled->draw() . $ProductFeaturedStatusDisabled->draw(); ?></td>
  </tr>
  <tr>
   <td class="main"><?php echo sysLanguage::get('TEXT_PRODUCTS_DATE_AVAILABLE'); ?><br><small>(YYYY-MM-DD)</small></td>
   <td class="main"><?php echo $ProductDateAvailable->draw(); ?></td>
  </tr>
  <tr>
   <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
   <td class="main"><?php echo sysLanguage::get('TEXT_PRODUCTS_MANUFACTURER'); ?></td>
   <td class="main"><?php echo $ManufacturerMenu->draw(); ?></td>
  </tr>
  <tr>
   <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
   <td class="main"><?php echo sysLanguage::get('TEXT_PRODUCT_ON_ORDER'); ?></td>
   <td class="main"><?php echo $ProductOnOrder->draw(); ?></td>
  </tr>
  <tr id="productOnOrderCal" style="display:<?php echo ($ProductOnOrder->attr('checked') == 'true' ? 'block' : 'none');?>;">
   <td class="main"><?php echo sysLanguage::get('TEXT_PRODUCT_DATE_ORDERED'); ?><br><small>(YYYY-MM-DD)</small></td>
   <td class="main"><?php echo $ProductDateOrdered->draw(); ?></td>
  </tr>
  <tr>
   <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
   <td class="main"><?php echo sysLanguage::get('TEXT_PRODUCTS_MODEL'); ?></td>
   <td class="main"><?php echo $ProductModel->draw(); ?></td>
  </tr>
  <tr>
   <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
   <td class="main"><?php echo sysLanguage::get('TEXT_PRODUCTS_WEIGHT'); ?></td>
   <td class="main"><?php echo $ProductWeight->draw(); ?></td>
  </tr>
 </table>