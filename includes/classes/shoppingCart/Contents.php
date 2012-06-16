<?php
/*
	Rental Store Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ShoppingCartContentsIterator extends ArrayIterator {

	/**
	 * @return ShoppingCartProduct
	 */
	public function current(){
		return parent::current();
	}
}

class ShoppingCartContents extends ArrayObject
{

	public function __construct($input=null, $flags=0, $iterator_class="ArrayIterator"){
		parent::__construct($input, $flags, 'ShoppingCartContentsIterator');
	}

	/**
	 * @return ShoppingCartContentsIterator
	 */
	public function getIterator(){
		return parent::getIterator();
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 */
	public function add(ShoppingCartProduct &$CartProduct) {
		$this->offsetSet($CartProduct->getId(), $CartProduct);

		$this->cleanUp();
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 * @return bool
	 */
	public function remove(ShoppingCartProduct &$CartProduct) {
		$success = false;
		if ($this->offsetExists($CartProduct->getId())){
			$this->offsetUnset($CartProduct->getId());

			$success = ($this->offsetExists($CartProduct->getId()) === false);
			if ($success === true){
				$this->cleanUp();
			}
		}
		return $success;
	}

	private function cleanUp() {
		foreach($this as $CartProduct){
			if ($CartProduct->getQuantity() < 1){
				$this->remove($CartProduct);
			}
		}
	}
}

?>