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
			$content = '<div class="myAddons">';

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
					foreach(PurchaseTypeModules::getModules() as $purchaseType){
						$code = $purchaseType->getCode();

							$purchaseType->loadProduct($addon);
						if($code == $typeName && $purchaseType->getData('status') == 1 && $purchaseType->hasInventory()){
							//&& $purchaseType->hasInventory() === true
								//$htmlSelectType->addOption($code,$purchaseType->getTitle());
								$htmlSelectType->setValue($code);
								$f = true;

						}
						//if($purchaseType->)
					}
					if($f){
						$content .= '<input type="checkbox" name="addon_product['.$addon.']" value="1">'.$ProductName[0]['products_name'].'&nbsp;'.$htmlSelectType->draw().'<br/>';
					}
			   }
			}
			$content .= '</div>';
		 	$settings['content'] .= $content;
		}

	}
?>