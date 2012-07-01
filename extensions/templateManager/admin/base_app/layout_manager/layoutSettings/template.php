<?php
$selApps = array();
$AppArray = $App->getApplications($selApps, false);

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

$BoxesContainer = htmlBase::newElement('div');

$pageCheckbox = htmlBase::newElement('checkbox')->addClass('pageBox');
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
			$pageCheckbox
				->setLabel($pageName1)
				->setLabelPosition('right')
				->setName('applications[' . $appName . '][]')
				->setValue($pageName)
				->setChecked(isset($selApps[$appName][$pageName]) ? $selApps[$appName][$pageName] : false);

			$rentalMemberCheckbox
				->setName('pagetype[' . $appName . '][' . $pageName . ']')
				->setChecked((isset($pageTypes[$appName][$pageName]) && $pageTypes[$appName][$pageName] == 'R') ? true : false);

			$nonRentalMemberCheckbox
				->setName('pagetype[' . $appName . '][' . $pageName . ']')
				->setChecked((isset($pageTypes[$appName][$pageName]) && $pageTypes[$appName][$pageName] == 'N') ? true : false);

			$checkboxes .= '<div style="margin: 0 0 0 1em;">';
			$checkboxes .= $pageCheckbox->draw();
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
				$pageCheckbox
					->setLabel($pageName)
					->setLabelPosition('right')
					->setName('applications[ext][' . $ExtName . '][' . $appName . '][]')
					->setValue($pageName)
					->setChecked(isset($selApps['ext'][$ExtName][$appName][$pageName]) ? $selApps['ext'][$ExtName][$appName][$pageName] : false);

				$rentalMemberCheckbox
					->setName('pagetype[ext][' . $ExtName . '][' . $appName . '][' . $pageName . ']')
					->setChecked((isset($pageTypes['ext'][$ExtName][$appName][$pageName]) && $pageTypes['ext'][$ExtName][$appName][$pageName] == 'R') ? true : false);

				$nonRentalMemberCheckbox
					->setName('pagetype[ext][' . $ExtName . '][' . $appName . '][' . $pageName . ']')
					->setChecked((isset($pageTypes['ext'][$ExtName][$appName][$pageName]) && $pageTypes['ext'][$ExtName][$appName][$pageName] == 'N') ? true : false);

				$checkboxes .= '<div style="margin: 0 0 0 1em;">';
				$checkboxes .= $pageCheckbox->draw();
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

$SettingsTable = htmlBase::newElement('table');

if ($Layout->layout_id <= 0){
	$layoutTemplatesContainer = htmlBase::newElement('div');
	$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/templateManager/admin/base_app/layout_manager/layoutSettings/template/startingLayout/');
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
			'<img src="' . sysConfig::getDirWsCatalog() . 'extensions/templateManager/admin/base_app/layout_manager/layoutSettings/template/startingLayout/' . $templateName . '/' . $templateName . '.png" width="200" height="200">' .
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

$SettingsTable->addBodyRow(array(
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

echo $SettingsTable->draw();
?>
<script>
	var height = 0;
	var width = 0;
	$('.mainBox').each(function () {
		if ($(this).outerWidth() > width){
			width = $(this).outerWidth();
		}

		if ($(this).outerHeight() > height){
			height = $(this).outerHeight();
		}
	});

	$('.mainBox').width(width).height(height);

	$('.checkAll').click(function(){
		var self = this;
		$(this).parent().find('input:checkbox').each(function (){
			this.checked = self.checked;
		});

		if (self.checked){
			$(this).parent().find('.checkAllText').html('Uncheck All');
		}else{
			$(this).parent().find('.checkAllText').html('Check All');
		}
	});

	$('.checkAllPages').click(function (){
		var self = this;
		$(self).parent().parent().find('.pageBox').each(function (){
			this.checked = self.checked;
		});
	});

	$('.checkAllApps').click(function (){
		var self = this;
		$(self).parent().parent().find('.appBox').each(function (){
			this.checked = self.checked;
		});
		$(self).parent().parent().find('.pageBox').each(function (){
			this.checked = self.checked;
		});
	});
</script>
