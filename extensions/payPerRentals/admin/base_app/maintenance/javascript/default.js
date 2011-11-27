
$(document).ready(function (){
	$('#maintenance_selectbox').change(function(){
		if($(this).val() != '-1'){
			window.location = js_app_link('appExt=payPerRentals&app=maintenance&appPage=default&type='+$(this).val());
		}else{
			window.location = js_app_link('appExt=payPerRentals&app=maintenance&appPage=repairs');
		}
	});

	$('.gridBody > .gridBodyRow').click(function (){
		if ($(this).hasClass('state-active')) return;

		$('.gridButtonBar').find('button').button('enable');
	});

	$('.editButton').click(function (){

		var getVars = [];
		getVars.push('appExt=payPerRentals');
		getVars.push('app=maintenance');
		getVars.push('appPage=default');
		getVars.push('action=getActionWindow');
		getVars.push('window=new');
		if ($('.gridBodyRow.state-active').size() > 0){
			getVars.push('mID=' + $('.gridBodyRow.state-active').attr('data-barcode_id'));
			getVars.push('type=' + $('.gridBodyRow.state-active').attr('data-type'));
		}

		gridWindow({
			buttonEl: this,
			gridEl: $('.gridContainer'),
			contentUrl: js_app_link(getVars.join('&')),
			onShow: function (){
				var self = this;



				$(self).find('.cancelButton').click(function (){
					var instance = CKEDITOR.instances['commentID'];
					if(instance){
						instance.setData('');
						instance.destroy();
					}
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
					getVars.push('appExt=payPerRentals');
					getVars.push('app=maintenance');
					getVars.push('appPage=default');
					getVars.push('action=save');
					if ($('.gridBodyRow.state-active').size() > 0){
						getVars.push('mID=' + $('.gridBodyRow.state-active').attr('data-barcode_id'));
						getVars.push('type=' + $('.gridBodyRow.state-active').attr('data-type'));
					}

					$.ajax({
						cache: false,
						url: js_app_link(getVars.join('&')),
						dataType: 'json',
						data: $(self).find('*').serialize(),
						type: 'post',
						success: function (data){
							$(self).effect('fade', {
								mode: 'hide'
							}, function (){
								$('.gridContainer').effect('fade', {
									mode: 'show'
								}, function (){
									$(self).remove();
								});
							});
							$('.gridBody > .gridBodyRow').each(function(){
								if($(this).attr('data-barcode_id') == data.removed){
									$(this).remove();
								}
							});
						}
					});
				});

				//instance.hide();
				$('#commentID').hide();
				$('.isB').click(function(){
					CKEDITOR.replace('commentID', {
						toolbar :
							[
								['Cut','Copy','Paste','PasteText','PasteFromWord','-'],
								['Undo','Redo','-'],
								['Image','Table','SpecialChar','PageBreak'],
								'/',
								['Styles','Format'],
								['Bold','Italic','Strike'],
								['NumberedList','BulletedList','-'],
								['Link','Unlink','Anchor']

							],
						filebrowserBrowseUrl: DIR_WS_ADMIN + 'rentalwysiwyg/editor/filemanager/browser/default/browser.php'
					});
					//disable save button
					$('.saveButton').attr('disabled', 'disabled');
					//ajax check of mID current_type..if is before send return a dropdown with available barcodes..if select from dropdown enable save button
					var $myForm = $('.newWindowContainer');
					showAjaxLoader($myForm, 'large');
					$.ajax({
						cache: false,
						dataType: 'json',
						data: 'mID='+$('#mid').attr('mid'),
						type:'get',
						url: js_app_link('appExt=payPerRentals&app=maintenance&appPage=default&action=checkBeforeSend'),
						success: function (data){
							removeAjaxLoader($myForm);
							if(data.isBefore){
								$myForm.find('.ui-dialog-content').append(data.dropDown);
								$('#availBarcodes').change(function(){
									if($(this).val() != '0'){
										$('.saveButton').removeAttr('disabled');
									}else{
										$('.saveButton').attr('disabled', 'disabled');
									}
								});
							}else{
								$('.saveButton').removeAttr('disabled');
							}
						}
					});
				});
				$('.isG').click(function(){

					var instance = CKEDITOR.instances['commentID'];
					if(instance){
						instance.setData('');
						instance.destroy();
					}
					$('#availBarcodes').remove();
					$('#commentID').hide();
				});
			}
		});
	});


});