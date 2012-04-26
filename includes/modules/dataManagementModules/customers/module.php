<?php
class DataManagementModuleCustomers extends DataManagementModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Customer Data Management');
		$this->setDescription('Import And Export Customers Using This Module');

		$this->init(
			'customers',
			true,
			__DIR__
		);
	}

	public function runImport(){
		$ImportFile = $this->getImportFileReader();
		$ImportFile->rewind();
		$ImportFile->parseHeaderLine();

		$Customers = Doctrine_Core::getTable('Customers');
		while($ImportFile->valid()){
			$CurrentRow = $ImportFile->currentRow();
			$item = array();
			while($CurrentRow->valid()){
				$CurrentColumn = $CurrentRow->current();

				$item[$CurrentColumn->key()] = $CurrentColumn->getText();

				$CurrentRow->next();
			}

			if (!isset($item['v_customers_email_address']) || strlen($item['v_customers_email_address']) <= 0){
				$ImportFile->next();
				continue;
			}

			$isNewCustomer = false;
			$Customer = $Customers->findOneByCustomersEmailAddress($item['v_customers_email_address']);
			if (!$Customer){
				$Customer = new Customers();
				$Customer->customers_email_address = $item['v_customers_email_address'];
				$Customer->CustomersInfo->customers_info_number_of_logons = 0;
				$Customer->CustomersInfo->customers_info_date_account_created = date('Y-m-d H:i:s');
				$Customer->CustomersInfo->global_product_notifications = 0;
				$isNewCustomer = true;
			}

			$Customer->customers_firstname = $item['v_customers_firstname'];
			$Customer->customers_lastname = $item['v_customers_lastname'];
			$Customer->customers_telephone = $item['v_customers_telephone'];
			$Customer->customers_dob = $item['v_customers_dob'];
			$Customer->customers_gender = $item['v_customers_gender'];
			$Customer->customers_newsletter = (strtolower($item['v_customers_newsletter']) == 'no' ? 0 : 1);
			$Customer->customers_fax = $item['v_customers_fax'];
			$Customer->language_id = ($item['v_customers_language_id'] == '' || $item['v_customers_language_id'] == 0 ? Session::get('languages_id') : $item['v_customers_language_id']);

			$i = 1;
			$DefaultDeliveryAddress = 0;
			$DefaultAddress = 0;
			while(true){
				if (
					(!isset($item['v_customers_addressbook_firstname_' . $i]) || empty($item['v_customers_addressbook_firstname_' . $i])) &&
					(!isset($item['v_customers_addressbook_lastname_' . $i]) || empty($item['v_customers_addressbook_lastname_' . $i])) &&
					(!isset($item['v_customers_addressbook_postcode_' . $i]) || empty($item['v_customers_addressbook_postcode_' . $i])) &&
					(!isset($item['v_customers_addressbook_state_' . $i]) || empty($item['v_customers_addressbook_state_' . $i]))
				){
					break;
				}

				$Qcheck = Doctrine_Query::create()
					->from('AddressBook')
					->where('customers_id = ?', $Customer->customers_id)
					->andWhere('entry_firstname = ?', $item['v_customers_addressbook_firstname_' . $i])
					->andWhere('entry_lastname = ?', $item['v_customers_addressbook_lastname_' . $i])
					->andWhere('entry_gender = ?', $item['v_customers_addressbook_gender_' . $i])
					->andWhere('entry_street_address = ?', $item['v_customers_addressbook_address_' . $i])
					->andWhere('entry_city = ?', $item['v_customers_addressbook_city_' . $i])
					->andWhere('entry_state = ?', $item['v_customers_addressbook_state_' . $i])
					->andWhere('entry_postcode = ?', $item['v_customers_addressbook_postcode_' . $i])
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

				$isNewAddress = true;
				if (count($Qcheck) > 0){
					$isNewAddress = false;
				}

				if ($isNewAddress === true){
					$Address = new AddressBook();
					$Address->entry_firstname = $item['v_customers_addressbook_firstname_' . $i];
					$Address->entry_lastname = $item['v_customers_addressbook_lastname_' . $i];
					$Address->entry_company = $item['v_customers_addressbook_company_' . $i];
					$Address->entry_gender = $item['v_customers_addressbook_gender_' . $i];
					$Address->entry_street_address = $item['v_customers_addressbook_address_' . $i];
					$Address->entry_city = $item['v_customers_addressbook_city_' . $i];
					$Address->entry_state = $item['v_customers_addressbook_state_' . $i];
					$Address->entry_postcode = $item['v_customers_addressbook_postcode_' . $i];
					$Address->entry_country_id = '0';
					$Address->entry_zone_id = '0';

					$Qcountry = Doctrine_Query::create()
						->select('countries_id')
						->from('Countries')
						->where('countries_name = ?', $items['v_customers_addressbook_country_' . $i])
						->orWhere('countries_iso_code_2 = ?', $items['v_customers_addressbook_country_' . $i])
						->orWhere('countries_iso_code_3 = ?', $items['v_customers_addressbook_country_' . $i])
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

					if (count($Qcountry) > 0){
						$Address->entry_country_id = $Qcountry[0]['countries_id'];
					}

					$QZones = Doctrine_Query::create()
						->select('zone_id')
						->from('Zones')
						->where('zone_country_id = ?', $Address->entry_country_id)
						->andWhere('zone_name = ?', $items['v_customers_addressbook_state_' . $i])
						->orWhere('zone_code = ?', $items['v_customers_addressbook_state_' . $i])
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

					if (count($QZones) > 0){
						foreach($QZones as $zInfo){
							if ($zInfo['zone_country_id'] == $Address->entry_country_id){
								$Address->entry_zone_id = $zInfo['zone_id'];
								break;
							}
						}
					}

					$Customer->AddressBook->add($Address);

					if (strtolower($item['v_customers_addressbook_is_default_' . $i]) == 'yes'){
						$DefaultAddress =& $Address;
					}

					if (strtolower($item['v_customers_addressbook_is_default_delivery_' . $i]) == 'yes'){
						$DefaultDeliveryAddress =& $Address;
					}
				}
				$i++;
			}
			//echo '<pre>';print_r($Customer->toArray());
			$Customer->save();
			if (is_object($DefaultAddress) || is_object($DefaultDeliveryAddress)){
				if (is_object($DefaultAddress)){
					$Customers->customers_default_address_id = $DefaultAddress->address_book_id;
				}
				if (is_object($DefaultDeliveryAddress)){
					$Customers->customers_delivery_address_id = $DefaultDeliveryAddress->address_book_id;
				}
				$Customer->save();
			}
			$ImportFile->next();
		}
	}

	public function runExport(){
		$ExportFile = $this->getExportFileWriter();

		$HeaderRow = $ExportFile->newHeaderRow();

		$HeaderRow->addColumn('v_customers_email_address');
		$HeaderRow->addColumn('v_customers_firstname');
		$HeaderRow->addColumn('v_customers_lastname');
		$HeaderRow->addColumn('v_customers_telephone');
		$HeaderRow->addColumn('v_customers_dob');
		$HeaderRow->addColumn('v_customers_gender');
		$HeaderRow->addColumn('v_customers_newsletter');
		$HeaderRow->addColumn('v_customers_fax');
		$HeaderRow->addColumn('v_customers_language_id');

		$QCount = Doctrine_Query::create()
			->select('count(*) as total')
			->from('AddressBook')
			->groupBy('customers_id')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$maxAddresses = 0;
		foreach($QCount as $aInfo){
			if ($aInfo['total'] > $maxAddresses){
				$maxAddresses = $aInfo['total'];
			}
		}

		for ($i=1; $i<=$maxAddresses; $i++){
			$HeaderRow->addColumn('v_customers_addressbook_firstname_' . $i);
			$HeaderRow->addColumn('v_customers_addressbook_lastname_' . $i);
			$HeaderRow->addColumn('v_customers_addressbook_gender_' . $i);
			$HeaderRow->addColumn('v_customers_addressbook_address_' . $i);
			$HeaderRow->addColumn('v_customers_addressbook_city_' . $i);
			$HeaderRow->addColumn('v_customers_addressbook_state_' . $i);
			$HeaderRow->addColumn('v_customers_addressbook_postcode_' . $i);
			$HeaderRow->addColumn('v_customers_addressbook_country_' . $i);
			$HeaderRow->addColumn('v_customers_addressbook_is_default_' . $i);
		}

		$Customers = Doctrine_Core::getTable('Customers')
			->findAll();
		foreach($Customers as $Customer){
			$CurrentRow = $ExportFile->newRow();
			$CurrentRow->addColumn($Customer->customers_email_address, 'v_customers_email_address');
			$CurrentRow->addColumn($Customer->customers_firstname, 'v_customers_firstname');
			$CurrentRow->addColumn($Customer->customers_lastname, 'v_customers_lastname');
			$CurrentRow->addColumn($Customer->customers_telephone, 'v_customers_telephone');
			$CurrentRow->addColumn($Customer->customers_dob, 'v_customers_dob');
			$CurrentRow->addColumn($Customer->customers_gender, 'v_customers_gender');
			$CurrentRow->addColumn(($Customer->customers_newsletter == 1 ? 'Yes' : 'No'), 'v_customers_newsletter');
			$CurrentRow->addColumn($Customer->customers_fax, 'v_customers_fax');
			$CurrentRow->addColumn($Customer->language_id, 'v_customers_language_id');

			$i = 1;
			foreach($Customer->AddressBook as $AddressBook){
				$CurrentRow->addColumn($AddressBook->entry_firstname, 'v_customers_addressbook_firstname_' . $i);
				$CurrentRow->addColumn($AddressBook->entry_lastname, 'v_customers_addressbook_lastname_' . $i);
				$CurrentRow->addColumn($AddressBook->entry_gender, 'v_customers_addressbook_gender_' . $i);
				$CurrentRow->addColumn($AddressBook->entry_street_address, 'v_customers_addressbook_address_' . $i);
				$CurrentRow->addColumn($AddressBook->entry_city, 'v_customers_addressbook_city_' . $i);
				$CurrentRow->addColumn($AddressBook->Zones->zone_name, 'v_customers_addressbook_state_' . $i);
				$CurrentRow->addColumn($AddressBook->entry_postcode, 'v_customers_addressbook_postcode_' . $i);
				$CurrentRow->addColumn($AddressBook->Countries->countries_iso_code_2, 'v_customers_addressbook_country_' . $i);
				$CurrentRow->addColumn(($AddressBook->address_book_id == $Customer->customers_default_address_id ? 'Yes' : 'No'), 'v_customers_addressbook_is_default_' . $i);

				$i++;
			}
		}
		//print_r($ExportFile);
		$ExportFile->output();
	}
}
