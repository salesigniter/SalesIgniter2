<?php
class TemplateManagerLayoutTypeModuleEmail extends TemplateManagerLayoutTypeModuleBase
{

	/**
	 *
	 */
	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Email Template');
		$this->setDescription('Email Template');

		$this->init('email', false, __DIR__);
	}

	public function getSetWidth(){
		return 960;
	}

	public function getLayoutSettings($Layout)
	{
		$SettingsTable = htmlBase::newElement('table');

		if ($Layout->layout_id <= 0){
			$SettingsTable->addBodyRow(array(
				'columns' => array(
					array('text' => 'Select starting layout:'),
					array('text' => $this->getLayoutTypeSelect())
				)
			));
		}

		$checkbox = htmlBase::newCheckbox()
			->setName('is_default')
			->setChecked(sysConfig::get('DEFAULT_EMAIL_TEMPLATE') == $Layout->layout_id);

		$SettingsTable->addBodyRow(array(
			'attr' => array(
				'data-for_page_type' => 'email'
			),
			'columns' => array(
				array('valign' => 'top', 'text' => 'Use For Emails: '),
				array('text' => $checkbox->draw() . '<br><b><u>IMPORTANT</u></b><br>Only one email template can be used at a time<br>So selecting this one will unselect any other')
			)
		));

		return $SettingsTable->draw();
	}

	public function afterSave($Layout){
		if (isset($_POST['is_default'])){
			$Configurations = Doctrine_Core::getTable('Configuration');
			$Config = $Configurations->findOneByConfigurationKey('DEFAULT_EMAIL_TEMPLATE');
			if (!$Config){
				$Config = $Configurations->create();
				$Config->configuration_key = 'DEFAULT_EMAIL_TEMPLATE';
				$Config->configuration_group_key = 'coreMyStore';
			}
			$Config->configuration_value = $Layout->layout_id;
			$Config->save();
		}
	}
}
