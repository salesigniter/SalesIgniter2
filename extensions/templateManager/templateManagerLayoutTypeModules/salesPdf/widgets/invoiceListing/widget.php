<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerWidgetInvoiceListing extends TemplateManagerWidget
{

	public function __construct() {
		global $App;
		$this->init('invoiceListing', false, __DIR__);
	}

	public function getColumnClasses(){
		$columnsPath = realpath(__DIR__ . '/columns');
		$clientColumnsPath = sysConfig::getDirFsCatalog() . 'clientData/' . str_replace(sysConfig::getDirFsCatalog(), '', $columnsPath);

		$ColumnClasses = array();
		$Dir = new DirectoryIterator(__DIR__ . '/columns');
		foreach($Dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isDir() || file_exists($clientColumnsPath . '/' . $dInfo->getBasename('.php'))){
				continue;
			}
			$ClassName = 'InvoiceListingWidgetColumn' . ucfirst($dInfo->getBasename('.php'));
			if (class_exists($ClassName) === false){
				require($dInfo->getPathname());
			}

			$ColumnClasses[$dInfo->getBasename('.php')] = new $ClassName();
		}

		$Dir = new DirectoryIterator($clientColumnsPath);
		foreach($Dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isDir()){
				continue;
			}
			$ClassName = 'InvoiceListingWidgetColumn' . ucfirst($dInfo->getBasename('.php'));
			if (class_exists($ClassName) === false){
				require($dInfo->getPathname());
			}

			$ColumnClasses[$dInfo->getBasename('.php')] = new $ClassName();
		}
		return $ColumnClasses;
	}

	public function addStyles($Styles, htmlElement $El) {
		//echo $El->getTagName() . '::<pre>';print_r($Styles);
		$css = array();
		foreach($Styles as $definition_key => $definition_value){
			if ($definition_key == 'custom_css'){
				$El->attr('data-custom_css', htmlspecialchars($definition_value));
			}

			if (is_string($definition_value) && (substr($definition_value, 0, 1) == '{' || substr($definition_value, 0, 1) == '[')){
				$css[$definition_key] = json_decode($definition_value);
			}
			else {
				$css[$definition_key] = $definition_value;
			}
			$El->css($definition_key, $css[$definition_key]);
		}
		$El->attr('data-styles', (empty($css) ? '{}' : htmlspecialchars(json_encode($css))));
	}

	public function addInputs($Inputs, $El) {
		$inputVals = array();
		foreach($Inputs as $configuration_key => $configuration_value){
			if ($configuration_key == 'table'){
				continue;
			}
			if (is_string($configuration_value) && (substr($configuration_value, 0, 1) == '{' || substr($configuration_value, 0, 1) == '[')){
				$inputVals[$configuration_key] = json_decode($configuration_value);
			}
			else {
				$inputVals[$configuration_key] = $configuration_value;
			}
		}
		$El->attr('data-inputs', (empty($inputVals) ? '{}' : htmlspecialchars(json_encode($inputVals))));
	}

	public function showLayoutPreview($WidgetSettings) {
		global $appExtension;

		if (!isset($WidgetSettings['settings']->tableColumns)){
			return $this->getCode();
		}

		$ListingTable = htmlBase::newTable()
			->css(
			array(
				'width' => '100%',
				'border' => '1px solid black'
			))
			->setCellPadding(3)
			->setCellSpacing(0);

		$TableColumns = (array)$WidgetSettings['settings']->tableColumns;

		usort($TableColumns, function ($a, $b){
			return ((int)$a->display_order > (int)$b->display_order ? 1 : -1);
		});

		$ColumnClasses = $this->getColumnClasses();
		$headerColumns = array();
		$bodyColumns = array();
		foreach($TableColumns as $cInfo){
			$headerColumns[] = array(
				'css' => array('border-bottom' => '1px solid black'),
				'text' => $cInfo->column_properties->heading_text
			);
			$bodyColumns[] = array('text' => $ColumnClasses[$cInfo->code]->getPreviewData());
		}

		$ListingTable->addHeaderRow(array(
			'columns' => $headerColumns
		));
		$ListingTable->addBodyRow(array(
			'columns' => $bodyColumns
		));

		return $ListingTable->draw();
	}

	public function isTable() {
		return true;
	}

	public function applyStyles(&$El, $stylesObj){
		foreach($stylesObj as $k => $v){
			$El->css($k, $v);
		}
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		/**
		 * @var Order $Sale
		 */
		$Sale = $LayoutBuilder->getVar('Sale');

		$ListingTable = htmlBase::newTable()
			->css(
			array(
				'width' => '100%',
				'border' => '1px solid black'
			))
			->setCellPadding(3)
			->setCellSpacing(0);

		$TableColumns = (array)$boxWidgetProperties->tableColumns;

		usort($TableColumns, function ($a, $b){
			return ((int)$a->display_order > (int)$b->display_order ? 1 : -1);
		});

		$ColumnClasses = $this->getColumnClasses();
		$headerColumns = array();
		foreach($TableColumns as $cInfo){
			$headerColumns[] = array(
				'css' => array('border-bottom' => '1px solid black'),
				'text' => $cInfo->column_properties->heading_text
			);
		}

		$ListingTable->addHeaderRow(array(
			'columns' => $headerColumns
		));

		foreach($Sale->ProductManager->getContents() as $Product){
			$bodyColumns = array();
			foreach($TableColumns as $cInfo){
				$bodyColumns[] = array('text' => $ColumnClasses[$cInfo->code]->getData($Product));
			}

			$ListingTable->addBodyRow(array(
				'columns' => $bodyColumns
			));
		}

		$this->setBoxContent($ListingTable->draw());
		return $this->draw();
	}
}

?>