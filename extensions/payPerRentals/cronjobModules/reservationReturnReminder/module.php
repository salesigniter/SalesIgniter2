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
 * Cron job reservation return reminder module
 *
 * @package   CronJob
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class CronjobModuleReservationReturnReminder extends CronjobModuleBase
{

	/**
	 *
	 */
	public function __construct()
	{
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Reservation Return Reminder');
		$this->setDescription('Reservation Return Reminder');

		$this->init('reservationReturnReminder', false, __DIR__);
	}

	public function process()
	{
		$EndDateCheck = new SesDateTime('now');
		$DateToCheck = $EndDateCheck->modify('+' . $this->getConfigData('DAYS_BEFORE_DUE') . ' Days');

		$Reservations = Doctrine_Query::create()
			->from('PayPerRentalReservations')
			->where('end_date <= ?', $DateToCheck->format(DATE_TIMESTAMP))
			->andWhere('end_date >= now()')
			->andWhere('rental_state = ?', 'out')
			->execute();

		$productLineTemplate = 'Product name: %s due on: %s';
		$toEmail = array();
		foreach($Reservations as $Reservation){
			$Sale = $Reservation->SaleProduct->Sale;
			$customerId = $Sale->customers_id;
			if (isset($toEmail[$customerId]) === false){
				$toEmail[$customerId] = array(
					'full_name'     => $Sale->customers_firstname . ' ' . $Sale->customers_lastname,
					'email_address' => $Sale->customers_email_address,
					'language_id'   => $Sale->Customer->language_id,
					'products'      => array()
				);
			}

			$toEmail[$customerId]['products'][] = sprintf(
				$productLineTemplate,
				$Reservation->Product->ProductsDescription[$toEmail[$customerId]['language_id']]->products_name,
				$Reservation->end_date->format(sysLanguage::getDateFormat('long'))
			);
		}

		foreach($toEmail as $customerId => $eInfo){
			$emailEvent = new emailEvent('return_reminder', $eInfo['language_id']);
			$emailEvent->setVar('firstname', $eInfo['full_name']);
			$emailEvent->setVar('email_address', $eInfo['email_address']);
			$emailEvent->setVar('rented_list', implode('<br/>', $eInfo['products']));

			$emailEvent->sendEmail(array(
				'email' => $eInfo['email_address'],
				'name'  => $eInfo['full_name']
			));
		}
	}
}
