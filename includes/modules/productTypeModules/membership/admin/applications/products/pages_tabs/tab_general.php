<?php
	$ProductStatusEnabled = htmlBase::newElement('radio')
	->setName('products_status')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_AVAILABLE'))
	->setValue('1');
	
	$ProductStatusDisabled = htmlBase::newElement('radio')
	->setName('products_status')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_NOT_AVAILABLE'))
	->setValue('0');
	
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