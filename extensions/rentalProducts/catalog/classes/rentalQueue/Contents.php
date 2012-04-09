<?php
/*
	Sales Igniter E-Commerce System Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class RentalQueueContentsIterator extends ArrayIterator {

	/**
	 * @return RentalQueueProduct
	 */
	public function current(){
		return parent::current();
	}
}

class RentalQueueContents extends ArrayObject
{

	public function __construct($input=array(), $flags=0, $iterator_class="ArrayIterator"){
		parent::__construct($input, $flags, 'RentalQueueContentsIterator');
	}

	public function fixPriorities(){
		$this->uasort(function (RentalQueueProduct $Product1, RentalQueueProduct $Product2){
			$priority1 = $Product1->getPriority();
			$prevPriority1 = $Product1->getData('prevPriority');

			$priority2 = $Product2->getPriority();
			$prevPriority2 = $Product2->getData('prevPriority');

			$cmp = 0;
			if ($priority1 == $priority2){
				if ($prevPriority1 < $prevPriority2){
					$cmp = 1;
				}
			}
			elseif ($priority1 < $priority2){
				$cmp = -1;
			}
			else{
				$cmp = 1;
			}

			return $cmp;
		});

		$newPriority = 1;
		$Contents =& $this->getIterator();
		while($Contents->valid()){
			$Product = $Contents->current();
			$Product->setData('priority', $newPriority);
			$newPriority++;
			$Contents->next();
		}
	}

	/**
	 * @return RentalQueueContentsIterator
	 */
	public function getIterator(){
		return parent::getIterator();
	}

	/**
	 * @param RentalQueueProduct $QueueProduct
	 */
	public function add(RentalQueueProduct &$QueueProduct) {
		$this->offsetSet($QueueProduct->getId(), $QueueProduct);

		$this->cleanUp();
	}

	/**
	 * @param RentalQueueProduct $QueueProduct
	 */
	public function remove(RentalQueueProduct &$QueueProduct) {
		if ($this->offsetExists($QueueProduct->getId())){
			$this->offsetUnset($QueueProduct->getId());

			$this->cleanUp();
		}
	}

	private function cleanUp() {
	}

	public function find($ProductId){
		foreach($this as $QueueProduct){
			if ($QueueProduct->getData('product_id') == $ProductId){
				return true;
			}
		}
		return false;
	}
}

?>
