<?php
class PurchaseTypeTabDownload_tab_files
{

	private $heading;

	private $displayOrder = 3;

	public function __construct() {
		$this->setHeading(sysLanguage::get('TAB_PURCHASE_TYPE_HEADING_DOWNLOAD_FILES'));
	}

	public function getDisplayOrder() {
		return $this->displayOrder;
	}

	public function setDisplayOrder($val) {
		$this->displayOrder = $val;
	}

	public function setHeading($val) {
		$this->heading = $val;
	}

	public function getHeading() {
		return $this->heading;
	}

	public function addTab(&$TabsObj, Product $Product, $PurchaseType) {
		$Table = htmlBase::newElement('table')
			->setCellPadding(3)
			->setCellSpacing(0)
			->addClass('ui-widget ui-widget-content downloadsTable')
			->css(array(
				'width' => '98%'
			));

		$headerColumns = array(
			array('align' => 'left', 'text' => sysLanguage::get('TABLE_HEADING_DOWNLOAD_PROVIDER')),
			array('text' => sysLanguage::get('TABLE_HEADING_DOWNLOAD_TYPE')),
			array('align' => 'left', 'text' => sysLanguage::get('TABLE_HEADING_DOWNLOAD_FILE')),
			array('align' => 'left', 'text' => sysLanguage::get('TABLE_HEADING_DOWNLOAD_DISPLAY_NAME'))
		);

		EventManager::notifyWithReturn('NewProductDownloadsTableAddHeaderCol', &$headerColumns);

		$headerColumns[] = array(
			'text' => '&nbsp;'
		);

		$Qproviders = Doctrine_Query::create()
			->from('ProductsDownloadProviders')
			->orderBy('provider_name')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$providerBox = htmlBase::newElement('selectbox')
			->setName('new_download_provider')
			->addClass('selectDownloadProvider')
			->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));
		if ($Qproviders){
			foreach($Qproviders as $providerInfo){
				$providerBox->addOption($providerInfo['provider_id'], $providerInfo['provider_name']);
			}
		}

		$fileNameInput = htmlBase::newElement('input')
			->addClass('providerFileName')
			->setName('new_download_file_name');

		$displayNameInput = htmlBase::newElement('input')
			->addClass('providerDisplayName')
			->setName('new_download_display_name');

		$inputRow = array(
			array('text' => $providerBox->draw()),
			array('align' => 'center', 'text' => '<div class="providerTypes">' . sysLanguage::get('TEXT_PLEASE_SELECT_PROVIDER') . '</div>'),
			array('text' => $fileNameInput->draw() . '<span class="ui-icon ui-icon-newwin" style="vertical-align:middle;"></span>'),
			array('text' => $displayNameInput->draw()),
		);

		EventManager::notifyWithReturn('NewProductDownloadsTableAddInputRow', &$inputRow);

		if (sizeof($inputRow)+1 != sizeof($headerColumns)){
			if (sizeof($inputRow)+1 > sizeof($headerColumns)){
				while(sizeof($inputRow)+1 > sizeof($headerColumns)){
					$headerColumns[] = array('text' => '&nbsp;');
				}
			}else{
				while(sizeof($inputRow)+1 < sizeof($headerColumns)){
					$inputRow[] = array('text' => '&nbsp;');
				}
			}
		}

		$inputRow[] = array(
			'align' => 'right',
			'text' => '<span class="ui-icon ui-icon-plusthick addDownloadIcon"></span>'
		);

		$Table->addHeaderRow(array(
				'addCls' => 'ui-widget-header',
				'columns' => $headerColumns
			));

		$Table->addBodyRow(array(
				'addCls' => 'ui-state-hover',
				'columns' => $inputRow
			));

		$Downloads = Doctrine_Query::create()
			->from('ProductsDownloads')
			->where('products_id = ?', $Product->getId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if ($Downloads){
			foreach($Downloads as $dInfo){
				$currentProvider = array();

				$providerBox = htmlBase::newElement('selectbox')
					->hide()
					->setName('download_provider[' . $dInfo['download_id'] . ']')
					->addClass('selectDownloadProvider')
					->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'))
					->selectOptionByValue($dInfo['provider_id']);
				if ($Qproviders){
					foreach($Qproviders as $providerInfo){
						$providerBox->addOption($providerInfo['provider_id'], $providerInfo['provider_name']);
						if ($providerInfo['provider_id'] == $dInfo['provider_id']){
							$currentProvider = $providerInfo;
						}
					}
				}

				$providerTypeBox = htmlBase::newElement('selectbox')
					->hide()
					->addClass('downloadProviderType')
					->setName('download_provider_type[' . $dInfo['download_id'] . ']')
					->selectOptionByValue($dInfo['download_type']);
				if (!empty($currentProvider)){
					$moduleName = $currentProvider['provider_module'];
					$className = 'DownloadProvider' . ucfirst($moduleName);
					if (!class_exists($className)){
						require(sysConfig::getDirFsCatalog() . 'extensions/downloadProducts/providerModules/' . $moduleName . '/module.php');
					}

					$Module = new $className();
					foreach($Module->getDownloadTypes() as $type){
						$providerTypeBox->addOption($type, ucfirst($type));
					}
				}

				$fileNameInput = htmlBase::newElement('input')
					->hide()
					->addClass('providerFileName')
					->setName('download_file_name[' . $dInfo['download_id'] . ']')
					->val($dInfo['file_name']);

				$displayNameInput = htmlBase::newElement('input')
					->hide()
					->addClass('providerDisplayName')
					->setName('download_display_name[' . $dInfo['download_id'] . ']')
					->val($dInfo['display_name']);

				$BodyColumns = array(
					array('text' => '<span class="downloadInfoText">' . $dInfo['ProductsDownloadProviders']['provider_name'] . '</span>' . $providerBox->draw()),
					array('align' => 'center', 'text' => '<span class="downloadInfoText">' . ucfirst($dInfo['download_type']) . '</span>' . $providerTypeBox->draw()),
					array('text' => '<span class="downloadInfoText">' . $dInfo['file_name'] . '</span>' . $fileNameInput->draw() . '<span class="ui-icon ui-icon-newwin" style="display:none;vertical-align:middle;"></span>'),
					array('text' => '<span class="downloadInfoText">' . $dInfo['display_name'] . '</span>' . $displayNameInput->draw())
				);

				EventManager::notifyWithReturn('NewProductDownloadsTableAddBodyCol', $dInfo, &$BodyColumns);

				$BodyColumns[] = array(
					'align' => 'right',
					'text' => '<span class="ui-icon ui-icon-pencil editDownloadIcon"></span><span class="ui-icon ui-icon-closethick deleteDownloadIcon"></span>'
				);

				$Table->addBodyRow(array(
						'columns' => $BodyColumns
					));
			}
		}

		$TabsObj->addTabHeader('purchaseTypeDownloadSettingsTabFiles', array('text' => $this->getHeading()))
			->addTabPage('purchaseTypeDownloadSettingsTabFiles', array('text' => $Table));
	}
}
