$(document).ready(function () {
	var $PageGrid = $('.gridContainer');
	$PageGrid.newGrid('option', 'buttons', [
		{
			selector          : '.newButton',
			disableIfNone     : false,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				GridClass.clearSelected();

				GridClass.showWindow({
					buttonEl   : this,
					contentUrl : GridClass.buildActionWindowLink('newCronJob'),
					onAfterShow : function (){
						var self = this;
						$(this).find('select[name=cron_template]').change(function (){
							var vals = $(this).val().split(' ');

							$(self).find('select[name=cron_minute]').val(vals[0]);
							$(self).find('select[name=cron_hour]').val(vals[1]);
							$(self).find('select[name=cron_day]').val(vals[2]);
							$(self).find('select[name=cron_month]').val(vals[3]);
							$(self).find('select[name=cron_weekday]').val(vals[4]);
						});
					},
					buttons    : ['cancel', {
						type: 'save',
						click: GridClass.windowButtonEvent('save', {
							actionName: 'saveCronJob'
						})
					}]
				});
			}
		},
		{
			selector          : '.editButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showWindow({
					buttonEl   : this,
					contentUrl : GridClass.buildActionWindowLink('newCronJob', true),
					onAfterShow : function (){
						var self = this;
						$(this).find('select[name=cron_template]').change(function (){
							var vals = $(this).val().split(' ');

							$(self).find('select[name=cron_minute]').val(vals[0]);
							$(self).find('select[name=cron_hour]').val(vals[1]);
							$(self).find('select[name=cron_day]').val(vals[2]);
							$(self).find('select[name=cron_month]').val(vals[3]);
							$(self).find('select[name=cron_weekday]').val(vals[4]);
						});
					},
					buttons    : ['cancel', {
						type: 'save',
						click: GridClass.windowButtonEvent('save', {
							actionName: 'saveCronJob'
						})
					}]
				});
			}
		},
		{
			selector          : '.deleteButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				GridClass.showDeleteDialog({
					onSuccess       : function (){
						js_redirect(GridClass.buildCurrentAppRedirect('crontab'));
					},
					confirmUrl: GridClass.buildActionLink('deleteCronJob')
				});
			}
		}
	]);
});