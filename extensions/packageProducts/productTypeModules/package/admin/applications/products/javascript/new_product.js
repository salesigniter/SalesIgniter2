$(document).ready(function () {
	var $PackagedProductsGrid = $('.PackagedProductsGrid');
	$PackagedProductsGrid.newGrid('option', 'buttons', [
		{
			selector          : '.deleteButton',
			disableIfNone     : true,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				GridClass.getSelectedRows().remove();
				//None will be selected, but this method also refreshes the button bar status
				GridClass.clearSelected();
			}
		},
		{
			selector          : '.editProductButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				window.open(GridClass.buildAppRedirect('products', 'new_product', false, [
					'product_id=' + GridClass.getSelectedData('product_id')
				]));
			}
		}
	]);

	$('.buttonAddToPackage').click(function () {
		var SelectedOption = $('#packageProductSelect > option:selected');
		if ($('input[name="package_product[]"][value="' + SelectedOption.val() + '"]').size() > 0){
			alert('This product already exists in the package.');
			return false;
		}
		$.ajax({
			url      : js_app_link('app=products&appPage=new_product&action=getSettingsAddToPackage&product_id=' + SelectedOption.val()),
			cache    : false,
			dataType : 'json',
			success  : function (data) {
				$('<div></div>').dialog({
					title   : 'Configure Package Product Settings',
					width   : 575,
					open    : function () {
						var content = $('<table style="width:100%"><tbody></tbody></table>');
						$.each(data.fields, function () {
							content.find('tbody').append('<tr>' +
								'<td valign="top"><b>' + this.label + ':</b> </td>' +
								'<td valign="top">' + this.field + '</td>' +
								'</tr>');
						});
						$(this).html(content);
					},
					buttons : [
						{
							text  : 'Add To Package',
							click : function () {
								var dialog = this;
								$.ajax({
									url      : js_app_link('app=products&appPage=new_product&action=getPackageRow'),
									cache    : false,
									data     : $(this).find('input, select').serialize(),
									type     : 'post',
									dataType : 'json',
									success  : function (data) {
										$.each(data.newRows, function () {
											$('.PackagedProductsGrid').newGrid('addBodyRow', this);
											$(dialog).dialog('close').remove();
										});
									}
								});
							}
						},
						{
							text  : 'Cancel',
							click : function () {
								$(this).dialog('close').remove();
							}
						}
					]
				});
			}
		});
	});
});