<?php
/**
 * Product manager for the order class
 *
 * @package    Order\ProductManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      2.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
 */

class OrderProductManager
{

	/**
	 * @var OrderProduct[]
	 */
	protected $Contents = array();

	/**
	 *
	 */
	public function __construct()
	{
	}

	/**
	 * This function is overridden in all other product managers that need to use
	 * their own custom product class
	 *
	 * @return OrderProduct
	 */
	public function getContentProductClass()
	{
		return new OrderProduct();
	}

	/**
	 * @return OrderProduct[]
	 */
	public function getContents()
	{
		return $this->Contents;
	}

	/**
	 * @param $id
	 * @return bool|OrderProduct
	 */
	public function &get($id)
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
	 * @return string
	 */
	public function getEmailList()
	{
		global $currencies, $typeNames;

		$orderedProductsString = '';
		foreach($this->getContents() as $orderedProduct){
			$orderedProductsString .= sprintf("%s x %s (%s) = %s\n",
				$orderedProduct->getQuantity(),
				$orderedProduct->getName(),
				$orderedProduct->getModel(),
				$currencies->display_price(
					$orderedProduct->getPrice(),
					$orderedProduct->getTaxRate(),
					$orderedProduct->getQuantity()
				)
			);

			$orderedProduct->onGetEmailList(&$orderedProductsString);
		}

		return $orderedProductsString;
	}

	/**
	 * @param AccountsReceivableSalesProducts $SaleProducts
	 * @param bool                            $assignInventory
	 */
	public function onSaveSale(AccountsReceivableSalesProducts &$SaleProducts, $assignInventory = false)
	{
		foreach($this->getContents() as $OrderProduct){
			$SaleProduct = $SaleProducts
				->getTable()
				->getRecord();

			$OrderProduct->onSaveSale($SaleProduct, $assignInventory);

			$SaleProducts->add($SaleProduct);
		}
	}

	/**
	 * Used when loading the sale from the database
	 *
	 * @param $Products
	 */
	public function jsonDecodeProduct($Products)
	{
		foreach($Products as $Product){
			$ContentProduct = $this->getContentProductClass();
			$ContentProduct->jsonDecodeProduct($Product);

			$this->Contents[$ContentProduct->getId()] = $ContentProduct;
		}
	}
}

require(__DIR__ . '/Product.php');
require(__DIR__ . '/RentalMembershipProduct.php');
require(__DIR__ . '/OrderGiftCertificateProduct.php');
