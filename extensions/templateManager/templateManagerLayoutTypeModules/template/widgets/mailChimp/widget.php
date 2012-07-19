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

class TemplateManagerWidgetMailChimp extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('mailChimp', false, __DIR__);
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		global $appExtension;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlDiv = htmlBase::newElement('div')
			->addClass('mailChimp');

		$htmlEmail = htmlBase::newElement('input')
			->setName('email_address')
			->setValue('Email Address')
			->setLabel(sysLanguage::get('WIDGET_MAILCHIMP_EMAIL_ADDRESS'))
			->setLabelPosition('before')
			->setLabelSeparator('<br/>')
			->setId('emailMailChimp');
		$htmlButton = htmlBase::newElement('button')
			->setType('submit')
			->addClass('mailChimpSignup')
			->setText(sysLanguage::get('WIDGET_MAILCHIMP_SEND'));
		$htmlDiv->append($htmlEmail)
			->append($htmlButton);

		$this->setBoxContent($htmlDiv->draw());
		return $this->draw();
	}

	public function buildJavascript()
	{
		$boxWidgetProperties = $this->getWidgetProperties();

		ob_start();
		?>
	$('#emailMailChimp').click(function(){
	$(this).val('');
	});
	$('.mailChimpSignup').click(function() {

	$.ajax({
	url: 'includes/modules/infoboxes/mailChimp/storeAddress.php',

	data: 'ajax=true&email=' + $('#emailMailChimp').val()+'&api=<?php echo $boxWidgetProperties->api_key; ?>&list=<?php echo $boxWidgetProperties->list_id; ?>',
	success: function(msg) {
	alert(msg);
	$('#emailMailChimp').val('');
	}
	});

	return false;
	});


	<?php
		$javascript = '/* MailChimp Menu --BEGIN-- */' . "\n" .
			ob_get_contents();
		'/* MailChimp --END-- */' . "\n";
		ob_end_clean();

		return $javascript;
	}
}

?>