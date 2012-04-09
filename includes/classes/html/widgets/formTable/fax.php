<?php
class formTableFieldFax extends formTableField {

	public function __construct(){
		$this->setLabel('Fax: ');

		$this->setName('entry_fax');
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
