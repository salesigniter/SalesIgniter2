<?php
/*
	Rental Products Extension Version 1

	I.T. Web Experts, SalesIgniter v1
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class Extension_rentalProducts extends ExtensionBase
{

	public function __construct() {
		parent::__construct('rentalProducts');
	}

	public function preSessionInit() {
		require(dirname(__FILE__) . '/catalog/classes/RentalQueue.php');
	}

	public function init() {
		global $appExtension, $RentalQueue;
		if ($this->isEnabled() === false){
			return;
		}

		EventManager::attachEvents(array(
			'ProductQueryBeforeExecute',
			'ProductInfoClassConstruct',
			'OrderQueryBeforeExecute',
			'ApplicationTopActionCheckPost',
			'ApplicationTopAction_add_queue',
			'ApplicationTopAction_add_queue_all',
			'ApplicationTopAction_update_queue',
			'ProcessLoginAfterExecute'
		), null, $this);

		if (APPLICATION_ENVIRONMENT == 'admin'){
			EventManager::attachEvents(array(
				'AdminNavMenuAddBox'
			), null, $this);
		}

		if (APPLICATION_ENVIRONMENT == 'catalog'){
			// create the rental queue & fix the queue if necesary - added by Deepali
			if (Session::exists('RentalQueue') === false){
				$RentalQueue = new RentalQueue();
				Session::set('RentalQueue', $RentalQueue);
			}
			$RentalQueue =& Session::getReference('RentalQueue');
			$RentalQueue->initContents();
		}

		$ProductsPurchaseTypes = Doctrine::getTable('ProductsPurchaseTypes')->getRecordInstance();

		$ProductsPurchaseTypes->hasOne('ProductsPurchaseTypesRentalSettings as RentalSettings', array(
			'local' => 'purchase_type_id',
			'foreign' => 'purchase_type_id',
			'cascade' => array('delete')
		));
	}

	public function AdminNavMenuAddBox(&$boxFiles){
		$sep = DIRECTORY_SEPARATOR;
		$boxFiles[] = __DIR__ . $sep . 'admin' . $sep . 'infoboxes' . $sep . 'rentalProducts.php';
	}

	public function ProcessLoginAfterExecute(RentalStoreUser $UserAccount){
		if (Session::exists('RentalQueue') === true){
			Session::set('rental_address_id', $UserAccount->plugins['membership']->membershipInfo['rental_address_id']);

			$RentalQueue = &Session::getReference('RentalQueue');
			$RentalQueue->restoreContents();
		}
	}

	public function ApplicationTopActionCheckPost(&$action) {
		if (isset($_POST['add_queue'])){
			$action = 'add_queue';
		}
		if (isset($_POST['add_queue_all'])){
			$action = 'add_queue_all';
		}
		if (isset($_POST['update_queue'])){
			$action = 'update_queue';
		}
	}

	public function ApplicationTopAction_add_queue() {
		$productsId = (isset($_POST['products_id']) ? $_POST['products_id'] : (isset($_GET['products_id']) ? $_GET['products_id'] : null));

		$RentalQueue =& Session::getReference('RentalQueue');
		$RentalQueue->add($productsId);
	}

	public function ApplicationTopAction_add_queue_all() {
		if ($userAccount->isLoggedIn() === true){
			$customerCanRent = $rentalQueue->rentalAllowed($customer_id);
			if ($customerCanRent === true){
				$pID = false;
				if (isset($_GET['products_id'])){
					$pID = $_GET['products_id'];
				}
				elseif (isset($_POST['products_id'])) {
					$pID = $_POST['products_id'];
				}

				if ($pID === false){
					$messageStack->addSession('pageStack', 'Error: No Product Id Found', 'warning');
					tep_redirect(itw_app_link(tep_get_all_get_params(array('action')), 'product', 'info'));
				}

				$rentalQueue->addBoxSet((int)$pID);
				tep_redirect(itw_app_link(tep_get_all_get_params($parameters), 'rentals', 'queue'));
			}
			else {
				$membership =& $userAccount->plugins['membership'];

				switch($customerCanRent){
					case 'membership':
						if (Session::exists('account_action') === true){
							Session::remove('account_action');
						}

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
		else {
			$navigation->set_snapshot();
			$messageStack->addSession('pageStack', sysLanguage::get('TO_ADD_TO_QUEUE_MESSAGE'), 'warning');
			tep_redirect(itw_app_link(tep_get_all_get_params($parameters), 'account', 'login') . '#tabNewRentAccount');
		}
	}

	public function ApplicationTopAction_update_queue() {
		$productsId = (isset($_POST['products_id']) ? $_POST['products_id'] : (isset($_GET['products_id']) ? $_GET['products_id'] : null));
		for($i = 0, $n = sizeof($productsId); $i < $n; $i++){
			if (in_array($productsId[$i], (isset($_REQUEST['queue_delete']) && is_array($_REQUEST['queue_delete']) ? $_REQUEST['queue_delete'] : array()))){
				$rentalQueueBase->removeFromQueue($productsId[$i]);
			}
			else {
				if ($_REQUEST['queue_priority'][$i] == ""){
					$_REQUEST['queue_priority'][$i] = 999;
				}
				$rentalQueue->updatePriority($productsId[$i], $_REQUEST['queue_priority'][$i], $_REQUEST['queue_previous_priority'][$i]);
			}
		}
		$rentalQueueBase->fixPriorities();
		tep_redirect(itw_app_link(tep_get_all_get_params($parameters), 'rentals', 'queue'));
	}

	public function ProductInfoClassConstruct(Product &$Product, Products $ProductModel) {
		$Product->setMembershipEnabled($ProductModel->membership_enabled);

		$Product->bindMethod('getMembershipEnabled', function (Product $Product) {
			return explode(',', $Product->info['_membership_enabled']);
		});

		$Product->bindMethod('getRentalSettings', function (Product $Product) {
			$QrentalSettings = Doctrine_Query::create()
				->from('ProductsRentalSettings')
				->where('products_id = ?', $Product->getId())
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			return ($QrentalSettings && sizeof($QrentalSettings) > 0 ? $QrentalSettings[0] : false);
		});
	}

	public function ProductQueryBeforeExecute(&$productQuery) {
		$productQuery->addSelect('prs.*')
			->leftJoin('p.ProductsRentalSettings prs');
	}

	public function OrderQueryBeforeExecute(&$Qorder) {
		$Qorder->leftJoin('op.OrdersProductsRentals rented')
			->leftJoin('rented.ProductsInventoryBarcodes rentedBarcodes');
	}
}

?>