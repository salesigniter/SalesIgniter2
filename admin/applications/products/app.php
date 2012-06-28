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

switch($AppPage->getName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFAULT'));
		break;
	case 'new_product':
		$AjaxSaveButton = htmlBase::newElement('button')
		->setType('submit')
		->usePreset('save')
		->addClass('ajaxSave')
		->setText(sysLanguage::get('TEXT_BUTTON_AJAX_SAVE'));

		$SaveButton = htmlBase::newElement('button')
		->setType('submit')
		->usePreset('save');
		if (isset($_GET['product_id'])){
			sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EDIT_PRODUCT'));
			$SaveButton->setText(sysLanguage::get('TEXT_BUTTON_UPDATE'));
		}
		else {
			sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NEW_PRODUCT'));
			$messageStack->add('pageStack', sysLanguage::get('INFO_MESSAGE_NEW_PRODUCT'), 'info');
			$SaveButton->setText(sysLanguage::get('TEXT_BUTTON_UPDATE'));
		}

		$CancelButton = htmlBase::newButton()
		->usePreset('cancel');
		if (Session::exists('categories_cancel_link') === true){
			$CancelButton->setHref(Session::get('categories_cancel_link'));
		}
		else {
			$CancelButton->setHref(itw_app_link((isset($_GET['product_id']) ? 'product_id=' . $_GET['product_id'] : ''), null, 'default'));
		}

		$AppPage->setPageFormParam(array(
			'name'   => 'new_product',
			'action' => itw_app_link(tep_get_all_get_params(array('action', 'product_id')) . 'action=saveProduct' . (isset($_GET['product_id']) ? '&product_id=' . $_GET['product_id'] : '')),
			'method' => 'post'
		));

		$AppPage->addMenuItem($AjaxSaveButton);
		$AppPage->addMenuItem($SaveButton);
		$AppPage->addMenuItem($CancelButton);
		break;
	case 'expected':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EXPECTED'));
		break;
}

if (!$App->getPageName() != 'expected'){

	if ($App->getAppPage() == 'new_product'){
		$Product = new Product(
			(isset($_GET['product_id']) && empty($_POST) ? $_GET['product_id'] : ''),
			true
		);
		if (!isset($_GET['product_id']) && isset($_GET['productType'])){
			$Product->setProductType($_GET['productType']);
		}

		$App->addJavascriptFile('ext/jQuery/external/datepick/jquery.datepick.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		$App->addJavascriptFile('ext/jQuery/external/fancybox/jquery.fancybox.js');
		$App->addJavascriptFile('ext/dymo_label_framework.js');
		$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.labelPrinter.js');

		$App->addStylesheetFile('ext/jQuery/external/datepick/css/jquery.datepick.css');
		$App->addStylesheetFile('ext/jQuery/external/fancybox/jquery.fancybox.css');

		$ProductType = $Product->getProductTypeClass();
		if (file_exists($ProductType->getPath() . 'admin/applications/products/javascript/new_product.js')){
			$App->addJavascriptFile($ProductType->getRelativePath() . 'admin/applications/products/javascript/new_product.js');
		}
	}

	$trackMethods = array(
		array(
			'id'   => 'quantity',
			'text' => 'Use Quantity Tracking'
		),
		array(
			'id'   => 'barcode',
			'text' => 'Use Barcode Tracking'
		)
	);
}
