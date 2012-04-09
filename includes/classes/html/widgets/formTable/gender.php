<?php
class formTableFieldGender extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_GENDER'));

		$this->setName('entry_gender');
		$this->setRequired(sysConfig::get('ACCOUNT_GENDER_REQUIRED'));
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('selectbox');
		$Field->addOption('', 'Please Select');
		$Field->addOption('m', 'Male');
		$Field->addOption('f', 'Female');

		return $Field;
	}
}
