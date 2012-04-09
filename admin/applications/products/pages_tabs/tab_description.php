<?php
$LanguageTabs = htmlBase::newElement('tabs')
	->setId('languageDefines');
foreach(sysLanguage::getLanguages() as $lInfo){
	$lID = $lInfo['id'];

	$ProductsName = htmlBase::newElement('input')
		->setName('products_name[' . $lID . ']');

	$ProductsUrl = htmlBase::newElement('input')
		->setName('products_url[' . $lID . ']');

	$ProductsShortDescription = htmlBase::newElement('ck_editor')
		->setName('products_short_description[' . $lID . ']');

	$ProductsDescription = htmlBase::newElement('ck_editor')
		->setName('products_description[' . $lID . ']');

	$ProductsSeoUrl = htmlBase::newElement('input')
		->setName('products_seo_url[' . $lID . ']');

	$ProductsName->setValue(stripslashes($Product->getName($lID)));
	$ProductsDescription->html(stripslashes($Product->getDescription($lID)));
	$ProductsShortDescription->html(stripslashes($Product->getShortDescription($lID)));
	$ProductsUrl->setValue($Product->getUrl($lID));
	$ProductsSeoUrl->setValue($Product->getSeoUrl($lID));

	$inputTable = htmlBase::newElement('table')
		->setCellPadding(0)
		->setCellSpacing(0);

	$inputTable->addBodyRow(array(
			'columns' => array(
				array('text' => sysLanguage::get('TEXT_PRODUCTS_NAME')),
				array('text' => $ProductsName->draw())
			)
		));

	$inputTable->addBodyRow(array(
			'columns' => array(
				array('text' => sysLanguage::get('TEXT_PRODUCTS_URL') . '<br><small>' . sysLanguage::get('TEXT_PRODUCTS_URL_WITHOUT_HTTP') . '</small>'),
				array('text' => $ProductsUrl->draw())
			)
		));

	$inputTable->addBodyRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('TEXT_PRODUCTS_SHORT_DESCRIPTION')),
			array('text' => $ProductsShortDescription->draw())
		)
	));

	$inputTable->addBodyRow(array(
		'columns' => array(
			array('colspan' => 2, 'text' => '&nbsp;')
		)
	));

	$inputTable->addBodyRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('TEXT_PRODUCTS_DESCRIPTION')),
			array('text' => $ProductsDescription->draw())
		)
	));

	$inputTable->addBodyRow(array(
			'columns' => array(
				array('colspan' => 2, 'text' => '<hr>' . sysLanguage::get('TEXT_PRODUCT_METTA_INFO'))
			)
		));

	$inputTable->addBodyRow(array(
			'columns' => array(
				array('text' => sysLanguage::get('TEXT_PRODUCTS_SEO_URL')),
				array('text' => $ProductsSeoUrl->draw())
			)
		));

	/**
	 * this event expects an array having two elements: label and content | i.e. (array(label=>'', content=>''))
	 */
	$contents_middle = array();
	EventManager::notify('ProductsFormMiddle', $lID, &$contents_middle, $Product);

	if (is_array($contents_middle)){
		foreach($contents_middle as $element){
			if (is_array($element)){
				if (!isset($element['label'])) {
					$element['label'] = 'no_defined';
				}
				if (!isset($element['content'])) {
					$element['content'] = 'no_defined';
				}

				$inputTable->addBodyRow(array(
						'columns' => array(
							array('text' => $element['label']),
							array('text' => $element['content'])
						)
					));
			}
			else {
				$inputTable->addBodyRow(array(
						'columns' => array(
							array('colspan' => 2, 'text' => $element)
						)
					));
			}
		}
	}

	$LanguageTabs->addTabHeader('langTab_' . $lID, array('text' => $lInfo['showName']()))
		->addTabPage('langTab_' . $lID, array('text' => $inputTable));
}
echo $LanguageTabs->draw();
?>
