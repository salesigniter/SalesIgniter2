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
		$this->hasOne('PayPerRentalMaintenance', array(
				'local'   => 'pay_per_rental_maintenance_id',
				'foreign' => 'pay_per_rental_maintenance_id'
		));
		$this->hasOne('Admin', array(
				'local'   => 'admin_id',
				'foreign' => 'admin_id'
		));
	}

	public function setUpParent(){

		$PayPerRentalMaintenance = Doctrine_Core::getTable('PayPerRentalMaintenance')->getRecordInstance();
		$Admin = Doctrine_Core::getTable('Admin')->getRecordInstance();
		$PayPerRentalMaintenance->hasMany('PayPerRentalMaintenanceRepairs', array(
				'local'   => 'pay_per_rental_maintenance_id',
				'foreign' => 'pay_per_rental_maintenance_id'
		));
		$Admin->hasMany('PayPerRentalMaintenance', array(
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

        $this->hasColumn('pay_per_rental_maintenance_id', 'integer', 4, array(
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
		        'scale' => 4,
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