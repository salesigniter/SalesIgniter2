<?php
class RelatedProductsProductClassImport extends MI_Importable
{

	private $_hasRelated = false;

	private $RelatedProducts = array();

	public function initRelatedProducts() {
		$Qdata = Doctrine_Query::create()
			->select('related_products')
			->from('Products')
			->where('products_id = ?', $this->getId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if ($Qdata && sizeof($Qdata) > 0){
			$Data = $Qdata[0];
			$this->_hasRelated = true;

			$products = explode(',', $Data['related_products']);
			foreach($products as $pID){
				$this->addRelatedProduct($pID);
			}
		}
	}

	public function hasRelatedProducts(){
		return $this->_hasRelated;
	}

	public function addRelatedProduct($id){
		$this->RelatedProducts[] = new Product($id);
	}

	public function getRelatedProducts(){
		return $this->RelatedProducts;
	}
}

/*
	Related Products Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class relatedProducts_admin_products_new_product extends Extension_relatedProducts {

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
		$ProductClass->import(new RelatedProductsProductClassImport);
		$ProductClass->initRelatedProducts();
	}

	public function get_category_tree_list($parent_id = '0', $checked = false, $include_itself = true){
		$langId = Session::get('languages_id');
		
		$catList = '';

		$QCategories = Doctrine_Query::create()
		->from('Categories c')
		->leftJoin('c.CategoriesDescription cd')
		->where('cd.language_id = ?', (int)$langId)
		->andWhere('c.parent_id = ?', (int)$parent_id)
		->orderBy('c.sort_order, cd.categories_name')
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		foreach($QCategories as $categories){
			$catList .= '<optgroup label="' . $categories['CategoriesDescription'][0]['categories_name'] . '">';

			$Qproducts = Doctrine_Query::create()
			->from('Products p')
			->leftJoin('p.ProductsDescription pd')
			->leftJoin('p.ProductsToCategories p2c')
			->where('pd.language_id = ?', (int) $langId)
			->andWhere('p2c.categories_id = ?', $categories['categories_id'])
			->orderBy('pd.products_name')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			foreach($Qproducts as $products){
				$catList .= '<option value="' . $products['products_id'] . '">(' . $products['products_model'] . ") " . $products['ProductsDescription'][0]['products_name'] . '</option>';
			}
			
			if (tep_childs_in_category_count($categories['categories_id']) > 0){
				$catList .= $this->get_category_tree_list($categories['categories_id'], $checked, false);
			}
			$catList .= '</optgroup>';
		}

		return $catList;
	}

	public function NewProductAddTabs(Product $Product, $ProductType, htmlWidget_tabs &$Tabs) {
		if ($ProductType->getCode() == 'standard'){
			$Tabs
				->addTabHeader('tab_' . $this->getExtensionKey(), array('text' => sysLanguage::get('TAB_PAY_RELATED_PRODUCTS')))
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
				array('attr' => array('width' => '40%'), 'text' => sysLanguage::get('TAB_PAY_RELATED_PRODUCTS_TEXT_PRODUCTS')),
				array('text' => '&nbsp;'),
				array('attr' => array('width' => '40%'), 'text' => sysLanguage::get('TAB_PAY_RELATED_PRODUCTS_TEXT_RELATED'))
			)
		));
		
		$relatedProducts = '';
		//print_r($pInfo);
        if ($Product->hasRelatedProducts() === true){
            foreach($Product->getRelatedProducts() as $RelatedProduct){
                $relatedProducts .= '<div><a href="#" class="ui-icon ui-icon-circle-close removeButton"></a><span class="main">' . $RelatedProduct->getName() . '</span>' . tep_draw_hidden_field('related_products[]', $RelatedProduct->getId()) . '</div>';
            }
        }
		$QrelatedGlobal = Doctrine_Query::create()			
			->from('ProductsRelatedGlobal ')
			->where('type = "P"')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	
		if (!empty($QrelatedGlobal)){  
            $relatedG = explode(',', $QrelatedGlobal[0]['related_global']);
			//if($relatedG == '') 
			//$relatedG = $QrelatedGlobal['related_global'];

            foreach($relatedG as $pID){
                $relatedProductsGlobal .= '<div><a href="#" class="ui-icon ui-icon-circle-close removeButton"></a><span class="main">' . tep_get_products_name($pID) . '</span>' . tep_draw_hidden_field('related_productsGlobal[]', $pID) . '</div>';
            }
        }
		
		$table->addBodyRow(array(
			'columns' => array(
				array(
					'addCls' => 'main',
					'attr' => array(
						'valign' => 'top'
					), 
					'text' => '<select size="30" style="width:100%;" id="productList">' . $this->get_category_tree_list() . '</select>'
				),
				array(
					'addCls' => 'main',
					'text' => '<button type="button" id="moveRight"><span>&nbsp;&nbsp;>>&nbsp;&nbsp;</span></button>'.
								'<button type="button" id="moveRightGlobal"><span>&nbsp;&nbsp;Global >>&nbsp;&nbsp;</span></button>'
				),
				array(
					'addCls' => 'main',
					'attr' => array(
						'id' => 'related',
						'valign' => 'top'
					), 
					'text' => $relatedProducts
				),
				array(
					'addCls' => 'main',
					'attr' => array(
						'id' => 'relatedGlobal',
						'valign' => 'top'
					), 
					'text' => $relatedProductsGlobal
				)
			)
		));
		return '<div id="tab_' . $this->getExtensionKey() . '">' . $table->draw() . '</div>';
	}
}
?>