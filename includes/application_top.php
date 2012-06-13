<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
// start profiling
if (isset($_GET['runProfile'])){
	xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

error_reporting(E_ALL & ~E_DEPRECATED);

function onShutdown() {
	global $ExceptionManager;
	// This is our shutdown function, in
	// here we can do any last operations
	// before the script is complete.

	if ($ExceptionManager->size() > 0){
		echo '<br /><div style="width:98%;margin-right:auto;margin-left:auto;">' . $ExceptionManager->output('text') . '</div>';
	}
	session_write_close();
}

register_shutdown_function('onShutdown');

define('APPLICATION_ENVIRONMENT', (isset($_GET['env']) ? $_GET['env'] : 'catalog'));
define('DATE_TIMESTAMP', 'Y-m-d H:i:s');

// start the timer for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());
define('START_MEMORY_USAGE', memory_get_usage());
require('includes/classes/Profiler/Base.php');
require('includes/classes/ConfigReader/Base.php');
require('includes/classes/MainConfigReader.php');
require('includes/classes/ModuleConfigReader.php');
require('includes/classes/ExtensionConfigReader.php');
require('includes/classes/system_configuration.php');
require('includes/classes/SesDateTime.php');

/* TO BE MOVED LATER -- BEGIN -- */
include('includes/conversionArrays.php');
define('USER_ADDRESS_BOOK_ENABLED', 'True');
/* TO BE MOVED LATER -- END -- */

date_default_timezone_set('America/New_York');

/*
 * Load system path/database settings
 */
sysConfig::init();

require(sysConfig::getDirFsCatalog() . 'ext/Doctrine.php');
spl_autoload_register(array('Doctrine_Core', 'autoload'));
spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true);
$manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
/*
	* KNOWN ISSUES
	1: causes the extension installer to not install doctrine tables
   */
$manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
Doctrine_Core::setModelsDirectory(sysConfig::getDirFsCatalog() . 'ext/Doctrine/Models');
//Doctrine_Core::loadModels(sysConfig::getDirFsCatalog() . 'ext/Doctrine/Models');

$profiler = new Doctrine_Connection_Profiler();

$conn = Doctrine_Manager::connection(sysConfig::get('DOCTRINE_CONN_STRING'), 'mainConnection');

$conn->setListener($profiler);

/*$cacheConnection = Doctrine_Manager::connection(new PDO('sqlite::memory:'), 'cacheConnection');
	$cacheDriver = new Doctrine_Cache_Db(array(
		'connection' => $conn,
		'tableName'  => 'DoctrineCache'
	));
	$conn->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver);
	$conn->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $cacheDriver);
	$conn->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN, 3600);*/
$conn->setCharset('utf8');
$conn->setCollate('utf8_general_ci');
$manager->setCurrentConnection('mainConnection');
$manager->setCollate('utf8_general_ci');
$manager->setCharset('utf8');

// customization for the design layout
define('BOX_WIDTH', 195); // how wide the boxes should be in pixels (default: 125)
define('RATING_UNITWIDTH', 10);
// include the database functions

require(sysConfig::getDirFsCatalog() . 'includes/functions/database.php');

// set the application parameters
sysConfig::load();

require(sysConfig::getDirFsCatalog() . 'includes/classes/ttfInfo.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/MultipleInheritance.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/Importable/Bindable.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/Importable/Installable.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/Importable/SortedDisplay.php');

require(sysConfig::getDirFsCatalog() . 'includes/classes/htmlBase.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/exceptionManager.php');
$ExceptionManager = new ExceptionManager;
set_error_handler(array($ExceptionManager, 'addError'));
set_exception_handler(array($ExceptionManager, 'add'));

require(sysConfig::getDirFsCatalog() . 'includes/classes/eventManager/Manager.php');

// define general functions used application-wide
require(sysConfig::getDirFsCatalog() . 'includes/functions/global.php');
require(sysConfig::getDirFsCatalog() . 'includes/functions/general.php');
require(sysConfig::getDirFsCatalog() . 'includes/functions/crypt.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/system_modules_loader.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/ModuleBase.php');
//require(sysConfig::getDirFsCatalog() . 'includes/modules/purchaseTypeModules/PurchaseTypeAbstract.php');
require(sysConfig::getDirFsCatalog() . 'includes/functions/html_output.php');

//Email Template Manager Start
require(sysConfig::getDirFsCatalog() . 'includes/classes/email_events.php');
//Email Template Manager End

/*
 * All Classes that will be registered in sessions must go here -- BEGIN
 */

require(sysConfig::getDirFsCatalog() . 'includes/classes/user/membership.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/user/address_book.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/user.php');

// include shopping cart class
require(sysConfig::getDirFsCatalog() . 'includes/classes/shopping_cart.php');

// include navigation history class
require(sysConfig::getDirFsCatalog() . 'includes/classes/navigation_history.php');

// include the product class
require(sysConfig::getDirFsCatalog() . 'includes/classes/ProductBase.php');

//Include the order class
//require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/Base.php');

/*
 * All Classes that will be registered in sessions must go here -- END
 */

require(sysConfig::getDirFsCatalog() . 'includes/classes/http_client.php');

require(sysConfig::getDirFsCatalog() . 'includes/classes/application.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/extension.php');

$App = new Application;
$appExtension = new Extension;

$appExtension->preSessionInit();

// define how the session functions will be used
require(sysConfig::getDirFsCatalog() . 'includes/classes/session.php');
Session::init(); /* Initialize the session */

// initialize the message stack for output messages
require(sysConfig::getDirFsCatalog() . 'includes/classes/message_stack.php');
$messageStack = new messageStack;

require(sysConfig::getDirFsCatalog() . 'includes/classes/system_language.php');
sysLanguage::init();

if (APPLICATION_ENVIRONMENT == 'catalog'){
	if (Session::exists('userAccount') === false){
		$userAccount = new RentalStoreUser();
		$userAccount->loadPlugins();
		Session::set('userAccount', $userAccount);
	}
	$userAccount = &Session::getReference('userAccount');
	$appExtension->bindMethods($userAccount);

	// create the shopping cart & fix the cart if necesary
	if (Session::exists('ShoppingCart') === false){
		$ShoppingCart = new ShoppingCart();
		Session::set('ShoppingCart', $ShoppingCart);
	}
	$ShoppingCart = &Session::getReference('ShoppingCart');

	// navigation history
	if (Session::exists('navigation') === false){
		Session::set('navigation', new navigationHistory);
	}
	$navigation = &Session::getReference('navigation');
	if (basename($_SERVER['PHP_SELF']) != 'javascript.php' && basename($_SERVER['PHP_SELF']) != 'stylesheet.php'){
		$navigation->add_current_page();
	}
}

$appExtension->postSessionInit();

$ExceptionManager->initSessionMessages();

require(sysConfig::getDirFsCatalog() . 'includes/modules/orderShippingModules/modules.php');
require(sysConfig::getDirFsCatalog() . 'includes/modules/orderPaymentModules/modules.php');
require(sysConfig::getDirFsCatalog() . 'includes/modules/orderTotalModules/modules.php');

if (!class_exists('ProductTypeModules')){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/productTypeModules/modules.php');
}

if (!class_exists('PurchaseTypeModules')){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/purchaseTypeModules/modules.php');
}

// include the mail classes
require(sysConfig::getDirFsCatalog() . 'includes/classes/mime.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/email.php');

// include currencies class and create an instance
require(sysConfig::getDirFsCatalog() . 'includes/classes/currencies.php');
$currencies = new currencies();

// include the breadcrumb class and start the breadcrumb trail
require(sysConfig::getDirFsCatalog() . 'includes/classes/breadcrumb.php');
$breadcrumb = new breadcrumb;

//$breadcrumb->add(sysLanguage::get('HEADER_TITLE_CATALOG') . ' ' . sysLanguage::get('HEADER_LINK_HOME'), itw_app_link(null, 'index', 'default'));

$appExtension->loadExtensions();

$App->loadApplication((isset($_GET['app']) ? $_GET['app'] : ''), (isset($_GET['appPage']) ? $_GET['appPage'] : ''));
if ($App->isValid() === false){
	echo 'No valid application found.';
	itwExit();
}
$appExtension->initApplicationPlugins();

$App->loadLanguageDefines();

if (APPLICATION_ENVIRONMENT == 'catalog'){
	$ShoppingCart->initContents();

	EventManager::notify('ApplicationTopBeforeCartAction');

	// Shopping cart actions
	$action = (isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : ''));

	if (isset($_POST['update_product'])){
		$action = 'update_product';
	}
	if (isset($_POST['add_product'])){
		$action = 'add_product';
	}
	if (isset($_POST['rent_now'])){
		$action = 'rent_now';
	}
	if (isset($_POST['cust_order'])){
		$action = 'cust_order';
	}
	if (isset($_POST['checkout'])){
		$action = 'update_product';
	}

	EventManager::notify('ApplicationTopActionCheckPost', &$action);
	if (!empty($action)){
		// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled
		if ($session_started == false){
			tep_redirect(itw_app_link('appExt=infoPages', 'show_page', 'cookie_usage'));
		}

		if (isset($_POST['checkout'])){
			$parameters = array('action', 'cPath', 'products_id', 'pid');
			$goto = itw_app_link(tep_get_all_get_params($parameters), 'checkout', 'default', 'SSL');
		}
		elseif (sysConfig::get('DISPLAY_CART') == 'true') {
			$parameters = array('app', 'appPage', 'action', 'cPath', 'products_id', 'pid');
			$goto = itw_app_link(tep_get_all_get_params($parameters), 'shoppingCart', 'default');
		}
		else {
			$parameters = array('action', 'pid', 'products_id');
			if (isset($_GET['seoTag'])){
				$goto = itw_app_link('products_id=' . $_GET['products_id'], 'product', 'info');
			}
			else {
				//if (isset($_GET['app'])){
				//tep_redirect(itw_app_link(tep_get_all_get_params($parameters)));
				//}else{
				$goto = itw_app_link(tep_get_all_get_params($parameters));
				//}
			}
		}

		if ($action == 'add_queue' || $action == 'rent_now'){
			$QboxCheck = Doctrine_Query::create()
				->select('products_id')
				->from('ProductsToBox')
				->where('box_id = ?', (int)$_REQUEST['products_id'])
				->execute();
			if ($QboxCheck->count() > 0){
				$_REQUEST['action'] = 'add_queue_all';
			}
			$QboxCheck->free();
			unset($QboxCheck);
		}

		$productsId = (isset($_POST['products_id']) ? $_POST['products_id'] : (isset($_GET['products_id']) ? $_GET['products_id'] : null));
		switch($action){
			// customer wants to update the product quantity in their shopping cart
			case 'update_product' :
				$error = false;
				if (isset($_POST['cart_id']) && !empty($_POST['cart_id'])){
					$deleteArr = (isset($_POST['cart_delete']) && is_array($_POST['cart_delete']) ? $_POST['cart_delete'] : array());
					foreach($_POST['cart_id'] as $cartId){
						if (in_array($cartId, $deleteArr)){
							$ShoppingCart->remove($cartId);
						}
						else {
							$ShoppingCart->update($cartId);
						}
					}
				}

				if (isset($_GET['rType']) && $_GET['rType'] == 'ajax'){
					echo json_encode(array(
						'success' => ($error === false)
					));
				}
				else {
					if ($error === false){
						tep_redirect($goto);
					}
					else {
						$messageStack->add('pageStack', 'There was an error updating your shopping cart.', 'error');
					}
				}
				break;
			case 'rent_now':
				$pID = $productsId;
				$attribs = array();
				if (isset($_GET['id']) && isset($_GET['id']['rental'])){
					$attribs = $_GET['id']['rental'];
				}
				elseif (isset($_POST['id']) && isset($_POST['id']['rental'])) {
					$attribs = $_POST['id']['rental'];
				}
				if ($userAccount->isLoggedIn() === true){
					if ($pID === false){
						$messageStack->addSession('pageStack', 'Error: No Product Id Found', 'warning');
						tep_redirect(itw_app_link(tep_get_all_get_params(array('action')), 'product', 'info'));
					}

					$customerCanRent = $rentalQueue->rentalAllowed($userAccount->getCustomerId());
					$membership =& $userAccount->plugins['membership'];
					if ($customerCanRent !== true){
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

					$rentalQueue->addToQueue($pID, $attribs);
					tep_redirect(itw_app_link(tep_get_all_get_params($parameters), 'rentals', 'queue'));
				}
				else {
					Session::set('add_to_queue_product_id', $productsId);
					Session::set('add_to_queue_product_attrib', $attribs);
					//$navigation->set_snapshot();
					$messageStack->addSession('pageStack', sysLanguage::get('TO_ADD_TO_QUEUE_MESSAGE'), 'warning');
					tep_redirect(itw_app_link('checkoutType=rental', 'checkout', 'default', 'SSL'));
				}
				break;
			case 'rateProduct':
				if ($userAccount->isLoggedIn() && isset($_GET['pID'])){
					$Ratings = Doctrine_Core::getTable('Ratings');
					$Rating = $Ratings->findOneByProductsIdAndCustomersId($_GET['pID'], $userAccount->getCustomerId());
					if (!$Rating){
						$Rating = $Ratings->create();
						$Rating->products_id = $_GET['pID'];
						$Rating->customers_id = $userAccount->getCustomerId();
					}
					$Rating->reviews_rating = number_format((float)$_GET['rating'], 1);
					$Rating->save();

					echo '{ success: true }';
				}
				else {
					echo '{ success: false }';
				}
				itwExit();
				break;
		}

		EventManager::notify('ApplicationTopAction_' . $action);
	}

	// include the who's online functions
	require(sysConfig::getDirFsCatalog() . 'includes/functions/whos_online.php');
	tep_update_whos_online();

	// include the password crypto functions
	require(sysConfig::getDirFsCatalog() . 'includes/functions/password_funcs.php');

	// include validation functions (right now only email address)
	require(sysConfig::getDirFsCatalog() . 'includes/functions/validations.php');

	// split-page-results
	require(sysConfig::getDirFsCatalog() . 'includes/classes/split_page_results.php');

	// infobox
	require(sysConfig::getDirFsCatalog() . 'includes/classes/boxes.php');

	// calculate category path
	if (isset($_GET['cPath'])){
		$cPath = $_GET['cPath'];
	}
	else {
		$cPath = '';
	}

	if (tep_not_null($cPath)){
		$cPath_array = tep_parse_category_path($cPath);
		$cPath = implode('_', $cPath_array);
		$current_category_id = $cPath_array[(sizeof($cPath_array) - 1)];
		Session::set('current_category_id', $current_category_id);
	}
	else {
		$current_category_id = 0;
	}

	// add category names to the breadcrumb trail
	if (isset($cPath_array)){
		for($i = 0, $n = sizeof($cPath_array); $i < $n; $i++){
			$Category = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchArray('select categories_name from categories_description where categories_id = "' . (int)$cPath_array[$i] . '" and language_id = "' . Session::get('languages_id') . '"');
			if (sizeof($Category) > 0){
				$breadcrumb->add($Category[0]['categories_name'], itw_app_link('cPath=' . implode('_', array_slice($cPath_array, 0, ($i + 1))), 'index', 'default'));
			}
			else {
				break;
			}
		}
	}

	// add the products model to the breadcrumb trail
	if (isset($_GET['products_id'])){
		$Product = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchArray('select products_name from products_description where products_id = "' . (int)$_GET['products_id'] . '" and language_id = "' . Session::get('languages_id') . '"');
		if (sizeof($Product) > 0){
			$breadcrumb->add($Product[0]['products_name'], itw_app_link('products_id=' . (int)$_GET['products_id'], 'product', 'info'));
		}
	}

	// set which precautions should be checked
	define('WARN_INSTALL_EXISTENCE', 'true');
	define('WARN_CONFIG_WRITEABLE', 'true');
	define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
	define('WARN_SESSION_AUTO_START', 'true');
	define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');

	require(sysConfig::getDirFsCatalog() . 'includes/add_ccgvdc_application_top.php'); // ICW CREDIT CLASS Gift Voucher Addittion
}

class PagerLayoutWithArrows extends Doctrine_Pager_Layout
{

	private $myType = '';

	public function setMyType($val) {
		$this->myType = $val;
	}

	public function getMyType() {
		return $this->myType;
	}

	public function display($options = array(), $return = false) {
		if (empty($this->myType)){
			$this->myType = sysLanguage::get('TEXT_PAGER_TYPE');
		}
		$pager = $this->getPager();
		$str = '';

		// First page
		$this->addMaskReplacement('page', '&laquo;', true);
		$options['page_number'] = $pager->getFirstPage();
		$str .= $this->processPage($options);

		// Previous page
		$this->addMaskReplacement('page', '&lsaquo;', true);
		$options['page_number'] = $pager->getPreviousPage();
		$str .= $this->processPage($options);

		// Pages listing
		$this->removeMaskReplacement('page');
		$str .= parent::display($options, true);

		// Next page
		$this->addMaskReplacement('page', '&rsaquo;', true);
		$options['page_number'] = $pager->getNextPage();
		$str .= $this->processPage($options);

		// Last page
		$this->addMaskReplacement('page', '&raquo;', true);
		$options['page_number'] = $pager->getLastPage();
		$str .= $this->processPage($options);

		$str .= '&nbsp;&nbsp;<b>' . $pager->getFirstIndice() . ' - ' . $pager->getLastIndice() . ' (' . sysLanguage::get('TEXT_PAGER_OF') . ' ' . $pager->getNumResults() . ' ' . $this->myType . ')</b>';
		// Possible wish to return value instead of print it on screen
		if ($return){
			return $str;
		}
		echo $str;
	}
}

require(sysConfig::getDirFsCatalog() . 'includes/classes/products.php');
$storeProducts = new storeProducts();

require(sysConfig::getDirFsCatalog() . 'includes/classes/productListing_row.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/productListing_col.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/productListing_date.php');
?>