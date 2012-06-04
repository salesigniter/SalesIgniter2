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

/**
 * Accounts receivable order module
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivableModuleOrder extends AccountsReceivableModule
{

	/**
	 *
	 */
	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Order');
		$this->setDescription('Order');

		$this->init('order');

		$this->canShowDetails(($this->getConfigData('CAN_SHOW_DETAILS') == 'True'));
		$this->canCancel(($this->getConfigData('CAN_CANCEL') == 'True'));
		$this->canPrint(($this->getConfigData('CAN_PRINT') == 'True'));
		$this->canExport(($this->getConfigData('CAN_EXPORT') == 'True'));
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getButtonText($type){
		$return = 'Unknown Button Type';
		if ($type == 'new'){
			$return = 'New Order';
		}
		elseif ($type == 'edit'){
			$return = 'Edit Order';
		}
		return $return;
	}
}