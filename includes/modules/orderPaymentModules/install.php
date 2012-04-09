<?php
die('DEPRECATED CLASS: OrderPaymentInstaller');
class OrderPaymentInstaller
{

	private $mInfo;

	public function __construct($moduleCode, $extName = null) {
		$this->moduleDir = sysConfig::getDirFsCatalog();
		if (is_null($extName) === false){
			$this->moduleDir .= 'extensions/' . $extName . '/orderPaymentModules/' . $moduleCode . '/';
		}
		else {
			$this->moduleDir .= 'includes/modules/orderPaymentModules/' . $moduleCode . '/';
		}
		$dataDir = $this->moduleDir . 'data/';

		$this->mInfo = simplexml_load_file(
			$dataDir . 'info.xml',
			'SimpleXMLElement',
			LIBXML_NOCDATA
		);
	}

	public function install() {
		$key = (string) $this->mInfo->installed_key;
		$code = (string) $this->mInfo->code;

		$Check = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchArray('select count(*) as total from modules m left join modules_configuration mc where m.modules_code = "' . $code . '" and mc.configuration_key = "' . $key . '"');
		if ($Check[0]['total'] <= 0){
			$moduleConfig = new Modules();
			$moduleConfig->modules_code = $code;
			$moduleConfig->modules_status = '1';
			$moduleConfig->modules_type = 'orderPayment';

			$moduleConfig->ModulesConfiguration[$key]->configuration_key = $key;
			$moduleConfig->ModulesConfiguration[$key]->configuration_value = 'True';
echo '<pre>';print_r($moduleConfig->toArray());
			$moduleConfig->save();

			/*
			 * @TODO: Translate module language files for installed languages
			 */

			$this->moduleCls->onInstall(&$this, &$moduleConfig);
		}
	}

	public function remove() {
		if ($this->moduleCls->isInstalled() === true){
			$Module = Doctrine_Core::getTable('Modules')->findOneByModulesCode((string) $this->mInfo->code);
			if ($Module){
				$Module->delete();
			}
			/*
							 * @TODO: Remove translated language files for the module
							 */
		}
	}
}

?>