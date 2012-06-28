<?php
class EmailModuleCustomer extends EmailModuleBase
{

	protected $_events = array(
		array(
			'key'         => 'NEW_ACCOUNT_CREATED',
			'text'        => 'A New Customer Account Has Been Created',
			'description' => 'This email is sent to the specified people when a new customer account is created'
		),
		array(
			'key'         => 'EXISTING_ACCOUNT_UPDATED',
			'text'        => 'An Existing Customer Account Has Been Updated',
			'description' => 'This email is sent to the specified people when an existing customer account has been updated'
		)
	);

	public function __construct()
	{
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Customer Email Management');
		$this->setDescription('Customer Email Management');

		$this->init('customer');
	}

	public function getEvents()
	{
		return $this->_events;
	}

	public function getEventVariables()
	{
		return array(
			array('varName' => 'password', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'full_name', 'conditional' => false),
			array('varName' => 'firstname', 'conditional' => false),
			array('varName' => 'lastname', 'conditional' => false),
			array('varName' => 'email_address', 'conditional' => false)
		);
	}

	public function getEventSettings($eventKey, $currentSettings = array())
	{
		$settings = array(
			'fields' => array()
		);

		switch($eventKey){
			case 'NEW_ACCOUNT_CREATED':
				$settings = false;
				break;
			case 'EXISTING_ACCOUNT_UPDATED':
				$settings = false;
				break;
		}
		return $settings;
	}

	public function prepareEventSettingsJson()
	{
		$return = array();
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

				$Customer = $o['CustomerObj'];
				if ($Customer){
					$this->setVar('firstname', $Customer->customers_firstname);
					$this->setVar('lastname', $Customer->customers_lastname);
					$this->setVar('full_name', $Customer->customers_firstname . ' ' . $Customer->customers_lastname);
					$this->setVar('email_address', $Customer->customers_email_address);
					$this->setVar('password', $Customer->customers_password);

					if ($GlobalTemplateSettings['send_to'] == 'customer'){
						$this->sendEmail(array(
							'name'  => $this->getVar('full_name'),
							'email' => $this->getVar('email_address')
						));
					}
					elseif ($GlobalTemplateSettings['send_to'] == 'admin') {
						$this->sendEmail(array(
							'name'  => sysConfig::get('STORE_OWNER'),
							'email' => sysConfig::get('STORE_OWNER_EMAIL_ADDRESS')
						));
					}
					else {
						$this->sendEmail(array(
							'name'  => $GlobalTemplateSettings['send_to'],
							'email' => $GlobalTemplateSettings['send_to']
						));
					}
				}
				unset($Customer);
			}
		}
	}
}
