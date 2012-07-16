<?php
class ProductTypeGiftVoucher extends ProductTypeBase
{

	private $_moduleCode = 'giftVoucher';

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Gift Voucher Product Type');
		$this->setDescription('Gift Voucher Product Type');

		$this->init($this->_moduleCode);
	}
}
