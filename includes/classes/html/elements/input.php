<?php
/**
 * Input Element Class
 *
 * @package Html
 */
class htmlElement_input extends htmlElement
{

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

	public function draw()
	{
		$html = '';
		if ($this->labelElement !== false){
			if ($this->hasAttr('id') === true){
				$this->labelElement->attr('for', $this->attr('id'));
			}
			if ($this->labelElementPosition == 'before'){
				$html .= $this->labelElement->draw();
				if (is_object($this->labelElementSeparator)){
					$html .= $this->labelElementSeparator->draw();
				}
				else {
					$html .= $this->labelElementSeparator;
				}
			}
		}

		$html .= parent::draw();

		if ($this->labelElement !== false){
			if ($this->labelElementPosition == 'after' || $this->labelElementPosition === false){
				if (is_object($this->labelElementSeparator)){
					$html .= $this->labelElementSeparator->draw();
				}
				else {
					$html .= $this->labelElementSeparator;
				}
				$html .= $this->labelElement->draw();
			}
		}
		return $html;
	}

	/* Required Functions From Interface: htmlElementPlugin --END-- */

	public function setValue($val)
	{
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