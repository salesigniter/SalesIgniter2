<?php
/*
	Customers Custom Fields Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class CustomersCustomFields extends Doctrine_Record {
	
	public function setUp(){
		$this->hasMany('CustomersCustomFieldsDescription as Description', array(
			'local' => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));
		
		$this->hasMany('CustomersCustomFieldsOptionsToFields as Options', array(
			'local'   => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));
		
		$this->hasMany('CustomersCustomFieldsToGroups as Groups', array(
			'local'   => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));
		
		$this->hasMany('CustomersCustomFieldsToCustomers as Customers', array(
			'local' => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));
	}
	
	public function setTableDefinition(){
		$this->setTableName('customers_custom_fields');
		
		$this->hasColumn('field_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => true,
		));
		
		$this->hasColumn('field_key', 'string', 255, array(
			'type' => 'string',
			'length' => 255,
			'fixed' => false,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));
		
		$this->hasColumn('show_on_site', 'integer', 1, array(
			'type' => 'integer',
			'length' => 1,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('show_on_listing', 'integer', 1, array(
			'type' => 'integer',
			'length' => 1,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));

		$this->hasColumn('show_name_on_listing', 'integer', 1, array(
			'type' => 'integer',
			'length' => 1,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('show_on_labels', 'integer', 1, array(
			'type' => 'integer',
			'length' => 1,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('show_on_tab', 'integer', 1, array(
			'type' => 'integer',
			'length' => 1,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));		
		
		$this->hasColumn('labels_max_chars', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('input_type', 'string', 12, array(
			'type' => 'string',
			'length' => 12,
			'fixed' => false,
			'primary' => false,
			'default' => 'text',
			'notnull' => true,
			'autoincrement' => false,
		));

		$this->hasColumn('include_in_search', 'integer', 1, array(
			'type' => 'integer',
			'length' => 1,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
	}
}