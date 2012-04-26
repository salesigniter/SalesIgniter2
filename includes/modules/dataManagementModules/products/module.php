<?php
class DataManagementModuleProducts extends DataManagementModuleBase
{

	public function __construct() {
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

	public function beforeActionProcess(){
		ProductTypeModules::loadModules();
	}

	public function runImport(){
		$ImportFile = $this->getImportFileReader();
		$ImportFile->rewind();
		$ImportFile->parseHeaderLine();

		while($ImportFile->valid()){
			$CurrentRow = $ImportFile->currentRow();
			$item = array();
			while($CurrentRow->valid()){
				$CurrentColumn = $CurrentRow->current();

				$item[$CurrentColumn->key()] = $CurrentColumn->getText();

				$CurrentRow->next();
			}

			if (!empty($item['v_products_model'])){
				$Qproduct = Doctrine_Query::create()
					->from('Products p')
					->where('p.products_model = ?', $item['v_products_model'])
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				$isNewProduct = false;
				if ($Qproduct){
					$Product = Doctrine_Core::getTable('Products')->find($Qproduct[0]['products_id']);
				}
				else {
					$Product = new Products();
					$Product->products_model = $item['v_products_model'];
					$Product->save();
					$isNewProduct = true;
				}

				$Product->products_tax_class_id = (isset($item['v_tax_class_title']) ? tep_get_tax_title_class_id($item['v_tax_class_title']) : '0');
				$Product->products_weight = (isset($item['v_products_weight']) ? $item['v_products_weight'] : '0');
				$Product->products_type = (isset($item['v_products_type']) ? $item['v_products_type'] : '');
				$Product->products_in_box = (isset($item['v_products_in_box']) ? $item['v_products_in_box'] : '0');
				$Product->products_featured = (isset($item['v_products_featured']) ? $item['v_products_featured'] : '0');
				$Product->products_date_available = (isset($item['v_date_avail']) ? $item['v_date_avail'] : null);
				$Product->products_status = (!isset($item['v_status']) || $item['v_status'] == $inactive ? '0' : '1');
				$Product->products_image = (!isset($item['v_products_image']) || $item['v_products_image'] == '' ? $default_image_product : $item['v_products_image']);

				if (isset($item['v_memberships_not_enabled']) && !empty($item['v_memberships_not_enabled'])){
					$Qmembership = Doctrine_Query::create()
						->from('Membership m')
						->leftJoin('m.MembershipPlanDescription md')
						->where('md.language_id = ?', Session::get('languages_id'))
						->orderBy('sort_order')
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
					$notEnableMembershipsNames = explode(';', $item['v_memberships_not_enabled']);
					$notenabledArr = array();
					foreach($Qmembership as $mInfo){
						if (in_array($mInfo['MembershipPlanDescription'][0]['name'], $notEnableMembershipsNames)){
							$notenabledArr[] = $mInfo['plan_id'];
						}
					}

					$Product->membership_enabled = implode(';', $notenabledArr);
				}

				$ProductsDescription =& $Product->ProductsDescription;
				foreach(sysLanguage::getLanguages() as $lInfo){
					$lID = $lInfo['id'];

					$CurrentDesc =& $ProductsDescription[$lID];

					$CurrentDesc->language_id = $lID;
					if (isset($item['v_products_url_' . $lID])){
						$CurrentDesc->products_url = $item['v_products_url_' . $lID];
					}

					if (isset($item['v_products_name_' . $lID])){
						$CurrentDesc->products_name = $item['v_products_name_' . $lID];
					}

					if (isset($item['v_products_description_' . $lID])){
						$CurrentDesc->products_description = $item['v_products_description_' . $lID];
					}

					if (isset($item['v_products_head_desc_tag_' . $lID])){
						$CurrentDesc->products_head_desc_tag = $item['v_products_head_desc_tag_' . $lID];
					}

					if (isset($item['v_products_head_title_tag_' . $lID])){
						$CurrentDesc->products_head_title_tag = $item['v_products_head_title_tag_' . $lID];
					}

					if (isset($item['v_products_head_keywords_tag_' . $lID])){
						$CurrentDesc->products_head_keywords_tag = $item['v_products_head_keywords_tag_' . $lID];
					}
				}

				if (!empty($item['v_products_categories'])){
					$Product->ProductsToCategories->delete();
					$ProductsToCategories = $Product->ProductsToCategories;

					$productsCategories = explode(';', $item['v_products_categories']);
					$productsCategories = array_unique($productsCategories);
					$productsCategories = array_values($productsCategories);
					foreach($productsCategories as $i => $catString){
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
							if ($Result){
								$categoryId = $Result[0]['categories_id'];
							}
							else {
								$Categories = new Categories();
								$Categories->parent_id = (isset($currentParent) ? $currentParent : 0);

								$Description =& $Categories->CategoriesDescription;
								$Description[Session::get('languages_id')]->categories_name = $catName;
								$Description[Session::get('languages_id')]->language_id = Session::get('languages_id');
								$Categories->save();

								$categoryId = $Categories->categories_id;
							}
							$currentParent = $categoryId;
						}

						$Product->ProductsToCategories[$i]['categories_id'] = $categoryId;
					}
				}

				foreach(ProductTypeModules::getModules() as $ProductTypeModule){
					$ProductTypeModule->processProductImport($Product, $item);
				}

				EventManager::notify('DataImportBeforeSave', $item, $Product);

				//echo '<pre>';print_r($Product->toArray(true));echo '</pre>';itwExit();
				$Product->save();

				foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
					$code = $PurchaseTypeModule->getCode();
					if (isset($item['v_autogenerate_barcodes_' . $code]) && $item['v_autogenerate_barcodes_' . $code] > 0){
						$this->generateBarcodes($Product, $code, $item['v_autogenerate_barcodes_' . $code]);
					}
				}
				$Product->save();

				if (isset($item['v_status']) && $item['v_status'] == $deleteStatus){
					$Product->delete();
					$status = 'Deleted';
				}
				else {
					$status = $Product->products_status;
				}

				/*$productLogArr = array(
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
				$Product->free();
			}
			$ImportFile->next();
		}
	}

	public function runExport(){
		$ExportFile = $this->getExportFileWriter();

		$HeaderRow = $ExportFile->newHeaderRow();

		$HeaderRow->addColumn('v_products_model');
		$HeaderRow->addColumn('v_products_image');
		$HeaderRow->addColumn('v_products_type');

		foreach(ProductTypeModules::getModules() as $ProductTypeModule){
			if (method_exists($ProductTypeModule, 'addExportHeaderColumns')){
				$ProductTypeModule->addExportHeaderColumns(&$HeaderRow);
			}
		}

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

		EventManager::notify('DataExportFullQueryFileLayoutHeader', &$HeaderRow);

		foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
			$HeaderRow->addColumn('v_autogenerate_barcodes_' . $PurchaseTypeModule->getCode());
		}

		$QfileLayout = Doctrine_Query::create()
			->select(
			'p.products_id, ' .
				'p.products_model as v_products_model, ' .
				'p.products_image as v_products_image, ' .
				'p.products_weight as v_products_weight, ' .
				'p.products_date_available as v_date_avail, ' .
				'p.products_tax_class_id as v_tax_class_id, ' .
				'p.products_type as v_products_type, ' .
				'p.products_in_box as v_products_in_box, ' .
				'p.products_featured as v_products_featured, ' .
				'p.products_status as v_status, ' .
				'p.membership_enabled as v_memberships_not_enabled, ' .
				'(SELECT group_concat(p2c.categories_id) FROM ProductsToCategories p2c WHERE p2c.products_id = p.products_id) as v_products_categories'
		)->from('Products p')
			->where('p.products_model is not null')
			->andWhere('p.products_model != ?', '');

		foreach(ProductTypeModules::getModules() as $ProductTypeModule){
			if (method_exists($ProductTypeModule, 'addExportQueryConditions')){
				$ProductTypeModule->addExportQueryConditions($QfileLayout);
			}
		}

		EventManager::notify('DataExportFullQueryBeforeExecute', &$QfileLayout);

		$Result = $QfileLayout->execute(array(), Doctrine_Core::HYDRATE_SCALAR);

		$p = -1;
		foreach($Result as $pInfo){
			foreach($pInfo as $k => $v){
				$pInfo[substr($k, strpos($k, '_')+1)] = $v;
				unset($pInfo[$k]);
			}

			$p++;
			if (isset($_POST['start_num']) && (!empty($_POST['start_num']) || $_POST['start_num'] == 0)){
				if ($p < $_POST['start_num']) continue;
			}
			if (isset($_POST['num_items']) && !empty($_POST['num_items'])){
				if ($p >= ((int)$_POST['start_num'] + $_POST['num_items'])) break;
			}

			$CurrentRow = $ExportFile->newRow();
			$CurrentRow->addColumn($pInfo['v_products_model'], 'v_products_model');
			$CurrentRow->addColumn($pInfo['v_products_image'], 'v_products_image');
			$CurrentRow->addColumn($pInfo['v_products_weight'], 'v_products_weight');
			$CurrentRow->addColumn($pInfo['v_products_date_available'], 'v_date_avail');
			$CurrentRow->addColumn($pInfo['v_products_tax_class_id'], 'v_tax_class_id');
			$CurrentRow->addColumn($pInfo['v_products_type'], 'v_products_type');
			$CurrentRow->addColumn($pInfo['v_products_in_box'], 'v_products_in_box');
			$CurrentRow->addColumn($pInfo['v_products_featured'], 'v_products_featured');
			$CurrentRow->addColumn($pInfo['v_products_status'], 'v_status');

			foreach(sysLanguage::getLanguages() as $lInfo){
				$lID = $lInfo['id'];

				$Qdescription = Doctrine_Query::create()
					->from('ProductsDescription pd')
					->where('products_id = ?', $pInfo['products_id'])
					->andWhere('language_id = ?', $lID)
					->execute()->toArray();
				if (isset($Qdescription[$lID])){
					$CurrentRow->addColumn($Qdescription[$lID]['products_name'], 'v_products_name_' . $lID);
					$CurrentRow->addColumn($Qdescription[$lID]['products_description'], 'v_products_description_' . $lID);
					$CurrentRow->addColumn($Qdescription[$lID]['products_url'], 'v_products_url_' . $lID);
					$CurrentRow->addColumn($Qdescription[$lID]['products_head_title_tag'], 'v_products_head_title_tag_' . $lID);
					$CurrentRow->addColumn($Qdescription[$lID]['products_head_desc_tag'], 'v_products_head_desc_tag_' . $lID);
					$CurrentRow->addColumn($Qdescription[$lID]['products_head_keywords_tag'], 'v_products_head_keywords_tag_' . $lID);
				}
			}

			$categories = explode(',', $pInfo['v_products_categories']);
			$catPaths = array();
			foreach($categories as $categoryId){
				$currentParent = $categoryId;
				$catPath = array();
				while($currentParent > 0){
					$Qcategory = Doctrine_Query::create()
						->select('c.categories_id, c.parent_id, cd.categories_name')
						->from('Categories c')
						->leftJoin('c.CategoriesDescription cd')
						->where('c.categories_id = ?', $currentParent)
						->andWhere('cd.language_id = ?', Session::get('languages_id'))
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

					$catPath[] = trim($Qcategory[0]['CategoriesDescription'][0]['categories_name']);
					$currentParent = $Qcategory[0]['parent_id'];
				}
				$catPaths[] = implode('>', array_reverse($catPath));
			}
			$CurrentRow->addColumn(implode(';', $catPaths), 'v_products_categories');

			$nmembershipsString = '';
			if ($pInfo['v_memberships_not_enabled'] != ''){
				$notEnabledMemberships = explode(';',$pInfo['v_memberships_not_enabled']);
				$Qmembership = Doctrine_Query::create()
					->from('Membership m')
					->leftJoin('m.MembershipPlanDescription md')
					->where('md.language_id = ?', Session::get('languages_id'))
					->orderBy('sort_order')
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				foreach($Qmembership as $mInfo){
					if(in_array($mInfo['plan_id'], $notEnabledMemberships)){
						$nmembershipsString.= $mInfo['MembershipPlanDescription'][0]['name'].';';
					}
				}
				$nmembershipsString = substr($nmembershipsString,0,strlen($nmembershipsString)-1);
			}

			foreach(ProductTypeModules::getModules() as $ProductTypeModule){
				if (method_exists($ProductTypeModule, 'addExportRowColumns')){
					$ProductTypeModule->addExportRowColumns($CurrentRow, $pInfo);
				}
			}

			$CurrentRow->addColumn($nmembershipsString, 'v_memberships_not_enabled');
			$CurrentRow->addColumn(tep_get_tax_class_title($pInfo['v_tax_class_id']), 'v_tax_class_title');
			$CurrentRow->addColumn(($pInfo['v_status'] == '1' ? $active : $inactive), 'v_status');

			EventManager::notify('DataExportBeforeFileLineCommit', $CurrentRow, $pInfo);

			foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
				$CurrentRow->addColumn(0, 'v_autogenerate_barcodes_' . $PurchaseTypeModule->getCode());
			}
		}
		//print_r($ExportFile);
		$ExportFile->output();
	}

	private function generateBarcodes(&$Product, $type, $numOfBarcodes){
		$Qinventory = Doctrine_Query::create()
			->select('i.inventory_id')
			->from('ProductsInventory i')
			->where('products_id = ?', $Product->products_id)
			->andWhere('type = ?', $type)
			->andWhere('track_method = ?', 'barcode')
			->andWhere('controller = ?', 'normal')
			->fetchOne();
		if (!$Qinventory){
			$Qmax = Doctrine_Query::create()
				->select('MAX(inventory_id) as next_id')
				->from('ProductsInventory')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$ProductsInventory = new ProductsInventory();
			$ProductsInventory->inventory_id = $Qmax[0]['next_id'] + 1;
			$ProductsInventory->type = $type;
			$ProductsInventory->track_method = 'barcode';
			$ProductsInventory->controller = 'normal';

			$Product->ProductsInventory->add($ProductsInventory);
		}else{
			$ProductsInventory = $Product->ProductsInventory[$Qinventory->inventory_id];
		}
		$Barcodes = $ProductsInventory->ProductsInventoryBarcodes;
		$nextIndex = $Barcodes->key() + 1;

		$productName = $Product->ProductsDescription[Session::get('languages_id')]->products_name;
		$nameFix = strtolower(substr(str_replace(' ', '_', strip_tags($productName)), 0, 4));
		if (substr($nameFix, -1) == '_'){
			while(substr($nameFix, -1) == '_'){
				$nameFix = substr($nameFix, 0, -1);
			}
		}
		$nameFix .= '_' . $Product->products_id;
		$Qcheck = Doctrine_Query::create()
			->select('barcode')
			->from('ProductsInventoryBarcodes')
			->where('barcode like ?', $nameFix . '_' . $type . '_%')
			->orderBy('barcode desc')
			->limit('1')
			->execute(array(), Doctrine::HYDRATE_ARRAY);
		if ($Qcheck){
			$bCode = $Qcheck[0]['barcode'];
			$start = (int)substr($bCode, strrpos($bCode, '_') + 1) + 1;
		}
		else {
			$start = 1;
		}

		$total = (int)$numOfBarcodes;
		$endNumber = $start;

		for($i = 0; $i < $total; $i++){
			$numberString = $endNumber;
			if ($numberString < 100){
				if (strlen($numberString) == 2){
					$numberString = '0' . $numberString;
				}
				elseif (strlen($numberString) == 1) {
					$numberString = '00' . $numberString;
				}
			}
			$genBarcode = $nameFix . '_' . $type . '_' . $numberString;
			if (sysConfig::get('SYSTEM_BARCODE_FORMAT') == 'Code 39'){
				$genBarcode = strtoupper($genBarcode);
				$genBarcode = str_replace('_', '-', $genBarcode);
			}
			if (sysConfig::get('SYSTEM_BARCODE_FORMAT') == 'Code 25' || sysConfig::get('SYSTEM_BARCODE_FORMAT') == 'Code 25 Interleaved'){
				$genBarcode = strtotime(date('Y-m-d H:i:s')) . $endNumber;
				if (strlen($genBarcode) % 2 == 1){
					$genBarcode = '0' . $genBarcode;
				}
			}
			$endNumber++;

			$Barcodes[$nextIndex]->barcode = $genBarcode;
			$Barcodes[$nextIndex]->status = $status;

			/* ????Put in extension???? */
			if (isset($aID_string)){
				$Barcodes[$nextIndex]->attributes = $aID_string;
			}

			EventManager::notify('ProductBarcodeNewBeforeExecute', &$Barcodes[$nextIndex]);

			$newBarcodes[] = $nextIndex;
			$nextIndex++;
		}
	}
}
