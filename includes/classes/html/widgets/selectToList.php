<?php
/**
 * Select To Select Widget Class
 *
 * @package Html
 */
class htmlWidget_selectToList implements htmlWidgetPlugin
{

	protected $mainElement;

	protected $fromElement;

	protected $toElement;

	protected $_selectedOptions = array();

	protected $buttonsElement;

	protected $labelElement;

	protected $labelElementPosition;

	protected $labelElementSeparator;

	public function __construct()
	{
		$this->mainElement = htmlBase::newElement('div')
			->addClass('selectToList');

		$MoveButton = htmlBase::newButton()
			->addClass('addButton')
			->setIcon('thickArrowWest')
			->setText('Add To List');

		$this->buttonsElement = htmlBase::newElement('div')
			->append($MoveButton);

		$this->fromElement = htmlBase::newSelectbox()
			->addClass('fromElement')
			->attr('multiple', 'multiple');

		$this->toElement = htmlBase::newList()
			->addClass('toElement');

		$this->labelElement = false;
		$this->labelElementPosition = 'before';
		$this->labelElementSeparator = '';
	}

	public function __call($function, $args)
	{
		$return = call_user_func_array(array($this->mainElement, $function), $args);
		if (!is_object($return)){
			return $return;
		}
		return $this;
	}

	/* Required Functions From Interface: htmlElementPlugin --BEGIN-- */
	public function startChain()
	{
		return $this;
	}

	public function setId($val)
	{
		$this->mainElement->attr('id', $val);
		return $this;
	}

	public function setName($val)
	{
		$this->mainElement->attr('data-field_name', $val);
		return $this;
	}

	public function draw()
	{
		if ($this->labelElement !== false){
			if ($this->hasAttr('id') === true){
				$this->labelElement->attr('for', $this->attr('id'));
			}
			if (in_array($this->labelElementPosition, array('before', 'left', 'top', 'above'))){
				if (in_array($this->labelElementPosition, array('top', 'above'))){
					$this->labelElement->css('display', 'block');
				}

				$html .= $this->labelElement->draw();
				if (is_object($this->labelElementSeparator)){
					$html .= $this->labelElementSeparator->draw();
				}
				else {
					$html .= $this->labelElementSeparator;
				}
			}
		}

		if (sizeof($this->_selectedOptions) > 0){
			$Options = $this->fromElement->getOptions();
			$deleteIcon = htmlBase::newIcon()
				->addClass('ui-icon ui-icon-closethick removeButton');

			foreach($Options as $Option){
				if (in_array($Option->val(), $this->_selectedOptions)){
					$hiddenInput = htmlBase::newInput()
						->setType('hidden')
						->attr('name', $this->mainElement->attr('data-field_name') . '[]')
						->setValue($Option->attr('value'));

					$itemText = htmlBase::newElement('span')
						->addClass('itemText')
						->html($Option->html());

					$ListItem = new htmlElement('li');
					$ListItem->addClass('ui-widget-content ui-corner-all');
					$ListItem->append($deleteIcon);
					$ListItem->append($itemText);
					$ListItem->append($hiddenInput);

					$this->toElement->append($ListItem);
					$this->fromElement->removeOption($Option->val());
				}
			}
			unset($Option);
			unset($Options);
		}

		$fromColumn = htmlBase::newElement('div')
			->addClass('column fromElementColumn')
			->append($this->fromElement);

		$buttonsColumn = htmlBase::newElement('div')
			->addClass('column buttonColumn')
			->append($this->buttonsElement);

		$toColumn = htmlBase::newElement('div')
			->addClass('column toElementColumn')
			->append($this->toElement);

		$this->mainElement->append($fromColumn);
		$this->mainElement->append($buttonsColumn);
		$this->mainElement->append($toColumn);

		$html .= $this->mainElement->draw();

		if ($this->labelElement !== false){
			if (in_array($this->labelElementPosition, array(
				'after', 'right', 'bottom', 'below'
			)) || $this->labelElementPosition === false
			){
				if (in_array($this->labelElementPosition, array('bottom', 'below'))){
					$this->labelElement->css('display', 'block');
				}
				else {
					if (is_object($this->labelElementSeparator)){
						$html .= $this->labelElementSeparator->draw();
					}
					else {
						$html .= $this->labelElementSeparator;
					}
				}

				$html .= $this->labelElement->draw();
			}
		}

		return $html;
	}

	/* Required Functions From Interface: htmlElementPlugin --END-- */

	public function addOption($val, $html = '', $selected = false, $attributes = null)
	{
		$this->fromElement->addOption($val, $html, $selected, $attributes);
		return $this;
	}

	public function setSize($val)
	{
		$this->fromElement->setSize($val);
		return $this;
	}

	public function setSelected($val)
	{
		if (is_array($val) === false){
			$val = explode(',', $val);
		}
		$this->_selectedOptions = $val;
		return $this;
	}

	public function setLabel($val)
	{
		if ($this->labelElement === false){
			$this->labelElement = new htmlElement('label');
		}
		$this->labelElement->html($val);
		return $this;
	}

	public function setLabelPosition($val)
	{
		$this->labelElementPosition = $val;
		return $this;
	}

	public function setLabelSeparator($val)
	{
		$this->labelElementSeparator = $val;
		return $this;
	}
}
