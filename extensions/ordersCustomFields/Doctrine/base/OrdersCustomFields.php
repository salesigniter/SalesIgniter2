<?php
/*
	Orders Custom Fields Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class OrdersCustomFields extends Doctrine_Record {
	
	public function setUp(){
		$this->hasMany('OrdersCustomFieldsDescription as Description', array(
			'local' => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));
		
		$this->hasMany('OrdersCustomFieldsOptionsToFields as Options', array(
			'local'   => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));
		
		$this->hasMany('OrdersCustomFieldsToOrders as Orders', array(
			'local' => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));
	}
	
	public function setTableDefinition(){
		$this->setTableName('orders_custom_fields');
		
		$this->hasColumn('field_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => true,
		));

		$this->hasColumn('field_identifier', 'string', '255');
		
		$this->hasColumn('input_type', 'string', 16, array(
			'type' => 'string',
			'length' => 16,
			'fixed' => false,
			'primary' => false,
			'default' => 'text',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('input_required', 'integer', 1, array(
			'type' => 'integer',
			'length' => 1,
			'fixed' => false,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));

		$this->hasColumn('sort_order', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'fixed' => false,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
	}
}