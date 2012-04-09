<?php
class formTableFieldPostcode extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_POST_CODE'));

		$this->setName('entry_postcode');
		$this->setRequired('false');
		$this->setMinLength(0);
		$this->setMaxLength(5);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
