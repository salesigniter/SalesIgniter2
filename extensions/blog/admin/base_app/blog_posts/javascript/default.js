$(document).ready(function (){
	$('.gridButtonBar').find('.newButton').click(function (){
		js_redirect(js_app_link('appExt=blog&app=blog_posts&appPage=new_post'));
	});

	$('.gridButtonBar').find('.editButton').click(function (){
		var postId = $('.gridBodyRow.state-active').attr('data-post_id');
		js_redirect(js_app_link('appExt=blog&app=blog_posts&appPage=new_post&pID=' + postId));
	});

	$('.gridButtonBar').find('.deleteButton').click(function (){
		var postId = $('.gridBodyRow.state-active').attr('data-post_id');

		confirmDialog({
			confirmUrl: js_app_link('appExt=blog&app=blog_posts&appPage=default&action=deletePostConfirm&pID=' + postId),
			title: 'Confirm Delete',
			content: 'Are you sure you want to delete this post?',
			success: function (){
				js_redirect(js_app_link('appExt=blog&app=blog_posts&appPage=default'));
			}
		});
	});
});