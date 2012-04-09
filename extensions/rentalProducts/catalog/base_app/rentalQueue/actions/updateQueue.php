<?php
/*
	Sales Igniter E-Commerce System v2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

$Contents = $RentalQueue->getContents()->getIterator();
while($Contents->valid()){
	$RentalProduct =& $Contents->current();
	if (is_array($_POST['queue_delete']) && in_array($RentalProduct->getId(), $_POST['queue_delete'])){
		$RentalQueue->remove($RentalProduct->getId());
	}else{
		$RentalProduct->setData('prevPriority', $RentalProduct->getPriority());
		$RentalProduct->setData('priority', $_POST['queue_priority'][$RentalProduct->getId()]);
	}
	$Contents->next();
}

$RentalQueue->fixPriorities();

EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action'))), 'redirect');
?>