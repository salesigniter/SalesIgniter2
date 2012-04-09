<?php
require(dirname(__FILE__) . '/Abstract.php');

class OrderTotalModules extends SystemModulesLoader {
	public static $dir = 'orderTotalModules';
	public static $classPrefix = 'OrderTotal';
	private static $TotalsData = array();

	/**
	 * @static
	 * @param string $moduleName
	 * @param bool $ignoreStatus
	 * @return OrderTotalModuleBase
	 */
	public static function getModule($moduleName, $ignoreStatus = false){
		return parent::getModule($moduleName, $ignoreStatus);
	}

	/**
	 * @static
	 * @return array
	 */
	public static function process(){
		if (self::hasModules() === true){
			$enabledModules = array();
			foreach(self::getModules() as $moduleName => $moduleClass){
				if ($moduleClass->isEnabled() === true){
					$enabledModules[] = $moduleClass;
				}
			}

			usort($enabledModules, function ($a, $b){
				return ($a->getDisplayOrder() < $b->getDisplayOrder() ? -1 : 1);
			});

			$TotalsData = array();
			foreach($enabledModules as $Module){
				$oInfo = array(
					'module_type' => $Module->getModuleType(),
					'code'        => $Module->getCode(),
					'module'      => null,
					'method'      => null,
					'title'       => null,
					'text'        => null,
					'value'       => null,
					'sort_order'  => $Module->getDisplayOrder()
				);

				$Module->process(&$oInfo);

				if (is_null($oInfo['title']) === false && !empty($oInfo['title'])){
					$TotalsData[] = $oInfo;
				}
			}
			self::$TotalsData = $TotalsData;
			return $TotalsData;
		}
		return array();
	}

	/*public static function process() {
		$orderTotalArray = array();
		$enabledModules = array();
		$enabledModulesName = array();
		$enabledModulesId = array();
		if (self::hasModules() === true) {
			foreach(self::getModules() as $moduleName => $moduleClass){
				if ($moduleClass->isEnabled() === true){
					$enabledModulesId[] = (int)$moduleClass->getDisplayOrder();
					$enabledModules[] = $moduleClass;
					$enabledModulesName[] = $moduleName;
				}
			}
			array_multisort($enabledModulesId, $enabledModules, $enabledModulesName);
			$pos = 0;
			foreach ($enabledModules as $moduleClass){
				$moduleClass->process();
				$moduleOutput = $moduleClass->getOutput();
				for ($i = 0, $n = sizeof($moduleOutput); $i < $n; $i++) {
					if (tep_not_null($moduleOutput[$i]['title']) && tep_not_null($moduleOutput[$i]['text'])) {
						$oInfo = array(
							'module_type' => $enabledModulesName[$pos],
							'code' => $moduleClass->getCode(),
							'module' => null,
							'method' => null,
							'title' => $moduleOutput[$i]['title'],
							'text' => $moduleOutput[$i]['text'],
							'value' => $moduleOutput[$i]['value'],
							'sort_order' => $moduleClass->getDisplayOrder()
						);

						$moduleClass->onOutputProcess(&$oInfo);

						$orderTotalArray[] = $oInfo;
					}
				}
				$pos++;
			}
		}
		return $orderTotalArray;
	}*/

	/**
	 * @static
	 * @param string $type
	 * @return array|string
	 */
	public static function output($type = 'html') {
		if (empty(self::$TotalsData)) die('Must call the process function before output can be called');

		if ($type == 'json'){
			$outputString = array();
			foreach(self::$TotalsData as $tInfo){
				$outputString[] = array(
					$tInfo['title'] . (isset($tInfo['help']) ? ' (<a href=\"' . $tInfo['help'] . '\" onclick=\"popupWindow(\'' . $tInfo['help'] . '\',\'300\',\'300\');return false;\">?</a>)' : ''),
					$tInfo['text']
				);
			}
		}else{
			$outputString = '';
			foreach(self::$TotalsData as $tInfo){
				$outputString .= '<tr>
	                           <td align="right" class="main">' . $tInfo['title'] . '</td>
	                           <td align="right" class="main">' . $tInfo['text'] . '</td>
	                          </tr>';
			}
		}
		return $outputString;
	}
}
?>