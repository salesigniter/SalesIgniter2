<?php
if (!class_exists('PurchaseTypeAbstract')){
	require(dirname(__FILE__) . '/product/PurchaseTypeAbstract.php');
}

if (!class_exists('productInventory')){
	require(dirname(__FILE__) . '/product/Inventory.php');
}

class ProductInfo {
	protected $boundMethods = array();

	public function __construct($pID){
		$Qproduct = mysql_query('SELECT ' . 
			'p.products_id, ' . 
			'p.products_image, ' . 
			'p.products_model, ' . 
			'p.products_date_added, ' . 
			'p.products_last_modified, ' . 
			'p.products_date_available, ' . 
			'p.products_weight, ' . 
			'p.products_status, ' . 
			'p.products_ordered, ' .
			'p.products_date_ordered, ' . 
			'p.products_last_sold, ' . 
			'pd.products_name, ' . 
			'pd.products_description' . 
			' FROM ' . 
			'products p LEFT JOIN products_description pd USING(products_id)' . 
			' WHERE ' . 
			'p.products_id = "' . (int) $pID . '" AND ' . 
			'pd.language_id = "' . (int) Session::get('languages_id') . '"'
		);
		$Product = mysql_fetch_assoc($Qproduct);
		
		$this->setId($Product['products_id']);
		$this->setName($Product['products_name']);
		$this->setDescription($Product['products_description']);
		$this->setImage($Product['products_image']);
		$this->setModel($Product['products_model']);
		$this->setDateAdded($Product['products_date_added']);
		$this->setLastModified($Product['products_last_modified']);
		$this->setDateAvailable($Product['products_date_available']);
		$this->setWeight($Product['products_weight']);
		$this->setStatus($Product['products_status']);
		$this->setTotalOrdered($Product['products_ordered']);
		$this->setDateOrdered($Product['products_date_ordered']);
		$this->setLastSold($Product['products_last_sold']);
		
		$purchaseTypes = array();
		$QproductTypes = mysql_query(
			'SELECT ' . 
			'type_name' . 
			' FROM ' . 
			'products_purchase_types' . 
			' WHERE ' . 
			'products_id = "' . (int) $Product['products_id'] . '"'
		);
		while($ptInfo = mysql_fetch_assoc($QproductTypes)){
			$purchaseTypes[] = $ptInfo['type_name'];
		}
		$this->setPurchaseTypes($purchaseTypes);
		
		EventManager::notify('ProductInfoClassBindMethods', $this);
	}
	
	public function __call($method, $args){
		if (isset($this->boundMethods[$method])){
			return call_user_func_array($this->boundMethods[$method], array_merge(array(&$this), $args));
		}
	}
	
	public function bindMethod($methodName, Closure $func){
		$this->boundMethods[$methodName] = $func;
	}

	public function unbindMethod($methodName){
		if (array_key_exists($methodName, $this->boundMethods)){
			unset($this->boundMethods[$methodName]);
		}
	}

	public function serialize(){
		$serialize = array();
		foreach(get_object_vars($this) as $varName => $varVal){
			if ($varName == 'boundMethods'){
				continue;
			}else{
				$serialize[$varName] = $varVal;
			}
		}
		return serialize($serialize);
	}
	
	public function unserialize($data){
		$data = unserialize($data);
		foreach($data as $varName => $varVal){
			$this->$varName = $varVal;
		}
	}

	public function getId(){ return (int) $this->info['products_id']; }
	public function getName(){ return stripslashes($this->info['products_name']); }
	public function getDescription(){ return stripslashes($this->info['products_description']); }
	public function getImage(){ return $this->info['products_image']; }
	public function getModel(){ return $this->info['products_model']; }
	public function getDateAdded(){ return $this->info['products_date_added']; }
	public function getLastModified(){ return $this->info['products_last_modified']; }
	public function getDateAvailable(){ return $this->info['products_date_available']; }
	public function getWeight(){ return $this->info['products_weight']; }
	public function getStatus(){ return $this->info['products_status']; }
	public function getTotalOrdered(){ return $this->info['products_ordered']; }
	public function getDateOrdered(){ return $this->info['products_date_ordered']; } //??????
	public function getLastSold(){ return $this->info['products_last_sold']; } //??????
	public function getPurchaseTypes(){ return $this->info['purchase_types']; }
	
	public function setId($val){ $this->info['products_id'] = $val; }
	public function setName($val){ $this->info['products_name'] = $val; }
	public function setDescription($val){ $this->info['products_description'] = $val; }
	public function setImage($val){ $this->info['products_image'] = $val; }
	public function setModel($val){ $this->info['products_model'] = $val; }
	public function setDateAdded($val){ $this->info['products_date_added'] = new DateTime($val); }
	public function setLastModified($val){ $this->info['products_last_modified'] = new DateTime($val); }
	public function setDateAvailable($val){ $this->info['products_date_available'] = new DateTime($val); }
	public function setWeight($val){ $this->info['products_weight'] = $val; }
	public function setStatus($val){ $this->info['products_status'] = $val; }
	public function setTotalOrdered($val){ $this->info['products_ordered'] = $val; }
	public function setDateOrdered($val){ $this->info['products_date_ordered'] = new DateTime($val); } //??????
	public function setLastSold($val){ $this->info['products_last_sold'] = new DateTime($val); } //??????
	public function setPurchaseTypes($val){ $this->info['purchase_types'] = $val; }
	
	public function isActive(){
		return ($this->getStatus() == 1);
	}
	
	public function isFeatured(){
		return false;
	}
	
	public function isBox(){
		return false;
	}
	
	public function isInBox(){
		return false;
	}
}

class Product {
	public function __construct($pID){
		global $appExtension;
		$this->pluginDir = sysConfig::getDirFsCatalog() . 'includes/classes/product/plugins/';
		$this->valid = false;
		
		$productQuery = Doctrine_Query::create()
		->select('p.*, pd.*, m.*')
		->from('Products p')
		->leftJoin('p.ProductsDescription pd')
		->where('p.products_id = ?', (int)$pID)
		->andWhere('pd.language_id = ?', Session::get('languages_id'));
		
		EventManager::notify('ProductQueryBeforeExecute', &$productQuery);
		//echo $productQuery->getSqlQuery();
		
		$this->initPlugins((int)$pID, $productQuery);
		
		$productInfo = $productQuery->fetchOne();

		if ($productInfo){
			$this->valid = true;
			$this->productInfo = $productInfo->toArray(true);
			$this->productInfo['taxRate'] = tep_get_tax_rate($this->productInfo['products_tax_class_id']);
			$this->productInfo['typeArr'] = explode(',', $this->productInfo['products_type']);
			
			foreach($this->plugins as $pluginName => $pluginClass){
				$this->plugins[$pluginName]->loadProductInfo($this->productInfo);
			}
			
			EventManager::notify('ProductQueryAfterExecute', &$this->productInfo);
		}
		$productQuery->free();
		$productQuery = null;
		unset($productQuery);
	}
	
	public function getPurchaseType($typeName, $forceEnable = false){
		global $appExtension;
		$PurchaseType = PurchaseTypeModules::getModule($typeName);
		if (is_object($PurchaseType)){
			$PurchaseType->loadProduct($this->getID());
		}else{
		
		$className = 'PurchaseType_' . $typeName;

		if (!class_exists($className, false)){
			$purchaseTypesPath = 'classes/product/purchase_types/';
			$baseFilePath = sysConfig::getDirFsCatalog() . 'includes/' . $purchaseTypesPath;
			if (file_exists($baseFilePath . $typeName . '.php')){
				require($baseFilePath . $typeName . '.php');
			}else{
				$extFilePath = sysConfig::getDirFsCatalog() . 'extensions/';
				$Exts = $appExtension->getExtensions();
				foreach($Exts as $extName => $extCls){
					if (file_exists($extFilePath . $extName . '/catalog/' . $purchaseTypesPath . $typeName . '.php')){
						require($extFilePath . $extName . '/catalog/' . $purchaseTypesPath . $typeName . '.php');
						break;
					}
				}
			}
		}
				
		$PurchaseType = null;
		if (class_exists($className, false)){
			$PurchaseType = new $className($this, $forceEnable);
		}
		}
		return $PurchaseType;
	}
	
	public function initPlugins($pID = null, &$productQuery){
		$fileObj = new DirectoryIterator($this->pluginDir);
		while($fileObj->valid()){
			if ($fileObj->isDot() || !$fileObj->isDir()){
				$fileObj->next();
				continue;
			}
			$pluginName = $fileObj->getBasename();
			$className = 'productPlugin_' . $pluginName;
			if (!class_exists($className, false)){
				require($fileObj->getPathname() . '/base.php');
			}
			
			if (!isset($this->plugins[$pluginName])){
				$this->plugins[$pluginName] = new $className($pID, $productQuery);
			}
			$fileObj->next();
		}
		
		EventManager::notify('ProductClassInitPlugins', &$pID, &$this);
		return $this;
	}
	
	function pluginIsLoaded($pluginName){
		if (isset($this->classes['plugin'][$pluginName])){
			return true;
		}
		return false;
	}

	function updateViews(){
		$Qupdate = dataAccess::setQuery('update {products_description} set products_viewed = products_viewed+1 where products_id = {product_id} and language_id = {language_id}');
		$Qupdate->setTable('{products_description}', TABLE_PRODUCTS_DESCRIPTION);
		$Qupdate->setValue('{product_id}', $this->productInfo['products_id']);
		$Qupdate->setValue('{language_id}', Session::get('languages_id'));
		$Qupdate->runQuery();
	}

	function isValid(){
		return isset($this->productInfo['products_id']) && $this->productInfo['products_id'] > 0;
	}

	function isActive(){
		return ($this->productInfo['products_status'] == '1');
	}

	function isFeatured(){
		return ($this->productInfo['products_featured'] == '1');
	}

	function isNotAvailable(){
		return ($this->getAvailableDate() > date('Y-m-d H:i:s'));
	}

	/* HAS Methods -- Begin -- */
	function hasModel(){ return (tep_not_null($this->productInfo['products_model'])); }
	function hasURL(){ return tep_not_null($this->productInfo['ProductsDescription'][Session::get('languages_id')]['products_url']); }
	function hasImage(){ return tep_not_null($this->productInfo['products_image']); }
	/* HAS Methods -- End -- */

	/* GET Methods -- Begin --*/
	function getID(){ return (int)$this->productInfo['products_id']; }
	function getStock(){ return (int)$this->productInfo['products_quantity']; }
	function getTaxRate(){ return $this->productInfo['taxRate']; }
	function getModel(){ return $this->productInfo['products_model']; }
	function getName(){ return stripslashes($this->productInfo['ProductsDescription'][Session::get('languages_id')]['products_name']); }
	function getImage(){ return $this->productInfo['products_image']; }
	function getDescription(){ return stripslashes($this->productInfo['ProductsDescription'][Session::get('languages_id')]['products_description']); }
	function getURL(){ return $this->productInfo['ProductsDescription'][Session::get('languages_id')]['products_url']; }
	function getPreview(){ return $this->productInfo['movie_preview']; }
	function getAvailableDate(){ return $this->productInfo['products_date_available']; }
	function getLastModified(){ return $this->productInfo['products_last_modified']; }
	function getDateAdded(){ return $this->productInfo['products_date_added']; }
	function getWeight(){ return $this->productInfo['products_weight']; }
	function getTaxClassID(){ return $this->productInfo['products_tax_class_id']; }
	function getPType(){ return $this->productInfo['products_ptype']; }
	//function getAuthMethod(){ return $this->productInfo['products_auth_method']; }
	//function getAuthCharge(){ return $this->productInfo['products_auth_charge']; }
	/* GET Methods -- End --*/

	/* Box Set Methods -- Begin -- */
	function isBox(){
		if (isset($this->plugins['box_sets'])){
			return $this->plugins['box_sets']->isBox();
		}
		return false;
	}

	function isInBox(){
		if (isset($this->plugins['box_sets'])){
			return $this->plugins['box_sets']->isInBox();
		}
		return false;
	}

	function getBoxID(){
		if (isset($this->plugins['box_sets'])){
			return $this->plugins['box_sets']->getBoxID();
		}
		return false;
	}

	function getTotalDiscs(){
		if (isset($this->plugins['box_sets'])){
			return $this->plugins['box_sets']->getTotalDiscs();
		}
		return 0;
	}

	function getDiscs($exclude = false, $onlyIds = false){
		if (isset($this->plugins['box_sets'])){
			return $this->plugins['box_sets']->getDiscs($exclude, $onlyIds);
		}
		return false;
	}

	function getBoxName(){
		if (isset($this->plugins['box_sets'])){
			return $this->plugins['box_sets']->getName();
		}
		return false;
	}

	function getDiscNumber($pID = false){
		if (isset($this->plugins['box_sets'])){
			return $this->plugins['box_sets']->getDiscNumber($pID);
		}
		return false;
	}
	/* Box Set Methods -- End -- */

}
?>