<?php
class OrderCreatorTotalTotal extends OrderTotalTotal
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}
}
