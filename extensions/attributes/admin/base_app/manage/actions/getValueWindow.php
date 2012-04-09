<?php
	$windowAction = $_GET['windowAction'];
	if ($windowAction == 'edit'){
		$Qvalue = Doctrine_Query::create()
		->select('v.products_options_values_id, vd.products_options_values_name')
		->from('ProductsOptionsValues v')
		->leftJoin('v.ProductsOptionsValuesDescription vd')
		->where('v.products_options_values_id = ?', $_GET['value_id'])
		->orderBy('vd.products_options_values_name')
		->fetchOne()->toArray();
	}

	$valueNames = htmlBase::newElement('table')->setCellPadding('3')->setCellSpacing('0');
	foreach (sysLanguage::getLanguages() as $lInfo){
		$langID = $lInfo['id'];
				
		$valueNameInput = htmlBase::newElement('input')->setName('value_name[' . $langID . ']');
		if (isset($Qvalue)){
			$valueNameInput->setValue($Qvalue['ProductsOptionsValuesDescription'][$langID]['products_options_values_name']);
		}
		$valueNames->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => $lInfo['showName']()),
				array('addCls' => 'main', 'text' => $valueNameInput)
			)
		));
	}

	$finalTable = htmlBase::newElement('table')->setCellPadding('3')->setCellSpacing('0');
	
	if (isset($_GET['value_id'])){
		$finalTable->attr('value_id', (int)$_GET['value_id']);
	}
			
	$finalTable->addBodyRow(array('columns' => array(
		array('addCls' => 'main', 'text' => '<b>' . sysLanguage::get('ENTRY_VALUE_NAME') . '</b>')
	)));

	$finalTable->addBodyRow(array('columns' => array(
		array('addCls' => 'main', 'text' => $valueNames)
	)));

	EventManager::attachActionResponse($finalTable->draw(), 'html');
?>