<?php
/**
 * Address manager class for the checkout sale
 *
 * @package    CheckoutSale\AddressManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      2.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
 */

class CheckoutSaleAddressManager extends OrderAddressManager
{

	/**
	 * @return CheckoutSaleAddress
	 */
	public function getAddressClass()
	{
		return new CheckoutSaleAddress();
	}

	/**
	 * @param $rType
	 * @return null|OrderAddress|CheckoutSaleAddress
	 */
	public function getAddress($rType)
	{
		return parent::getAddress($rType);
	}

	/**
	 * @param array $addresses
	 * @param null  $againstGoogleZone
	 * @return bool
	 */
	public function validate($addresses = array(), $againstGoogleZone = null)
	{
		global $messageStack;
		$validated = true;
		if (empty($addresses)){
			foreach($this->getAddresses() as $SaleAddress){
				$validated = $SaleAddress->validate($againstGoogleZone);
				if ($validated === false){
					break;
				}
			}
		}
		else {
			foreach($addresses as $type){
				$SaleAddress = $this->getAddress($type);
				$validated = $SaleAddress->validate($againstGoogleZone);
				if ($validated === false){
					break;
				}
			}
		}
		return $validated;
	}
}

require(__DIR__ . '/Address.php');
