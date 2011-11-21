<?php
/*
	Inventory Centers Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class PayPerRentalMaintenanceRepairParts extends Doctrine_Record {
	
	public function setUp(){
		$this->setUpParent();

		$this->hasOne('PayPerRentalMaintenanceRepairs', array(
				'local' => 'pay_per_rental_maintenance_repairs_id',
				'foreign' => 'pay_per_rental_maintenance_repairs_id'
		));
	}

	public function setUpParent(){
		$PayPerRentalEvents = Doctrine::getTable('PayPerRentalMaintenanceRepairs')->getRecordInstance();

		$PayPerRentalEvents->hasMany('PayPerRentalMaintenanceRepairParts', array(
				'local' => 'pay_per_rental_maintenance_repairs_id',
				'foreign' => 'pay_per_rental_maintenance_repairs_id',
				'cascade' => array('delete')
			));
	}
	 
	public function setTableDefinition(){
		$this->setTableName('pay_per_rental_maintenance_repair_parts');
		
		$this->hasColumn('pay_per_rental_maintenance_repairs_parts_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'autoincrement' => true,
		));

		$this->hasColumn('pay_per_rental_maintenance_repairs_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));


		$this->hasColumn('part_name', 'string', 250, array(
				'type'          => 'string',
				'length'        => 250,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('part_price', 'decimal', 15, array(
				'type' => 'decimal',
				'length' => 15,
				'unsigned' => 0,
				'primary' => false,
				'default' => '0.0000',
				'notnull' => true,
				'autoincrement' => false,
				'scale' => 4,
		));





	}
}