<?php
/*
	SMS Notify Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class SmsCarriers extends Doctrine_Record {

	public function setUp(){
		parent::setUp();
	}

	public function setTableDefinition(){
		$this->setTableName('sms_carriers');

		$this->hasColumn('carrier_id', 'integer', 4, array(
			'type'          => 'integer',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => true,
			'notnull'       => true,
			'autoincrement' => true,
		));

		$this->hasColumn('carrier_name', 'string', 128);
		$this->hasColumn('carrier_domain', 'string', 128);
		$this->hasColumn('message_max_length', 'integer', 2);

		$this->hasColumn('works_yes', 'integer', 2, array(
			'type'          => 'integer',
			'default'       => '0',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));

		$this->hasColumn('works_no', 'integer', 2, array(
			'type'          => 'integer',
			'default'       => '0',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));

		$this->hasColumn('works_partial', 'integer', 2, array(
			'type'          => 'integer',
			'default'       => '0',
			'length'        => 4,
			'unsigned'      => 0,
			'primary'       => false,
			'notnull'       => true,
			'autoincrement' => false,
		));
	}
}