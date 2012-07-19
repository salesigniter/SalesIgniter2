<?php
if (class_exists('ProductTypePackage') === false){
	require(sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/module.php');
}

/**
 * Package product type for the order class
 *
 * @package    Order\ProductManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      2.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
 */

class OrderProductTypePackage extends ProductTypePackage
{

	/**
	 * @var array
	 */
	protected $PackagedProducts = array();

	/**
	 * @var array
	 */
	protected $pInfo = array();

	/**
	 * @param $k
	 * @return mixed
	 */
	public function getInfo($k = null)
	{
		return ($k === null ? $this->pInfo : $this->pInfo[$k]);
	}

	/**
	 * @param      $k
	 * @param null $v
	 */
	public function setInfo($k, $v = null)
	{
		if ($v === null){
			$this->pInfo = $k;
		}
		else {
			$this->pInfo[$k] = $v;
		}
	}

	/**
	 * @param $k
	 * @return bool
	 */
	public function hasInfo($k)
	{
		return isset($this->pInfo[$k]);
	}

	/**
	 * @return OrderProduct
	 */
	public function getContentPackagedProductClass()
	{
		return new OrderProduct();
	}

	/**
	 * @return array OrderProduct[]
	 */
	public function hasPackagedProducts()
	{
		return (empty($this->PackagedProducts) === false);
	}

	/**
	 * @return OrderProduct[] array
	 */
	public function getPackagedProducts()
	{
		return $this->PackagedProducts;
	}

	/**
	 * @param OrderProduct $PackagedProduct
	 */
	public function addPackagedProduct(OrderProduct $PackagedProduct)
	{
		$this->PackagedProducts[] = $PackagedProduct;
	}

	/**
	 * @param bool $showExtraInfo
	 * @return string
	 */
	public function showProductInfo($showExtraInfo = true)
	{
		$PackageHtml = '';
		foreach($this->getPackagedProducts() as $PackageProduct){
			$ProductType = $PackageProduct->getProductTypeClass();
			$pInfoHtml = htmlBase::newElement('span')
				->css(array(
				'font-size'  => '.8em',
				'font-style' => 'italic'
			))
				->html(' - ' . $PackageProduct->getQuantity() . 'x ' . $PackageProduct->getName());

			$PackageHtml .= $pInfoHtml->draw() .
				'<div style="margin-left:10px;">' .
				$ProductType->showProductInfo($showExtraInfo);

			$Result = EventManager::notifyWithReturn('OrderProductAfterProductName', $PackageProduct, $showExtraInfo);
			foreach($Result as $html){
				$PackageHtml .= $html;
			}
			$PackageHtml .= '</div>';
		}
		return $PackageHtml;
	}

	/**
	 * @TODO: Figure out something better
	 *
	 * @param int $priceTime
	 * @return int
	 */
	public function getReservationPrice($priceTime)
	{
		global $currencies;

		foreach($this->getPackagedProducts() as $PackagedProduct){
			$PackageData = $PackagedProduct->getInfo('PackageData');
			$Quantity = $PackagedProduct->getQuantity();

			$ProductType = $PackagedProduct->getProductTypeClass();
			if (isset($PackageData['purchase_type']) && $PackageData['purchase_type'] == 'reservation'){
				$pprId = $ProductType
					->getPurchaseTypeClass()
					->getPayPerRentalId();
				foreach(PurchaseType_reservation_utilities::getRentalPricing($pprId) as $priceInfo){
					if (
						isset($PackageData['price']) &&
						isset($PackageData['price'][$pprId]) &&
						isset($PackageData['price'][$pprId][$priceInfo['pay_per_rental_types_id']]) &&
						isset($PackageData['price'][$pprId][$priceInfo['pay_per_rental_types_id']][$priceInfo['number_of']])
					){
						$price = $PackageData['price'][$pprId][$priceInfo['pay_per_rental_types_id']][$priceInfo['number_of']];
					}
					else {
						$price = $priceInfo['price'];
					}
					//$partName = $priceInfo['Description'][0]['price_per_rental_per_products_name'];
					$partName = $priceInfo['Type']['minutes'];
					if (!isset($prices[$partName])){
						$prices[$partName] = 0;
					}
					$prices[$partName] += $price;
				}
			}
		}

		return (isset($prices[$priceTime]) ? $prices[$priceTime] : 0);
	}

	/**
	 *
	 */
	public function onSetQuantity()
	{
		if ($this->hasPackagedProducts() === true){
			foreach($this->getPackagedProducts() as $PackageProduct){
				$PackageData = $PackageProduct->getInfo('PackageData');
				$PackageProduct->setQuantity($PackageData['quantity'] * $OrderProduct->getQuantity());
			}
		}
	}

	/**
	 * @param int $Qty
	 * @return bool
	 */
	public function hasEnoughInventory($Qty = 1)
	{
		$return = true;
		if ($this->hasPackagedProducts() === true){
			foreach($this->getPackagedProducts() as $PackageProduct){
				$PackageData = $PackageProduct->getInfo('PackageData');
				//echo __FILE__ . '::' . __LINE__ . '<pre>' . $Qty . ' * ' . $PackageData['quantity'] . '::';print_r($PackageData);
				$return = $PackageProduct->hasEnoughInventory($Qty * $PackageData['quantity']);

				if ($return === false){
					break;
				}
			}
		}
		return $return;
	}

	/**
	 * @param AccountsReceivableSalesProducts $SaleProduct
	 * @param bool                            $AssignInventory
	 */
	public function onSaveSale(&$SaleProduct, $AssignInventory = false)
	{
		foreach($this->getPackagedProducts() as $PackagedProduct){
			$PackageProduct = $SaleProduct->Packaged
				->getTable()
				->getRecord();
			$PackagedProduct->onSaveSale($PackageProduct, $AssignInventory);
			$SaleProduct->Packaged->add($PackageProduct);
		}
	}

	/**
	 * @return array
	 */
	public function prepareSave()
	{
		$toEncode = $this->getInfo();
		if ($this->hasPackagedProducts()){
			foreach($this->getPackagedProducts() as $k => $PackagedProduct){
				$toEncode['PackagedProducts'][$k] = $PackagedProduct->prepareSave();
			}
		}
		return $toEncode;
	}

	/**
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $Product
	 * @param array                                                                   $ProductTypeJson
	 */
	public function loadDatabaseData($Product, $ProductTypeJson)
	{
		$this->setInfo($ProductTypeJson);

		/*
		 * @TODO: Figure out how to put this into the payPerRentals extension
		 */
		if ($this->hasInfo('ReservationInfo') === true){
			$ReservationInfo = $this->getInfo('ReservationInfo');

			$ReservationInfo['start_date'] = SesDateTime::createFromArray($ReservationInfo['start_date']);
			$ReservationInfo['end_date'] = SesDateTime::createFromArray($ReservationInfo['end_date']);

			$this->setInfo('ReservationInfo', $ReservationInfo);
		}

		//echo __FILE__ . '::' . __LINE__ . '<pre>' . "\n";print_r($ProductTypeJson);
		if ($Product->Packaged && $Product->Packaged->count() > 0){
			foreach($Product->Packaged as $PackagedProduct){
				$PackageProduct = $this->getContentPackagedProductClass();
				//echo '<pre>';print_r($PackagedProduct->toArray());
				$PackageProduct->loadDatabaseData($PackagedProduct);

				$this->addPackagedProduct($PackageProduct);
			}
		}
	}

	/**
	 * @param $orderedProductsString
	 */
	public function onGetEmailList(&$orderedProductsString)
	{
		$orderedProductsString .= ' - Packaged Products - ' . "\n";
		$orderedProductsString .= '-----------------------' . "\n";

		foreach($this->getPackagedProducts() as $PackagedProduct){
			if (method_exists($PackagedProduct, 'onGetEmailList')){
				$PackagedProduct->onGetEmailList(&$orderedProductsString);
			}
		}
	}
}