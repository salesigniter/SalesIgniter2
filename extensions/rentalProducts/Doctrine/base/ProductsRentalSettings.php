<?php
/*
Rental Products Extension Version 1

I.T. Web Experts, Sales Igniter v1
http://www.itwebexperts.com

Copyright (c) 2011 I.T. Web Experts

This script and it's source is not redistributable
*/

class ProductsPurchaseTypesRentalSettings extends Doctrine_Record {
	
	public function setUp(){
		parent::setUp();

		$this->hasOne('ProductsPurchaseTypes', array(
			'local' => 'purchase_type_id',
			'foreign' => 'purchase_type_id'
		));
	}

	public function setTableDefinition(){
		$this->setTableName('products_putchase_types_rental_settings');
		
		$this->hasColumn('purchase_type_id', 'integer', 4);
		
		$this->hasColumn('rental_period', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));
	}
}