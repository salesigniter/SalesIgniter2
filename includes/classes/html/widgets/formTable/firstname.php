<?php
class formTableFieldFirstname extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_FIRST_NAME'));

		$this->setName('entry_firstname');
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
