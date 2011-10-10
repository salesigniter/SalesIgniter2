<?php
/*
	Multi Stores Extension Version 1.1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class StoreToStorePayments extends Doctrine_Record {

	public function setUp(){
		parent::setUp();

		$this->hasOne('Stores as FromStore', array(
				'local' => 'from_store_id',
				'foreign' => 'stores_id'
			));

		$this->hasOne('Stores as ToStore', array(
				'local' => 'to_store_id',
				'foreign' => 'stores_id'
			));

		$OrdersProducts = Doctrine_Core::getTable('OrdersProducts')->getRecordInstance();
		$OrdersProducts->hasOne('StoreToStorePayments', array(
				'local' => 'orders_products_id',
				'foreign' => 'orders_products_id',
				'cascade' => array('delete')
			));
	}

	public function setTableDefinition(){
		$this->setTableName('store_to_store_payments');

		$this->hasColumn('payment_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'unsigned'      => 0,
				'primary'       => true,
				'notnull'       => true,
				'autoincrement' => true,
			));

		$this->hasColumn('orders_products_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'unsigned'      => 0,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('from_store_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'unsigned'      => 0,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('to_store_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'unsigned'      => 0,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('payment_amount', 'decimal', 15);

		$this->hasColumn('payment_status', 'integer', 1, array(
				'type'          => 'integer',
				'length'        => 1,
				'unsigned'      => 0,
				'default'       => '0',
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));
	}
}