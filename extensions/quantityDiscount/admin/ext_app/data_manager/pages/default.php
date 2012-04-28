<?php
/*
	Quantity Discount Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class quantityDiscount_admin_data_manager_default extends Extension_quantityDiscount {

	public function __construct(){
		parent::__construct();
	}
	
	public function load(){
		global $appExtension;
		if ($this->isEnabled() === false) return;
		
		EventManager::attachEvents(array(
			'DataExportFullQueryBeforeExecute',
			'DataExportFullQueryFileLayoutHeader',
			'DataExportBeforeFileLineCommit',
			'DataImportBeforeSave',
			'DataImportProductLogBeforeExecute',
		), null, $this);
	}
	
	public function DataImportProductLogBeforeExecute(&$Product, &$productLogArr){
	}
	
	public function DataExportFullQueryBeforeExecute(&$query){
		$query->addSelect('(SELECT count(*) from ProductsQuantityDiscounts pqd where pqd.products_id = p.products_id) as quantityDiscounts');
	}
	
	public function DataExportFullQueryFileLayoutHeader(&$HeaderRow){
		for($i=1; $i<(sysConfig::get('EXTENSION_QUANTITY_DISCOUNT_LEVELS') + 1); $i++){
			$HeaderRow->addColumn('v_quantity_discount_' . $i . '_from');
			$HeaderRow->addColumn('v_quantity_discount_' . $i . '_to');
			$HeaderRow->addColumn('v_quantity_discount_' . $i . '_price');
		}
	}
	
	public function DataExportBeforeFileLineCommit(&$CurrentRow, $Product){
		if ($Product->ProductsQuantityDiscounts && $Product->ProductsQuantityDiscounts->count() > 0){
			$i=1;
			foreach($Product->ProductsQuantityDiscounts as $Discount){
				$CurrentRow->addColumn($Discount->quantity_from, 'v_quantity_discount_' . $i . '_from');
				$CurrentRow->addColumn($Discount->quantity_to, 'v_quantity_discount_' . $i . '_to');
				$CurrentRow->addColumn($Discount->price, 'v_quantity_discount_' . $i . '_price');
				$i++;
			}
			
			if ($i < (sysConfig::get('EXTENSION_QUANTITY_DISCOUNT_LEVELS') + 1)){
				for($j=$i; $j<(sysConfig::get('EXTENSION_QUANTITY_DISCOUNT_LEVELS') + 1); $j++){
					$CurrentRow->addColumn('0', 'v_quantity_discount_' . $j . '_from');
					$CurrentRow->addColumn('0', 'v_quantity_discount_' . $j . '_to');
					$CurrentRow->addColumn('0.000', 'v_quantity_discount_' . $j . '_price');
				}
			}
		}
	}
	
	public function DataImportBeforeSave(&$CurrentRow, &$Product){
		$Product->ProductsQuantityDiscounts->delete();
		$DiscountCheck = $CurrentRow->getColumnValue('v_quantity_discount_1_from');
		if ($DiscountCheck !== false){
			$qtyCount = 0;
			for($i=1; $i<(sysConfig::get('EXTENSION_QUANTITY_DISCOUNT_LEVELS') + 1); $i++){
				$qtyFrom = $CurrentRow->getColumnValue('v_quantity_discount_' . $i . '_from');
				$qtyTo = $CurrentRow->getColumnValue('v_quantity_discount_' . $i . '_to');
				$price = $CurrentRow->getColumnValue('v_quantity_discount_' . $i . '_price');
				if ($qtyFrom > 0 && $qtyTo > 0){
					$Product->ProductsQuantityDiscounts[$qtyCount]->quantity_from = $qtyFrom;
					$Product->ProductsQuantityDiscounts[$qtyCount]->quantity_to = $qtyTo;
					$Product->ProductsQuantityDiscounts[$qtyCount]->price = $price;
					$qtyCount++;
				}
			}
		}
	}
}
?>