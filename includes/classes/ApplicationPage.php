<?php
class ApplicationPage
{

	protected $pageName;

	protected $_pageForm = array();

	protected $_pageMenu = array();

	public function __construct($pageName){
		$this->pageName = $pageName;
	}

	public function getName(){
		return $this->pageName;
	}

	public function setPageFormParam($k, $v = null){
		if (is_array($k)){
			$this->_pageForm = $k;
		}else{
			$this->_pageForm[$k] = $v;
		}
	}

	public function hasPageForm(){
		return (empty($this->_pageForm) === false);
	}

	public function getPageFormName(){
		return $this->_pageForm['name'];
	}

	public function getPageFormAction(){
		return $this->_pageForm['action'];
	}

	public function getPageFormMethod(){
		return $this->_pageForm['method'];
	}

	public function hasMenu(){
		return (empty($this->_pageMenu) === false);
	}

	public function addMenuItem($item){
		$this->_pageMenu[] = $item;
	}

	public function drawMenu(){
		$MenuContainer = htmlBase::newElement('div')
		->addClass('ApplicationPageMenu');

		$MenuList = htmlBase::newList();
		foreach($this->_pageMenu as $item){
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
			elseif (is_object($item)){
				$ItemLi->html($item->draw());
			}

			$MenuList->addItemObj($ItemLi);
		}
		$MenuContainer->append($MenuList);

		return $MenuContainer->draw();
	}
}