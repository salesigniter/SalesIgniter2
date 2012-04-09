<?php
class formTableFieldTelephone extends formTableField {

	public function __construct($fieldInfo){
		$this->setLabel(sysLanguage::get('ENTRY_TELEPHONE_NUMBER'));

		$this->setName('entry_telephone');
		$this->setRequired(sysConfig::get('ACCOUNT_TELEPHONE_REQUIRED'));
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
