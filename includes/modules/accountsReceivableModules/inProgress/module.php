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
 * Accounts receivable inProgress module
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivableModuleInProgress extends AccountsReceivableModule
{

	/**
	 *
	 */
	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Sale In Progress');
		$this->setDescription('Sale In Progress');

		$this->init('inProgress');

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
			$return = 'Start New Sale';
		}
		elseif ($type == 'edit'){
			$return = 'Edit Sale In Progress';
		}
		return $return;
	}

	/**
	 * @return bool
	 */
	public function OwnsSale() {
		return (!isset($_GET['sale_module']) || $_GET['sale_module'] == $this->getCode());
	}

	/**
	 * @return mixed|string
	 */
	public function getSaveAsButton(){
		return '';
	}

	/**
	 * @return mixed|string
	 */
	public function getSaveButton(){
		return '';
	}
}
