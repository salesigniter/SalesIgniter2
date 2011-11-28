<?php
/*
Pay Per Rental Products Extension Version 1

I.T. Web Experts, Rental Store v2
http://www.itwebexperts.com

Copyright (c) 2009 I.T. Web Experts

This script and it's source is not redistributable
*/

class PayPerRentalMaintenanceRepairs extends Doctrine_Record {
	
	public function setUp(){
		$this->setUpParent();
		$this->hasOne('PayPerRentalMaintenancePeriods', array(
				'local'   => 'maintenance_period_id',
				'foreign' => 'maintenance_period_id'
		));
		$this->hasOne('Admin', array(
				'local'   => 'admin_id',
				'foreign' => 'admin_id'
		));
		$this->hasOne('ProductsInventoryBarcodes', array(
				'local'   => 'barcode_id',
				'foreign' => 'barcode_id'
		));
	}

	public function setUpParent(){

		$PayPerRentalMaintenancePeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->getRecordInstance();
		$Admin = Doctrine_Core::getTable('Admin')->getRecordInstance();
		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->getRecordInstance();

		$ProductsInventoryBarcodes->hasMany('PayPerRentalMaintenanceRepairs', array(
				'local'   => 'barcode_id',
				'foreign' => 'barcode_id'
			));

		$PayPerRentalMaintenancePeriods->hasMany('PayPerRentalMaintenanceRepairs', array(
				'local'   => 'maintenance_period_id',
				'foreign' => 'maintenance_period_id'
		));
		$Admin->hasMany('PayPerRentalMaintenanceRepairs', array(
				'local'   => 'admin_id',
				'foreign' => 'admin_id'
		));
	}

	
	public function setTableDefinition(){
		$this->setTableName('pay_per_rental_maintenance_repairs');
		
		$this->hasColumn('pay_per_rental_maintenance_repairs_id', 'integer', 4, array(
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

        $this->hasColumn('maintenance_date', 'datetime', null, array(
		        'type'          => 'datetime',
		        'primary'       => false,
		        'notnull'       => true,
		        'autoincrement' => false
	        ));

        $this->hasColumn('maintenance_period_id', 'integer', 4, array(
		        'type' => 'integer',
		        'length' => 4,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => 0,
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

        $this->hasColumn('repair_date', 'datetime', null, array(
		        'type'          => 'datetime',
		        'primary'       => false,
		        'notnull'       => true,
		        'autoincrement' => false
	    ));

        $this->hasColumn('price', 'decimal', 15, array(
		        'type' => 'decimal',
		        'length' => 15,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => '0.0000',
		        'notnull' => true,
		        'autoincrement' => false,
		        'scale' => false,
	    ));


        $this->hasColumn('comments', 'string', null, array(
		'type' => 'string',
		'length' => null,
		'fixed' => false,
		'primary' => false,
		'default' => '',
		'notnull' => true,
		'autoincrement' => false,
		));
	}
}