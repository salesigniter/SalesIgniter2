<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/**
 * Product manager for the order class
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderProductManager
{

	/**
	 * @var int|null
	 */
	protected $orderId = null;

	/**
	 * @var array
	 */
	protected $Contents = array();

	/**
	 * @param array|null $orderedProducts
	 * @param int|null   $order
	 */
	public function __construct(array $orderedProducts = null, $order = null)
	{
		if (is_null($orderedProducts) === false){
			$is_gift_certificate = 0;
			if (is_null($order) === false && isset($order['is_gift_certificate']) && $order['is_gift_certificate']){
				$is_gift_certificate = $order['is_gift_certificate'];
			}

			foreach($orderedProducts as $i => $pInfo){
				if ($is_gift_certificate){
					$orderedProduct = new OrderGiftCertificateProduct($pInfo);
				}
				else {
					if (!isset($pInfo['purchase_type']) || $pInfo['purchase_type'] != 'membership'){
						$orderedProduct = new OrderProduct($pInfo);
					}
					else {
						$orderedProduct = new OrderRentalMembershipProduct($pInfo);
					}
				}
				$this->add($orderedProduct);
			}
		}
	}

	/**
	 * @param int $val
	 */
	public function setOrderId($val)
	{
		$this->orderId = $val;
	}

	/**
	 * @return OrderProduct[]
	 */
	public function getContents()
	{
		return $this->Contents;
	}

	/**
	 * @param OrderProduct $orderProduct
	 */
	public function add(OrderProduct &$orderProduct)
	{
		$this->Contents[$orderProduct->getId()] = $orderProduct;
	}

	/**
	 * @param int $id
	 * @return OrderProduct|bool
	 */
	public function get($id)
	{
		$id = (int)$id;
		if (array_key_exists($id, $this->Contents)){
			return $this->Contents[$id];
		}
		return false;
	}

	/**
	 * @return float|int
	 */
	public function getTotalWeight()
	{
		$total_weight = 0;
		foreach($this->Contents as $Product){
			$total_weight += $Product->getWeight();
		}
		return $total_weight;
	}

	/**
	 * @param bool  $showTableHeading
	 * @param bool  $showQty
	 * @param bool  $showBarcode
	 * @param bool  $showModel
	 * @param bool  $showName
	 * @param bool  $showExtraInfo
	 * @param bool  $showPrice
	 * @param bool  $showPriceWithTax
	 * @param bool  $showTotal
	 * @param bool  $showTotalWithTax
	 * @param bool  $showTax
	 * @param Order $Order
	 * @return htmlElement_table
	 */
	public function listProducts($showTableHeading = true, $showQty = true, $showBarcode = true, $showModel = true, $showName = true, $showExtraInfo = true, $showPrice = true, $showPriceWithTax = true, $showTotal = true, $showTotalWithTax = true, $showTax = true, Order $Order)
	{
		global $currencies, $typeNames;
		$productsTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0)->css('width', '100%');

		$productTableHeaderColumns = array();
		if ($showQty){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_QTY'));
		}
		if ($showName){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_NAME'));
		}
		if ($showBarcode){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_BARCODE'));
		}
		if ($showModel){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_MODEL'));
		}
		if ($showTax){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_TAX'));
		}
		if ($showPrice){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_PRICE_EXCLUDING_TAX'));
		}
		if ($showPriceWithTax){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_PRICE_INCLUDING_TAX'));
		}
		if ($showTotal){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_EXCLUDING_TAX'));
		}
		if ($showTotalWithTax){
			$productTableHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_INCLUDING_TAX'));
		}

		foreach($productTableHeaderColumns as $i => $cInfo){
			$productTableHeaderColumns[$i]['addCls'] = 'main ui-widget-header';
			if ($i > 0){
				$productTableHeaderColumns[$i]['css'] = array(
					'border-left' => 'none'
				);
			}

			if ($i > 1){
				$productTableHeaderColumns[$i]['align'] = 'right';
			}
		}

		$productsTable->addHeaderRow(array(
			'columns' => $productTableHeaderColumns
		));

		foreach($this->getContents() as $orderedProduct){
			$orderedProductId = $orderedProduct->getOrderedProductId();
			$finalPrice = $orderedProduct->getPrice();
			$finalPriceWithTax = $orderedProduct->getPrice(true);
			$taxRate = $orderedProduct->getTaxRate();
			$productQty = $orderedProduct->getQuantity();
			$productModel = $orderedProduct->getModel();
			$i = 0;
			$barcode = $orderedProduct->displayBarcodes();

			$productsName = $orderedProduct->getNameHtml($showExtraInfo);

			$bodyColumns = array();
			if ($showQty){
				$bodyColumns[] = array(
					'align' => 'right',
					'text'  => $productQty . '&nbsp;x'
				);
			}
			if ($showName){
				$bodyColumns[] = array(
					'text' => $productsName
				);
			}
			if ($showBarcode){
				$bodyColumns[] = array(
					'text' => $barcode
				);
			}
			if ($showModel){
				$bodyColumns[] = array(
					'text' => $productModel
				);
			}
			if ($showTax){
				$bodyColumns[] = array(
					'align' => 'right',
					'text'  => $taxRate . '%'
				);
			}
			if ($showPrice){
				$bodyColumns[] = array(
					'align' => 'right',
					'text'  => '<b>' . $currencies->format($finalPrice, true, $Order->getCurrency(), $Order->getCurrencyValue()) . '</b>'
				);
			}
			if ($showPriceWithTax){
				$bodyColumns[] = array(
					'align' => 'right',
					'text'  => '<b>' . $currencies->format($finalPriceWithTax, true, $Order->getCurrency(), $Order->getCurrencyValue()) . '</b>'
				);
			}
			if ($showTotal){
				$bodyColumns[] = array(
					'align' => 'right',
					'text'  => '<b>' . $currencies->format($finalPrice * $productQty, true, $Order->getCurrency(), $Order->getCurrencyValue()) . '</b>'
				);
			}
			if ($showTotalWithTax){
				$bodyColumns[] = array(
					'align' => 'right',
					'text'  => '<b>' . $currencies->format($finalPriceWithTax * $productQty, true, $Order->getCurrency(), $Order->getCurrencyValue()) . '</b>'
				);
			}

			$sizeOf = sizeof($bodyColumns);
			foreach($bodyColumns as $idx => $colInfo){
				$bodyColumns[$idx]['addCls'] = 'ui-widget-content';
				$bodyColumns[$idx]['valign'] = 'top';
				$bodyColumns[$idx]['css'] = array(
					'border-top' => 'none'
				);

				if ($idx > 0 && $idx < $sizeOf){
					$bodyColumns[$idx]['css']['border-left'] = 'none';
				}
			}

			$productsTable->addBodyRow(array(
				'columns' => $bodyColumns
			));
		}

		return $productsTable;
	}

	/**
	 * @return string
	 */
	public function jsonEncode()
	{
		$ProductsJsonArray = array();
		foreach($this->getContents() as $Id => $OrderProduct){
			$ProductsJsonArray[$Id] = $OrderProduct->jsonEncode();
		}
		return json_encode($ProductsJsonArray);
	}

	/**
	 * @param AccountsReceivableSalesProducts $Product
	 */
	public function jsonDecodeProduct(AccountsReceivableSalesProducts $Product)
	{
		$OrderProduct = new OrderProduct();
		$OrderProduct->jsonDecodeProduct($Product);
		$this->Contents[$OrderProduct->getId()] = $OrderProduct;
	}

	/**
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$Contents = json_decode($data, true);
		foreach($Contents as $Id => $opInfo){
			$OrderProduct = new OrderProduct();
			$OrderProduct->jsonDecode($opInfo);
			$this->Contents[$OrderProduct->getId()] = $OrderProduct;
		}
	}
}

require(dirname(__FILE__) . '/Product.php');
require(dirname(__FILE__) . '/RentalMembershipProduct.php');
require(dirname(__FILE__) . '/OrderGiftCertificateProduct.php');
