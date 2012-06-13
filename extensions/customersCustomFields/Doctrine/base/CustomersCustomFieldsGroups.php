<?php
/*
	Customers Custom Fields Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class CustomersCustomFieldsGroups extends Doctrine_Record {
	
	public function setUp(){
		$this->hasMany('CustomersCustomFieldsToGroups as Fields', array(
			'local'   => 'group_id',
			'foreign' => 'group_id',
			'orderBy' => 'sort_order',
			'cascade' => array('delete')
		));
	}
	
	public function setTableDefinition(){
		$this->setTableName('customers_custom_fields_groups');
		
		$this->hasColumn('group_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => true,
		));
		
		$this->hasColumn('group_name', 'string', 64, array(
			'type' => 'string',
			'length' => 64,
			'fixed' => false,
			'primary' => false,
			'notnull' => true,
			'autoincrement' => false,
		));
	}
}