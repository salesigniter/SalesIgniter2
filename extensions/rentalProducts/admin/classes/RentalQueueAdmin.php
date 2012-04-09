<?php
include(sysConfig::getDirFsCatalog() . 'extensions/RentalProducts/catalog/classes/RentalQueue.php');

class RentalQueueAdmin extends RentalQueue
{

	function __construct($cID) {
		parent::__construct();

		$this->customerID = $cID;
		$this->loadQueue($cID);
		$this->initContents();
	}

	function count_rented() {
		$Total = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select count(customers_id) as total from rented_queue where customers_id = "' . $this->customerID . '"');
		return (int) $Total[0]['total'];
	}

	function incrementTopRentals($pID) {
		$QrentalsTop = Doctrine_Core::getTable('RentalTop');
		$Qrental = $QrentalsTop->findOneByRentalTopId($pID);
		if ($Qrental){
			$Qrental->top += 1;
		}
		else {
			$Qrental = new RentalTop();
			$Qrental->products_id = $pID;
			$Qrental->top = 1;
		}
		$Qrental->save();
	}
}
