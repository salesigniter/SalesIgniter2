<?php
$SendOnArray = array(
	array('eventName' => 'ADMIN_MEMBERSHIP_ACTIVATED_EMAIL',   'type' => 'admin', 'text' => '(Admin) Membership activated email', 'description' => 'This email is sent to the customer when their rental membership has been activated'),
	array('eventName' => 'ADMIN_MEMBERSHIP_CANCELED_EMAIL',    'type' => 'admin', 'text' => '(Admin) Membership cancelled email', 'description' => 'This email is sent to the customer when their rental membership has been cancelled'),
	array('eventName' => 'ADMIN_MEMBERSHIP_UPGRADED_EMAIL',    'type' => 'admin', 'text' => '(Admin) Membership upgraded email', 'description' => 'This email is sent to the customer when their rental membership has been upgraded'),
	array('eventName' => 'ONETIME_RENTAL_SENT_EMAIL',          'type' => 'admin', 'text' => '(Admin) Onetime rental sent email', 'description' => 'This email is sent to the customer when their reservation has been sent'),
	array('eventName' => 'ONETIME_RENTAL_RETURNED_EMAIL',      'type' => 'admin', 'text' => '(Admin) Onetime rental returned email', 'description' => 'This email is sent to the customer when their reservation has been returned'),
	array('eventName' => 'ORDER_UPDATE_EMAIL',                 'type' => 'admin', 'text' => '(Admin) Order update email', 'description' => 'This email is sent to the customer when their order has been updatedt'),
	array('eventName' => 'ORDER_PROCESS_EMAIL',                'type' => 'admin', 'text' => '(Admin) Order process email', 'description' => 'This email is sent to the customer when their order is being processed'),
	array('eventName' => 'RENTAL_SENT_EMAIL',                  'type' => 'admin', 'text' => '(Admin) Rental sent email', 'description' => 'This email is sent to the customer when their membership rental has been sent'),
	array('eventName' => 'RENTAL_RETURNED_EMAIL',              'type' => 'admin', 'text' => '(Admin) Rental returned email', 'description' => 'This email is sent to the customer when their membership rental has been returned'),
	array('eventName' => 'RENTAL_ISSUES_EMAIL',                'type' => 'admin', 'text' => '(Admin) Rental issues email', 'description' => 'This email is sent to the administrator when a rental issue has been reported'),
	array('eventName' => 'RENTAL_QUEUE_EMPTY_EMAIL',           'type' => 'admin', 'text' => '(Admin) Rental queue empty email', 'description' => 'This email is sent to the customer when their rental qeue has been emptied'),
	array('eventName' => 'CREATE_ACCOUNT_EMAIL',               'type' => 'site',  'text' => '(Site) Account creation email', 'description' => 'This email is sent to the customer when their account has been created'),
	array('eventName' => 'GIFT_VOUCHER_SEND_EMAIL',            'type' => 'site',  'text' => '(Site) Gift voucher send email', 'description' => 'This email is sent to the customers friend when they are sent a gift voucher'),
	array('eventName' => 'MEMBERSHIP_CANCEL_REQUEST_EMAIL',    'type' => 'site',  'text' => '(Site) Membership cancel request email', 'description' => 'This email is sent to the administrator when a customer has requested to cancel their rental membership'),
	array('eventName' => 'NEW_RENTAL_CUSTOMER_EMAIL',          'type' => 'site',  'text' => '(Site) New rental customer email', 'description' => 'This email is sent to the customer when their rental membership has been created'),
	array('eventName' => 'ORDER_SUCCESS_EMAIL',                'type' => 'site',  'text' => '(Site) Order success email', 'description' => 'This email is sent to the customer when they have successfully submitted an order'),
	array('eventName' => 'PASSWORD_FORGOTTEN_EMAIL',           'type' => 'site',  'text' => '(Site) Password forgotten email', 'description' => 'This email is sent to the customer when they have forgotten their password'),
	array('eventName' => 'RENTAL_ORDER_SUCCESS_EMAIL',         'type' => 'site',  'text' => '(Site) Rental order success email', 'description' => 'This email is sent to the customer when they have successfully submitted a rental membership order'),
	array('eventName' => 'TALK_TO_US_EMAIL',                   'type' => 'site',  'text' => '(Site) Talk to us email', 'description' => 'This email is sent to the administrator when a customer has submitted a talk to us request'),
	array('eventName' => 'MEMBERSHIP_EXPIRED_EMAIL',           'type' => 'site',  'text' => '(Site) Membership expired email', 'description' => 'This email is sent to the customer when their rental membership has expired'),
	array('eventName' => 'MEMBERSHIP_RENEWAL_FAIL',            'type' => 'site',  'text' => '(Site) Membership payment failure email', 'description' => 'This email is sent to the customer when their rental membership payment failed to process'),
	array('eventName' => 'RENTAL_ORDER_SUCCESS_ADMIN_EMAIL',   'type' => 'site',  'text' => '(Site) Admin email for membership', 'description' => 'This email is sent to the administrator when a new rental membership has been created'),
	array('eventName' => 'ORDER_INVENTORY_SUCCESS_EMAIL',      'type' => 'site',  'text' => '(Site) Inventory Owner Email when a order is placed', 'description' => 'This email is sent to the inventory owner when an order is placed for a product they control the inventory for'),
	array('eventName' => 'ORDER_UPDATE_EMAIL_INVENTORY',       'type' => 'site',  'text' => '(Site) Email from Inventory Owner when order is approved or cancelled', 'description' => 'This email is sent to the inventory owner when an order has been approved or cancelled for a product they control the inventory for'),
	array('eventName' => 'ORDER_TIME_SPECIFIC_EMAIL',          'type' => 'site',  'text' => '(Cron) Order Time Specific Email', 'description' => 'This email will be sent to the customer the specified period before or after an order has been placed ( Must have cron job "Order Time Emails" enabled )')
);

$languages = sysLanguage::getLanguages();

$toLangDrop = htmlBase::newElement('selectbox')->setName('toLanguage');
foreach(sysLanguage::getGoogleLanguages() as $code => $lang){
	$toLangDrop->addOption($code, $lang);
}
?>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script>
	google.load("language", "1");
</script>
<div class="relativeParent" style="position:relative;">
	<div class="ui-widget ui-widget-content" style="height:600px;width:275px;overflow:auto;position:absolute;left:0em;top:0em;"><?php
		echo '<div class="ui-widget-content ui-corner-all ui-state-default editLink ui-state-active" style="padding:.5em;margin:.3em;" template_id="new">New Email Template</div>';
		$Qtemplates = Doctrine_Query::create()
			->from('EmailTemplates')
			->orderBy('email_templates_name')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		foreach($Qtemplates as $tInfo){
			echo '<div class="ui-widget-content ui-corner-all ui-state-default editLink" style="padding:.5em;margin:.3em;" template_id="' . $tInfo['email_templates_id'] . '">' . $tInfo['email_templates_name'] . '</div>';
		}
		?></div>
	<form name="emailTemplate" action="<?php echo itw_app_link('action=saveEmailTemplate'); ?>" method="post" enctype="multipart/form-data">
		<div class="ui-widget ui-widget-content ui-corner-all" style="position:relative;margin-left:285px;margin-bottom:1em;text-align:right;"><?php
			echo '<span style="float:left;margin-left:.5em;line-height:3em;">' . htmlBase::newElement('icon')
				->setType('circleTriangleWest')->draw() . '<b><u>Click template to the left to edit</u></b></span>';
			echo htmlBase::newElement('button')
				->usePreset('save')
				->setType('submit')
				->addClass('saveButton')
				->css('margin', '.3em')
				->draw();
			?></div>
		<div id="templateConfigure" class="ui-widget ui-widget-content ui-corner-all" style="position:relative;margin-left:285px;margin-top:.5em;">
			<div style="margin:.5em;">
				<table cellpadding="3" cellspacing="0" border="0" width="100%">
					<tr>
						<td class="main" width="150"><b>Template Name:</b></td>
						<td class="main"><input type="text" id="emailTemplate" name="email_template" style="width:80%">
						</td>
					</tr>
					<tr>
						<td class="main"><b>Event Name:</b></td>
						<td class="main"><input type="text" name="email_event" id="emailEvent" style="width:80%"></td>
					</tr>
					<tr>
						<td class="main"><b>Attached File:</b></td>
						<td class="main">
							<input type="text" id="emailAtt" class="emailAtt" name="email_att" style="width:80%"><br><input type="file" id="emailFile" class="emailFile" name="email_file" value="" style="width:80%">
						</td>
					</tr>
				</table>
			</div>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" id="varsTable">
				<tr>
					<td valign="top" width="33%"><?php
						echo '<div class="ui-widget-header" style="padding:.3em;">' . sysLanguage::get('HEADING_GLOBAL_VARS') . '</div>' .
							'<div class="main globalVars" style="margin:.5em;">$store_name<br>$store_owner<br>$store_owner_email<br>$today_short<br>$today_long<br>$store_url<br></div>';
						?></td>
					<td valign="top" width="33%" style="padding: 0em 1em;"><?php
						echo '<div class="ui-widget-header" style="padding:.3em;"><span class="ui-icon ui-icon-plusthick addStandardVar" style="float:right;"></span>' . sysLanguage::get('HEADING_AVAIL_VARS') . '</div>' .
							'<div class="main standardVars" style="margin:.5em;"><span class="noVars">No Variables Available.</span></div>';
						?></td>
					<td valign="top" width="33%"><?php
						echo '<div class="ui-widget-header" style="padding:.3em;"><span class="ui-icon ui-icon-plusthick addConditionVar" style="float:right;"></span>' . sysLanguage::get('HEADING_COND_VARS') . '</div>' .
							'<div class="main conditionVars" style="margin:.5em;"><span class="noVars">No Variables Available.</span></div>';
						?></td>
				</tr>
			</table>
			<!--<div class="main" style="text-align:right;"><?php
			echo 'From: English&nbsp;&nbsp;&nbsp;' .
				'To: ' . $toLangDrop->draw() . '&nbsp;&nbsp;&nbsp;' .
				htmlBase::newElement('button')->setId('googleTranslate')->setText('Translate Using Google')
					->draw() . '<br>' .
				'<div id="googleBrand"></div>';
			?></div>-->
			<div class="ui-tabs-container" style="margin:.5em;">
				<ul>
					<?php foreach($languages as $lInfo){ ?>
					<li><a href="#tab_<?php echo $lInfo['id']; ?>"><span><?php echo $lInfo['showName'](); ?></span></a>
					</li>
					<?php } ?>
				</ul>
				<?php foreach($languages as $lInfo){ ?>
				<div id="tab_<?php echo $lInfo['id']; ?>" lang_name="<?php echo $lInfo['name']; ?>">
					<b>Subject:</b>
					<input type="text" class="emailSubject" name="email_subject[<?php echo $lInfo['id']; ?>]" value="" style="width:80%"><br /><br />
					<textarea rows="20" cols="100" style="width:100%" name="email_text[<?php echo $lInfo['id']; ?>]" class="makeFCK"></textarea><br /><br />
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="ui-widget ui-widget-content ui-corner-all" style="margin-left:285px;margin-top:1em;text-align:right;"><?php
			echo htmlBase::newElement('button')
				->usePreset('save')
				->setType('submit')
				->addClass('saveButton')
				->css('margin', '.3em')
				->draw();
			?></div>
	</form>
</div>