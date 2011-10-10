<?php
/*
	Multi Stores Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class multiStore_admin_products_new_product extends Extension_multiStore
{

	private $selectBox = false;

	private $stockMethodText = 'Store';

	public function __construct() {
		parent::__construct('multiStore');
	}

	public function load() {
		global $appExtension;
		if ($this->enabled === false){
			return;
		}

		EventManager::attachEvents(array(
				'NewProductAddTabs',
				'NewProductSave'
			), null, $this);

		if ($appExtension->isInstalled('inventoryCenters') === false || $appExtension->isEnabled('inventoryCenters') === false){
			EventManager::attachEvents(array(
					'AdminProductListingQueryBeforeExecute',
					'NewProductAddTrackMethods',
					'NewProductAddQuantityRows',
					'NewProductAddBarcodeOptionsHeader',
					'NewProductAddBarcodeOptionsBody',
					'NewProductAddBarcodeListingHeader',
					'NewProductAddBarcodeListingBody',
					'ProductBarcodeNewBeforeExecute',
					'NewProductLoadProductInventory',
					'SaveProductInventoryQuantity',
					'NewProductAddAttributeTrackMethods',
					'NewProductAddAttributeQuantityRows',
					'AdminProductPurchaseTypeOnSave',

					'AdminEditProductLoadInventoryQuantity'
				), null, $this);

			$this->selectBox = htmlBase::newElement('selectbox')
				->addClass('invStore')
				->setName('invStore');
			foreach($this->getStoresArray() as $cInfo){
				$this->selectBox->addOption($cInfo['stores_id'], $cInfo['stores_name']);
			}
		}
	}

	public function allowTableAddition() {
		$add = true;
		/*if (array_key_exists('useCenter', $_POST)){
			$add = ($_POST['useCenter'] == 'true');
		}*/
		return $add;
	}

	public function NewProductSave(&$Product) {
		$ProductsToStores =& $Product->ProductsToStores;
		$ProductsToStores->delete();

		if (isset($_POST['store'])){
			foreach($_POST['store'] as $storeId){
				$ProductsToStores[]->stores_id = $storeId;
			}
		}
	}

	public function NewProductAddTabs(Product $Product, $ProductType, htmlWidget_tabs &$Tabs) {
		if ($ProductType->getCode() == 'standard'){
			$Tabs
				->addTabHeader('tab_' . $this->getExtensionKey(), array('text' => sysLanguage::get('TAB_STORES')))
				->addTabPage('tab_' . $this->getExtensionKey(), array('text' => $this->NewProductTabBody($Product)));
		}
	}

	public function NewProductTabBody(Product &$Product) {
		$contents = '';
		$Qstores = Doctrine_Query::create()
			->from('Stores')
			->orderBy('stores_name');

		if ($Product->getId() > 0){
			$Qproduct = Doctrine_Query::create()
				->from('ProductsToStores')
				->where('products_id = ?', $Product->getId())
				->execute(array(), Doctrine::HYDRATE_ARRAY);

			$curStores = array();
			foreach($Qproduct as $psInfo){
				$curStores[] = $psInfo['stores_id'];
			}
		}
		$Result = $Qstores->execute(array(), Doctrine::HYDRATE_ARRAY);
		foreach($Result as $sInfo){
			$checkbox = htmlBase::newElement('checkbox')
				->setId('store_' . $sInfo['stores_id'])
				->setName('store[]')
				->setValue($sInfo['stores_id'])
				->setLabel($sInfo['stores_name'])
				->setLabelPosition('after')
				->setChecked((isset($curStores) && in_array($sInfo['stores_id'], $curStores)));

			$contents .= $checkbox->draw() . '<br />';
		}
		return '<div id="tab_' . $this->getExtensionKey() . '">' . $contents . '</div>';
	}

	public function AdminProductPurchaseTypeOnSave($PurchaseType, &$data) {
		if ($PurchaseType->getConfigData('INVENTORY_ENABLED') == 'True'){
			if (isset($_POST['use_store']['normal'][$data->type_name])){
				$data->use_store_inventory = '1';
			}
			else {
				$data->use_store_inventory = '0';
			}
		}
	}

	public function SaveProductInventoryQuantity(&$ProductsInventory, $controller, $purchaseType, $qInfo) {
		global $appExtension;
		if (isset($_POST['use_store'][$controller][$purchaseType])){
			if (isset($qInfo['stores'])){
				if ($controller == 'normal'){
					foreach($qInfo['stores'] as $storeID => $qtyInfo){
						$QProductsInventoryQuantity = Doctrine_Query::create()
							->select('quantity_id')
							->from('ProductsInventoryQuantity')
							->where('inventory_id = ?', $ProductsInventory->inventory_id)
							->andWhere('inventory_store_id = ?', $storeID);
						$Result = $QProductsInventoryQuantity->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

						if (!$Result){
							$ProductsInventoryQuantity = new ProductsInventoryQuantity();
							$ProductsInventoryQuantity->inventory_id = $ProductsInventory->inventory_id;
							$ProductsInventoryQuantity->inventory_store_id = $storeID;
						}
						else {
							$ProductsInventoryQuantity = Doctrine_Core::getTable('ProductsInventoryQuantity')
								->find($Result[0]['quantity_id']);
						}
						$ProductsInventoryQuantity->available = $qtyInfo['A'];
						$ProductsInventoryQuantity->save();
					}
				}
				elseif ($controller == 'attribute') {
					foreach($postedQty as $aID_string => $aInfo){
						if (!isset($postedQty[$aID_string][$purchaseType])){
							continue;
						}
						if (!isset($postedQty[$aID_string][$purchaseType]['inventory_centers'])){
							continue;
						}

						$attributePermutations = attributesUtil::permutateAttributesFromString($aID_string);
						foreach($postedQty[$aID_string][$purchaseType]['inventory_centers'] as $centerID => $qtyInfo){
							$QProductsInventoryQuantity = Doctrine_Query::create()
								->select('quantity_id')
								->from('ProductsInventoryQuantity')
								->where('inventory_id = ?', $ProductsInventory->inventory_id)
								->andWhereIn('attributes', $attributePermutations)
								->andWhere('inventory_store_id = ?', $centerID);
							$Result = $QProductsInventoryQuantity->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

							if (!$Result){
								$ProductsInventoryQuantity = new ProductsInventoryQuantity();
								$ProductsInventoryQuantity->inventory_id = $ProductsInventory->inventory_id;
								$ProductsInventoryQuantity->attributes = $aID_string;
								$ProductsInventoryQuantity->inventory_store_id = $centerID;
							}
							else {
								$ProductsInventoryQuantity = Doctrine_Core::getTable('ProductsInventoryQuantity')
									->find($Result[0]['quantity_id']);
							}
							$ProductsInventoryQuantity->available = $qtyInfo['A'];
							$ProductsInventoryQuantity->save();
						}
					}
				}
			}
		}
	}

	public function NewProductLoadProductInventory(&$inventory, &$pInfo, &$productInventory) {
		if (!isset($this->use_center)){
			$this->use_center = array();
		}
		$this->use_center[$inventory['controller']][$inventory['type']] = $inventory['use_center'];
	}

	public function ProductBarcodeNewBeforeExecute(&$Barcode) {
		$invStore = $_POST['invStore'];
		if (!empty($invStore) && $invStore != 'none'){
			$InventoryCenter =& $Barcode->ProductsInventoryBarcodesToStores;
			$InventoryCenter->inventory_store_id = $invStore;
		}
	}

	public function NewProductAddAttributeTrackMethods(&$purchaseType, &$pInfo, &$trackMethodTable) {
		$this->NewProductAddTrackMethods(&$purchaseType, &$pInfo, &$trackMethodTable);
	}

	public function NewProductAddTrackMethods($invController, &$PurchaseType, &$trackMethodTable) {
		$inputName = 'use_store[' . $invController . '][' . $PurchaseType->getCode() . ']';

		$checkbox = htmlBase::newElement('checkbox')
			->setName($inputName)
			->setValue('1')
			->setChecked($PurchaseType->getData('use_store_inventory') == '1')
			->setLabelPosition('after')
			->setLabel('Use Stores');

		$trackMethodTable->addBodyRow(array(
				'columns' => array(
					array('text' => '<br />' . $checkbox->draw())
				)
			));
	}

	public function NewProductAddBarcodeOptionsHeader(&$barcodeTableHeaders) {
		if ($this->allowTableAddition() === false){
			return;
		}

		$barcodeTableHeaders[] = array(
			'addCls' => 'centerAlign main',
			'text' => $this->stockMethodText
		);
	}

	public function NewProductAddBarcodeOptionsBody(&$barcodeTableBody) {
		if ($this->allowTableAddition() === false){
			return;
		}

		$barcodeTableBody[] = array(
			'addCls' => 'centerAlign main',
			'text' => $this->selectBox
		);
	}

	public function NewProductAddBarcodeListingHeader(&$currentBarcodesTableHeaders) {
		if ($this->allowTableAddition() === false){
			return;
		}

		$currentBarcodesTableHeaders[] = array(
			'addCls' => 'ui-widget-content ui-state-default ui-grid-cell',
			'text' => $this->stockMethodText
		);
	}

	public function NewProductAddBarcodeListingBody(&$barcodes, &$currentBarcodesTableBody) {
		if ($this->allowTableAddition() === false){
			return;
		}

		$QinventoryCenter = Doctrine_Query::create()
			->select('s.stores_id as id, s.stores_name as name')
			->from('Stores s')
			->leftJoin('s.ProductsInventoryBarcodesToStores b2s')
			->where('b2s.barcode_id = ?', $barcodes['barcode_id']);

		$Result = $QinventoryCenter->execute(array(), Doctrine::HYDRATE_ARRAY);

		if ($barcodes['status'] == 'R' || $barcodes['status'] == 'O'){
			$colText = ($Result ? $Result[0]['name'] : '&nbsp;');
		}
		else {
			$box = $this->selectBox;
			if ($Result){
				$box->selectOptionByValue($Result[0]['id']);
			}
			$colText = $box;
		}

		$currentBarcodesTableBody[] = array(
			'addCls' => 'ui-widget-content ui-grid-cell centerAlign',
			'text' => $colText
		);
	}

	public function NewProductAddAttributeQuantityRows($settings, $inventoryColumns, &$pInfo, &$quantityTable) {
		$this->NewProductAddQuantityRows($settings, $inventoryColumns, &$pInfo, &$quantityTable);
	}

	public function NewProductAddQuantityRows($PurchaseType, $inventoryColumns, &$quantityTable) {
		if ($this->allowTableAddition() === false){
			return;
		}

		$purchaseTypeCode = $PurchaseType->getCode();
		//$dataSet = $settings['dataSet'];
		//$aID_string = (isset($settings['attributeString']) ? $settings['attributeString'] : null);
		$output = '';

		$quantityTable->addBodyRow(array(
				'columns' => array(
					array(
						'addCls' => 'ui-widget-content ui-state-default ui-grid-cell',
						'colspan' => (sizeof($inventoryColumns) + 1),
						'align' => 'center',
						'text' => '<b>' . $this->stockMethodText . '</b>'
					)
				)
			));

		$totalCols = sizeof($inventoryColumns);
		foreach($this->getStoresArray() as $sInfo){
			$storeId = $sInfo['stores_id'];
			$storeName = $sInfo['stores_name'];

			$rowColumns = array(
				array(
					'addCls' => 'ui-widget-content ui-state-default ui-grid-cell ui-grid-cell-first',
					'text' => '<b>' . $storeName . '</b>'
				)
			);

			$col = 0;
			foreach($inventoryColumns as $short){
				$lastCell = false;
				if ($col == ($totalCols - 1)){
					$lastCell = true;
				}

				$invQty = '0';
				$QinventoryQuantity = Doctrine_Query::create()
					->from('ProductsInventory i')
					->leftJoin('i.ProductsInventoryQuantity iq')
					->where('i.products_id = ?', $PurchaseType->getProductId())
					->andWhere('track_method = ?', 'quantity')
					->andWhere('type = ?', $purchaseTypeCode)
					->andWhere('controller = ?', 'normal');

				EventManager::notify('AdminEditProductLoadInventoryQuantity', $QinventoryQuantity, $storeId);

				$Result = $QinventoryQuantity->execute();
				if ($Result){
					$Quantity = $Result[0]->ProductsInventoryQuantity[0];
					switch($short){
						case 'A': $invQty = $Quantity->available; break;
						case 'O': $invQty = $Quantity->qty_out; break;
						case 'B': $invQty = $Quantity->broken; break;
						case 'R': $invQty = $Quantity->reserved; break;
						case 'P': $invQty = $Quantity->purchased; break;
					}
				}

				//if (is_null($aID_string) === false){
				//	$inputName = 'inventory_quantity[attribute][' . $aID_string . '][' . $purchaseType . '][inventory_centers][' . $centerId . '][' . $short . ']';
				//}
				//else {
				//$inputName = 'inventory_quantity[normal][' . $purchaseTypeCode . '][stores][' . $storeId . '][' . $short . ']';
				//}

				if ($short == 'A'){
					$input = htmlBase::newElement('input')
						->attr('size', '5')
						->setName('inventory_quantity[normal][' . $purchaseTypeCode . '][stores][' . $storeId . '][' . $short . ']')
						->setValue($invQty);

					$colText = '&nbsp;' . $input->draw() . '&nbsp;';
				}
				else {
					$colText = '&nbsp;' . $invQty . '&nbsp;';
				}

				$rowColumns[] = array(
					'addCls' => 'ui-widget-content ui-grid-cell ' . ($lastCell === true ? 'ui-grid-cell-last '
						: '') . 'centerAlign',
					'text' => $colText
				);
				$col++;
			}

			$quantityTable->addBodyRow(array(
					'columns' => $rowColumns
				));
		}
	}

	public function AdminEditProductLoadInventoryQuantity(&$QinventoryQuantity, $storeId = false){
		$QinventoryQuantity->andWhere('inventory_store_id = ?', ($storeId !== false ? $storeId : '0'));
	}
}

?>