<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/**
 * Cron job reservation auto send and return module
 *
 * @package   CronJob
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class CronjobModuleReservationAutoSendReturn extends CronjobModuleBase
{

	/**
	 *
	 */
	public function __construct()
	{
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Reservation Auto Send & Return');
		$this->setDescription('Reservation Auto Send & Return');

		$this->init('reservationAutoSendReturn', false, __DIR__);
	}

	public function process()
	{
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_AUTO_SEND') == 'True'){
			$this->sendReservations();
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_AUTO_RETURN') == 'True'){
			$this->returnReservations();
		}
	}

	private function returnReservations(){
		$Reservations = Doctrine_Query::create()
			->from('PayPerRentalReservations')
			->where('end_date <= ?', date(DATE_TIMESTAMP))
			->andWhere('rental_state = ?', 'out')
			->execute();
		if ($Reservations && $Reservations->count() > 0){
			foreach($Reservations as $Reservation){
				if ($Reservation->SaleProduct->SaleInventory->barcode_id > 0){
					$Reservation->SaleProduct->SaleInventory->Barcode->status = 'A';
				}
				elseif ($Reservation->SaleProduct->SaleInventory->quantity_id > 0){
					$Reservation->SaleProduct->SaleInventory->Quantity->reserved -= 1;
					$Reservation->SaleProduct->SaleInventory->Quantity->available += 1;
				}
				else{
					trigger_error('Unknown Inventory Assigned', E_USER_NOTICE);
					continue;
				}

				$Reservation->rental_state = 'returned';
				$Reservation->date_returned = $Reservation->end_date;
				$Reservation->broken = '0';

				EventManager::notify('ReservationOnReturn', $Reservation);

				$Reservation->save();
			}
		}
	}

	private function sendReservations(){
		$Reservations = Doctrine_Query::create()
			->from('PayPerRentalReservations')
			->where('start_date <= ?', date(DATE_TIMESTAMP))
			->andWhere('rental_state = ?', 'reserved')
			->execute();
		if ($Reservations && $Reservations->count() > 0){
			foreach($Reservations as $Reservation){
				if ($Reservation->SaleProduct->SaleInventory->barcode_id > 0){
					$Reservation->SaleProduct->SaleInventory->Barcode->status = 'O';
				}
				elseif ($Reservation->SaleProduct->SaleInventory->quantity_id > 0){
					$Reservation->SaleProduct->SaleInventory->Quantity->reserved -= 1;
					$Reservation->SaleProduct->SaleInventory->Quantity->qty_out += 1;
				}
				else{
					trigger_error('Unknown Inventory Assigned', E_USER_NOTICE);
					continue;
				}

				$Reservation->rental_state = 'out';
				$Reservation->date_shipped = $Reservation->start_date; //date('Y-m-d');

				EventManager::notify('ReservationOnSend', $Reservation);

				$Reservation->save();

				$emailEvent = new emailEvent('reservation_sent', $Reservation->SaleProduct->Sale->Customer->language_id);

				$fullName = $Reservation->SaleProduct->Sale->customers_firstname . ' ' . $Reservation->SaleProduct->Sale->customers_lastname;
				$emailAddress = $Reservation->SaleProduct->Sale->customers_email_address;

				$emailEvent->setVars(array(
					'full_name'      => $fullName,
					'rented_product' => $Reservation->Product->ProductsDescription[$Reservation->SaleProduct->Sale->Customer->language_id]->products_name,
					'due_date'       => $Reservation->end_date->format(sysLanguage::getDateFormat('long')),
					'email_address'  => $emailAddress
				));

				$emailEvent->sendEmail(array(
					'email' => $emailAddress,
					'name'  => $fullName
				));
				$Reservation->free(true);
			}
			$Reservations->free(true);
		}
	}
}
