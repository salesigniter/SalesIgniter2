<?php
/*
	Sales Igniter E-Commerce System
	Version: 1.0
	
	I.T. Web Experts
	http://www.itwebexperts.com
	
	Copyright (c) 2010 I.T. Web Experts
	
	This script and its source are not distributable without the written conscent of I.T. Web Experts
*/

$contents = array(
	'text'     => 'Content Management',
	'link'     => false,
	'children' => array()
);

$EmailChildren = array();

EmailModules::loadModules();
foreach(EmailModules::getModules() as $Module){
	$EmailChildren[] = array(
		'link' => itw_app_link('module=' . $Module->getCode(), 'emailManager', 'default', 'SSL'),
		'text' => $Module->getTitle()
	);
}
$contents['children'][] = array(
	'link' => false,
	'text' => 'Email Management',
	'children' => $EmailChildren
);

EventManager::notify('BoxCmsAddLink', &$contents);
if (count($contents['children']) == 0){
	$contents = array();
}
?>