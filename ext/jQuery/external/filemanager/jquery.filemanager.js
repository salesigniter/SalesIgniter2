(function ($, undefined) {
	$.widget("ui.filemanager", {
		options : {
			type : "detail",
			dataUrl : "/ext/jQuery/external/filemanager/getFileListing.php",
			allowedTypes : "*",
			formAction : "/ext/jQuery/external/filemanager/processAction.php",
			fileSource : "/images",
			onSelect : false,
			allowMultiple : false
		},
		isWindow : false,
		windowsHeight : 0,
		data : {},
		listingDecorators : {},
		selectedFiles : [],
		inputElement : null,
		noFileApi : false,
		dialogEl : null,
		maxUpload : 5000000,
		shiftActive : false,
		ctrlActive : false,
		processingShiftClick : false,
		windowBlocks : {
			uploadBlock : '<div style="position: relative;">' +
				'<div id="uploadInfo">Current Upload: <span id="curUpload" data-total_upload="0">0</span><span style="float:left;">&nbsp;&nbsp;Max Upload: <span id="maxUpload">0</span></span></div>' +
				'<button id="returnToListing" type="button"><span>Return To List</span></button>' +
				'<form enctype="multipart/form-data" method="post" action="">' +
				'<input type="file" name="fileToUpload" id="fileToUpload" multiple="true" />' +
				'<input type="hidden" name="uploadPath" value="" />' +
				'<button id="processAction" type="submit"><span>Upload Queued Files</span></button>' +
				'<button id="clearUploads" type="reset"><span>Clear Upload Queue</span></button>' +
				'</form>' +
				'</div>',
			createDirBlock : '<form enctype="multipart/form-data" method="post" action="">' +
				'<button id="processAction"><span>Create Directory</span></button><button id="returnToListing" type="button"><span>Return To List</span></button>' +
				'<div style="margin:.5em;">' +
				'Directory Name: <input type="text" id="newDirName" name="directory_name"><br>' +
				'</div>' +
				'</form>',
			deleteItemBlock : '<form enctype="multipart/form-data" method="post" action="">' +
				'<button id="processAction"><span>Yes Delete Items</span></button><button id="returnToListing" type="button"><span>No I Changed My Mind</span></button>' +
				'<div style="margin:.5em;">' +
				'Are you sure you want to delete the item(s) below?&nbsp;&nbsp;<br><br>' +
				'<span class="selectedItems"></span>' +
				'</div>' +
				'</form>',
			previewBlock : '<div>' +
				'<button id="processAction"><span>Select Image</span></button><button id="returnToListing" type="button"><span>Return To Listing</span></button>' +
				'<div class="preview"></div>' +
				'</div>',
			editItemBlock : '<form  enctype="multipart/form-data" method="post" action="">' +
				'	<button id="processAction"><span>Save</span></button><button id="returnToListing" type="button"><span>Return To List</span></button>' +
				'	<div style="margin:.5em;">' +
				'		<div><b>Name</b></div>' +
				'		<div><input type="text" name="item_name"><input type="hidden" name="item_old_name"></div>' +
				'		<div><b>Permissions</b> ( <input type="text" name="permissions" class="selectedPermissions" size="4" maxlength="3"> )</div>' +
				'		<table>' +
				'			<tr>' +
				'				<td></td>' +
				'				<td>Write</td>' +
				'				<td>Read</td>' +
				'				<td>Execute</td>' +
				'			</tr>' +
				'			<tr>' +
				'				<td>Owner</td>' +
				'				<td><input type="checkbox" value="400"></td>' +
				'				<td><input type="checkbox" value="200"></td>' +
				'				<td><input type="checkbox" value="100"></td>' +
				'			</tr>' +
				'			<tr>' +
				'				<td>Group</td>' +
				'				<td><input type="checkbox" value="40"></td>' +
				'				<td><input type="checkbox" value="20"></td>' +
				'				<td><input type="checkbox" value="10"></td>' +
				'			</tr>' +
				'			<tr>' +
				'				<td>Others</td>' +
				'				<td><input type="checkbox" value="4"></td>' +
				'				<td><input type="checkbox" value="2"></td>' +
				'				<td><input type="checkbox" value="1"></td>' +
				'			</tr>' +
				'		</table>' +
				'	</div>' +
				'</form>'
		},
		_create : function () {
			var self = this;

			if (self.element.is('div')){
				self.isWindow = true;
				self.inputElement = self.element;
			}
			else if (self.element.is('input:file')){
				self.inputElement = $('<input type="text">')
					.attr('name', self.element.attr('name'))
					.val(self.element.val())
					.insertAfter(self.element);

				self.element.attr('name', 'RENAMED').hide();
			}
			else {
				self.inputElement = self.element;
			}
			if (self.element.data('files_source')){
				self.options.fileSource = self.element.data('files_source');
			}
			if (self.element.data('is_multiple')){
				self.options.allowMultiple = (self.element.data('is_multiple') === true);
			}
			if (self.element.data('allowed_extensions')){
				self.options.allowedTypes = self.element.data('allowed_extensions').split(',');
			}
			if (self.element.data('data_url')){
				self.options.dataUrl = self.element.data('data_url');
			}
			if (self.element.data('listing_type')){
				self.options.type = self.element.data('listing_type');
			}
			if (self.options.onSelect){
				self.inputElement.bind('onSelect', function (){
					self.options.onSelect.apply(self, arguments);
				});
			}

			self.inputElement.bind('selectFile', function () {
				var selected = [];
				$('#fileListing').find('.ui-state-active').each(function () {
					var fileInfo = $(this).data('fileInfo');
					selected.push(fileInfo.path.relative);
				});

				$(this).val(selected.join(','));
				$(this).trigger('onSelect', [selected]);

				if (self.isWindow === true){
					window.close();
				}
				else {
					self.dialogEl.dialog('close');
				}
			});

			if (self.isWindow === true){
				self._openDialog();
			}
			else {
				self.inputElement.addClass("ui-filemanager-input")
					.attr({
						role : "upload",
						"aria-haspopup" : "true"
					})
					.bind("click.filemanager", function (event) {
						event.preventDefault();
						if (self.options.disabled){
							return;
						}

						self._openDialog();
					});
			}

			/*var infoEl = $('<div></div>')
			 .css('fontSize', '.8em')
			 .html('Base Dir: ' + self.options.fileSource)
			 .insertAfter(self.inputElement);*/
		},
		_getDecorator : function () {
			return this.listingDecorators[this.options.type];
		},
		_fixElements : function () {
			var self = this;
			self.dialogEl.parent().css('overflow', 'hidden');
			if (self.isWindow === true){
				self.windowsHeight = $(window).innerHeight();
			}
			else {
				self.windowsHeight = self.dialogEl.innerHeight();
			}
			self.dialogEl.find('.ui-filemanager-toolbar').each(function () {
				self.windowsHeight -= $(this).outerHeight();
			});
			self.dialogEl.find('#windows, #uploadBlock, #createDirBlock, #deleteItemBlock, #fileListing, #editItemBlock, #previewBlock').each(function () {
				var paddingTop = $(this).outerHeight(true) - $(this).height();
				$(this).height(self.windowsHeight - paddingTop);

				if ($(this).attr('id') != 'windows' && $(this).css('top') != '0px'){
					$(this).css('top', -(self.windowsHeight));
				}

				if ($(this).attr('id') == 'fileListing'){
					var ListingDecorator = self._getDecorator();
					if (typeof ListingDecorator.fixElements == 'function'){
						ListingDecorator.fixElements.apply(self);
					}
				}
			});
		},
		_showAjaxLoader : function (altDiv) {
			showAjaxLoader(altDiv || $('#fileListing'), 'xlarge');
		},
		_removeAjaxLoader : function (altDiv) {
			removeAjaxLoader(altDiv || $('#fileListing'));
		},
		_showPageBlock : function (showBlock) {
			this.dialogEl.find('.ui-filemanager-toolbar-file-info').animate({
				bottom : '-=' + this.dialogEl.find('.ui-filemanager-toolbar-file-info').outerHeight() + 'px'
			}, 'normal');
			this.dialogEl.find('#fileListing').animate({
				top : '-=' + this.dialogEl.find('#fileListing').outerHeight() + 'px'
			}, 'normal', function () {
				var paddingTop = showBlock.outerHeight(true) - showBlock.height();
				showBlock.animate({
					top : '+=' + showBlock.outerHeight() + 'px'
				}, 'normal');
			});
		},
		_hidePageBlock : function (showBlock) {
			var self = this;
			var paddingTop = showBlock.outerHeight(true) - showBlock.height();
			showBlock.animate({
				top : '-=' + (showBlock.outerHeight() + paddingTop) + 'px'
			}, 'fast', function () {
				showBlock.empty();
				self.dialogEl.find('.ui-filemanager-toolbar-file-info').animate({
					bottom : '+=' + self.dialogEl.find('.ui-filemanager-toolbar-file-info').outerHeight() + 'px'
				}, 'normal');
				self.dialogEl.find('#fileListing').animate({
					top : '+=' + self.dialogEl.find('#fileListing').outerHeight() + 'px'
				}, 'normal');
				self._getFileListing();
				self.selectedFiles = [];
				self._updateSelectedFile();
			});
		},
		_reportMessages : function (blockEl, messages) {
			$.each(messages, function () {
				blockEl.prepend('<div class="errorReport ui-state-error">' + this + '</div>');
			});
		},
		_openDialog : function () {
			var self = this;

			var dialogHtml = '<div>' +
				'	<div class="ui-widget-header ui-filemanager-toolbar">' +
				'		<div class="ui-filemanager-toolbar-action-icons">' +
				'			<span>Actions: </span>' +
				'			<a href="javascript:void(0)" id="createDir" class="ui-filemanager-icon ui-filemanager-icon-action ui-filemanager-icon-action-newdir" tooltip="Create A New Folder" data-action_block="createDirBlock"></a>' +
				'			<a href="javascript:void(0)" id="uploadFile" class="ui-filemanager-icon ui-filemanager-icon-action ui-filemanager-icon-action-upload" tooltip="Upload File(s)" data-action_block="uploadBlock"></a>' +
				'			<!--<a href="javascript:void(0)" id="downloadFile" class="ui-filemanager-icon ui-filemanager-icon-action ui-filemanager-icon-action-saveas ui-state-disabled" tooltip="Download Selected File(s)"></a>-->' +
				'			<a href="javascript:void(0)" id="editItem" class="ui-filemanager-icon ui-filemanager-icon-action ui-filemanager-icon-action-edit ui-state-disabled" tooltip="Edit Selected File/Folder" data-action_block="editItemBlock"></a>' +
				'			<a href="javascript:void(0)" id="deleteItem" class="ui-filemanager-icon ui-filemanager-icon-action ui-filemanager-icon-action-delete ui-state-disabled" tooltip="Delete Selected File(s)/Folder(s)" data-action_block="deleteItemBlock"></a>' +
				'			<a href="javascript:void(0)" id="previewFile" class="ui-filemanager-icon ui-filemanager-icon-action ui-filemanager-icon-action-preview ui-state-disabled" tooltip="Preview File" data-action_block="previewBlock"></a>' +
				'			<a href="javascript:void(0)" id="refreshListing" class="ui-filemanager-icon ui-filemanager-icon-action ui-filemanager-icon-action-refresh" tooltip="Refresh Listing"></a>' +
				'		</div>' +
				'		<div class="ui-filemanager-toolbar-view-icons">' +
				'			<span>View: </span>' +
				'			<a href="javascript:void(0)" tooltip="Choose View" class="ui-filemanager-icon ui-filemanager-icon-view ui-filemanager-icon-view-choose"></a>' +
				'			<ul class="ui-widget ui-widget-content ui-corner-all ui-filemanager-toolbar-view-icons-list">' +
				'				<li' + (self.options.type == 'icons' ? ' class="ui-state-active"' : '') + ' data-view_type="icons"><span class="ui-filemanager-icon ui-filemanager-icon-view ui-filemanager-icon-view-icons"></span>&nbsp;Large Icons</li>' +
				'				<li' + (self.options.type == 'columns' ? ' class="ui-state-active"' : '') + ' data-view_type="columns"><span class="ui-filemanager-icon ui-filemanager-icon-view ui-filemanager-icon-view-columns"></span>&nbsp;Small Icons</li>' +
				'				<li' + (self.options.type == 'detail' ? ' class="ui-state-active"' : '') + ' data-view_type="detail"><span class="ui-filemanager-icon ui-filemanager-icon-view ui-filemanager-icon-view-detail"></span>&nbsp;Details</li>' +
				'				<li' + (self.options.type == 'tile' ? ' class="ui-state-active"' : '') + ' data-view_type="tile"><span class="ui-filemanager-icon ui-filemanager-icon-view ui-filemanager-icon-view-tile"></span>&nbsp;Tiles</li>' +
				'				<li' + (self.options.type == 'list' ? ' class="ui-state-active"' : '') + ' data-view_type="list"><span class="ui-filemanager-icon ui-filemanager-icon-view ui-filemanager-icon-view-list"></span>&nbsp;Content</li>' +
				'			</ul>' +
				'		</div>' +
				'		<div class="ui-filemanager-toolbar-breadcrumb">' +
				'			<a href="javascript:void(0)" id="breadcrumbBack" class="ui-filemanager-icon ui-filemanager-icon-breadcrumb ui-filemanager-icon-breadcrumb-prev" tooltip="Previous Directory"></a>' +
				'			<span></span>' +
				'		</div>' +
				'	</div>' +
				'	<div id="windows" style="position:relative;">' +
				'		<div id="fileListing"></div>' +
				'		<div id="uploadBlock"></div>' +
				'		<div id="createDirBlock"></div>' +
				'		<div id="deleteItemBlock"></div>' +
				'		<div id="editItemBlock"></div>' +
				'		<div id="previewBlock"></div>' +
				'	</div>' +
				'	<div class="ui-widget-header ui-filemanager-toolbar ui-filemanager-toolbar-file-info">' +
				'		<div id="selectedFileInfo">' +
				'			<div class="column" id="selectedFileIcon" style="width: 50px;"></div>' +
				'			<div class="column" style="width: 150px;text-align:left;">' +
				'				<div id="selectedFileName" class="value"></div>' +
				'				<div id="selectedFileType" class="value"></div>' +
				'			</div>' +
				'			<div class="column" style="width: 490px;">' +
				'				<div id="selectedFileSize">' +
				'					<span class="label">Size: </span>' +
				'					<span class="value"></span>' +
				'				</div>' +
				'				<div id="selectedFileDateTaken">' +
				'					<span class="label">Date Taken: </span>' +
				'					<span class="value"></span>' +
				'				</div>' +
				'				<div id="selectedFileDimensions">' +
				'					<span class="label">Dimensions: </span>' +
				'					<span class="value"></span>' +
				'				</div>' +
				'				<div id="selectedFileDateAdded">' +
				'					<span class="label">Date Added: </span>' +
				'					<span class="value"></span>' +
				'				</div>' +
				'				<div id="selectedFileDateModified">' +
				'					<span class="label">Date Modified: </span>' +
				'					<span class="value"></span>' +
				'				</div>' +
				'			</div>' +
				'		</div>' +
				'	</div>' +
				'</div>';

			function setupFileListingWindow() {
				var dialogWindow = $(this);

				dialogWindow.parent().addClass('ui-filemanager-dialog');
				dialogWindow.parent().removeClass('ui-corner-all');
				dialogWindow.parent().find('.ui-corner-all').removeClass('ui-corner-all');

				$(document).bind('keyup.filemanager',
					function (e) {
						var keyName = self.getKeyName(e.which);
						if (keyName == 'unknown'){
							return true;
						}
						if (keyName == 'shift'){
							self.shiftActive = false;
							if ($('#fileListing').data('anchorItem')){
								$('#fileListing').data('currentItem', $('#fileListing').data('anchorItem'));
								$('#fileListing').removeData('anchorItem');
							}
						}
						if (keyName == 'ctrl'){
							self.ctrlActive = false;
						}
					}).bind('keydown.filemanager', function (e) {
						var ListingDecorator = self._getDecorator();
						var keyName = self.getKeyName(e.which);
						if (keyName == 'unknown'){
							return true;
						}
						if (self.ctrlActive === true && keyName == 'selectAll'){
							$(ListingDecorator.listItemSelector).click();
							return false;
						}
						if (keyName == 'shift'){
							self.shiftActive = true;
							return true;
						}
						if (keyName == 'ctrl'){
							self.ctrlActive = true;
							return true;
						}

						if (ListingDecorator.supportsKeyDown(keyName)){
							var idxArr = ListingDecorator.getKeyDownSelections(keyName);
							if ($.isArray(idxArr) === false && idxArr != 0){
								if (self.shiftActive === true){
									if (!$('#fileListing').data('anchorItem')){
										$('#fileListing').data('anchorItem', $('#fileListing').data('currentItem'));
									}
									var anchorItem = $('#fileListing').data('anchorItem');
								}
								var newIdxArr = [];
								var toIndex = Math.abs(idxArr);
								var addToIdx = (idxArr > 0);
								var thisItem = $('#fileListing').data('currentItem');
								var thisIndex = parseInt(thisItem.index());
								if (anchorItem){
									var fromIdx = parseInt(anchorItem.index());
									newIdxArr.push(fromIdx);
									while(fromIdx < thisIndex || fromIdx > thisIndex){
										if (fromIdx > thisIndex){
											fromIdx--;
										}
										else {
											fromIdx++;
										}
										newIdxArr.push(fromIdx);
									}
								}
								//alert(print_r(newIdxArr, true));
								for(var i = 1; i <= toIndex; i++){
									var idxResult = 0;
									if (addToIdx === true){
										idxResult = thisIndex + i;
									}
									else {
										idxResult = thisIndex - i;
									}

									var arrKey = $.inArray(idxResult, newIdxArr);
									if (arrKey == -1){
										if (self.shiftActive || i == toIndex){
											newIdxArr.push(idxResult);
										}
									}
									else {
										while(newIdxArr[arrKey + 1]){
											newIdxArr.pop();
										}
									}
								}
								//alert(print_r(newIdxArr, true));
								idxArr = newIdxArr;
							}
							self._selectItems(idxArr);
						}
					});
				$('#fileListing').data('homeDirectory', self.options.fileSource);
				$('#fileListing').data('currentDirectory', self.options.fileSource);
			}

			if (self.isWindow === true){
				self.dialogEl = self.inputElement;
				self.inputElement.html(dialogHtml);

				$(window).resize(function () {
					self._fixElements();
				});

				setupFileListingWindow.apply(self.dialogEl);
			}
			else {
				self.dialogEl = $(dialogHtml).dialog({
					title : 'File Manager',
					minWidth : 700,
					height : 565,
					close : function () {
						$(this).dialog('destroy').remove();
					},
					resize : function () {
						self._fixElements();
					},
					open : setupFileListingWindow,
					buttons : {

					}
				});
			}
			self._fixElements();

			self._setupBreadcrumb();
			self._setupViewsBar();
			self._setupActionsBar();
			self._getFileListing();
		},
		_changeDirectory : function (To) {
			if ($.isPlainObject(To)){
				$('#fileListing').data('currentDirectory', To.path.absolute);
			}
			else {
				$('#fileListing').data('currentDirectory', To);
			}
			this.selectedFiles = [];
			this._updateSelectedFile();
			this._setupBreadcrumb();
			this._getFileListing();
		},
		_setupBreadcrumb : function () {
			var self = this;
			var home = $('#fileListing').data('homeDirectory').split('/');
			var current = $('#fileListing').data('currentDirectory').split('/');
			var breadcrumb = '/';
			var dirPath = '/';
			for(var i = 0; i < current.length; i++){
				if (current[i] == ''){
					continue;
				}

				dirPath += current[i] + '/';
				if (home[i]){
					if (home[i + 1]){
						breadcrumb += home[i] + '/';
					}
					else {
						breadcrumb += '<a href="Javascript:void(0)" data-to_dir="' + dirPath + '">' + current[i] + '</a>/';
					}
				}
				else {
					breadcrumb += '<a href="Javascript:void(0)" data-to_dir="' + dirPath + '">' + current[i] + '</a>/';
				}
			}
			self.dialogEl.find('.ui-filemanager-toolbar-breadcrumb span').html(breadcrumb);

			var prevButton = $('.ui-filemanager-toolbar-breadcrumb .ui-filemanager-icon-breadcrumb-prev');
			if ($('.ui-filemanager-toolbar-breadcrumb span a').size() == 1){
				prevButton.addClass('ui-state-disabled');
			}
			else {
				prevButton.removeClass('ui-state-disabled');
			}

			prevButton.unbind('click').click(function () {
				if ($(this).hasClass('ui-state-disabled')){
					return false;
				}
				var Links = $('.ui-filemanager-toolbar-breadcrumb span a');
				if (Links.size() > 1){
					self._changeDirectory(Links.last().prev().data('to_dir'));
				}
			});

			$('.ui-filemanager-toolbar-breadcrumb span a').unbind('click').click(function () {
				self._changeDirectory($(this).data('to_dir'));
			});

			if (self.options.allowMultiple === true){
				var button = $('<button><span>Select Images</span></button>').click(
					function () {
						self.inputElement.trigger('selectFile');
					}).css({
						position : 'absolute',
						bottom : 0,
						right : 0
					}).button();

				button.insertAfter($('.ui-filemanager-toolbar-breadcrumb'));
			}
		},
		_selectItems : function (idxArr) {
			var self = this;
			var ListingDecorator = self._getDecorator();
			$(ListingDecorator.listItemSelector + '.ui-state-active').each(function () {
				if ($.inArray(idxArr, $(this).index()) == -1){
					$(this).trigger('unclick');
				}
			});

			for(var i = 0; i < idxArr.length; i++){
				var listingEl = $(ListingDecorator.listItemSelector + ':eq(' + idxArr[i] + ')');
				listingEl.trigger('mousedown');
			}

			if (self.processingShiftClick === true){
				self.processingShiftClick = false;
			}
		},
		_getFileListing : function () {
			var self = this;
			$.ajax({
				cache : false,
				url : self.options.dataUrl,
				dataType : 'json',
				data : 'filesSource=' + $('#fileListing').data('currentDirectory'),
				type : 'post',
				beforeSend : function () {
					self._showAjaxLoader();
				},
				success : function (data) {
					self._removeAjaxLoader();
					self.data = data.files;
					self.maxUpload = data.maxUpload;

					self._buildFileListing(self.dialogEl.find('#fileListing'));
				}
			});
		},
		_buildFileListing : function (appendToEl) {
			var self = this;
			var ListingDecorator = self._getDecorator();

			appendToEl.empty();
			appendToEl.append(ListingDecorator.init(self));
			appendToEl.find(ListingDecorator.listItemSelector)
				.mouseover(function () {
					this.style.cursor = 'pointer';
					if (!$(this).hasClass('ui-state-active')){
						$(this).addClass('ui-state-hover');
					}
				})
				.mouseout(function () {
					this.style.cursor = 'default';
					if (!$(this).hasClass('ui-state-active')){
						$(this).removeClass('ui-state-hover');
					}
				})
				.mousedown(function (e, keyDownEvent) {
					this.waitForMouseUp = false;
					if (self.processingShiftClick === false && self.shiftActive && !$(this).hasClass('ui-state-active')){
						var selectIdxArr = [];
						if ($('#fileListing').data('currentItem').index() > $(this).index()){
							selectIdxArr.push($(this).index());
							for(var i = $(this).index(); i < $('#fileListing').data('currentItem').index(); i++){
								selectIdxArr.push(i);
							}
						} else if ($('#fileListing').data('currentItem').index() < $(this).index()){
							for(var i = $('#fileListing').data('currentItem').index(); i < $(this).index(); i++){
								selectIdxArr.push(i);
							}
							selectIdxArr.push($(this).index());
						}
						self.processingShiftClick = true;
						self._selectItems(selectIdxArr);
					}
					else if (self.ctrlActive && $(this).hasClass('ui-state-active')){
						$(this).trigger('unclick');
						self._updateSelectedFile();
					}
					else {
						if ($(this).hasClass('ui-state-active')){
							this.waitForMouseUp = true;
						}

						if (this.waitForMouseUp === false){
							if (!self.ctrlActive && !self.shiftActive){
								$('#fileListing').find('.ui-state-active').removeClass('ui-state-active');
							}

							$(this).removeClass('ui-state-hover').addClass('ui-state-active');
							$('#fileListing').data('currentItem', $(this));
							self._updateSelectedFile($(this).data('fileInfo'));
						}
					}
				})
				.mouseup(function (){
					if (this.waitForMouseUp === true){
						if (!self.ctrlActive && !self.shiftActive){
							$('#fileListing').find('.ui-state-active').removeClass('ui-state-active');
						}

						$(this).removeClass('ui-state-hover').addClass('ui-state-active');
						$('#fileListing').data('currentItem', $(this));
						self._updateSelectedFile($(this).data('fileInfo'));
					}
				})
				.dblclick(function () {
					if ($(this).data('fileInfo').type.mime == 'directory'){
						self._changeDirectory($(this).data('fileInfo'));
					}
					else {
						self.inputElement.trigger('selectFile');
					}
				})
				.bind('unclick', function () {
					$(this).removeClass('ui-state-hover').removeClass('ui-state-active');
					var keyIdx = $.inArray($(this).data('fileInfo'), self.selectedFiles);
					if (keyIdx >= 0){
						if ($(this) == $('#fileListing').data('currentItem')){
							if (self.selectedFiles[keyIdx - 1]){
								$('#fileListing').data('currentItem', self.selectedFiles[keyIdx - 1]);
							} else if (self.selectedFiles[keyIdx + 1]){
								$('#fileListing').data('currentItem', self.selectedFiles[keyIdx + 1]);
							}
							else {
								$('#fileListing').removeData('currentItem');
							}
						}
						self.selectedFiles.splice(keyIdx, 1);
					}
				});

			appendToEl.find(ListingDecorator.listItemSelector).draggable({
				revert : false,
				cursorAt: { left: 3, top: 3 },
				helper : function (e, ui) {
					var items = [];
					$('#fileListing').find('.ui-state-active').each(function () {
						items.push($(this).data('fileInfo'));
					});

					var html;
					if (items.length > 1){
						html = items.length + ' Items';
					}else{
						if (/image/i.test(items[0].type.mime)){
							html = '<img src="' + items[0].path.relative + '" width="128" height="128">';
						}
						else {
							html = '<span class="ui-filemanager-icon ui-filemanager-icon-128 ' + items[0].type.icon + '"></span>';
						}
					}

					return $('<div></div>')
						.data('selectedItems', items)
						.addClass('ui-filemanager-dragging')
						.html(html);
				},
				appendTo : appendToEl
			});

			appendToEl.find(ListingDecorator.listItemSelector).each(function (){
				if ($(this).data('fileInfo').type.mime == 'directory'){
					var moveToDir = $(this).data('fileInfo').name;
					$(this).droppable({
						tolerance: 'pointer',
						accept: '.ui-draggable',
						hoverClass: 'ui-state-hover',
						drop: function (e, ui){
							var items = [];
							$.each(ui.helper.data('selectedItems'), function (){
								items.push('item[]=' + this.name);
							});

							$.ajax({
								cache : false,
								url : self.options.formAction + '?rType=ajax&action=moveItem',
								dataType : 'json',
								data : 'currentDir=' + $('#fileListing').data('currentDirectory') + '&moveToDir=' + moveToDir + '&' + items.join('&'),
								type : 'post',
								beforeSend : function () {
									self._showAjaxLoader($('#fileListing'));
								},
								success : function (data) {
									self._removeAjaxLoader($('#fileListing'));
									self._getFileListing();
								}
							});
						}
					});
				}
			});

			if (typeof ListingDecorator.onLoad == 'function'){
				ListingDecorator.onLoad.apply(self, [appendToEl]);
			}
		},
		_setupViewsBar : function () {
			var self = this;
			var barEl = self.dialogEl.find('.ui-filemanager-toolbar-view-icons');

			barEl.find('li')
				.mouseover(function () {
					$(this).addClass('ui-state-hover');
				})
				.mouseout(function () {
					$(this).removeClass('ui-state-hover');
				})
				.click(function (e) {
					e.preventDefault();
					var FileListing = self.dialogEl.find('#fileListing');
					var selectedFile = FileListing.find('.ui-state-active').attr('data-file_path');

					barEl.find('li').removeClass('ui-state-active');
					$(this).removeClass('ui-state-hover').addClass('ui-state-active');

					var ListingDecorator = self._getDecorator();
					if (typeof ListingDecorator.onUnload == 'function'){
						ListingDecorator.onUnload.apply(self);
					}

					self.options.type = $(this).data('view_type');
					self._buildFileListing(FileListing);
					$.each(self.selectedFiles, function () {
						FileListing.find('*[data-file_path="' + this.path.absolute + '"]').click();
					})
				});

			barEl.find('.ui-filemanager-icon-view-choose').click(function () {
				if (barEl.find('.ui-filemanager-toolbar-view-icons-list').is(':hidden')){
					barEl.find('.ui-filemanager-toolbar-view-icons-list').show();
					$(document).one('click', function () {
						barEl.find('.ui-filemanager-toolbar-view-icons-list').hide();
					});
				}
				else {
					barEl.find('.ui-filemanager-toolbar-view-icons-list').hide();
				}
				return false;
			});
		},
		_setupActionsBar : function () {
			var self = this;
			var barEl = self.dialogEl.find('.ui-filemanager-toolbar-action-icons');

			barEl.find('a').click(function () {
				if ($(this).hasClass('ui-state-disabled')){
					return false;
				}

				if (typeof self['_' + $(this).attr('id')] == 'function'){
					self['_' + $(this).attr('id')].apply(self);
				}
				else {
					var blockContainer = self.dialogEl.find('#' + $(this).data('action_block'));
					if (blockContainer.html() == ''){
						var actionBlock = $(self.windowBlocks[blockContainer.attr('id')]);
						blockContainer.append(actionBlock);
						self['_setupBlock'](blockContainer.attr('id'), actionBlock);

						self._showPageBlock(blockContainer);
					}
					else {
						self._hidePageBlock(blockContainer);
					}
				}
			});
		},
		_refreshListing : function () {
			this._getFileListing();
		},
		_setupBlock : function (actionBlockId, blockEl) {
			var self = this;
			blockEl.find('button').button();

			blockEl.find('#returnToListing').unbind('click').click(function (e) {
				e.preventDefault();
				self.dialogEl.find('a[data-action_block="' + actionBlockId + '"]').click();
			});

			self['_setupBlock_' + actionBlockId](blockEl);
		},
		_setupBlock_editItemBlock : function (blockEl) {
			var self = this;
			var permissions = 000;
			var formatPerms = function (perms) {
				if (perms < 10){
					return '00' + perms;
				} else if (perms < 100){
					return '0' + perms;
				} else if (perms < 1000){
					return perms;
				}
			};
			var selectedEl = $('#fileListing').find('.ui-state-active');
			blockEl.find('input[name=item_old_name]').val(selectedEl.data('fileInfo').name);
			blockEl.find('input[name=item_name]').val(selectedEl.data('fileInfo').name);

			blockEl.find('input[name=permissions]').unbind('keyup').keyup(function () {
				var inputVal = $(this).val();
				blockEl.find('input:checkbox').each(function () {
					this.checked = false;
					var thisVal = $(this).val();
					var thisDigit = thisVal.substr(0, 1);
					var permDigit = 0;
					if (thisVal < 10){
						if (parseInt(inputVal) > 99){
							permDigit = inputVal.substr(2, 1);
						} else if (parseInt(inputVal) > 9){
							permDigit = inputVal.substr(1, 1);
						}
						else {
							permDigit = inputVal.substr(0, 1);
						}
					} else if (thisVal < 100){
						if (parseInt(inputVal) > 99){
							var permDigit = inputVal.substr(1, 1);
						} else if (parseInt(inputVal) > 9){
							var permDigit = inputVal.substr(0, 1);
						}
					}
					else {
						if (parseInt(inputVal) > 99){
							var permDigit = inputVal.substr(0, 1);
						}
					}
					if (thisDigit == '4'){
						this.checked = (permDigit == '7' || permDigit == '6' || permDigit == '5' || permDigit == '4');
					} else if (thisDigit == '2'){
						this.checked = (permDigit == '7' || permDigit == '6' || permDigit == '3' || permDigit == '2');
					}
					else {
						this.checked = (permDigit == '7' || permDigit == '5' || permDigit == '3' || permDigit == '1');
					}
				});
			});

			var curPerms = selectedEl.data('fileInfo').permissions;
			blockEl.find('input:checkbox').each(function () {
				var thisVal = $(this).val();
				var thisDigit = thisVal.substr(0, 1);
				var permDigit;
				if (thisVal.length == 3){
					permDigit = curPerms.substr(1, 1);
				} else if (thisVal.length == 2){
					permDigit = curPerms.substr(2, 1);
				}
				else {
					permDigit = curPerms.substr(3, 1);
				}
				if (thisDigit == '4'){
					this.checked = (permDigit == '7' || permDigit == '6' || permDigit == '5' || permDigit == '4');
				} else if (thisDigit == '2'){
					this.checked = (permDigit == '7' || permDigit == '6' || permDigit == '3' || permDigit == '2');
				}
				else {
					this.checked = (permDigit == '7' || permDigit == '5' || permDigit == '3' || permDigit == '1');
				}
			});

			blockEl.find('input:checkbox').each(function () {
				$(this).unbind('click').click(function () {
					permissions = 0;
					blockEl.find('input[type=checkbox]:checked').each(function () {
						permissions += parseInt($(this).val());
					});
					$('.selectedPermissions').val(formatPerms(permissions));
				});

				if ($(this).is(':checked')){
					permissions += parseInt($(this).val());
				}
			});
			$('.selectedPermissions').val(formatPerms(permissions));

			blockEl.find('#processAction').unbind('click').click(function (e) {
				e.preventDefault();
				$.ajax({
					cache : false,
					url : self.options.formAction + '?rType=ajax&action=editItem',
					dataType : 'json',
					data : 'currentDir=' + $('#fileListing').data('currentDirectory') + '&item_name=' + blockEl.find('input[name=item_name]').val() + '&item_old_name=' + blockEl.find('input[name=item_old_name]').val() + '&permissions=' + blockEl.find('input[name=permissions]').val(),
					type : 'post',
					beforeSend : function () {
						self._showAjaxLoader(blockEl);
					},
					success : function (data) {
						self._removeAjaxLoader(blockEl);
						self.dialogEl.find('a[data-action_block="' + blockEl.attr('id') + '"]').click();
						self._getFileListing();
					}
				});
			});
		},
		_setupBlock_deleteItemBlock : function (blockEl) {
			var self = this;
			var Dirs = [];
			var Items = [];
			var ItemHtml = [];
			$('#fileListing .ui-state-active').each(function () {
				if ($(this).data('fileInfo').type.mime == 'directory'){
					Dirs.push('dir[]=' + $(this).data('fileInfo').name);
					ItemHtml.push('Directory: ' + $(this).data('fileInfo').name);
				}
				else {
					Items.push('item[]=' + $(this).data('fileInfo').name);
					ItemHtml.push('File: ' + $(this).data('fileInfo').name);
				}
			});

			blockEl.find('.selectedItems').html(ItemHtml.join('<br>'));
			blockEl.find('#processAction').unbind('click').click(function (e) {
				e.preventDefault();
				$.ajax({
					cache : false,
					url : self.options.formAction + '?rType=ajax&action=deleteItems',
					dataType : 'json',
					data : 'currentDir=' + $('#fileListing').data('currentDirectory') + '&' + Items.join('&') + '&' + Dirs.join('&'),
					type : 'post',
					beforeSend : function () {
						self._showAjaxLoader(blockEl);
					},
					success : function (data) {
						if (data.success === false){
							self._reportMessages(blockEl, data.messages);
						}
						else {
							self.dialogEl.find('a[data-action_block="' + blockEl.attr('id') + '"]').click();
							self._getFileListing();
						}
						self._removeAjaxLoader(blockEl);
					}
				});
			});
		},
		_setupBlock_previewBlock : function (blockEl) {
			var self = this;

			blockEl.find('.preview').html('<img src="' + $('#fileListing').data('currentItem').data('fileInfo').path.relative + '">');
			blockEl.find('#processAction').unbind('click').click(function (e) {
				e.preventDefault();
				self.dialogEl.find('a[data-action_block="' + blockEl.attr('id') + '"]').click();
			});
		},
		_setupBlock_createDirBlock : function (blockEl) {
			var self = this;
			blockEl.find('#processAction').unbind('click').click(function (e) {
				e.preventDefault();
				$.ajax({
					cache : false,
					url : self.options.formAction + '?rType=ajax&action=createDirectory',
					dataType : 'json',
					data : 'currentDir=' + $('#fileListing').data('currentDirectory') + '&dirName=' + blockEl.find('#newDirName').val(),
					type : 'post',
					beforeSend : function () {
						self._showAjaxLoader(blockEl);
					},
					success : function (data) {
						self._removeAjaxLoader(blockEl);
						self.dialogEl.find('a[data-action_block="' + blockEl.attr('id') + '"]').click();
						self._getFileListing();
					}
				});
			});
		},
		_setupBlock_uploadBlock : function (blockEl) {
			var self = this;
			blockEl.find('form').attr('action', self.options.formAction + '?action=upload');
			blockEl.find('input[name=uploadPath]').val($('#fileListing').data('currentDirectory'));

			blockEl.find('#maxUpload').html(self._formatBytes(self.maxUpload));

			blockEl.find('#fileToUpload').fileUploader({
				selectFileLabel : 'Select files',
				allowedExtension : (self.options.allowedTypes != '*' ? self.options.allowedTypes.join('|') : ''),
				buttonUpload : '#processAction',
				buttonClear : '#clearUploads',
				onFileChange : function (e, form) {
					var curUpload = blockEl.find('#curUpload').data('total_upload');
					if (!curUpload){
						curUpload = 0;
					}
					curUpload += parseFloat(e.size);
					blockEl.find('#curUpload').html(self._formatBytes(curUpload));
					blockEl.find('#curUpload').data('total_upload', curUpload);
				}
			});
			return;
		},
		_formatBytes : function (bytes) {
			var Formatted = '';
			if (bytes > 1024 * 1000){
				Formatted = (Math.round(bytes / (1024 * 1000))).toString() + 'MB';
			}
			else if (bytes > 1024){
				Formatted = (Math.round(bytes / 1024)).toString() + 'KB';
			}
			else {
				Formatted = bytes.toString() + 'Bytes';
			}
			return Formatted;
		},
		_formatTransferSpeed : function (bytes) {
			var Formatted = '';
			if (bytes > 1024 * 1024){
				Formatted = (Math.round(bytes * 100 / (1024 * 1024)) / 100).toString() + 'MBps';
			}
			else if (bytes > 1024){
				Formatted = (Math.round(bytes * 100 / 1024) / 100).toString() + 'KBps';
			}
			else {
				Formatted = bytes.toString() + 'Bps';
			}
			return Formatted;
		},
		_checkUploadSize : function (Size) {
			return (Size < this.maxUpload);
		},
		_checkExtension : function (val) {
			var self = this;

			var returnVal = false;
			if (self.options.allowedTypes == '*'){
				returnVal = true;
			}
			else {
				$.each(self.options.allowedTypes, function () {
					var extCheck = val.substr(val.lastIndexOf('.') + 1);
					if (extCheck == this){
						returnVal = true;
						return;
					}
				});
			}
			return returnVal;
		},
		_updateSelectedFile : function (fileInfo) {
			var self = this;
			$('#editItem, #deleteItem, #previewFile').addClass('ui-state-disabled');
			if (fileInfo){
				if (self.ctrlActive || self.shiftActive){
					if ($.inArray(fileInfo, self.selectedFiles) < 0){
						self.selectedFiles.push(fileInfo);
					}
					$('#deleteItem').removeClass('ui-state-disabled');
				}
				else {
					self.selectedFiles = [fileInfo];
					if (fileInfo.type.mime != 'directory'){
						$('#previewFile').removeClass('ui-state-disabled');
					}
					$('#editItem, #deleteItem').removeClass('ui-state-disabled');
				}
			}

			if (self.selectedFiles.length == 0){
				$('#selectedFileInfo').hide();
				return;
			}

			if (self.selectedFiles.length > 1){
				var totalSizeSelected = 0;
				var allTypes = [];
				$.each(self.selectedFiles, function () {
					totalSizeSelected += parseFloat(this.size.integer);
					if ($.inArray(this.type.name, allTypes) < 0){
						allTypes.push(this.type.name);
					}
				});

				$('#selectedFileInfo').each(function () {
					$(this).find('#selectedFileIcon').html('<div width="50" height="50"></div>').show();
					$(this).find('#selectedFileName').html(self.selectedFiles.length + ' Items Selected').show();
					$(this).find('#selectedFileType').html(allTypes.join(', ')).show();
					$(this).find('#selectedFileSize .value').html(self._formatBytes(totalSizeSelected)).show();
					$(this).find('#selectedFileDimensions').hide();
					$(this).find('#selectedFileDateAdded').hide();
					$(this).find('#selectedFileDateModified').hide();
					$(this).find('#selectedFileDateTaken').hide();
				});
			}
			else {
				$.each(self.selectedFiles, function () {
					var fileInfo = this;
					$('#selectedFileInfo').each(function () {
						var preview = '';
						if (fileInfo.type.mime == 'directory'){
							preview = '<span class="ui-filemanager-icon ui-filemanager-icon-32 ' + fileInfo.type.icon + '"></span>';
						}
						else {
							preview = '<img src="' + fileInfo.path.relative + '" width="50" height="50">';
						}
						$(this).find('#selectedFileIcon').html(preview).show();
						$(this).find('#selectedFileName').attr('tooltip', fileInfo.name).html(fileInfo.name).show();
						$(this).find('#selectedFileType').html(fileInfo.type.name).show();
						$(this).find('#selectedFileSize').show().find('.value').html(self._formatBytes(fileInfo.size.integer));
						$(this).find('#selectedFileDimensions').show().find('.value').html(fileInfo.dimensions);
						$(this).find('#selectedFileDateAdded').show().find('.value').html(fileInfo.created);
						$(this).find('#selectedFileDateModified').show().find('.value').html(fileInfo.modified);
						$(this).find('#selectedFileDateTaken').hide();
					});
				});
			}
			$('#selectedFileInfo').show();
		},
		getKeyName : function (which) {
			switch(which){
				case 16:
					return 'shift';
					break;
				case 17:
					return 'ctrl';
					break;
				case 37:
					return 'arrowLeft';
					break;
				case 38:
					return 'arrowUp';
					break;
				case 39:
					return 'arrowRight';
					break;
				case 40:
					return 'arrowDown';
					break;
				case 65:
					return 'selectAll';
					break;
			}
			return 'unknown';
		}
	});

	$.extend($.ui.filemanager.prototype.listingDecorators, {
		columns : {
			listItemSelector : '.ui-filemanager-listing-columns li',
			supportsKeyDown : function (keyName) {
				return true;
			},
			getKeyDownSelections : function (keyName) {
				switch(keyName){
					case 'arrowLeft':
						return -1;
						break;
					case 'arrowRight':
						return 1;
						break;
					case 'arrowUp':
						return -(this._getNumOfItemsInRow());
						break;
					case 'arrowDown':
						return this._getNumOfItemsInRow();
						break;
				}
				return 0;
			},
			init : function (mngrClass) {
				var files = mngrClass.data;
				var listingEl = $('<ul></ul>')
					.addClass('ui-filemanager-listing-columns');

				$.each(files, function () {
					var file = this;
					var listingItem = $('<li></li>')
						.append('<span class="ui-filemanager-icon ui-filemanager-icon-18 ' + file.type.icon + '"></span><span class="fileName" data-fullname="' + file.name + '">' + file.name + '</span>');

					listingItem.attr('data-file_path', file.path.absolute).data('fileInfo', file);

					listingEl.append(listingItem);
				});

				return listingEl;
			},
			_getNumOfItemsInRow : function () {
				var currentItem = $('#fileListing').find('li').first();
				var positionTopCheck = currentItem.position().top;
				var numOfItems = 1;
				while(true){
					currentItem = currentItem.next();
					if (positionTopCheck == currentItem.position().top){
						numOfItems++;
					}
					else {
						break;
					}
				}
				return numOfItems;
			},
			onLoad : function (appendToEl) {
				var widest = 0;
				var tallest = 0;
				appendToEl.find('li').each(function () {
					if ($(this).width() > widest){
						widest = $(this).width();
					}
					if ($(this).height() > tallest){
						tallest = $(this).height();
					}
				});
				appendToEl.find('li').css({
					height : tallest + 'px',
					width : widest + 'px'
				});
			}
		}
	});

	$.extend($.ui.filemanager.prototype.listingDecorators, {
		detail : {
			listItemSelector : '.ui-filemanager-listing-detail tbody tr',
			supportsKeyDown : function (keyName) {
				return (keyName == 'arrowLeft' || keyName == 'arrowRight' ? false : true);
			},
			getKeyDownSelections : function (keyName) {
				switch(keyName){
					case 'arrowUp':
						return -1;
						break;
					case 'arrowDown':
						return 1;
						break;
				}
				return 0;
			},
			init : function (mngrClass) {
				var files = mngrClass.data;
				var listingEl = $('<table cellspacing="0" width="100%"></table>')
					.addClass('ui-filemanager-listing-detail');
				var listingTbody = $('<tbody></tbody>');

				listingEl.append('<thead>' +
					'<tr>' +
					'<th></th>' +
					'<th>Name</th>' +
					'<th>Modified</th>' +
					'<th>Type</th>' +
					'<th>Owner</th>' +
					'<th>Permissions</th>' +
					'<th>Size</th>' +
					'</tr>' +
					'</thead>');

				$.each(files, function () {
					var file = this;
					var listingItem = $('<tr></tr>')
						.append('<td><span class="ui-filemanager-icon ui-filemanager-icon-18 ' + file.type.icon + '"></span></td>')
						.append('<td data-fullname="' + file.name + '"><span class="fileName">' + file.name + '</span></td>')
						.append('<td>' + file.modified + '</td>')
						.append('<td>' + file.type.name + '</td>')
						.append('<td>' + file.owner.name + '</td>')
						.append('<td align="center">' + file.permissions + '</td>')
						.append('<td align="right">' + file.size.string + '</td>');

					listingItem.attr('data-file_path', file.path.absolute).data('fileInfo', file);

					listingTbody.append(listingItem);
				});

				listingEl.append(listingTbody);

				return listingEl;
			},
			fixElements : function () {
				var fileListing = $('#fileListing');
				fileListing.find('th').each(function (i, item) {
					$(this).width(fileListing.find('tr').last().find('td:eq(' + i + ')').width());
				});
			},
			onLoad : function (appendToEl) {
				if (this.isWindow === true){
					appendToEl.find('thead').css({
						position : 'fixed',
						top : $('.ui-filemanager-toolbar').first().height()
					});
					$('#fileListing').css('padding-top', appendToEl.find('thead > tr').height() + 'px');
					this._fixElements();
				}
			},
			onUnload : function () {
				$('#fileListing').css('margin-top', '0px');
				this._fixElements();
			}
		}
	});

	$.extend($.ui.filemanager.prototype.listingDecorators, {
		icons : {
			listItemSelector : '.ui-filemanager-listing-icons li',
			supportsKeyDown : function (keyName) {
				return true;
			},
			getKeyDownSelections : function (keyName) {
				switch(keyName){
					case 'arrowLeft':
						return -1;
						break;
					case 'arrowRight':
						return 1;
						break;
					case 'arrowUp':
						return -(this._getNumOfItemsInRow());
						break;
					case 'arrowDown':
						return this._getNumOfItemsInRow();
						break;
				}
				return 0;
			},
			init : function (mngrClass) {
				var files = mngrClass.data;
				var listingEl = $('<ul></ul>')
					.addClass('ui-filemanager-listing-icons');

				$.each(files, function () {
					var file = this;
					var fileName = file.name;

					var listingItem = $('<li></li>');
					if (/image/i.test(file.type.mime)){
						listingItem.append('<img src="' + file.path.relative + '" width="100" height="100"><br><span class="fileName" data-fullname="' + file.name + '">' + fileName + '</span>');
					}
					else {
						listingItem.append('<span class="ui-filemanager-icon ui-filemanager-icon-128 ' + file.type.icon + '"></span><br><span class="fileName" data-fullname="' + file.name + '">' + fileName + '</span>');
					}

					listingItem.attr('data-file_path', file.path.absolute).data('fileInfo', file);

					listingEl.append(listingItem);
				});

				return listingEl;
			},
			_getNumOfItemsInRow : function () {
				var currentItem = $('#fileListing').find('li').first();
				var positionTopCheck = currentItem.position().top;
				var numOfItems = 1;
				while(true){
					currentItem = currentItem.next();
					if (positionTopCheck == currentItem.position().top){
						numOfItems++;
					}
					else {
						break;
					}
				}
				return numOfItems;
			},
			onLoad : function (appendToEl) {
				var widest = 0;
				var tallest = 0;
				appendToEl.find('li').each(function () {
					if ($(this).width() > widest){
						widest = $(this).width();
					}
					if ($(this).height() > tallest){
						tallest = $(this).height();
					}
				});
				appendToEl.find('li').css({
					height : tallest + 'px',
					width : widest + 'px'
				});
			}
		}
	});

	$.extend($.ui.filemanager.prototype.listingDecorators, {
		list : {
			listItemSelector : '.ui-filemanager-listing-list tbody tr',
			supportsKeyDown : function (keyName) {
				return (keyName == 'arrowLeft' || keyName == 'arrowRight' ? false : true);
			},
			getKeyDownSelections : function (keyName) {
				switch(keyName){
					case 'arrowUp':
						return -1;
						break;
					case 'arrowDown':
						return 1;
						break;
				}
				return 0;
			},
			init : function (mngrClass) {
				var files = mngrClass.data;
				var listingEl = $('<table cellspacing="0" width="100%"></table>')
					.addClass('ui-filemanager-listing-list');
				var listingTbody = $('<tbody></tbody>');

				$.each(files, function () {
					var file = this;
					var listingImage = '';
					if (/image/i.test(file.type.mime)){
						listingImage = '<img src="' + file.path.relative + '" width="32" height="32">';
					}
					else {
						listingImage = '<span class="ui-filemanager-icon ui-filemanager-icon-32 ' + file.type.icon + '"></span>';
					}
					var listingItem = $('<tr></tr>')
						.append('<td>' + listingImage + '</td>')
						.append('<td data-fullname="' + file.name + '"><span class="fileName">' + file.name + '</span></td>')
						.append('<td>Type: ' + file.type.name + '<br>Dimensions: ' + file.dimensions + '<br>Permissions: ' + file.permissions + '<br>Owner: ' + file.owner.name + '</td>')
						.append('<td>Size: ' + file.size.string + '</td>');

					listingItem.find('td')
						.attr('valign', 'top');

					listingItem.attr('data-file_path', file.path.absolute).data('fileInfo', file);

					listingTbody.append(listingItem);
				});

				listingEl.append(listingTbody);

				return listingEl;
			}
		}
	});

	$.extend($.ui.filemanager.prototype.listingDecorators, {
		tile : {
			listItemSelector : '.ui-filemanager-listing-tile li',
			supportsKeyDown : function (keyName) {
				return true;
			},
			getKeyDownSelections : function (keyName) {
				switch(keyName){
					case 'arrowLeft':
						return -1;
						break;
					case 'arrowRight':
						return 1;
						break;
					case 'arrowUp':
						return -(this._getNumOfItemsInRow());
						break;
					case 'arrowDown':
						return this._getNumOfItemsInRow();
						break;
				}
				return 0;
			},
			init : function (mngrClass) {
				var files = mngrClass.data;
				var listingEl = $('<ul></ul>')
					.addClass('ui-filemanager-listing-tile');

				$.each(files, function () {
					var file = this;
					var listingImage = '';
					if (/image/i.test(file.type.mime)){
						listingImage = '<img src="' + file.path.relative + '" width="32" height="32">';
					}
					else {
						listingImage = '<span class="ui-filemanager-icon ui-filemanager-icon-32 ' + file.type.icon + '"></span>';
					}
					var listingItem = $('<li></li>')
						.append('<table cellspacing="0" cellpadding="1">' +
						'<tr>' +
						'<td style="border:none;">' + listingImage + '</td>' +
						'<td style="border:none;"><table cellspacing="0" cellpadding="1">' +
						'<tr>' +
						'<td style="border:none;" data-fullname="' + file.name + '"><span class="fileName">' + file.name + '</span></td>' +
						'</tr>' +
						'<tr>' +
						'<td style="border:none;">' + file.type.name + '</td>' +
						'</tr>' +
						'<tr>' +
						'<td style="border:none;">' + file.size.string + '</td>' +
						'</tr>' +
						'<tr>' +
						'<td style="border:none;">' + file.permissions + '</td>' +
						'</tr>' +
						'</table></td>' +
						'</tr>' +
						'</table>');

					listingItem.attr('data-file_path', file.path.absolute).data('fileInfo', file);

					listingEl.append(listingItem);
				});

				return listingEl;
			},
			_getNumOfItemsInRow : function () {
				var currentItem = $('#fileListing').find('li').first();
				var positionTopCheck = currentItem.position().top;
				var numOfItems = 1;
				while(true){
					currentItem = currentItem.next();
					if (positionTopCheck == currentItem.position().top){
						numOfItems++;
					}
					else {
						break;
					}
				}
				return numOfItems;
			},
			onLoad : function (appendToEl) {
				var widest = 0;
				var tallest = 0;
				appendToEl.find('li').each(function () {
					if ($(this).width() > widest){
						widest = $(this).width();
					}
					if ($(this).height() > tallest){
						tallest = $(this).height();
					}
				});
				appendToEl.find('li').css({
					height : tallest + 'px',
					width : widest + 'px'
				});
			}
		}
	});
}(jQuery));
