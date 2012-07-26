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
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @var int
	 */
	protected $products_id = 0;

	/**
	 * @var int
	 */
	protected $products_tax = 0;

	/**
	 * @var int
	 */
	protected $products_tax_class_id = 0;

	/**
	 * @var float
	 */
	protected $products_price = 0.0000;

	/**
	 * @var int
	 */
	protected $products_quantity = 0;

	/**
	 * @var string
	 */
	protected $products_model = '';

	/**
	 * @var string
	 */
	protected $products_name = '';

	/**
	 * @var int
	 */
	protected $products_weight = 0;

	/**
	 * @var string
	 */
	protected $products_type = '';

	/**
	 * @var array
	 */
	protected $inventory = array();

	/**
	 * @var ProductTypeBase
	 */
	protected $ProductTypeClass;

	/**
	 * @var array|null
	 */
	protected $extraInfo = array();

	/**
	 *
	 */
	public function __construct()
	{
		$this->regenerateId();
	}

	public function loadProductBaseInfo($productId)
	{
		$Product = Doctrine_Core::getTable('Products')
			->find((int)$productId);

		$this->products_id = (int)$Product->products_id;
		$this->products_tax = 0;
		$this->products_tax_class_id = (int)$Product->products_tax_class_id;
		$this->products_quantity = 0;
		$this->products_model = $Product->products_model;
		$this->products_name = $Product->ProductsDescription[sysLanguage::getId()]->products_name;
		$this->products_weight = (int)$Product->products_weight;
		$this->products_type = $Product->products_type;
	}

	public function loadProductType()
	{
		ProductTypeModules::$classPrefix = 'OrderProductType';
		$isLoaded = ProductTypeModules::loadModule(
			$this->products_type,
			sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/ProductTypeModules/' . $this->products_type . '/'
		);
		if ($isLoaded === true){
			$this->ProductTypeClass = ProductTypeModules::getModule($this->products_type);
			if ($this->ProductTypeClass === false){
				echo '<pre>';
				debug_print_backtrace();
				echo '</pre>';
				die('Error loading product type: ' . $this->products_type);
			}
			$this->ProductTypeClass->setProductId($this->products_id);
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
		return (int)$this->products_id;
	}

	/**
	 * @return float
	 */
	public function getTaxRate()
	{
		return $this->products_tax;
	}

	/**
	 * @return int
	 */
	public function getTaxClassId()
	{
		return (int)$this->products_tax_class_id;
	}

	/**
	 * @return int
	 */
	public function getQuantity()
	{
		return (int)$this->products_quantity;
	}

	/**
	 * @return string
	 */
	public function getModel()
	{
		return (string)$this->products_model;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return (string)$this->products_name;
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
		return (isset($this->extraInfo['Barcodes']));
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
			$barcode = $ProductType->getOrderedProductBarcodes($this->extraInfo);
		}
		return (string)$barcode;
	}

	/**
	 * @param bool $wQty
	 * @param bool $wTax
	 * @return float
	 */
	public function getPrice($wQty = false, $wTax = false)
	{
		$price = $this->products_price;
		if ($wQty === true){
			$price *= $this->getQuantity();
		}

		if ($wTax === true){
			$Tax = $price * $this->getTaxRate() / 100;
			$price += $Tax;
		}
		return (float)$price;
	}

	/**
	 * @return float
	 */
	public function getWeight()
	{
		return (float)($this->products_weight * $this->getQuantity());
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
		return (isset($this->extraInfo[$key]));
	}

	/**
	 * @param null $key
	 * @return array|bool|null
	 */
	public function getInfo($key = null)
	{
		if (is_null($key)){
			return $this->extraInfo;
		}
		else {
			if (isset($this->extraInfo[$key])){
				return $this->extraInfo[$key];
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
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'hasEnoughInventory')){
			$return = $ProductType->hasEnoughInventory($Qty);
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
		$ProductType = $this->getProductTypeClass();

		/**
		 * This is only for core fields, all possible module/extension fields should
		 * either be in their own table or in the provided product_json array
		 */
		$SaleProduct->product_id = $this->getProductsId();
		$SaleProduct->products_model = $this->getModel();
		$SaleProduct->products_name = $this->getName();
		$SaleProduct->products_price = $this->getFinalPrice();
		$SaleProduct->products_tax = $this->getTaxRate();
		$SaleProduct->products_tax_class_id = $this->getTaxClassId();
		$SaleProduct->products_quantity = $this->getQuantity();
		$SaleProduct->products_weight = $this->getWeight();
		$SaleProduct->products_type = $ProductType->getCode();

		if (method_exists($ProductType, 'onSaveSale')){
			$ProductType->onSaveSale($this, $SaleProduct, $AssignInventory);
		}

		$SaleProduct->product_json = $this->prepareSave();
	}

	/**
	 * @return array
	 */
	public function prepareSave()
	{
		$toEncode = array(
			'id'                    => $this->id,
			'product_id'            => $this->getProductsId(),
			'products_model'        => $this->getModel(),
			'products_name'         => $this->getName(),
			'products_price'        => $this->getPrice(),
			'products_tax'          => $this->getTaxRate(),
			'products_tax_class_id' => $this->getTaxClassId(),
			'products_quantity'     => $this->getQuantity(),
			'products_weight'       => $this->getWeight(),
			'products_type'         => $this
				->getProductTypeClass()
				->getCode(),
			'extra_info'            => $this->extraInfo
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
		$this->id = $Product->id;
		$this->product_id = $Product->product_id;
		$this->products_model = $Product->products_model;
		$this->products_name = $Product->products_name;
		$this->products_price = $Product->products_price;
		$this->products_tax = $Product->products_tax;
		$this->products_tax_class_id = $Product->products_tax_class_id;
		$this->products_quantity = $Product->products_quantity;
		$this->products_weight = $Product->products_weight;
		$this->products_type = $Product->products_type;

		if (is_array($Product->product_json) && empty($Product->product_json) === false){
			$this->extraInfo = $Product->product_json;

			/**
			 * @TODO: Temporary until i can make things come from the right places
			 */
			if (isset($this->extraInfo['ReservationInfo'])){
				$this->extraInfo['ReservationInfo']['start_date'] = SesDateTime::createFromArray($this->extraInfo['ReservationInfo']['start_date']);
				$this->extraInfo['ReservationInfo']['end_date'] = SesDateTime::createFromArray($this->extraInfo['ReservationInfo']['end_date']);
			}

			/*$this->inventory = array();
			if ($Product->hasRelation('SaleInventory') && $Product->SaleInventory->count() > 0){
				foreach($Product->SaleInventory as $Inventory){
					if ($Inventory->serial_number != ''){
						$BarcodeInfo = $Inventory->Serial;
						$this->inventory[] = array(
							'serial_number' => $BarcodeInfo->serial_number
						);
					}
				}
			}*/

			//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($this->inventory);

			//$this->loadProductBaseInfo($this->products_id);
			$this->loadProductType();
			if (method_exists($this->ProductTypeClass, 'loadDatabaseData')){
				$this->ProductTypeClass->loadDatabaseData($Product, $this->extraInfo['ProductTypeJson']);
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
