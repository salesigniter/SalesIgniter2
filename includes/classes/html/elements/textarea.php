<?php
/**
 * Textarea Element Class
 * @package Html
 */
class htmlElement_textarea implements htmlElementPlugin {
	protected $textareaElement, $labelElement, $labelElementPosition;
	
	public function __construct(){
		$this->textareaElement = new htmlElement('textarea');
		$this->labelElement = false;
		$this->labelElementPosition = false;
	}
	
	public function __call($function, $args){
		$return = call_user_func_array(array($this->textareaElement, $function), $args);
		if (!is_object($return)){
			return $return;
		}
		return $this;
	}
	
	/* Required Functions From Interface: htmlElementPlugin --BEGIN-- */
	public function startChain(){
		return $this;
	}
	
	public function setId($val){
		$this->textareaElement->attr('id', $val);
		return $this;
	}
	
	public function setName($val){
		$this->textareaElement->attr('name', $val);
		return $this;
	}

	public function setRows($val){
		$this->textareaElement->attr('rows', $val);
		return $this;
	}

	public function setCols($val){
		$this->textareaElement->attr('cols', $val);
		return $this;
	}

	
	public function draw(){
		$html = '';
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

		$html .= $this->textareaElement->draw();

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