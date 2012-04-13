<?php
ProductTypeModules::loadModules();

/* New Tabbed Logging - BEGIN */
require('includes/classes/data_populate/export.php');
$dataExport = new dataExport();
$uploaded = false;
if (isset($_FILES['usrfl'])){
	$upload = new upload('usrfl');
	$upload->set_extensions(array('txt', 'xls', 'csv', 'tsv'));
	$upload->set_destination($dataExport->tempDir);
	if ($upload->parse() && $upload->save()){
		$uploaded = true;
		$showLogInfo = true;
	}
}
$logArray = array(
	'new'	=> array(),
	'update' => array(),
	'error'  => array()
);
function logNew($type, $lInfo) {
	global $logArray;
	$logArray['new'][$type][] = $lInfo;
}

function logUpdate($type, $lInfo) {
	global $logArray;
	$logArray['update'][$type][] = $lInfo;
}

function logError($type, $lInfo) {
	global $logArray;
	$logArray['error'][$type][] = $lInfo;
}

function getLogTabs() {
	global $logArray;
	$html = '';
	if (sizeof($logArray['error']) > 0){
		$html .= '<li><a href="#tab1">Errors</a></li>';
	}
	if (sizeof($logArray['update']) > 0){
		$html .= '<li><a href="#tab2">Updates</a></li>';
	}
	if (sizeof($logArray['new']) > 0){
		$html .= '<li><a href="#tab3">New Entries</a></li>';
	}
	return $html;
}

function getLogDivs() {
	global $logArray;
	$html = '';
	if (sizeof($logArray['error']) > 0){
		$html .= '<div id="tab1">' . logSection('error', $logArray['error']) . '</div>';
	}
	if (sizeof($logArray['update']) > 0){
		$html .= '<div id="tab2">' . logSection('update', $logArray['update']) . '</div>';
	}
	if (sizeof($logArray['new']) > 0){
		$html .= '<div id="tab3">' . logSection('new', $logArray['new']) . '</div>';
	}
	$html .= '</div>';
	return $html;
}

function infoTable($columns, $info) {
	$html = '<table cellpadding="2" cellspacing="0" border="0">';
	for($i = 0; $i < sizeof($info); $i++){
		$col = 1;
		$html .= '<tr>';
		foreach($info[$i] as $text => $val){
			$html .= '<td class="main" valign="top"><b>' . $text . '</b></td><td class="main" valign="top">' . $val . '</td>';
			$col++;
			if ($col > $columns){
				$col = 1;
				$html .= '</tr><tr>';
			}
		}
		$html .= '</tr><tr><td colspan="' . $columns . '"><br></td></tr>';
	}
	$html .= '</table>';
	return $html;
}

function logSection($divID, $lArr) {
	$html = '';
	foreach($lArr as $type => $eInfo){
		$html .= '<div style="margin:5px;">TYPE: ' . $type . '</div><a class="expandHref" href="Javascript:void(0)" onclick="showHideDivs(\'#' . $divID . '_' . $type . '\');">Click Here To Expand</a><div id="' . $divID . '_' . $type . '" style="margin:10px;display:none;">';
		if ($type == 'product_barcode' || $type == 'product_description'){
			$html .= infoTable(1, $eInfo);
		}
		else {
			$html .= infoTable(2, $eInfo);
		}
		$html .= '</div>';
	}
	return $html;
}

/* New Tabbed Logging - END*/

function generateBarcodes(&$Product, $type, $numOfBarcodes){
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

class dataImportHeaderIterator extends ArrayIterator
{

	public function current() {
		return str_replace('"', '', parent::current());
	}
}

class dataImportLineIterator extends ArrayIterator
{

	public function current() {
		return ltrim(rtrim(parent::current()));
	}
}

class dataImportColumnIterator extends ArrayIterator
{

	public function current() {
	}

	public function offsetClean($offset, $replace_quotes) {
		if (parent::offsetExists($offset) === false) {
			return;
		}

		if (function_exists('ini_get')){
			$currentVal = parent::offsetGet($offset);
			if (ini_get('magic_quotes_runtime') == 1){
				if (substr($currentVal, -1) == '"'){
					$currentVal = substr($currentVal, 2, strlen($currentVal) - 4);
				}
				$currentVal = str_replace('\"\"', "&#34", $currentVal);
				if ($replace_quotes){
					$currentVal = str_replace('\"', "&#34", $currentVal);
					$currentVal = str_replace("\'", "&#39", $currentVal);
				}
			}
			else {
				if (substr($currentVal, -1) == '"'){
					$currentVal = substr($currentVal, 1, strlen($currentVal) - 2);
				}
				$currentVal = str_replace('""', "&#34", $currentVal);
				if ($replace_quotes){
					$currentVal = str_replace('"', "&#34", $currentVal);
					$currentVal = str_replace("'", "&#39", $currentVal);
				}
			}
			parent::offsetSet($offset, $currentVal);
		}
	}
}

if ($uploaded === true){
	$fileName = $upload->filename;

	$messageStack->addSession('pageStack', '<p>File uploaded.<br />Temporary filename: ' . $upload->tmp_filename . '<br />User filename: ' . $fileName . '<br />Size: ' . $upload->file_size . '<br /></p>', 'success');

	require(sysConfig::getDirFsCatalog() . 'includes/classes/FileParser/csv.php');
	$ImportFile = new FileParserCsv($dataExport->tempDir . $fileName);
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
					generateBarcodes($Product, $code, $item['v_autogenerate_barcodes_' . $code]);
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
			// end of row insertion code
			$Product->free();
		}
		$ImportFile->next();
	}
}
/*
		$originalContents = file($dataExport->tempDir . $fileName);

		// now we string the entire thing together in case there were carriage returns in the data
		$fileString = '';
		foreach($originalContents as $fileLine){
			$fileString .= $fileLine;
		}
		
		// now newreaded has the entire file together without the carriage returns.
		// if for some reason excel put qoutes around our EOREOR, remove them then split into rows
		$fileString = str_replace('"EOREOR"', 'EOREOR', $fileString);
		$fileContent = explode($separator . 'EOREOR', $fileString);
		
		// Now we'll populate the filelayout based on the header row.
		$fileHeaders = explode($separator, $fileContent[0]); // explode the first row, it will be our filelayout
		unset($fileContent[0]); //  we don't want to process the headers with the data
		
		$fileHeaderObj = new ArrayObject($fileHeaders);
		$fileHeaderObj->setIteratorClass('dataImportHeaderIterator');
		$fileHeaderIterator = $fileHeaderObj->getIterator();
		
		$fileArrObj = new ArrayObject($fileContent);
		$fileArrObj->setIteratorClass('dataImportLineIterator');
		$lineIterator = $fileArrObj->getIterator();
		
		while($lineIterator->valid()){
			$currentLine = $lineIterator->current();

			// blow it into an array, splitting on the tabs
			$columns = explode($separator, $currentLine);
			$columnsObj = new ArrayObject($columns);
			$columnsObj->setIteratorClass('dataImportColumnIterator');
			$columnIterator = $columnsObj->getIterator();
			
			$items = array();
			while($fileHeaderIterator->valid()){
				$i = $fileHeaderIterator->key();
				if ($columnIterator->offsetExists($i) === false){
					$columnIterator->offsetSet($i, '');
				}else{
					$columnIterator->offsetClean($i, $replace_quotes);
				}
				$items[$fileHeaderIterator->current()] = trim($columnIterator->offsetGet($i));
				$fileHeaderIterator->next();
			}
			$fileHeaderIterator->rewind();

			if (!isset($items['v_products_model']) || strlen($items['v_products_model']) <= 0 || $items['v_products_model'] == ''){
				$lineIterator->next();
				continue;
			}
			
			$Qproduct = Doctrine_Query::create()
			->from('Products p')
			->where('p.products_model = ?', $items['v_products_model'])
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$isNewProduct = false;
			if ($Qproduct){
				$Product = Doctrine_Core::getTable('Products')->find($Qproduct[0]['products_id']);
			}else{
				$Product = new Products();
				$Product->products_model = $items['v_products_model'];
				$Product->save();
				$isNewProduct = true;
			}
			
			$Product->products_tax_class_id = (isset($items['v_tax_class_title']) ? tep_get_tax_title_class_id($items['v_tax_class_title']) : '0');
			$Product->products_weight = (isset($items['v_products_weight']) ? $items['v_products_weight'] : '0');
			$Product->products_type = (isset($items['v_products_type']) ? $items['v_products_type'] : '');
			$Product->products_in_box = (isset($items['v_products_in_box']) ? $items['v_products_in_box'] : '0');
			$Product->products_featured = (isset($items['v_products_featured']) ? $items['v_products_featured'] : '0');
			$Product->products_date_available = (isset($items['v_date_avail']) ? $items['v_date_avail'] : null);
			$Product->products_status = (!isset($items['v_status']) || $items['v_status'] == $inactive ? '0' : '1');
			$Product->products_image = (!isset($items['v_products_image']) || $items['v_products_image'] == '' ? $default_image_product : $items['v_products_image']);
			$Product->products_price = '0.0000';
			$Product->products_price_used = '0.0000';
			$Product->products_price_stream = '0.0000';
			$Product->products_price_download = '0.0000';
	
			if (isset($items['v_products_price']) && !empty($items['v_products_price'])){
				$Product->products_price = (float)$items['v_products_price'];
			}
	
			if (isset($items['v_products_price_used']) && !empty($items['v_products_price_used'])){
				$Product->products_price_used = (float)$items['v_products_price_used'];
			}
	
			if (isset($items['v_products_price_stream']) && !empty($items['v_products_price_stream'])){
				$Product->products_price_stream = (float)$items['v_products_price_stream'];
			}
	
			if (isset($items['v_products_price_download']) && !empty($items['v_products_price_download'])){
				$Product->products_price_download = (float)$items['v_products_price_download'];
			}

			if(isset($items['v_memberships_not_enabled']) && !empty($items['v_memberships_not_enabled'])){
				$Qmembership = Doctrine_Query::create()
				->from('Membership m')
				->leftJoin('m.MembershipPlanDescription md')
				->where('md.language_id = ?', Session::get('languages_id'))
				->orderBy('sort_order')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				$notEnableMembershipsNames = explode(';', $items['v_memberships_not_enabled']);
				$notenabledArr = array();
				foreach($Qmembership as $mInfo){
					if(in_array($mInfo['MembershipPlanDescription'][0]['name'], $notEnableMembershipsNames)){
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
				if (isset($items['v_products_url_' . $lID])){
					$CurrentDesc->products_url = $items['v_products_url_' . $lID];
				}
				
				if (isset($items['v_products_name_' . $lID])){
					$CurrentDesc->products_name = $items['v_products_name_' . $lID];
				}
				
				if (isset($items['v_products_description_' . $lID])){
					$CurrentDesc->products_description = $items['v_products_description_' . $lID];
				}
				
				if (isset($items['v_products_head_desc_tag_' . $lID])){
					$CurrentDesc->products_head_desc_tag = $items['v_products_head_desc_tag_' . $lID];
				}
				
				if (isset($items['v_products_head_title_tag_' . $lID])){
					$CurrentDesc->products_head_title_tag = $items['v_products_head_title_tag_' . $lID];
				}
				
				if (isset($items['v_products_head_keywords_tag_' . $lID])){
					$CurrentDesc->products_head_keywords_tag = $items['v_products_head_keywords_tag_' . $lID];
				}
			}
			
			if (!empty($items['v_products_categories'])){
				$Product->ProductsToCategories->delete();
				$ProductsToCategories = $Product->ProductsToCategories;
				
				$productsCategories = explode(';', $items['v_products_categories']);
				$productsCategories = array_unique($productsCategories);
				$productsCategories = array_values($productsCategories);
				foreach($productsCategories as $i => $catString){
					if (stristr($catString, '>')){
						$catPath = explode('>', $catString);
					}else{
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
						}else{
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
			
			EventManager::notify('DataImportBeforeSave', &$items, &$Product);
			
			//echo '<pre>';print_r($Product->toArray(true));echo '</pre>';
			$Product->save();
			$QPPR = Doctrine_Core::getTable('ProductsPayPerRental')->findByProductsId($Product->products_id);
			EventManager::notify('DataImportAfterSave', &$items, &$QPPR[0]);

			if (isset($items['v_status']) && $items['v_status'] == $deleteStatus){
				$Product->delete();
				$status = 'Deleted';
			}else{
				$status = $Product->products_status;
			}
			
			$productLogArr = array(
				'ID:'              => $Product->products_id,
				'Image:'           => $Product->products_image,
				'Model:'           => $Product->products_model,
				'Price:'           => $Product->products_price,
				'Price User:'      => $Product->products_price_used,
				'Price Stream:'    => $Product->products_price_stream,
				'Price Download:'  => $Product->products_price_download,
				'Status:'          => $status,
				'Tax Class ID:'    => $Product->products_tax_class_id,
				'Weight:'          => $Product->products_weight,
				'Type:'            => $Product->products_type,
				'In Box:'          => $Product->products_in_box,
				'Featured'         => $Product->products_featured
			);
	
			EventManager::notify('DataImportProductLogBeforeExecute', &$Product, &$productLogArr);
	
			if ($isNewProduct === true){
				logNew('product', $productLogArr);
			} else {
				logUpdate('product', $productLogArr);
			}

			foreach($Product->ProductsDescription as $Description){
				$productDescLogArr = array(
					'ID:'                 => $Description['products_id'],
					'Language:'           => $Description['language_id'],
					'Name:'               => $Description['products_name'],
					'Description:'        => $Description['products_description'],
					'URL:'                => $Description['products_url'],
					'Header Title:'       => $Description['products_head_title_tag'],
					'Header Description:' => $Description['products_head_desc_tag'],
					'Header Keywords:'    => $Description['products_head_keywords_tag']
				);
	
				EventManager::notify('DataImportProductDescriptionLogBeforeExecute', &$productDescLogArr);
		
				if ($isNewProduct === true){
					logNew('product_description', $productDescLogArr);
				}else{
					logUpdate('product_description', $productDescLogArr);
				}
			}
			// end of row insertion code
			$Product->free();
			
			$lineIterator->next();
		}
	}
*/
?>