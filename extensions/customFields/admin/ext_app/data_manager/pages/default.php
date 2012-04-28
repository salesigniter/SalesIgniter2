<?php
/*
	Products Custom Fields Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class customFields_admin_data_manager_default extends Extension_customFields {

	public function __construct(){
		parent::__construct();
	}
	
	public function load(){
		if ($this->isEnabled() === false) return;
		
		EventManager::attachEvents(array(
			'DataExportFullQueryBeforeExecute',
			'DataExportFullQueryFileLayoutHeader',
			'DataExportBeforeFileLineCommit',
			'DataImportBeforeSave'
		), null, $this);
	}
	
	public function DataImportBeforeSave(&$CurrentRow, &$Product){
		$FieldCheck = $CurrentRow->getColumnValue('v_custom_fields_group');
		if ($FieldCheck === null) return;
		
		if (!empty($FieldCheck)){
			$FieldsGroup = Doctrine_Query::create()
			->select('group_id')
			->from('ProductsCustomFieldsGroups')
			->where('group_name = ?', $FieldCheck)
			->fetchOne();
			if ($FieldsGroup){
				$Group = $FieldsGroup->toArray();
				$GroupsToProducts =& $Product->ProductsCustomFieldsGroupsToProducts;
				if (isset($GroupsToProducts->group_id)){
					if ($Group['group_id'] != $GroupsToProducts->group_id){
						$GroupsToProducts[0]->group_id = $Group['group_id'];
					}
				}else{
					$GroupsToProducts[0]->group_id = $Group['group_id'];
				}

				$FieldsToGroups = Doctrine_Query::create()
				->select('f2g.field_id, f.input_type')
				->from('ProductsCustomFieldsToGroups f2g')
				->leftJoin('f2g.ProductsCustomFields f')
				->where('f2g.group_id = ?', $Group['group_id'])
				->orderBy('f2g.sort_order')
				->execute();
				if ($FieldsToGroups){
					/*
					 * @todo: Is this really the best way?
					 */
					$Product->ProductsCustomFieldsToProducts->delete();
					$FieldsToProducts = $Product->ProductsCustomFieldsToProducts;
					$fieldCount = 1;
					$count = 0;
					foreach($FieldsToGroups->toArray(true) as $fInfo){
						$Value = $CurrentRow->getColumnValue('v_custom_field' . $fieldCount++);
						if ($Value !== false && $fInfo['field_id'] > 0){
							$FieldsToProducts[$count]->field_id = $fInfo['field_id'];
							$FieldsToProducts[$count]->value = $Value;
							$FieldsToProducts[$count]->field_type = $fInfo['ProductsCustomFields']['input_type'];
							$count++;
						}
					}
				}
			}
		}
	}
	
	public function DataExportFullQueryFileLayoutHeader(&$HeaderRow){
		$HeaderRow->addColumn('v_custom_fields_group');

		$mostFields = 0;
		$Qfields = Doctrine_Query::create()
		->select('count(field_id) as total')
		->from('ProductsCustomFieldsToGroups f2g')
		->groupBy('group_id')
		->execute();
		foreach($Qfields as $fTotal){
			if ($fTotal['total'] > $mostFields){
				$mostFields = $fTotal['total'];
			}
		}
			
		for($i=1; $i<$mostFields+1; $i++){
			$HeaderRow->addColumn('v_custom_field' . $i);
		}
	}
	
	public function DataExportFullQueryBeforeExecute(&$query){
		$query->leftJoin('p.ProductsCustomFieldsGroupsToProducts fg2p')
		->leftJoin('fg2p.ProductsCustomFieldsGroups fg')
		->addSelect('fg2p.group_id as v_custom_fields_group_id')
		->addSelect('fg.group_name as v_custom_fields_group');
	}
	
	public function DataExportBeforeFileLineCommit(&$CurrentRow, $Product){
		if ($Product->ProductsCustomFieldsGroupsToProducts && $Product->ProductsCustomFieldsGroupsToProducts->count() > 0){
			$Group = $Product->ProductsCustomFieldsGroupsToProducts->ProductsCustomFieldsGroups;
			$CurrentRow->addColumn(
				$Group->group_name,
				'v_custom_fields_group'
			);

			if ($Group->ProductsCustomFieldsToGroups && $Group->ProductsCustomFieldsToGroups->count() > 0){
				$fieldNum = 1;
				foreach($Group->ProductsCustomFieldsToGroups as $FieldToGroup){
					$Field = $FieldToGroup->ProductsCustomFields;
					$CurrentRow->addColumn(
						$Field->value,
						'v_custom_field' . $fieldNum ++
					);
				}
			}
		}
	}
}
?>