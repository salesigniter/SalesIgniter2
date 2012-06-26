<?php
/*
	Products Custom Fields Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class customersCustomFields_admin_customers_new extends Extension_customersCustomFields {

	public function __construct(){
		parent::__construct();
	}
	
	public function load(){
		if ($this->isEnabled() === false) return;
		
		EventManager::attachEvents(array(
			'CustomerInfoAddTableContainer'
		), null, $this);
	}
	
	public function CustomerInfoAddTableContainer(Customers &$Customers){
		$data = array();
		if ($Customers->Fields && $Customers->Fields->count() > 0){
			foreach($Customers->Fields as $fInfo){
				$data[$fInfo->field_id] = $fInfo->value;
			}
		}

		return $this->buildFieldsetBlock($data);
	}
}
?>