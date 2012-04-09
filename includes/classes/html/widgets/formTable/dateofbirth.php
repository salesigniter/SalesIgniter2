<?php
class formTableFieldDateofbirth extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_DATE_OF_BIRTH'));

		$this->setName('entry_date_of_birth');
		$this->setRequired(sysConfig::get('ACCOUNT_DOB_REQUIRED'));
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
