<?php
class Extension_orderCreator extends ExtensionBase {

	public function __construct(){
		parent::__construct('orderCreator');
	}

	public function preSessionInit(){
		$this->removeSession = true;
		if (isset($_GET['appExt']) && $_GET['appExt'] == 'orderCreator'){
			if (!isset($_GET['action']) && !isset($_POST['action']) && !isset($_GET['error'])){
				$this->removeSession = true;
			}else{
				$this->removeSession = false;
			}
			
			/* 
			 * Require any core classes
			 */
			require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/Base.php');
			
			/*
			 * Require any extension specific classes
			 */
			require(dirname(__FILE__) . '/admin/classes/Order/Base.php');
		}else{
			EventManager::attachEvents(array(
				'SessionBeforeReadValue'
			), null, $this);
		}
	}

	public function SessionBeforeReadValue(&$value){
		if (stristr($value, 'OrderCreator')){
			$value = preg_replace('/OrderCreator\|(.*)(}}|N)/', '', $value);
		}
	}

	public function postSessionInit(){
		if (Session::exists('OrderCreator')){
			if (basename($_SERVER['PHP_SELF']) != 'stylesheet.php' && basename($_SERVER['PHP_SELF']) != 'javascript.php'){
				if (isset($this->removeSession) && $this->removeSession === true){
					Session::remove('OrderCreator');
				}
			}
		}
	}
	
	public function init(){
		global $appExtension;
		if ($this->isEnabled() === false) return;
	}
}
?>