<?php
class TemplateManagerLayoutTypeModulePage extends TemplateManagerLayoutTypeModuleBase
{

	protected $title = 'Standalone Page Template';

	protected $code = 'page';

	protected $startingLayoutPath = 'extensions/templateManager/layoutTypeModules/page/startingLayout/';

	public function getSetWidth(){
		return 960;
	}

	public function getLayoutSettings($Layout)
	{
		$LayoutSettings = json_decode($Layout->layout_settings, true);

		$SettingsTable = htmlBase::newElement('table');

		$ApplicationNameInput = htmlBase::newInput()
			->setName('appName')
			->setValue((isset($LayoutSettings['appName']) ? $LayoutSettings['appName'] : ''));

		$ApplicationPageTitleInput = htmlBase::newInput()
			->setName('appPageTitle')
			->setValue((isset($LayoutSettings['appPageTitle']) ? $LayoutSettings['appPageTitle'][Session::get('languages_id')] : ''));

		$ApplicationPageSubTitleInput = htmlBase::newInput()
			->setName('appPageSubTitle')
			->setValue((isset($LayoutSettings['appPageSubTitle']) ? $LayoutSettings['appPageSubTitle'][Session::get('languages_id')] : ''));

		$ApplicationPageNameInput = htmlBase::newInput()
			->setName('appPageName')
			->setValue((isset($LayoutSettings['appPageName']) ? $LayoutSettings['appPageName'] : ''));

		if ($Layout->layout_id <= 0){
			$SettingsTable->addBodyRow(array(
				'columns' => array(
					array('text' => 'Select starting layout:'),
					array('text' => $this->getLayoutTypeSelect())
				)
			));
		}

		$SettingsTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Display Type:'),
				array('text' => htmlBase::newElement('selectbox')
					->setName('layoutType')
					->addOption('desktop', 'Desktop')
					->addOption('smartphone', 'Smart Phone')
					->addOption('tablet', 'Tablet')
					->selectOptionByValue($Layout->layout_type)
					->draw())
			)
		));

		$SettingsTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Application Name:'),
				array('text' => $ApplicationNameInput)
			)
		));

		$SettingsTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Application Page Name:'),
				array('text' => $ApplicationPageNameInput->draw() . '.php')
			)
		));

		$SettingsTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Application Page Title:'),
				array('text' => $ApplicationPageTitleInput)
			)
		));

		$SettingsTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Application Page Sub Title:'),
				array('text' => $ApplicationPageSubTitleInput)
			)
		));

		return $SettingsTable->draw();
	}

	public function onSave($Layout){
		$Layout->layout_settings = json_encode(array(
			'appName' => $_POST['appName'],
			'appPageName' => $_POST['appPageName'],
			'appPageTitle' => array(
				Session::get('languages_id') => $_POST['appPageTitle']
			),
			'appPageSubTitle' => array(
				Session::get('languages_id') => $_POST['appPageSubTitle']
			)
		));
	}
}
