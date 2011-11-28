<?php
if (!class_exists('PurchaseTypeModules')){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/purchaseTypeModules/modules.php');
}

if (!class_exists('ProductTypeModules')){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/productTypeModules/modules.php');
}

class Product extends MI_Base
{

	public $info = array(
		'AdditionalImages' => array()
	);

	private $productType = false;

	private $_isValid = false;

	public function __construct($pID = '') {
		$Products = Doctrine_Core::getTable('Products');
		if (!empty($pID)){
			$Product = $Products->find((int)$pID);
			$this->_isValid = true;
		}
		else {
			$Product = $Products->getRecord();
			$this->_isValid = false;
		}

		$this->setProductType($Product->products_type);

		$this->setId($Product->products_id);
		$this->setImage($Product->products_image);
		$this->setModel($Product->products_model);
		$this->setDateAdded($Product->products_date_added);
		if ($Product->products_last_modified > 0){
			$this->setLastModified($Product->products_last_modified);
		}else{
			$this->setLastModified('0000-00-00');
		}

		if ($Product->products_date_available != '0000-00-00 00:00:00' && ($Product->products_date_available) != 'null'){
			$this->setDateAvailable($Product->products_date_available);
		}else{
			$this->setDateAvailable(date('Y-m-d'));
		}
		$this->setWeight($Product->products_weight);
		//$this->setPrice($Product->products_price);
		$this->setKeepPrice($Product->products_keepit_price);
		$this->setStatus($Product->products_status);
		$this->setFeatured($Product->products_featured);
		$this->setManufacturer($Product->manufacturers_id);
		$this->setMembershipEnabled($Product->membership_enabled);
		$this->setTotalOrdered($Product->products_ordered);
		$this->setOnOrder($Product->products_on_order);
		$this->setDateOrdered($Product->products_date_ordered);
		$this->setLastSold($Product->products_last_sold);
		//$this->setInventoryController($Product->products_inventory_controller);
		if(is_object($Product->ProductsAdditionalImages)){
			foreach($Product->ProductsAdditionalImages->toArray() as $iInfo){
				$this->addAdditionalImage($iInfo);
			}
		}
		if(is_object($Product->ProductsDescription)){
			foreach($Product->ProductsDescription->toArray() as $dInfo){
				$this->setName($dInfo['products_name'], $dInfo['language_id']);
				$this->setDescription($dInfo['products_description'], $dInfo['language_id']);
				$this->setUrl($dInfo['products_url'], $dInfo['language_id']);
				$this->setSeoUrl($dInfo['products_seo_url'], $dInfo['language_id']);
			}
		}

		EventManager::notify('ProductInfoClassConstruct', &$this, $Product);
	}

	/**
	 * @return void
	 */
	function updateViews() {
		mysql_query('update ' .
			'products_description' .
			' set ' .
			'products_viewed = products_viewed + 1' .
			' where ' .
			'products_id = "' . $this->getId() . '" and ' .
			'language_id = "' . Session::get('languages_id') . '"'
		);
	}

	/**
	 * @param string $ProductsType
	 * @return void
	 */
	public function loadProductType($ProductsType) {
		if (ProductTypeModules::isEnabled($ProductsType, true)){
			$this->productType = ProductTypeModules::getModule($ProductsType);
		}else{
			$this->productType = new stdClass();
		}
	}

	/**
	 * @return mixed
	 */
	public function &getProductTypeClass() {
		if ($this->productType === false){
			$this->loadProductType($this->getProductType());
		}
		return $this->productType;
	}

	/**
	 * @param int $id
	 * @return int
	 */
	public function getLanguageId($id) {
		return (int)($id <= 0 || $id == '' || $id === false ? Session::get('languages_id') : $id);
	}

	/**
	 * @return int
	 */
	public function getId() { return (int)$this->info['products_id']; }

	/**
	 * @param int $langId
	 * @return string
	 */
	public function getName($langId = 0) {
		$ProductType = $this->getProductTypeClass();
		$langId = $this->getLanguageId($langId);
		if (method_exists($ProductType, 'getProductName')){
			$return = $ProductType->getProductName($langId);
		}else{
			$return = $this->info['products_name'][$langId];
		}
		return $return;
	}

	/**
	 * @param int $langId
	 * @return string
	 */
	public function getDescription($langId = 0) {
		$ProductType = $this->getProductTypeClass();
		$langId = $this->getLanguageId($langId);
		if (method_exists($ProductType, 'getProductDescription')){
			$return = $ProductType->getProductDescription($langId);
		}else{
			$return = $this->info['products_description'][$langId];
		}
		return $return;
	}

	/**
	 * @return float
	 */
	public function getPrice() {
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'getProductPrice')){
			$return = call_user_method_array('getProductPrice', $ProductType, func_get_args());
		}else{
			$return = $this->info['products_price'];
		}
		return $return;
	}

	/**
	 * @return float
	 */
	public function getKeepPrice() {
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'getProductKeepPrice')){
			$return = call_user_method_array('getProductKeepPrice', $ProductType, func_get_args());
		}else{
			$return = $this->info['products_keepit_price'];
		}
		return $return;
	}

	/**
	 * @param int $langId
	 * @return string
	 */
	public function getUrl($langId = 0) {
		return $this->info['products_url'][$this->getLanguageId($langId)];
	}

	/**
	 * @param int $langId
	 * @return string
	 */
	public function getSeoUrl($langId = 0) {
		return $this->info['products_seo_url'][$this->getLanguageId($langId)];
	}

	/**
	 * @return string
	 */
	public function getImage() { return $this->info['products_image']; }

	/**
	 * @return string
	 */
	public function getMembershipEnabled() { return $this->info['membership_enabled']; }

	/**
	 * @return string
	 */
	public function getModel() { return $this->info['products_model']; }

	/**
	 * @return DateTime
	 */
	public function getDateAdded() { return $this->info['products_date_added']; }

	/**
	 * @return DateTime
	 */
	public function getLastModified() { return $this->info['products_last_modified']; }

	/**
	 * @return DateTime
	 */
	public function getDateAvailable() { return $this->info['products_date_available']; }

	/**
	 * @return float
	 */
	public function getWeight() { return (float)$this->info['products_weight']; }

	/**
	 * @return int
	 */
	public function getStatus() { return (int)$this->info['products_status']; }

	/**
	 * @return int
	 */
	public function getFeatured() { return (int)$this->info['products_featured']; }

	/**
	 * @return int
	 */
	public function getManufacturer() { return (int)$this->info['manufacturers_id']; }

	/**
	 * @return int
	 */
	public function getTotalOrdered() { return (int)$this->info['products_ordered']; }

	/**
	 * @return DateTime
	 */
	public function getDateOrdered() { return $this->info['products_date_ordered']; } //??????

	/**
	 * @return DateTime
	 */
	public function getLastSold() { return $this->info['products_last_sold']; } //??????

	/**
	 * @return ModuleBase
	 */
	public function getProductType() { return $this->info['products_type']; }

	/**
	 * @return int
	 */
	public function getOnOrder() { return (int)$this->info['products_on_order']; }

	/**
	 * @return string
	 */
	public function getInventoryController() { return $this->info['products_inventory_controller']; }

	/**
	 * @return array
	 */
	public function getAdditionalImages() { return (array)$this->info['AdditionalImages']; }

	/**
	 * @param int $val
	 * @return void
	 */
	public function setId($val) {
		$this->info['products_id'] = (int)$val;

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'setProductId')){
			$ProductType->setProductId($this->info['products_id']);
		}
	}

	/**
	 * @param string $val
	 * @param int $langId
	 * @return void
	 */
	public function setName($val, $langId = 0) {
		$ProductType = $this->getProductTypeClass();
		$langId = $this->getLanguageId($langId);
		if (method_exists($ProductType, 'setProductName')){
			$ProductType->setProductName($val, $this->getLanguageId($langId));
		}else{
			$this->info['products_name'][$langId] = $val;;
		}
	}

	/**
	 * @param string $val
	 * @param int $langId
	 * @return void
	 */
	public function setDescription($val, $langId = 0) {
		$ProductType = $this->getProductTypeClass();
		$langId = $this->getLanguageId($langId);
		if (method_exists($ProductType, 'setProductDescription')){
			$ProductType->setProductDescription($val, $this->getLanguageId($langId));
		}else{
			$this->info['products_description'][$langId] = $val;
		}
	}

	/**
	 * @param string $val
	 * @param int $langId
	 * @return void
	 */
	public function setUrl($val, $langId = 0) {
		$this->info['products_url'][$this->getLanguageId($langId)] = $val;
	}

	/**
	 * @param string $val
	 * @param int $langId
	 * @return void
	 */
	public function setSeoUrl($val, $langId = 0) {
		$this->info['products_seo_url'][$this->getLanguageId($langId)] = $val;
	}

	/**
	 * @param string $val
	 * @return void
	 */
	public function setImage($val) { $this->info['products_image'] = $val; }

	/**
	 * @param string $val
	 * @return void
	 */
	public function setModel($val) { $this->info['products_model'] = $val; }

	/**
	 * @param string $val
	 * @return void
	 */
	public function setDateAdded($val) { $this->info['products_date_added'] = new DateTime($val); }

	/**
	 * @param string $val
	 * @return void
	 */
	public function setLastModified($val) { $this->info['products_last_modified'] = new DateTime($val); }

	/**
	 * @param string $val
	 * @return void
	 */
	public function setDateAvailable($val) { $this->info['products_date_available'] = new DateTime($val); }

	/**
	 * @param float $val
	 * @return void
	 */
	public function setWeight($val) { $this->info['products_weight'] = (float)$val; }

	/**
	 * @param float $val
	 * @return void
	 */
	public function setPrice($val) { $this->info['products_price'] = (float)$val; }

	/**
	 * @param float $val
	 * @return void
	 */
	public function setKeepPrice($val) { $this->info['products_keepit_price'] = (float)$val; }

	/**
	 * @param int $val
	 * @return void
	 */
	public function setStatus($val) { $this->info['products_status'] = (int)$val; }

	/**
	 * @param int $val
	 * @return void
	 */
	public function setFeatured($val) { $this->info['products_featured'] = (int)$val; }

	/**
	 * @param int $val
	 * @return void
	 */
	public function setManufacturer($val) { $this->info['manufacturers_id'] = (int)$val; }

	/**
	 * @param int $val
	 * @return void
	 */
	public function setMembershipEnabled($val) { $this->info['membership_enabled'] = $val; }

	/**
	 * @param int $val
	 * @return void
	 */
	public function setTotalOrdered($val) { $this->info['products_ordered'] = (int)$val; }

	/**
	 * @param string $val
	 * @return void
	 */
	public function setDateOrdered($val) { $this->info['products_date_ordered'] = new DateTime($val); }

	/**
	 * @param string $val
	 * @return void
	 */
	public function setLastSold($val) { $this->info['products_last_sold'] = new DateTime($val); }

	/**
	 * @param int $val
	 * @return void
	 */
	public function setOnOrder($val) { $this->info['products_on_order'] = (int)$val; }

	/**
	 * @param $val
	 * @return void
	 */
	public function setInventoryController($val) { $this->info['products_inventory_controller'] = $val; }

	/**
	 * @param array $val
	 * @return void
	 */
	public function addAdditionalImage($val) { $this->info['AdditionalImages'][] = $val; }

	/**
	 * @param string $val
	 * @return void
	 */
	public function setProductType($val) {
		$this->info['products_type'] = $val;

		$this->loadProductType($val);
	}

	/**
	 * @return bool
	 */
	public function hasModel() { return ($this->getModel() != ''); }

	/**
	 * @return bool
	 */
	public function hasImage() { return ($this->getImage() != ''); }

	/**
	 * @return bool
	 */
	public function hasUrl() { return ($this->getUrl(Session::get('languages_id')) != ''); }

	/**
	 * @return bool
	 */
	public function isOnOrder() {
		return ($this->getOnOrder() == 1);
	}

	/**
	 * @return bool
	 */
	public function isAvailable() {
		return ($this->getDateAvailable()->getTimestamp() < time());
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return $this->_isValid;
	}

	/**
	 * @return bool
	 */
	public function isActive() {
		return ($this->getStatus() == 1);
	}

	/**
	 * @return bool
	 */
	public function isFeatured() {
		return ($this->getFeatured() == 1);
	}

	/**
	 * @return bool
	 */
	public function isBox() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function isInBox() {
		return false;
	}

	/**
	 * @param array $CartProductData
	 * @return void
	 */
	public function addToCartPrepare(&$CartProductData){
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'addToCartPrepare')){
			$ProductType->addToCartPrepare(&$CartProductData);
		}
	}

	/**
	 * @param $CartProductData
	 * @return bool
	 */
	public function allowAddToCart(&$CartProductData){
		$allowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'allowAddToCart')){
			$allowed = $ProductType->allowAddToCart(&$CartProductData);
		}
		return $allowed;
	}
}