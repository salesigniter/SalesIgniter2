<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

$tax_class_array = array(
	array(
		'id'   => '0',
		'text' => sysLanguage::get('TEXT_NONE')
	)
);
$QtaxClass = Doctrine_Query::create()
	->select('tax_class_id, tax_class_title')
	->from('TaxClass')
	->orderBy('tax_class_title')
	->execute()->toArray();
foreach($QtaxClass as $taxClass){
	$tax_class_array[] = array(
		'id'   => $taxClass['tax_class_id'],
		'text' => $taxClass['tax_class_title']
	);
}

switch($App->getPageName()){
	case 'billing_report':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_REPORTS'));
		break;
}
