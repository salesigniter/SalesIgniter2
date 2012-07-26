<?php
/*
	Products Inventory Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsInventoryItems extends Doctrine_Record {

	public function setUp(){
		parent::setUp();
		$this->setAttribute(Doctrine::ATTR_COLL_KEY, 'item_status');

		$this->hasOne('Products as Product', array(
			'local'   => 'products_id',
			'foreign' => 'products_id'
		));

		$this->hasOne('ProductsPurchaseTypes as PurchaseType', array(
			'local'   => 'purchase_type_id',
			'foreign' => 'purchase_type_id'
		));

		$this->hasMany('ProductsSerialNumbers as Serials', array(
			'refClass' => 'ProductsSerialNumbersToProductsInventoryItems',
			'local' => 'inventory_item_id',
			'foreign'   => 'serial_number_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition(){
		$this->setTableName('products_inventory_items');

		$this->hasColumn('products_id', 'integer', 4);
		$this->hasColumn('purchase_type_id', 'integer', 4);
		$this->hasColumn('item_total', 'integer', 4);
		$this->hasColumn('item_status', 'integer', 4, array(
			'default' => '0'
		));
	}
}