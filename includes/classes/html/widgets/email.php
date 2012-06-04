<?php
/**
 * Email Input Element Widget Class
 *
 * @package Html
 */
class htmlWidget_email implements htmlWidgetPlugin
{

	protected $inputElement;

	public function __construct() {
		$this->inputElement = htmlBase::newElement('input');
		if (
			(SesBrowserDetect::isWebkit() && SesBrowserDetect::isMinEngineVersion(528)) ||
			(SesBrowserDetect::isPresto() && SesBrowserDetect::isMinEngineVersion(2)) ||
			(SesBrowserDetect::isGecko() && SesBrowserDetect::isMinEngineVersion(2)) ||
			(SesBrowserDetect::isTrident() && SesBrowserDetect::isMinEngineVersion(6))
		){
			$this->inputElement->setType('email');
		}else{
			$this->inputElement->setType('text');
		}
		$this->inputElement->attr('data-validate', 'true');
		$this->inputElement->attr('minlength', 9);
	}

	public function setRequired($val){
		$this->inputElement->setRequired($val);

		if ($val === true){
			$this->inputElement->attr('pattern', $this->getValidationRegex());
		}else{
			$this->inputElement->removeAttr('pattern');
		}
		return $this;
	}

	public function __call($function, $args) {
		$return = call_user_func_array(array($this->inputElement, $function), $args);
		if (!is_object($return)){
			return $return;
		}
		return $this;
	}

	/* Required Functions From Interface: htmlElementPlugin --BEGIN-- */
	public function startChain() {
		return $this;
	}

	public function setId($val) {
		$this->inputElement->setId($val);
		return $this;
	}

	public function setName($val) {
		$this->inputElement->setName($val);
		return $this;
	}

	private function getValidationRegex() {
		return '^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$';
	}

	public function draw() {
		return $this->inputElement->draw();
	}
}
