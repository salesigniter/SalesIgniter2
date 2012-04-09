<?php
/*
Pay Per Rental Products Extension Version 1

I.T. Web Experts, Rental Store v2
http://www.itwebexperts.com

Copyright (c) 2009 I.T. Web Experts

This script and it's source is not redistributable
*/

class PayPerRentalMaintenance extends Doctrine_Record {
	
	public function setUp(){
		$this->setUpParent();
		$this->hasOne('ProductsInventoryBarcodes', array(
				'local'   => 'barcode_id',
				'foreign' => 'barcode_id'
		));
		$this->hasOne('Admin', array(
				'local'   => 'admin_id',
				'foreign' => 'admin_id'
		));

	}

	public function setUpParent(){

		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->getRecordInstance();
		$Admin = Doctrine_Core::getTable('Admin')->getRecordInstance();
		$ProductsInventoryBarcodes->hasMany('PayPerRentalMaintenance', array(
				'local'   => 'barcode_id',
				'foreign' => 'barcode_id'
			));
		$Admin->hasMany('PayPerRentalMaintenance', array(
				'local'   => 'admin_id',
				'foreign' => 'admin_id'
			));
	}

	
	public function setTableDefinition(){
		$this->setTableName('pay_per_rental_maintenance');
		
		$this->hasColumn('pay_per_rental_maintenance_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => true,
		));

        $this->hasColumn('barcode_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));

        $this->hasColumn('admin_id', 'integer', 4, array(
		        'type' => 'integer',
		        'length' => 4,
		        'unsigned' => 0,
		        'primary' => false,
		        'notnull' => false,
		        'autoincrement' => false,
	    ));

        $this->hasColumn('maintenance_date', 'datetime', null, array(
		        'type'          => 'datetime',
		        'primary'       => false,
		        'notnull'       => true,
		        'autoincrement' => false
	    ));

        $this->hasColumn('type', 'integer', 4, array(
		        'type' => 'integer',
		        'length' => 4,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => 0,
		        'notnull' => false,
		        'autoincrement' => false,
	    ));

        $this->hasColumn('cond', 'integer', 1, array(
		        'type' => 'integer',
		        'length' => 1,
		        'unsigned' => 0,
		        'default' => 0,
		        'primary' => false,
		        'notnull' => false,
		        'autoincrement' => false,
	    ));


        $this->hasColumn('comments', 'string', 999, array(
		'type' => 'string',
		'length' => 999,
		'fixed' => false,
		'primary' => false,
		'default' => '',
		'notnull' => true,
		'autoincrement' => false,
		));
	}
}