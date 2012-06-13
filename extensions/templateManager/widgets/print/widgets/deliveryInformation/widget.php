<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerPrintWidgetDeliveryInformation extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('deliveryInformation');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		$Address = $Sale->AddressManager->getAddress('delivery');
		if ($boxWidgetProperties->company){
			$htmlText .= 'Company: ' . $Address->getCompany() . '<br/>';
		}

		$entry_name = explode(' ', $Address->getName());

		if ($boxWidgetProperties->firstname){
			$htmlText .= 'First Name: ' . (isset($entry_name[0]) ? $entry_name[0] : '') . '<br/>';
		}
		if ($boxWidgetProperties->lastname){
			$htmlText .= 'Last Name: ' . (isset($entry_name[1]) ? $entry_name[1] : '') . '<br/>';
		}
		if ($boxWidgetProperties->name){
			$htmlText .= 'Name: ' . $Address->getName() . '<br/>';
		}

		if ($boxWidgetProperties->cif){
			$htmlText .= 'CIF: ' . $Address->getCIF() . '<br/>';
		}

		if ($boxWidgetProperties->vat){
			$htmlText .= 'VAT: ' . $Address->getVAT() . '<br/>';
		}

		if ($boxWidgetProperties->gender){
			$htmlText .= 'Gender: ' . $Address->getGender() . '<br/>';
		}

		if ($boxWidgetProperties->dob){
			$htmlText .= 'Date of Birth: ' . $Address->getDateOfBirth() . '<br/>';
		}

		if ($boxWidgetProperties->fulladdress){

			$htmlText .= 'Address: ' . $Address->getStreetAddress() . '<br/>' . $Address->getCity() . ', ' . $Address->getState() . ' ' . $Address->getPostcode() . '<br/>';
		}

		if (isset($boxWidgetProperties->street_address) && $boxWidgetProperties->street_address){
			$htmlText .= 'Street Address: ' . $Address->getStreetAddress() . '<br/>';
		}

		if ($boxWidgetProperties->city){
			$htmlText .= 'City: ' . $Address->getCity() . '<br/>';
		}

		if ($boxWidgetProperties->state){
			$htmlText .= 'State: ' . $Address->getState() . '<br/>';
		}

		if ($boxWidgetProperties->postcode){
			$htmlText .= 'Postcode: ' . $Address->getPostcode() . '<br/>';
		}

		if ($boxWidgetProperties->country){
			$htmlText .= 'Country: ' . $Address->getCountry() . '<br/>';
		}

		if ($boxWidgetProperties->telephone){
			$htmlText .= 'Telephone: ' . $Sale->getTelephone() . '<br/>';
		}

		if ($boxWidgetProperties->email){
			$htmlText .= 'Email: ' . $Sale->getEmailAddress() . '<br/>';
		}

		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>