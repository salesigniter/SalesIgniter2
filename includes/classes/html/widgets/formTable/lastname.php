<?php
class formTableFieldLastname extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_LAST_NAME'));

		$this->setName('entry_lastname');
		$this->setRequired('true');
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
