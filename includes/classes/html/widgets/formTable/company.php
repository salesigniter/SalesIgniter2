<?php
class formTableFieldCompany extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_COMPANY'));

		$this->setName('entry_company');
		$this->setRequired(sysConfig::get('ACCOUNT_COMPANY_REQUIRED'));
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
