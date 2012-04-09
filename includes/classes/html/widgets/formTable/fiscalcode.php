<?php
class formTableFieldFiscalcode extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_FISCAL_CODE'));

		$this->setName('entry_fiscal_code');
		$this->setRequired(sysConfig::get('ACCOUNT_FISCAL_CODE_REQUIRED'));
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
