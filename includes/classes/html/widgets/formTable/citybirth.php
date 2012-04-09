<?php
class formTableFieldCitybirth extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_CITY_BIRTH'));

		$this->setName('entry_city_birth');
		$this->setRequired(sysConfig::get('ACCOUNT_CITY_BIRTH_REQUIRED'));
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
