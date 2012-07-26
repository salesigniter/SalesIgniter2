<?php
/*
	Products Inventory Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsSerialNumbers extends Doctrine_Record {

	public function setUp(){
		parent::setUp();
		$this->setAttribute(Doctrine::ATTR_COLL_KEY, 'serial_number');

		$this->hasOne('ProductsInventoryItems as InventoryItem', array(
			'refClass' => 'ProductsSerialNumbersToProductsInventoryItems',
			'local'   => 'serial_number_id',
			'foreign' => 'inventory_item_id'
		));

		$this->hasMany('ProductsSerialNumbersComments as Comments', array(
			'local' => 'id',
			'foreign' => 'serial_number_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition(){
		$this->setTableName('products_serial_numbers');

		$this->hasColumn('serial_number', 'string', 255);
	}
}