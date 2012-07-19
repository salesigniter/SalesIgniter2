<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerWidgetBillingInformation extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('billingInformation', false, __DIR__);
	}

	public function showLayoutPreview($WidgetSettings)
	{
		$addressArray = array();
		if ($WidgetSettings['settings']->fulladdress || $WidgetSettings['settings']->company){
			$addressArray['entry_company'] = 'John Doe Inc.';
		}

		if ($WidgetSettings['settings']->fulladdress || $WidgetSettings['settings']->name){
			$addressArray['entry_name'] = 'John Doe';
		}
		else {
			if ($WidgetSettings['settings']->firstname){
				$addressArray['entry_firstname'] = 'John';
			}
			if ($WidgetSettings['settings']->lastname){
				$addressArray['entry_lastname'] = 'Doe';
			}
		}

		if ($WidgetSettings['settings']->fulladdress || $WidgetSettings['settings']->street_address){
			$addressArray['entry_street_address'] = '11 My Way';
		}

		if ($WidgetSettings['settings']->fulladdress || $WidgetSettings['settings']->city){
			$addressArray['entry_city'] = 'New York';
		}

		if ($WidgetSettings['settings']->fulladdress || $WidgetSettings['settings']->state){
			$addressArray['entry_state'] = 'New York';
		}

		if ($WidgetSettings['settings']->fulladdress || $WidgetSettings['settings']->postcode){
			$addressArray['entry_postcode'] = '77777';
		}

		if ($WidgetSettings['settings']->fulladdress || $WidgetSettings['settings']->country){
			$addressArray['entry_country'] = 'United States';
		}

		$htmlText = tep_address_format(1, $addressArray, true, '', '<br>') . '<br>';

		if ($WidgetSettings['settings']->cif){
			$htmlText .= 'CIF: 000000000<br/>';
		}

		if ($WidgetSettings['settings']->vat){
			$htmlText .= 'VAT: 000000000<br/>';
		}

		if ($WidgetSettings['settings']->gender){
			$htmlText .= 'Gender: Male/Female<br/>';
		}

		if ($WidgetSettings['settings']->dob){
			$htmlText .= 'Date of Birth: 12/25/1970<br/>';
		}

		if ($WidgetSettings['settings']->telephone){
			$htmlText .= 'Telephone: 555-7777<br/>';
		}

		if ($WidgetSettings['settings']->email){
			$htmlText .= 'Email: johndoe@domain.com<br/>';
		}
		return $htmlText;
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		$Address = $Sale->AddressManager->getAddress('billing');

		$formatAddress = function ($Address) use ($boxWidgetProperties)
		{
			$addressArray = array();
			if ($boxWidgetProperties->fulladdress){
				$htmlText = $Address->format();
			}
			else {
				if ($boxWidgetProperties->company){
					$addressArray['entry_company'] = $Address->getCompany();
				}

				if ($boxWidgetProperties->name){
					$addressArray['entry_name'] = $Address->getName();
				}
				else {
					if ($boxWidgetProperties->firstname){
						$addressArray['entry_firstname'] = $Address->getFirstName();
					}
					if ($boxWidgetProperties->lastname){
						$addressArray['entry_lastname'] = $Address->getLastName();
					}
				}

				if (isset($boxWidgetProperties->street_address) && $boxWidgetProperties->street_address){
					$addressArray['entry_street_address'] = $Address->getStreetAddress();
				}

				if ($boxWidgetProperties->city){
					$addressArray['entry_city'] = $Address->getCity();
				}

				if ($boxWidgetProperties->state){
					$addressArray['entry_state'] = $Address->getState();
				}

				if ($boxWidgetProperties->postcode){
					$addressArray['entry_postcode'] = $Address->getPostcode();
				}

				if ($boxWidgetProperties->country){
					$addressArray['entry_country'] = $Address->getCountry();
				}

				$htmlText = tep_address_format($Address->getFormatId(), $addressArray, true) . '<br>';
			}
			return $htmlText;
		};

		$htmlText = $formatAddress($Address);
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