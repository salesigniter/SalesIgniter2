<?php
/*
	Late Fees Extension Version 1.0

	Sales Ingiter E-Commerce System v2
	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class Extension_lateFees extends ExtensionBase {

	public function __construct(){
		parent::__construct('lateFees');
	}

	public function init(){
		global $App, $appExtension, $Template;
		if ($this->enabled === false) return;

		EventManager::attachEvents(array(
				'AdminDeleteCustomerCheckAllowed',
				'OrderCreatorLoadCustomerInfoResponse'
			), null, $this);
	}

	public function voidStatusId(){
		return '1';
	}

	public function paidStatusId(){
		return '2';
	}

	public function openStatusId(){
		return '0';
	}

	public function AdminDeleteCustomerCheckAllowed(&$isAllowed, &$errorMessages, $Customer){
		if ($isAllowed === false) return;

		$openFees = $this->getOpenFees($Customer->customers_id);
		if (sizeof($openFees) > 0){
			$isAllowed = false;
			$errorMessages[] = 'Customer has outstanding late fees.';
		}
	}

	public function getAllFees($customerId = false){
		$Qfees = Doctrine_Query::create()
			->from('LateFees lf')
			->leftJoin('lf.OrdersProducts op');
		if ($customerId !== false){
			$Qfees->where('lf.customers_id = ?', $customerId);
		}
		$Result = $Qfees->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if (!$Result || !is_array($Result)){
			$Result = array();
		}
		return $Result;
	}

	public function getOpenFees($customerId = false){
		$Qfees = Doctrine_Query::create()
			->from('LateFees lf')
			->leftJoin('lf.OrdersProducts op')
			->where('lf.fee_status = ?', $this->openStatusId());
		if ($customerId !== false){
			$Qfees->andWhere('lf.customers_id = ?', $customerId);
		}
		$Result = $Qfees->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if (!$Result || !is_array($Result)){
			$Result = array();
		}
		return $Result;
	}

	public function OrderCreatorLoadCustomerInfoResponse(&$response, $Customer){
		global $Editor;
		$Fees = $this->getOpenFees($Customer->customers_id);
		if (!empty($Fees)){
			$feeTotal = 0;
			foreach($Fees as $fInfo){
				$feeTotal += $fInfo['fee_amount'];
			}
			$Editor->TotalManager->add(new OrderCreatorTotal(array(
						'module_type' => 'late_fee',
						'editable' => false,
						'title' => 'Late Fees:',
						'value' => $feeTotal,
						'sort_order' => 0
					)));
			$response['orderTotalTable'] = $Editor->editTotals()->draw();
		}
	}
}
