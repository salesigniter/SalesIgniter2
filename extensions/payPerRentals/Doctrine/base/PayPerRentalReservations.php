<?php

class PayPerRentalReservations extends Doctrine_Record {

	public function setUp(){
		$this->hasOne('AccountsReceivableSalesProducts as SaleProduct', array(
			'local'   => 'sale_product_id',
			'foreign' => 'id'
		));
		$this->hasOne('Products as Product', array(
			'local'   => 'products_id',
			'foreign' => 'products_id'
		));
	}

	public function setTableDefinition(){
		$this->setTableName('pay_per_rental_reservation');

		$this->hasColumn('products_id', 'integer', 4);
		$this->hasColumn('sale_product_id', 'integer', 4);

		$this->hasColumn('rental_status_id', 'integer', 4);

		$this->hasColumn('start_date', 'timestamp');
		$this->hasColumn('end_date', 'timestamp');

		$this->hasColumn('event_date', 'timestamp');
		$this->hasColumn('event_name', 'string', 255);
		$this->hasColumn('event_gate', 'string', 255);
		$this->hasColumn('semester_name', 'string', 255);

		$this->hasColumn('rental_state', 'string', 32);

		$this->hasColumn('date_shipped', 'timestamp');
		$this->hasColumn('date_returned', 'timestamp');

		$this->hasColumn('shipping_days_before', 'integer', 4);
		$this->hasColumn('shipping_days_after', 'integer', 4);
		$this->hasColumn('shipping_method', 'string', 64);
		$this->hasColumn('shipping_method_title', 'string', 128);
		$this->hasColumn('shipping_cost', 'decimal', 15, array(
			'scale' => 4,
		));

		$this->hasColumn('tracking_number', 'string', 255);
		$this->hasColumn('tracking_type', 'string', 30);

		$this->hasColumn('insurance', 'decimal', 15, array(
			'scale' => 4,
		));
		$this->hasColumn('amount_payed', 'decimal', 15, array(
			'scale' => 4,
		));

		$this->hasColumn('rental_terms', 'string', 999);
	}
}
?>