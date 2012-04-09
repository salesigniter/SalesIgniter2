<?php
/*
	SalesIgniter E-Commerce System v1

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2010 I.T. Web Experts

	This script and it's source is not redistributable
*/
chdir('../../../../');
require('includes/application_top.php');
require('includes/classes/uploadManager.php');

$uploadDir = $_POST['uploadPath'];
$mgr = new UploadManager($uploadDir, '777');
if (isset($_POST['allowedTypes'])){
	$mgr->setExtensions($_POST['allowedTypes']);
}

$json = array(
	'success' => true,
	'messages' => array()
);

foreach($_FILES as $inputName => $fInfo){
	$file = new UploadFile($inputName);
	$success = true;
	if ($mgr->processFile($file) === false){
		$success = false;
		if (isset($json)){
			$json['success'] = false;
		}
	}

	$exception = $mgr->getException();
	$json['messages'][] = $exception->getMessage();
}

if ($json['success'] === true){
	$response = '<div id="status">success</div>' .
		'<div id="message">Upload Completed Successfully</div>';
}else{
	$response = '<div id="status">error</div>' .
	'<div id="message">' . implode($json['messages'], '<br>') . '</div>';
}
echo $response;
itwExit();
?>