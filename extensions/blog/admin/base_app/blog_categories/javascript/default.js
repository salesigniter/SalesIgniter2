$(document).ready(function (){
	$('.gridBody > .gridBodyRow').click(function (){
		if ($(this).hasClass('state-active')) return;

		$('.gridButtonBar').find('button').button('enable');
	});

	$('.gridButtonBar').find('.newButton').click(function (){
		js_redirect(js_app_link('appExt=blog&app=blog_categories&appPage=new_category'));
	});

	$('.gridButtonBar').find('.editButton').click(function (){
		var categoryId = $('.gridBodyRow.state-active').attr('data-category_id');
		js_redirect(js_app_link('appExt=blog&app=blog_categories&appPage=new_category&cID=' + categoryId));
	});

	$('.gridButtonBar').find('.deleteButton').click(function (){
		var categoryId = $('.gridBodyRow.state-active').attr('data-category_id');

		confirmDialog({
			confirmUrl: js_app_link('appExt=blog&app=blog_categories&appPage=default&action=deleteCategoryConfirm&cID=' + categoryId),
			title: 'Confirm Delete',
			content: 'Are you sure you want to delete this category?',
			success: function (){
				js_redirect(js_app_link('appExt=blog&app=blog_categories&appPage=default'));
			}
		});
	});
});