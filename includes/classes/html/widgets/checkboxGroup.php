<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/**
 * Checkbox Group Element Widget Class
 *
 * @package Html
 */
class htmlWidget_checkboxGroup implements htmlWidgetPlugin
{

	protected $groupElement;

	protected $groupSeparator = '<br>';

	protected $_addClass = array();

	protected $_inputs = array();

	protected $_checkedVal;

	protected $_id = 'random_id';

	protected $_name = 'random_name';

	protected $required = false;

	protected $labelElement;

	protected $labelElementPosition;

	protected $labelElementSeparator;

	public function __construct()
	{
		$this->groupElement = htmlBase::newElement('span');

		$this->labelElement = false;
		$this->labelElementPosition = 'before';
		$this->labelElementSeparator = '';
	}

	public function __call($function, $args)
	{
		$return = call_user_func_array(array($this->groupElement, $function), $args);
		if (!is_object($return)){
			return $return;
		}
		return $this;
	}

	public function addClass($val){
		$this->_addClass[] = $val;
		return $this;
	}

	/* Required Functions From Interface: htmlElementPlugin --BEGIN-- */
	public function startChain()
	{
		return $this;
	}

	public function setId($val)
	{
		$this->_id = $val;
		return $this;
	}

	public function setName($val)
	{
		$this->_name = $val;
		return $this;
	}

	public function draw()
	{
		$html = '';

		$groupHtml = '';
		if (is_array($this->groupSeparator)){
			if ($this->groupSeparator['type'] == 'table'){
				$table = htmlBase::newElement('table')
				->setCellPadding(3)
				->setCellSpacing(0);
				$columns = array();
				foreach($this->_inputs as $k => $button){
					foreach($this->_addClass as $clsName){
						$button->addClass($clsName);
					}
					$button->setType('checkbox');
					$button->setId($this->_id . '_' . $k);
					$button->setName($this->_name . '[]');
					if ($this->required === true){
						$button
						->addClass('required')
						->attr('required', 'required');
					}
					if ($button->val() == $this->_checkedVal){
						$button->setChecked(true);
					}
					$columns[] = array('text' => $button->draw());
					if (sizeof($columns) == $this->groupSeparator['cols']){
						$table->addBodyRow(array(
							'columns' => $columns
						));
						$columns = array();
					}
				}
				if (!empty($columns)){
					$table->addBodyRow(array(
						'columns' => $columns
					));
					$columns = array();
				}
				$groupHtml .= $table->draw();
			}
		}
		else {
			$htmlOutput = array();
			foreach($this->_inputs as $k => $button){
				foreach($this->_addClass as $clsName){
					$button->addClass($clsName);
				}
				$button->setType('checkbox');
				$button->setId($this->_id . '_' . $k);
				$button->setName($this->_name . '[]');
				if ($this->required === true){
					$button
					->addClass('required')
					->attr('required', 'required');
				}
				if ($button->val() == $this->_checkedVal){
					$button->setChecked(true);
				}
				$htmlOutput[] = $button->draw();
			}
			$groupHtml .= implode($this->groupSeparator, $htmlOutput);
		}

		if ($this->labelElement !== false){
			if ($this->hasAttr('id') === true){
				$this->labelElement->attr('for', $this->attr('id'));
			}
			if ($this->labelElementPosition == 'before' || $this->labelElementPosition == 'left' || $this->labelElementPosition == 'top'){
				if ($this->labelElementPosition == 'top'){
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

		$html .= $this->groupElement->html($groupHtml)->draw();

		if ($this->labelElement !== false){
			if ($this->labelElementPosition == 'after' || $this->labelElementPosition == 'right' || $this->labelElementPosition == 'bottom' || $this->labelElementPosition === false){
				if ($this->labelElementPosition == 'bottom'){
					$this->labelElement->css('display', 'block');
				}else{
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

	public function setGroupSeparator($val)
	{
		$this->groupSeparator = $val;
		return $this;
	}

	public function addInput($Input)
	{
		$this->_inputs[] = $Input;
		return $this;
	}

	public function setChecked($val)
	{
		$this->_checkedVal = $val;
		return $this;
	}

	public function setRequired($val)
	{
		$this->required = $val;
		return $this;
	}

	public function setLabel($val)
	{
		if ($this->labelElement === false){
			$this->labelElement = new htmlElement('label');
			if ($this->labelElementPosition === false){
				$this->labelElementPosition = 'after';
			}
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