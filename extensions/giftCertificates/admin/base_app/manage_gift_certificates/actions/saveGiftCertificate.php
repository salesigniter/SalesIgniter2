<?php
$GiftCertificates = Doctrine_Core::getTable('GiftCertificates');
if (isset($_GET['gift_certificates_id'])){
	$GiftCertificates = $GiftCertificates->find((int)$_GET['gift_certificates_id']);
	$GiftCertificates->PurchaseTypes->clear();
} else{
	$GiftCertificates = $GiftCertificates->create();
}
$GiftCertificates->gift_certificates_price = $_POST['gift_certificates_price'];

foreach (sysLanguage::getLanguages() as $lInfo){
	$GiftCertificates->GiftCertificatesDescription[$lInfo['id']]->language_id = $lInfo['id'];
	$GiftCertificates->GiftCertificatesDescription[$lInfo['id']]->gift_certificates_name = $_POST['gift_certificates_name'][$lInfo['id']];
	$GiftCertificates->GiftCertificatesDescription[$lInfo['id']]->gift_certificates_description = $_POST['gift_certificates_description'][$lInfo['id']];
}
$i = 0;
foreach ($_POST['gift_certificates_purchase_type'] as $Code => $On){
	$PurchaseType = new GiftCertificatesToPurchaseTypes();
	$PurchaseType->type_name = $Code;
	$PurchaseType->gift_certificates_value = $_POST['gift_certificates_purchase_type_value'][$Code];

	$GiftCertificates->PurchaseTypes->add($PurchaseType);
}

EventManager::notify('GiftCertificatesEditBeforeSave', $GiftCertificates);

$GiftCertificates->save();

EventManager::attachActionResponse(array(
	'success'  => true
), 'json');
