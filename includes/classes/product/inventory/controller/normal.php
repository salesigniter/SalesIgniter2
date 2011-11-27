<?php
	class productInventory_normal {
		
		public function __construct($invData){
			$trackerDir = sysConfig::getDirFsCatalog() . 'includes/classes/product/inventory/track_method/normal/';

			$trackMethod = 'quantity';
			if ($invData['track_method'] == 'barcode'){
				$trackMethod = $invData['track_method'];
			}

			if (file_exists($trackerDir . $trackMethod . '.php')){
				$className = 'productInventoryNormal_' . $trackMethod;
				if (!class_exists($className)){
					require($trackerDir . $trackMethod . '.php');
				}
				$this->trackMethod = new $className($invData);
			}
		}

		public function hasInventory(){
			return $this->trackMethod->hasInventory();
		}

		public function getTotalInventory(){
			return $this->trackMethod->getInventoryItemCount();
		}

		public function getNextInventoryItemId(){
			return $this->trackMethod->getNextInventoryItemId();
		}

		public function updateStock($orderId, $orderProductId, $cartProduct){
			return $this->trackMethod->updateStock($orderId, $orderProductId, $cartProduct);
		}
		
		public function addStockToCollection(&$ProductObj, &$CollectionObj){
			return $this->trackMethod->addStockToCollection($ProductObj, $CollectionObj);
		}

		public function getInventoryItems(){
			return $this->trackMethod->getInventoryItems();
		}
		public function getInvUnavailableStatus(){
			return $this->trackMethod->getInvUnavailableStatus();
		}

		public function setInvUnavailableStatus($val){
			return $this->trackMethod->setInvUnavailableStatus($val);
		}
	}
?>