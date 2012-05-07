<?php
class htmlWidget {

	protected $element;

	public function __call($function, $args) {
		$return = call_user_func_array(array($this->element, $function), $args);
		if (!is_object($return)){
			return $return;
		}
		return $this;
	}

	public function draw(){
		return $this->element->draw();
	}

	public function setId($val){
		$this->element->attr('id', $val);
		return $this;
	}

	public function setName($val){
		$this->element->attr('name', $val);
		return $this;
	}

	public function startChain(){
		return $this;
	}
}
