<?php
class formTableFieldCity extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_CITY'));

		$this->setName('entry_city');
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
