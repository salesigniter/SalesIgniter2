<?php
	$typesArray = array(
		'text'     => 'Text',
		'textarea' => 'Textarea',
		'select'   => 'Drop Down',
		'upload'   => 'Upload',
		'search'   => 'Click to search'
	);

	$barcodeStatusArray = array(
		array('id' => 'A', 'text' => 'Available'),
		array('id' => 'B', 'text' => 'Broken'),
		array('id' => 'O', 'text' => 'Out'),
		array('id' => 'R', 'text' => 'Reserved'),
		array('id' => 'P', 'text' => 'Purchased'),
		array('id' => 'T', 'text' => 'In Transfer')
	);

	$barcodeStatuses = array(
		'A' => 'Available',
		'B' => 'Broken',
		'O' => 'Out',
		'R' => 'Reserved',
		'P' => 'Purchased',
		'T' => 'In Transfer'
	);

	$extraInfoPages = array(
		'page' => 'Stand Alone',
		'popup' => 'Popup',
		'block' => 'Content Block'
	);
	
	$fileTypeUploadDirs = array(
		'image' => array(
			'rel' => sysConfig::getDirWsCatalog() . 'images/',
			'abs' => sysConfig::getDirFsCatalog() . 'images/'
		),
		'file' => array(
			'rel' => sysConfig::getDirWsCatalog() . 'files/',
			'abs' => sysConfig::getDirFsCatalog() . 'files/'
		),
		'movie' => array(
			'rel' => sysConfig::getDirWsCatalog() . 'movies/',
			'abs' => sysConfig::getDirFsCatalog() . 'movies/'
		)
	);
?>