<?php
class TemplateManagerLayoutTypeModuleLabel extends TemplateManagerLayoutTypeModuleBase
{

	/**
	 *
	 */
	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Label Printer Template');
		$this->setDescription('Label Printer Template');

		$this->init('label', false, __DIR__);
	}

	public function isEnabled(){
		return false;
	}

	public function getLayoutSettings($Layout)
	{
		$SettingsTable = htmlBase::newElement('table');

		if ($Layout->layout_id <= 0){
			$SettingsTable->addBodyRow(array(
				'columns' => array(
					array('text' => 'Select label type:'),
					array('text' => $this->getLayoutTypeSelect())
				)
			));
		}

		return $SettingsTable->draw();
	}
}
