<?php
class rentalProducts_catalog_account_login extends Extension_rentalProducts
{

	public function __construct(){
		parent::__construct();
	}

	public function load(){
		if ($this->isEnabled() === false) return;

		EventManager::attachEvents(array(
			'AccountLoginAddTabs',
		), null, $this);
	}

	public function AccountLoginAddTabs(&$tabsArr){
		$tabsArr['tabNewRentAccount'] = array(
			'heading' => sysLanguage::get('HEADING_NEW_RENTAL_CUSTOMER'),
			'contentFile' => sysConfig::getDirFsCatalog() . 'extensions/rentalProducts/catalog/ext_app/account/pages_tabs/login/rentalCustomer.php'
		);
	}
}
