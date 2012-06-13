<?php
class TemplateManagerLayoutTypeModuleEmail extends TemplateManagerLayoutTypeModuleBase
{

	protected $title = 'Email Template';

	protected $code = 'email';

	protected $startingLayoutPath = 'extensions/templateManager/layoutTypeModules/email/startingLayout/';

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

		$EmailTemplates = Doctrine_Core::getTable('EmailTemplates')
			->findAll();

		$boxes = array();
		foreach($EmailTemplates as $EmailTemplate){
			$boxes[] = array(
				'labelPosition' => 'after',
				'label' => $EmailTemplate->email_templates_name,
				'value' => $EmailTemplate->email_templates_event
			);
		}
		$EventsCheckboxes = htmlBase::newElement('checkbox')
			->addGroup(array(
			'name' => 'email_template[]',
			'checked' => (isset($LayoutSettings->emailTemplates) ? (array) $LayoutSettings->emailTemplates: ''),
			'separator' => array(
				'type' => 'table',
				'cols' => 3
			),
			'data' => $boxes
		));
		$SettingsTable->addBodyRow(array(
			'attr' => array(
				'data-for_page_type' => 'email'
			),
			'columns' => array(
				array('valign' => 'top', 'text' => 'Email Events: '),
				array('text' => '<input type="checkbox" class="checkAll"/> <span class="checkAllText">Check All</span><br><br>' . $EventsCheckboxes->draw())
			)
		));

		return $SettingsTable->draw();
	}

	public function onSave($Layout){
		$Layout->layout_settings = json_encode(array(
			'emailTemplates' => $_POST['email_template']
		));
	}
}
