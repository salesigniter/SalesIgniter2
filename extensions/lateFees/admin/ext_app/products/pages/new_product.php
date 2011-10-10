<?php
class LateFees_admin_products_new_product extends Extension_lateFees
{

	public function __construct() {
		parent::__construct('lateFees');
	}

	public function load() {
		if ($this->enabled === false) {
			return;
		}

		EventManager::attachEvents(array(
				'AdminProductEditAddPurchaseTypeSettingsTab',
				'AdminProductPurchaseTypeOnSave'
			), null, $this);
	}

	public function AdminProductEditAddPurchaseTypeSettingsTab(&$Sorted, $purchaseType) {
		if ($purchaseType->getConfigData('LATE_FEES_ENABLED') == 'True'){
			$baseClassName = 'PurchaseTypeSettingsTab';
			$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/lateFees/admin/ext_app/products/settingsTabs/');
			foreach($Dir as $d){
				if ($d->isDot() || $d->isDir()) {
					continue;
				}

				$ClassName = $baseClassName . '_' . $d->getBasename('.php');
				if (!class_exists($ClassName)){
					require($d->getPathname());
				}
				$ClassObj = new $ClassName;
				$Sorted[] = $ClassObj;
			}
		}
	}

	public function AdminProductPurchaseTypeOnSave(&$PurchaseTypeObj){
		$pType = $PurchaseTypeObj->type_name;
		if (isset($_POST['late_fee'][$pType]) && !empty($_POST['late_fee'][$pType])){
			$PurchaseTypeObj->late_fee = $_POST['late_fee'][$pType];
			$PurchaseTypeObj->late_fee_calculation = $_POST['late_fee_calculation'][$pType];
		}
	}
}