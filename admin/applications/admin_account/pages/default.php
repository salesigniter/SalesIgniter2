<?php
$Qaccount = Doctrine_Query::create()
	->select('a.admin_id, a.admin_firstname, a.admin_lastname, a.admin_email_address, a.admin_created, a.admin_modified, a.admin_logdate, a.admin_lognum, g.admin_groups_name')
	->from('Admin a')
	->leftJoin('a.AdminGroups g')
	->where('a.admin_id = ?', Session::get('login_id'));

$Account = $Qaccount->fetchOne();
$Qaccount->free();
unset($Qaccount);

$adminId = $Account['admin_id'];
$adminFirstname = $Account['admin_firstname'];
$adminLastname = $Account['admin_lastname'];
$adminEmailAddress = $Account['admin_email_address'];
$adminGroupName = $Account['AdminGroups']['admin_groups_name'];
$adminDateCreated = $Account['admin_created']->format(sysLanguage::getDateFormat('long'));
$adminLastModified = $Account['admin_modified']->format(sysLanguage::getDateFormat('long'));
$adminLogNumber = $Account['admin_lognum'];
$adminLogDate = $Account['admin_logdate']->format(sysLanguage::getDateFormat('long'));

if (Session::exists('confirm_account') === true){
	Session::remove('confirm_account');
}

$infoTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_FULLNAME')
		),
		array(
			'addCls' => 'main',
			'text'   => $adminFirstname . ' ' . $adminLastname
		)
	)
));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_EMAIL')
		),
		array(
			'addCls' => 'main',
			'text'   => $adminEmailAddress
		)
	)
));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_PASSWORD')
		),
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_PASSWORD_HIDDEN')
		)
	)
));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_GROUP')
		),
		array(
			'addCls' => 'main',
			'text'   => $adminGroupName
		)
	)
));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_CREATED')
		),
		array(
			'addCls' => 'main',
			'text'   => $adminDateCreated
		)
	)
));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_LOGNUM')
		),
		array(
			'addCls' => 'main',
			'text'   => $adminLogNumber
		)
	)
));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_LOGDATE')
		),
		array(
			'addCls' => 'main',
			'text'   => $adminLogDate
		)
	)
));
$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => sysLanguage::get('TEXT_INFO_MODIFIED')
		),
		array(
			'addCls' => 'main',
			'text'   => $adminLastModified
		)
	)
));

$infoBox = htmlBase::newElement('infobox');
switch($action){
	case 'edit_process':
		$infoBox->setHeader('<b>' . sysLanguage::get('TEXT_INFO_HEADING_DEFAULT') . '</b>');
		$infoBox->addContentRow(sysLanguage::get('TEXT_INFO_INTRO_EDIT_PROCESS') . tep_draw_hidden_field('id_info', $adminId));
		break;
	case 'checkAccount':
		$infoBox->setHeader('<b>' . sysLanguage::get('TEXT_INFO_HEADING_CONFIRM_PASSWORD') . '</b>');

		$infoBox->setForm(array(
			'name'   => 'check_password',
			'action' => itw_app_link('action=checkPassword')
		));

		$backButton = htmlBase::newElement('button')->usePreset('back')->setHref(itw_app_link());
		$confirmButton = htmlBase::newElement('button')->setType('submit')->usePreset('save')
			->setText(sysLanguage::get('TEXT_BUTTON_CONFIRM'));

		$infoBox->addButton($backButton)->addButton($confirmButton);

		$infoBox->addContentRow(sysLanguage::get('TEXT_INFO_INTRO_CONFIRM_PASSWORD') . tep_draw_hidden_field('id_info', $adminId));
		$infoBox->addContentRow(tep_draw_password_field('password_confirmation'));
		break;
	default:
		$infoBox->setHeader('<b>' . sysLanguage::get('TEXT_INFO_HEADING_DEFAULT') . '</b>');
		$infoBox->addContentRow(sysLanguage::get('TEXT_INFO_INTRO_DEFAULT'));

		if ($adminEmailAddress == 'admin@localhost'){
			$infoBox->addContentRow(sprintf(sysLanguage::get('TEXT_INFO_INTRO_DEFAULT_FIRST'), $adminFirstname));
		}
		elseif ($adminLastModified == '0000-00-00 00:00:00' || $adminLogDate <= 1) {
			$infoBox->addContentRow(sprintf(sysLanguage::get('TEXT_INFO_INTRO_DEFAULT_FIRST_TIME'), $adminFirstname));
		}
		break;
}
?>
<div>
	<div style="margin-right:5px;margin-left:5px;">
		<div style="margin:5px;">
			<?php echo $infoTable->draw();?>
		</div>
	</div>
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
		<tr>
			<td align="right"><?php
				if ($action == 'edit_process'){
					$backButton = htmlBase::newElement('button')->usePreset('back')->setHref(itw_app_link());

					echo $backButton->draw();

					if (Session::exists('confirm_account') === true){
						$backButton = htmlBase::newElement('button')->setType('submit')->usePreset('save');
						echo $backButton->draw();
					}
				}
				elseif ($action == 'check_account') {
					echo '&nbsp;';
				}
				else {
					$editButton = htmlBase::newElement('button')->usePreset('edit')
						->setHref(itw_app_link('action=checkAccount'));
					echo $editButton->draw();
				}
				?></td>
		</tr>
	</table>
</div>
