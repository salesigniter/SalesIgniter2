<?php

$content = '<div class="myAddons">';

$pID = $_POST['pID'];
	$Qdata = Doctrine_Query::create()
		->from('Products')
		->where('products_id = ?', $pID)
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	$addonProduct = explode(',', $Qdata[0]['addon_products']);
	foreach($addonProduct as $addon){
		$ProductName = Doctrine_Query::create()
		->from('ProductsDescription')
		->where('products_id = ?', $addon)
		->andWhere('language_id=?', Session::get('languages_id'))
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$htmlSelectType = htmlBase::newElement('selectbox')
		->setName('addon_product_type['.$addon.']');

		PurchaseTypeModules::loadModules();
		foreach(PurchaseTypeModules::getModules() as $purchaseType){
			$code = $purchaseType->getCode();
			$purchaseType->loadProduct($addon);

			if ($purchaseType->getData('status') == 1 ){//&& $purchaseType->hasInventory() === true
				$htmlSelectType->addOption($code,$purchaseType->getTitle());
			}
			//if($purchaseType->)
		}

		$content .= '<input type="checkbox" name="addon_product['.$addon.']" value="1">'.$ProductName[0]['products_name'].'&nbsp;'.$htmlSelectType->draw().'<br/>';
	}
$content .= '</div>';

EventManager::attachActionResponse(array(
		'success' => true,
		'addonProducts'  => $content
	), 'json');
	?>