<?php
/*
	Inventory Centers Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class PayPerRentalMaintenancePeriods extends Doctrine_Record {
	
	public function setUp(){
	}

	public function setUpParent(){
	}
	 
	public function setTableDefinition(){
		$this->setTableName('pay_per_rental_maintenance_periods');
		
		$this->hasColumn('maintenance_period_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'autoincrement' => true,
		));

		$this->hasColumn('maintenance_period_name', 'string', 128, array(
			'type'          => 'string',
			'length'        => 128,
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));

		$this->hasColumn('maintenance_period_start_date', 'datetime', null, array(
				'type'          => 'datetime',
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false
		));

		$this->hasColumn('maintenance_period_description', 'string', null, array(
				'type'          => 'string',
				'length'        => null,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('before_send', 'integer', 1, array(
				'type'          => 'integer',
				'length'        => 1,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('hours_before_send', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('after_return', 'integer', 1, array(
				'type'          => 'integer',
				'length'        => 1,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('hours_after_return', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('quarantine_until_completed', 'integer', 1, array(
				'type'          => 'integer',
				'length'        => 1,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('is_repair', 'integer', 1, array(
				'type'          => 'integer',
				'length'        => 1,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('show_number_days', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('show_number_rentals', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));

		$this->hasColumn('quarantine_number_days', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('quarantine_number_rentals', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
			));

		$this->hasColumn('email_admin', 'integer', 1, array(
				'type'          => 'integer',
				'length'        => 1,
				'fixed'         => false,
				'primary'       => false,
				'notnull'       => true,
				'autoincrement' => false,
		));


	}
}