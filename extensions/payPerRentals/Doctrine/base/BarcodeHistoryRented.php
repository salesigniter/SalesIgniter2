<?php
/*
Pay Per Rental Products Extension Version 1

I.T. Web Experts, Rental Store v2
http://www.itwebexperts.com

Copyright (c) 2009 I.T. Web Experts

This script and it's source is not redistributable
*/

class BarcodeHistoryRented extends Doctrine_Record {
	
	public function setUp(){
		$this->setUpParent();
		$this->hasOne('ProductsInventoryBarcodes', array(
				'local'   => 'barcode_id',
				'foreign' => 'barcode_id'
		));
		$this->hasOne('PayPerRentalMaintenancePeriods', array(
				'local'   => 'last_maintenance_type',
				'foreign' => 'maintenance_period_id'
		));

	}

	public function setUpParent(){

		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->getRecordInstance();

		$PayPerRentalMaintenancePeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->getRecordInstance();

		$ProductsInventoryBarcodes->hasMany('BarcodeHistoryRented', array(
				'local'   => 'barcode_id',
				'foreign' => 'barcode_id'
		));

		$PayPerRentalMaintenancePeriods->hasMany('BarcodeHistoryRented', array(
				'local'   => 'maintenance_period_id',
				'foreign' => 'last_maintenance_type'
		));
	}

	
	public function setTableDefinition(){
		$this->setTableName('barcode_history_rented');


        $this->hasColumn('barcode_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'notnull' => false,
			'autoincrement' => false,
		));

        $this->hasColumn('number_rents', 'integer', 4, array(
		        'type' => 'integer',
		        'length' => 4,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => 0,
		        'notnull' => false,
		        'autoincrement' => false,
	    ));
        $this->hasColumn('last_maintenance_date', 'datetime', null, array(
		        'type'          => 'datetime',
		        'primary'       => false,
		        'notnull'       => true,
		        'autoincrement' => false
	    ));

        $this->hasColumn('last_maintenance_type', 'integer', 4, array(
		        'type' => 'integer',
		        'length' => 4,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => 0,
		        'notnull' => false,
		        'autoincrement' => false,
	    ));

        $this->hasColumn('current_maintenance_type', 'integer', 4, array(
		        'type' => 'integer',
		        'length' => 4,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => 0,
		        'notnull' => false,
		        'autoincrement' => false,
	     ));

        $this->hasColumn('current_maintenance_cond', 'integer', 1, array(
		        'type' => 'integer',
		        'length' => 1,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => 0,
		        'notnull' => false,
		        'autoincrement' => false,
	    ));

        $this->hasColumn('last_maintenance_cond', 'integer', 1, array(
		        'type' => 'integer',
		        'length' => 1,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => 0,
		        'notnull' => false,
		        'autoincrement' => false,
	        ));
        $this->hasColumn('current_maintenance_comments', 'string', 999, array(
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