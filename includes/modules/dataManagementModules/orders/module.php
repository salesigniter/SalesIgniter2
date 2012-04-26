<?php
class DataManagementModuleOrders extends DataManagementModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Order Data Management');
		$this->setDescription('Import And Export Orders Using This Module');

		$this->init(
			'orders',
			true,
			__DIR__
		);
	}

	public function runImport(){

	}

	public function runExport(){

	}
}
