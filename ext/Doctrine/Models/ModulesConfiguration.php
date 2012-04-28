<?php
class ModulesConfiguration extends Doctrine_Record {
	public function setUp(){
		parent::setUp();
		$this->setAttribute(Doctrine::ATTR_COLL_KEY, 'configuration_key');
	}
	
	public function setTableDefinition(){
		$this->setTableName('modules_configuration');
		
		$this->hasColumn('configuration_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'autoincrement' => true
		));
		
		$this->hasColumn('modules_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'autoincrement' => false
		));
		
		$this->hasColumn('configuration_key', 'string', 64, array(
			'type'          => 'string',
			'length'        => 64,
			'fixed'         => false,
			'primary'       => false,
			'default'       => '',
			'notnull'       => true,
			'autoincrement' => false
		));
		
		$this->hasColumn('configuration_value', 'string', 999, array(
			'type'          => 'string',
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false
		));
	}
}