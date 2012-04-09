<?php
class formTableFieldVatnumber extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_VAT_NUMBER'));

		$this->setName('entry_vat_number');
		$this->setRequired(sysConfig::get('ACCOUNT_VAT_NUMBER_REQUIRED'));
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
