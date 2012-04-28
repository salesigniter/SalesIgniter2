<?php
/*
	Prouct Attributes Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class attributes_admin_data_manager_default extends Extension_attributes {

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
	
	public function DataExportFullQueryBeforeExecute(&$query){
	}
	
	public function DataExportFullQueryFileLayoutHeader(&$HeaderRow){
		$mostAttributes = 0;
		$Qattributes = Doctrine_Query::create()
		->select('count(products_attributes_id) as total')
		->from('ProductsAttributes')
		->groupBy('products_id')
		->execute();
		foreach($Qattributes as $aTotal){
			if ($aTotal['total'] > $mostAttributes){
				$mostAttributes = $aTotal['total'];
			}
		}
			
		for($i=1; $i<$mostAttributes+1; $i++){
			$HeaderRow->addColumn('v_attribute_' . $i);
			$HeaderRow->addColumn('v_attribute_' . $i . '_image');
			$HeaderRow->addColumn('v_attribute_' . $i . '_views');
			$HeaderRow->addColumn('v_attribute_' . $i . '_price');
			$HeaderRow->addColumn('v_attribute_' . $i . '_sort');
		}
	}
	
	public function DataExportBeforeFileLineCommit(&$CurrentRow, $Product){
		$Attributes = $Product->ProductsAttributes;
		if ($Attributes){
			$lID = Session::get('languages_id');
			foreach($Attributes as $Attribute){
				if ($Attribute->ProductsOptionsGroups && isset($Attribute->ProductsOptions->ProductsOptionsDescription) && isset($Attribute->ProductsOptionsValues->ProductsOptionsValuesDescription)){
					$crumb = $Attribute->ProductsOptionsGroups->products_options_groups_name . '>' . $Attribute->ProductsOptions->ProductsOptionsDescription[$lID]->products_options_name . '>' . $Attribute->ProductsOptionsValues->ProductsOptionsValuesDescription[$lID]->products_options_values_name;

					$views = array();
					if ($Attribute->ProductsAttributesViews && $Attribute->ProductsAttributesViews->count() > 0){
						foreach($Attribute->ProductsAttributesViews as $viewInfo){
							$views[] = $viewInfo->view_name . ':' . $viewInfo->view_image;
						}
					}

					$realCount = $i+1;
					$CurrentRow->addColumn($crumb, 'v_attribute_' . $realCount);
					$CurrentRow->addColumn($Attribute->options_values_image, 'v_attribute_' . $realCount . '_image');
					$CurrentRow->addColumn(implode(';', $views), 'v_attribute_' . $realCount . '_views');
					$CurrentRow->addColumn($Attribute->options_values_price, 'v_attribute_' . $realCount . '_price');
					$CurrentRow->addColumn($Attribute->sort_order, 'v_attribute_' . $realCount . '_sort');
				}
			}
		}
	}
	
	public function DataImportBeforeSave(&$CurrentRow, &$Product){
		$ProductsAttributes =& $Product->ProductsAttributes;
		$ProductsAttributes->delete();
		$AttributeCheck = $CurrentRow->getColumnValue('v_attribute_1');
		if ($AttributeCheck !== false && is_null($AttributeCheck) === false){
			$end = false;
			$count = 1;
			while($end === false){
				$CurrentAttribute = $CurrentRow->getColumnValue('v_attribute_' . $count);
				if ($CurrentAttribute === false){
					$end = true;
					continue;
				}
				
				if (is_null($CurrentAttribute) === true){
					$count++;
					continue;
				}
				
				$crumb = explode('>', $CurrentAttribute);
				$image = $CurrentRow->getColumnValue('v_attribute_' . $count . '_image');
				$views = $CurrentRow->getColumnValue('v_attribute_' . $count . '_views', 0);
				$price = $CurrentRow->getColumnValue('v_attribute_' . $count . '_price', 0);
				$sort = $CurrentRow->getColumnValue('v_attribute_' . $count . '_sort', 0);
				
				$optionName = $crumb[0];
				$valueName = $crumb[1];
				if (sizeof($crumb) > 2){
					$groupName = $crumb[0];
					$optionName = $crumb[1];
					$valueName = $crumb[2];
				}
				
				$Query = Doctrine_Query::create()
				->select('o.products_options_id, v2o.products_options_values_id')
				->from('ProductsOptions o')
				->leftJoin('o.ProductsOptionsDescription od')
				->leftJoin('o.ProductsOptionsValuesToProductsOptions v2o')
				->leftJoin('v2o.ProductsOptionsValues ov')
				->leftJoin('ov.ProductsOptionsValuesDescription ovd')
				->where('od.products_options_name = ?', $optionName)
				->andWhere('ovd.products_options_values_name = ?', $valueName);
				if (isset($groupName)){
					$Query->addSelect('o2g.products_options_groups_id')
					->leftJoin('o.ProductsOptionsToProductsOptionsGroups o2g')
					->leftJoin('o2g.ProductsOptionsGroups og')
					->andWhere('og.products_options_groups_name = ?', $groupName);
				}
				
				$Result = $Query->fetchOne();
				if ($Result){
					$attribute = $Result->toArray();
					//print_r($attribute);exit;
					if (!isset($attributeCount)) $attributeCount = 0;
					
					$ProductsAttributes[$attributeCount]->groups_id = (isset($groupName) ? $attribute['ProductsOptionsToProductsOptionsGroups'][0]['products_options_groups_id'] : null);
					$ProductsAttributes[$attributeCount]->options_id = $attribute['products_options_id'];
					$ProductsAttributes[$attributeCount]->options_values_id = $attribute['ProductsOptionsValuesToProductsOptions'][0]['products_options_values_id'];
					$ProductsAttributes[$attributeCount]->options_values_image = $image;
					$ProductsAttributes[$attributeCount]->options_values_price = abs($price);
					$ProductsAttributes[$attributeCount]->price_prefix = ($price >= 0 ? '+' : '-');
					$ProductsAttributes[$attributeCount]->sort_order = $sort;
					
					if (!empty($views)){
						$parts = explode(';', $views);
						$ProductsAttributesViews =& $ProductsAttributes[$attributeCount]->ProductsAttributesViews;
						$ProductsAttributesViews->delete();
						foreach($parts as $i => $viewInfo){
							if (empty($viewInfo)) continue;
							
							$infoArr = explode(':', $viewInfo);
							$viewName = $infoArr[0];
							$viewImage = $infoArr[1];
							
							$ProductsAttributesViews[$i]->view_name = $viewName;
							$ProductsAttributesViews[$i]->view_image = $viewImage;
						}
					}
					$attributeCount++;
					unset($attribute);
				}
				$Query->free(true);
				$count++;
			}
		}
	}
}
?>