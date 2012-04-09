<?php
class formTableFieldEmailaddress extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_EMAIL_ADDRESS'));

		$this->setName('entry_email_address');
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
