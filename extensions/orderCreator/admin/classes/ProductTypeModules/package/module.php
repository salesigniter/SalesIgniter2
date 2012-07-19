<?php
if (class_exists('OrderProductTypePackage') === false){
	require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/ProductTypeModules/package/module.php');
}

/**
 * Package product type class for the order creator product manager class
 *
 * @package   OrderCreator\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorProductTypePackage extends OrderProductTypePackage
{

	/**
	 * @return OrderCreatorProduct|OrderProduct
	 */
	public function getContentPackagedProductClass()
	{
		return new OrderCreatorProduct();
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 */
	public function onUpdateOrderProduct(OrderCreatorProduct &$OrderedProduct)
	{
		foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();
			if (method_exists($ProductType, 'onUpdateOrderProduct')){
				$ProductType->onUpdateOrderProduct($PackageOrderProduct);
			}
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 * @param array               $SelectedBarcodes
	 * @return string
	 */
	public function OrderCreatorBarcodeEdit(OrderCreatorProduct $OrderedProduct, &$SelectedBarcodes = array())
	{
		$return = '';
		foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();
			$return .= '<span style="font-size:.8em;"><b>' . $PackageOrderProduct->getName() . '</b><br><span style="font-style:italic;">';
			if (method_exists($ProductType, 'OrderCreatorBarcodeEdit')){
				$SelectedBarcodes = $PackageOrderProduct->getBarcodes();
				for($i = 0; $i < $OrderedProduct->getQuantity(); $i++){
					$return .= $ProductType->OrderCreatorBarcodeEdit($PackageOrderProduct, &$SelectedBarcodes);
				}
			}
			elseif (method_exists($ProductType, 'displayOrderedBarcodes')) {
				$return .= $ProductType->displayOrderedBarcodes();
			}
			$return .= '</span></span><br>';
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 * @param bool                $allowEdit
	 * @return string
	 */
	public function OrderCreatorAfterProductName(OrderCreatorProduct $OrderedProduct, $allowEdit = true)
	{
		$return = '';
		if ($OrderedProduct->hasInfo('PackagedProducts')){
			$hasReservation = false;
			$reservationIds = array();
			foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
				if ($PackageOrderProduct->hasInfo('PurchaseType') && $PackageOrderProduct->getInfo('PurchaseType') == 'reservation'){
					$hasReservation = true;
					$reservationIds[] = $PackageOrderProduct->getProductsId();
				}

				$pInfoHtml = htmlBase::newElement('span')
					->css(array(
					'font-size'  => '.8em',
					'font-style' => 'italic'
				))
					->html(' - ' . $PackageOrderProduct->getQuantity() . 'x ' . $PackageOrderProduct->getName());

				$return .= '<br>' . $pInfoHtml->draw();

				$ProductTypeClass = $PackageOrderProduct->getProductTypeClass();
				if (method_exists($ProductTypeClass, 'OrderCreatorAfterProductName')){
					$return .= $ProductTypeClass->OrderCreatorAfterProductName($PackageOrderProduct, false) . '<br>';
				}
			}

			if ($hasReservation === true){
				$return = '&nbsp;' . htmlBase::newElement('button')
					->attr('data-product_ids', implode(',', $reservationIds))
					->addClass('reservationDates')
					->setText('Select Reservation Dates')
					->draw() . $return;
			}
		}
		return $return;
	}

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorUpdateProductInfo(array &$pInfo)
	{
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function allowAddToContents(OrderCreatorProduct $OrderProduct)
	{
		$return = true;
		if (isset($_POST['reservation_begin'])){
			$this->setInfo('ReservationInfo', array(
				'start_date'      => $_POST['reservation_begin'],
				'start_time'      => $_POST['reservation_begin_time'],
				'end_date'        => $_POST['reservation_end'],
				'end_time'        => $_POST['reservation_end_time'],
				'weight'          => 0,
				'shipping'        => false,
				'insurance_cost'  => 0,
				'insurance_value' => 0,
				'deposit_amount'  => 0,
				'days_before'     => (isset($_POST['days_before']) ? $_POST['days_before'] : 0),
				'days_after'      => (isset($_POST['days_after']) ? $_POST['days_after'] : 0),
			));
		}

		foreach($this->getProductsRaw() as $PackageInfo){
			$ProductId = $PackageInfo['product_id'];
			$PackageData = $PackageInfo['packageData'];
			if (isset($PackageData['purchase_type'])){
				$_GET['purchase_type'] = $PackageData['purchase_type'];
			}

			$PackageOrderProduct = new OrderCreatorProduct();
			$PackageOrderProduct->regenerateId();
			$PackageOrderProduct->setProductId($ProductId);
			$PackageOrderProduct->setQuantity($PackageData['quantity']);
			$PackageOrderProduct->setInfo('PackageData', $PackageData);

			$ProductType = $PackageOrderProduct->getProductTypeClass();
			if (method_exists($ProductType, 'allowAddToContents')){
				$return = $ProductType->allowAddToContents($PackageOrderProduct);
			}

			if ($return === false){
				break;
			}
			else {
				$this->addPackagedProduct($PackageOrderProduct);
			}
		}

		return $return;
	}

	/**
	 * @return mixed
	 */
	public function getProductsRaw()
	{
		$Query = Doctrine_Query::create()
			->from('ProductsPackagedProducts')
			->where('package_id = ?', $this->getProductId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$products = array();
		foreach($Query as $pInfo){
			$packageInfo = array(
				'product_id'   => $pInfo['product_id'],
				'packageData'  => $pInfo['package_data'],
			);

			$products[] = $packageInfo;
		}
		return $products;
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 */
	public function onAddToContents(OrderCreatorProduct &$OrderProduct)
	{
		global $Editor, $appExtension;

		$PackageProducts = array();
		$MainPrice = 0;
		$ConfirmProducts = array();
		$ReservationInfo = $this->getInfo('ReservationInfo');

		/**
		 * Reset Just In Case
		 */
		$ReservationInfo['weight'] = 0;
		$ReservationInfo['insurance_value'] = 0;
		$ReservationInfo['insurance_cost'] = 0;
		$ReservationInfo['deposit_amount'] = 0;

		foreach($this->getPackagedProducts() as $PackageProduct){
			$ProductType = $PackageProduct->getProductTypeClass();
			if (method_exists($ProductType, 'onAddToContents')){
				$ProductType->onAddToContents($PackageProduct);
			}

			$PurchaseType = $ProductType->getPurchaseTypeClass();
			if ($PurchaseType->getCode() == 'reservation'){
				$ReservationInfo['weight'] += $PackageProduct->getWeight();
				$ReservationInfo['insurance_value'] += $PurchaseType->getInsuranceValue();
				$ReservationInfo['insurance_cost'] += $PurchaseType->getInsuranceCost();
				$ReservationInfo['deposit_amount'] += $PurchaseType->getDepositAmount();
			}

			$MainPrice += $PackageProduct->getPrice();

			if ($PackageProduct->needsConfirmation() === true){
				$OrderProduct->needsConfirmation(true);
				$ConfirmProducts[] = $PackageProduct->getName();
			}
		}

		if ($OrderProduct->needsConfirmation() === true){
			$OrderProduct->setConfirmationMessage('The Following Products In This Package Do Not Have Enough Inventory:' . '<br><br>' . implode('<br>', $ConfirmProducts));
		}

		$OrderProduct->setPrice($MainPrice);
		$this->setInfo('ReservationInfo', $ReservationInfo);
		//$this->loadReservationPricing($OrderProduct->getInfo('PackagedProducts'));
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function OrderCreatorAllowProductUpdate(OrderCreatorProduct $OrderProduct)
	{
		$return = true;
		foreach($OrderProduct
					->getProductTypeClass()
					->getPackagedProducts() as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();
			if (method_exists($ProductType, 'OrderCreatorAllowProductUpdate')){
				$return = $ProductType->OrderCreatorAllowProductUpdate($PackageOrderProduct);
			}
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $Product
	 */
	public function OrderCreatorProductManagerUpdateFromPost(OrderCreatorProduct &$Product)
	{
		foreach($Product->getInfo('PackagedProducts') as $PackageProduct){
			if (isset($_POST['product'][$PackageProduct->getId()]['barcode_id'])){
				if ($PackageProduct->hasInfo('Barcodes') && $PackageProduct->getInfo('Barcodes') != ''){
					foreach($PackageProduct->getInfo('Barcodes') as $Barcode){
						Doctrine_Query::create()
							->update('ProductsInventoryBarcodes')
							->set('status', '?', 'A')
							->where('barcode_id = ?', $Barcode['barcode_id'])
							->execute();
					}
				}

				$Barcodes = array();
				foreach($_POST['product'][$PackageProduct->getId()]['barcode_id'] as $bID){
					$Barcodes[] = array(
						'barcode_id' => $bID
					);
				}
				$PackageProduct->setBarcodes($Barcodes);
			}
		}
	}

	/**
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $SaleProduct
	 */
	public function onSaveProgress(&$SaleProduct)
	{
		foreach($this->getPackagedProducts() as $PackagedProduct){
			$PackageProduct = $SaleProduct->Packaged
				->getTable()
				->getRecord();

			$PackagedProduct->onSaveProgress($PackageProduct);

			$SaleProduct->Packaged->add($PackageProduct);
		}
	}

	/**
	 * @param array $ProductTypeJson
	 */
	public function loadSessionData(array $ProductTypeJson)
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

		if (isset($ProductTypeJson['PackagedProducts'])){
			foreach($ProductTypeJson['PackagedProducts'] as $k => $PackagedJson){
				$PackageProduct = new OrderCreatorProduct();
				$PackageProduct->loadSessionData($PackagedJson);

				$this->addPackagedProduct($PackageProduct);
			}
		}
	}
}
