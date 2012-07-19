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

class TemplateManagerWidgetCustomImage extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('customImage');
	}

	public function showLayoutPreview($WidgetSettings)
	{
		$return = '';
		if (isset($WidgetSettings['settings']->images) && sizeof($WidgetSettings['settings']->images) > 0){
			foreach($WidgetSettings['settings']->images as $Image){
				if (isset($Image->{Session::get('languages_id')}->source)){
					$return .= '<img ' .
						'src="' . $Image->{Session::get('languages_id')}->source . '" ' .
						'width="' . $Image->{Session::get('languages_id')}->dimensions->width . '" ' .
						'height="' . $Image->{Session::get('languages_id')}->dimensions->height . '" ' .
						'/>';
				}
				else {
					$return = $this->getTitle() . '<br>Image Not Available';
				}
			}
		}
		else {
			$return = $this->getTitle();
		}
		return $return;
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
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
			}
			else {
				$ImageHtml[] = $ImageEl->draw();
			}
		}
		$this->setBoxContent(implode('', $ImageHtml));
		return $this->draw();
	}
}

?>