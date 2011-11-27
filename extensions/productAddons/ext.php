<?php
/*
	Related Products Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class Extension_productAddons extends ExtensionBase {

	public function __construct(){
		parent::__construct('productAddons');
	}

	public function init(){
		if ($this->enabled === false) return;

		EventManager::attachEvents(array(
			'PurchaseTypeHiddenFields',
			'ProductListingModuleShowBeforeShow'
		), null, $this);

		require(dirname(__FILE__) . '/classEvents/ShoppingCart.php');
		$eventClass = new ShoppingCart_productAddons();
		$eventClass->init();
	}

	public function PurchaseTypeHiddenFields(&$hiddenFields){
		foreach($_POST['addon_product'] as $addon => $val){
			$purchaseTypeCode = $_POST['addon_product_type'][$addon];
			$hiddenFields[] = tep_draw_hidden_field('addon_product['.$addon.']', $val);
			$hiddenFields[] = tep_draw_hidden_field('addon_product_type['.$addon.']', $purchaseTypeCode);
		}
	}


	public function ProductListingModuleShowBeforeShow($typeName, $PurchaseTypeClass, &$pprButton, &$extraContent){
		$content = '<div class="myAddons"><b>Recommended Add-ons:</b><br/><div class="myAddonsInner"> ';

		$pID = $PurchaseTypeClass->getData('products_id');
		$Qdata = Doctrine_Query::create()
			->from('Products')
			->where('products_id = ?', $pID)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$addonProduct = explode(',', $Qdata[0]['addon_products']);
		foreach($addonProduct as $addon){
			if(!empty($addon)){
				$ProductName = Doctrine_Query::create()
					->from('ProductsDescription')
					->where('products_id = ?', $addon)
					->andWhere('language_id=?', Session::get('languages_id'))
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				$htmlSelectType = htmlBase::newElement('input')
					->setType('hidden')
					->setName('addon_product_type['.$addon.']');

				PurchaseTypeModules::loadModules();
				$f = false;
				$dataOptions = '';

				foreach(PurchaseTypeModules::getModules() as $purchaseType){
					$code = $purchaseType->getCode();

					$purchaseType->loadProduct($addon);

					$isInInventory = true;

					EventManager::notify('ProductIsInInventory', &$isInInventory, $addon);
					if($code == $typeName && $purchaseType->getData('status') == 1 && $purchaseType->hasInventory() && $isInInventory){
						//&& $purchaseType->hasInventory() === true
						//$htmlSelectType->addOption($code,$purchaseType->getTitle());
						$htmlSelectType->setValue($code);
						$f = true;

						$dataOptions = $purchaseType->getPurchaseHtml('product_info');
						break;
					}
					//if($purchaseType->)
				}
				if($f){
					$content .= '<div class="checkBoxInput"><input type="checkbox" name="addon_product['.$addon.']" value="1">'.$ProductName[0]['products_name'].'</div><div class="priceOptions">'. $dataOptions['content'] . '</div>'. $htmlSelectType->draw().'<br/>';
				}
			}
		}
		$content .= '</div></div>';
		$extraContent = $content;
	}

}
?>