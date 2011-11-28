<?php
	class productAddons_catalog_product_info extends Extension_productAddons {
		public function __construct(){
			global $App;
			parent::__construct();

		}

		public function load(){
			if ($this->enabled === false) return;

			EventManager::attachEvent('ProductInfoPurchaseBoxOnLoad', null, $this);
		}
		public function ProductInfoPurchaseBoxOnLoad(&$settings, $typeName, $purchaseTypes){
			$content = '<div class="myAddons"><b>Recommended Add-ons:</b><br/><div class="myAddonsInner"> ';

			$pID = $_GET['products_id'];
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
						$content .= '<div><input type="checkbox" name="addon_product['.$addon.']" value="1">'.$ProductName[0]['products_name'].'</div><div>'. $dataOptions['content'] . '</div>'. $htmlSelectType->draw().'<br/>';
					}
			   }
			}
			$content .= '</div></div>';
		 	$settings['content'] .= $content;
		}

	}
?>