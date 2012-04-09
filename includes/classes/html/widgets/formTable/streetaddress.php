<?php
class formTableFieldStreetaddress extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_STREET_ADDRESS'));

		$this->setName('entry_street_address');
		$this->setRequired('false');
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
