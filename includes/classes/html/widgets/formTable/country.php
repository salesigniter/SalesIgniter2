<?php
class formTableFieldCountry extends formTableField {

	public function __construct(){
		$this->setLabel(sysLanguage::get('ENTRY_COUNTRY'));

		$this->setName('entry_country');
		$this->setRequired('false');
		$this->setValue('');
	}

	public function getField($type = null){
		$Field = parent::getField('selectbox');
		$Field->addOption('', 'Please Select');

		$Qcountries = Doctrine_Query::create()
			->from('Countries')
			->orderBy('countries_name')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		foreach($Qcountries as $cInfo){
			$Field->addOption($cInfo['countries_id'], $cInfo['countries_name']);
		}

		return $Field;
	}
}
