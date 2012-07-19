<?php
/**
 * I.T. Web Experts
 * http://www.itwebexperts.com
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * Extension: PackageProducts
 * Extension Version: 1.0
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/**
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */
class ProductTypePackage extends ProductTypeBase
{

	/**
	 * @var string
	 */
	private $_moduleCode = 'package';

	/**
	 * @var array
	 */
	private $purchaseTypes = array();

	/**
	 * @var string
	 */
	private $cartPurchaseType = '';

	/**
	 * @var array
	 */
	private $checked = array();

	/**
	 * @var array
	 */
	private $info = array(
		'id'          => 0,
		'name'        => array(),
		'description' => array(),
		'products'    => array()
	);

	/**
	 * @var array
	 */
	private $purchaseTypeModules = array();

	/**
	 *
	 */
	public function __construct()
	{
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Package Product Type');
		$this->setDescription('Package Product Type');

		$this->init(
			$this->_moduleCode,
			false,
			sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/'
		);
	}

	/**
	 * @param $val
	 */
	public function setProductId($val)
	{
		$this->info['id'] = $val;
		//$this->loadPackagedProducts();
	}

	/**
	 * @param $val
	 * @param $langId
	 */
	public function setProductName($val, $langId)
	{
		$this->info['name'][$langId] = $val;
	}

	/**
	 * @param $val
	 * @param $langId
	 */
	public function setProductDescription($val, $langId)
	{
		$this->info['description'][$langId] = $val;
	}

	/**
	 * @param $val
	 * @param $langId
	 */
	public function setProductShortDescription($val, $langId)
	{
		$this->info['short_description'][$langId] = $val;
	}

	/**
	 * @return mixed
	 */
	public function getProductId()
	{
		return $this->info['id'];
	}

	/**
	 * @return int
	 */
	public function getTaxClassId()
	{
		return 0;
	}

	/**
	 * @param bool $langId
	 * @return mixed
	 */
	public function getProductName($langId = false)
	{
		if ($langId === false){
			$langId = Session::get('languages_id');
		}

		return $this->info['name'][$langId];
	}

	/**
	 * @param bool $langId
	 * @return mixed
	 */
	public function getProductDescription($langId = false)
	{
		if ($langId === false){
			$langId = Session::get('languages_id');
		}
		return $this->info['description'][$langId];
	}

	/**
	 * @param bool $langId
	 * @return mixed
	 */
	public function getProductShortDescription($langId = false)
	{
		if ($langId === false){
			$langId = Session::get('languages_id');
		}
		return $this->info['short_description'][$langId];
	}

	/**
	 * @return int|string
	 */
	public function getProductPrice()
	{
		if (empty($this->info['products'])){
			$this->loadPackagedProducts();
		}

		$Price = 0;
		foreach($this->info['products'] as $pInfo){
			$Product = $pInfo['productClass'];
			$PackageData = $pInfo['packageData'];
			$Quantity = $PackageData['quantity'];

			$ProductType = $Product->getProductTypeClass();
			if (isset($PackageData['price'])){
				$Price += $PackageData['price'] * $Quantity;
			}
			elseif (isset($PackageData['purchase_type'])) {
				$ProductType->loadPurchaseType($PackageData['purchase_type']);
				$Price += $ProductType->getProductPrice($PackageData['purchase_type']) * $Quantity;
			}
			else {
				$Price += $ProductType->getProductPrice() * $Quantity;
			}
		}

		return $Price;
	}

	/**
	 * @return bool
	 */
	public function hasInventory()
	{
		if (empty($this->info['products'])){
			$this->loadPackagedProducts();
		}

		$hasInventory = true;
		foreach($this->info['products'] as $pInfo){
			$Product = $pInfo['productClass'];

			$ProductType = $Product->getProductTypeClass();
			if (isset($pInfo['packageData']['purchase_type'])){
				$PurchaseType = $ProductType->getPurchaseType($pInfo['packageData']['purchase_type']);
				$hasInventory = $PurchaseType->hasInventory();
				if ($hasInventory === true && $pInfo['packageData']['quantity'] > 1){
					$TotalInvItems = $PurchaseType->getCurrentStock();
					$hasInventory = (($TotalInvItems - $pInfo['packageData']['quantity']) >= 0);
				}
			}
			else {
				$hasInventory = $ProductType->hasInventory();
				if ($hasInventory === true && $pInfo['packageData']['quantity'] > 1){
					$TotalInvItems = $ProductType->getCurrentStock();
					$hasInventory = (($TotalInvItems - $pInfo['packageData']['quantity']) >= 0);
				}
			}

			if ($hasInventory === false){
				break;
			}
		}

		return $hasInventory;
	}

	/**
	 * @param $CartProductData
	 * @return bool
	 */
	public function allowAddToCart(&$CartProductData)
	{
		$return = true;
		foreach($this->getProductsRaw() as $PackageInfo){
			$Product = $PackageInfo['productClass'];
			$PackageData = $PackageInfo['packageData'];

			$ProductType = $Product->getProductTypeClass();
			if (method_exists($ProductType, 'allowAddToCart')){
				$return = $ProductType->allowAddToCart(&$CartProductData['packageInfo'][$Product->getId()]);
			}

			if ($return === false){
				break;
			}
		}
		return $return;
	}

	/**
	 * @param $CartProductData
	 */
	public function addToCartPrepare(&$CartProductData)
	{
		$CartProductData['PackagedProducts'] = array();
		foreach($this->getProductsRaw() as $PackageInfo){
			$Product = $PackageInfo['productClass'];
			$ProductType = $Product->getProductTypeClass();
			$PackageData = $PackageInfo['packageData'];

			$ProductId = $Product->getId();

			$PackageCartProduct = new ShoppingCartProduct(array(
				'product_id'   => $ProductId,
				'id_string'    => $ProductId,
				'tax_class_id' => 0,
				'quantity'     => $PackageData['quantity']
			));
			if (isset($PackageData['purchase_type'])){
				$PackageCartProduct->setData('purchase_type', $PackageData['purchase_type']);
				$PackageCartProduct->setData('tax_class_id', $ProductType->getTaxClassId($PackageData['purchase_type']));
			}
			$PackageCartProduct->loadProductClass($Product);

			if (method_exists($ProductType, 'addToCartPrepare')){
				$ProductData = $PackageCartProduct->getInfo();
				$ProductType->addToCartPrepare(&$ProductData);
				$PackageCartProduct->updateInfo($ProductData);
			}

			if (isset($PackageData['price']) && !is_object($PackageData['price'])){
				$PackageCartProduct->setPrice($PackageData['price']);
				$PackageCartProduct->setFinalPrice($PackageData['price']);
			}

			$MainPrice += $PackageCartProduct->getPrice() * $PackageCartProduct->getQuantity();
			$FinalPrice += $PackageCartProduct->getFinalPrice() * $PackageCartProduct->getQuantity();

			$CartProductData['PackagedProducts'][] = $PackageCartProduct;
		}
		$CartProductData['quantity'] = 1;
		$CartProductData['price'] = $MainPrice;
		$CartProductData['final_price'] = $FinalPrice;
		$CartProductData['tax_class_id'] = 0;
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 */
	public function addToCartBeforeAction(ShoppingCartProduct &$CartProduct)
	{
		foreach($CartProduct->getData('PackagedProducts') as $PackageCartProduct){
			$ProductType = $PackageCartProduct
				->getProductClass()
				->getProductTypeClass();
			if (method_exists($ProductType, 'addToCartBeforeAction')){
				$ProductType->addToCartBeforeAction($PackageCartProduct);
			}
		}
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 */
	public function addToCartAfterAction(ShoppingCartProduct &$CartProduct)
	{
		foreach($CartProduct->getData('PackagedProducts') as $PackageCartProduct){
			$ProductType = $PackageCartProduct
				->getProductClass()
				->getProductTypeClass();
			if (method_exists($ProductType, 'addToCartAfterAction')){
				$ProductType->addToCartAfterAction($PackageCartProduct);
			}
		}
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 */
	public function onCartProductLoad(ShoppingCartProduct &$CartProduct)
	{
		foreach($CartProduct->getData('PackagedProducts') as $PackageCartProduct){
			$PackageCartProduct->loadProductClass();

			$ProductType = $PackageCartProduct
				->getProductClass()
				->getProductTypeClass();
			if (method_exists($ProductType, 'onCartProductLoad')){
				$ProductType->onCartProductLoad($PackageCartProduct);
			}
		}
	}

	/**
	 * @param OrderProduct $OrderedProduct
	 * @return string
	 */
	public function displayOrderedProductBarcodes(OrderProduct $OrderedProduct)
	{
		$return = '';
		if ($OrderedProduct->hasInfo('PackagedProducts')){
			foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageProduct){
				$ProductType = $PackageProduct
					->getProductClass()
					->getProductTypeClass();
				$return .= '<span style="font-size:.8em;"><b>' . $PackageProduct->getName() . '</b><br><span style="font-style:italic;">';
				if (method_exists($ProductType, 'displayOrderedProductBarcodes')){
					$return .= $ProductType->displayOrderedProductBarcodes($PackageProduct);
				}
				$return .= '</span></span><br>';
			}
		}
		return $return;
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 * @param array               $settings
	 * @return string
	 */
	public function showShoppingCartProductInfo(ShoppingCartProduct $CartProduct, $settings = array())
	{
		$html = '';
		foreach($CartProduct->getData('PackagedProducts') as $PackageCartProduct){
			$Product = $PackageCartProduct->getProductClass();

			$pInfoHtml = htmlBase::newElement('span')
				->css(
				array(
					'font-size'  => '.8em',
					'font-style' => 'italic'
				))
				->html(' - ' . $PackageCartProduct->getQuantity() . 'x ' . $Product->getName());

			$html .= $pInfoHtml->draw();

			$ProductType = $Product->getProductTypeClass();
			if (method_exists($ProductType, 'showShoppingCartProductInfo')){
				$html .= '<div style="margin-left:10px;">' . $ProductType->showShoppingCartProductInfo($PackageCartProduct, $settings) . '</div>';
			}

			$html .= '<br>';
		}

		return $html;
	}

	/**
	 * @param Products $Product
	 */
	public function onSaveProduct(Products $Product)
	{
		if ($_POST['products_type'] == 'package' && isset($_POST['package_product'])){
			$PackageProducts = $Product->PackageProducts;
			$keepProducts = array();
			foreach($_POST['package_product'] as $packProductId){
				$keepProducts[] = $packProductId;
				$packageSettings = json_encode($_POST['package_product_settings'][$packProductId]);

				//$PackageProducts[$packProductId]->product_id = $packProductId;
				$PackageProducts[$packProductId]->package_data = $packageSettings;
			}

			foreach($PackageProducts as $packProduct){
				if (!in_array($packProduct->product_id, $keepProducts)){
					$packProduct->delete();
				}
			}
		}
	}

	/**
	 *
	 */
	private function loadPackagedProducts()
	{
		$Query = Doctrine_Query::create()
			->from('ProductsPackagedProducts')
			->where('package_id = ?', $this->getProductId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$this->info['products'] = array();
		foreach($Query as $pInfo){
			$PackageProduct = new Product($pInfo['product_id']);

			$packageInfo = array(
				'productClass' => $PackageProduct,
				'packageData'  => $pInfo['package_data'],
			);

			$this->info['products'][] = $packageInfo;
		}
	}

	/**
	 * @return bool
	 */
	public function hasReservation()
	{
		$hasReservation = false;
		foreach($this->getProductsRaw() as $PackageInfo){
			if (isset($PackageInfo['packageData']['purchase_type']) && $PackageInfo['packageData']['purchase_type'] == 'reservation'){
				$hasReservation = true;
				break;
			}
		}
		return $hasReservation;
	}

	/**
	 * @return mixed
	 */
	public function getProductsRaw()
	{
		if (empty($this->info['products'])){
			$this->loadPackagedProducts();
		}
		return $this->info['products'];
	}

	/**
	 * @return array
	 */
	public function getProducts()
	{
		foreach($this->getProductsRaw() as $pInfo){
			$productId = $pInfo['productClass']->getId();
			$packData = $pInfo['packageData'];

			$showPrice = '';
			if (isset($packData->price)){
				if (is_object($packData->price)){
					$PurchaseType = $pInfo['productClass']
						->getProductTypeClass()
						->getPurchaseType($packData->purchase_type);
					foreach(PurchaseType_reservation_utilities::getRentalPricing($PurchaseType->getPayPerRentalId()) as $iPrices){
						$pprId = $iPrices['pay_per_rental_id'];
						$priceId = $iPrices['pay_per_rental_types_id'];
						$numberOf = $iPrices['number_of'];

						$showPrice .= $iPrices['Description'][0]['price_per_rental_per_products_name'] . ': ';
						if (isset($packData->price->$pprId->$priceId->$numberOf)){
							$showPrice .= $packData->price->$pprId->$priceId->$numberOf;
							//$hiddenField .= '<input type="hidden" name="package_product_settings[' . $PurchaseType->getProductId() . '][price][' . $pprId . '][' . $priceId . '][' . $numberOf . ']" value="' . $packData->price->$pprId->$priceId->$numberOf . '">';
						}
						else {
							$showPrice .= 'Period Current Price';
						}
						$showPrice .= '<br>';
					}
				}
				else {
					$showPrice = '<input type="hidden" name="package_product_settings[' . $productId . '][price]" value="' . $packData->price . '">' . $packData->price;
				}
			}

			$packageInfo = array(
				'packageData' => $packData,
				'id'          => $productId,
				'name'        => '<input type="hidden" name="package_product[]" value="' . $productId . '">' . $pInfo['productClass']->getName(),
				'type'        => $pInfo['productClass']->getProductType(),
				'price'       => $showPrice,
				'quantity'    => '<input type="hidden" name="package_product_settings[' . $productId . '][quantity]" value="' . $packData->quantity . '">' . $packData->quantity
			);

			$products[] = $packageInfo;
		}
		return $products;
	}

	/*
	 * @TODO: Figure out something better
	 */
	/**
	 * @param $priceName
	 * @return int
	 */
	public function getReservationPrice($priceName)
	{
		global $currencies;
		if (empty($this->info['products'])){
			$this->loadPackagedProducts();
		}

		foreach($this->info['products'] as $pInfo){
			$Product = $pInfo['productClass'];
			$PackageData = $pInfo['packageData'];
			$Quantity = $PackageData['quantity'];

			$ProductType = $Product->getProductTypeClass();
			if (isset($PackageData['purchase_type']) && $PackageData['purchase_type'] == 'reservation'){
				$ProductType->loadPurchaseType('reservation');
				$pprId = $ProductType
					->getPurchaseType('reservation')
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
					$partName = $priceInfo['Description'][0]['price_per_rental_per_products_name'];
					if (!isset($prices[$partName])){
						$prices[$partName] = 0;
					}
					$prices[$partName] += $price;
				}
			}
		}

		return (isset($prices[$priceName]) ? $prices[$priceName] : 0);
	}

	/**
	 * @param $col
	 * @return string
	 */
	public function showProductListing($col)
	{
		global $currencies;
		if (empty($this->info['products'])){
			$this->loadPackagedProducts();
		}

		$return = '';
		switch($col){
			case 'productsPriceReservation':
				foreach($this->info['products'] as $pInfo){
					$Product = $pInfo['productClass'];
					$PackageData = $pInfo['packageData'];
					$Quantity = $PackageData['quantity'];

					$ProductType = $Product->getProductTypeClass();
					if (isset($PackageData['purchase_type']) && $PackageData['purchase_type'] == 'reservation'){
						$pprId = $ProductType
							->getPurchaseType('reservation')
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
							$partName = $priceInfo['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'];
							if (!isset($prices[$partName])){
								$prices[$partName] = 0;
							}
							$prices[$partName] += $price;
						}
					}
				}

				$return = '';
				foreach($prices as $priceName => $price){
					$return .= '<span style="display:block;">' . $priceName .
						' - ' .
						$currencies->format($price) . '</span>';
				}
				break;
			case 'productsPriceUsed':
			case 'productsPriceNew':
				$return = $currencies->format($this->getProductPrice());
				break;
			case 'productsName':
				$NameTable = htmlBase::newElement('table')
					->setCellPadding(1)
					->setCellSpacing(0);

				$NameLink = htmlBase::newElement('a')
					->setHref(itw_app_link('products_id=' . $this->getProductId(), 'product', 'info'))
					->html($this->getProductName());

				$NameTable->addBodyRow(array(
					'columns' => array(
						array(
							'css'     => array(
								'font-style' => 'inherit',
								'font-size'  => 'inherit',
								'color'      => 'inherit'
							),
							'colspan' => 3,
							'text'    => $NameLink->draw()
						)
					)
				));

				foreach($this->getProductsRaw() as $pInfo){
					$Product = $pInfo['productClass'];
					$PackageData = $pInfo['packageData'];

					$RowCss = array(
						'font-style'  => 'inherit',
						'font-size'   => '.8em',
						'color'       => 'inherit',
						'white-space' => 'nowrap'
					);

					$NameLink = htmlBase::newElement('a')
						->setHref(itw_app_link('products_id=' . $Product->getId(), 'product', 'info'))
						->html($Product->getName());

					$RowCols = array();
					$RowCols[] = array('css' => $RowCss, 'text' => '- ' . $PackageData['quantity'] . 'x');
					$RowCols[] = array('css' => $RowCss, 'text' => $NameLink->draw());
					if (isset($PackageData['purchase_type'])){
						$PurchaseType = $Product
							->getProductTypeClass()
							->getPurchaseType($PackageData['purchase_type']);
						$RowCols[] = array('css' => $RowCss, 'text' => ' ( ' . $PurchaseType->getTitle() . ' ) ');
					}

					$NameTable->addBodyRow(array(
						'columns' => $RowCols
					));
				}
				$return = $NameTable->draw();
				break;
		}
		return $return;
	}

	/**
	 * @param $Product
	 * @param $CurrentRow
	 */
	public function processProductImport(&$Product, $CurrentRow)
	{
		$PackageProducts =& $Product->PackageProducts;
		$PackageProducts->delete();
		$PackageProductsCheck = $CurrentRow->getColumnValue('v_packaged_products');
		if ($PackageProductsCheck !== false && $PackageProductsCheck !== null){
			$Products = explode("\n", $PackageProductsCheck);
			foreach($Products as $pLine){
				$pInfo = explode(',', $pLine);

				$ProductModel = trim($pInfo[0]);
				$ProductQuantity = trim($pInfo[1]);
				$ProductPrice = trim($pInfo[2]);
				$ProductPurchaseType = trim($pInfo[3]);

				$QproductId = Doctrine_Query::create()
					->select('products_id')
					->from('Products')
					->where('products_model = ?', $ProductModel)
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

				if ($QproductId && sizeof($QproductId) > 0){
					$packData = array('quantity' => $ProductQuantity);
					if (!empty($ProductPrice)){
						$packData['price'] = $ProductPrice;
					}
					if (!empty($ProductPurchaseType)){
						$packData['purchase_type'] = $ProductPurchaseType;
					}

					$newProduct = new ProductsPackagedProducts();
					$newProduct->product_id = $QproductId[0]['products_id'];
					$newProduct->package_data = json_encode($packData);

					$PackageProducts->add($newProduct);
				}
				unset($QproductId);
			}
		}
	}

	/**
	 *
	 */
	public function getExportTableColumns()
	{
	}

	/**
	 * @param $QfileLayout
	 */
	public function addExportQueryConditions(&$QfileLayout)
	{
	}

	/**
	 * @param $headerRow
	 */
	public function addExportHeaderColumns(&$headerRow)
	{
		$headerRow->addColumn('v_packaged_products');
	}

	/**
	 * @param $CurrentRow
	 * @param $Product
	 */
	public function addExportRowColumns(&$CurrentRow, $Product)
	{
		$RowData = array();
		$PackagedProducts = $Product->PackageProducts;
		if ($PackagedProducts && $PackagedProducts->count() > 0){
			foreach($PackagedProducts as $PackagedProduct){
				$PackageData = $PackagedProduct->package_data;

				$LineData = array();
				$LineData[] = $PackagedProduct->ProductInfo->products_model;
				$LineData[] = $PackageData['quantity'];
				if (isset($PackageData['price'])){
					$LineData[] = $PackageData['price'];
				}
				else {
					$LineData[] = '';
				}
				if (isset($PackageData['purchase_type'])){
					$LineData[] = $PackageData['purchase_type'];
				}
				else {
					$LineData[] = '';
				}

				$RowData[] = implode(',', $LineData);
			}
		}
		$CurrentRow->addColumn(implode("\n", $RowData), 'v_packaged_products');
	}

	/**
	 * @param $PackageProducts
	 */
	public function loadReservationPricing($PackageProducts)
	{
		foreach($PackageProducts as $PackagedProduct){
			$PackageData = $PackagedProduct->getInfo('PackageData');
			if ($PackageData['purchase_type'] == 'reservation'){
				if (in_array($PackagedProduct->getProductsId(), $_POST['reservation_products_id'])){
					$PurchaseType = $PackagedProduct
						->getProductTypeClass()
						->getPurchaseType();
					if (isset($PackageData['price']) && is_object($PackageData['price'])){
						PurchaseType_reservation_utilities::getRentalPricing($PurchaseType->getPayPerRentalId());
						$CachedPrice =& PurchaseType_reservation_utilities::$RentalPricingCache[$PurchaseType->getPayPerRentalId()];
						foreach($CachedPrice as $k => $pInfo){
							$Price = (array)$PackageData['price'];
							if (isset($Price[$pInfo['pay_per_rental_id']])){
								$Type = (array)$Price[$pInfo['pay_per_rental_id']];
								if (isset($Type[$pInfo['pay_per_rental_types_id']])){
									$NumOf = (array)$Type[$pInfo['pay_per_rental_types_id']];
									if (isset($NumOf[$pInfo['number_of']])){
										$CachedPrice[$k]['price'] = $NumOf[$pInfo['number_of']];
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
