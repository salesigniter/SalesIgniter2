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
	private function getValue($key, $arrayName = null)
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
		return substr($this->getValue('entry_name'), strpos($this->getValue('entry_name'), ' '));
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
	 * @return string
	 */
	public function getCountryId()
	{
		return $this->getValue('countries_id', 'Country');
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