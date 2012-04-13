(function ($) {

	$.widget("ui.newGrid", {
		GridElement : null,
		GridButtonElement : null,
		GridPagerElement : null,
		options : {
			onRowClick : null
		},
		_create : function () {
			this.GridElement = $(this.element).find('.grid');
			this.GridButtonElement = $(this.element).find('.gridButtonBar');
			this.GridPagerElement = $(this.element).find('gridPagerBar');
			this.useSortable = $(this.element).hasClass('useSortables');

			var self = this;

			$('.gridInfoRow').hide();
		},
		_init : function () {
			var self = this;

			$(this.GridElement).find('.gridBodyRow').live('mouseover mouseout click refresh', function (e, isRefresh) {
				switch(e.type){
					case 'mouseover':
						if ($(this).hasClass('noHover')){
							return false;
						}

						if (!$(this).hasClass('state-active')){
							$(this).addClass('state-hover');
							this.style.cursor = 'pointer';
						}
						break;
					case 'mouseout':
						if ($(this).hasClass('noHover')){
							return false;
						}

						if (!$(this).hasClass('state-active')){
							$(this).removeClass('state-hover');
							this.style.cursor = 'default';
						}
						break;
					case 'click':
						if ($(this).hasClass('noSelect')){
							return false;
						}

						$(self.GridButtonElement).find('button').button('enable');

						if (e.ctrlKey && e.type == 'click'){
							if ($(this).hasClass('state-active')){
								$(this).removeClass('state-active');
							}
							else {
								$(this).removeClass('state-hover').addClass('state-active');
							}
						}
						else {
							if ($(this).hasClass('state-active') && $(this).parent().find('.state-active').size() == 1){
								return;
							}

							$(this).parent().find('.state-active').removeClass('state-active');
							$(this).removeClass('state-hover').addClass('state-active');
						}

						if (self.options.onRowClick){
							self.options.onRowClick.apply(this, [e]);
						}
						showInfoBox($(this).attr('infobox_id'));
						break;
					case 'refresh':
						$(this).trigger('click', [true]);
						break;
				}
			});

			$(this.GridElement).find('.ui-icon-info').live('click', function () {
				if ($(this).hasClass('active')){
					$('.gridInfoRow').hide();
					$(this).removeClass('active');
				}
				else {
					$('.gridInfoRow').hide();

					$(this).addClass('active');
					$(this).parentsUntil('tbody').next().show();
				}
			});

			$(this.GridElement).find('tr.gridSearchHeaderRow').each(function () {
				$(this).find('.clearFilterIcon').click(function () {
					$(this).parent().find('input').val('');
					$(this).parent().find('select').val('');
					$('.applyFilterButton').click();
				});

				$(this).find('.applyFilterButton').click(function () {
					var getVars = [];
					var ignoreParams = ['action'];
					$(this).parent().parent().find('input, select').each(function () {
						if ($(this).val() != ''){
							getVars.push($(this).attr('name') + '=' + $(this).val());
						}
						ignoreParams.push($(this).attr('name'));
					});
					js_redirect(js_app_link(js_get_all_get_params(ignoreParams) + getVars.join('&')));
				});

				$(this).find('.resetFilterButton').click(function () {
					var ignoreParams = ['action'];
					$(this).parent().parent().find('input, select').each(function () {
						ignoreParams.push($(this).attr('name'));
					});
					js_redirect(js_app_link(js_get_all_get_params(ignoreParams)));
				});
			});

			$(this.GridElement).find('th.ui-grid-sortable-header').each(function () {
				var sortKey = $(this).parent().parent().parent().attr('data-sort_key');
				var sortDirKey = $(this).parent().parent().parent().attr('data-sort_dir_key');

				var sortDir = 'asc';
				if ($(this).attr('data-current_sort_direction') == 'desc'){
					sortDir = 'asc';
				} else if ($(this).attr('data-current_sort_direction') == 'asc'){
					sortDir = 'desc';
				}

				var getVars = [];
				getVars.push(sortKey + '=' + $(this).attr('data-sort_by'));
				getVars.push(sortDirKey + '=' + (sortDir == 'none' ? 'desc' : sortDir));

				var sortArrow = $('<a></a>')
					.attr('href', js_app_link(js_get_all_get_params(['action', sortKey, sortDirKey]) + getVars.join('&')))
					.addClass('ui-icon')
					.css({
						'float' : 'right'
					});
				sortArrow.addClass('ui-icon-sort-' + $(this).attr('data-current_sort_direction'));

				$(this).append(sortArrow);
			});

			if (this.useSortable === true){
				$(this.GridElement).find('thead > tr').prepend('<th class="gridHeaderRowColumn" style="width:2em;">*</th>');
				$(this.GridElement).find('tbody > tr').each(function (k, v){
					$(this).attr('id', 'gridsort_' + k);
					$(this).prepend('<td class="gridBodyRowColumn gridSortNumber" style="width:2em;">' + (k+1) + '</td>');
				});

				$(this.GridElement).find('tbody').bind('rowAdded', function (){
					var $LastRow = $(this).find('tr').last();
					$LastRow.attr('id', 'gridsort_' + $LastRow.index()).prepend('<td class="gridBodyRowColumn gridSortNumber" style="width:2em;">' + ($LastRow.index()+1) + '</td>');
				});
				
				$(this.GridElement).sortable({
					items : 'tr',
					helper : function (e, item) {
						var $originals = item.children();
						var $helper = item.clone();
						$helper.children().each(function (index) {
							// Set helper cell sizes to match the original sizes
							$(this).width($originals.eq(index).width())
						});
						return $helper;
					},
					update: function (e, ui){
						$(ui.item).parentsUntil('table').last().find('.gridSortNumber').each(function (k, v){
							$(this).html(k+1);
						});
					},
					forcePlaceholderSize : true,
					forceHelperSize : true,
					containment : $(this.GridElement).find('tbody'),
					axis : 'y',
					tolerance : 'pointer'
				});

				$(this.element).parents('form').last().submit(function (){
					var value = $(self.GridElement).sortable('serialize');
					$(this).append('<input type="hidden" name="gridSortable" value="' + value + '">');
				});
			}
		},
		addBodyRow : function (data) {
			var $Row = $('<tr class="gridBodyRow"></tr>');
			if (data.rowAttr){
				$.each(data.rowAttr, function (k, v) {
					$Row.attr(k, v);
				});
			}
			if ($(this.element).hasClass('noRowSelect')){
				$Row.addClass('noSelect');
			}
			if ($(this.element).hasClass('noRowHover')){
				$Row.addClass('noHover');
			}
			$.each(data.columns, function () {
				$Row.append('<td class="gridBodyRowColumn">' + this.text + '</td>');
			});

			$(this.GridElement).find('tbody').append($Row);
			$(this.GridElement).find('tbody').trigger('rowAdded');
			return $Row;
		},
		getSelectedData : function (dataName) {
			var data = [];
			$(this.GridElement).find('.gridBodyRow.state-active').each(function () {
				data.push($(this).data(dataName));
			});
			return data.join(',');
		}
	});
})(jQuery);