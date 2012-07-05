<?php
class OrderCreatorTotalTax extends OrderTotalTax
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}
}
