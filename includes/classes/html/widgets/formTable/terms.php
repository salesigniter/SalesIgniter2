<?php
class formTableFieldTerms extends formTableField {

	public function __construct(){
		$this->setLabel('Terms And Conditions: ');

		$this->setName('entry_terms');
		$this->setRequired('true');
		$this->setValue('1');
	}

	public function getField($type = null){
		$Field = parent::getField('checkbox');

		return $Field;
	}
}
