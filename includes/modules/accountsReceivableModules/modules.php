<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/**
 * Main accounts receivable modules class
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivableModules extends SystemModulesLoader
{

	/**
	 * @var string
	 */
	public static $dir = 'accountsReceivableModules';

	/**
	 * @var string
	 */
	public static $classPrefix = 'AccountsReceivableModule';

	/**
	 * @static
	 * @param      $moduleName
	 * @param bool $ignoreStatus
	 * @return AccountsReceivableModule
	 */
	public static function getModule($moduleName, $ignoreStatus = false)
	{
		return parent::getModule($moduleName, $ignoreStatus);
	}

	/**
	 * @static
	 * @param bool $includeDisabled
	 * @return AccountsReceivableModule[]
	 */
	public static function getModules($includeDisabled = false)
	{
		return parent::getModules($includeDisabled);
	}
}

/**
 * Accounts receivable modules base class
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */
class AccountsReceivableModule extends ModuleBase
{

	/**
	 * @var null
	 */
	protected $id = null;

	/**
	 * @var int
	 */
	protected $revision = 0;

	/**
	 * @var bool
	 */
	protected $assignInventory = false;

	/**
	 * @var bool
	 */
	protected $_canShowDetails = false;

	/**
	 * @var bool
	 */
	protected $_canBeCancelled = false;

	/**
	 * @var bool
	 */
	protected $_canBePrinted = false;

	/**
	 * @var bool
	 */
	protected $_canBeExported = false;

	/**
	 * @param string $code
	 * @param bool   $forceEnable
	 * @param bool   $moduleDir
	 */
	public function init($code, $forceEnable = false, $moduleDir = false)
	{
		$this->import(new Installable);

		$this->setModuleType('accountsReceivable');
		parent::init($code, $forceEnable, $moduleDir);

		$this->assignInventory = ($this->getConfigData($this->getModuleInfo('assign_inventory_key')) == 'True') ? true : false;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canShowDetails($val = null){
		if ($val !== null){
			$this->_canShowDetails = $val;
		}
		return $this->_canShowDetails;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canCancel($val = null){
		if ($val !== null){
			$this->_canBeCancelled = $val;
		}
		return $this->_canBeCancelled;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canPrint($val = null){
		if ($val !== null){
			$this->_canBePrinted = $val;
		}
		return $this->_canBePrinted;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canExport($val = null){
		if ($val !== null){
			$this->_canBeExported = $val;
		}
		return $this->_canBeExported;
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getButtonText($type)
	{
		return 'No Sale Module Found';
	}

	/**
	 * @return bool
	 */
	public function hasHistory()
	{
		return false;
	}

	/**
	 * @return string
	 */
	public function getHistoryList()
	{
		return '';
	}

	/**
	 * @param int $SaleId
	 * @param int $Revision
	 * @return Order
	 */
	public function getSale($SaleId, $Revision = 0)
	{
		$Order = new Order();
		$this->load($Order, true, $SaleId, $Revision);
		return $Order;
	}

	/**
	 * @return Doctrine_Query
	 */
	public function getSalesQuery()
	{
		return Doctrine_Query::create()
			->select('MAX(sale_revision) as sale_revision, *')
			->from('AccountsReceivableSales')
			->where('sale_module = ?', $this->getCode())
			->groupBy('sale_id')
			->orderBy('date_added desc');
	}

	/**
	 * @return bool
	 */
	public function canConvert()
	{
		$return = false;
		$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'includes/modules/accountsReceivableModules/');
		foreach($Dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isFile()){
				continue;
			}

			if (file_exists($dInfo->getPathname() . '/convert/' . $this->getCode() . '.php')){
				$return = true;
				break;
			}
		}
		return $return;
	}

	/**
	 * @return string
	 */
	public function getConvertButtons()
	{
		if (empty($this->convertTo)){
			$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'includes/modules/accountsReceivableModules/');
			foreach($Dir as $dInfo){
				if ($dInfo->isDot() || $dInfo->isFile()){
					continue;
				}

				if (file_exists($dInfo->getPathname() . '/convert/' . $this->getCode() . '.php')){
					$Module = AccountsReceivableModules::getModule($dInfo->getBasename());
					$this->convertTo[] = array(
						'code'  => $Module->getCode(),
						'title' => $Module->getTitle()
					);
				}
			}
		}

		$buttons = '';
		foreach($this->convertTo as $cInfo){
			$buttons .= htmlBase::newElement('button')
				->setType('submit')
				->setName('convertTo')
				->val($cInfo['code'])
				->usePreset('convert')
				->setText($cInfo['title'])
				->draw();
		}
		return $buttons;
	}

	/**
	 * @param null $Buttons
	 * @return array|null
	 */
	public function getPrintButtons(&$Buttons = null)
	{
		if ($Buttons === null){
			$Buttons = array();
		}

		if ($this->canBePrinted === false){
			return $Buttons;
		}

		$ModuleDir = sysConfig::getDirFsCatalog() . 'includes/modules/accountsReceivableModules/' . $this->getCode() . '/';
		if (is_dir($ModuleDir . 'print')){
			$PrintDir = new DirectoryIterator($ModuleDir . 'print');
			foreach($PrintDir as $dInfo){
				if ($dInfo->isDot() || $dInfo->isDir()){
					continue;
				}

				$ClassName = 'AccountsReceivableModules' . ucfirst($this->getCode()) . 'Print' . ucfirst($dInfo->getBasename('.php'));
				if (class_exists($ClassName) === false){
					require($dInfo->getPathname());
				}
				$Buttons[] = $ClassName::getButton();
			}
		}

		$TemplateDir = new DirectoryIterator(sysConfig::get('DIR_FS_CATALOG_TEMPLATES'));
		foreach($TemplateDir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isFile()){
				continue;
			}

			if (is_dir($dInfo->getPathname() . '/modules/accountsReceivableModules/' . $this->getCode() . '/print')){
				$PrintDir = new DirectoryIterator($dInfo->getPathname() . '/modules/accountsReceivableModules/' . $this->getCode() . '/print');
				foreach($PrintDir as $pdInfo){
					if ($pdInfo->isDot() || $pdInfo->isDir()){
						continue;
					}

					$ClassName = 'AccountsReceivableModules' . ucfirst($this->getCode()) . 'Print' . ucfirst($pdInfo->getBasename('.php'));
					if (class_exists($ClassName) === false){
						require($pdInfo->getPathname());
					}
					$Buttons[] = $ClassName::getButton();
				}
			}
		}

		return $Buttons;
	}

	/**
	 * @return mixed
	 */
	public function getSaveAsButton()
	{
		return htmlBase::newElement('button')
			->setType('submit')
			->setName('saveAs')
			->val($this->getCode())
			->usePreset('save')
			->setText($this->getTitle())
			->draw();
	}

	/**
	 * @return mixed
	 */
	public function getSaveButton()
	{
		return htmlBase::newElement('button')
			->setType('submit')
			->setName('save')
			->val($this->getCode())
			->usePreset('save')
			->setText('Save')
			->draw();
	}

	/**
	 * @return bool
	 */
	public function OwnsSale()
	{
		return (isset($_GET['sale_module']) && $_GET['sale_module'] == $this->getCode());
	}

	/**
	 * @param Order $Order
	 * @param bool  $loadManagers
	 * @param int   $saleId
	 * @param int   $revisionId
	 */
	public function load(Order &$Order, $loadManagers = true, $saleId = 0, $revisionId = 0)
	{
		if ($saleId > 0){
			$QSale = Doctrine_Query::create()
				->from('AccountsReceivableSales')
				->where('sale_id = ?', $saleId)
				->andWhere('sale_module = ?', $this->getCode());

			if ($revisionId > 0){
				$QSale->andWhere('sale_revision = ?', $revisionId);
			}
			else {
				$QSale->orderBy('sale_revision desc');
			}

			$Sale = $QSale->fetchOne();

			$this->id = $Sale->sale_id;
			$this->revision = $Sale->sale_revision;

			if ($loadManagers === true){
				$Order->setSaleModule($this);
				$Order->InfoManager->jsonDecode($Sale->info_json);
				$Order->AddressManager->jsonDecode($Sale->address_json);
				//$Order->ProductManager->jsonDecode($Sale->Products);
				$Order->TotalManager->jsonDecode($Sale->totals_json);
				$Order->InfoManager->setInfo('date_added', $Sale->date_added);
				$Order->InfoManager->setInfo('last_modified', $Sale->date_modified);
				$Order->InfoManager->setInfo('sale_id', $this->id);
				$Order->InfoManager->setInfo('revision', $Sale->sale_revision);

				foreach($Sale->Products as $Product){
					$Order->ProductManager->jsonDecodeProduct($Product);
				}
			}
		}
	}

	/**
	 * @return int
	 */
	public function getCurrentRevision()
	{
		return $this->revision;
	}

	/**
	 * @return bool
	 */
	public function hasRevisions()
	{
		return $this->revision > 0;
	}

	/**
	 * @return string
	 */
	public function getRevisionSelect()
	{
		$selectBox = htmlBase::newElement('selectbox')
			->addClass('loadRevision');
		$selectBox->addOption('0', 'Select A Revision');

		$Revisions = Doctrine_Query::create()
			->select('sale_revision, date_added')
			->from('AccountsReceivableSales')
			->where('sale_id = ?', $this->id)
			->andWhere('sale_revision <> ?', $this->revision)
			->orderBy('sale_revision asc')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		foreach($Revisions as $rInfo){
			$selectBox->addOption(
				$rInfo['sale_revision'],
				'#' . $rInfo['sale_revision'] . ' - ' . $rInfo['date_added']->format('M j, Y g:ia')
			);
		}
		return $selectBox->draw();
	}

	/**
	 * @param $convertTo
	 */
	public function convertSale($convertTo)
	{
		if (file_exists(sysConfig::getDirFsCatalog() . 'includes/modules/accountsReceivableModules/' . $convertTo . '/convert/' . $this->getCode() . '.php')){
			require(sysConfig::getDirFsCatalog() . 'includes/modules/accountsReceivableModules/' . $convertTo . '/convert/' . $this->getCode() . '.php');
			$ClassName = 'AccountsReceivableModule' . ucfirst($convertTo) . 'Convert' . ucfirst($this->getCode());
			$ClassName::convert($this->id);
		}
	}

	/**
	 * @param Order $OrderClass
	 */
	public function saveSale(Order $OrderClass)
	{
		if ($this->id === null){
			$this->id = AccountsReceivable::getNextId($this->getCode());
		}

		$Sale = new AccountsReceivableSales();
		$Sale->sale_module = $this->getCode();
		$Sale->sale_id = $this->id;
		$Sale->sale_status_id = 1;
		$Sale->customers_id = $OrderClass->InfoManager->getInfo('customers_id');
		$Sale->customers_firstname = $OrderClass->InfoManager->getInfo('customers_firstname');
		$Sale->customers_lastname = $OrderClass->InfoManager->getInfo('customers_lastname');
		$Sale->customers_email_address = $OrderClass->InfoManager->getInfo('customers_email_address');
		$Sale->sale_total = $OrderClass->TotalManager->getTotalValue('total');
		$Sale->date_added = date(DATE_TIMESTAMP);
		$Sale->sale_revision = $this->revision + 1;
		$Sale->sale_most_current = 1;
		$Sale->info_json = $OrderClass->InfoManager->jsonEncode();
		$Sale->address_json = $OrderClass->AddressManager->jsonEncode();
		//$Sale->products_json = $OrderClass->ProductManager->jsonEncode();
		$Sale->totals_json = $OrderClass->TotalManager->jsonEncode();

		$SaleProducts = $Sale->Products;
		foreach($OrderClass->ProductManager->getContents() as $OrderProduct){
			$SaleProduct = $SaleProducts->getTable()->getRecord();

			$OrderProduct->onSaveSale($SaleProduct, $this->assignInventory);

			$SaleProducts->add($SaleProduct);
		}

		//echo '<pre>';print_r($Sale->toArray(true));itwExit();

		$Sale->save();

		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&sale_module=' . $this->getCode() . '&sale_id=' . $Sale->sale_id, 'default', 'new'), 'redirect');
	}

	/**
	 * @param $PrintType
	 * @return array
	 */
	public function getPrintTemplate($PrintType)
	{
		if ($this->canBePrinted === false){
			echo 'This Cannot Be Printed';
			itwExit();
		}
		$PrintTemplate = array();
		$Code = $this->getCode();

		$requireFile = null;
		$ClassName = 'AccountsReceivableModules' . ucfirst($Code) . 'Print' . ucfirst($PrintType);
		if (file_exists($this->getPath() . 'print/' . $PrintType . '.php')){
			$requireFile = $this->getPath() . 'print/' . $PrintType . '.php';
		}
		else {
			$TemplateDir = new DirectoryIterator(sysConfig::get('DIR_FS_CATALOG_TEMPLATES'));
			foreach($TemplateDir as $tInfo){
				if ($tInfo->isDot() || $tInfo->isFile()){
					continue;
				}

				if (file_exists($tInfo->getPathname() . '/modules/accountsReceivableModules/' . $Code . '/print/' . $PrintType . '.php')){
					$requireFile = $tInfo->getPathname() . '/modules/accountsReceivableModules/' . $Code . '/print/' . $PrintType . '.php';
					break;
				}
			}
		}

		if (!empty($requireFile)){
			if (class_exists($ClassName) === false){
				require($requireFile);
			}
			$PrintTemplate = $ClassName::getPrintTemplate();
		}
		return $PrintTemplate;
	}
}