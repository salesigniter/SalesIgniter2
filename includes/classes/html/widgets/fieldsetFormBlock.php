<?php
/**
 * Fieldset Form Block Element Class
 *
 * @package Html
 */

htmlBase::loadElement('fieldset');

class htmlWidget_fieldsetFormBlock extends htmlElement_fieldset
{

	protected $Blocks = array();

	private $_displayOrder = 0;

	public function draw()
	{
		$html = '';
		if ($this->legendElement !== false){
			$this->legendElement
			->css(array(
				'margin-left' => '10px',
				'padding'     => '5px'
			))
			->addClass('ui-widget-content ui-corner-all');
			$this->fieldsetElement->prepend($this->legendElement);
		}

		$this->fieldsetElement
		->addClass('ui-widget-content ui-corner-all fieldsetForm')
		->css('padding', 0);

		$BlocksSorted = $this->Blocks;
		usort($BlocksSorted, function ($a, $b)
		{
			return ($a['displayOrder'] > $b['displayOrder'] ? 1 : -1);
		});

		$List = htmlBase::newList()
		->addClass('ui-widget-content')
		->css(array(
			'list-style'   => 'none',
			'margin'       => '10px 0 0 0',
			'padding'      => 0,
			'border-width' => '1px 0 0 0',
			'background'   => 'transparent',
			'width'        => '100%'
		));

		foreach($BlocksSorted as $k => $bInfo){
			$BlockListItem = htmlBase::newElement('li')
			->addClass('ui-widget-content')
			->css(array(
				'border-top-width'   => 0,
				'border-left-width'  => 0,
				'border-right-width' => 0,
				'position'           => 'relative',
				'padding'            => '15px 15px 15px 10px',
				'background'         => 'transparent'
			));
			if (isset($BlocksSorted[$k + 1]) === false){
				$BlockListItem->css('border-bottom-width', 0);
			}

			$LabelColumn = htmlBase::newElement('div')
			->addClass('column fieldsetFormMainLabel')
			->css(array(
				'width'          => '25%',
				'vertical-align' => 'top'
			))
			->html($bInfo['label']);

			$RowsColumn = htmlBase::newElement('div')
			->addClass('column')
			->css(array(
				'width' => '75%'
			));

			foreach($bInfo['rows'] as $Row){
				$InputRow = htmlBase::newElement('div')
				->css(array(
					'width'  => '100%',
					'margin' => '0 0 10px 0'
				));

				$numberOfFields = sizeof($Row);
				$ColumnWidth = 100 / $numberOfFields;

				foreach($Row as $Field){
					if ($Field->attr('type') != 'radio' && $Field->attr('type') != 'checkbox'){
						if ($numberOfFields == 1){
							$Field->css('width', '99%');
						}
						else {
							$Field->css('width', '98%');
						}
					}

					$InputColumn = htmlBase::newElement('div')
					->addClass('column')
					->css(array(
						'width'          => $ColumnWidth . '%',
						'vertical-align' => 'top'
					))
					->append($Field);

					$InputRow->append($InputColumn);
				}
				$RowsColumn->append($InputRow);
			}
			$BlockListItem
			->append($LabelColumn)
			->append($RowsColumn);

			$List->addItemObj($BlockListItem);
		}

		$this->fieldsetElement->append($List);

		$html .= $this->fieldsetElement->draw();
		return $html;
	}

	/**
	 * @param       $id
	 * @param       $text
	 * @param array $bInfo
	 * @param int   $displayOrder
	 * @return htmlElement_fieldsetFormBlock
	 */
	public function addBlock($id, $text, array $bInfo, $displayOrder = 0)
	{
		if ($displayOrder === 0){
			$displayOrder = $this->_displayOrder;
			$this->_displayOrder++;
		}
		elseif ($displayOrder > $this->_displayOrder) {
			$this->_displayOrder = $displayOrder;
		}

		$this->Blocks[$id] = array(
			'label'        => $text,
			'displayOrder' => $displayOrder,
			'rows'         => $bInfo
		);
		return $this;
	}

	/**
	 * @param $id
	 * @return htmlElement_fieldsetFormBlock
	 */
	public function removeBlock($id)
	{
		if (isset($this->Blocks[$id])){
			unset($this->Blocks[$id]);
		}
		return $this;
	}
}
