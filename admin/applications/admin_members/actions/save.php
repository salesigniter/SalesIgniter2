<?php
$Qcheck = Doctrine_Query::create()
	->select('admin_email_address')
	->from('Admin')
	->where('admin_email_address = ?', $_POST['admin_email_address']);
if (isset($_GET['admin_id'])){
	$Qcheck->andWhere('admin_id != ?', (int)$_GET['admin_id']);
}
$Result = $Qcheck->execute();

$errorMsg = '';
if (!isset($_GET['admin_id'])){
	function randomize() {
		$salt = "abchefghjkmnpqrstuvwxyz0123456789";
		srand((double)microtime() * 1000000);
		$i = 0;
		while($i <= 7){
			$num = rand() % 33;
			$tmp = substr($salt, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}

	$makePassword = randomize();
}

$Admin = Doctrine_Core::getTable('Admin');
if (isset($_GET['admin_id'])){
	$adminAccount = $Admin->findOneByAdminId((int)$_GET['admin_id']);
}
else {
	$adminAccount = $Admin->create();
	//$adminAccount->admin_password = tep_encrypt_password($makePassword);
}

if (!empty($_POST['admin_password'])){
	$adminAccount->admin_password = tep_encrypt_password($_POST['admin_password']);
}
$adminAccount->admin_override_password = (!empty($_POST['admin_override_password']) ? $_POST['admin_override_password'] : '');
$adminAccount->admin_groups_id = $_POST['admin_groups_id'];
$adminAccount->admin_firstname = $_POST['admin_firstname'];
$adminAccount->admin_lastname = $_POST['admin_lastname'];
$adminAccount->admin_email_address = $_POST['admin_email_address'];
$adminAccount->admin_simple_admin = isset($_POST['simple_admin']) ? 1 : 0;
if ($_POST['admin_favorites_id'] == '0'){
	$adminAccount->favorites_links = str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link(null, 'categories', 'default')) . ';' .
		str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link(null, 'products', 'default')) . ';' .
		str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link('appExt=infoPages', 'manage', 'default')) . ';' .
		str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link('appExt=payPerRentals', 'reservations_reports', 'default')) . ';' .
		str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link('appExt=payPerRentals', 'return', 'default')) . ';' .
		str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link('appExt=payPerRentals', 'send', 'default')) . ';' .
		str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link('appExt=blog', 'blog_posts', 'default')) . ';' .
		str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link(null, 'label_maker', 'default')) . ';' .
		str_replace(sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(), '', itw_app_link(null, 'rental_queue', 'default'));

	$adminAccount->favorites_names = 'Categories;' .
		'Products;' .
		'Manage Pages;' .
		'Reservation Reports;' .
		'Return Reservation Rentals;' .
		'Send Reservation Rentals;' .
		'Manage Blog Posts;' .
		'Label Maker;' .
		'Rental Queue;';
}
else {
	$AdminFavs = Doctrine_Core::getTable('AdminFavorites')->find($_POST['admin_favorites_id']);
	$adminAccount->favorites_links = $AdminFavs->favorites_links;
	$adminAccount->favorites_names = $AdminFavs->favorites_names;
	$adminAccount->admin_favs_id = $AdminFavs->admin_favs_id;
}

$response = array(
	'success' => false
);
if ($adminAccount->isValid(true) === false){
	$AdminErrorStack = $adminAccount->getErrorStack();

	foreach($AdminErrorStack->toArray() as $FieldName => $ErrorTypes){
		foreach($ErrorTypes as $ErrorType){
			$messageStack->add('pageStack', sysLanguage::get('TEXT_' . strtoupper($FieldName) . '_ERROR_' . strtoupper($ErrorType)), 'error');
		}
	}

	if ($messageStack->size('pageStack') > 0){
		$response['messageStack'] = $messageStack->output('pageStack', true);
	}
}
else {
	$response['success'] = true;
	$adminAccount->save();

	if (isset($_GET['admin_id'])){
		$subject = sysLanguage::get('ADMIN_EMAIL_EDIT_SUBJECT');
		$string = sysLanguage::get('ADMIN_EMAIL_EDIT_TEXT');
		$passText = '--hidden--';
	}
	else {
		$subject = sysLanguage::get('ADMIN_EMAIL_SUBJECT');
		$string = sysLanguage::get('ADMIN_EMAIL_TEXT');
		$passText = $makePassword;
	}

	tep_mail(
		$adminAccount->admin_firstname . ' ' . $adminAccount->admin_lastname,
		$adminAccount->admin_email_address,
		$subject,
		sprintf(
			str_replace('\n', "\n", $string),
			$adminAccount->admin_firstname,
			sysConfig::get('HTTP_SERVER') . sysConfig::getDirWsAdmin(),
			$adminAccount->admin_email_address,
			$passText,
			sysConfig::get('STORE_OWNER')
		),
		sysConfig::get('STORE_OWNER'),
		sysConfig::get('STORE_OWNER_EMAIL_ADDRESS')
	);
}

EventManager::attachActionResponse($response, 'json');
