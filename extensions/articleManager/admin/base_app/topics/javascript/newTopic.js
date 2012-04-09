$(document).ready(function (){
		$('.makeFCK').each(function (){
			CKEDITOR.replace(this);
		});
			$('.useDatepicker').datepicker();
});