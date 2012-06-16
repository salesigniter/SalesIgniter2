<?php
/**
 * Address class for the checkout sale address manager
 *
 * @package CheckoutSale
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class CheckoutSaleAddress extends OrderAddress
{

	/**
	 * @param string $val
	 */
	public function setName($val) {
		$this->addressInfo['entry_name'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setCompany($val) {
		$this->addressInfo['entry_company'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setCityBirth($val) {
		$this->addressInfo['entry_city_birth'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setVATNumber($val) {
		$this->addressInfo['entry_vat'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setFiscalCode($val) {
		$this->addressInfo['entry_cif'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setDOB($val) {
		$this->addressInfo['entry_dob'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setStreetAddress($val) {
		$this->addressInfo['entry_street_address'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setSuburb($val) {
		$this->addressInfo['entry_suburb'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setCity($val) {
		$this->addressInfo['entry_city'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setPostcode($val) {
		$this->addressInfo['entry_postcode'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setState($val) {
		$this->addressInfo['entry_state'] = (string) $val;

		$Qcheck = Doctrine_Query::create()
			->from('Zones')
			->where('zone_name = ?', $val)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if ($Qcheck){
			$this->Zone = $Qcheck[0];
		}
	}

	/**
	 * @param string $val
	 */
	public function setCountry($val) {
		$this->addressInfo['entry_country'] = (string) $val;

		$Qcheck = Doctrine_Query::create()
			->from('Countries c')
			->leftJoin('c.AddressFormat')
			->where('c.countries_name = ?', $val)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if ($Qcheck){
			$this->Country = $Qcheck[0];
			$this->addressInfo['entry_country_id'] = $Qcheck[0]['countries_id'];
			$this->Format = $Qcheck[0]['AddressFormat'];
		}
	}

	private function getGoogleCoordinates($address)
	{
		global $checkedAddresses, $http;
		if (class_exists('Services_JSON') === false){
			require(sysConfig::getDirFsCatalog() . 'includes/classes/json.php');
		}
		$json = new Services_JSON();

		$addressStr = $address['entry_street_address'] . ', ' .
			$address['entry_city'] . ', ' .
			$address['entry_postcode'];

		if (isset($address['entry_state'])){
			$addressStr .= ', ' . $address['entry_state'];
		}

		if (isset($address['entry_country_name'])){
			$addressStr .= ', ' . $address['entry_country_name'];
		}

		$addressStr = str_replace(' ', '+', $addressStr);

		if (!isset($checkedAddresses[$addressStr])){
			$pointCoordinates = array(
				'lng' => 'false',
				'lat' => 'false'
			);
			$address = "http://maps.google.com/maps/geo?q=" . $addressStr . "&key=" . sysConfig::get('GOOGLE_API_SERVER_KEY') . "&output=json";
			$page = file_get_contents($address);

			if (tep_not_null($page)){
				$addressArr = $json->decode($page);
				if (isset($addressArr->Placemark)){
					$point = $addressArr->Placemark[0]->Point->coordinates;
				}
				if (isset($point) && is_array($point)){
					$pointCoordinates['lng'] = $point[0];
					$pointCoordinates['lat'] = $point[1];
				}
			}
			$checkedAddresses[$addressStr] = $pointCoordinates;
		}
		return $checkedAddresses[$addressStr];
	}

	private function polygonContains($polygon, $lon, $lat)
	{
		$j = 0;
		$oddNodes = false;
		$x = $lon;
		$y = $lat;
		for($i = 0; $i < sizeof($polygon); $i++){
			$j++;
			if ($j == sizeof($polygon)){
				$j = 0;
			}

			$iLat = $polygon[$i]['lat'];
			$iLng = $polygon[$i]['lng'];
			$jLat = $polygon[$j]['lat'];
			$jLng = $polygon[$j]['lng'];

			if (($iLat < $y && $jLat >= $y) || ($jLat < $y && $iLat >= $y)){
				if (($iLng + ($y - $iLat) / ($jLat - $iLat) * ($jLng - $iLng)) < $x){
					$oddNodes = !$oddNodes;
				}
			}
		}
		return $oddNodes;
	}

	public function validate($againstGoogleZone = null){
		global $messageStack;
		$validateSuccess = true;
		if ($this->getName() == ''){
			if ($this->getFirstName() == ''){
				$validateSuccess = false;
				$messageStack->addSession('pageStack', 'Your ' . $this->getAddressType() . ' address is missing "First Name"');
			}
			if ($this->getLastName() == ''){
				$validateSuccess = false;
				$messageStack->addSession('pageStack', 'Your ' . $this->getAddressType() . ' address is missing "Last Name"');
			}
		}
		if ($this->getStreetAddress() == ''){
			$validateSuccess = false;
			$messageStack->addSession('pageStack', 'Your ' . $this->getAddressType() . ' address is missing  "Street Address"');
		}
		if ($this->getCity() == ''){
			$validateSuccess = false;
			$messageStack->addSession('pageStack', 'Your ' . $this->getAddressType() . ' address is missing  "City"');
		}
		if ($this->getZoneId() == ''){
			$validateSuccess = false;
			$messageStack->addSession('pageStack', 'Your ' . $this->getAddressType() . ' address is missing  "State"');
		}
		if ($this->getCountryId() == ''){
			$validateSuccess = false;
			$messageStack->addSession('pageStack', 'Your ' . $this->getAddressType() . ' address is missing  "Country"');
		}
		if ($this->getPostcode() == ''){
			$validateSuccess = false;
			$messageStack->addSession('pageStack', 'Your ' . $this->getAddressType() . ' address is missing  "Postcode"');
		}

		if ($validateSuccess === true && $againstGoogleZone !== null){
			$Zone = Doctrine_Core::getTable('GoogleZones')
				->find($againstGoogleZone);
			$Polygon = unserialize($Zone->gmaps_polygon);
			$AddressArray = $this->toArray();

			$Coordinates = $this->getGoogleCoordinates($AddressArray);

			$validateSuccess = $this->polygonContains(
				$Polygon,
				$Coordinates['lng'],
				$Coordinates['lat']
			);

			if ($validateSuccess === false){
				$messageStack->addSession('pageStack', 'Your ' . $this->getAddressType() . ' address doesn\'t appear to be within our service area.');
			}
		}

		return $validateSuccess;
	}
}

?>