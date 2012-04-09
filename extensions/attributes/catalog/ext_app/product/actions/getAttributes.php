<?php
$hasButton = true;
$hide = false;
$outofstock = '';

$Product = new Product((int) $_GET['products_id']);
$ProductTypeClass = $Product->getProductTypeClass();
if (isset($_GET['purchase_type'])){
	$PurchaseType = $ProductTypeClass->getPurchaseType($_GET['purchase_type']);
	$ProductsAttributes = attributesUtil::getAttributesByPurchaseType($PurchaseType);
	if ($PurchaseType->hasInventory() == false){
		$outofstock = '(Out of Stock)';
		if (sysConfig::get('EXTENSION_ATTRIBUTES_HIDE_NO_INVENTORY') == 'True'){
			$hide = true;
		}
		$hasButton = false;
	}
	$fieldNamePrefix = 'id[' . $PurchaseType->getCode() . ']';
	$PostArr = (isset($_POST['id'][$PurchaseType->getCode()]) ? $_POST['id'][$PurchaseType->getCode()] : array());
}else{
	$ProductsAttributes = attributesUtil::getAttributesByProductType($ProductTypeClass);
	$fieldNamePrefix = 'id';
	$PostArr = (isset($_POST['id']) ? $_POST['id'] : array());
}

$table = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0)
	->addClass('attributesTable');

$table->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PRODUCT_OPTIONS'), 'attr' => array('colspan' => 2))
		)
	));

$Attributes = attributesUtil::organizeAttributeArray($ProductsAttributes);

foreach($Attributes as $optionId => $oInfo){
	$optionsValues = $oInfo['ProductsOptionsValues'];
	$html = '';
	switch($oInfo['option_type']){
		case 'radio':
			$list = htmlBase::newElement('list')
				->css(array(
					'list-style' => 'none',
					'padding' => 0,
					'margin' => 0
				));
			for($i=0, $n=sizeof($optionsValues); $i<$n; $i++){
				$valueId = $optionsValues[$i]['options_values_id'];

				$input = htmlBase::newElement('radio')
					->setId('option_' . $optionId . '_value_' . $valueId)
					->setName($fieldNamePrefix . '[' . $optionId . ']')
					->setValue($optionsValues[$i]['options_values_id'])
					->setLabel($optionsValues[$i]['options_values_name']);

				$multiList = '';
				if ($oInfo['use_image'] == '1'){
					$list->addClass('useImage');
					if ($oInfo['use_multi_image'] == '1'){
						$list->addClass('useMultiImage');

						$multiList = htmlBase::newElement('list')
							->setId('images_' . $optionId . '_' . $valueId)
							->css('display', 'none');
						foreach($optionsValues[$i]['ProductsAttributesViews'] as $idx => $viewInfo){
							if ($idx == 0){
								$input->attr('title', $viewInfo['view_name'])
									->attr('imageSrc', $viewInfo['view_image']);
							}

							$liObj = htmlBase::newElement('li')
								->attr('imgSrc', 'product_thumb.php?w=280&img=' . $viewInfo['view_image'])
								->attr('bigImgSrc', $viewInfo['view_image'])
								->html($viewInfo['view_name']);

							$multiList->addItemObj($liObj);
						}
						$multiList = $multiList->draw();
					}else{
						$list->addClass('useSingleImage');

						$input->attr('title', $optionsValues[$i]['options_values_name'])
							->attr('imageSrc', $optionsValues[$i]['options_values_image']);
					}
				}

				$list->addItem('', $input->draw() . $multiList);
			}
			$html .= $list->draw() . '<br />';
			break;
		default:
			$input = htmlBase::newElement('selectbox')
				->setName($fieldNamePrefix . '[' . $optionId . ']')
				->addClass('attrSelect');
			//->addOption('', 'Please Select');

			if ($oInfo['use_image'] == '1'){
				$input->addClass('useImage');
				if ($oInfo['use_multi_image'] == '1'){
					$input->addClass('useMultiImage');
				}else{
					$input->addClass('useSingleImage');
				}
			}

			if (isset($PostArr[$optionId])){
				$input->selectOptionByValue($PostArr[$optionId]);
			}
			$multiList = '';
			for($i=0, $n=sizeof($optionsValues); $i<$n; $i++){
				$valueId = $optionsValues[$i]['options_values_id'];
				$price = '';
				if ($optionsValues[$i]['options_values_price'] != '0') {
					$price = ' (' .
						$optionsValues[$i]['price_prefix'] .
						$currencies->display_price($optionsValues[$i]['options_values_price'], $productClass->getTaxRate()) .
						') ';
				}

				$valName = $optionsValues[$i]['options_values_name'];
				if (!empty($PostArr)){
					foreach($PostArr as $k => $v){
						if ($optionId == $k && $v == $valueId){
							$valName .= $outofstock;
						}
					}
				}

				$optionEl = htmlBase::newElement('option')
					->attr('value', $optionsValues[$i]['options_values_id'])
					->html($valName . $price);

				if ($oInfo['use_image'] == '1'){
					if ($oInfo['use_multi_image'] == '1'){
						$imageList = htmlBase::newElement('list')
							->setId('images_' . $optionId . '_' . $valueId)
							->css('display', 'none');
						foreach($optionsValues[$i]['ProductsAttributesViews'] as $viewInfo){
							$liObj = htmlBase::newElement('li')
								->attr('imgSrc', $viewInfo['view_image'])
								->html($viewInfo['view_name']);

							$imageList->addItemObj($liObj);
						}
						$multiList .= $imageList->draw();
					}else{
						$optionEl->attr('title', $optionsValues[$i]['options_values_name'])
							->attr('imageSrc', $optionsValues[$i]['options_values_image']);
					}
				}
				if($hide == false){
					$input->addOptionObj($optionEl);
				}
			}

			/*if ($ShoppingCart->inCart($_GET['products_id'], $settings['purchase_type'])){
				$cartProduct = $ShoppingCart->getProduct($_GET['products_id'], $settings['purchase_type']);

				if ($cartProduct->hasInfo('attributes')){
					$Attributes = $cartProduct->getInfo('attributes');
					if (isset($Attributes[$optionId])){
						$input->selectOptionByValue($optionId);
					}
				}
			}*/
			$html .= $input->draw() . $multiList;
			break;
	}

	$table->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => $oInfo['options_name'] . ':', 'attr' => array('valign' => 'top')),
				array('addCls' => 'main', 'text' => $html, 'attr' => array('valign' => 'top'))
			)
		));
}


$json = array(
	'success' => true,
	'html' => $table->draw(),
	'hasButton' => $hasButton
);
EventManager::attachActionResponse($json, 'json');
?>