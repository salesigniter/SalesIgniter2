<?php
class PurchaseTypeTabReservation_tab_pricing
{

	private $heading;

	private $displayOrder = 1;

	public function __construct()
	{
		//$this->setHeading(sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING'));
	}

	public function setHeading($val)
	{
		$this->heading = $val;
	}

	public function getHeading()
	{
		return $this->heading;
	}

	public function getDisplayOrder()
	{
		return $this->displayOrder;
	}

	public function setDisplayOrder($val)
	{
		$this->displayOrder = $val;
	}

	public function addTab(htmlWidget_tabs &$TabsObj, Product $Product, PurchaseType_reservation $PurchaseType)
	{
		$typeName = $PurchaseType->getCode();
		$typeText = sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING_PRICING');

		$htype = htmlBase::newElement('selectbox')->attr('id', 'types_select');
		foreach(PurchaseType_reservation_utilities::getRentalTypes() as $iType){
			$htype->addOption($iType['pay_per_rental_types_id'], $iType['pay_per_rental_types_name']);
		}
		/*Period Metrics*/

		$Qcheck = Doctrine_Query::create()
			->select('MAX(price_per_rental_per_products_id) as nextId')
			->from('PricePerRentalPerProducts')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$Table = htmlBase::newElement('table')
			->setCellPadding(3)
			->setCellSpacing(0)
			->addClass('ui-widget ui-widget-content pprPriceTable')
			->css(array(
			'width' => '100%'
		))
			->attr('data-next_id', $Qcheck[0]['nextId'] + 1)
			->attr('language_id', Session::get('languages_id'));

		$Table->addHeaderRow(array(
			'addCls'  => 'ui-state-hover pprPriceTableHeader',
			'columns' => array(
				array(
					'text' => '<div style="float:left;width:80px;">' . sysLanguage::get('TABLE_HEADING_NUMBER_OF') . '</div>' . '<div style="float:left;width:100px;">' . sysLanguage::get('TABLE_HEADING_TYPE') . '</div>' . '<div style="float:left;width:80px;">' . sysLanguage::get('TABLE_HEADING_PRICE') . '</div>' . '<div style="float:left;width:150px;">' . sysLanguage::get('TABLE_HEADING_DETAILS') . '</div>' . '<div style="float:left;width:40px;">' . htmlBase::newElement('icon')
						->setType('insert')->addClass('insertIcon')->draw() . '</div><br style="clear:both"/>'
				)
			)
		));

		$deleteIcon = htmlBase::newElement('icon')->setType('delete')->addClass('deleteIcon')->draw();

		$sortableList = htmlBase::newElement('sortable_list');
		foreach(PurchaseType_reservation_utilities::getRentalPricing($PurchaseType->getPayPerRentalId()) as $iPrice){
			$pprid = $iPrice['price_per_rental_per_products_id'];
			$Text = htmlBase::newElement('div');
			$br = htmlBase::newElement('br');
			foreach(sysLanguage::getLanguages() as $lInfo){
				$Textl = htmlBase::newElement('input')
					->addClass('ui-widget-content')
					->setLabel($lInfo['showName']())
					->setLabelPosition('before')
					->setName('pprp[' . $pprid . '][details][' . $lInfo['id'] . ']')
					->css(array(
					'width' => '100%'
				));
				foreach($iPrice['Description'] as $desc){
					if ($lInfo['id'] == $desc['language_id']){
						$Textl->val($desc['price_per_rental_per_products_name']);
						break;
					}
				}

				$Text->append($Textl)
					->append($br);
			}

			$numberOf = htmlBase::newElement('input')
				->addClass('ui-widget-content')
				->setName('pprp[' . $pprid . '][number_of]')
				->attr('size', '8')
				->val($iPrice['number_of']);

			$price = htmlBase::newElement('input')
				->addClass('ui-widget-content')
				->setName('pprp[' . $pprid . '][price]')
				->attr('size', '6')
				->val($iPrice['price']);

			$type = htmlBase::newElement('selectbox')
				->addClass('ui-widget-content')
				->setName('pprp[' . $pprid . '][type]')
				->selectOptionByValue($iPrice['pay_per_rental_types_id']);

			foreach(PurchaseType_reservation_utilities::getRentalTypes() as $iType){
				$type->addOption($iType['pay_per_rental_types_id'], $iType['pay_per_rental_types_name']);
			}

			$divLi1 = '<div style="float:left;width:80px;">' . $numberOf->draw() . '</div>';
			$divLi2 = '<div style="float:left;width:100px;">' . $type->draw() . '</div>';
			$divLi3 = '<div style="float:left;width:80px;">' . $price->draw() . '</div>';
			$divLi4 = '<div style="float:left;width:150px;">' . $Text->draw() . '</div>';
			$divLi5 = '<div style="float:left;width:40px;">' . $deleteIcon . '</div>';

			$liObj = new htmlElement('li');
			$liObj->css(array(
				'font-size'     => '.8em',
				'line-height'   => '1.1em',
				'border-bottom' => '1px solid #cccccc',
				'cursor'        => 'crosshair'
			))
				->html($divLi1 . $divLi2 . $divLi3 . $divLi4 . $divLi5 . '<br style="clear:both;"/>');

			$sortableList->addItemObj($liObj);
		}

		$Table->addBodyRow(array(
			'columns' => array(
				array(
					'align'  => 'center',
					'text'   => $sortableList->draw(),
					'addCls' => 'pricePPR'
				)
			)
		));

		/*End Metrics*/

		/*time periods*/
		$Periods = PurchaseType_reservation_utilities::getRentalPeriods();
		if (count($Periods) > 0){
			$pricingPeriods = htmlBase::newElement('table')
				->setCellPadding(3)
				->setCellSpacing(0);

			$pricingPeriods->addBodyRow(array(
				'columns' => array(
					array('text' => '&nbsp;'),
					array(
						'addCls' => 'main',
						'text'   => '<h3>' . sysLanguage::get('TEXT_PAY_PER_RENTAL_PRICING_PERIODS') . '</h3>',
						'css'    => array(
							'color' => '#ff0000'
						)
					)
				)
			));

			foreach($Periods as $iPeriod){
				$periodPrice = htmlBase::newElement('input')
					->setName('reservation_price_period[' . $iPeriod['period_id'] . ']')
					->setLabel($iPeriod['period_name'])
					->setLabelPosition('before');

				if ($Product->getId() > 0){
					$Price = PurchaseType_reservation_utilities::getProductPeriods($Product->getId(), $iPeriod['period_id']);

					$periodPrice->setValue($Price[0]['price']);
				}

				$pricingPeriods->addBodyRow(array(
					'columns' => array(
						array(
							'addCls' => 'main',
							'text'   => $periodPrice->draw()
						),
					)
				));
			}
		}
		/*end time periods*/
		$pricingTable = htmlBase::newElement('table')
			->setCellPadding(3)
			->setCellSpacing(0);

		$pricingTable->addBodyRow(array(
			'columns' => array(
				array(
					'addCls' => 'mainPricePPR',
					'text'   => '<b>' . $Table->draw() . $htype->draw() . '</b>'
				),
			)
		));
		$TabsObj->addTabHeader('productPricingTab_' . $typeName, array('text' => $typeText))
			->addTabPage('productPricingTab_' . $typeName, array('text' => (isset($pricingPeriods) ? $pricingPeriods->draw() : '') . $pricingTable->draw()));
	}
}

?>