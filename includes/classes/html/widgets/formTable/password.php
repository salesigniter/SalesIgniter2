<?php
class formTableFieldPassword extends formTableField {

	public function __construct(){
		$this->setLabel('Password: ');

		$this->setName('entry_password');
		$this->setRequired('false');
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');
		$Field->setType('password');
		$Field->val('');

		return $Field;
	}
}
