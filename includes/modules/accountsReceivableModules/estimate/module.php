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
 * Accounts receivable estimate module
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivableModuleEstimate extends AccountsReceivableModule
{

	/**
	 *
	 */
	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Estimate');
		$this->setDescription('Estimate');

		$this->init('estimate');
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getButtonText($type){
		$return = 'Unknown Button Type';
		if ($type == 'new'){
			$return = 'New Estimate';
		}
		elseif ($type == 'edit'){
			$return = 'Edit Estimate';
		}
		return $return;
	}
}
