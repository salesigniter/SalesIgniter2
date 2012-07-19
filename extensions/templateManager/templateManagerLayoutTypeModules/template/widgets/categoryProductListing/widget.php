<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

class TemplateManagerWidgetCategoryProductListing extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('categoryProductListing', false, __DIR__);
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		global $ShoppingCart, $currencies;
		$WidgetProperties = $this->getWidgetProperties();

		$Qcategories = Doctrine_Core::getTable('Categories')
			->findByParentId((int)$WidgetProperties->category_id);
		$catList = '<ul>';
		foreach($Qcategories as $Category){
			$catList .= '<li>';
			$catList .= '<a href="' . itw_app_link('cPath=' . $Category->categories_id, 'index', 'default') . '">' .
				$Category->CategoriesDescription[Session::get('languages_id')]->categories_name .
			'</a>';
			if ($Category->ProductsToCategories->count() > 0 || $Category->Children->count() > 0){
				if (
					$Category->ProductsToCategories->count() <= $WidgetProperties->max_products ||
					$WidgetProperties->when_max_products == 'limit_products'
				){
					$catList .= '<ul>';
					$maxProducts = $WidgetProperties->max_products;
					foreach($Category->ProductsToCategories as $ProductToCategory){
						$Product = $ProductToCategory->Products;
						if ($Product->products_status == 1){
							$catList .= '<li>' .
								'<a href="' . itw_app_link('products_id=' . $Product->products_id, 'product', 'info') . '">' .
								$Product->ProductsDescription[Session::get('languages_id')]->products_name .
								'</a>' .
								'</li>';
							$maxProducts--;
							if ($maxProducts == 0){
								break;
							}
						}
					}
					$catList .= '</ul>';
				}
				else {
					$catList .= '<ul>';
					foreach($Category->Children as $SubCategory){
						$catList .= '<li>' .
							'<a href="' . itw_app_link('cPath=' . $SubCategory->parent_id . '_' . $SubCategory->categories_id, 'index', 'default') . '">' .
							$SubCategory->CategoriesDescription[Session::get('languages_id')]->categories_name .
							'</a>' .
							'</li>';
					}
					$catList .= '</ul>';
				}
			}
			$catList .= '</li>';
		}
		$catList .= '</ul>';

		$this->setBoxContent($catList);

		return $this->draw();
	}
}

?>