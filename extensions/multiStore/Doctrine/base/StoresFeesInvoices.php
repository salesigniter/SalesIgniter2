<?php
/*
	Multi Stores Extension Version 1.1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class StoresFeesInvoices extends Doctrine_Record {

	public function setUp(){
		parent::setUp();

		$this->hasOne('Stores', array(
				'local' => 'stores_id',
				'foreign' => 'stores_id'
			));
	}

	public function setTableDefinition(){
		$this->setTableName('stores_fees_invoices');

		$this->hasColumn('stores_id', 'integer', 4);
		$this->hasColumn('paid', 'integer', 1);
		$this->hasColumn('date_added', 'string', 255);
		$this->hasColumn('date_paid', 'string', 255);
		
		$this->hasColumn('fee_royalty', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_royalty_discount', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_management', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_management_discount', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_marketing', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_marketing_discount', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_labor', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_labor_discount', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_parts', 'decimal', 15, array(
				'scale' => 4
			));
			
		$this->hasColumn('fee_parts_discount', 'decimal', 15, array(
				'scale' => 4
			));
			
	}
}