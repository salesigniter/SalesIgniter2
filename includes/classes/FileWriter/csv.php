<?php
require(SysConfig::getDirFsCatalog() . 'includes/classes/FileWriter/csv/row.php');
require(SysConfig::getDirFsCatalog() . 'includes/classes/FileWriter/csv/col.php');

class FileWriterCsv
{

	private $fileObj;

	private $headerRow;

	private $rows = array();

	public function __construct($filename, $open_mode = "r", $use_include_path = false, $context = false) {
		if ($filename == 'temp'){
			$this->fileObj = new SplTempFileObject();
		}
		elseif ($context === false){
			$this->fileObj = new SplFileObject($filename, $open_mode, $use_include_path);
		}
		else {
			$this->fileObj = new SplFileObject($filename, $open_mode, $use_include_path, $context);
		}
		$this->fileObj->setCsvControl(',');
		$this->fileObj->setFlags(SplFileObject::READ_CSV);
		//$this->fwrite(pack("CCC",0xef,0xbb,0xbf)); /*patch for utf-8 files*/
	}

	public function setCsvControl($val){
		$this->fileObj->setCsvControl($val);
	}

	public function newHeaderRow(){
		$NewRow = new FileWriterCsvRow();

		$this->headerRow = $NewRow;

		return $NewRow;
	}

	public function &newRow() {
		$NewRow = new FileWriterCsvRow($this->headerRow->getRefArray());

		$this->rows[] = $NewRow;

		return $NewRow;
	}

	public function output(){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"CsvExport-" . date('Y_m_d') . ".csv\";" );
		header("Content-Transfer-Encoding: binary");

		$cInfo = $this->fileObj->getCsvControl();

		$headerColumns = $this->headerRow->getColumns();
		foreach($headerColumns as $k => $Col){
			echo '' . $cInfo[1] . '' . $Col->getValue() . '' . $cInfo[1] . '';
			if (isset($headerColumns[$k+1])){
				echo $cInfo[0];
			}
		}
		echo "\n";

		foreach($this->rows as $row){
			foreach($headerColumns as $k => $Col){
				$RowCol = $row->getColumn($Col->getValue());
				if ($RowCol === false){
					//echo '' . $cInfo[1] . 'ERROR: Column Not Set ( ' . $Col->getValue() . ' ) ' . $cInfo[1] . '';
					echo '' . $cInfo[1] . $cInfo[1] . '';
				}else{
					echo '' . $cInfo[1] . '' . str_replace('"', '""', $RowCol->getValue()) . '' . $cInfo[1] . '';
				}
				if (isset($headerColumns[$k+1])){
					echo $cInfo[0];
				}
			}
			echo "\n";
		}
		itwExit();
	}
}