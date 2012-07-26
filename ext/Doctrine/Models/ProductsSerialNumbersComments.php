<?php
/*
	Products Inventory Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsSerialNumbersComments extends Doctrine_Record {

	public function setUp(){
		parent::setUp();

		$this->hasOne('ProductsSerialNumbers as Serial', array(
			'local'   => 'serial_number_id',
			'foreign' => 'id'
		));
	}

	public function setTableDefinition(){
		$this->setTableName('products_serial_numbers_comments');

		$this->hasColumn('serial_number_id', 'integer', 4);

		$this->hasColumn('comments', 'string', 999, array(
			'type'          => 'string',
			'length'        => 999,
			'primary'       => false,
			'default'       => null,
			'notnull'       => false,
			'autoincrement' => false,
		));
	}
}