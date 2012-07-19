<?php
/**
 * Address manager for the order class
 *
 * @package    Order\AddressManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      1.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
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
	 *
	 */
	public function __construct()
	{
		$this->addressHeadings = array(
			'customer' => 'Customer Address',
			'billing'  => 'Billing Address',
			'delivery' => 'Shipping Address'
		);

		if (sysConfig::exists('EXTENSION_PAY_PER_RENTALS_CHOOSE_PICKUP') && sysConfig::get('EXTENSION_PAY_PER_RENTALS_CHOOSE_PICKUP') == 'True'){
			$this->addressHeadings['pickup'] = 'Pickup Address';
		}
	}

	/**
	 * This function is overridden in all other address managers that need to use
	 * their own custom address class
	 *
	 * @return OrderAddress
	 */
	public function getAddressClass()
	{
		return new OrderAddress();
	}

	/**
	 * @return OrderAddress[]
	 */
	public function getAddresses()
	{
		return $this->addresses;
	}

	/**
	 * @param $rType
	 * @return OrderAddress|null
	 */
	public function getAddress($rType)
	{
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
	public function listAll()
	{
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
	public function getFormattedAddress($type)
	{
		$Address = '';
		if (isset($this->addresses[$type])){
			$Address = $this->showAddress($this->addresses[$type], true);
		}
		return $Address;
	}

	/**
	 * @param OrderAddress $Address
	 * @param bool         $html
	 * @return mixed
	 */
	public function showAddress(OrderAddress $Address, $html = true)
	{
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

	/**
	 * @return array
	 */
	public function prepareSave()
	{
		$toEncode = array();
		foreach($this->getAddresses() as $Type => $Address){
			$toEncode['addresses'][$Type] = $Address->prepareSave();
		}
		return $toEncode;
	}

	public function loadDatabaseData($data)
	{
		if (isset($data['addresses'])){
			foreach($data['addresses'] as $Type => $aInfo){
				$this->addresses[$Type] = $this->getAddressClass();
				$this->addresses[$Type]->loadDatabaseData($aInfo);
			}
		}
	}

	public function onExport($addColumns, &$CurrentRow, &$HeaderRow)
	{
		foreach($this->getAddresses() as $Address){
			if ($addColumns['v_' . $Address->getAddressType() . '_address'] === true){
				$Address->onExport($addColumns, $CurrentRow, $HeaderRow);
			}
		}
	}
}

require(__DIR__ . '/Address.php');
