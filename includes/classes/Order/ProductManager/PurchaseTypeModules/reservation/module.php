<?php
if (class_exists('PurchaseType_reservation') === false){
	require(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/purchaseTypeModules/reservation/module.php');
}

/**
 * Reservation purchase type for the order class
 *
 * @package   Order\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderPurchaseTypeReservation extends PurchaseType_reservation
{

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

	public function showProductInfo($showExtraInfo = true)
	{
		//echo __FILE__ . '::' . __LINE__ . '<pre>SHOW_EXTRA::' . (int)$showExtraInfo;print_r($this->getInfo());
		if ($showExtraInfo){
			$resData = $this->getInfo();
			if ($resData && $resData['start_date']->getTimestamp() > 0){
				return PurchaseType_reservation_utilities::parse_reservation_info($resData);
			}
		}
		return '';
	}

	public function onGetEmailList(&$orderedProductsString)
	{
		global $currencies;
		$ReservationInfo = $this->getInfo();
		if ($ReservationInfo['start_date']->getTimestamp() > 0){
			$orderedProductsString .= "\t" . '- Reservation Info' . "\n" .
				"\t\t" . '- Start Date: ' . $ReservationInfo['start_date']->format(sysLanguage::getDateFormat('long')) . "\n" .
				"\t\t" . '- End Date: ' . $ReservationInfo['end_date']->format(sysLanguage::getDateFormat('long')) . "\n";

			if (isset($ReservationInfo['shipping']) && !empty($ReservationInfo['shipping']['title'])){
				$orderedProductsString .= "\t\t" . '- Shipping Method: ' . $ReservationInfo['shipping']['title'] . ' (' . $currencies->format($ReservationInfo['shipping']['cost']) . ')' . "\n";
			}
			$orderedProductsString .= "\t\t" . '- Insurance: ' . $currencies->format($ReservationInfo['insurance_cost']) . "\n";
		}
	}

	public function prepareJsonSave()
	{
		$toEncode = $this->getInfo();
		return $toEncode;
	}

	public function jsonDecode(array $PurchaseTypeJson)
	{
		$this->setInfo($PurchaseTypeJson);

		$this->setInfo('start_date', SesDateTime::createFromArray($PurchaseTypeJson['start_date']));
		$this->setInfo('end_date', SesDateTime::createFromArray($PurchaseTypeJson['end_date']));
	}

	/**
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $Product
	 * @param array                                                                   $PurchaseTypeJson
	 */
	public function jsonDecodeProduct($Product, array $PurchaseTypeJson)
	{
		$this->setInfo($PurchaseTypeJson);

		$this->setInfo('start_date', SesDateTime::createFromArray($PurchaseTypeJson['start_date']));
		$this->setInfo('end_date', SesDateTime::createFromArray($PurchaseTypeJson['end_date']));
	}
}