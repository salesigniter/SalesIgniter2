<?php
/*
	Products Inventory Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsInventoryQuantity extends Doctrine_Record
{

	public function setUp() {
		parent::setUp();
		$this->setAttribute(Doctrine_Core::ATTR_COLL_KEY, 'quantity_status_id');

		$this->hasOne('ProductsInventory', array(
				'local' => 'inventory_id',
				'foreign' => 'inventory_id'
			));

		$this->hasMany('ProductsInventoryQuantityComments as Comments', array(
			'local' => 'quantity_id',
			'foreign' => 'quantity_id',
			'cascade' => array('delete')
		));

		$this->hasOne('SystemStatuses as Status', array(
			'local' => 'quantity_status_id',
			'foreign' => 'status_id'
		));
	}

	public function setTableDefinition() {
		$this->setTableName('products_inventory_quantity');

		$this->hasColumn('inventory_id', 'integer', 4);
		$this->hasColumn('quantity_number', 'integer', 4);
		$this->hasColumn('quantity_status_id', 'integer', 4);
	}
}