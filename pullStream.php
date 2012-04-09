<?php
include('includes/application_top.php');
$canView = false;
$name = substr($_GET['file'], 0, strpos($_GET['file'], '.'));
if($name == 'preview' && !isset($_GET['opID'])){
	$canView = true;
}
if($name != 'preview' && !stristr($name, 'loaded_stream_')){
	$canView = false;
}
$pID = (isset($_GET['pID']) ? $_GET['pID'] : (isset($_GET['pid']) ? $_GET['pid'] : false));
if($pID === false || !is_numeric($pID)){
	$canView = false;
}
if($userAccount->isLoggedIn() === true && isset($_GET['opID']) && isset($_GET['oID'])){
	$canView = true;
}
if($canView === true){
	if(isset($_GET['oID']) && $userAccount->isLoggedIn() === true){
		$PurchaseType = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select purchase_type from ' . TABLE_ORDERS_PRODUCTS . ' where orders_products_id = "' . (int) $_GET['opID'] . '"');

		if(sizeof($PurchaseType) > 0){
			switch($PurchaseType[0]['purchase_type']){
				case 'stream':
					$Stream = false;
					EventManager::notify('PullStreamAfterUpdate', &$Stream, (int) $_GET['oID'], (int) $_GET['opID']);
					$file = array(
						array(
							'file_name' => $Stream['file_name'],
							'type' => 'stream'
						)
					);
					break;
				case 'download':
					$Download = false;
					$fileName = $Stream['file_name'];
					EventManager::notify('PullDownloadAfterUpdate', &$Download, (int) $_GET['oID'], (int) $_GET['opID']);
					$file = array(
						array(
							'file_name' => $Stream['file_name'],
							'type' => 'download'
						)
					);
					break;
			}
		}
	} elseif($name == 'preview'){
		$file = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select movie_preview as file_name, "stream" as type from ' . TABLE_PRODUCTS . ' where products_id = "' . (int) $pID . '"');
	}
	if($file[0]['type'] == 'download'){
		if(stristr($file[0]['file_name'], '.gif')){
			header('Content-type: image/gif');
		} elseif(stristr($file[0]['file_name'], '.png')){
			header('Content-type: image/png');
		} elseif(stristr($file[0]['file_name'], '.jpg')){
			header('Content-type: image/jpg');
		} elseif(stristr($file[0]['file_name'], '.mpg')){
			header('Content-type: video/mpg');
		} elseif(stristr($file[0]['file_name'], '.flv')){
			header("Content-Type: video/flv");
		}
		header('Content-Disposition: attachment; filename="' . $file[0]['file_name'] . '"');
	}
	readfile('streamer/movies/' . $file[0]['file_name']);
	exit;
} else{
	echo 'File Not Found';
}
include('includes/application_bottom.php');
?>