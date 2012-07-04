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
	 * @param array|null $addressArray
	 */
	public function __construct(array $addressArray = null)
	{
		$this->addressHeadings['customer'] = true;
		$this->addressHeadings['billing'] = true;
		$this->addressHeadings['delivery'] = true;
		$this->addressHeadings['pickup'] = true;

		if (is_null($addressArray) === false){
			foreach($addressArray as $type => $aInfo){
				if (isset($this->addressHeadings[$type])){
					$this->addresses[$type] = new CheckoutSaleAddress($aInfo);
				}
			}
		}
		else {
			foreach($this->addressHeadings as $type => $heading){
				$this->addresses[$type] = new CheckoutSaleAddress(array(
					'address_type' => $type
				));
			}
		}
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

	/**
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$Decoded = json_decode($data, true);
		foreach($Decoded['addresses'] as $Type => $aInfo){
			$this->addresses[$Type] = new CheckoutSaleAddress();
			$this->addresses[$Type]->jsonDecode($aInfo);
		}
	}
}

require(__DIR__ . '/Address.php');
