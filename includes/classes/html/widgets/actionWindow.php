<?php
/**
 * Infobox Widget Class
 *
 * @package Html
 */
class htmlWidget_actionWindow implements htmlWidgetPlugin
{

	protected $windowElement;

	protected $menuElement;

	protected $contentElement;

	public function __construct()
	{
		$this->windowElement = htmlBase::newElement('div')
			->addClass('actionWindow');
		$this->menuElement = htmlBase::newPageMenu();
		$this->contentElement = htmlBase::newElement('div')
			->addClass('actionWindowContent');
	}

	public function __call($function, $args)
	{
		$return = call_user_func_array(array($this->boxElement, $function), $args);
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
		$this->windowElement->attr('id', $val);
		return $this;
	}

	public function setName($val)
	{
		$this->windowElement->attr('name', $val);
		return $this;
	}

	public function draw()
	{
		$this->windowElement
			->append($this->menuElement)
			->append($this->contentElement);
		return $this->windowElement->draw();
	}

	/* Required Functions From Interface: htmlElementPlugin --END-- */

	public function addButton($buttonObj)
	{
		$this->menuElement->addMenuItem($buttonObj);
		return $this;
	}

	public function setContent($content)
	{
		if (is_object($content)){
			$this->contentElement->html($content->draw());
		}
		else {
			$this->contentElement->html($content);
		}
		return $this;
	}

	public function setHeader($header){
		$this->windowElement->data('header', $header);
		return $this;
	}
}
