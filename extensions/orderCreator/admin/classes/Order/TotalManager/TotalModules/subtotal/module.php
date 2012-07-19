<?php
class OrderCreatorTotalSubtotal extends OrderTotalSubtotal
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	public function loadSessionData($ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
