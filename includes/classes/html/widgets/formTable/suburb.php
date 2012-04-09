<?php
class formTableFieldSuburb extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_SUBURB'));

		$this->setName('entry_suburb');
		$this->setRequired(sysConfig::get('ACCOUNT_SUBURB_REQUIRED'));
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
