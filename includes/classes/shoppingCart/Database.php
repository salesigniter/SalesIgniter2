<?php
/*
	Rental Store Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

	class ShoppingCartDatabaseActions {
		
		public function __construct(){
		}
		
		public function runAction($action, ShoppingCart $ShoppingCart){
			$userAccount =& Session::getReference('userAccount');
			if ($userAccount->isLoggedIn() === true){
				Doctrine_Query::create()
					->update('CustomersBasket')
					->set('cart_data', '?', $ShoppingCart->serialize())
					->where('customers_id = ?', $userAccount->getCustomerId())
					->execute();
			}
		}

		public function getCartFromDatabase(ShoppingCart $ShoppingCart){
			$userAccount =& Session::getReference('userAccount');
			$CartData = Doctrine_Query::create()
				->select('cart_data')
				->from('CustomersBasket')
				->where('customers_id = ?', $userAccount->getCustomerId())
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			if ($CartData){
				$ShoppingCart->unserialize($CartData);
			}
		}
	}
?>