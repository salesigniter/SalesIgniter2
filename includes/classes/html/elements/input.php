<?php
/**
 * Input Element Class
 *
 * @package Html
 */
class htmlElement_input extends htmlElement
{

	protected $_isMultiple = false;

	protected $labelElement;

	protected $labelElementPosition;

	protected $labelElementSeparator;

	public function __construct()
	{
		parent::__construct('input');

		$this->labelElement = false;
		$this->labelElementPosition = 'before';
		$this->labelElementSeparator = '';
	}

	public function isMultiple($val)
	{
		$this->_isMultiple = $val;
		return $this;
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

	public function setPlaceholder($val)
	{
		$this->attr('placeholder', $val);
		return $this;
	}

	public function draw()
	{
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

		if ($this->_isMultiple === true){
			$this->setName($this->attr('name') . '[]');
			$this->css(array(
				'padding'       => '0 32px 0 0',
				'width'         => '100%',
				'line-height'   => '31px',
				'text-indent'   => '5px'
			));

			$MultipleTable = htmlBase::newTable()
				->setCellPadding(3)
				->setCellSpacing(0)
				->addClass('multipleTextInput')
				->css('width', '100%');

			$iconCss = array(
				'position' => 'absolute',
				'top'      => '5px',
				'right'    => '2px',
				'height'   => '31px',
				'width'    => '31px',
				'cursor'   => 'default'
			);

			$AddIcon = htmlBase::newIcon()
				->addClass('ui-widget-content ui-corner-right addInput')
				->setType('plusthick')
				->setTooltip('Add More')
				->css(array_merge($iconCss, array('background-position' => '-82px -111px')));

			$RemoveIcon = htmlBase::newIcon()
				->addClass('ui-widget-content ui-corner-right removeInput')
				->setType('closethick')
				->setTooltip('Remove')
				->hide()
				->css(array_merge($iconCss, array('background-position' => '-166px -110px')));

			$UndoIcon = htmlBase::newIcon()
				->addClass('ui-widget-content ui-corner-right undoRemove')
				->setType('undo')
				->setTooltip('Undo Remove')
				->hide()
				->css(array_merge($iconCss, array('background-position' => '-83px -139px')));

			if ($this->val() != ''){
				$valStr = $this->val();
				if (strstr($valStr, ';')){
					foreach(explode(';', $valStr) as $k => $val){
						$this->val($val);
						if ($k > 0){
							$AddIcon->hide();
							$RemoveIcon->show();
						}

						$MultipleTable->addBodyRow(array(
							'columns' => array(
								array(
									'css'  => array('position' => 'relative'),
									'text' => parent::draw() .
										$AddIcon->draw() .
										$RemoveIcon->draw() .
										$UndoIcon->draw()
								)
							)
						));
					}
				}
				else {
					$MultipleTable->addBodyRow(array(
						'columns' => array(
							array(
								'css'  => array('position' => 'relative'),
								'text' => parent::draw() .
									$AddIcon->draw() .
									$RemoveIcon->draw() .
									$UndoIcon->draw()
							)
						)
					));
				}
			}
			else {
				$MultipleTable->addBodyRow(array(
					'columns' => array(
						array(
							'css'  => array('position' => 'relative'),
							'text' => parent::draw() .
								$AddIcon->draw() .
								$RemoveIcon->draw() .
								$UndoIcon->draw()
						)
					)
				));
			}

			$html .= $MultipleTable->draw();
		}
		else {
			$html .= parent::draw();
		}

		if ($this->labelElement !== false){
			if (in_array($this->labelElementPosition, array('after', 'right', 'bottom', 'below')) || $this->labelElementPosition === false){
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

	public function setValue($val)
	{
		if (is_array($val)){
			$val = implode(';', $val);
		}
		$this->attr('value', stripslashes($val));
		return $this;
	}

	public function setType($type)
	{
		$this->attr('type', $type);
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

	public function setChecked($val)
	{
		if ($val === true){
			$this->attr('checked', 'checked');
		}
		else {
			$this->removeAttr('checked');
		}
		return $this;
	}
}

?>