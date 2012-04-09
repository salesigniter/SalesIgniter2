<?php
class formTableFieldState extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_STATE'));

		$this->setName('entry_state');
		$this->setRequired(sysConfig::get('ACCOUNT_STATE_REQUIRED'));
		$this->setMinLength(0);
		$this->setMaxLength(32);
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('input');

		return $Field;
	}
}
