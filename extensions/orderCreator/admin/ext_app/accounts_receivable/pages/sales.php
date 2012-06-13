<?php
/*
	Order Creator Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class orderCreator_admin_accounts_receivable_sales extends Extension_orderCreator
{

	public function __construct() {
		parent::__construct();
	}

	public function load() {
		if ($this->isEnabled() === false) {
			return;
		}

		EventManager::attachEvents(array(
			'SalesGridButtonsBeforeAdd'
		), null, $this);
	}

	public function SalesGridButtonsBeforeAdd(&$gridButtons) {
		if (isset($_GET['sale_module'])){
			$Module = AccountsReceivableModules::getModule($_GET['sale_module']);
			if ($Module){
				$NewButtonText = $Module->getButtonText('new');
				$EditButtonText = $Module->getButtonText('edit');

				$gridButtons[] = htmlBase::newElement('button')
					->usePreset('new')
					->setText($NewButtonText)
					->addClass('createButton')
					->setHref(itw_app_link('appExt=orderCreator&sale_module=' . $Module->getCode(), 'default', 'new'));

				$gridButtons[] = htmlBase::newElement('button')
					->usePreset('edit')
					->setText($EditButtonText)
					->addClass('editButton')
					->disable();
			}
		}
	}
}

?>