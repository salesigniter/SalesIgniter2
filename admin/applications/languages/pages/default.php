<?php
/*
 * Sales Igniter E-Commerce System
 * Version: 2.0
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) 2011 I.T. Web Experts
 *
 * This script and its source are not distributable without the written conscent of I.T. Web Experts
 */

$langArr = array();
$Languages = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'includes/languages/');
foreach($Languages as $langDir){
	if ($langDir->isDot() || $langDir->isFile()){
		continue;
	}
	$dirName = $langDir->getBasename();

	$langSettings = simplexml_load_file(
		$langDir->getPathname() . '/settings.xml',
		'SimpleXMLElement',
		LIBXML_NOCDATA
	);

	$langArr[$dirName] = array(
		'name'           => (string)$langSettings->name,
		'code'           => (string)$langSettings->code,
		'directory'      => str_replace('\\', '/', $langDir->getPathname()),
		'installed'      => false,
		'forced_default' => 0
	);

	$Qcheck = Doctrine_Query::create()
		->select('languages_id, forced_default')
		->from('Languages')
		->where('code = ?', (string)$langSettings->code)
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	if ($Qcheck){
		$langArr[$dirName]['installed'] = true;
		$langArr[$dirName]['id'] = $Qcheck[0]['languages_id'];
		$langArr[$dirName]['forced_default'] = $Qcheck[0]['forced_default'];
	}
	else {
		unset($langArr[$dirName]);
	}
}
ksort($langArr);

$tableGrid = htmlBase::newElement('newGrid')
	->setMainDataKey('language_id')
	->allowMultipleRowSelect(true)
	->usePagination(false);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable(),
	htmlBase::newElement('button')->usePreset('edit')->addClass('defineButton')->setText('Definitions')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_LANGUAGE_NAME')),
		array('text' => sysLanguage::get('TABLE_HEADING_LANGUAGE_CODE')),
		array('text' => 'Forced Default'),
		array('text' => 'Info')
	)
));

if ($langArr){
	foreach($langArr as $lInfo){
		$languageId = $lInfo['id'];
		if (sysConfig::get('DEFAULT_LANGUAGE') == $lInfo['code']){
			$gridShowName = '<b>' . $lInfo['name'] . '</b> (' . sysLanguage::get('TEXT_DEFAULT') . ')';
		}
		else {
			$gridShowName = $lInfo['name'];
		}

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-language_id'   => $languageId,
				'data-language_dir'  => $lInfo['directory'],
				'data-language_code' => $lInfo['code'],
				'data-is_installed'  => ($lInfo['installed'] === true ? 'true' : 'false'),
				'data-is_default'    => (sysConfig::get('DEFAULT_LANGUAGE') == $lInfo['code'] ? 'true' : 'false')
			),
			'columns' => array(
				array('text' => $gridShowName),
				array('text' => $lInfo['code']),
				array(
					'text'  => '<a href="' . itw_app_link('action=forceDefault&lID=' . $languageId . '&force=' . ($lInfo['forced_default'] == '1' ? '0' : '1'), 'languages', 'default') . '"><span class="ui-icon ui-icon-circle-' . ($lInfo['forced_default'] == '1' ? 'check' : 'close') . ' forceDefault"></span></a>',
					'align' => 'center'
				),
				array(
					'text'  => htmlBase::newElement('icon')->setType('info')->draw(),
					'align' => 'center'
				)
			)
		));

		$tableGrid->addBodyRow(array(
			'addCls'  => 'gridInfoRow',
			'columns' => array(
				array(
					'colspan' => 6,
					'text'    => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_LANGUAGE_NAME') . '</b></td>' .
						'<td> ' . $lInfo['name'] . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_LANGUAGE_CODE') . '</b></td>' .
						'<td>' . $lInfo['code'] . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_LANGUAGE_DIRECTORY') . '</b></td>' .
						'<td>' . $lInfo['directory'] . '</td>' .
						'</tr>' .
						'</table>'
				)
			)
		));
	}
}

echo $tableGrid->draw();
