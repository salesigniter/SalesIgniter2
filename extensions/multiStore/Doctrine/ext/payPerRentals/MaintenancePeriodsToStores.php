<?php
/*
	Multi Stores Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class MaintenancePeriodsToStores extends Doctrine_Record {

	public function setUp(){
		parent::setUp();
		$this->setUpParent();
		$this->hasOne('Stores', array(
				'local' => 'stores_id',
				'foreign' => 'stores_id'
			));

		$this->hasOne('PayPerRentalMaintenancePeriods', array(
				'local' => 'maintenance_period_id',
				'foreign' => 'maintenance_period_id',
				'cascade' => array('delete')
			));
	}
	
	public function setUpParent(){
		$PayPerRentalMaintenancePeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->getRecordInstance();
		$Stores = Doctrine_Core::getTable('Stores')->getRecordInstance();

		$PayPerRentalMaintenancePeriods->hasMany('MaintenancePeriodsToStores', array(
			'local'   => 'maintenance_period_id',
			'foreign' => 'maintenance_period_id',
			'cascade' => array('delete')
		));
		
		$Stores->hasMany('MaintenancePeriodsToStores', array(
			'local'   => 'stores_id',
			'foreign' => 'stores_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition(){
		$this->setTableName('maintenance_periods_to_stores');
		
		$this->hasColumn('maintenance_period_to_stores_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'autoincrement' => true
		));

		$this->hasColumn('maintenance_period_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('stores_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));

		$this->hasColumn('assign_to', 'string', null, array(
				'type'          => 'string',
				'length'        => null,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));
	}
}