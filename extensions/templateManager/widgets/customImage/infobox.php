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
		if (isset($WidgetSettings->images) && sizeof($WidgetSettings->images) > 0){
			foreach($WidgetSettings->images as $Image){
				if (isset($Image->{Session::get('languages_id')}->source)){
					$return .= '<img ' .
						'src="' . $Image->{Session::get('languages_id')}->source . '" ' .
						'width="' . $Image->{Session::get('languages_id')}->dimensions->width . '" ' .
						'height="' . $Image->{Session::get('languages_id')}->dimensions->height . '" ' .
						'/>';
				}else{
					$return = $this->getBoxCode() . '<br>Image Not Available';
				}
			}
		}else{
			$return = $this->getBoxCode();
		}
		return $return;
	}

	public function show() {
		global $appExtension;
		$boxWidgetProperties = $this->getWidgetProperties();
		$TemplateManager = $appExtension->getExtension('templateManager');

		$ImageHtml = array();
		foreach($boxWidgetProperties->images as $iInfo){
			$imageSource = $iInfo->{Session::get('languages_id')}->source;
			$linkInfo = $iInfo->{Session::get('languages_id')}->link;
			
			$ImageEl = htmlBase::newElement('image')
				->attr('width', $iInfo->{Session::get('languages_id')}->dimensions->width)
				->attr('height', $iInfo->{Session::get('languages_id')}->dimensions->height)
				->setSource($imageSource);
			
			if ($linkInfo !== false){
				$LinkEl = htmlBase::newElement('a')
					->append($ImageEl)
					->setHref($TemplateManager->parseItemLink($linkInfo));

				$ImageHtml[] = $LinkEl->draw();
			}else{
				$ImageHtml[] = $ImageEl->draw();
			}
		}
		$this->setBoxContent(implode('', $ImageHtml));
		return $this->draw();
	}
}

?>