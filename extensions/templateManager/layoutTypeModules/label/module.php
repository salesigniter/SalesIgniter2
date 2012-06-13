<?php
class TemplateManagerLayoutTypeModuleLabel extends TemplateManagerLayoutTypeModuleBase
{

	protected $title = 'Label Printer Template';

	protected $code = 'label';

	protected $startingLayoutPath = 'extensions/templateManager/layoutTypeModules/label/avery/';

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
