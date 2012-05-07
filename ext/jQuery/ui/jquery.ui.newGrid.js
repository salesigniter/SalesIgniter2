(function ($) {

	$.widget("ui.newGrid", {
		GridElement             : null,
		GridButtonElement       : null,
		GridPagerElement        : null,
		dataKey                 : null,
		allowMultiple           : false,
		options                 : {
			buttons       : [],
			onRowClick    : null,
			onRowDblClick : null
		},
		buttonPresets           : {
			new    : {
				selector          : '.newButton',
				disableIfNone     : false,
				disableIfMultiple : false,
				click             : function (e, GridClass) {
					GridClass.clearSelected();
					GridClass.showWindow({
						buttonEl   : this,
						contentUrl : GridClass.buildActionWindowLink('new'),
						buttons    : ['cancel', 'save']
					});
				}
			},
			edit   : {
				selector          : '.editButton',
				disableIfNone     : true,
				disableIfMultiple : true,
				click             : function (e, GridClass) {
					GridClass.showWindow({
						buttonEl   : this,
						contentUrl : GridClass.buildActionWindowLink('new', true),
						buttons    : ['cancel', 'save']
					});
				}
			},
			delete : {
				selector          : '.deleteButton',
				disableIfNone     : true,
				disableIfMultiple : false,
				click             : function (e, GridClass) {
					GridClass.showDeleteDialog();
				}
			},
			export : {
				selector          : '.csvExportButton',
				disableIfNone     : true,
				disableIfMultiple : false,
				click             : function (e, GridClass) {
					GridClass.showExportDialog(this);
				}
			}
		},
		_create                 : function () {
			this.GridElement = $(this.element).find('.grid');
			this.GridButtonElement = $(this.element).find('.gridButtonBar');
			this.GridPagerElement = $(this.element).find('.gridPagerBar');
			this.useSortable = $(this.element).hasClass('useSortables');
			this.dataKey = $(this.element).data('main_data_key');
			this.allowMultiple = ($(this.element).data('allow_multiple') == true ? true : false);

			var self = this;

			$('.gridInfoRow').hide();

			$(this.GridElement).on('mouseover mouseout click refresh dblclick selectAll', '.gridBodyRow', function (e, isRefresh) {
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
					case 'selectAll':
					case 'click':
						if ($(this).hasClass('noSelect')){
							return false;
						}

						self.enableButton('button');

						if (self.allowMultiple === true && ( (e.ctrlKey && e.type == 'click') || (e.type == 'selectAll') )){
							if ($(this).hasClass('state-active')){
								if (e.type != 'selectAll'){
									$(this).removeClass('state-active');
								}
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

						$.each(self.options.buttons, function () {
							if (this.disableIfMultiple === true && self.getSelectedRows().size() > 1){
								self.disableButton(this.selector);
							} else if (this.disableIfNone === true && self.getSelectedRows().size() == 0){
								self.disableButton(this.selector);
							}
						});

						if (self.options.onRowClick){
							self.options.onRowClick.apply(this, [e, self]);
						}
						break;
					case 'dblclick':
						if (self.options.onRowDblClick){
							self.options.onRowDblClick.apply(this, [e, self]);
						}
						break;
					case 'refresh':
						$(this).trigger('click', [true]);
						break;
				}
			});

			$(document).on('keydown', function (e) {
				switch(e.which){
					case 65:
						if (self.getSelectedRows().size() > 0){
							$(self.GridElement).find('.gridBodyRow').trigger('selectAll');
							return false;
						}
						break;
				}
			});

			$(this.GridElement).on('click', '.ui-icon-info', function () {
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
				$(this.GridElement).find('tbody > tr').each(function (k, v) {
					$(this).attr('id', 'gridsort_' + k);
					$(this).prepend('<td class="gridBodyRowColumn gridSortNumber" style="width:2em;">' + (k + 1) + '</td>');
				});

				$(this.GridElement).find('tbody').bind('rowAdded', function () {
					var $LastRow = $(this).find('tr').last();
					$LastRow.attr('id', 'gridsort_' + $LastRow.index()).prepend('<td class="gridBodyRowColumn gridSortNumber" style="width:2em;">' + ($LastRow.index() + 1) + '</td>');
				});

				$(this.GridElement).sortable({
					items                : 'tr',
					helper               : function (e, item) {
						var $originals = item.children();
						var $helper = item.clone();
						$helper.children().each(function (index) {
							// Set helper cell sizes to match the original sizes
							$(this).width($originals.eq(index).width())
						});
						return $helper;
					},
					update               : function (e, ui) {
						$(ui.item).parentsUntil('table').last().find('.gridSortNumber').each(function (k, v) {
							$(this).html(k + 1);
						});
					},
					forcePlaceholderSize : true,
					forceHelperSize      : true,
					containment          : $(this.GridElement).find('tbody'),
					axis                 : 'y',
					tolerance            : 'pointer'
				});

				$(this.element).parents('form').last().submit(function () {
					var value = $(self.GridElement).sortable('serialize');
					$(this).append('<input type="hidden" name="gridSortable" value="' + value + '">');
				});
			}
		},
		_init                   : function () {
			var self = this;

			$(this.GridButtonElement).find('button').click(function (e) {
				var buttonEl = this;
				$.each(self.options.buttons, function () {
					if ($(buttonEl).is(this.selector) && this.click){
						this.click.apply(buttonEl, [e, self]);
					}
				});
			});
		},
		_setOption              : function (key, value) {
			var self = this;
			switch(key){
				case "buttons":
					var actualSettings = [];
					$.each(value, function () {
						if ($.isPlainObject(this)){
							actualSettings.push(this);
						}
						else {
							if (self.buttonPresets[this]){
								actualSettings.push(self.buttonPresets[this]);
							}
						}
					});
					value = actualSettings;
					break;
			}

			// In jQuery UI 1.8, you have to manually invoke the _setOption method from the base widget
			$.Widget.prototype._setOption.apply(this, arguments);
			// In jQuery UI 1.9 and above, you use the _super method instead
			//this._super( "_setOption", key, value );
		},
		addBodyRow              : function (data) {
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
		enableButton            : function (selector) {
			$(this.GridButtonElement).find(selector).button('enable');
		},
		disableButton           : function (selector) {
			$(this.GridButtonElement).find(selector).button('disable');
		},
		getDataKey              : function () {
			return this.dataKey;
		},
		getSelectedData         : function (dataKey) {
			dataKey = dataKey || this.getDataKey();
			var data = [];
			this.getSelectedRows().each(function () {
				data.push($(this).data(dataKey));
			});
			return data.join(',');
		},
		getSelectedRows         : function () {
			return $(this.GridElement).find('tbody').find('.gridBodyRow.state-active');
		},
		hasSelected             : function () {
			return (this.getSelectedRows().size() > 0);
		},
		clearSelected           : function () {
			var self = this;
			this.getSelectedRows().removeClass('state-active');
			$.each(self.options.buttons, function () {
				if (this.disableIfNone === true){
					$(self.GridButtonElement).find(this.selector).button('disable');
				}
			});
		},
		postWindowData          : function (o) {
			var self = this;
			showAjaxLoader(o.buttonEl, 'small');

			var url = [this.buildActionLink(o.actionName)];
			if (o.addKeyToUrl === true){
				url.push(o.dataKey + '=' + this.getSelectedData(o.dataKey));
			}

			$.ajax({
				cache    : false,
				url      : url.join('&'),
				dataType : o.dataType || 'json',
				data     : o.data || o.windowEl.find('*').serialize(),
				type     : o.type || 'post',
				success  : o.onSuccess || function (data) {
					removeAjaxLoader(o.buttonEl);
					if (data.success){
						js_redirect(self.buildCurrentAppRedirect());
					}
					else {
						var ErrorMessage = 'An Unknown Error Occured!';
						if (data.error){
							ErrorMessage = data.error.message;
						}
						self.newWindow.find('#messageStack').html(ErrorMessage);
					}
				}
			});
		},
		baseLinkParams          : function (pageName) {
			pageName = pageName || thisAppPage
			var getVars = [];
			if ($_GET['appExt']){
				getVars.push('appExt=' + thisAppExt);
			}
			getVars.push('app=' + thisApp);
			getVars.push('appPage=' + pageName);
			return getVars;
		},
		buildActionWindowLink   : function (windowName, addDataKey, addGetVars) {
			addGetVars = addGetVars || [];
			var self = this;

			var getVars = self.baseLinkParams();
			getVars.push('action=getActionWindow');
			getVars.push('window=' + windowName);
			if (addDataKey === true){
				getVars.push(self.getDataKey() + '=' + self.getSelectedData());
			}

			if ($.isArray(addGetVars)){
				$.each(addGetVars, function () {
					getVars.push(this);
				});
			}
			return js_app_link(getVars.join('&'));
		},
		buildActionLink         : function (actionName, addGetVars) {
			addGetVars = addGetVars || [];
			var getVars = this.baseLinkParams();
			getVars.push('action=' + actionName);
			if ($.isArray(addGetVars)){
				$.each(addGetVars, function () {
					getVars.push(this);
				});
			}
			return js_app_link(getVars.join('&'));
		},
		buildAppRedirect        : function (appName, pageName, extName, addGetVars) {
			addGetVars = addGetVars || [];
			var getVars = [];
			if (extName){
				getVars.push('appExt=' + extName);
			}
			getVars.push('app=' + appName);
			getVars.push('appPage=' + pageName);

			if ($.isArray(addGetVars)){
				$.each(addGetVars, function () {
					getVars.push(this);
				});
			}
			return js_app_link(getVars.join('&'));
		},
		buildCurrentAppRedirect : function (pageName, addGetVars) {
			pageName = pageName || thisAppPage;
			addGetVars = addGetVars || [];
			var getVars = [];
			if ($_GET['appExt']){
				getVars.push('appExt=' + thisAppExt);
			}
			getVars.push('app=' + thisApp);
			getVars.push('appPage=' + pageName);

			if ($.isArray(addGetVars)){
				$.each(addGetVars, function () {
					getVars.push(this);
				});
			}
			return js_app_link(getVars.join('&'));
		},
		windowButtonEvent       : function (type, o) {
			var self = this;
			o = o || {};
			if (type == 'cancel'){
				return function () {
					var mainContainer = $(this).parentsUntil('.newWindowContainer').last().parent();
					mainContainer.effect('fade', {
						mode : 'hide'
					}, function () {
						$(self.element).effect('fade', {
							mode : 'show'
						}, function () {
							mainContainer.remove();
						});
					});
				};
			} else if (type == 'save'){
				return function () {
					o.actionName = o.actionName || 'save';
					var options = $.extend({
						dataKey     : self.getDataKey(),
						actionName  : o.actionName,
						addKeyToUrl : (self.getSelectedRows().size() > 0),
						windowEl    : $(this).parentsUntil('.newWindowContainer').last().parent(),
						buttonEl    : $(this)
					}, o);
					self.postWindowData(options);
				};
			}
			return function () {
				alert('Window Button Event Type Not Defined: ' + type);
			};
		},
		showWindow              : function (o) {
			var self = this;
			var gridEl = $(this.element);
			var buttonEl = o.buttonEl;
			showAjaxLoader($(buttonEl), 'small');

			$.ajax({
				cache    : false,
				url      : o.contentUrl,
				dataType : 'html',
				success  : function (htmlData) {
					gridEl.effect('fade', {
						mode : 'hide'
					}, function () {
						self.newWindow = $('<div class="newWindowContainer"></div>')
							.append('<div id="messageStack"></div>')
							.append(htmlData);

						if (o.onBeforeShow){
							o.onBeforeShow.apply(self.newWindow, [
								{
									triggerEl : self
								}
							]);
						}

						self.newWindow.insertAfter(gridEl).effect('fade', {
							mode : 'show'
						}, function () {
							self.newWindow.find('button').button();

							$.each(o.buttons, function () {
								if ($.isPlainObject(this)){
									if (this.type == 'cancel'){
										self.newWindow.find('.cancelButton').click(this.click);
									} else if (this.type == 'save'){
										self.newWindow.find('.saveButton').click(this.click);
									}
								}
								else {
									if (this == 'cancel'){
										self.newWindow.find('.cancelButton').click(self.windowButtonEvent('cancel'));
									} else if (this == 'save'){
										self.newWindow.find('.saveButton').click(self.windowButtonEvent('save'));
									}
								}
							});

							removeAjaxLoader($(buttonEl));
						});
					});
				}
			});
		},
		showConfirmDialog       : function (o) {
			var self = this;
			var o = $.extend({
				title     : 'Please Confirm',
				content   : 'Press Confirm Or Cancel',
				onConfirm : function () {
					$(this).dialog('close').remove();
				},
				onCancel  : function () {
					$(this).dialog('close').remove();
				}
			}, o);

			$('<div></div>').html(o.content).attr('title', o.title).dialog({
				resizable  : false,
				allowClose : false,
				modal      : true,
				buttons    : [
					{
						text  : jsLanguage.get('TEXT_BUTTON_CONFIRM'),
						icon  : 'ui-icon-check',
						click : function (e){
							o.onConfirm.apply(this, [e, self]);
						}
					},
					{
						text  : jsLanguage.get('TEXT_BUTTON_CANCEL'),
						icon  : 'ui-icon-closethick',
						click : function (e){
							o.onCancel.apply(this, [e, self]);
						}
					}
				]
			});
		},
		showDeleteDialog        : function (o) {
			var self = this;
			var o = $.extend({
				title           : jsLanguage.get('TEXT_DIALOG_TITLE_CONFIRM_DELETE'),
				messageSingle   : jsLanguage.get('TEXT_DIALOG_CONTENT_CONFIRM_DELETE_SINGLE'),
				messageMultiple : jsLanguage.get('TEXT_DIALOG_CONTENT_CONFIRM_DELETE_MULTIPLE'),
				confirmUrl      : this.buildActionLink('deleteConfirm'),
				onSuccess       : function (){
					js_redirect(self.buildCurrentAppRedirect('default'));
				},
				dataType        : 'json',
				type            : 'get'
			}, o);

			var selData = this.getSelectedData();
			var message = o.messageSingle;
			if (/,/.test(selData)){
				message = o.messageMultiple;
			}

			this.showConfirmDialog({
				title     : o.title,
				content   : message,
				onConfirm : function () {
					var dialogEl = this;
					showAjaxLoader($(dialogEl), 'large', false);
					$.ajax({
						cache    : false,
						url      : o.confirmUrl,
						dataType : o.dataType,
						type     : o.type,
						data     : self.getDataKey() + '=' + selData,
						success  : function (data) {
							if (data.success == true){
								if (o.onSuccess){
									o.onSuccess.apply(dialogEl, [data]);
								}
							}
							else {
								if (data.errorMessage){
									alert(data.errorMessage);
								}
								else {
									alert(o.errorMessage);
								}
							}
							removeAjaxLoader($(dialogEl));
							$(dialogEl).dialog('close').remove();
						}
					});
				}
			});
		},
		deleteDialog            : function (o) {
			var self = this;
			return function () {
				self.showDeleteDialog(o);
			}
		},
		showExportDialog        : function (buttonEl) {
			var self = this;
			if (this.getSelectedRows().size() == 0){
				alert('No Rows Selected To Export');
				return;
			}
			var Fields = $(buttonEl).data('fields');

			var FieldTable = $('<table cellpadding="1" cellspacing="0"></table>');
			var FieldTableHeader = $('<thead></thead>');
			var FieldTableBody = $('<thead></thead>');

			FieldTableHeader.append('<tr><td colspan="5">Uncheck to exclude from export</td></tr>');

			var colCount = 0;
			var CurrentRow = $('<tr></tr>');
			$.each(Fields.split(','), function () {
				var FieldLabel = this.split('_');
				FieldLabel.shift();
				$.each(FieldLabel, function (i, k) {
					FieldLabel[i] = FieldLabel[i].charAt(0).toUpperCase() + FieldLabel[i].slice(1);
				});

				CurrentRow.append('<td>' +
					'<input type="checkbox" name="' + this + '" checked="checked">' +
					'<label>' + FieldLabel.join(' ') + '</label>' +
					'</td>');

				colCount++;
				if (colCount > 4){
					FieldTableBody.append(CurrentRow);
					CurrentRow = $('<tr></tr>');
					colCount = 0;
				}
			});

			FieldTable.append(FieldTableHeader);
			FieldTable.append(FieldTableBody);

			var ExportWindow = $('<div></div>').html(FieldTable).attr('title', 'Please Select Fields To Export').dialog({
				resizable  : true,
				allowClose : true,
				modal      : true,
				width      : 'auto',
				buttons    : [
					{
						text  : 'Process Export',
						icon  : 'ui-icon-check',
						click : function () {
							var addedGetVars = [];
							addedGetVars.push(self.getDataKey() + '=' + self.getSelectedData());

							var exportFields = [];
							ExportWindow.find(':checked').each(function () {
								exportFields.push($(this).attr('name'));
							});
							addedGetVars.push('export_columns=' + exportFields.join(','));
							js_redirect(self.buildActionLink('export', [addedGetVars.join('&')]));
						}
					},
					{
						text  : jsLanguage.get('TEXT_BUTTON_CANCEL'),
						icon  : 'ui-icon-closethick',
						click : function () {
							$(this).dialog('close').remove();
						}
					}
				]
			});
		},
		showConfigurationWindow : function (o) {
			var self = this;

			o = $.extend({
				buttonEl      : null,
				contentUrl    : null,
				saveUrl       : null,
				onSaveSuccess : null
			}, o || {});

			self.showWindow({
				buttonEl     : o.buttonEl,
				contentUrl   : o.contentUrl,
				onBeforeShow : function () {
					var window = this;

					var fieldNameError = false;
					var origValues = [];
					$(window).find('input, select, textarea').each(function () {
						var inputName = $(this).attr('name');
						if (inputName == 'configuration_value'){
							fieldNameError = true;
							$(this).addClass('error').attr('disabled', 'disabled');
							return;
						}

						if (!origValues[inputName]){
							if ($(this).attr('type') == 'checkbox'){
								origValues[inputName] = []
							}
							else {
								origValues[inputName] = '';
							}
						}

						var clickFnc = false;
						if ($(this).attr('type') == 'checkbox'){
							if (this.checked){
								origValues[inputName].push($(this).val());
							}
							clickFnc = true;
						} else if ($(this).attr('type') == 'radio'){
							if (this.checked){
								origValues[inputName] = $(this).val();
							}
							clickFnc = true;
						}
						else {
							origValues[inputName] = $(this).val();
						}

						var processChange = function () {
							var edited = false;
							if (typeof origValues[inputName] == 'object'){
								if (this.checked && $.inArray($(this).val(), origValues[inputName]) == -1){
									edited = true;
								} else if (this.checked === false && $.inArray($(this).val(), origValues[inputName]) > -1){
									edited = true;
								}
							} else if (origValues[inputName] != $(this).val()){
								edited = true;
							}

							if (edited === true){
								$('[name="' + inputName + '"]').removeClass('notEdited').addClass('edited');
								$(this).parentsUntil('tbody').last().find('.ui-icon-alert').show();
							}
							else {
								$('[name="' + inputName + '"]').removeClass('edited').addClass('notEdited');
								$(this).parentsUntil('tbody').last().find('.ui-icon-alert').hide();
							}
						};

						if (clickFnc){
							$(this).click(processChange);
						}
						else {
							$(this).blur(processChange);
						}
					});

					$(window).find('.makeModFCK').each(function () {
						CKEDITOR.replace(this, {
							toolbar : 'Simple'
						});
					});

					$(window).find('.makeTabPanel').tabs();
					$(window).find('.makeTabsVertical').each(function () {
						makeTabsVertical('#' + $(this).attr('id'));
					});

					if (fieldNameError === true){
						alert('Editing of some fields has been disabled due to an input naming error, please notify the cart administrator.');
					}
				},
				buttons      : [
					{
						type  : 'cancel',
						click : function () {
							var process = false;
							var hideWindow = function () {
								self.newWindow.effect('fade', {
									mode : 'hide'
								}, function () {
									$(self.element).effect('fade', {
										mode : 'show'
									}, function () {
										self.newWindow.remove();
									});
								});
							};

							if ($(window).find('.edited').size() > 0){
								confirmDialog({
									title     : jsLanguage.get('TEXT_HEADER_CONFIRM_LOST_CHANGES'),
									content   : jsLanguage.get('TEXT_INFO_LOST_CHANGES'),
									onConfirm : hideWindow
								});
							}
							else {
								hideWindow();
							}
						}
					},
					{
						type  : 'save',
						click : function () {
							showAjaxLoader(self.newWindow.find('.edited'), 'small');
							var emptyCheckboxes = [];
							self.newWindow.find('.edited').each(function () {
								if ($(this).attr('type') == 'checkbox'){
									if (this.checked === false){
										if ($.inArray($(this).attr('name'), emptyCheckboxes) == -1){
											emptyCheckboxes.push($(this).attr('name'));
										}
									} else if ($.inArray($(this).attr('name'), emptyCheckboxes) > -1){
										emptyCheckboxes[$.inArray($(this).attr('name'), emptyCheckboxes)] = null;
										delete emptyCheckboxes[$.inArray($(this).attr('name'), emptyCheckboxes)];
									}
								}
							});

							var addPost = '';
							if (emptyCheckboxes.length > 0){
								$.each(emptyCheckboxes, function () {
									addPost += this + '=&';
								});
							}
							$.post(o.saveUrl, addPost + self.newWindow.find('.edited').serialize(), function (data, textStatus, jqXHR) {
								if (data.success === true){
									removeAjaxLoader(self.newWindow.find('.edited'));
									self.newWindow.find('.edited').removeClass('edited').addClass('notEdited');
									if (o.onSaveSuccess){
										o.onSaveSuccess.apply();
									}
								}
							}, 'json');
						}
					}
				]
			});
		}
	});
})(jQuery);