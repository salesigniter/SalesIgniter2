<?php
/*
	Products Inventory Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsInventoryItemsSerialsComments extends Doctrine_Record {

	public function preInsert($event){
		$this->date_added = date('Y-m-d');
	}

	public function preUpdate($event){
	}

	public function setTableDefinition(){
		$this->setTableName('products_inventory_items_serials_comments');

		$this->hasColumn('serial_id', 'integer', 4);

		$this->hasColumn('comments', 'string', 999, array(
			'type'          => 'string',
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false
		));

		$this->hasColumn('date_added', 'date', null, array(
			'type'          => 'date',
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false
		));
	}
}