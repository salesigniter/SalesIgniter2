
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
				toolbar : 'Simple'
			});
		});
		$('#start_date').datepicker({
			dateFormat: 'yy-mm-dd'
		});
		$('.isRepair').trigger('change');
	});