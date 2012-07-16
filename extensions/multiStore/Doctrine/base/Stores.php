<?php
/*
	Multi Stores Extension Version 1.1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class Stores extends Doctrine_Record {

	public function setUp(){
		parent::setUp();

		$this->hasOne('StoreToStorePaymentSettings', array(
				'local' => 'stores_id',
				'foreign' => 'stores_id',
				'cascade' => array('delete')
			));

		$this->hasOne('StoresFees', array(
				'local' => 'stores_id',
				'foreign' => 'stores_id',
				'cascade' => array('delete')
			));

		$this->hasMany('StoreToStorePayments as PaidFrom', array(
				'local' => 'stores_id',
				'foreign' => 'from_store_id',
				'cascade' => array('delete')
			));

		$this->hasMany('StoreToStorePayments as PaidTo', array(
				'local' => 'stores_id',
				'foreign' => 'to_store_id',
				'cascade' => array('delete')
			));
	}

	public function preSave($event)
	{
		$this->stores_data = json_encode($this->stores_data);
	}

	public function preHydrate($event)
	{
		$data = $event->data;
		if (isset($data['stores_data'])){
			$data['stores_data'] = json_decode($data['stores_data'], true);
		}
		$event->data = $data;
	}

	public function setTableDefinition(){
		$this->setTableName('stores');

		$this->hasColumn('stores_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'notnull'       => true,
			'autoincrement' => true,
		));

		$this->hasColumn('stores_name', 'string', 128, array(
			'type'          => 'string',
			'length'        => 128,
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));

		$this->hasColumn('stores_domain', 'string', 128, array(
			'type'          => 'string',
			'length'        => 128,
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));

		$this->hasColumn('stores_ssl_domain', 'string', 128, array(
			'type'          => 'string',
			'length'        => 128,
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));

		$this->hasColumn('stores_data', 'string', 999, array(
			'default' => json_decode('', true)
		));

		$this->hasColumn('is_default', 'integer', 1, array(
				'type' => 'integer',
				'length' => 1,
				'unsigned' => 0,
				'default' => 0,
				'primary' => false,
				'autoincrement' => false
		));
	}
}