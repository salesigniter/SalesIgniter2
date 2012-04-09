<?php
if (!isset($_GET['secretKey']) || !isset($_GET[$_GET['secretKey']])){
	header("HTTP/1.0 404 Not Found");
	//header("Status: 404 Not Found");
	exit;
}
$ConfigFile = simplexml_load_file('../includes/configure.xml');
$checkVal = (string) $ConfigFile->config[13]->value;
$key = $_GET[$_GET['secretKey']];
if ($key != $checkVal){
	die('Keys do not match!');
}
$fsCatalog = (string) $ConfigFile->config[3]->value . (string) $ConfigFile->config[2]->value;
$Directory = new RecursiveDirectoryIterator($fsCatalog);
$Iterator = new RecursiveIteratorIterator($Directory);
echo '(?!.*\/images|cache|ext\/)(?:^.*\.php$)';
$Regex = new RegexIterator($Iterator, '/^.+\.(php|js|css|htaccess|png|gif|jpg|jpeg)$/i', RecursiveRegexIterator::GET_MATCH);
$arr = array();
foreach($Regex as $fInfo){
	if (preg_match('/' . str_replace('/', '\/', $fsCatalog) . '[cache|images|templates]\//', $fInfo[0])){
		continue;
	}
	$arr[] = array(
		'pathAbs' => $fInfo[0],
		'pathRel' => str_replace($fsCatalog, '', $fInfo[0]),
		'cksum' => sha1_file($fInfo[0])
	);
}

echo json_encode($arr);
exit;