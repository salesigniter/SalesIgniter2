<?php
class TemplateManagerLayoutTypeModulePrint extends TemplateManagerLayoutTypeModuleBase
{

	protected $title = 'Print Template';

	protected $code = 'print';

	protected $startingLayoutPath = 'extensions/templateManager/layoutTypeModules/print/startingLayout/';

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

		AccountsReceivableModules::loadModules();
		$boxes = array();
		foreach(AccountsReceivableModules::getModules() as $Module){
			$boxes[] = array(
				'labelPosition' => 'after',
				'label' => $Module->getTitle(),
				'value' => $Module->getCode()
			);
			if (is_dir(sysConfig::getDirFsCatalog() . 'templates/chater/modules/accountsReceivableModules/' . $Module->getCode() . '/print')){
				$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'templates/chater/modules/accountsReceivableModules/' . $Module->getCode() . '/print');
				foreach($Dir as $dInfo){
					if ($dInfo->isDot() || $dInfo->isDir()){
						continue;
					}

					$boxes[] = array(
						'labelPosition' => 'after',
						'label' => $Module->getTitle() . ' - ' . ucfirst($dInfo->getBasename('.php')),
						'value' => $Module->getCode() . '_' . $dInfo->getBasename('.php')
					);
				}
			}
		}

		$SettingsTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Which Modules:'),
				array('text' => htmlBase::newElement('checkbox')
					->addGroup(array(
					'name' => 'print_modules[]',
					'checked' => (isset($LayoutSettings->printModules) ? (array) $LayoutSettings->printModules: ''),
					'separator' => array(
						'type' => 'table',
						'cols' => 3
					),
					'data' => $boxes
				))->draw()
				)
			)
		));

		return $SettingsTable->draw();
	}

	public function onSave($Layout){
		$Layout->layout_settings = json_encode(array(
			'layoutOrientation' => $_POST['layoutOrientation'],
			'printModules' => $_POST['print_modules']
		));
	}
}
