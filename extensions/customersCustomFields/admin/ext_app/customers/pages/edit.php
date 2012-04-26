<?php
/*
	Products Custom Fields Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class customersCustomFields_admin_customers_edit extends Extension_customersCustomFields {

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
		$table = htmlBase::newElement('table')
		->setCellPadding(3)
		->setCellSpacing(0);

		$data = array();
		if ($Customers->Fields && $Customers->Fields->count() > 0){
			foreach($Customers->Fields as $fInfo){
				$data[$fInfo->field_id] = $fInfo->value;
			}
		}

		$Groups = Doctrine_Query::create()
		->from('CustomersCustomFieldsGroups')
		->orderBy('group_name')
		->execute();
		if ($Groups){
			foreach($Groups as $Group){
				$fieldset = '<fieldset>
				<legend>' . $Group->group_name . '</legend>
				<table>';
				foreach($Group->Fields as $fInfo){
					$fieldset .= '<tr>
					<td>' . $fInfo->Field->Description[Session::get('languages_id')]->field_name . ':</td>
					<td><input type="text" name="custom_fields[' . $fInfo->field_id . ']" value="' . (isset($data[$fInfo->field_id]) ? $data[$fInfo->field_id] : '') . '"></td>
					</tr>';
				}
				$fieldset .= '</table></fieldset>';
				$table->addBodyRow(array(
					'columns' => array(
						array('text' => $fieldset)
					)
				));
			}
		}
		return $table->draw();
	}
}
?>