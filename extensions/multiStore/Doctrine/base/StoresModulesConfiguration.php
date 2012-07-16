<?php
class StoresModulesConfiguration extends Doctrine_Record
{

	public function setUp()
	{
		parent::setUp();
		$this->setAttribute(Doctrine::ATTR_COLL_KEY, 'configuration_key');
	}

	public function setTableDefinition()
	{
		$this->setTableName('stores_modules_configuration');

		$this->hasColumn('store_id', 'integer', 4);
		$this->hasColumn('modules_id', 'integer', 4);
		$this->hasColumn('configuration_key', 'string', 128);
		$this->hasColumn('configuration_value', 'string', 999);
	}
}