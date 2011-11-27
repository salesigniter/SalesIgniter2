
	$(document).ready(function (){
		$('.isRepair').change(function(){
			if($(this).is(':checked')){
				$('.not_repair').hide();
				$('.not_repair').prev().hide();
			}else{
				$('.not_repair').show();
				$('.not_repair').prev().show();
			}
		});
		$('.makeFCK').each(function (){
			CKEDITOR.replace(this, {
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
		});
		$('#start_date').datepicker({
			dateFormat: 'yy-mm-dd'
		});
		$('.isRepair').trigger('change');
	});