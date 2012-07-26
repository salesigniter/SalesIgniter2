<?php
/**
 * Select Box Element Class
 *
 * @package Html
 */
class htmlElement_selectbox extends htmlElement
{

	protected $selectOptions;

	protected $selectedOptionValue = null;

	protected $selectedOptionIndex = null;

	protected $labelElement;

	protected $labelElementPosition;

	protected $labelElementSeparator;

	public function __construct()
	{
		parent::__construct('select');

		$this->selectOptions = array();
		$this->optionsAppended = false;
		$this->labelElement = false;
		$this->labelElementPosition = 'before';
		$this->labelElementSeparator = '';
	}

	/* Required Functions From Interface: htmlElementPlugin --BEGIN-- */
	public function startChain()
	{
		return $this;
	}

	public function setId($val)
	{
		$this->attr('id', $val);
		return $this;
	}

	public function setName($val)
	{
		$this->attr('name', $val);
		return $this;
	}

	public function draw($skipOptionAdd = false)
	{
		if ($this->optionsAppended === false){
			$options = $this->selectOptions;
		}
		else {
			$options = &$this->getAppendedElements();
		}

		foreach($options as $index => $optionObj){
			if ($this->hasAttr('multiple') === false){
				if ((string)$optionObj->val() === (string)$this->selectedOptionValue){
					$optionObj->attr('selected', 'selected');
				}
				elseif ($this->selectedOptionIndex !== null && (int)$index === (int)$this->selectedOptionIndex) {
					$optionObj->attr('selected', 'selected');
				}
				else {
					$optionObj->removeAttr('selected');
				}
			}

			if ($this->optionsAppended === false){
				$this->append($optionObj);
			}
		}

		if ($this->optionsAppended === false){
			$this->optionsAppended = true;
		}

		$html = '';
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

		/**
		 * Commented out until i have some time to test the new field functionality
		 */
		/*
		$listItems = '';
		$valueText = array();
		foreach($options as $index => $optionObj){
			$AddCls = '';
			if ($optionObj->hasAttr('selected')){
				$valueText[] = $optionObj->html();
				$AddCls = ' ui-state-active';
			}
			$listItems .= '<li class="ui-selectbox-searchable-option' . $AddCls . '" value="' . $optionObj->attr('value') . '">' . $optionObj->html() . '</li>';
		}
		if (empty($valueText)){
			$valueText = 'Please Select';
		}else{
			$valueText = implode(', ', $valueText);
		}
		$html .= '<span class="ui-selectbox-searchable ui-corner-all">' .
			'<div class="ui-selectbox-searchable-value-box ui-corner-all ui-state-default">' .
				'<div class="ui-icon ui-selectbox-searchable-trigger"></div>' .
				'<div class="ui-selectbox-searchable-value">' . $valueText . '</div>' .
				$this->selectElement->hide()->draw() .
				'<input type="text" class="ui-selectbox-searchable-search-input">' .
			'</div>' .
			'<div class="ui-selectbox-searchable-options-box ui-corner-bottom">' .
				'<ul>' .
					$listItems .
				'</ul>' .
			'</div>' .
		'</span>';
		*/
		$html .= parent::draw();

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

	public function val($val = null)
	{
		if ($val === null){
			return $this->selectedOptionValue;
		}
		$this->selectOptionByValue($val);
		return $this;
	}

	public function addOption($val, $html = '', $selected = false, $attributes = null)
	{
		$optionEl = new htmlElement('option');
		$optionEl->attr('value', $val);
		if (strlen($html) > 0){
			$optionEl->html($html);
		}
		/*if ($selected === true){
			$optionEl->attr('selected', 'selected');
		}*/

		if (is_null($attributes) === false){
			foreach($attributes as $k => $v){
				$optionEl->attr($k, $v);
			}
		}

		$this->selectOptions[] = $optionEl;
		return $this;
	}

	public function addOptionWithAttributes($val, $html = '', $attributes, $selected = false)
	{
		$optionEl = new htmlElement('option');
		$optionEl->attr('value', $val);
		foreach($attributes as $attr){
			$optionEl->attr($attr['name'], $attr['value']);
		}
		if (strlen($html) > 0){
			$optionEl->html($html);
		}
		if ($selected === true){
			$optionEl->attr('selected', 'selected');
		}
		$this->selectOptions[] = $optionEl;
		return $this;
	}

	public function removeOption($optionValue)
	{
		if (!empty($this->selectOptions)){
			foreach($this->selectOptions as $idx => $optionObj){
				if ($optionObj->val() == $optionValue){
					unset($this->selectOptions[$idx]);
					ksort($this->selectOptions);
					break;
				}
			}
		}
		return $this;
	}

	public function addOptionObj($optionObj)
	{
		$this->selectOptions[] = $optionObj;
		return $this;
	}

	public function getOptions(){
		return $this->selectOptions;
	}

	public function selectOptionByIndex()
	{
		die('never used, guess it is time.');
	}

	public function selectOptionByValue($val)
	{
		$this->selectedOptionValue = $val;
		return $this;
	}

	public function change($event)
	{
		$this->attr('onchange', $event);
		return $this;
	}

	public function setSize($val)
	{
		$this->attr('size', $val);
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

?>