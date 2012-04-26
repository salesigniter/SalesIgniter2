<?php
class DataManagementModuleProductInventory extends DataManagementModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Product Inventory Data Management');
		$this->setDescription('Import And Export Product Inventory Using This Module');

		$this->init(
			'productInventory',
			true,
			__DIR__
		);
	}

	public function runImport(){

	}

	public function runExport(){

	}
}
