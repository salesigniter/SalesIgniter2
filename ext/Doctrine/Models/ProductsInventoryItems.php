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

		$this->hasOne('ProductsInventory as Inventory', array(
			'local'   => 'inventory_id',
			'foreign' => 'inventory_id'
		));

		$this->hasMany('ProductsInventoryItemsComments as Comments', array(
			'local'   => 'id',
			'foreign' => 'item_id',
			'cascade' => array('delete')
		));

		$this->hasMany('ProductsInventoryItemsSerials as Serials', array(
			'local'   => 'id',
			'foreign' => 'item_id',
			'cascade' => array('delete')
		));

		$this->hasOne('ProductsInventoryItemsToProductsPurchaseTypes as PurchaseType', array(
			'local'   => 'id',
			'foreign' => 'item_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition(){
		$this->setTableName('products_inventory_items');

		$this->hasColumn('inventory_id', 'integer', 4);
		$this->hasColumn('item_total', 'integer', 4);
		$this->hasColumn('item_status', 'integer', 4, array(
			'default' => '0'
		));
	}
}