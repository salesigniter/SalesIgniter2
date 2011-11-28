<?php
	Doctrine_Query::create()
	->delete('PayPerRentalMaintenancePeriods')
	->where('maintenance_period_id = ?', (int)$_GET['mID'])
	->execute();
	
	EventManager::attachActionResponse(itw_app_link('appExt=payPerRentals', 'maintenance_periods', 'default'), 'redirect');
?>