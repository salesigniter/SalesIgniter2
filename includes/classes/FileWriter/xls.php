<?php
require(SysConfig::getDirFsCatalog() . 'includes/classes/FileWriter/xls/row.php');
require(SysConfig::getDirFsCatalog() . 'includes/classes/FileWriter/xls/col.php');
require(sysConfig::getDirFsCatalog() . 'ext/PHPExcel.php');

class FileWriterXls
{

	/**
	 * @var PHPExcel
	 */
	private $fileObj;

	private $headerRow;

	private $rows = array();

	public function __construct($filename, $open_mode = "r", $use_include_path = false, $context = false) {
		$this->fileObj = new PHPExcel();
		$this->fileObj->getProperties()
			->setCreator(sysConfig::get('STORE_OWNER'))
			->setLastModifiedBy(sysConfig::get('STORE_OWNER'))
			->setTitle('Sales Igniter XLS Export')
			->setSubject('Sales Igniter XLS Export')
			->setDescription('This export file was generated from Sales Igniter')
			->setKeywords("office 2007 openxml php")
			->setCategory('export file');

		$this->fileObj->setActiveSheetIndex(0);
	}

	public function newHeaderRow(){
		$NewRow = new FileWriterXlsRow();

		$this->headerRow = $NewRow;

		return $NewRow;
	}

	public function &newRow() {
		$NewRow = new FileWriterXlsRow($this->headerRow->getRefArray());

		$this->rows[] = $NewRow;

		return $NewRow;
	}

	public function output(){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"XlsExport-" . date('Y_m_d') . ".xls\";" );
		header("Content-Transfer-Encoding: binary");

		/**
		 * Main Worksheet
		 */
		$this->fileObj->setActiveSheetIndex(0);

		$headerColumns = $this->headerRow->getColumns();
		foreach($headerColumns as $k => $Col){
			$this->fileObj->getActiveSheet()->getCellByColumnAndRow($k, 1)->setValue($Col->getValue());
		}

		$ClonableListValidation = new PHPExcel_Cell_DataValidation();
		$ClonableListValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
		$ClonableListValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
		$ClonableListValidation->setAllowBlank(false);
		$ClonableListValidation->setShowInputMessage(true);
		$ClonableListValidation->setShowErrorMessage(true);
		$ClonableListValidation->setShowDropDown(true);
		$ClonableListValidation->setErrorTitle('Input error');
		$ClonableListValidation->setError('Value is not in list.');
		$ClonableListValidation->setPromptTitle('Pick from list');
		$ClonableListValidation->setPrompt('Please pick a value from the drop-down list.');

		$StatusValidation = clone $ClonableListValidation;
		$StatusValidation->setFormula1('"Enabled,Disabled"');

		$YesNoValidation = clone $ClonableListValidation;
		$YesNoValidation->setFormula1('"Yes,No"');

		$ProductTypeValidation = clone $ClonableListValidation;
		$ProductTypeValidation->setFormula1('"standard,package"');

		$TaxClasses = Doctrine_Core::getTable('TaxClass')
			->findAll();
		$TaxClassArr = array();
		foreach($TaxClasses as $TaxClass){
			$TaxClassArr[] = $TaxClass->tax_class_title;
		}
		$TaxClassValidation = clone $ClonableListValidation;
		$TaxClassValidation->setFormula1('"None,' . implode(',', $TaxClassArr) . '"');

		$TimePeriods = Doctrine_Core::getTable('PayPerRentalTypes')
			->findAll();
		$TimePeriodArr = array();
		foreach($TimePeriods as $TimePeriod){
			$TimePeriodArr[] = $TimePeriod->pay_per_rental_types_name;
		}
		$TimePeriodValidation = clone $ClonableListValidation;
		$TimePeriodValidation->setFormula1('"' . implode(',', $TimePeriodArr) . '"');

		foreach($this->rows as $rowNum => $row){
			foreach($headerColumns as $colNum => $Col){
				$RowCol = $row->getColumn($Col->getValue());

				$Cell = $this->fileObj->getActiveSheet()->getCellByColumnAndRow($colNum, $rowNum+2);
				if ($RowCol === false){
					$Cell->setValue('');
				}else{
					$Cell->setValue($RowCol->getValue());
				}

				if (substr($Col->getValue(), -7) == '_status'){
					$Cell->setDataValidation($StatusValidation);
				}
				elseif ($Col->getValue() == 'v_products_type'){
					$Cell->setDataValidation($ProductTypeValidation);
				}
				elseif (in_array($Col->getValue(), array('v_products_featured', 'v_standard_reservation_overbooking')) || substr($Col->getValue(), -12) == '_use_serials'){
					$Cell->setDataValidation($YesNoValidation);
				}
				elseif ($Col->getValue() == 'v_tax_class_title' || substr($Col->getValue(), -13) == '_tax_class_id'){
					$Cell->setDataValidation($TaxClassValidation);
				}
				elseif (stristr($Col->getValue(), 'time_period_type_name')){
					$Cell->setDataValidation($TimePeriodValidation);
				}
			}
		}
		$objWriter = PHPExcel_IOFactory::createWriter($this->fileObj, 'Excel5');
		$objWriter->save('php://output');
		itwExit();
	}
}