<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerPrintWidgetInvoiceListing extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('invoiceListing');
	}

	public function addStyles($Styles, htmlElement $El) {
		//echo $El->getTagName() . '::<pre>';print_r($Styles);
		$css = array();
		foreach($Styles as $definition_key => $definition_value){
			if ($definition_key == 'custom_css'){
				$El->attr('data-custom_css', htmlspecialchars($definition_value));
			}

			if (is_string($definition_value) && (substr($definition_value, 0, 1) == '{' || substr($definition_value, 0, 1) == '[')){
				$css[$definition_key] = json_decode($definition_value);
			}
			else {
				$css[$definition_key] = $definition_value;
			}
			$El->css($definition_key, $css[$definition_key]);
		}
		$El->attr('data-styles', (empty($css) ? '{}' : htmlspecialchars(json_encode($css))));
	}

	public function addInputs($Inputs, $El) {
		$inputVals = array();
		foreach($Inputs as $configuration_key => $configuration_value){
			if ($configuration_key == 'table'){
				continue;
			}
			if (is_string($configuration_value) && (substr($configuration_value, 0, 1) == '{' || substr($configuration_value, 0, 1) == '[')){
				$inputVals[$configuration_key] = json_decode($configuration_value);
			}
			else {
				$inputVals[$configuration_key] = $configuration_value;
			}
		}
		$El->attr('data-inputs', (empty($inputVals) ? '{}' : htmlspecialchars(json_encode($inputVals))));
	}

	public function showLayoutPreview($WidgetSettings) {
		global $appExtension;
		if (!isset($WidgetSettings['tableSettings'])){
			return $this->getCode();
		}
		$TableSettings = $WidgetSettings['tableSettings'];
		$TableCss = $WidgetSettings['tableStyles'];

		$TableInputs = json_decode($TableSettings->table);
		$TableTheadInputs = json_decode($TableSettings->thead);
		$TableTheadTrInputs = json_decode($TableSettings->thead_tr);
		$TableTheadTrThInputs = json_decode($TableSettings->thead_tr_th);
		$TableTbodyInputs = json_decode($TableSettings->tbody);
		$TableTbodyTrInputs = json_decode($TableSettings->tbody_tr);
		$TableTbodyTrTdInputs = json_decode($TableSettings->tbody_tr_td);

		$TableStyles = json_decode($TableCss->table);
		$TableTheadStyles = json_decode($TableCss->thead);
		$TableTheadTrStyles = json_decode($TableCss->thead_tr);
		$TableTheadTrThStyles = json_decode($TableCss->thead_tr_th);
		$TableTbodyStyles = json_decode($TableCss->tbody);
		$TableTbodyTrStyles = json_decode($TableCss->tbody_tr);
		$TableTbodyTrTdStyles = json_decode($TableCss->tbody_tr_td);

		$return = '';
		$Table = new htmlElement('table');
		$Table->attr('cellpadding', 3);
		$Table->attr('cellspacing', 0);
		$this->addInputs($TableInputs, $Table);
		$this->addStyles($TableStyles, $Table);

		$Thead = new htmlElement('thead');
		$this->addInputs($TableTheadInputs, $Thead);
		$this->addStyles($TableTheadStyles, $Thead);

		$TheadTr = new htmlElement('tr');
		$this->addInputs($TableTheadTrInputs, $TheadTr);
		$this->addStyles($TableTheadTrStyles, $TheadTr);

		$TheadTh1 = new htmlElement('th');
		$TheadTh1->html('This Is');
		$this->addInputs($TableTheadTrThInputs, $TheadTh1);
		$this->addStyles($TableTheadTrThStyles, $TheadTh1);

		$TheadTh2 = new htmlElement('th');
		$TheadTh2->html('A Demo');
		$this->addInputs($TableTheadTrThInputs, $TheadTh2);
		$this->addStyles($TableTheadTrThStyles, $TheadTh2);

		$TheadTh3 = new htmlElement('th');
		$TheadTh3->html('Table Preview');
		$this->addInputs($TableTheadTrThInputs, $TheadTh3);
		$this->addStyles($TableTheadTrThStyles, $TheadTh3);

		$Tbody = new htmlElement('tbody');
		$this->addInputs($TableTbodyInputs, $Tbody);
		$this->addStyles($TableTbodyStyles, $Tbody);

		$TbodyTr = new htmlElement('tr');
		$this->addInputs($TableTbodyTrInputs, $TbodyTr);
		$this->addStyles($TableTbodyTrStyles, $TbodyTr);

		$TbodyTd1 = new htmlElement('td');
		$TbodyTd1->html('To Help');
		$this->addInputs($TableTbodyTrTdInputs, $TbodyTd1);
		$this->addStyles($TableTbodyTrTdStyles, $TbodyTd1);

		$TbodyTd2 = new htmlElement('td');
		$TbodyTd2->html('You Style');
		$this->addInputs($TableTbodyTrTdInputs, $TbodyTd2);
		$this->addStyles($TableTbodyTrTdStyles, $TbodyTd2);

		$TbodyTd3 = new htmlElement('td');
		$TbodyTd3->html('The Table');
		$this->addInputs($TableTbodyTrTdInputs, $TbodyTd3);
		$this->addStyles($TableTbodyTrTdStyles, $TbodyTd3);

		$TheadTr->append($TheadTh1)->append($TheadTh2)->append($TheadTh3);
		$Thead->append($TheadTr);
		$Table->append($Thead);
		$TbodyTr->append($TbodyTd1)->append($TbodyTd2)->append($TbodyTd3);
		$Tbody->append($TbodyTr);
		$Table->append($Tbody);

		return $Table->draw();
	}

	public function isTable() {
		return true;
	}

	public function applyStyles(&$El, $stylesObj){
		foreach($stylesObj as $k => $v){
			$El->css($k, $v);
		}
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		$boxCssProperties = $this->getWidgetCss();
		if ($boxCssProperties !== null){
			$TableCss = array();
			foreach($boxCssProperties as $cssInfo){
				if ($cssInfo['definition_key'] == 'table'){
					$TableCss = json_decode($cssInfo['definition_value']);
				}
			}

			if (!empty($TableCss)){
				$TableStyles = json_decode($TableCss->table);
				$TableTheadStyles = json_decode($TableCss->thead);
				$TableTheadTrStyles = json_decode($TableCss->thead_tr);
				$TableTheadTrThStyles = json_decode($TableCss->thead_tr_th);
				$TableTbodyStyles = json_decode($TableCss->tbody);
				$TableTbodyTrStyles = json_decode($TableCss->tbody_tr);
				$TableTbodyTrTdStyles = json_decode($TableCss->tbody_tr_td);
			}
		}

		$Table = new htmlElement('table');
		$Table->attr('cellpadding', 3);
		$Table->attr('cellspacing', 0);
		$this->applyStyles($Table, $TableStyles);
		if ($boxWidgetProperties->tableHeading === true){
			$TableHeading = new htmlElement('thead');
			$this->applyStyles($TableHeading, $TableTheadStyles);

			$TableHeadingRow = new htmlElement('tr');
			$this->applyStyles($TableHeadingRow, $TableTheadTrStyles);

			if ($boxWidgetProperties->showQty === true){
				$TableHeadings['qty'] = new htmlElement('th');
				$TableHeadings['qty']->html(sysLanguage::get('TABLE_HEADING_PRODUCTS_QTY'));
				$this->applyStyles($TableHeadings['qty'], $TableTheadTrThStyles);
			}
			if ($boxWidgetProperties->showBarcode === true){
				$TableHeadings['barcode'] = new htmlElement('th');
				$TableHeadings['barcode']->html(sysLanguage::get('TABLE_HEADING_PRODUCTS_BARCODE'));
				$this->applyStyles($TableHeadings['barcode'], $TableTheadTrThStyles);
			}
			if ($boxWidgetProperties->showModel === true){
				$TableHeadings['model'] = new htmlElement('th');
				$TableHeadings['model']->html(sysLanguage::get('TABLE_HEADING_PRODUCTS_MODEL'));
				$this->applyStyles($TableHeadings['model'], $TableTheadTrThStyles);
			}
			if ($boxWidgetProperties->showName === true){
				$TableHeadings['name'] = new htmlElement('th');
				$TableHeadings['name']->html(sysLanguage::get('TABLE_HEADING_PRODUCTS_NAME'));
				$this->applyStyles($TableHeadings['name'], $TableTheadTrThStyles);
			}
			if ($boxWidgetProperties->showPrice === true){
				$TableHeadings['price'] = new htmlElement('th');
				$TableHeadings['price']->html(sysLanguage::get('TABLE_HEADING_PRICE_EXCLUDING_TAX'));
				$this->applyStyles($TableHeadings['price'], $TableTheadTrThStyles);
			}
			if ($boxWidgetProperties->showPriceTax === true){
				$TableHeadings['price_inc'] = new htmlElement('th');
				$TableHeadings['price_inc']->html(sysLanguage::get('TABLE_HEADING_PRICE_INCLUDING_TAX'));
				$this->applyStyles($TableHeadings['price_inc'], $TableTheadTrThStyles);
			}
			if ($boxWidgetProperties->showTotal === true){
				$TableHeadings['total'] = new htmlElement('th');
				$TableHeadings['total']->html(sysLanguage::get('TABLE_HEADING_TOTAL_EXCLUDING_TAX'));
				$this->applyStyles($TableHeadings['total'], $TableTheadTrThStyles);
			}
			if ($boxWidgetProperties->showTotalTax === true){
				$TableHeadings['total_inc'] = new htmlElement('th');
				$TableHeadings['total_inc']->html(sysLanguage::get('TABLE_HEADING_TOTAL_INCLUDING_TAX'));
				$this->applyStyles($TableHeadings['total_inc'], $TableTheadTrThStyles);
			}
			if ($boxWidgetProperties->showTax === true){
				$TableHeadings['tax'] = new htmlElement('th');
				$TableHeadings['tax']->html(sysLanguage::get('TABLE_HEADING_TAX'));
				$this->applyStyles($TableHeadings['tax'], $TableTheadTrThStyles);
			}

			foreach($TableHeadings as $heading){
				$TableHeadingRow->append($heading);
			}
			$TableHeading->append($TableHeadingRow);
			$Table->append($TableHeading);
		}

		if (!empty($TableHeadings)){
			$TableBody = new htmlElement('tbody');
			$this->applyStyles($TableBody, $TableTbodyStyles);
		}
		foreach($Sale->getProducts() as $OrderedProduct){
			$TableRow = new htmlElement('tr');
			$this->applyStyles($TableRow, $TableTbodyTrStyles);

			foreach($TableHeadings as $type => $El){
				$TableColumn = new htmlElement('td');
				$this->applyStyles($TableColumn, $TableTbodyTrTdStyles);
				switch($type){
					case 'qty':
						$TableColumn->attr('align', 'right');
						$TableColumn->html($OrderedProduct->getQuantity() . '&nbsp;x');
						break;
					case 'name':
						$TableColumn->html($OrderedProduct->getNameHtml($boxWidgetProperties->showExtraInfo));
						break;
					case 'barcode':
						$TableColumn->html($OrderedProduct->displayBarcodes());
						break;
					case 'model':
						$TableColumn->html($OrderedProduct->getModel());
						break;
					case 'tax':
						$TableColumn->attr('align', 'right');
						$TableColumn->html($OrderedProduct->getTaxRate() . '%');
						break;
					case 'price':
						$TableColumn->attr('align', 'right');
						$TableColumn->html('<b>' . $currencies->format($OrderedProduct->getPrice(), true, $Sale->getCurrency(), $Sale->getCurrencyValue()) . '</b>');
						break;
					case 'price_inc':
						$TableColumn->attr('align', 'right');
						$TableColumn->html('<b>' . $currencies->format($OrderedProduct->getPrice(true), true, $Sale->getCurrency(), $Sale->getCurrencyValue()) . '</b>');
						break;
					case 'total':
						$TableColumn->attr('align', 'right');
						$TableColumn->html('<b>' . $currencies->format($OrderedProduct->getPrice() * $OrderedProduct->getQuantity(), true, $Sale->getCurrency(), $Sale->getCurrencyValue()) . '</b>');
						break;
					case 'total_inc':
						$TableColumn->attr('align', 'right');
						$TableColumn->html('<b>' . $currencies->format($OrderedProduct->getPrice(true) * $OrderedProduct->getQuantity(), true, $Sale->getCurrency(), $Sale->getCurrencyValue()) . '</b>');
						break;
				}
				$TableRow->append($TableColumn);
			}
			$TableBody->append($TableRow);
		}
		$Table->append($TableBody);

		$this->setBoxContent($Table->draw());
		return $this->draw();
	}
}

?>