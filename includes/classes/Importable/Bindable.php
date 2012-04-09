<?php
class Bindable extends MI_Importable
{

	public $boundMethods = false;

	public function bindMethod($functionName, Closure $function){
		$this->boundMethods[$functionName] = $function;
	}

	public function hasBoundMethod($functionName){
		return isset($this->boundMethods[$functionName]);
	}

	public function executeBoundMethod($methodName, $args){
		return call_user_func_array($this->boundMethods[$methodName], array_merge(array($this->base), $args));
	}
}

