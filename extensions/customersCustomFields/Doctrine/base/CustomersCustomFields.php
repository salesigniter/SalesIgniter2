<?php
/*
	Customers Custom Fields Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class CustomersCustomFields extends Doctrine_Record
{

	public function setUp()
	{
		$this->hasMany('CustomersCustomFieldsDescription as Description', array(
			'local'   => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));

		$this->hasMany('CustomersCustomFieldsOptions as Options', array(
			'local'   => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));

		$this->hasMany('CustomersCustomFieldsToGroups as Groups', array(
			'local'   => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));

		$this->hasMany('CustomersCustomFieldsToCustomers as Customers', array(
			'local'   => 'field_id',
			'foreign' => 'field_id',
			'cascade' => array('delete')
		));
	}

	public function preSave($event)
	{
		$this->field_data = json_encode($this->field_data);
	}

	public function preHydrate($event)
	{
		$data = $event->data;
		$data['field_data'] = json_decode($data['field_data']);
		$event->data = $data;
	}

	public function setTableDefinition()
	{
		$this->setTableName('customers_custom_fields');

		$this->hasColumn('field_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'autoincrement' => true,
		));

		$this->hasColumn('field_key', 'string', 255, array(
			'type'          => 'string',
			'length'        => 255,
			'fixed'         => false,
			'primary'       => false,
			'notnull'       => false,
			'autoincrement' => false,
		));

		$this->hasColumn('field_data', 'string', 999);
	}
}