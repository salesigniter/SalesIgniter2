<?php
class ProductTypePackage_admin_products_new_product{

	public function __construct(){
		sysLanguage::loadDefinitions(
			sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/admin/applications/products/language_defines/global.xml'
		);
	}

	public function AddPageTabs($Product, &$Tabs){
		ob_start();
		require(sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/admin/applications/products/pages_tabs/tab_products.php');
		$TabContent = ob_get_contents();
		ob_end_clean();

		$Tabs->addTabHeader('tab_package_products', array('text' => sysLanguage::get('TAB_PACKAGE_PRODUCTS')))
			->addTabPage('tab_package_products', array('text' => $TabContent));

	}
}