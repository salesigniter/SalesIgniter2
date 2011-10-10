<?php
class ProductTypePackage_packages_admin_products_new_product extends ProductTypePackage_admin_products_new_product{

	public function __construct(){

	}

	public function AddPageTabs($Product, &$Tabs){
		global $appExtension;
		echo 'kkkk';
		itwExit();
		ob_start();
		require(sysConfig::getDirFsCatalog() . 'extensions/packages/productTypeModules/package/admin/applications/products/pages_tabs/tab_build_package.php');
		$TabContent = ob_get_contents();
		ob_end_clean();

		$Tabs->addTabHeader('tab_pricing', array('text' => sysLanguage::get('TAB_PURCHASE_TYPES')))
			->addTabPage('tab_pricing', array('text' => $TabContent));
	}
}