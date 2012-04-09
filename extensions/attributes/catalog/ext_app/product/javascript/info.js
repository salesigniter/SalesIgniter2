$(document).ready(function(){
	$('.attrSelect').live('change', function(){
		var self= $(this).closest('.attributesTable');
		var selfParent = self.parent();
		showAjaxLoader(self, 'xlarge');
		var linkParams = [];
		linkParams.push('app=product');
		linkParams.push('appPage=info');
		linkParams.push('action=getAttributes');
		linkParams.push('products_id=' + self.parent().data('product_id'));
		if (self.parent().data('purchase_type')){
			linkParams.push('purchase_type=' + self.parent().data('purchase_type'));
		}
		$.ajax({
			cache: false,
			dataType: 'json',
			data:self.find('*').serialize(),
			type:'post',
			url: js_app_link(linkParams.join('&')),
			success: function (data){
				removeAjaxLoader(self);
				self.parent().html(data.html);
				if(data.hasButton == false){
					selfParent.closest('form').find('.ui-dialog-buttonpane').hide();
				}else{
					selfParent.closest('form').find('.ui-dialog-buttonpane').show();
				}
			}
		});
	});
	$('.attrSelect').change();
});