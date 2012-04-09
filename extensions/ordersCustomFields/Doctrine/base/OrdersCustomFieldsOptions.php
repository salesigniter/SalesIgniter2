<?php
/*
	Orders Custom Fields Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class OrdersCustomFieldsOptions extends Doctrine_Record {
	
	public function setUp(){
		$this->hasMany('OrdersCustomFieldsOptionsDescription', array(
			'local'   => 'option_id',
			'foreign' => 'option_id',
			'cascade' => array('delete')
		));
		
		$this->hasMany('OrdersCustomFieldsOptionsToFields', array(
			'local'   => 'option_id',
			'foreign' => 'option_id',
			'cascade' => array('delete')
		));
	}
	
	public function setTableDefinition(){
		$this->setTableName('orders_custom_fields_options');
		
		$this->hasColumn('option_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => true,
		));
		
		$this->hasColumn('sort_order', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'notnull' => true,
			'autoincrement' => false,
		));
		$this->hasColumn('extra_data', 'string', 999);
	}
}