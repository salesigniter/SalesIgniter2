<?php
class ProductTypeStandard_admin_products_new_product {

	public function __construct(){

	}

	public function AddPageTabs($Product, &$Tabs){
		global $appExtension;
		
		ob_start();
		require(sysConfig::getDirFsCatalog() . 'admin/applications/products/pages_tabs/tab_purchase_types.php');
		$TabContent = ob_get_contents();
		ob_end_clean();

		$Tabs->addTabHeader('tab_pricing', array('text' => sysLanguage::get('TAB_PURCHASE_TYPES')))
			->addTabPage('tab_pricing', array('text' => $TabContent));

		/*
		ob_start();
		require(sysConfig::getDirFsCatalog() . 'admin/applications/products/pages_tabs/tab_inventory.php');
		$TabContent = ob_get_contents();
		ob_end_clean();

		$Tabs->addTabHeader('tab_inventory', array('text' => sysLanguage::get('TAB_INVENTORY')))
			->addTabPage('tab_inventory', array('text' => $TabContent));
		*/
		
		ob_start();
		require(sysConfig::getDirFsCatalog() . 'admin/applications/products/pages_tabs/tab_box_set.php');
		$TabContent = ob_get_contents();
		ob_end_clean();

		$Tabs->addTabHeader('tab_box_set', array('text' => 'Box Set'))
			->addTabPage('tab_box_set', array('text' => $TabContent));
	}
}