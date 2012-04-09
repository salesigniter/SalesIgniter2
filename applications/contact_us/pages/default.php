<?php
	$contactTable = htmlBase::newElement('table')
	->setCellPadding(2)
	->setCellSpacing(0);
	
	$contactTable->addBodyRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('ENTRY_NAME'))
		)
	));
	
	$contactTable->addBodyRow(array(
		'columns' => array(
			array('text' => htmlBase::newElement('input')->setName('name'))
		)
	));
	
	$contactTable->addBodyRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('ENTRY_EMAIL'))
		)
	));
	
	$contactTable->addBodyRow(array(
		'columns' => array(
			array('text' => htmlBase::newElement('input')->setName('email'))
		)
	));
	
	$contactTable->addBodyRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('ENTRY_ENQUIRY'))
		)
	));
	
	$contactTable->addBodyRow(array(
		'columns' => array(
			array('text' => tep_draw_textarea_field('enquiry', 'soft', 50, 15))
		)
	));

	if (sysConfig::get('CATPCHA_ENABLED') == 'True'){
		$contactTable->addBodyRow(array(
			'columns' => array(
				array('text' => '<br>' . sysLanguage::get('PLEASE_FILL_IN_CAPTCHA'))
			)
		));
		
		$contactTable->addBodyRow(array(
			'columns' => array(
				array('text' => '<img src="'.sysConfig::getDirWsCatalog().'securimage_show.php" style="vertical-align:middle;">&nbsp;&nbsp;<input type="text" name="code" style="width:175px;vertical-align:middle;" />')
			)
		));
	}
	
	$infoPages = $appExtension->getExtension('infoPages');
	$pageContents = $infoPages->displayContentBlock('contact_us_block');
	$pageContents .= $contactTable->draw();
	
	$pageButtons = htmlBase::newElement('button')->usePreset('continue')->setType('submit')->draw();

$pageContents = htmlBase::newElement('form')
	->setAction(itw_app_link('action=send'))
	->setName('contact_us')
	->setMethod('post')
	->html($pageContents)
	->draw();

	$pageContent->set('pageTitle', sysLanguage::get('HEADING_TITLE'));
	$pageContent->set('pageContent', $pageContents);
	$pageContent->set('pageButtons', $pageButtons);
