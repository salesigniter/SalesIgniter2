<?php
/*
	Multi Stores Extension Version 1.1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class StoreToStorePaymentSettings extends Doctrine_Record {

	public function setUp(){
		parent::setUp();

		$this->hasOne('Stores', array(
				'local' => 'stores_id',
				'foreign' => 'stores_id'
			));
	}

	public function setTableDefinition(){
		$this->setTableName('store_to_store_payment_settings');

		$this->hasColumn('settings_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'unsigned'      => 0,
				'primary'       => true,
				'notnull'       => true,
				'autoincrement' => true,
			));

		$this->hasColumn('stores_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'unsigned'      => 0,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('use_global', 'integer', 1, array(
				'type'          => 'integer',
				'length'        => 1,
				'default'      => '1',
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('payment_settings', 'string', 999, array(
				'type'          => 'string',
				'length'        => 999
			));
	}
}