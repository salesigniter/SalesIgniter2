<?php

/**
 * ProductsAdditionalImages
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class ProductsAdditionalImages extends Doctrine_Record {

	public function setUp(){
		$this->hasOne('Products', array(
			'local' => 'products_id',
			'foreign' => 'products_id'
		));
	}
	
	public function setTableDefinition(){
		$this->setTableName('products_additional_images');

		$this->hasColumn('products_id', 'integer', 4);
		$this->hasColumn('file_name', 'string', 999);
	}
}