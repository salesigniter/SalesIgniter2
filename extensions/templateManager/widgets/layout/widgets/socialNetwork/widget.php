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

class TemplateManagerWidgetSocialNetwork extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('socialNetwork');
	}

	public function show()
	{

		$boxWidgetProperties = $this->getWidgetProperties();
		$youtube = (isset($boxWidgetProperties->youtube) && $boxWidgetProperties->youtube != '') ? '<a target="_blank" href="' . $boxWidgetProperties->youtube . '"><img src="' . sysConfig::get('DIR_WS_TEMPLATE') . 'images/youtubeSocial.png" /></a>' . $boxWidgetProperties->youtubeText : '';
		$facebook = (isset($boxWidgetProperties->facebook) && $boxWidgetProperties->facebook != '') ? '<a target="_blank" href="' . $boxWidgetProperties->facebook . '"><img src="' . sysConfig::get('DIR_WS_TEMPLATE') . 'images/facebookSocial.png" /></a>' . $boxWidgetProperties->facebookText : '';
		$twitter = (isset($boxWidgetProperties->twitter) && $boxWidgetProperties->twitter != '') ? '<a href="' . $boxWidgetProperties->twitter . '" target="_blank"><img src="' . sysConfig::get('DIR_WS_TEMPLATE') . 'images/twitterSocial.png" /></a>' . $boxWidgetProperties->twitterText : '';
		$linked = (isset($boxWidgetProperties->linked) && $boxWidgetProperties->linked != '') ? '<a href="' . $boxWidgetProperties->linked . '" target="_blank"><img src="' . sysConfig::get('DIR_WS_TEMPLATE') . 'images/linkedinSocial.png" /></a>' . $boxWidgetProperties->linkedText : '';
		$email = (isset($boxWidgetProperties->email) && $boxWidgetProperties->email != '') ? '<a href="' . $boxWidgetProperties->email . '" target="_blank"><img src="' . sysConfig::get('DIR_WS_TEMPLATE') . 'images/emailSocial.png" /></a>' . $boxWidgetProperties->emailText : '';
		$beforeText = (isset($boxWidgetProperties->beforeText) && $boxWidgetProperties->beforeText != '') ? $boxWidgetProperties->beforeText : '';

		$htmlText = htmlBase::newElement('div')
			->addClass('socialNetwork')
			->html($beforeText . $youtube . $facebook . $linked . $twitter . $email);

		$this->setBoxContent($htmlText->draw());
		return $this->draw();
	}
}

?>