<?php
if (!class_exists('phpQuery')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/html/dom/phpQuery.php');
}

$BandwidthGraph = $cPanel->api1_query(
	sysConfig::get('MY_SERVER_CPANEL_USERNAME'),
	'Bandwidth',
	'displaymainbwheader'
);
$BandwidthGraphJSON = json_decode($BandwidthGraph, true);

$BandwidthGraphHtml = phpQuery::newDocumentHTML($BandwidthGraphJSON['data']['result']);
foreach($BandwidthGraphHtml->find('img[file]') as $Image){
	$Image = pq($Image);
	$FileName = $Image->attr('file');

	if (file_exists(sysConfig::get('DIR_FS_DOCUMENT_ROOT') . '/../tmp/' . $FileName)){
		copy(
			sysConfig::get('DIR_FS_DOCUMENT_ROOT') . '/../tmp/' . $FileName,
			sysConfig::getDirFsCatalog() . 'tmp/' . $FileName
		);
		$Image->attr('src', sysConfig::getDirWsCatalog() . 'tmp/' . $FileName);
	}
	elseif (file_exists(sysConfig::get('DIR_FS_DOCUMENT_ROOT') . '/../' . $Image->attr('src'))){
		copy(
			sysConfig::get('DIR_FS_DOCUMENT_ROOT') . '/..' . $Image->attr('src'),
			sysConfig::getDirFsCatalog() . 'tmp/' . $FileName
		);
		$Image->attr('src', sysConfig::getDirWsCatalog() . 'tmp/' . $FileName);
	}
	else{
		$Image->replaceWith('<span>' . sysConfig::get('DIR_FS_DOCUMENT_ROOT') . '/../tmp/' . $FileName . '</span>');
	}

	if (preg_match('/7days/i', $FileName)){
		$BandwidthGraphHtml->html($Image);
		break;
	}
}

$Result = $cPanel->api2_query(
	sysConfig::get('MY_SERVER_CPANEL_USERNAME'),
	'StatsBar',
	'stat',
	array('display' => 'bandwidthusage|diskusage|hostingpackage')
);
$ResultJSON = json_decode($Result, true);
?>
<div style="margin:5px;">
	<div style="margin:5px;">
		<table>
			<tr>
				<td>Hosting Account: </td>
				<td><?php echo ucwords($ResultJSON['cpanelresult']['data'][2]['value']);?></td>
			</tr>
			<tr>
				<td>Disk Usage: </td>
				<td><?php echo $ResultJSON['cpanelresult']['data'][1]['count'] . ' ' . $ResultJSON['cpanelresult']['data'][1]['units'] . ' / ' . $ResultJSON['cpanelresult']['data'][1]['max'];?></td>
			</tr>
			<tr>
				<td>Bandwidth Usage: </td>
				<td><?php echo $BandwidthGraphHtml->show();?></td>
			</tr>
		</table>
	</div>
	<hr>
	<div style="margin:5px;">
		<div class="ui-widget" style="float:left;margin:10px;">
			<a href="<?php echo itw_app_link(null, 'cpanel', 'crontab');?>">
				<div class="ui-widget-content ui-corner-all" style="padding:.5em;text-align:center;">
					<img src="applications/cpanel/images/crontab.png"><br><br>
					<span style="font-size:.9em;">Manage Cron Jobs</span>
				</div>
			</a>
		</div>
		<div class="ui-helper-clearfix"></div>
	</div>
</div>
