<?php
/*
	Products Inventory Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsInventoryItemsSerials extends Doctrine_Record {

	public function setUp(){
		parent::setUp();
		$this->setAttribute(Doctrine::ATTR_COLL_KEY, 'serial_number');

		$this->hasOne('ProductsInventoryItems as InventoryItem', array(
			'local'   => 'item_id',
			'foreign' => 'id'
		));

		$this->hasMany('ProductsInventoryItemsSerialsComments as Comments', array(
			'local'   => 'id',
			'foreign' => 'serial_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition(){
		$this->setTableName('products_inventory_items_serials');

		$this->hasColumn('item_id', 'integer', 4);
		$this->hasColumn('serial_number', 'string', 255);
	}
}