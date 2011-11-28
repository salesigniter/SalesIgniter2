<?php
/*
	Late Fees Extension Version 1.0

	Sales Ingiter E-Commerce System v2
	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class LateFees extends Doctrine_Record {

	public function setUp(){
		parent::setUp();

		$this->hasOne('Customers', array(
				'local' => 'customers_id',
				'foreign' => 'customers_id'
			));

		$this->hasOne('OrdersProducts', array(
				'local' => 'orders_products_id',
				'foreign' => 'orders_products_id'
			));
	}

	public function preInsert(){
		$this->date_added = date(DATE_RSS);
	}

	public function setTableDefinition(){
		$this->setTableName('late_fees');

		$this->hasColumn('fee_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'unsigned'      => 0,
				'primary'       => true,
				'notnull'       => true,
				'autoincrement' => true,
			));

		$this->hasColumn('fee_status', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'default'       => '0',
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('customers_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('orders_products_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('fee_amount', 'decimal', 15, array(
				'type'          => 'decimal',
				'length'        => 15,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('date_added', 'string', 128, array(
				'type'          => 'string',
				'length'        => 128
			));

		$this->hasColumn('date_paid', 'string', 128, array(
				'type'          => 'string',
				'length'        => 128
			));

		$this->hasColumn('comments', 'string', 999, array(
				'type'          => 'string',
				'length'        => 999
			));
	}
}