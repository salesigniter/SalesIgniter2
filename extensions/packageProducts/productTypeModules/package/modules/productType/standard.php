<?php
class PackageProductTypeStandard {

	private static $PurchaseTypes = array();

	private static function getPurchaseTypes(){
		if (empty(self::$PurchaseTypes)){
			$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/modules/purchaseType/');
			foreach($Dir as $File){
				if ($File->isDot() || $File->isDir()){
					continue;
				}
				require($File->getPathName());
				$className = 'PackagePurchaseType' . ucfirst($File->getBasename('.php'));

				self::$PurchaseTypes[] = $className;
			}
		}
		return self::$PurchaseTypes;
	}

	public static function getPackagedTableHeaders(){
		$return = array(
			array('id' => 'purchase_type', 'text' => 'Purchase Type')
		);

		foreach(self::getPurchaseTypes() as $PurchaseType){
			if (method_exists($PurchaseType, 'getPackagedTableHeaders')){
				$return = array_merge($return, $PurchaseType::getPackagedTableHeaders());
			}
		}
		return $return;
	}

	public static function getPackagedTableBody($pInfo){
		$return = array(
			array('text' => '<input type="hidden" name="package_product_settings[' . $pInfo['id'] . '][purchase_type]" value="' . $pInfo['packageData']->purchase_type . '">' . $pInfo['packageData']->purchase_type)
		);

		foreach(self::getPurchaseTypes() as $PurchaseType){
			if (method_exists($PurchaseType, 'getPackagedTableBody')){
				$return = array_merge($return, $PurchaseType::getPackagedTableBody($pInfo));
			}
		}
		return $return;
	}

	public static function getSettingsAddToPackage(Product $Product){
		$SelectBox = htmlBase::newElement('selectbox')
			->setName('settings[' . $Product->getId() . '][purchase_type]')
			->attr('onchange', '$(\'.purchaseTypeSettings\').show().not(\'.purchaseTypeSettings_\' + $(this).find(\'option:selected\').val()).hide()')
			->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));

		$PurchaseTypeFields = array();
		$PurchaseTypes = $Product->getProductTypeClass()->getPurchaseTypes();
		foreach($PurchaseTypes as $PurchaseType){
			$SelectBox->addOption($PurchaseType->getCode(), $PurchaseType->getTitle());

			$moduleFile = sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/modules/purchaseType/' . $PurchaseType->getCode() . '.php';
			if (file_exists($moduleFile)){
				require($moduleFile);
				$PurchaseTypeTable = htmlBase::newElement('table')
					->addClass('purchaseTypeSettings purchaseTypeSettings_' . $PurchaseType->getCode())
					->setCellPadding(3)
					->setCellSpacing(0)
					->hide();

				$className = 'PackagePurchaseType' . ucfirst($PurchaseType->getCode());
				foreach($className::getSettingsAddToPackage($PurchaseType) as $fInfo){
					if (isset($fInfo['colspan'])){
						$PurchaseTypeTable->addBodyRow(array(
							'columns' => array(
								array('colspan' => $fInfo['colspan'], 'text' => $fInfo['label'])
							)
						));
					}else{
						$PurchaseTypeTable->addBodyRow(array(
							'columns' => array(
								array('text' => $fInfo['label'] . ':'),
								array('text' => $fInfo['field'])
							)
						));
					}
				}
				$PurchaseTypeTables[] = $PurchaseTypeTable->draw();
			}
		}

		$Fields = array_merge(array(
			array(
				'label' => 'Purchase Type',
				'field' => $SelectBox->draw() . implode('', $PurchaseTypeTables)
			)
		), $PurchaseTypeFields);

		return $Fields;
	}

	public static function addPackageRowData(Product $Product, $sInfo, &$NewRow){
		$moduleFile = sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/modules/purchaseType/' . $sInfo['purchase_type'] . '.php';
		$className = 'PackagePurchaseType' . ucfirst($sInfo['purchase_type']);
		$addDefaultData = true;
		if (file_exists($moduleFile)){
			if (!class_exists($className)){
				require($moduleFile);
			}
			if (method_exists($className, 'addPackageRowData')){
				$PurchaseType = $Product->getProductTypeClass()->getPurchaseType($sInfo['purchase_type']);
				$className::addPackageRowData($PurchaseType, $sInfo, &$NewRow);
				$addDefaultData = false;
			}
		}

		if ($addDefaultData === true){
			$NewRow['columns'][] = array(
				'text' => (isset($sInfo['override_price']) ? '<input type="hidden" name="package_product_settings[' . $Product->getId() . '][price]" value="' . $sInfo['price'] . '">' . $sInfo['price'] : 'Products Current Price')
			);
		}

		$NewRow['columns'][] = array(
			'text' => '<input type="hidden" name="package_product_settings[' . $Product->getId() . '][purchase_type]" value="' . $sInfo['purchase_type'] . '">' . $sInfo['purchase_type']
		);
	}
}