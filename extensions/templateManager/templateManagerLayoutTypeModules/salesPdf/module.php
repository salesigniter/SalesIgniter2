<?php
class TemplateManagerLayoutTypeModuleSalesPdf extends TemplateManagerLayoutTypeModuleBase
{

	/**
	 *
	 */
	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Sales Pdf Template');
		$this->setDescription('Sales Pdf Template');

		$this->init('salesPdf', false, __DIR__);
	}

	public function getSetWidth(){
		return 595;
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
		}

		$LayoutSettings = $Layout->layout_settings;

		$EstimateGroup = htmlBase::newCheckboxGroup()
			->setId('sale_modules_estimate')
			->setName('sale_modules[estimate]')
			->setGroupSeparator('<br>')
			->setChecked($LayoutSettings['saleModules']['estimate'])
			->addInput(htmlBase::newCheckbox()->setValue('main')->setLabel('Estimate Default')->setLabelPosition('after'))
			->addInput(htmlBase::newCheckbox()->setValue('prepSheet')->setLabel('Estimate Prep Sheet')->setLabelPosition('after'))
			->addInput(htmlBase::newCheckbox()->setValue('carnet')->setLabel('Estimate Carnet')->setLabelPosition('after'));

		$OrderGroup = htmlBase::newCheckboxGroup()
			->setId('sale_modules_order')
			->setName('sale_modules[order]')
			->setGroupSeparator('<br>')
			->setChecked($LayoutSettings['saleModules']['order'])
			->addInput(htmlBase::newCheckbox()->setValue('main')->setLabel('Order Default')->setLabelPosition('after'))
			->addInput(htmlBase::newCheckbox()->setValue('prepSheet')->setLabel('Order Prep Sheet')->setLabelPosition('after'))
			->addInput(htmlBase::newCheckbox()->setValue('carnet')->setLabel('Order Carnet')->setLabelPosition('after'));

		$InvoiceGroup = htmlBase::newCheckboxGroup()
			->setId('sale_modules_invoice')
			->setName('sale_modules[invoice]')
			->setChecked($LayoutSettings['saleModules']['invoice'])
			->setGroupSeparator('<br>')
			->addInput(htmlBase::newCheckbox()->setValue('main')->setLabel('Invoice Default')->setLabelPosition('after'))
			->addInput(htmlBase::newCheckbox()->setValue('prepSheet')->setLabel('Invoice Prep Sheet')->setLabelPosition('after'))
			->addInput(htmlBase::newCheckbox()->setValue('carnet')->setLabel('Invoice Carnet')->setLabelPosition('after'));

		$ModulesBlock = htmlBase::newTable()
			->setCellPadding(3)
			->setCellSpacing(0);

		$ModulesBlock->addBodyRow(array(
			'columns' => array(
				array('text' => $EstimateGroup),
				array('text' => $OrderGroup),
				array('text' => $InvoiceGroup)
			)
		));

		$SettingsTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Which Sale Types:'),
				array('text' => $ModulesBlock)
			)
		));

		return $SettingsTable->draw();
	}

	public function onSave($Layout){
		$Layout->layout_settings = json_encode(array(
			'saleModules' => $_POST['sale_modules']
		));
	}
}
