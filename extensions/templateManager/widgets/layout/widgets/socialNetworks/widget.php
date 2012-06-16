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

class TemplateManagerWidgetSocialNetworks extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('socialNetworks');
	}

	public function show()
	{

		$boxWidgetProperties = $this->getWidgetProperties();
		$facebook = (isset($boxWidgetProperties->facebook) && $boxWidgetProperties->facebook != '') ? '<a target="_blank" href="' . $boxWidgetProperties->facebook . '"><img src="' . sysConfig::getDirWsCatalog() . 'images/facebookSocial.png" /></a>' . sysLanguage::get('WIDGET_SOCIAL_NETWORKS_FACEBOOK_TEXT') : '';
		$twitter = (isset($boxWidgetProperties->twitter) && $boxWidgetProperties->twitter != '') ? '<a href="' . $boxWidgetProperties->twitter . '" target="_blank"><img src="' . sysConfig::getDirWsCatalog() . 'images/twitterSocial.png" /></a>' . sysLanguage::get('WIDGET_SOCIAL_NETWORKS_TWITTER_TEXT') : '';
		$email = (isset($boxWidgetProperties->email) && $boxWidgetProperties->email != '') ? '<a href="' . $boxWidgetProperties->email . '" target="_blank"><img src="' . sysConfig::getDirWsCatalog() . 'images/emailSocial.png" /></a>' . sysLanguage::get('WIDGET_SOCIAL_NETWORKS_EMAIL_TEXT') : '';

		$htmlText = htmlBase::newElement('div')
			->addClass('socialNetworks')
			->html(sysLanguage::get('WIDGET_SOCIAL_NETWORKS_TEXT') . $facebook . $twitter . $email);

		$this->setBoxContent($htmlText->draw());
		return $this->draw();
	}
}

?>