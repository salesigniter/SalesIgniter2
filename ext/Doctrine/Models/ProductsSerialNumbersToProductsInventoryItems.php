<?php
/*
	Products Inventory Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsSerialNumbersToProductsInventoryItems extends Doctrine_Record {

	public function setUp(){
		$this->hasOne('ProductsSerialNumbers as Serial', array(
			'local' => 'serial_number_id',
			'foreign' => 'id'
		));

		$this->hasOne('ProductsInventoryItems as InventoryItem', array(
			'local' => 'inventory_item_id',
			'foreign' => 'id'
		));
	}

	public function setTableDefinition(){
		$this->setTableName('products_serial_numbers_to_products_inventory_items');

		$this->hasColumn('serial_number_id', 'integer', 4, array(
			'primary' => true
		));
		$this->hasColumn('inventory_item_id', 'integer', 4, array(
			'primary' => true
		));
	}
}