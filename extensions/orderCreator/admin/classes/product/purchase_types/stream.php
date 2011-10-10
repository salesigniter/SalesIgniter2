<?php
/*
	Product Purchase Type: Stream

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
if (!class_exists('PurchaseType_stream')){
	require(sysConfig::getDirFsCatalog() . 'extensions/streamProducts/purchaseTypeModules/stream/module.php');
}

class OrderCreatorProductPurchaseTypeStream extends PurchaseType_stream {

	public function addToOrdersProductCollection(&$ProductObj, &$CollectionObj){
		$Qstreams = Doctrine_Query::create()
		->from('ProductsUploads')
		->where('products_id = ?', (int)$ProductObj->getProductsId())
		->andWhere('type = ?', 'stream')
		->execute();
			
		if ($Qstreams->count() > 0){
			foreach($Qstreams->toArray() as $sInfo){
				$Stream = new OrdersProductsStream();
				$Stream->orders_products_filename = $sInfo['file_name'];
				$Stream->stream_maxdays = sysConfig::get('STREAMING_MAX_DAYS');
				$Stream->stream_count = '0';
				if(isset($_POST['estimateOrder'])){
					$Stream->is_estimate = 1;
				}else{
					$Stream->is_estimate = 0;
				}
				$CollectionObj->OrdersProductsStream->add($Stream);
			}
		}
	}
}
?>