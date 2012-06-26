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
 * Address class for the order class
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderAddress
{

	/**
	 * @var array|null
	 */
	protected $addressInfo = array();

	/**
	 * @var int
	 */
	protected $Id = 0;

	/**
	 * @var string
	 */
	protected $Type = '';

	/**
	 * @var array
	 */
	protected $Zone = array();

	/**
	 * @var array
	 */
	protected $Country = array();

	/**
	 * @var array
	 */
	protected $Format = array();

	/**
	 * @param array|null $aInfo
	 */
	public function __construct(array $aInfo = null)
	{
		if (is_null($aInfo) === false){
			$this->addressInfo = $aInfo;
			$this->Type = $this->addressInfo['address_type'];
			if (isset($this->addressInfo['id'])){
				$this->Id = $this->addressInfo['id'];
				if (isset($this->addressInfo['Zones'])){
					$this->Zone = $this->addressInfo['Zones'];
					$this->addressInfo['entry_zone_id'] = $this->Zone['zone_id'];
				}
				if (isset($this->addressInfo['Countries'])){
					$this->Country = $this->addressInfo['Countries'];
					$this->addressInfo['entry_country_id'] = $this->Country['countries_id'];
				}
				if (isset($this->Country['AddressFormat'])){
					$this->Format = $this->Country['AddressFormat'];
				}
			}
		}
	}

	/**
	 * @return array|null
	 */
	public function toArray()
	{
		return $this->addressInfo;
	}

	/**
	 * @param $val
	 */
	public function setAddressType($val)
	{
		$this->Type = $val;
	}

	/**
	 * @return string
	 */
	public function getAddressType()
	{
		return $this->Type;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->Id;
	}

	/**
	 * @param      $key
	 * @param null $arrayName
	 * @return string
	 */
	public function getValue($key, $arrayName = null)
	{
		if (is_null($arrayName) === false){
			$Arr = $this->$arrayName;
		}
		else {
			$Arr = $this->addressInfo;
		}

		if (array_key_exists($key, $Arr)){
			$returnVal = $Arr[$key];
		}
		else {
			$returnVal = '';
		}

		return $returnVal;
	}

	/**
	 * @return string
	 */
	public function getFormatId()
	{
		return $this->getValue('address_format_id', 'Format');
	}

	/**
	 * @return string
	 */
	public function getFormat()
	{
		return $this->getValue('address_format', 'Format');
	}

	/**
	 * @return string
	 */
	public function getGender()
	{
		return $this->getValue('entry_gender');
	}

	/**
	 * @return string
	 */
	public function getDateOfBirth()
	{
		return $this->getValue('entry_dob');
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->getValue('entry_name');
	}

	/**
	 * @return string
	 */
	public function getFirstName()
	{
		return substr($this->getValue('entry_name'), 0, strpos($this->getValue('entry_name'), ' '));
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		return substr($this->getValue('entry_name'), strpos($this->getValue('entry_name'), ' ') + 1);
	}

	/**
	 * @return string
	 */
	public function getCompany()
	{
		return $this->getValue('entry_company');
	}

	/**
	 * @return string
	 */
	public function getStreetAddress()
	{
		return $this->getValue('entry_street_address');
	}

	/**
	 * @return string
	 */
	public function getSuburb()
	{
		return $this->getValue('entry_suburb');
	}

	/**
	 * @return string
	 */
	public function getCity()
	{
		return $this->getValue('entry_city');
	}

	/**
	 * @return string
	 */
	public function getVAT()
	{
		return $this->getValue('entry_vat');
	}

	/**
	 * @return string
	 */
	public function getCIF()
	{
		return $this->getValue('entry_cif');
	}

	/**
	 * @return string
	 */
	public function getCityBirth()
	{
		return $this->getValue('entry_city_birth');
	}

	/**
	 * @return string
	 */
	public function getPostcode()
	{
		return $this->getValue('entry_postcode');
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->getValue('entry_state');
	}

	/**
	 * @return string
	 */
	public function getZone()
	{
		return $this->getValue('zone_name', 'Zone');
	}

	/**
	 * @return string
	 */
	public function getZoneId()
	{
		return $this->getValue('zone_id', 'Zone');
	}

	/**
	 * @return string
	 */
	public function getZoneCode()
	{
		return $this->getValue('zone_code', 'Zone');
	}

	/**
	 * @return string
	 */
	public function getCountry()
	{
		return $this->getValue('countries_name', 'Country');
	}

	/**
	 * @param int $type
	 * @return string
	 */
	public function getCountryCode($type = 2)
	{
		return $this->getValue('countries_iso_code_' . $type, 'Country');
	}

	/**
	 * @return string
	 */
	public function getCountryId()
	{
		return $this->getValue('countries_id', 'Country');
	}

	/**
	 * @param bool $asHtml
	 * @return string
	 */
	public function format($asHtml = true)
	{
		$Format = $this->getFormat();

		if ($asHtml === true){
			$Format = nl2br($Format);
		}

		$company = $this->getCompany();
		$firstname = $this->getFirstName();
		$lastname = $this->getLastName();
		$street_address = $this->getStreetAddress();
		$suburb = $this->getSuburb();
		$city = $this->getCity();
		$vat = $this->getVAT();
		$cif = $this->getCIF();
		$city_birth = $this->getCityBirth();
		$state = $this->getZone();
		$country = $this->getCountry();
		$abbrstate = $this->getZoneCode();
		$postcode = $this->getPostcode();

		eval("\$address = \"$Format\";");

		return $address;
	}

	/**
	 * @param array $AddressInfo
	 */
	public function updateFromArray(array $AddressInfo)
	{
		foreach($AddressInfo as $k => $v){
			$this->addressInfo[$k] = $v;
		}

		if (!empty($AddressInfo['entry_country_id'])){
			$Country = Doctrine_Query::create()
				->from('Countries c')
				->leftJoin('c.AddressFormat f')
				->where('c.countries_id = ?', $this->addressInfo['entry_country_id'])
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$this->Country = $Country[0];
			$this->Format = $this->Country['AddressFormat'];
		}

		if (!empty($AddressInfo['entry_zone_id'])){
			$Zone = Doctrine_Query::create()
				->from('Zones')
				->where('zone_country_id = ?', $this->getCountryId())
				->andWhere('zone_id = ?', $this->addressInfo['entry_zone_id'])
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$this->Zone = $Zone[0];
		}
		elseif (!empty($AddressInfo['entry_state'])) {
			$Zone = Doctrine_Query::create()
				->from('Zones')
				->where('zone_country_id = ?', $this->getCountryId())
				->andWhere('zone_name LIKE ? OR zone_code LIKE ?', array(
				$this->addressInfo['entry_state'] . '%',
				$this->addressInfo['entry_state'] . '%'
			))
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$this->Zone = $Zone[0];
		}
	}

	/**
	 * @param array $data
	 */
	public function jsonDecode(array $data)
	{
		$this->Type = $data['Type'];
		$this->updateFromArray($data['addressInfo']);
	}

	/**
	 * @return string
	 */
	public function prepareJsonSave()
	{
		return array(
			'addressInfo' => $this->addressInfo,
			'Type'        => $this->Type
		);
	}
}

?>