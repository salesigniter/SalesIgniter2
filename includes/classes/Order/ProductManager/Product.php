<?php
/**
 * Product for the order product manager
 *
 * @package   Order\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderProduct
{

	/**
	 * @var array|null
	 */
	protected $pInfo = array(
		'products_id'           => 0,
		'products_tax'          => 0,
		'products_tax_class_id' => 0,
		'products_quantity'     => 0,
		'products_model'        => '',
		'products_name'         => '',
		'products_weight'       => 0,
		'products_type'         => ''
	);

	/**
	 * @var array
	 */
	protected $inventory = array();

	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @var ProductTypeBase
	 */
	protected $ProductTypeClass;

	/**
	 * @param array|null $pInfo
	 */
	public function __construct(array $pInfo = null)
	{
		$this->regenerateId();
	}

	public function loadProductBaseInfo($productId)
	{
		$Product = Doctrine_Core::getTable('Products')
			->find((int)$productId);

		if ($this->pInfo['products_id'] == 0){
			$this->pInfo['products_id'] = (int)$Product->products_id;
		}
		if ($this->pInfo['products_tax'] == 0){
			$this->pInfo['products_tax'] = 0;
		}
		if ($this->pInfo['products_tax_class_id'] == 0){
			$this->pInfo['products_tax_class_id'] = (int)$Product->products_tax_class_id;
		}
		if ($this->pInfo['products_quantity'] == 0){
			$this->pInfo['products_quantity'] = (int)0;
		}
		if ($this->pInfo['products_model'] == ''){
			$this->pInfo['products_model'] = $Product->products_model;
		}
		if ($this->pInfo['products_name'] == ''){
			$this->pInfo['products_name'] = $Product->ProductsDescription[Session::get('languages_id')]->products_name;
		}
		if ($this->pInfo['products_weight'] == ''){
			$this->pInfo['products_weight'] = (int)$Product->products_weight;
		}
		if ($this->pInfo['products_type'] == ''){
			$this->pInfo['products_type'] = $Product->products_type;
		}
	}

	public function loadProductType()
	{
		$ProductType = $this->pInfo['products_type'];
		ProductTypeModules::$classPrefix = 'OrderProductType';
		$isLoaded = ProductTypeModules::loadModule(
			$ProductType,
			sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/ProductTypeModules/' . $ProductType . '/'
		);
		if ($isLoaded === true){
			$this->ProductTypeClass = ProductTypeModules::getModule($ProductType);
			if ($this->ProductTypeClass === false){
				echo '<pre>';
				debug_print_backtrace();
				echo '</pre>';
				die('Error loading product type: ' . $ProductType);
			}
			$this->ProductTypeClass->setProductId($this->pInfo['products_id']);
		}
	}

	/**
	 * @return ProductTypeBase
	 */
	public function &getProductTypeClass()
	{
		return $this->ProductTypeClass;
	}

	/**
	 *
	 */
	public function regenerateId()
	{
		$this->id = tep_rand(5555, 99999);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getProductsId()
	{
		return (int)$this->pInfo['products_id'];
	}

	/**
	 * @return float
	 */
	public function getTaxRate()
	{
		return (float)$this->pInfo['products_tax'];
	}

	/**
	 * @return int
	 */
	public function getTaxClassId()
	{
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'getTaxClassId')){
			$return = $ProductType->getTaxClassId();
		}
		else {
			$return = $this->pInfo['products_tax_class_id'];
		}
		return (int)$return;
	}

	/**
	 * @return int
	 */
	public function getQuantity()
	{
		return (int)$this->pInfo['products_quantity'];
	}

	/**
	 * @return string
	 */
	public function getModel()
	{
		return (string)$this->pInfo['products_model'];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return (string)$this->pInfo['products_name'];
	}

	/**
	 * @return bool
	 */
	public function hasBarcode()
	{
		return ($this->getBarcode() !== false);
	}

	/**
	 * @return bool
	 */
	public function hasBarcodes()
	{
		return (isset($this->pInfo['Barcodes']));
	}

	/**
	 * @param string $separator
	 * @return string
	 */
	public function displayBarcodes($separator = '<br>')
	{
		$return = array();
		foreach($this->inventory as $BarcodeInfo){
			$return[] = $BarcodeInfo['barcode'];
		}
		$return = implode($separator, $return);

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'displayOrderedProductBarcodes')){
			$return .= $ProductType->displayOrderedProductBarcodes($this);
		}
		return $return;
	}

	/**
	 * @return string
	 */
	public function getBarcodes()
	{
		$barcode = '';

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'getOrderedProductBarcodes')){
			$barcode = $ProductType->getOrderedProductBarcodes($this->pInfo);
		}
		return (string)$barcode;
	}

	/**
	 * @param bool $wQty
	 * @param bool $wTax
	 * @return float
	 */
	public function getFinalPrice($wQty = false, $wTax = false)
	{
		$price = $this->pInfo['final_price'];
		if ($wQty === true){
			$price *= $this->getQuantity();
		}

		if ($wTax === true){
			$price = tep_add_tax($price, $this->getTaxRate());
		}
		return (float)$price;
	}

	/**
	 * @param bool $wTax
	 * @return float
	 */
	public function getPrice($wTax = false)
	{
		$price = $this->pInfo['products_price'];

		if ($wTax === true){
			$price = tep_add_tax($price, $this->getTaxRate());
		}
		return (float)$price;
	}

	/**
	 * @return float
	 */
	public function getWeight()
	{
		return (float)($this->pInfo['products_weight'] * $this->getQuantity());
	}

	/**
	 * @return array
	 */
	private function getTaxAddressInfo()
	{
		global $order, $userAccount;
		$zoneId = null;
		$countryId = null;
		if (is_object($order)){
			$taxAddress = $userAccount->plugins['addressBook']->getAddress($order->taxAddress);
			$zoneId = $taxAddress['entry_zone_id'];
			$countryId = $taxAddress['entry_country_id'];
		}
		return array(
			'zoneId'    => $zoneId,
			'countryId' => $countryId
		);
	}

	/**
	 * @param bool $showExtraInfo
	 * @return string
	 */
	public function getNameHtml($showExtraInfo = true)
	{
		$nameHref = htmlBase::newElement('a')
			->setHref(itw_catalog_app_link('products_id=' . $this->getProductsId(), 'product', 'info'))
			->css(array(
			'font-weight' => 'bold'
		))
			->attr('target', '_blank')
			->html($this->getName());

		$name = $nameHref->draw() .
			'<br />' .
			$this
				->getProductTypeClass()
				->showProductInfo($showExtraInfo);

		$Result = EventManager::notifyWithReturn('OrderProductAfterProductName', $this, $showExtraInfo);
		foreach($Result as $html){
			$name .= $html;
		}

		return (string)$name;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function hasInfo($key)
	{
		return (isset($this->pInfo[$key]));
	}

	/**
	 * @param null $key
	 * @return array|bool|null
	 */
	public function getInfo($key = null)
	{
		if (is_null($key)){
			return $this->pInfo;
		}
		else {
			if (isset($this->pInfo[$key])){
				return $this->pInfo[$key];
			}
			else {
				return null;
			}
		}
	}

	/**
	 * @param int $Qty
	 * @return bool
	 */
	public function hasEnoughInventory($Qty = 1)
	{
		//echo __FILE__ . '::' . __LINE__ . '::CHECKING QTY::' . $Qty . "\n";
		$return = true;
		if (method_exists($this->ProductTypeClass, 'hasEnoughInventory')){
			$return = $this->ProductTypeClass->hasEnoughInventory($Qty);
		}
		return $return;
	}

	/**
	 * Used to save the sale to the database
	 *
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $SaleProduct
	 * @param bool                                                                    $AssignInventory
	 */
	public function onSaveSale(&$SaleProduct, $AssignInventory = false)
	{
		if (method_exists($this->ProductTypeClass, 'onSaveSale')){
			//echo __FILE__ . '::' . __LINE__ . '<br>';
			$this->ProductTypeClass->onSaveSale($SaleProduct, $AssignInventory);
		}

		$SaleProduct->product_json = $this->prepareSave();
	}

	/**
	 * @return array
	 */
	public function prepareSave()
	{
		$toEncode = array(
			'id'    => $this->id,
			'pInfo' => $this->pInfo
		);

		$ProductTypeClass = $this->getProductTypeClass();
		if (method_exists($ProductTypeClass, 'prepareSave')){
			$toEncode['ProductTypeJson'] = $ProductTypeClass->prepareSave();
		}
		return $toEncode;
	}

	/**
	 * Used when loading the sale from the database
	 *
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $Product
	 */
	public function loadDatabaseData($Product)
	{
		if (is_array($Product->product_json) && empty($Product->product_json) === false){
			$this->id = $Product->product_json['id'];
			$this->pInfo = $Product->product_json['pInfo'];

			/**
			 * @TODO: Temporary until i can make things come from the right places
			 */
			if (isset($this->pInfo['ReservationInfo'])){
				$this->pInfo['ReservationInfo']['start_date'] = SesDateTime::createFromArray($this->pInfo['ReservationInfo']['start_date']);
				$this->pInfo['ReservationInfo']['end_date'] = SesDateTime::createFromArray($this->pInfo['ReservationInfo']['end_date']);
			}

			$this->inventory = array();
			if ($Product->hasRelation('SaleInventory') && $Product->SaleInventory->count() > 0){
				foreach($Product->SaleInventory as $Inventory){
					if ($Inventory->barcode_id > 0){
						$BarcodeInfo = $Inventory->Barcode;
						$this->inventory[] = array(
							'barcode_id' => $BarcodeInfo->barcode_id,
							'barcode'    => $BarcodeInfo->barcode,
							'status'     => $BarcodeInfo->status
						);
					}
				}
			}

			//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($this->inventory);

			$this->loadProductBaseInfo($this->pInfo['products_id']);
			$this->loadProductType();
			if (method_exists($this->ProductTypeClass, 'loadDatabaseData')){
				$this->ProductTypeClass->loadDatabaseData($Product, $Product->product_json['ProductTypeJson']);
			}
		}
	}

	public function onGetEmailList(&$orderedProducts)
	{
		if (method_exists($this->ProductTypeClass, 'onGetEmailList')){
			$this->ProductTypeClass->onGetEmailList(&$orderedProducts);
		}
	}

	public function onExport($addColumns, &$CurrentRow, &$HeaderRow, $i)
	{
		if ($addColumns['v_products'] === true){
			if ($HeaderRow->hasColumn('v_products_name_' . $i) === false){
				$HeaderRow->addColumn('v_products_name_' . $i);
			}
			if ($HeaderRow->hasColumn('v_products_model_' . $i) === false){
				$HeaderRow->addColumn('v_products_model_' . $i);
			}
			if ($HeaderRow->hasColumn('v_products_price_' . $i) === false){
				$HeaderRow->addColumn('v_products_price_' . $i);
			}
			if ($HeaderRow->hasColumn('v_products_tax_' . $i) === false){
				$HeaderRow->addColumn('v_products_tax_' . $i);
			}
			if ($HeaderRow->hasColumn('v_products_finalprice_' . $i) === false){
				$HeaderRow->addColumn('v_products_finalprice_' . $i);
			}
			if ($HeaderRow->hasColumn('v_products_qty_' . $i) === false){
				$HeaderRow->addColumn('v_products_qty_' . $i);
			}
			if ($HeaderRow->hasColumn('v_products_barcode_' . $i) === false){
				$HeaderRow->addColumn('v_products_barcode_' . $i);
			}
			$CurrentRow->addColumn($this->getName(), 'v_products_name_' . $i);
			$CurrentRow->addColumn($this->getModel(), 'v_products_model_' . $i);
			$CurrentRow->addColumn($this->getPrice(), 'v_products_price_' . $i);
			$CurrentRow->addColumn($this->getTaxRate(), 'v_products_tax_' . $i);
			$CurrentRow->addColumn($this->getFinalPrice(), 'v_products_finalprice_' . $i);
			$CurrentRow->addColumn($this->getQuantity(), 'v_products_qty_' . $i);
			$CurrentRow->addColumn($this->displayBarcodes(), 'v_products_barcode_' . $i);
		}
	}
}
