<?php
/**
 * Page Menu Widget Class
 *
 * @package Html
 */
class htmlWidget_pageMenu implements htmlWidgetPlugin
{

	protected $menuElement;

	protected $_menuItems = array();

	public function __construct()
	{
		$this->menuElement = htmlBase::newElement('div')
			->addClass('ApplicationPageMenu');

		$this->_menuItems = array();
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
		$this->menuElement->attr('id', $val);
		return $this;
	}

	public function setName($val)
	{
		$this->menuElement->attr('name', $val);
		return $this;
	}

	public function draw()
	{
		$MenuList = htmlBase::newList();
		foreach($this->_menuItems as $item){
			$ItemLi = htmlBase::newElement('li')
				->addClass('rootItem');
			if (is_array($item)){
				$Icon = '';
				if (isset($item['icon'])){
					$Icon = htmlBase::newElement('icon')
						->setType($item['icon'])
						->draw();
				}

				$Text = htmlBase::newElement('span')
					->html($item['text']);

				$ItemSubMenu = '';
				if (isset($item['children'])){
					$ItemSubMenu = htmlBase::newList()
						->addClass('subMenu');
					foreach($item['children'] as $obj){
						$ItemSubLi = htmlBase::newElement('li')
							->addClass('subItem')
							->html($obj->draw());

						$ItemSubMenu->addItemObj($ItemSubLi);
					}
					$ItemSubMenu = $ItemSubMenu->draw();
				}

				$ItemLi->html($Icon . $Text->draw() . $ItemSubMenu);
			}
			elseif (is_object($item)) {
				$ItemLi->html($item->draw());
			}

			$MenuList->addItemObj($ItemLi);
		}

		$this->menuElement->append($MenuList);
		return $this->menuElement->draw();
	}

	/* Required Functions From Interface: htmlElementPlugin --END-- */

	public function addMenuItem($item)
	{
		$this->_menuItems[] = $item;
	}
}
