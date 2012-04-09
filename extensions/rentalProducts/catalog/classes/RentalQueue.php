<?php
require(dirname(__FILE__) . '/rentalQueue/Contents.php');
require(dirname(__FILE__) . '/rentalQueue/Product.php');

class RentalQueue implements Serializable
{

	/**
	 * @var RentalQueueContents
	 */
	private $contents;

	/**
	 * @var int
	 */
	private $queueID = 0;

	/**
	 *
	 */
	public function __construct() {
		$this->emptyQueue();
	}

	/**
	 * @return bool
	 */
	public function hasId(){
		return ($this->getId() > 0);
	}

	/**
	 *
	 */
	public function setId(){
		$this->queueID = $this->generateQueueId();
	}

	/**
	 * @return int
	 */
	public function getId(){
		return $this->queueID;
	}

	/**
	 * @return bool
	 */
	public function hasContents(){
		return ($this->countContents() > 0);
	}

	/**
	 * @return RentalQueueContents
	 */
	public function getContents(){
		return $this->contents;
	}

	/**
	 * @return RentalQueueContents
	 */
	public function getProducts(){
		return $this->getContents();
	}

	/**
	 *
	 */
	public function storeQueue() {
		$userAccount =& Session::getReference('userAccount');
		if ($userAccount->isLoggedIn() === true){
			$CustomersQueue = Doctrine_Core::getTable('CustomersQueue');

			$CustomerQueue = $CustomersQueue->findOneByCustomersId($userAccount->getCustomerId());
			if ($CustomerQueue && $this->hasContents() === false){
				$CustomerQueue->delete();
			}
			else {
				if (!$CustomerQueue){
					$CustomerQueue = $CustomersQueue->create();
					$CustomerQueue->customers_id = $userAccount->getCustomerId();
				}
				$CustomerQueue->queue_data = $this->serialize();
				$CustomerQueue->save();
			}
		}
	}

	/**
	 * @param int $cID
	 */
	public function loadQueue($cID = 0) {
		$load = false;
		if ($cID == 0){
			$userAccount =& Session::getReference('userAccount');
			$load = ($userAccount->isLoggedIn() === true);
			$cID = $userAccount->getCustomerId();
		}else{
			$load = true;
		}

		if ($load){
			$QueueData = Doctrine_Query::create()
				->select('queue_data')
				->from('CustomersQueue')
				->where('customers_id = ?', $cID)
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			if ($QueueData){
				$this->unserialize($QueueData[0]['queue_data']);
			}
		}
	}

	/**
	 * @param bool $reset_database
	 */
	public function emptyQueue($reset_database = false) {
		$this->contents = new RentalQueueContents();

		if ($reset_database == true){
			$this->storeQueue();
		}
		unset($this->queueID);
		if (Session::exists('queueID') === true){
			Session::remove('queueID');
		}
	}

	/**
	 *
	 */
	public function initContents() {
		$Contents = $this->contents->getIterator();
		while($Contents->valid()){
			$RentalProduct = $Contents->current();
			$RentalProduct->init();

			$Contents->next();
		}
	}

	/**
	 * @return string
	 */
	public function serialize() {
		return serialize($this->contents);
	}

	/**
	 * @param string $data
	 */
	public function unserialize($data) {
		$this->contents = unserialize($data);
	}

	/**
	 * @param int $pID
	 */
	private function _notify($pID) {
		Session::set('new_products_id_in_queue', $pID);
	}

	/**
	 * @param int $length
	 * @return bool|string
	 */
	public function generateQueueId($length = 5) {
		return tep_create_random_value($length, 'digits');
	}

	/**
	 * @return string
	 */
	public function getProductIdList() {
		$product_id_list = '';

		foreach($this->contents as $queueProduct){
			$product_id_list .= ', ' . $queueProduct->getIdString();
		}

		return substr($product_id_list, 2);
	}

	/**
	 * @param array $QueueProductData
	 * @param Product $Product
	 * @return bool
	 */
	public function allowAdd($QueueProductData, Product $Product) {
		global $messageStack, $userAccount;
		$return = $Product->isActive();
		$PurchaseType = $Product->getProductTypeClass()->getPurchaseType('membershipRental');

		if ($userAccount->isLoggedIn() === false){
			Session::set('add_to_queue_product_id', $QueueProductData['product_id']);
			//Session::set('add_to_queue_product_attrib', $attribs);
			//$navigation->set_snapshot();
			$messageStack->addSession('pageStack', sysLanguage::get('TO_ADD_TO_QUEUE_MESSAGE'), 'warning');
			tep_redirect(itw_app_link(null, 'account', 'login'));
		}else{
			$customerCanRent = $PurchaseType->rentalAllowed();
			if ($customerCanRent !== true){
				switch($customerCanRent){
					case 'membership':
						$errorMsg = sprintf(sysLanguage::get('TEXT_NOT_RENTAL_CUSTOMER'), itw_app_link('checkoutType=rental', 'checkout', 'default', 'SSL'), itw_app_link(null, 'account', 'login'));
						break;
					case 'inactive':
						$errorMsg = sprintf(sysLanguage::get('TEXT_NOT_ACTIVE_CUSTOMER'), itw_app_link('checkoutType=rental', 'checkout', 'default', 'SSL'));
						break;
					case 'pastdue':
						$errorMsg = sprintf(sysLanguage::get('RENTAL_CUSTOMER_IS_PAST_DUE'), itw_app_link((isset($membership) ? 'edit=' . $membership->getRentalAddressId() : ''), 'account', 'billing_address_book', 'SSL')); //
						break;
				}
				$messageStack->addSession('pageStack', $errorMsg, 'warning');
				tep_redirect(itw_app_link(tep_get_all_get_params(array('action')), 'product', 'info'));
			}
		}

		$currentPlan = $userAccount->plugins['membership']->getPlanId();
		$notEnabledMemberships = explode(';', $Product->getMembershipEnabled());

		if(in_array($currentPlan, $notEnabledMemberships)){
			Session::set('add_to_queue_product_id', $products_id);
			Session::set('add_to_queue_product_attrib', $attributes);
			$messageStack->addSession('pageStack',sprintf(sysLanguage::get('TEXT_UPGRADE_PLAN'),itw_app_link(null,'contact_us','default')),'warning');
			tep_redirect( itw_app_link(null,'rentals','queue'));
			//tep_redirect(itw_app_link('checkoutType=rental','checkout','default'));
		}

		if ($return === true){
			$Allow = EventManager::notifyWithReturn('RentalQueue\AddToQueueAllow', $QueueProductData, $Product);
			foreach($Allow as $Result){
				if ($Result === false){
					$return = false;
					break;
				}
			}
		}
		return $return;
	}

	/**
	 * @param array $v
	 * @return string
	 */
	public function hashArray(array $v){
		return md5(serialize($v));
	}

	/**
	 * @return mixed
	 */
	public function getErrorUrl(){
		return ;
	}

	/**
	 * @param int $productId
	 * @return bool
	 */
	public function add($productId) {
		$Product = new Product($productId);
		$Product->getProductTypeClass()->loadPurchaseType('membershipRental');

		$PurchaseType = $Product->getProductTypeClass()->getPurchaseType('membershipRental');

		$QueueProductData = array(
			'hash_id' => null,
			'product_id' => $Product->getId(),
			'id_string' => $Product->getId()
		);

		$PurchaseType->addToQueuePrepare(&$QueueProductData);

		EventManager::notify('RentalQueue\AddToQueuePrepare', &$QueueProductData);

		$success = false;
		if ($this->allowAdd($QueueProductData, $Product)){
			$hashId = $this->hashArray($QueueProductData);
			$QueueProductData['hash_id'] = $hashId;
			$QueueProductData['priority'] = $this->getMaxPriority();

			$QueueProduct = new RentalQueueProduct($QueueProductData);
			$QueueProduct->loadProductClass($Product);
			$QueueProduct->addToQueueBeforeAction();
			$this->contents->offsetSet($hashId, $QueueProduct);
			$QueueProduct->addToQueueAfterAction();

			$this->queueID = $this->generateQueueId();
			$success = true;

			$this->storeQueue();
		}

		return $success;
	}

	private function getMaxPriority(){
		$max = 0;

		$Iterator = $this->contents->getIterator();
		while($Iterator->valid() === true){
			$QueueProduct = $Iterator->current();

			if ($QueueProduct->getPriority() > $max){
				$max = $QueueProduct->getPriority();
			}

			$Iterator->next();
		}

		return ($max + 1);
	}

	/**
	 * @param int $id
	 */
	public function remove($id) {
		$this->contents->offsetUnset($id);
		$this->queueID = $this->generateQueueId();

		$this->storeQueue();
	}

	/**
	 * @return int
	 */
	public function countContents() {
		return $this->contents->count();
	}

	/**
	 * @param int $pID_string
	 * @return bool
	 */
	public function inQueue($pID_string) {
		$QueueProduct = $this->contents->find($pID_string);
		if ($QueueProduct){
			return true;
		}
		return false;
	}

	/**
	 * @param int $id
	 * @return RentalQueueProduct|null
	 */
	public function getProduct($id){
		$QueueProduct = $this->contents->offsetGet($id);
		if ($QueueProduct){
			return $QueueProduct;
		}
		return null;
	}

	/**
	 *
	 */
	public function restoreContents() {
		$this->loadQueue();
	}

	/**
	 *
	 */
	function fixPriorities(){
		$this->contents->fixPriorities();
		$this->storeQueue();
	}

	/**
	 *
	 */
	function removeSentItems(){
		if (sizeof($this->contents) > 0){
			$userAccount = &$this->getUserAccount();
			foreach($this->contents as $pID_string => $qInfo){
				$Qcheck = Doctrine_Query::create()
					->select('products_id')
					->from('RentedQueue')
					->where('products_id = ?', $pID_string)
					->andWhere('customers_id = ?', $userAccount->getCustomerId())
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				if ($Qcheck){
					$this->removeFromQueue($pID_string, false);
				}
			}
			$this->fixPriorities();
		}
	}
}

?>