<?php
/**
 * Form Element Class
 * @package Html
 */
class htmlElement_form implements htmlElementPlugin {
	protected $formElement;

	public function __construct(){
		$this->formElement = new htmlElement('form');
	}

	public function __call($function, $args){
		$return = call_user_func_array(array($this->formElement, $function), $args);
		if (!is_object($return)){
			return $return;
		}
		return $this;
	}

	/* Required Functions From Interface: htmlElementPlugin --BEGIN-- */
	public function startChain(){
		return $this;
	}

	public function setId($val){
		$this->formElement->attr('id', $val);
		return $this;
	}

	public function setName($val){
		$this->formElement->attr('name', $val);
		return $this;
	}

	public function draw(){
		return $this->formElement->draw();
	}
	/* Required Functions From Interface: htmlElementPlugin --END-- */

	public function setAction($val){
		$this->formElement->attr('action', $val);
		return $this;
	}

	public function setMethod($val){
		$this->formElement->attr('method', $val);
		return $this;
	}
}
?>