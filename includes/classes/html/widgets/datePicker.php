<?php
/**
 * Date Picker Input Element Widget Class
 *
 * @package Html
 */
class htmlWidget_datePicker extends htmlElement_input implements htmlWidgetPlugin
{

	public function __construct() {
		parent::__construct();
		if (
			(SesBrowserDetect::isWebkit() && SesBrowserDetect::isMinEngineVersion(550)) ||
			(SesBrowserDetect::isPresto() && SesBrowserDetect::isMinEngineVersion(3))/* ||
			(SesBrowserDetect::isGecko() && SesBrowserDetect::isMinEngineVersion(2)) ||
			(SesBrowserDetect::isTrident() && SesBrowserDetect::isMinEngineVersion(6))*/
		){
			$this->setType('date');
		}else{
			$this->setType('text');
			$this->addClass('makeDatepicker');
		}
		$this->attr('data-validate', 'true');
		//$this->attr('minlength', 9);
	}

	public function setRequired($val){
		parent::setRequired($val);

		if ($val === true){
			$this->attr('pattern', $this->getValidationRegex());
		}else{
			$this->removeAttr('pattern');
		}
		return $this;
	}

	/* Required Functions From Interface: htmlElementPlugin --BEGIN-- */
	private function getValidationRegex() {
		return '^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$';
	}
}
