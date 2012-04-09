<?php

/**
 * AddressBook
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class AddressBook extends Doctrine_Record {
	
	public function setUp(){
		$this->hasOne('Countries', array(
			'local' => 'entry_country_id',
			'foreign' => 'countries_id'
		));
	}
	
	public function setTableDefinition(){
		$this->setTableName('address_book');
		
		$this->hasColumn('address_book_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => true,
		));
		
		$this->hasColumn('customers_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_gender', 'string', 1, array(
			'type' => 'string',
			'length' => 1,
			'fixed' => true,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_company', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));

		$this->hasColumn('entry_dob', 'date');
		
		$this->hasColumn('entry_firstname', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_lastname', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_street_address', 'string', 64, array(
			'type' => 'string',
			'length' => 64,
			'fixed' => false,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_suburb', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_postcode', 'string', 10, array(
			'type' => 'string',
			'length' => 10,
			'fixed' => false,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_city', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_state', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));

		$this->hasColumn('entry_cif', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));

		$this->hasColumn('entry_vat', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));

		$this->hasColumn('entry_city_birth', 'string', 64, array(
				'type' => 'string',
				'length' => 64,
				'fixed' => false,
				'primary' => false,
				'notnull' => false,
				'autoincrement' => false,
		));
		
		$this->hasColumn('entry_country_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('entry_zone_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'default' => '0',
			'notnull' => true,
			'autoincrement' => false,
		));
	}
}