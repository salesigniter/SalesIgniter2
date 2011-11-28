<?php
class productInventory
{
	
	private $invData;

	private  $invMethod;
	
	function __construct($pId, $invInfo) {
		$Qcheck = Doctrine_Query::create()
			->from('ProductsInventory')
			->where('products_id = ?', $pId)
			->andWhere('type = ?', $invInfo['type_name'])
			->andWhere('controller = ?', $invInfo['inventory_controller']);

		EventManager::notify('ProductInventoryQueryBeforeExecute', &$Qcheck);

		$Result = $Qcheck->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if (count($Result) > 0){
			$this->invData = array_merge($invInfo, $Result[count($Result) - 1]);
			$invController = $this->invData['inventory_controller'];
			$controllerDir = sysConfig::getDirFsCatalog() . 'includes/classes/product/inventory/controller/';

			if (file_exists($controllerDir . $invController . '.php')){
				$className = 'productInventory_' . $invController;
				if (!class_exists($className)){
					require($controllerDir . $invController . '.php');
				}
				$this->invMethod = new $className($this->invData);

				EventManager::notify(
					'ProductInventorySetMethod',
					&$this->invData,
					&$this->invMethod,
					$invController
				);
			}
		}
	}

	function getTrackMethod() {
		if (is_null($this->invData) === false){
			return $this->invData['track_method'];
		}
		return false;
	}

	function getControllerName() {
		if (is_null($this->invData) === false){
			return $this->invData['controller'];
		}
		return false;
	}

	function getPurchaseType() {
		if (is_null($this->invData) === false){
			return $this->invData['type'];
		}
		return false;
	}

	function getController() {
		if (is_null($this->invMethod) === false){
			return $this->invMethod;
		}
		return false;
	}

	function hasInventory() {
		if (is_null($this->invMethod) === false){
			return ($this->invMethod->hasInventory() === true);
		}
		return false;
	}

	function getCurrentStock() {
		if (is_null($this->invMethod) === false){
			return $this->invMethod->getTotalInventory();
		}
		return false;
	}

	function getInventoryItems() {
		if (is_null($this->invMethod) === false){
			return $this->invMethod->getInventoryItems();
		}
		return false;
	}

	public function getInvUnavailableStatus() {
		return $this->invMethod->getInvUnavailableStatus();
	}

	public function setInvUnavailableStatus($val) {
		return $this->invMethod->setInvUnavailableStatus($val);
	}

	function updateStock($orderId, $orderProductId, &$cartProduct) {
		if (is_null($this->invMethod) === false){
			return $this->invMethod->updateStock($orderId, $orderProductId, &$cartProduct);
		}
		return false;
	}

	public function getNextInventoryItemId() {
		if (is_null($this->invMethod) === false){
			return $this->invMethod->getNextInventoryItemId();
		}
		return false;
	}

	public function addStockToCollection(&$ProductObj, &$CollectionObj) {
		if (is_null($this->invMethod) === false){
			return $this->invMethod->addStockToCollection($ProductObj, $CollectionObj);
		}
		return false;
	}
}

?>