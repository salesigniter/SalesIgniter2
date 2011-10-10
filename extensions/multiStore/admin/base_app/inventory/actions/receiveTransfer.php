<?php
Doctrine_Query::create()
	->update('ProductsInventoryBarcodeTransfers')
	->set('status = ?', 'R')
	->set('is_history = ?', '1')
	->where('transfer_id = ?', $_GET['tID'])
	->execute();

EventManager::attachActionResponse(array(
		'success' => true
	), 'json');