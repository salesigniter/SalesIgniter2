<?php
/*
	Order Creator Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class orderCreator_admin_orders_default extends Extension_orderCreator
{

	public function __construct() {
		parent::__construct();
	}

	public function load() {
		if ($this->isEnabled() === false) {
			return;
		}

		EventManager::attachEvents(array(
			'OrdersGridButtonsBeforeAdd'
		), null, $this);
	}

	public function OrdersGridButtonsBeforeAdd(&$gridButtons) {
		$gridButtons[] = htmlBase::newElement('button')
			->usePreset('new')
			->setText(sysLanguage::get('TEXT_NEW_ORDER'))
			->addClass('createButton')
			->setHref(itw_app_link('appExt=orderCreator', 'default', 'new'));

		$gridButtons[] = htmlBase::newElement('button')
			->usePreset('edit')
			->setText(sysLanguage::get('TEXT_EDIT_ORDER'))
			->addClass('editButton')
			->disable();
	}
}

?>