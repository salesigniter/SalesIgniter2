<?php
/**
 * File Manager Widget Class
 * @package Html
 */
class htmlWidget_fileManager implements htmlWidgetPlugin {
	protected $inputElement;

	public function __construct(){
		$this->inputElement = htmlBase::newElement('input')
			->setType('text')
			->addClass('fileManagerInput');

		if (APPLICATION_ENVIRONMENT == 'admin'){
			$this->setDirectory(sysConfig::get('DIR_FS_CATALOG_TEMPLATES'));
		}
	}

	public function __call($function, $args){
		$return = call_user_func_array(array($this->inputElement, $function), $args);
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
		$this->inputElement->setId($val);
		return $this;
	}

	public function setName($val){
		$this->inputElement->setName($val);
		return $this;
	}

	public function draw(){
		return $this->inputElement->draw();
	}
	/* Required Functions From Interface: htmlElementPlugin --END-- */

	public function setDirectory($val){
		$this->inputElement->attr('data-files_source', $val);
		return $this;
	}

	public function allowMultiple($val){
		$this->inputElement->attr('data-is_multiple', $val);
		return $this;
	}

	public function setAllowedExtensions($val){
		$this->inputElement->attr('data-allowed_extensions', $val);
		return $this;
	}

	public function setDataUrl($val){
		$this->inputElement->attr('data-data_url', $val);
		return $this;
	}

	public function setDefaultListing($val){
		$this->inputElement->attr('data-listing_type', $val);
		return $this;
	}
}
?>