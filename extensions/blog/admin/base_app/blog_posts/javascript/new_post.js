$(document).ready(function (){
	$('.gridButtonBar').find('.editCommentButton').click(function (){
		var commentId = $('.gridBodyRow.state-active').attr('data-comment_id');
		js_redirect(js_app_link('appExt=blog&app=blog_posts&appPage=new_comment&cID=' + commentId));
	});

	$('.gridButtonBar').find('.deleteCommentButton').click(function (){
		var commentId = $('.gridBodyRow.state-active').attr('data-comment_id');

		confirmDialog({
			confirmUrl: js_app_link('appExt=blog&app=blog_posts&appPage=default&action=deleteCommentConfirm&cID=' + commentId),
			title: 'Confirm Delete',
			content: 'Are you sure you want to delete this comment?',
			success: function (){
				js_redirect(js_app_link('appExt=blog&app=blog_posts&appPage=default&pID=' + $_GET['pID']) + '#page-comments');
			}
		});
	});

	$('#page-2').tabs();
	$('#tab_container').tabs();
	makeTabsVertical('#tab_container');

    $('.useDatepicker').datepicker({
		dateFormat: 'yy-mm-dd'
	});
		$('.makeFCK').each(function (){
			CKEDITOR.replace(this);
		});
	$('input[name=post_featured_image]').filemanager();
	$('input[name=post_full_featured_image]').filemanager();
});