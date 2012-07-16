<?php
/*
	Products Inventory Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsInventoryItemsToProductsPurchaseTypes extends Doctrine_Record
{

	public function setUp()
	{
		parent::setUp();

		$this->hasOne('ProductsInventoryItems as InventoryItem', array(
			'local'   => 'item_id',
			'foreign' => 'id'
		));

		$this->hasOne('ProductsPurchaseTypes as PurchaseTypeInfo', array(
			'local'   => 'purchase_type_id',
			'foreign' => 'purchase_type_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition()
	{
		$this->setTableName('products_inventory_items_to_products_purchase_types');

		$this->hasColumn('item_id', 'integer', 4);
		$this->hasColumn('purchase_type_id', 'integer', 4);
	}
}