<?php
/*
	SalesIgniter E-Commerce System v1

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2010 I.T. Web Experts

	This script and it's source is not redistributable
*/
$uploadDir = sysConfig::getDirFsCatalog() . 'extensions/multiStore/admin/base_app/inventory/csvUploads/';

require(SysConfig::getDirFsCatalog() . 'includes/classes/FileParser/csv.php');
require(SysConfig::getDirFsCatalog() . 'includes/classes/uploadManager.php');
$mgr = new UploadManager($uploadDir, '777');
$mgr->setExtensions(array('csv', 'xls'));

$file = new UploadFile('fileToUpload');
if ($mgr->processFile($file)){
	$FileObj = new FileParserCsv($uploadDir . $file->getName());
	$FileObj->parseHeaderLine();
	$ProcessInfo = '';
	while($FileObj->valid()){
		$Row = $FileObj->currentRow();

		$TrackingNum = '';
		$Store = '';
		$Model = '';
		$Barcode = '';
		$Status = '';
		while($Row->valid()){
			$Col = $Row->current();
			switch($Col->key()){
				case 'tracking':
					$TrackingNum = $Col->getText();
					break;
				case 'store':
					$Store = $Col->getText();
					break;
				case 'model':
					$Model = $Col->getText();
					break;
				case 'barcode':
					$Barcode = $Col->getText();
					break;
				case 'status':
					$Status = $Col->getText();
					break;
			}
			$Row->next();
		}

		if (!empty($Model) && !empty($Store) && !empty($Barcode)){
			$Qcheck = mysql_query('select barcode products_inventory_barcodes where barcode = "' . $Barcode . '"');
			if (mysql_num_rows($Qcheck) <= 0){
				$Qproduct = mysql_query('select products_id from products where products_model = "' . $Model . '"');
				if (mysql_num_rows($Qproduct) > 0){
					$Product = mysql_fetch_assoc($Qproduct);

					$Qinventory = mysql_query('select inventory_id from products_inventory where products_id = "' . $Product['products_id'] . '" and track_method = "barcode" and type = "rental"');
					if (mysql_num_rows($Qinventory) > 0){
						$Inventory = mysql_fetch_assoc($Qinventory);
						mysql_query('insert into products_inventory_barcodes (inventory_id, barcode, status) values ("' . $Inventory['inventory_id'] . '", "' . $Barcode . '", "A")');
						mysql_query('insert into products_inventory_barcodes_to_stores (inventory_store_id, barcode_id) values (1, "' . mysql_insert_id() . '")');
						$ProcessInfo .= '<br>Barcode ' . $Barcode . ' Added To Product #' . $Product['products_id'] . ' In Store #1';
					}
				}
			}
		}

		if (!empty($Status)){
			$Qcheck = mysql_query('select * from products_inventory_barcodes_transfers where barcode = "' . $Barcode . '" and is_history = "0"');
			if (mysql_num_rows($Qcheck) > 0){
				$Check = mysql_fetch_assoc($Qcheck);
				$OriginId = $Check['origin_id'];
				$DestId = $Check['destination_id'];
				mysql_query('update products_inventory_barcodes_transfers set is_history = 1 where barcode = "' . $Barcode . '" and is_history = 0');
				$ProcessInfo .= '<br>Made All Transfers History For Barcode ' . $Barcode;
			}else{
				$Qorigin = mysql_query('select b2s.inventory_store_id from products_inventory_barcodes b left join products_inventory_barcodes_to_stores b2s ON b.barcode_id = b2s.barcode_id where b.barcode = "' . $Barcode . '"');
				if (mysql_num_rows($Qorigin) > 0){
					$Origin = mysql_fetch_assoc($Qorigin);
					if ($Status == 'E'){
						$OriginId = $Origin['inventory_store_id'];
						$DestId = '1';
					}elseif ($Status == 'P' || $Status == 'S'){
						$OriginId = $Origin['inventory_store_id'];
						$DestId = $Store;
					}
				}
			}

			mysql_query('insert into products_inventory_barcodes_transfers (barcode, status, origin_id, destination_id, date_added, tracking_number) values ("' . $Barcode . '", "' . $Status . '", "' . $OriginId . '", "' . $DestId . '", now(), "' . $TrackingNum . '")');
			mysql_query('update products_inventory_barcodes set status = "T" where barcode = "' . $Barcode . '"');
			$ProcessInfo .= '<br>New Transfer Info Added For Barcode ' . $Barcode . ' From Store #' . $OriginId . ' To Store #' . $DestId . ' Using Status ' . $Status . ' And Tracking Number ' . $TrackingNum;
		}
		$FileObj->next();
	}

	$exception = $mgr->getException();
	if (isset($_GET['rType']) && $_GET['rType'] == 'ajax'){
		$json = array(
			'success' => true,
			'message' => $exception->getMessage() . $ProcessInfo
		);
	}else{
		$messageStack->addSession('pageStack', $exception->getMessage() . $ProcessInfo, 'success');
	}
}else{
	$exception = $mgr->getException();
	if (isset($_GET['rType']) && $_GET['rType'] == 'ajax'){
		$json = array(
			'success' => false,
			'message' => $exception->getMessage()
		);
	}else{
		$messageStack->addSession('pageStack', $exception->getMessage(), 'error');
	}
}

if (isset($json)){
	EventManager::attachActionResponse($json, 'json');
}else{
	EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action'))), 'redirect');
}
?>