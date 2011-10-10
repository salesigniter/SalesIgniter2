<?php
class ProductTypeMembership_admin_products_new_product {

	public function __construct(){

	}

	public function AddPageTabs($Product, &$Tabs){
		global $tax_class_array;
		ob_start();
		require(sysConfig::getDirFsCatalog() . 'includes/modules/productTypeModules/membership/admin/applications/products/pages_tabs/tab_pricing.php');
		$TabContent = ob_get_contents();
		ob_end_clean();

		$Tabs->addTabHeader('tab_rental_membership', array('text' => sysLanguage::get('TAB_PRICING')))
			->addTabPage('tab_rental_membership', array('text' => $TabContent));
	}
}