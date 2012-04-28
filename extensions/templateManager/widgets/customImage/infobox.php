<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class InfoBoxCustomImage extends InfoBoxAbstract
{

	public function __construct() {
		global $App;
		$this->init('customImage');
	}

	public function showLayoutPreview($WidgetSettings) {
		$return = '';
		if (isset($WidgetSettings->images) && sizeof($WidgetSettings->images) == 1){
			foreach($WidgetSettings->images as $iInfo){
				if (isset($iInfo->source->{Session::get('languages_id')})){
					$return = '<img src="' . $iInfo->source->{Session::get('languages_id')} . '" />';
					break;
				}

			}
		}else{
			$return = $this->getBoxCode() . '<br>' . sizeof($WidgetSettings->images) . ' Images';
		}
		return $return;
	}

	public function show() {
		global $appExtension;
		$boxWidgetProperties = $this->getWidgetProperties();

		$ImageHtml = array();
		foreach($boxWidgetProperties->images as $iInfo){
			$imageSource = $iInfo->source->{Session::get('languages_id')};
			$linkInfo = $iInfo->link;
			
			$ImageEl = htmlBase::newElement('image')
				->setSource($imageSource);
			
			if ($linkInfo !== false){
				$LinkEl = htmlBase::newElement('a')
					->append($ImageEl);
				$this->parseLink($LinkEl, $linkInfo);

				$ImageHtml[] = $LinkEl->draw();
			}else{
				$ImageHtml[] = $ImageEl->draw();
			}
		}
		$this->setBoxContent(implode('', $ImageHtml));
		return $this->draw();
	}

	private function parseLink(&$LinkEl, $lInfo){
		if ($lInfo->type == 'app'){
			$getParams = null;
			if (stristr($lInfo->app->name, '/')){
				$extInfo = explode('/', $lInfo->app->name);
				$application = $extInfo[1];
				$getParams = 'appExt=' . $extInfo[0];
			}
			else {
				$application = $lInfo->app->name;
			}

			$LinkEl->setHref(itw_app_link($getParams, $application, $lInfo->app->page));
		}
		elseif ($lInfo->type == 'category'){
			$LinkEl->setHref(itw_app_link($lInfo->get_vars, $lInfo->app->name, $lInfo->app->page));
		}
		elseif ($lInfo->type == 'custom') {
			$LinkEl->setHref($lInfo->url);
		}

		if ($lInfo->type != 'none'){
			if ($lInfo->target == 'new'){
				$LinkEl->attr('target', '_blank');
			}
			elseif ($lInfo->target == 'dialog') {
				$LinkEl->attr('onclick', 'Javascript:popupWindow(this.href);');
			}
		}
	}
}

?>