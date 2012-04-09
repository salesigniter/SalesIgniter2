$(document).ready(function (){
	$('.gridBody > .gridBodyRow').click(function (){
		if ($(this).hasClass('state-active')) return;

		$('.gridButtonBar').find('button').button('enable');
	});

	if ($.inArray($_GET, 'sID')){
		$('.gridBody > .gridBodyRow[data-status_id="' + $_GET['sID'] + '"]').click();
	}

	$('.newButton, .editButton').click(function (){
		if ($(this).hasClass('newButton')){
			$('.gridBodyRow.state-active').removeClass('state-active');
			$('.gridButtonBar').find('.editButton, .deleteButton').button('disable');
		}

		var getVars = [];
		getVars.push('appExt=' + thisAppExt);
		getVars.push('app=' + thisApp);
		getVars.push('appPage=' + thisAppPage);
		getVars.push('action=getActionWindow');
		getVars.push('window=new');
		if ($('.gridBodyRow.state-active').size() > 0){
			getVars.push('sID=' + $('.gridBodyRow.state-active').attr('data-status_id'));
		}

		gridWindow({
			buttonEl: this,
			gridEl: $('.gridContainer'),
			contentUrl: js_app_link(getVars.join('&')),
			onShow: function (){
				var self = this;

				$(self).find('.cancelButton').click(function (){
					$(self).effect('fade', {
						mode: 'hide'
					}, function (){
						$('.gridContainer').effect('fade', {
							mode: 'show'
						}, function (){
							$(self).remove();
						});
					});
				});

				$(self).find('.saveButton').click(function (){
					var getVars = [];
					getVars.push('appExt=' + thisAppExt);
					getVars.push('app=' + thisApp);
					getVars.push('appPage=' + thisAppPage);
					getVars.push('action=save');
					if ($('.gridBodyRow.state-active').size() > 0){
						getVars.push('sID=' + $('.gridBodyRow.state-active').attr('data-status_id'));
					}

					$.ajax({
						cache: false,
						url: js_app_link(getVars.join('&')),
						dataType: 'json',
						data: $(self).find('*').serialize(),
						type: 'post',
						success: function (data){
							if (data.success){
								js_redirect(js_app_link('appExt=' + thisAppExt + '&app=' + thisApp + '&appPage=' + thisAppPage + '&sID=' + data.sID));
							}else{
								alert(data.message);
							}
						}
					});
				});
			}
		});
	});

	$('.deleteButton').click(function (){
		var statusId = $('.gridBodyRow.state-active').attr('data-status_id');
		confirmDialog({
			confirmUrl: js_app_link('appExt=' + thisAppExt + '&app=' + thisApp + '&appPage=' + thisAppPage + '&action=delete&sID=' + statusId),
			title: jsLanguage.get('WINDOW_HEADING_DELETE_STATUS'),
			content: jsLanguage.get('WINDOW_DELETE_STATUS_INTRO'),
			errorMessage: jsLanguage.get('WINDOW_DELETE_STATUS_ERROR'),
			success: function (){
				js_redirect(js_app_link('appExt=' + thisAppExt + '&app=' + thisApp + '&appPage=' + thisAppPage));
			}
		});
	});
});