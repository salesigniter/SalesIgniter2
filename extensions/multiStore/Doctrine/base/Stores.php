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

/* Auto Upgrade ( Version 1.0 to 1.1 ) --BEGIN-- */
		$this->hasColumn('stores_owner', 'string', 128, array(
			'type'          => 'string',
			'length'        => 128,
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));
/* Auto Upgrade ( Version 1.0 to 1.1 ) --END-- */

		$this->hasColumn('stores_name', 'string', 128, array(
			'type'          => 'string',
			'length'        => 128,
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('stores_email', 'string', 128, array(
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

		$this->hasColumn('stores_template', 'string', 128, array(
				'type'          => 'string',
				'length'        => 128,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('stores_street_address', 'string', 999, array(
				'type'          => 'string',
				'length'        => 999
			));

		$this->hasColumn('stores_postcode', 'string', 12, array(
				'type'          => 'string',
				'length'        => 12
			));

		$this->hasColumn('google_key', 'string', 255, array(
				'type'          => 'string',
				'length'        => 255,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('stores_reg_number', 'string', 255, array(
				'type'          => 'string',
				'length'        => 255,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));
		$this->hasColumn('stores_vat_number', 'string', 255, array(
				'type'          => 'string',
				'length'        => 255,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));
	}
}