<?php
/*
	Products Inventory Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsInventory extends Doctrine_Record
{

	public function setUp()
	{
		parent::setUp();

		$this->hasOne('Products', array(
			'local'   => 'products_id',
			'foreign' => 'products_id',
			'cascade' => array('delete')
		));

		$this->hasMany('ProductsInventoryItems as Items', array(
			'local'   => 'inventory_id',
			'foreign' => 'inventory_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition()
	{
		$this->setTableName('products_inventory');

		$this->hasColumn('inventory_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'autoincrement' => true
		));

		$this->hasColumn('products_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => false,
			'default'       => '0',
			'notnull'       => true,
			'autoincrement' => false
		));
	}
}