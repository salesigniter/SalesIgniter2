<?php
class AddonProductsProductClassImport extends MI_Importable
{

	private $_hasAddons = false;

	private $AddonProducts = array();

	private $_hasOptionalAddons = false;

	private $OptionalAddonProducts = array();

	public function initAddonProducts() {
		$Qdata = Doctrine_Query::create()
			->select('addon_products')
			->from('Products')
			->where('products_id = ?', $this->getId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if ($Qdata && sizeof($Qdata) > 0){
			$Data = $Qdata[0];
			$this->_hasAddons = true;

			$products = explode(',', $Data['addon_products']);
			foreach($products as $pID){
				if(!empty($pID)){
					$this->addAddonProduct($pID);
				}
			}
		}
	}

	public function initOptionalAddonProducts() {
		$Qdata = Doctrine_Query::create()
			->select('optional_addon_products')
			->from('Products')
			->where('products_id = ?', $this->getId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if ($Qdata && sizeof($Qdata) > 0){
			$Data = $Qdata[0];
			$this->_hasOptionalAddons = true;

			$products = explode(',', $Data['optional_addon_products']);
			foreach($products as $pID){
				if(!empty($pID)){
					$this->addOptionalAddonProduct($pID);
				}
			}
		}
	}

	public function hasAddonProducts(){
		return $this->_hasAddons;
	}

	public function hasOptionalAddonProducts(){
		return $this->_hasOptionalAddons;
	}

	public function addAddonProduct($id){
		$this->AddonProducts[] = new Product($id);
	}

	public function getAddonProducts(){
		return $this->AddonProducts;
	}

	public function addOptionalAddonProduct($id){
		$this->OptionalAddonProducts[] = new Product($id);
	}

	public function getOptionalAddonProducts(){
		return $this->OptionalAddonProducts;
	}
}

/*
	Addon Products Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class productAddons_admin_products_new_product extends Extension_productAddons {

	public function __construct(){
		parent::__construct();
	}
	
	public function load(){
		if ($this->enabled === false) return;
		
		EventManager::attachEvents(array(
			'NewProductAddTabs',
			'ProductInfoClassConstruct'
		), null, $this);
	}

	public function ProductInfoClassConstruct(Product &$ProductClass, $Product) {
		$ProductClass->import(new AddonProductsProductClassImport);
		$ProductClass->initAddonProducts();
		$ProductClass->initOptionalAddonProducts();
	}

	public function get_category_tree_list($parent_id = '0', $checked = false, $include_itself = true, $ProdID){
		$langId = Session::get('languages_id');
		$excludedList = array();
		$excludedList[] = $ProdID;
		$catList = '';
		//$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$langId . "' and c.parent_id = '" . (int)$parent_id . "' order by c.sort_order, cd.categories_name");
		$QCategories = Doctrine_Query::create()
		->from('Categories c')
		->leftJoin('c.CategoriesDescription cd')
		->where('cd.language_id = ?', $langId)
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		foreach($QCategories as $cat){
			$catList .= '<optgroup label="' . $cat['CategoriesDescription'][0]['categories_name'] . '">';
			$QProducts = Doctrine_Query::create()
			->from('Products p')
			->leftJoin('p.ProductsDescription pd')
			->leftJoin('p.ProductsToCategories p2c')
			->where('pd.language_id = ?', $langId)
			->andWhere('p2c.categories_id = ?', $cat['categories_id'])
			->andWhereNotIn('p.products_id', $excludedList)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			foreach($QProducts as $products){
				$exclude = false;
				$Product = new Product($products['products_id']);
				foreach($Product->getAddonProducts() as $AddonProduct){
					if($AddonProduct->getId() == $ProdID){
						$exclude = true;
					}
				}
				foreach($Product->getOptionalAddonProducts() as $AddonProduct){
					if($AddonProduct->getId() == $ProdID){
						$exclude = true;
					}
				}
				if(!$exclude){
					$catList .= '<option value="' . $products['products_id'] . '">(' . $products['products_model'] . ") " . $products['ProductsDescription'][0]['products_name'] . '</option>';
				}
			}
			
			if (tep_childs_in_category_count($cat['categories_id']) > 0){
				$catList .= $this->get_category_tree_list($cat['categories_id'], $checked, false);
			}
			$catList .= '</optgroup>';
		}
		return $catList;
	}

	public function no_category($ProdID){
		$langId = Session::get('languages_id');
		$catList = '<optgroup label="nocategory">';
		$excludedList = array();
		$excludedList[] = $ProdID;

		$QProducts = Doctrine_Query::create()
			->from('Products p')
			->leftJoin('p.ProductsDescription pd')
			->leftJoin('p.ProductsToCategories p2c')
			->where('pd.language_id = ?', $langId)
			->andWhereNotIn('p.products_id', $excludedList)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		foreach($QProducts as $products){
		    if(count($products['ProductsToCategories']) == 0){
			    $exclude = false;
			    $Product = new Product($products['products_id']);
			    foreach($Product->getAddonProducts() as $AddonProduct){
				    if($AddonProduct->getId() == $ProdID){
						$exclude = true;
				    }
			    }
			    foreach($Product->getOptionalAddonProducts() as $AddonProduct){
				    if($AddonProduct->getId() == $ProdID){
					    $exclude = true;
				    }
			    }
			    if(!$exclude){
					$catList .= '<option value="' . $products['products_id'] . '">(' . $products['products_model'] . ") " . $products['ProductsDescription'][0]['products_name'] . '</option>';
			    }
		    }
		}
		$catList .= '</optgroup>';
		return $catList;
	}

	public function NewProductAddTabs(Product $Product, $ProductType, htmlWidget_tabs &$Tabs) {
		if ($ProductType->getCode() == 'standard'){
			$Tabs
				->addTabHeader('tab_' . $this->getExtensionKey(), array('text' => sysLanguage::get('TAB_PAY_ADDON_PRODUCTS')))
				->addTabPage('tab_' . $this->getExtensionKey(), array('text' => $this->NewProductTabBody($Product)));
		}
	}

	public function NewProductTabBody(Product $Product){
		$table = htmlBase::newElement('table')
		->setCellPadding(3)
		->setCellSpacing(0)
		->css('width', '100%');

		$table->addHeaderRow(array(
			'columns' => array(
				array('attr' => array('width' => '40%'), 'text' => sysLanguage::get('TAB_PAY_ADDON_PRODUCTS_TEXT_PRODUCTS')),
				array('text' => '&nbsp;'),
				array('attr' => array('width' => '40%'), 'text' => sysLanguage::get('TAB_PAY_ADDON_PRODUCTS_TEXT_ADDONS'))
			)
		));
		
		$addonProducts = '';
		//print_r($pInfo);
        if ($Product->hasAddonProducts() === true){
            foreach($Product->getAddonProducts() as $AddonProduct){
                $addonProducts .= '<div><a href="#" class="ui-icon ui-icon-circle-close removeButton"></a><span class="main">' . $AddonProduct->getName() . '</span>' . tep_draw_hidden_field('addon_products[]', $AddonProduct->getId()) . '</div>';
            }
        }
		
		$table->addBodyRow(array(
			'columns' => array(
				array(
					'addCls' => 'main',
					'attr' => array(
						'valign' => 'top'
					), 
					'text' => '<select size="30" style="width:100%;" id="productListAddons">' .  $this->no_category($Product->getId()) . $this->get_category_tree_list('0',false,true,$Product->getId()) . '</select>'
				),
				array(
					'addCls' => 'main',
					'text' => '<button type="button" id="moveRightAddon"><span>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;</span></button>'
				),
				array(
					'addCls' => 'main',
					'attr' => array(
						'id' => 'addons',
						'valign' => 'top'
					), 
					'text' => $addonProducts
				)
			)
		));

		$addonTables = $table->draw();

		$tableOptional = htmlBase::newElement('table')
			->setCellPadding(3)
			->setCellSpacing(0)
			->css('width', '100%');

		$tableOptional->addHeaderRow(array(
				'columns' => array(
					array('attr' => array('width' => '40%'), 'text' => sysLanguage::get('TAB_PAY_ADDON_PRODUCTS_TEXT_PRODUCTS')),
					array('text' => '&nbsp;'),
					array('attr' => array('width' => '40%'), 'text' => sysLanguage::get('TAB_PAY_ADDON_PRODUCTS_TEXT_ADDONS_OPTIONAL'))
				)
			));

		$addonProducts = '';
		//print_r($pInfo);
		if ($Product->hasOptionalAddonProducts() === true){
			foreach($Product->getOptionalAddonProducts() as $AddonProduct){
				$addonProducts .= '<div><a href="#" class="ui-icon ui-icon-circle-close removeButton"></a><span class="main">' . $AddonProduct->getName() . '</span>' . tep_draw_hidden_field('addon_products[]', $AddonProduct->getId()) . '</div>';
			}
		}

		$tableOptional->addBodyRow(array(
				'columns' => array(
					array(
						'addCls' => 'main',
						'attr' => array(
							'valign' => 'top'
						),
						'text' => '<select size="30" style="width:100%;" id="productListOptional">' . $this->no_category($Product->getId()) . $this->get_category_tree_list('0',false,true,$Product->getId()) . '</select>'
					),
					array(
						'addCls' => 'main',
						'text' => '<button type="button" id="moveOptionalRightAddon"><span>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;</span></button>'
					),
					array(
						'addCls' => 'main',
						'attr' => array(
							'id' => 'optionaladdons',
							'valign' => 'top'
						),
						'text' => $addonProducts
					)
				)
			));
		$addonTables .= '<br/>'. $tableOptional->draw();


		return '<div id="tab_' . $this->getExtensionKey() . '">' . $addonTables . '</div>';
	}
}
?>