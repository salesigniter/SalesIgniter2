<?php
class ProductTypeMembership extends ProductTypeBase
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
}
