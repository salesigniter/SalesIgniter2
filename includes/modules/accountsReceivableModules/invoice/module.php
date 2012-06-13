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
 * Accounts receivable invoice module
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivableModuleInvoice extends AccountsReceivableModule
{

	/**
	 *
	 */
	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Invoice');
		$this->setDescription('Invoice');

		$this->init('invoice');
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getButtonText($type){
		$return = 'Unknown Button Type';
		if ($type == 'new'){
			$return = 'New Invoice';
		}
		elseif ($type == 'edit'){
			$return = 'Edit Invoice';
		}
		return $return;
	}
}