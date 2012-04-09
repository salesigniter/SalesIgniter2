<?php
/**
 * Address manager for the order class
 *
 * @package Order
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/Address.php');

/**
 * @package Order
 */
class OrderAddressManager
{

	/**
	 * @var array
	 */
	protected $addresses = array();

	/**
	 * @var array
	 */
	protected $addressHeadings = array();

	/**
	 * @var int
	 */
	protected $orderId = 0;

	/**
	 * @param array|null $addressArray
	 */
	public function __construct(array $addressArray = null) {
		$this->addressHeadings = array(
			'customer' => 'Customer Address',
			'billing'  => 'Billing Address',
			'delivery' => 'Shipping Address'
		);

		if (sysConfig::exists('EXTENSION_PAY_PER_RENTALS_CHOOSE_PICKUP') && sysConfig::get('EXTENSION_PAY_PER_RENTALS_CHOOSE_PICKUP') == 'True'){
			$this->addressHeadings['pickup'] = 'Pickup Address';
		}

		if (is_null($addressArray) === false){
			foreach($addressArray as $type => $aInfo){
				$this->addresses[$type] = new OrderAddress($aInfo);
			}
		}
		else {
			foreach($this->addressHeadings as $type => $heading){
				$this->addresses[$type] = new OrderAddress(array(
					'address_type' => $type
				));
			}
		}
	}

	/**
	 * @param int $val
	 */
	public function setOrderId($val) {
		$this->orderId = (int) $val;
	}

	/**
	 * @return OrderAddress[]
	 */
	public function getAddresses(){
		return $this->addresses;
	}

	/**
	 * @param $rType
	 * @return OrderAddress|null
	 */
	public function getAddress($rType) {
		$return = null;
		foreach($this->addresses as $type => $addressObj){
			if ($type == $rType){
				$return = $addressObj;
				break;
			}
		}
		return $return;
	}

	/**
	 * @return string
	 */
	public function listAll() {
		$addressesTable = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0)
			->css('width', '100%');

		$addressesRow = array();
		foreach($this->addresses as $type => $addressObj){
			if (isset($this->addressHeadings[$addressObj->getAddressType()])){
				$addressTable = htmlBase::newElement('table')
					->setCellPadding(2)
					->setCellSpacing(0)
					->css('width', '100%');

				$addressTable->addBodyRow(array(
					'columns' => array(
						array(
							'addCls' => 'main',
							'valign' => 'top',
							'text'   => '<b>' . $this->addressHeadings[$addressObj->getAddressType()] . '</b>'
						)
					)
				));

				$addressTable->addBodyRow(array(
					'columns' => array(
						array(
							'addCls' => 'main ' . $addressObj->getAddressType() . 'Address',
							'valign' => 'top',
							'text'   => $this->showAddress($addressObj)
						)
					)
				));

				$addressesRow[] = array(
					'valign' => 'top',
					'text'   => $addressTable
				);
			}
		}
		$addressesTable->addBodyRow(array(
			'columns' => $addressesRow
		));

		return $addressesTable->draw();
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getFormattedAddress($type) {
		$Address = '';
		if (isset($this->addresses[$type])){
			$Address = $this->showAddress($this->addresses[$type], true);
		}
		return $Address;
	}

	/**
	 * @param OrderAddress $Address
	 * @param bool $html
	 * @return mixed
	 */
	public function showAddress(OrderAddress $Address, $html = true) {
		if (sysConfig::get('ACCOUNT_COMPANY') == 'true'){
			$company = htmlspecialchars($Address->getCompany());
		}
		$firstname = htmlspecialchars($Address->getName());
		$lastname = '';
		$street_address = htmlspecialchars($Address->getStreetAddress());
		$suburb = htmlspecialchars($Address->getSuburb());
		$city = htmlspecialchars($Address->getCity());
		$state = htmlspecialchars($Address->getState());
		$country = htmlspecialchars($Address->getCountry());
		$postcode = htmlspecialchars($Address->getPostcode());
		$abbrstate = htmlspecialchars($Address->getZoneCode());
		$vat = htmlspecialchars($Address->getVAT());
		$cif = htmlspecialchars($Address->getCIF());
		$city_birth = htmlspecialchars($Address->getCityBirth());
		$fmt = $Address->getFormat();
		if ($html){
			$fmt = nl2br($fmt);
		}
		eval("\$address = \"$fmt\";");

		return $address;
	}
}

?>