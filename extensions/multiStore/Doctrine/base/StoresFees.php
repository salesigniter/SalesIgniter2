<?php
/*
	Multi Stores Extension Version 1.1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class StoresFees extends Doctrine_Record {

	public function setUp(){
		parent::setUp();

		$this->hasOne('Stores', array(
				'local' => 'stores_id',
				'foreign' => 'stores_id'
			));
	}

	public function setTableDefinition(){
		$this->setTableName('stores_fees');

		$this->hasColumn('stores_id', 'integer', 4, array(
				'type'          => 'integer',
				'length'        => 4,
				'unsigned'      => 0,
				'primary'       => true,
				'notnull'       => true,
				'autoincrement' => true,
			));

		$this->hasColumn('fee_royalty', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_management', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_marketing', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_labor', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_parts', 'decimal', 15, array(
				'scale' => 4
			));
	}
}