<?php
class EmailModuleReservation extends EmailModuleBase
{

	protected $_events = array(
		array(
			'key'         => 'RESERVATION_SEND_EMAIL',
			'text'        => 'A Reservation Has Been Sent',
			'description' => 'This email is sent to the specified people when a reservation has been sent'
		),
		array(
			'key'         => 'RESERVATION_RETURN_EMAIL',
			'text'        => 'A Reservation Has Been Returned',
			'description' => 'This email is sent to the specified people when a reservation has been returned'
		),
		array(
			'key'         => 'RESERVATION_BEFORE_SEND_TIME_SPECIFIC_EMAIL',
			'text'        => 'Specific Time Before Reservation Is Shipped',
			'description' => 'This email will be sent to the specified people the specified period before a reservation is to be sent ( Must have cron job "Order Time Emails" enabled )'
		),
		array(
			'key'         => 'RESERVATION_AFTER_SEND_TIME_SPECIFIC_EMAIL',
			'text'        => 'Specific Time Afer Reservation Is Shipped',
			'description' => 'This email will be sent to the specified people the specified period after a reservation has been sent ( Must have cron job "Order Time Emails" enabled )'
		),
		array(
			'key'         => 'RESERVATION_BEFORE_RETURN_TIME_SPECIFIC_EMAIL',
			'text'        => 'Specific Time Before Reservation Is Returned',
			'description' => 'This email will be sent to the specified people the specified period before a reservation is to be returned ( Must have cron job "Order Time Emails" enabled )'
		),
		array(
			'key'         => 'RESERVATION_AFTER_RETURN_TIME_SPECIFIC_EMAIL',
			'text'        => 'Specific Time Afer Reservation Is Returned',
			'description' => 'This email will be sent to the specified people the specified period after a reservation has been returned ( Must have cron job "Order Time Emails" enabled )'
		)
	);

	public function __construct()
	{
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Reservation Email Management');
		$this->setDescription('Reservation Email Management');

		$this->init('reservation', false, __DIR__);
	}

	public function getEvents()
	{
		return $this->_events;
	}

	public function getEventVariables()
	{
		return array(
			array('varName' => 'firstname', 'conditional' => false),
			array('varName' => 'full_name', 'conditional' => false),
			array('varName' => 'email_address', 'conditional' => false),
			array('varName' => 'days_late', 'conditional' => false),
			array('varName' => 'rented_product', 'conditional' => false),
			array('varName' => 'due_date', 'conditional' => false),
			array('varName' => 'rented_list', 'conditional' => false)
		);
	}

	public function getEventSettings($eventKey, $currentSettings = array())
	{
		$settings = array(
			'fields' => array()
		);

		switch($eventKey){
			case 'RESERVATION_SEND_EMAIL':
			case 'RESERVATION_RETURN_EMAIL':
				$settings = false;
				break;
			case 'RESERVATION_BEFORE_SEND_TIME_SPECIFIC_EMAIL':
			case 'RESERVATION_AFTER_SEND_TIME_SPECIFIC_EMAIL':
			case 'RESERVATION_BEFORE_RETURN_TIME_SPECIFIC_EMAIL':
			case 'RESERVATION_AFTER_RETURN_TIME_SPECIFIC_EMAIL':
				$settings['fields'] = array();

				$NumberInput = htmlBase::newInput()
					->setName('send_on_time');
				if (isset($currentSettings['send_on_time'])){
					$NumberInput->val($currentSettings['send_on_time']);
				}

				$PeriodInput = htmlBase::newSelectbox()
					->setName('send_on_period')
					->addOption('3600', 'Hour(s)')
					->addOption('86400', 'Day(s)')
					->addOption('604800', 'Week(s)');
				if (isset($currentSettings['send_on_period'])){
					$PeriodInput->selectOptionByValue($currentSettings['send_on_period']);
				}

				$settings['fields'][] = array(
					'label' => 'Send On Time',
					'input' => $NumberInput
				);
				$settings['fields'][] = array(
					'label' => 'Send On Period',
					'input' => $PeriodInput
				);
				break;
		}
		return $settings;
	}

	public function prepareEventSettingsJson($arr)
	{
		$return = array();
		switch($arr['module_event_key']){
			case 'RESERVATION_BEFORE_SEND_TIME_SPECIFIC_EMAIL':
			case 'RESERVATION_AFTER_SEND_TIME_SPECIFIC_EMAIL':
			case 'RESERVATION_BEFORE_RETURN_TIME_SPECIFIC_EMAIL':
			case 'RESERVATION_AFTER_RETURN_TIME_SPECIFIC_EMAIL':
				$return['send_on_time'] = $arr['send_on_time'];
				$return['send_on_period'] = $arr['send_on_period'];
				break;
		}
		return $return;
	}

	public function process($eventKey, $o = array())
	{
		$Qtemplate = Doctrine_Query::create()
			->select('t.template_settings, td.email_templates_subject, td.email_templates_content')
			->from('EmailTemplates t')
			->leftJoin('t.Description td')
			->where('t.email_module_event_key = ?', $eventKey)
			->andWhere('t.template_status = ?', '1')
			->andWhere('td.language_id = ?', Session::get('languages_id'))
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		if ($Qtemplate && sizeof($Qtemplate) > 0){
			foreach($Qtemplate as $Template){
				$TemplateSettings = json_decode($Template['template_settings'], true);
				$GlobalTemplateSettings = $TemplateSettings['global'];
				$ModuleTemplateSettings = $TemplateSettings['module'];

				$this->setEmailSubject($Template['Description'][0]['email_templates_subject']);
				$this->setEmailBody($Template['Description'][0]['email_templates_content']);

				if (isset($o['testMode']) && $o['testMode'] === true){
					$Result = true;
				}
				elseif (isset($o['ReservationObj']) === false) {
					if (isset($ModuleTemplateSettings['send_on_time'])){
						$PeriodTime = ($ModuleTemplateSettings['send_on_time'] * $ModuleTemplateSettings['send_on_period']);
						$PeriodLookAhead = (23 * 3600); //Look ahead 23 hours to pick up items that wouldn't show up otherwise
					}

					$Qreservations = Doctrine_Query::create()
						->from('PayPerRentalReservations');

					switch($eventKey){
						case 'RESERVATION_SEND_EMAIL':
						case 'RESERVATION_RETURN_EMAIL':
							$Qreservations->where('id = ?', $o['reservation_id']);
							break;
						case 'RESERVATION_BEFORE_SEND_TIME_SPECIFIC_EMAIL':
							$Qreservations
								->where('(UNIX_TIMESTAMP(start_date) - UNIX_TIMESTAMP(now())) BETWEEN ? AND ?', array(
								$PeriodTime,
								$PeriodTime + $PeriodLookAhead
							))
								->andWhere('rental_state = ?', 'reserved');
							break;
						case 'RESERVATION_AFTER_SEND_TIME_SPECIFIC_EMAIL':
							$Qreservations
								->where('(UNIX_TIMESTAMP(start_date) - UNIX_TIMESTAMP(now())) BETWEEN ? AND ?', array(
								$PeriodTime,
								$PeriodTime + $PeriodLookAhead
							))
								->andWhere('rental_state = ?', 'out');
							break;
						case 'RESERVATION_BEFORE_RETURN_TIME_SPECIFIC_EMAIL':
							$Qreservations
								->where('(UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(now())) BETWEEN ? AND ?', array(
								$PeriodTime,
								$PeriodTime + $PeriodLookAhead
							))
								->andWhere('rental_state = ?', 'reserved');
							break;
						case 'RESERVATION_AFTER_RETURN_TIME_SPECIFIC_EMAIL':
							$Qreservations
								->where('(UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(end_date)) BETWEEN ? AND ?', array(
								$PeriodTime,
								$PeriodTime + $PeriodLookAhead
							))
								->andWhere('rental_state = ?', 'returned');
							break;
					}
					$Result = $Qreservations->execute();
				}
				else {
					$Result = array($o['ReservationObj']);
				}

				if ($Result){
					if (isset($o['testMode']) && $o['testMode'] === true){
						$GlobalTemplateSettings['send_to'] = $o['sendTo'];

						$this->setVar('firstname', 'John');
						$this->setVar('full_name', 'John Doe');
						$this->setVar('email_address', 'johndoe@domain.com');
						$this->setVar('days_late', '5 Days');
						$this->setVar('rented_product', 'Rented Products Name');
						$this->setVar('due_date', date(sysLanguage::getDateFormat('long')));
						$this->setVar('rented_list', 'Rented Products Name');

						$this->sendEmail(array(
							'name'  => $GlobalTemplateSettings['send_to'],
							'email' => $GlobalTemplateSettings['send_to']
						));
					}
					else {
						foreach($Result as $Reservation){
							$DaysLateDate = new SesDateTime('now');

							$this->setVar('firstname', $Reservation->SaleProduct->Sale->customers_firstname);
							$this->setVar('full_name', $Reservation->SaleProduct->Sale->customers_firstname . ' ' . $Reservation->SaleProduct->Sale->customers_lastname);
							$this->setVar('email_address', $Reservation->SaleProduct->Sale->customers_email_address);
							$this->setVar('days_late', $DaysLateDate
								->diff($Reservation->end_date)
								->format('%a days'));
							$this->setVar('rented_product', $Reservation->Product->ProductsDescription[Session::get('languages_id')]->products_name);
							$this->setVar('due_date', $Reservation->end_date->format(sysLanguage::getDateFormat('long')));
							$this->setVar('rented_list', $Reservation->Product->ProductsDescription[Session::get('languages_id')]->products_name);

							if ($GlobalTemplateSettings['send_to'] == 'customer'){
								$this->sendEmail(array(
									'name'  => $this->getVar('full_name'),
									'email' => $this->getVar('email_address'),
									'attach' => (isset($o['attach']) ? $o['attach'] : false)
								));
							}
							elseif ($GlobalTemplateSettings['send_to'] == 'admin') {
								$this->sendEmail(array(
									'name'  => sysConfig::get('STORE_OWNER'),
									'email' => sysConfig::get('STORE_OWNER_EMAIL_ADDRESS'),
									'attach' => (isset($o['attach']) ? $o['attach'] : false)
								));
							}
							else {
								$this->sendEmail(array(
									'name'  => $GlobalTemplateSettings['send_to'],
									'email' => $GlobalTemplateSettings['send_to'],
									'attach' => (isset($o['attach']) ? $o['attach'] : false)
								));
							}
							$this->clearVars();
						}
					}
				}
				unset($Result);
			}
		}
	}
}
