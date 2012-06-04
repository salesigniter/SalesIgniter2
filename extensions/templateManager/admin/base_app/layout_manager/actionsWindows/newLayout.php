<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stephen
 * Date: 3/26/11
 * Time: 5:14 PM
 * To change this template use File | Settings | File Templates.
 */

$selApps = array();
$TemplateManagerLayouts = Doctrine_Core::getTable('TemplateManagerLayouts');
if (isset($_GET['layout_id'])){
	$Layout = $TemplateManagerLayouts->find((int) $_GET['layout_id']);

	$QselApps = Doctrine_Query::create()
	->from('TemplatePages')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	foreach($QselApps as $sInfo){
		$layouts = explode(',', $sInfo['layout_id']);
		$pageType = explode(',', $sInfo['page_type']);
		$assocurls = explode(',', $sInfo['associative_url']);
		if (in_array($Layout->layout_id, $layouts)){
			if (!empty($sInfo['extension'])){
				$selApps['ext'][$sInfo['extension']][$sInfo['application']][$sInfo['page']] = true;
				$pageTypes['ext'][$sInfo['extension']][$sInfo['application']][$sInfo['page']] = $pageType[array_search($Layout->layout_id,$layouts)];
				$assocurl['ext'][$sInfo['extension']][$sInfo['application']][$sInfo['page']] = $assocurls[array_search($Layout->layout_id,$layouts)];

			}
			else {
				$selApps[$sInfo['application']][$sInfo['page']] = true;
				$pageTypes[$sInfo['application']][$sInfo['page']] = $pageType[array_search($Layout->layout_id,$layouts)];
				$assocurl[$sInfo['application']][$sInfo['page']] = $assocurls[array_search($Layout->layout_id,$layouts)];
			}
		}
	}
}else{
	$Layout = $TemplateManagerLayouts->getRecord();
}
$LayoutSettings = json_decode($Layout->layout_settings);

$SettingsTable = htmlBase::newElement('table')
->setCellPadding(3)
->setCellSpacing(0);

$SettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Layout Name:'),
		array('text' => htmlBase::newElement('input')
		->setName('layoutName')
		->attr('id', 'layoutName')
		->val($Layout->layout_name)
		->draw())
	)
));

if($Layout->Template->Configuration['NAME']->configuration_value == 'codeGeneration'){
	$associativeUrl = htmlBase::newElement('input')
		->setLabel('Show in page:');

}

$AppArray = $App->getApplications($selApps, false);

$BoxesContainer = htmlBase::newElement('div');

$rentalMemberCheckbox = htmlBase::newElement('checkbox')->setLabel('R')->setValue('R');
$nonRentalMemberCheckbox = htmlBase::newElement('checkbox')->setLabel('N')->setValue('N');

$col = 0;
foreach($AppArray as $appName => $aInfo){
	if ($appName == 'ext'){
		continue;
	}

	if (!empty($aInfo)){
		$Box = htmlBase::newElement('div')
		->addClass('ui-widget-content ui-corner-all mainBox')
		->css(array(
			'float' => 'left',
			'margin' => '.5em',
			'min-width' => '260px',
			'min-height' => '250px',
			'padding' => '.5em'
		));

		$checkboxes = '<div class="ui-widget-header"><input type="checkbox" class="appBox checkAllPages"> ' . $appName . '</div>';
		foreach($aInfo as $pageName => $pageChecked){
			$pageName1 = $pageName;
			if($appName == 'product' && is_numeric($pageName) && isset($associativeUrl)){
				$QProducts = Doctrine_Query::create()
					->from('Products p')
					->leftJoin('p.ProductsDescription pd')
					->where('pd.language_id = ?', Session::get('languages_id'))
					->andWhere('p.products_id = ?', $pageName)
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

				$pageName1 = $QProducts[0]['ProductsDescription'][0]['products_name'];
			}
			$rentalMemberCheckbox
					->setName('pagetype[' . $appName . '][' . $pageName . ']')
					->setChecked((isset($pageTypes[$appName][$pageName]) && $pageTypes[$appName][$pageName] == 'R') ? true : false);

			$nonRentalMemberCheckbox
					->setName('pagetype[' . $appName . '][' . $pageName . ']')
					->setChecked((isset($pageTypes[$appName][$pageName]) && $pageTypes[$appName][$pageName] == 'N') ? true : false);

			$checkboxes .= '<div style="margin: 0 0 0 1em;"><input class="pageBox" type="checkbox" name="applications[' . $appName . '][]" value="' . $pageName . '"' . ($pageChecked === true ? ' checked="checked"' : '') . '> ' . $pageName1;
			$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$nonRentalMemberCheckbox->draw();
			$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$rentalMemberCheckbox->draw();
			if(isset($associativeUrl)){
				$associativeUrl->setName('assocurl['. $appName . '][' . $pageName . ']')
				->setValue(isset($assocurl[$appName][$pageName])?$assocurl[$appName][$pageName]:'');
				$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$associativeUrl->draw();
			}
			$checkboxes .= '</div>';

		}

		$Box->html($checkboxes);
		$BoxesContainer->append($Box);
	}
}

foreach($AppArray['ext'] as $ExtName => $eInfo){
	if (!empty($eInfo)){
		$Box = htmlBase::newElement('div')
		->addClass('ui-widget-content ui-corner-all mainBox')
		->css(array(
			'float' => 'left',
			'margin' => '.5em',
			'min-width' => '260px',
			'min-height' => '250px',
			'padding' => '.5em'
		));

		$checkboxes = '<div class="ui-widget-header"><input type="checkbox" class="extensionBox checkAllApps"> ' . $ExtName . '</div>';
		foreach($eInfo as $appName => $aInfo){
			$checkboxes .= '<div><div class="ui-state-hover" style="margin: .5em .5em 0 .5em"><input type="checkbox" class="appBox checkAllPages"> ' . $appName . '</div>';
			foreach($aInfo as $pageName => $pageChecked){
				$rentalMemberCheckbox
						->setName('pagetype[ext][' . $ExtName . '][' . $appName . '][' . $pageName . ']')
						->setChecked((isset($pageTypes['ext'][$ExtName][$appName][$pageName]) && $pageTypes['ext'][$ExtName][$appName][$pageName] == 'R') ? true : false);

				$nonRentalMemberCheckbox
						->setName('pagetype[ext][' . $ExtName . '][' . $appName . '][' . $pageName . ']')
						->setChecked((isset($pageTypes['ext'][$ExtName][$appName][$pageName]) && $pageTypes['ext'][$ExtName][$appName][$pageName] == 'N') ? true : false);

				$checkboxes .= '<div style="margin: 0 0 0 1em;"><input type="checkbox" class="pageBox" name="applications[ext][' . $ExtName . '][' . $appName . '][]" value="' . $pageName . '"' . ($pageChecked === true ? ' checked="checked"' : '') . '> ' . $pageName;
				$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$nonRentalMemberCheckbox->draw();
				$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$rentalMemberCheckbox->draw();

				if(isset($associativeUrl)){
					$associativeUrl->setName('assocurl[ext][' . $ExtName . '][' . $appName . '][' . $pageName . ']')
						->setValue(isset($assocurl['ext'][$ExtName][$appName][$pageName])?$assocurl['ext'][$ExtName][$appName][$pageName]:'');
					$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$associativeUrl->draw();
				}
				$checkboxes .= '</div>';
			}
			$checkboxes .= '</div>';
		}

		$Box->html($checkboxes);
		$BoxesContainer->append($Box);
	}
}
$BoxesContainer->append(htmlBase::newElement('div')->addClass('ui-helper-clearfix'));

if ($Layout->layout_id <= 0){
	$layoutTemplatesContainer = htmlBase::newElement('div');
	$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/templateManager/layoutTemplates/');
	foreach($Dir as $d){
		if ($d->isFile() === true || $d->isDot() === true || strtolower($d->getBasename()) == 'codegeneration'){
			continue;
		}
		$sortedTemplates[] =  $d->getBasename();
	}
	sort($sortedTemplates);
	foreach($sortedTemplates as $templateName){
		$Box = htmlBase::newElement('div')
		->css(array(
			'float' => 'left',
			'margin' => '.5em'
		))
		->html('<center>' .
		'<input type="radio" name="layout_template" value="' . $templateName . '"' . ($templateName == 'empty' ? ' checked=checked' : '') . '>' .
		'&nbsp;' . ucfirst($templateName) . '<br>' .
		'<img src="' . sysConfig::getDirWsCatalog() . 'extensions/templateManager/layoutTemplates/' . $templateName . '/' . $templateName . '.png" width="200" height="200">' .
		'</center>');

		$layoutTemplatesContainer->append($Box);
	}
	$layoutTemplatesContainer->append(htmlBase::newElement('div')->addClass('ui-helper-clearfix'));

	$SettingsTable->addBodyRow(array(
		'columns' => array(
			array('text' => 'Select starting layout:'),
			array('text' => $layoutTemplatesContainer->draw())
		)
	));
}

$PageTypeSelect = htmlBase::newElement('selectbox')
	->setName('pageType')
	->selectOptionByValue($Layout->page_type)
	->addOption('template', 'Layout Template')
	->addOption('page', 'Standalone Page')
	->addOption('print', 'Print Layout Template')
	->addOption('email', 'Email Layout Template');

$ApplicationNameInput = htmlBase::newElement('input')
	->setName('appName')
	->setValue((isset($LayoutSettings->appName) ? $LayoutSettings->appName : ''));

$ApplicationPageTitleInput = htmlBase::newElement('input')
	->setName('appPageTitle')
	->setValue((isset($LayoutSettings->appPageTitle) ? $LayoutSettings->appPageTitle : ''));

$ApplicationPageSubTitleInput = htmlBase::newElement('input')
	->setName('appPageSubTitle')
	->setValue((isset($LayoutSettings->appPageSubTitle) ? $LayoutSettings->appPageSubTitle : ''));

$ApplicationPageNameInput = htmlBase::newElement('input')
	->setName('appPageName')
	->setValue((isset($LayoutSettings->appPageName) ? $LayoutSettings->appPageName : ''));

$SettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Page Type:'),
		array('text' => $PageTypeSelect->draw())
	)
));

$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'page'
	),
	'columns' => array(
		array('text' => 'Display Type:'),
		array('text' => htmlBase::newElement('selectbox')
			->setName('layoutType')
			->addOption('desktop', 'Desktop')
			->addOption('smartphone', 'Smart Phone')
			->addOption('tablet', 'Tablet')
			->selectOptionByValue($Layout->layout_type)
			->draw())
	)
));

$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'page'
	),
	'columns' => array(
		array('text' => 'Application Name:'),
		array('text' => $ApplicationNameInput)
	)
));

$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'page'
	),
	'columns' => array(
		array('text' => 'Application Page Name:'),
		array('text' => $ApplicationPageNameInput->draw() . '.php')
	)
));

$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'page'
	),
	'columns' => array(
		array('text' => 'Application Page Title:'),
		array('text' => $ApplicationPageTitleInput)
	)
));

$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'page'
	),
	'columns' => array(
		array('text' => 'Application Page Sub Title:'),
		array('text' => $ApplicationPageSubTitleInput)
	)
));


$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'template'
	),
	'columns' => array(
		array('text' => 'Display Type:'),
		array('text' => htmlBase::newElement('selectbox')
			->setName('layoutType')
			->addOption('desktop', 'Desktop')
			->addOption('smartphone', 'Smart Phone')
			->addOption('tablet', 'Tablet')
			->selectOptionByValue($Layout->layout_type)
			->draw())
	)
));

$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'template'
	),
	'columns' => array(
		array( 'text' => 'Layout Pages:'),
		array('css'=>array('color'=>'red'),'text' => '<strong>N : Non Rental Members <br> R : Rental Members</strong>')
	)
));

$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'template'
	),
	'columns' => array(
		array('colspan' => 2, 'text' => '<input type="checkbox" class="checkAll"/> <span class="checkAllText">Check All</span>' . $BoxesContainer->draw())
	)
));

$EmailTemplates = Doctrine_Core::getTable('EmailTemplates')
	->findAll();

$boxes = array();
foreach($EmailTemplates as $EmailTemplate){
	$boxes[] = array(
		'labelPosition' => 'after',
		'label' => $EmailTemplate->email_templates_name,
		'value' => $EmailTemplate->email_templates_event
	);
}
$EventsCheckboxes = htmlBase::newElement('checkbox')
	->addGroup(array(
	'name' => 'email_template[]',
	'checked' => (isset($LayoutSettings->emailTemplates) ? (array) $LayoutSettings->emailTemplates: ''),
	'separator' => array(
		'type' => 'table',
		'cols' => 3
	),
	'data' => $boxes
));
$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'email'
	),
	'columns' => array(
		array('valign' => 'top', 'text' => 'Email Events: '),
		array('text' => '<input type="checkbox" class="checkAll"/> <span class="checkAllText">Check All</span><br><br>' . $EventsCheckboxes->draw())
	)
));

$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'print'
	),
	'columns' => array(
		array('text' => 'Orientation:'),
		array('text' => htmlBase::newElement('selectbox')
			->setName('layoutOrientation')
			->addOption('portrait', 'Portrait')
			->addOption('landscape', 'Landscape')
			->selectOptionByValue((isset($LayoutSettings->layoutOrientation) ? $LayoutSettings->layoutOrientation : 'portrait'))
			->draw())
	)
));

AccountsReceivableModules::loadModules();
$boxes = array();
foreach(AccountsReceivableModules::getModules() as $Module){
	$boxes[] = array(
		'labelPosition' => 'after',
		'label' => $Module->getTitle(),
		'value' => $Module->getCode()
	);
	if (is_dir(sysConfig::getDirFsCatalog() . 'templates/chater/modules/accountsReceivableModules/' . $Module->getCode() . '/print')){
		$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'templates/chater/modules/accountsReceivableModules/' . $Module->getCode() . '/print');
		foreach($Dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isDir()){
				continue;
			}

			$boxes[] = array(
				'labelPosition' => 'after',
				'label' => $Module->getTitle() . ' - ' . ucfirst($dInfo->getBasename('.php')),
				'value' => $Module->getCode() . '_' . $dInfo->getBasename('.php')
			);
		}
	}
}
$SettingsTable->addBodyRow(array(
	'attr' => array(
		'data-for_page_type' => 'print'
	),
	'columns' => array(
		array('text' => 'Which Modules:'),
		array('text' => htmlBase::newElement('checkbox')
			->addGroup(array(
			'name' => 'print_modules[]',
			'checked' => (isset($LayoutSettings->printModules) ? (array) $LayoutSettings->printModules: ''),
			'separator' => array(
				'type' => 'table',
				'cols' => 3
			),
			'data' => $boxes
		))->draw()
		)
	)
));

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . sysLanguage::get('TEXT_INFO_HEADING_NEW') . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);
$infoBox->addContentRow($SettingsTable->draw());

EventManager::attachActionResponse($infoBox->draw(), 'html');
?>