<?php
class ProductTypeMembership extends ModuleBase
{

	private $_moduleCode = 'membership';

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Membership Product Type');
		$this->setDescription('Membership Product Type');

		$this->init($this->_moduleCode);
	}

	public function init($forceEnable = false) {
		$this->import(new Installable);

		$this->setModuleType('productType');
		parent::init($this->_moduleCode, $forceEnable);
	}
}
