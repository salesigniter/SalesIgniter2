<table border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td><table border="0" cellspacing="0" cellpadding="2">
			<tr<?php echo ((sysConfig::get('EXTENSION_ORDER_CREATOR_HIDE_ORDER_STATUS') == 'False') ? '' : ' style="display:none"');?>>
				<td class="main"><b><?php echo sysLanguage::get('ENTRY_STATUS');?></b> <?php echo $statusDrop->draw();?></td>
			</tr>
			<tr>
				<td class="main"><b><?php echo sysLanguage::get('ENTRY_NOTIFY_CUSTOMER');?></b> <?php echo tep_draw_checkbox_field('notify', '', true);?></td>
				<td class="main"><b><?php echo sysLanguage::get('ENTRY_NOTIFY_COMMENTS');?></b> <?php echo tep_draw_checkbox_field('notify_comments', '', true);?></td>
			</tr>
		</table></td>
	</tr>
</table></div>
