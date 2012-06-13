<?php
class OrderCreatorOrderTotalTotal extends OrderTotalTotal
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}
}
