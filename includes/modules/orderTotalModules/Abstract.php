<?php
/**
 *
 */
class OrderTotalModuleBase extends ModuleBase
{
	/**
	 * @var array|null
	 */
	protected $data = array(
		'value' => 0
	);

	/**
	 * @param string $code
	 * @param bool   $forceEnable
	 * @param bool   $moduleDir
	 */
	public function init($code, $forceEnable = false, $moduleDir = false) {
		$this->import(new Installable);
		$this->import(new SortedDisplay);

		$this->setModuleType('orderTotal');
		parent::init($code, $forceEnable, $moduleDir);


		if ($this->configExists($this->getModuleInfo('display_order_key'))){
			$this->setDisplayOrder((int)$this->getConfigData($this->getModuleInfo('display_order_key')));
		}
	}

	/**
	 * @param $data
	 */
	public function setData($data){
		$this->data = $data;
	}

	/**
	 * @param      $k
	 * @param null $v
	 */
	public function updateData($k, $v = null){
		if (is_array($k)){
			foreach($k as $key => $value){
				$this->data[$key] = $value;
			}
		}else{
			$this->data[$k] = $v;
		}
	}

	/**
	 * @param $k
	 * @return mixed
	 */
	public function getData($k){
		return $this->data[$k];
	}

	/**
	 * @param array $outputData
	 */
	public function process(array &$outputData) {
		die('Process function not overwritten.');
	}

	/**
	 * @return mixed
	 */
	public function getValue(){
		return $this->data['value'];
	}

	/**
	 * @param $val
	 */
	public function setValue($val){
		$this->data['value'] = $val;
	}

	/**
	 *
	 */
	public function getText(){
		die(__FUNCTION__ . ' not overwritten.');
	}

	public function prepareJsonSave(){
		return $this->data;
	}

	public function jsonDecode(array $data){
		$this->data = $data;
		$this->setDisplayOrder($this->data['sort_order']);
	}
}

?>