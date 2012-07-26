<?php
class DataManagementModuleProducts extends DataManagementModuleBase
{

	public function __construct()
	{
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Product Data Management');
		$this->setDescription('Import And Export Products Using This Module');

		$this->init(
			'products',
			true,
			__DIR__
		);
	}

	public function beforeActionProcess()
	{
		ProductTypeModules::loadModules();
	}

	public function runImport()
	{
		global $messageStack;
		$ImportFile = $this->getImportFileReader();
		$ImportFile->rewind();
		$ImportFile->parseHeaderLine();

		$x = 0;
		$Products = Doctrine_Core::getTable('Products');
		while($ImportFile->valid()){
			$CurrentRow =& $ImportFile->currentRow();
			$ProductModel = $CurrentRow->getColumnValue('v_products_model');
			if ($ProductModel !== false && $ProductModel !== null){
				$Product = $Products->findOneByProductsModel($ProductModel);
				$isNewProduct = false;
				if (!$Product){
					$Product = new Products();
					$Product->products_model = $ProductModel;
					$isNewProduct = true;
				}

				$Product->products_tax_class_id = tep_get_tax_title_class_id($CurrentRow->getColumnValue('v_tax_class_title', 0));
				$Product->products_weight = $CurrentRow->getColumnValue('v_products_weight', 0);
				$Product->products_type = $CurrentRow->getColumnValue('v_products_type', 'standard');
				$Product->products_in_box = $CurrentRow->getColumnValue('v_products_in_box', 0);
				$Product->products_featured = $CurrentRow->getColumnValue('v_products_featured', 0);
				$Product->products_date_available = $CurrentRow->getColumnValue('v_date_avail', date('Y-m-d'));
				$Product->products_status = ($CurrentRow->getColumnValue('v_status') == $inactive ? '0' : '1');
				$Product->products_image = $CurrentRow->getColumnValue('v_products_image', $default_image_product);

				$MembershipsNotEnabled = $CurrentRow->getColumnValue('v_memberships_not_enabled');
				if ($MembershipsNotEnabled !== null){
					$Qmembership = Doctrine_Query::create()
						->from('Membership m')
						->leftJoin('m.MembershipPlanDescription md')
						->where('md.language_id = ?', Session::get('languages_id'))
						->orderBy('sort_order')
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
					$notEnableMembershipsNames = explode(';', $MembershipsNotEnabled);
					$notenabledArr = array();
					foreach($Qmembership as $mInfo){
						if (in_array($mInfo['MembershipPlanDescription'][0]['name'], $notEnableMembershipsNames)){
							$notenabledArr[] = $mInfo['plan_id'];
						}
					}
					$Qmembership->free(true);

					$Product->membership_enabled = implode(';', $notenabledArr);
					unset($notenabledArr);
					unset($notEnableMembershipsNames);
				}

				foreach(sysLanguage::getLanguages() as $lInfo){
					$lID = $lInfo['id'];

					$Product->ProductsDescription[$lID]->language_id = $lID;
					$Product->ProductsDescription[$lID]->products_url = $CurrentRow->getColumnValue('v_products_url_' . $lID);
					$Product->ProductsDescription[$lID]->products_name = $CurrentRow->getColumnValue('v_products_name_' . $lID);
					$Product->ProductsDescription[$lID]->products_description = $CurrentRow->getColumnValue('v_products_description_' . $lID);
					$Product->ProductsDescription[$lID]->products_head_desc_tag = $CurrentRow->getColumnValue('v_products_head_desc_tag_' . $lID);
					$Product->ProductsDescription[$lID]->products_head_title_tag = $CurrentRow->getColumnValue('v_products_head_title_tag_' . $lID);
					$Product->ProductsDescription[$lID]->products_head_keywords_tag = $CurrentRow->getColumnValue('v_products_head_keywords_tag_' . $lID);
				}

				$ProductsCategories = $CurrentRow->getColumnValue('v_products_categories');
				if ($ProductsCategories !== null){
					$Product->ProductsToCategories->delete();

					$ProductsCategories = explode(';', $ProductsCategories);
					$ProductsCategories = array_unique($ProductsCategories);
					$ProductsCategories = array_values($ProductsCategories);
					foreach($ProductsCategories as $i => $catString){
						if (stristr($catString, '>')){
							$catPath = explode('>', $catString);
						}
						else {
							$catPath = array($catString);
						}

						$currentParent = 0;
						foreach($catPath as $catName){
							$Qcategory = Doctrine_Query::create()
								->select('c.categories_id')
								->from('Categories c')
								->leftJoin('c.CategoriesDescription cd')
								->where('cd.categories_name = ?', trim($catName));

							if (isset($currentParent)){
								$Qcategory->andWhere('c.parent_id = ?', $currentParent);
							}

							$Result = $Qcategory->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
							$Qcategory->free(true);
							unset($Qcategory);
							if ($Result){
								$categoryId = $Result[0]['categories_id'];
							}
							else {
								$Categories = new Categories();
								$Categories->parent_id = (isset($currentParent) ? $currentParent : 0);

								$Categories->CategoriesDescription[Session::get('languages_id')]->categories_name = $catName;
								$Categories->CategoriesDescription[Session::get('languages_id')]->language_id = Session::get('languages_id');
								$Categories->save();

								$categoryId = $Categories->categories_id;
								$Categories->free(true);
							}
							unset($Result);
							$currentParent = $categoryId;
						}

						$Product->ProductsToCategories[$i]['categories_id'] = $categoryId;
					}
					unset($ProductsCategories);
				}

				$ProductTypeModule = ProductTypeModules::getModule($Product->products_type);
				$ProductTypeModule->processProductImport($Product, $CurrentRow);

				EventManager::notify('DataImportBeforeSave', $CurrentRow, $Product);

				//echo '<pre>';print_r($Product->toArray(true));echo '</pre>';itwExit();
				$Product->save();

				foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
					$code = $PurchaseTypeModule->getCode();
					$AutogenerateTotal = $CurrentRow->getColumnValue('v_autogenerate_serials_' . $code, 0);
					if ($AutogenerateTotal > 0){
						$this->generateSerials($Product, $code, $AutogenerateTotal);
					}
				}
				$Product->save();

				/*if (isset($item['v_status']) && $item['v_status'] == $deleteStatus){
					$Product->delete();
					$status = 'Deleted';
				}
				else {
					$status = $Product->products_status;
				}

				$productLogArr = array(
					'ID:'			  => $Product->products_id,
					'Image:'		   => $Product->products_image,
					'Model:'		   => $Product->products_model,
					'Status:'		  => $status,
					'Tax Class ID:'	=> $Product->products_tax_class_id,
					'Weight:'		  => $Product->products_weight,
					'Type:'			=> $Product->products_type,
					'In Box:'		  => $Product->products_in_box,
					'Featured'		 => $Product->products_featured
				);

				EventManager::notify('DataImportProductLogBeforeExecute', &$Product, &$productLogArr);

				if ($isNewProduct === true){
					logNew('product', $productLogArr);
				}
				else {
					logUpdate('product', $productLogArr);
				}

				foreach($Product->ProductsDescription as $Description){
					$productDescLogArr = array(
						'ID:'				 => $Description['products_id'],
						'Language:'		   => $Description['language_id'],
						'Name:'			   => $Description['products_name'],
						'Description:'		=> $Description['products_description'],
						'URL:'				=> $Description['products_url'],
						'Header Title:'	   => $Description['products_head_title_tag'],
						'Header Description:' => $Description['products_head_desc_tag'],
						'Header Keywords:'	=> $Description['products_head_keywords_tag']
					);

					EventManager::notify('DataImportProductDescriptionLogBeforeExecute', &$productDescLogArr);

					if ($isNewProduct === true){
						logNew('product_description', $productDescLogArr);
					}
					else {
						logUpdate('product_description', $productDescLogArr);
					}
				}
				*/
				// end of row insertion code
				$Product->free(true);
				$x++;
				$this->checkMemoryThreshold($x);
			}
			$ImportFile->next();
		}
	}

	public function runExport()
	{
		global $messageStack, $ExceptionManager;
		$ExportFile = $this->getExportFileWriter();

		$HeaderRow = $ExportFile->newHeaderRow();

		$HeaderRow->addColumn('v_products_model');
		$HeaderRow->addColumn('v_products_image');
		$HeaderRow->addColumn('v_products_type');
		$HeaderRow->addColumn('v_products_in_box');
		$HeaderRow->addColumn('v_products_featured');
		$HeaderRow->addColumn('v_products_weight');
		$HeaderRow->addColumn('v_date_avail');
		$HeaderRow->addColumn('v_memberships_not_enabled');
		$HeaderRow->addColumn('v_products_categories');

		foreach(sysLanguage::getLanguages() as $lInfo){
			$lID = $lInfo['id'];

			$HeaderRow->addColumn('v_products_name_' . $lID);
			$HeaderRow->addColumn('v_products_description_' . $lID);
			$HeaderRow->addColumn('v_products_url_' . $lID);
			$HeaderRow->addColumn('v_products_head_title_tag_' . $lID);
			$HeaderRow->addColumn('v_products_head_desc_tag_' . $lID);
			$HeaderRow->addColumn('v_products_head_keywords_tag_' . $lID);
		}

		$HeaderRow->addColumn('v_tax_class_title');
		$HeaderRow->addColumn('v_status');

		foreach(ProductTypeModules::getModules() as $ProductTypeModule){
			if (method_exists($ProductTypeModule, 'addExportHeaderColumns')){
				$ProductTypeModule->addExportHeaderColumns(&$HeaderRow);
			}
		}

		$QProducts = Doctrine_Query::create()
			->from('Products');

		if ($_POST['start_record'] != ''){
			$start = (int) $_POST['start_record'];
			if ($_POST['end_record'] != ''){
				$end = (int) $_POST['end_record'];
				$QProducts->limit($end - $start);
			}
			$QProducts->offset($start);
		}
		elseif (isset($_POST['end_record'])){
			$end = (int) $_POST['end_record'];
			$QProducts->limit($end);
		}

		$Products = $QProducts->execute();
		$x = 0;
		foreach($Products as $Product){
			if (empty($Product->products_model)){
				continue;
			}

			$CurrentRow = $ExportFile->newRow();
			$CurrentRow->addColumn($Product->products_model, 'v_products_model');
			$CurrentRow->addColumn($Product->products_image, 'v_products_image');
			$CurrentRow->addColumn($Product->products_weight, 'v_products_weight');
			$CurrentRow->addColumn($Product->products_date_available, 'v_date_avail');
			$CurrentRow->addColumn($Product->products_type, 'v_products_type');
			$CurrentRow->addColumn($Product->products_in_box, 'v_products_in_box');
			$CurrentRow->addColumn($Product->products_featured, 'v_products_featured');
			$CurrentRow->addColumn($Product->TaxClass->tax_class_title, 'v_tax_class_title');
			$CurrentRow->addColumn(($Product->products_status == '1' ? $active : $inactive), 'v_status');

			$Descriptions = $Product->ProductsDescription;
			foreach(sysLanguage::getLanguages() as $lInfo){
				$lID = $lInfo['id'];

				if (isset($Descriptions[$lID])){
					$CurrentRow->addColumn($Descriptions[$lID]->products_name, 'v_products_name_' . $lID);
					$CurrentRow->addColumn($Descriptions[$lID]->products_description, 'v_products_description_' . $lID);
					$CurrentRow->addColumn($Descriptions[$lID]->products_url, 'v_products_url_' . $lID);
					$CurrentRow->addColumn($Descriptions[$lID]->products_head_title_tag, 'v_products_head_title_tag_' . $lID);
					$CurrentRow->addColumn($Descriptions[$lID]->products_head_desc_tag, 'v_products_head_desc_tag_' . $lID);
					$CurrentRow->addColumn($Descriptions[$lID]->products_head_keywords_tag, 'v_products_head_keywords_tag_' . $lID);
				}
			}

			$Categories = $Product->ProductsToCategories;
			$catPaths = array();
			foreach($Categories as $Category){
				$CurrentCategory = $Category->Categories;
				$catPath = array();
				if ($CurrentCategory->parent_id == 0){
					$catPath[] = trim($CurrentCategory->CategoriesDescription[Session::get('languages_id')]->categories_name);
				}
				else {
					while($CurrentCategory->parent_id > 0){
						$catPath[] = trim($CurrentCategory->CategoriesDescription[Session::get('languages_id')]->categories_name);
						$CurrentCategory = $CurrentCategory->Parent;
					}
				}
				$catPaths[] = implode('>', array_reverse($catPath));
			}
			$CurrentRow->addColumn(implode(';', $catPaths), 'v_products_categories');

			$nmembershipsString = array();
			if ($Product->membership_enabled != ''){
				$notEnabledMemberships = explode(';', $Product->membership_enabled);
				$Qmembership = Doctrine_Query::create()
					->from('Membership m')
					->leftJoin('m.MembershipPlanDescription md')
					->where('md.language_id = ?', Session::get('languages_id'))
					->andWhereIn('m.plan_id', $notEnabledMemberships)
					->orderBy('sort_order')
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				foreach($Qmembership as $mInfo){
					$nmembershipsString[] = $mInfo['MembershipPlanDescription'][0]['name'];
				}
			}
			$CurrentRow->addColumn(implode(';', $nmembershipsString), 'v_memberships_not_enabled');

			foreach(ProductTypeModules::getModules() as $ProductTypeModule){
				if (method_exists($ProductTypeModule, 'addExportRowColumns')){
					$ProductTypeModule->addExportRowColumns($CurrentRow, $Product);
				}
			}

			EventManager::notify('DataExportBeforeFileLineCommit', $CurrentRow, $Product);

			$x++;
			$Product->free(true);
			$this->checkMemoryThreshold($x);
		}
		//print_r($ExportFile);
		$ExportFile->output();
	}

	private function generateSerials(&$Product, $type, $numOfBarcodes)
	{
		$PurchaseType = $Product->ProductsPurchaseTypes[$type];
		if ($PurchaseType->use_serials == 1){
			$PurchaseTypeModule = PurchaseTypeModules::getModule($type);
			$AvailableItems = $PurchaseType->InventoryItems[$PurchaseTypeModule->getConfigData('INVENTORY_AVAILABLE_STATUS')];

			$total = (int)$numOfBarcodes;
			for($i = 0; $i < $total; $i++){
				$exists = true;
				$newSerialNumber = tep_rand(111111, 999999);
				while($exists === true){
					$QCheck = Doctrine_Query::create()
						->select('count(*) as total')
						->from('ProductsInventoryItemsSerials')
						->where('serial_number = ?', $newSerialNumber)
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
					if ($QCheck[0]['total'] > 0){
						$newSerialNumber = tep_rand(111111, 999999);
					}
					else {
						$exists = false;
					}
				}

				$NewSerial = $AvailableItems->Serials
					->getTable()
					->create();
				$NewSerial->serial_number = $newSerialNumber;

				$AvailableItems->Serials->add($NewSerial);
			}
		}
	}
}
