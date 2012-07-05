<?php
class OrderCreatorTotalSubtotal extends OrderTotalSubtotal
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}
}
