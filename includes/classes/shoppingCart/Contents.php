<?php
/*
	Rental Store Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ShoppingCartContents extends ArrayObject
{

	public function add(ShoppingCartProduct &$CartProduct) {
		$this->offsetSet($CartProduct->getId(), $CartProduct);

		$this->cleanUp();
	}

	public function remove(ShoppingCartProduct &$CartProduct) {
		if ($this->offsetExists($CartProduct->getId())){
			$this->offsetUnset($CartProduct->getId());

			$this->cleanUp();
		}
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