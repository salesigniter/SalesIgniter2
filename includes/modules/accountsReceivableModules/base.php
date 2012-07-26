<?php
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
	protected $_assignInventory = false;

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
	 * @var bool
	 */
	protected $_canBeDuplicated = false;

	/**
	 * @var bool
	 */
	protected $_acceptsPayments = false;

	/**
	 * @var array
	 */
	protected $convertTo = array();

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

		$this->assignInventory(($this->getConfigData($this->getModuleInfo('assign_inventory_key')) == 'True'));
		$this->canShowDetails(($this->getConfigData($this->getModuleInfo('show_details_key')) == 'True'));
		$this->canCancel(($this->getConfigData($this->getModuleInfo('can_cancel_key')) == 'True'));
		$this->canPrint(($this->getConfigData($this->getModuleInfo('can_print_key')) == 'True'));
		$this->canExport(($this->getConfigData($this->getModuleInfo('can_export_key')) == 'True'));
		$this->canDuplicate(($this->getConfigData($this->getModuleInfo('can_duplicate_key')) == 'True'));
		$this->acceptsPayments(($this->getConfigData($this->getModuleInfo('accepts_payments_key')) == 'True'));
	}

	/**
	 * @param null $val
	 * @return mixed
	 */
	public function assignInventory($val = null)
	{
		if ($val !== null){
			$this->_assignInventory = $val;
		}
		return $this->_assignInventory;
	}

	/**
	 * @param null $val
	 * @return mixed
	 */
	public function acceptsPayments($val = null)
	{
		if ($val !== null){
			$this->_acceptsPayments = $val;
		}
		return $this->_acceptsPayments;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canShowDetails($val = null)
	{
		if ($val !== null){
			$this->_canShowDetails = $val;
		}
		return $this->_canShowDetails;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canCancel($val = null)
	{
		if ($val !== null){
			$this->_canBeCancelled = $val;
		}
		return $this->_canBeCancelled;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canPrint($val = null)
	{
		if ($val !== null){
			$this->_canBePrinted = $val;
		}
		return $this->_canBePrinted;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canExport($val = null)
	{
		if ($val !== null){
			$this->_canBeExported = $val;
		}
		return $this->_canBeExported;
	}

	/**
	 * @param null $val
	 * @return bool
	 */
	public function canDuplicate($val = null)
	{
		if ($val !== null){
			$this->_canBeDuplicated = $val;
		}
		return $this->_canBeDuplicated;
	}

	/**
	 * @return null
	 */
	public function getSaleId()
	{
		return $this->id;
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
		if (class_exists('Order') === false){
			require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/Base.php');
		}
		$Order = new Order();
		$this->load($Order, true, $SaleId, $Revision);
		return $Order;
	}

	/**
	 * @param null $SaleId
	 * @param null $SaleRevision
	 * @return Doctrine_Query
	 */
	public function getSalesQuery($SaleId = null, $SaleRevision = null)
	{
		$Query = Doctrine_Query::create()
			->select('MAX(sale_revision) as sale_revision, *')
			->from('AccountsReceivableSales')
			->where('sale_module = ?', $this->getCode())
			->groupBy('sale_id')
			->orderBy('date_added desc');

		if ($SaleId !== null){
			$Query->andWhere('sale_id = ?', $SaleId);
		}

		if ($SaleRevision !== null){
			$Query->andWhere('sale_revision = ?', $SaleRevision);
		}

		return $Query;
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
				$Module = AccountsReceivableModules::getModule($dInfo->getBasename());
				if ($Module && $Module->isEnabled()){
					$return = true;
					break;
				}
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

	public function getConvertOptions()
	{
		if (empty($this->convertTo)){
			$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'includes/modules/accountsReceivableModules/');
			foreach($Dir as $dInfo){
				if ($dInfo->isDot() || $dInfo->isFile()){
					continue;
				}

				if (file_exists($dInfo->getPathname() . '/convert/' . $this->getCode() . '.php')){
					$Module = AccountsReceivableModules::getModule($dInfo->getBasename());
					if ($Module && $Module->isEnabled()){
						$this->convertTo[] = array(
							'code'  => $Module->getCode(),
							'title' => $Module->getTitle()
						);
					}
				}
			}
		}
		return $this->convertTo;
	}

	public function getPrintOptions()
	{
		$Buttons = array();
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
				$Buttons[] = $ClassName::getModuleInfo();
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
					$Buttons[] = $ClassName::getModuleInfo();
				}
			}
		}

		return $Buttons;
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
		return $this->revision > 1;
	}

	public function getRevisions()
	{
		$return = array();
		$Revisions = Doctrine_Query::create()
			->select('sale_revision, date_added')
			->from('AccountsReceivableSales')
			->where('sale_id = ?', $this->id)
			->andWhere('sale_revision <> ?', $this->revision)
			->orderBy('sale_revision asc')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		foreach($Revisions as $rInfo){
			$return[] = array(
				'id'         => $rInfo['sale_revision'],
				'date_added' => $rInfo['date_added'],
				'text'       => '#' . $rInfo['sale_revision'] . ' - ' . $rInfo['date_added']->format('M j, Y g:ia')
			);
		}
		return $return;
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
	 * @param Order $Order
	 * @param bool  $loadManagers
	 * @param int   $saleId
	 * @param int   $revisionId
	 */
	public function load(Order &$Order, $loadManagers = true, $saleId = 0, $revisionId = null)
	{
		$Order->setSaleModule($this);
		if ($saleId > 0){
			$QSale = Doctrine_Query::create()
				->from('AccountsReceivableSales')
				->where('sale_id = ?', $saleId)
				->andWhere('sale_module = ?', $this->getCode());

			if ($revisionId !== null){
				$QSale->andWhere('sale_revision = ?', $revisionId);
			}
			else {
				$QSale->orderBy('sale_revision desc');
			}

			$Sale = $QSale->fetchOne();

			$this->id = $Sale->sale_id;
			$this->revision = $Sale->sale_revision;

			$Order->setSaleId($this->id);

			if ($loadManagers === true){
				$Order->statusId = $Sale->sale_status_id;

				$Order->AddressManager->loadDatabaseData($Sale->address_json);
				$Order->TotalManager->loadDatabaseData($Sale->Totals);
				$Order->ProductManager->loadDatabaseData($Sale->Products);
				$Order->InfoManager->loadDatabaseData($Sale->info_json);

				$Order->InfoManager->setInfo('customers_firstname', $Sale->customers_firstname);
				$Order->InfoManager->setInfo('customers_lastname', $Sale->customers_lastname);
				$Order->InfoManager->setInfo('date_added', $Sale->date_added);
				$Order->InfoManager->setInfo('last_modified', $Sale->date_modified);
				$Order->InfoManager->setInfo('sale_id', $this->id);
				$Order->InfoManager->setInfo('revision', $Sale->sale_revision);
				$Order->PaymentManager->setPaymentModule('fdggc2');
			}
		}
	}

	/**
	 * @param Order $Order
	 * @return int
	 */
	public function duplicateSale(Order $Order)
	{
		$this->id = AccountsReceivable::getNextId($this->getCode());

		$this->revision = 1;

		$Sale = new AccountsReceivableSales();
		$Sale->sale_id = $this->id;
		$Sale->sale_module = $this->getCode();
		$Sale->date_added = date(DATE_TIMESTAMP);
		$Sale->customers_id = $SaleClass->InfoManager->getInfo('customers_id');
		$Sale->customers_firstname = $SaleClass->AddressManager
			->getAddress('customer')
			->getFirstName();
		$Sale->customers_lastname = $SaleClass->AddressManager
			->getAddress('customer')
			->getLastName();
		$Sale->customers_email_address = $SaleClass->InfoManager->getInfo('customers_email_address');
		$Sale->sale_total = $SaleClass->TotalManager->getTotalValue('total');
		$Sale->sale_status_id = 1;
		$Sale->sale_revision = 1;
		$Sale->sale_most_current = 1;
		$Sale->info_json = $SaleClass->InfoManager->prepareSave();
		$Sale->address_json = $SaleClass->AddressManager->prepareSave();

		$SaleClass->TotalManager->onSaveSale($Sale->Totals);
		$SaleClass->ProductManager->onSaveSale($Sale->Products, false);

		//echo '<pre>';print_r($Sale->toArray(true));itwExit();

		$Sale->save();

		$SaleClass->setSaleId($Sale->sale_id);

		return (int)$Sale->sale_id;
	}

	/**
	 * @param Order $SaleClass
	 */
	public function saveProgress(Order $SaleClass)
	{
		if ($this->getCurrentRevision() > 0){
			return;
		}
		$Sales = Doctrine_Core::getTable('AccountsReceivableSales');
		if ($SaleClass->getSaleId() <= 0){
			$id = AccountsReceivable::getNextId($this->getCode());
			$SaleClass->setSaleId($id);

			$Sale = $Sales->create();
			$Sale->sale_id = $id;
			$Sale->sale_revision = 0;
			$Sale->sale_most_current = 1;
			$Sale->sale_module = $this->getCode();
		}
		else {
			$Sale = $Sales->findOneBySaleIdAndSaleModule($SaleClass->getSaleId(), $this->getCode());
		}

		$Sale->customers_id = $SaleClass->InfoManager->getInfo('customers_id');
		$Sale->customers_firstname = $SaleClass->InfoManager->getInfo('customers_firstname');
		$Sale->customers_lastname = $SaleClass->InfoManager->getInfo('customers_lastname');
		$Sale->customers_email_address = $SaleClass->InfoManager->getInfo('customers_email_address');
		$Sale->sale_total = $SaleClass->TotalManager->getTotalValue('total');
		$Sale->date_added = date(DATE_TIMESTAMP);
		$Sale->info_json = $SaleClass->InfoManager->prepareSave();
		$Sale->address_json = $SaleClass->AddressManager->prepareSave();

		$SaleClass->TotalManager->onSaveProgress($Sale->Totals);
		$SaleClass->ProductManager->onSaveProgress($Sale->Products);

		//echo '<pre>';print_r($Sale->toArray(true));itwExit();

		$Sale->save();
	}

	/**
	 * @param Order $SaleClass
	 * @return int
	 */
	public function saveSale(Order &$SaleClass)
	{
		if ($this->revision === 0 && $this->id !== null){
			$QSale = AccountsReceivable::getSalesQuery(
				$this->getCode(),
				$this->id,
				$this->revision
			)->execute();
			$Sale = $QSale[0];
		}
		else {
			if ($this->id === null){
				$this->id = AccountsReceivable::getNextId($this->getCode());
			}

			$Sale = new AccountsReceivableSales();
			$Sale->sale_id = $this->id;
			$Sale->sale_module = $this->getCode();
		}

		$this->revision++;

		$Sale->date_added = date(DATE_TIMESTAMP);
		$Sale->customers_id = $SaleClass->InfoManager->getInfo('customers_id');
		$Sale->customers_firstname = $SaleClass->AddressManager
			->getAddress('customer')
			->getFirstName();
		$Sale->customers_lastname = $SaleClass->AddressManager
			->getAddress('customer')
			->getLastName();
		$Sale->customers_email_address = $SaleClass->InfoManager->getInfo('customers_email_address');
		$Sale->sale_total = $SaleClass->TotalManager->getTotalValue('total');
		$Sale->sale_status_id = $SaleClass->InfoManager->getInfo('sale_status');
		$Sale->sale_revision = $this->revision;
		$Sale->sale_most_current = 1;
		$Sale->info_json = $SaleClass->InfoManager->prepareSave();
		$Sale->address_json = $SaleClass->AddressManager->prepareSave();

		$SaleClass->TotalManager->onSaveSale($Sale->Totals);
		$SaleClass->ProductManager->onSaveSale($Sale->Products, $this->assignInventory());

		//echo '<pre>';print_r($Sale->toArray(true));itwExit();

		$Sale->save();

		$SaleClass->setSaleId($Sale->sale_id);

		return (int)$Sale->sale_id;
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

		$PrintTemplates = array();
		$Qtemplates = Doctrine_Query::create()
			->from('TemplateManagerLayouts')
			->where('page_type = ?', 'salesPdf')
			->execute();
		if ($Qtemplates->count() > 0){
			foreach($Qtemplates as $Template){
				//echo '<pre>';print_r($Template->layout_settings);
				if (
					is_array($Template->layout_settings) &&
					is_array($Template->layout_settings['saleModules']) &&
					isset($Template->layout_settings['saleModules'][$this->getCode()]) &&
					in_array($PrintType, $Template->layout_settings['saleModules'][$this->getCode()])
				){
					$PrintTemplates[] = $Template;
				}
			}
		}

		if (sizeof($PrintTemplates) > 1){
			echo 'There are multiple templates attached, please select one';
			itwExit();
		}
		elseif (sizeof($PrintTemplates) == 0) {
			echo 'There are no templates assigned for sales pdf printing!.';
			itwExit();
		}

		return $PrintTemplates[0];
	}
}