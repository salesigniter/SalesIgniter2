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

	public function getSupportedColumns() {
		$supportedColumns = array(
			'v_customers_email_address' => true,
			'v_customers_firstname'     => true,
			'v_customers_lastname'      => true,
			'v_customers_telephone'     => true,
			'v_customers_dob'           => true,
			'v_customers_gender'        => true,
			'v_customers_newsletter'    => true,
			'v_customers_fax'           => true,
			'v_customers_language_id'   => true
		);

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

		for($i = 1; $i <= $maxAddresses; $i++){
			$supportedColumns = array_merge($supportedColumns, array(
				'v_customers_addressbook_firstname_' . $i  => true,
				'v_customers_addressbook_lastname_' . $i   => true,
				'v_customers_addressbook_gender_' . $i     => true,
				'v_customers_addressbook_address_' . $i    => true,
				'v_customers_addressbook_city_' . $i       => true,
				'v_customers_addressbook_state_' . $i      => true,
				'v_customers_addressbook_postcode_' . $i   => true,
				'v_customers_addressbook_country_' . $i    => true,
				'v_customers_addressbook_is_default_' . $i => true
			));
		}
		return $supportedColumns;
	}

	public function runImport() {
		$ImportFile = $this->getImportFileReader();
		$ImportFile->rewind();
		$ImportFile->parseHeaderLine();

		$x = 0;
		$Customers = Doctrine_Core::getTable('Customers');
		while($ImportFile->valid()){
			$CurrentRow = $ImportFile->currentRow();
			$CustomersEmail = $CurrentRow->getColumnValue('v_customers_email_address');
			if ($CustomersEmail === false || $CustomersEmail === null){
				$CustomersEmail = 'example@example' . $x . '.com';
			}

			if ($CustomersEmail !== false && $CustomersEmail !== null){
				$isNewCustomer = false;
				$Customer = $Customers->findOneByCustomersEmailAddress($CustomersEmail);
				if (!$Customer){
					$Customer = new Customers();
					$Customer->customers_email_address = $CustomersEmail;
					$Customer->CustomersInfo->customers_info_number_of_logons = 0;
					$Customer->CustomersInfo->customers_info_date_account_created = date('Y-m-d H:i:s');
					$Customer->CustomersInfo->global_product_notifications = 0;
					$isNewCustomer = true;
				}

				$CustomerLanguageId = $CurrentRow->getColumnValue('v_customers_language_id');
				$CustomerNewsletter = $CurrentRow->getColumnValue('v_customers_newsletter');
				$CustomerFirstname = $CurrentRow->getColumnValue('v_customers_firstname');
				$CustomerLastname = $CurrentRow->getColumnValue('v_customers_lastname');
				if ($CustomerFirstname === false || $CustomerFirstname === null){
					$CustomerFirstname = $CurrentRow->getColumnValue('v_customers_addressbook_firstname_1');
					if ($CustomerFirstname == ''){
						$CustomerFirstname = $CurrentRow->getColumnValue('v_customers_addressbook_company_1');
					}
					if ($CustomerFirstname == ''){
						$CustomerFirstname = 'NotFound';
					}
				}
				if ($CustomerLastname === false || $CustomerLastname === null){
					$CustomerLastname = $CurrentRow->getColumnValue('v_customers_addressbook_lastname_1');
					if ($CustomerLastname == ''){
						$CustomerLastname = $CurrentRow->getColumnValue('v_customers_addressbook_company_1');
					}
					if ($CustomerLastname == ''){
						$CustomerLastname = 'NotFound';
					}
				}

				$Customer->customers_firstname = $CustomerFirstname;
				$Customer->customers_lastname = $CustomerLastname;
				$Customer->customers_telephone = $CurrentRow->getColumnValue('v_customers_telephone');
				$Customer->customers_dob = $CurrentRow->getColumnValue('v_customers_dob');
				$Customer->customers_gender = $CurrentRow->getColumnValue('v_customers_gender');
				$Customer->customers_newsletter = (strtolower($CustomerNewsletter) == 'no' ? 0 : 1);
				$Customer->customers_fax = $CurrentRow->getColumnValue('v_customers_fax');
				$Customer->language_id = ($CustomerLanguageId == '' || $CustomerLanguageId == 0 ? Session::get('languages_id') : $CustomerLanguageId);

				$i = 1;
				$DefaultDeliveryAddress = 0;
				$DefaultAddress = 0;
				while(true){
					$AddressBookFirstname = $CurrentRow->getColumnValue('v_customers_addressbook_firstname_' . $i);
					$AddressBookLastname = $CurrentRow->getColumnValue('v_customers_addressbook_lastname_' . $i);
					$AddressBookPostcode = $CurrentRow->getColumnValue('v_customers_addressbook_postcode_' . $i);
					$AddressBookState = $CurrentRow->getColumnValue('v_customers_addressbook_state_' . $i);
					if (
						($AddressBookFirstname === false || $AddressBookFirstname === null) &&
						($AddressBookLastname === false || $AddressBookLastname === null) &&
						($AddressBookPostcode === false || $AddressBookPostcode === null) &&
						($AddressBookState === false || $AddressBookState === null)
					){
						break;
					}

					$AddressBookGender = $CurrentRow->getColumnValue('v_customers_addressbook_gender_' . $i);
					$AddressBookStreetAddress = $CurrentRow->getColumnValue('v_customers_addressbook_address_' . $i);
					$AddressBookCity = $CurrentRow->getColumnValue('v_customers_addressbook_city_' . $i);
					$AddressBookCompany = $CurrentRow->getColumnValue('v_customers_addressbook_company_' . $i);
					$AddressBookCountry = $CurrentRow->getColumnValue('v_customers_addressbook_country_' . $i);
					$AddressIsDefault = $CurrentRow->getColumnValue('v_customers_addressbook_is_default_' . $i);
					$AddressIsDefaultDelivery = $CurrentRow->getColumnValue('v_customers_addressbook_is_default_delivery_' . $i);

					$Qcheck = Doctrine_Query::create()
						->from('AddressBook')
						->where('customers_id = ?', $Customer->customers_id)
						->andWhere('entry_firstname = ?', $AddressBookFirstname)
						->andWhere('entry_lastname = ?', $AddressBookLastname)
						->andWhere('entry_gender = ?', $AddressBookGender)
						->andWhere('entry_street_address = ?', $AddressBookStreetAddress)
						->andWhere('entry_city = ?', $AddressBookCity)
						->andWhere('entry_state = ?', $AddressBookState)
						->andWhere('entry_postcode = ?', $AddressBookPostcode)
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

					$isNewAddress = true;
					if (count($Qcheck) > 0){
						$isNewAddress = false;
					}
					unset($Qcheck);

					if ($isNewAddress === true){
						$Address = new AddressBook();
						$Address->entry_firstname = $AddressBookFirstname;
						$Address->entry_lastname = $AddressBookLastname;
						$Address->entry_company = $AddressBookCompany;
						$Address->entry_gender = $AddressBookGender;
						$Address->entry_street_address = $AddressBookStreetAddress;
						$Address->entry_city = $AddressBookCity;
						$Address->entry_state = $AddressBookState;
						$Address->entry_postcode = $AddressBookPostcode;
						$Address->entry_country_id = '0';
						$Address->entry_zone_id = '0';

						$Qcountry = Doctrine_Query::create()
							->select('countries_id')
							->from('Countries')
							->where('countries_name = ?', $AddressBookCountry)
							->orWhere('countries_iso_code_2 = ?', $AddressBookCountry)
							->orWhere('countries_iso_code_3 = ?', $AddressBookCountry)
							->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

						if (count($Qcountry) > 0){
							$Address->entry_country_id = $Qcountry[0]['countries_id'];
						}
						unset($Qcountry);

						$QZones = Doctrine_Query::create()
							->select('zone_id')
							->from('Zones')
							->where('zone_country_id = ?', $Address->entry_country_id)
							->andWhere('zone_name = ?', $AddressBookState)
							->orWhere('zone_code = ?', $AddressBookState)
							->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

						if (count($QZones) > 0){
							foreach($QZones as $zInfo){
								if ($zInfo['zone_country_id'] == $Address->entry_country_id){
									$Address->entry_zone_id = $zInfo['zone_id'];
									break;
								}
							}
						}
						unset($QZones);

						$Customer->AddressBook->add($Address);

						if (strtolower($AddressIsDefault) == 'yes'){
							$DefaultAddress =& $Address;
						}

						if (strtolower($AddressIsDefaultDelivery) == 'yes'){
							$DefaultDeliveryAddress =& $Address;
						}
					}
					$i++;
				}
				//echo '<pre>';print_r($Customer->toArray());
				$Customer->save();
				if (is_object($DefaultAddress) || is_object($DefaultDeliveryAddress)){
					if (is_object($DefaultAddress)){
						$Customer->customers_default_address_id = $DefaultAddress->address_book_id;
					}
					if (is_object($DefaultDeliveryAddress)){
						$Customer->customers_delivery_address_id = $DefaultDeliveryAddress->address_book_id;
					}
					$Customer->save();
					unset($DefaultAddress);
					unset($DefaultDeliveryAddress);
				}
				$Customer->free(true);
				$x++;
				$this->checkMemoryThreshold($x);
				//error_log($x . '-', 3, sysConfig::getDirFsCatalog() . 'error_log');
			}
			$ImportFile->next();
		}
	}

	public function runExport($Ids = array(), $Columns = array()) {
		$ExportFile = $this->getExportFileWriter();

		$HeaderRow = $ExportFile->newHeaderRow();
		$addColumns = $this->getSupportedColumns();

		if (is_array($Columns) && sizeof($Columns) > 0){
			foreach($addColumns as $k => $v){
				$addColumns[$k] = false;
			}
			foreach($Columns as $colName){
				if (isset($addColumns[$colName])){
					$addColumns[$colName] = true;
				}
			}
		}
		foreach($addColumns as $k => $include){
			if ($include === true){
				$HeaderRow->addColumn($k);
			}
		}

		if (is_array($Ids) && sizeof($Ids) > 0){
			$Customers = Doctrine_Query::create()
				->from('Customers')
				->whereIn('customers_id', $Ids)
				->execute();
		}else{
			$Customers = Doctrine_Core::getTable('Customers')
				->findAll();
		}
		foreach($Customers as $Customer){
			$CurrentRow = $ExportFile->newRow();
			if ($addColumns['v_customers_email_address'] === true){
				$CurrentRow->addColumn($Customer->customers_email_address, 'v_customers_email_address');
			}
			if ($addColumns['v_customers_firstname'] === true){
				$CurrentRow->addColumn($Customer->customers_firstname, 'v_customers_firstname');
			}
			if ($addColumns['v_customers_lastname'] === true){
				$CurrentRow->addColumn($Customer->customers_lastname, 'v_customers_lastname');
			}
			if ($addColumns['v_customers_telephone'] === true){
				$CurrentRow->addColumn($Customer->customers_telephone, 'v_customers_telephone');
			}
			if ($addColumns['v_customers_dob'] === true){
				$CurrentRow->addColumn($Customer->customers_dob, 'v_customers_dob');
			}
			if ($addColumns['v_customers_gender'] === true){
				$CurrentRow->addColumn($Customer->customers_gender, 'v_customers_gender');
			}
			if ($addColumns['v_customers_newsletter'] === true){
				$CurrentRow->addColumn(($Customer->customers_newsletter == 1 ? 'Yes' : 'No'), 'v_customers_newsletter');
			}
			if ($addColumns['v_customers_fax'] === true){
				$CurrentRow->addColumn($Customer->customers_fax, 'v_customers_fax');
			}
			if ($addColumns['v_customers_language_id'] === true){
				$CurrentRow->addColumn($Customer->language_id, 'v_customers_language_id');
			}

			$i = 1;
			foreach($Customer->AddressBook as $AddressBook){
				if ($addColumns['v_customers_addressbook_firstname_' . $i] === true){
					$CurrentRow->addColumn($AddressBook->entry_firstname, 'v_customers_addressbook_firstname_' . $i);
				}
				if ($addColumns['v_customers_addressbook_lastname_' . $i] === true){
					$CurrentRow->addColumn($AddressBook->entry_lastname, 'v_customers_addressbook_lastname_' . $i);
				}
				if ($addColumns['v_customers_addressbook_gender_' . $i] === true){
					$CurrentRow->addColumn($AddressBook->entry_gender, 'v_customers_addressbook_gender_' . $i);
				}
				if ($addColumns['v_customers_addressbook_address_' . $i] === true){
					$CurrentRow->addColumn($AddressBook->entry_street_address, 'v_customers_addressbook_address_' . $i);
				}
				if ($addColumns['v_customers_addressbook_city_' . $i] === true){
					$CurrentRow->addColumn($AddressBook->entry_city, 'v_customers_addressbook_city_' . $i);
				}
				if ($addColumns['v_customers_addressbook_state_' . $i] === true){
					$CurrentRow->addColumn($AddressBook->Zones->zone_name, 'v_customers_addressbook_state_' . $i);
				}
				if ($addColumns['v_customers_addressbook_postcode_' . $i] === true){
					$CurrentRow->addColumn($AddressBook->entry_postcode, 'v_customers_addressbook_postcode_' . $i);
				}
				if ($addColumns['v_customers_addressbook_country_' . $i] === true){
					$CurrentRow->addColumn($AddressBook->Countries->countries_iso_code_2, 'v_customers_addressbook_country_' . $i);
				}
				if ($addColumns['v_customers_addressbook_is_default_' . $i] === true){
					$CurrentRow->addColumn(($AddressBook->address_book_id == $Customer->customers_default_address_id ? 'Yes' : 'No'), 'v_customers_addressbook_is_default_' . $i);
				}

				$i++;
			}
		}
		//print_r($ExportFile);
		$ExportFile->output();
	}
}
