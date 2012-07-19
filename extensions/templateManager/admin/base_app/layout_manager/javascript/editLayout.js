/* construct.js */

/* Standard elements that are created on actions */
var wrapperEl = '<div class="container wrapper needsMin"></div>';
var containerEl = '<div class="container needsMin"></div>';
var columnEl = '<div class="column needsMin"></div>';
var listEl = '<ul class="sortableList needsMin"></ul>';
var widgetEl = '<li class="widget"><span class="widgetName"></span></li>';
var selectedClass = 'state-active';
var hoverClass = 'state-hover';
var highlightClass = 'state-highlight';
var selectedOpacity = 1;
var unselectedOpacity = .8;
var Designer;

function getTemplateName() { return templateName; }
function getLayoutId() { return layoutId; }

function getParams(addParams) {
	var getVars = [];
	getVars.push('appExt=templateManager');
	getVars.push('app=layout_manager');
	getVars.push('appPage=editLayout');
	getVars.push('pageType=' + pageType);
	getVars.push('layout_id=' + layoutId);
	getVars.push('rType=ajax');
	getVars.push('showErrors=true');

	if (addParams){
		$.each(addParams, function () {
			getVars.push(this);
		});
	}

	return getVars.join('&');
}

function showWidgetEditWindow(o) {
	$('#widgetsForm').show();
	$('.boxListing').fadeOut(500, function () {
		$('.editWindow').html(o.htmlTable).fadeIn(500, function () {
			$(this).find('button').button();

			if (o.onShow){
				o.onShow.apply(this);
			}

			if (o.onHide){
				$(this).unbind('onHide').bind('onHide', o.onHide);
			}
		});
	});
}

function hideWidgetEditWindow() {
	var self = this;
	$(self).fadeOut(250, function () {
		$(self).trigger('onHide');
		$(self).empty();
		$('.boxListing').fadeIn(250, function () {
			$('#widgetsForm').hide();
		});
	});
}

function addWidget(draggable, ul) {
	var widgetCode = $(draggable).attr('id');

	var $listItem = $(widgetEl);
	$listItem.attr('tmid', new Date().getTime());
	$listItem.data('widget_code', widgetCode);
	$listItem.data('widget_settings', {});
	$listItem.data('styles', {});
	$listItem.data('inputs', {});
	$listItem.find('.widgetName').html(widgetCode);
	$listItem.appendTo(ul);

	$listItem.parentsUntil('#construct').each(function (){
		$(this).removeClass('needsMin');
	});

	showSaveLayout();
}

function editWidget(li) {
	var widgetEl = $(li);
	var ajaxLoaderEl = widgetEl.parent().parent();
	var isLinkedWidget = (widgetEl.parentsUntil('#construct').last().data('link_id') > 0);

	$.ajax({
		cache: false,
		url: js_app_link(getParams([
			'action=editWidget',
			'widgetCode=' + widgetEl.data('widget_code')
		])),
		dataType: 'html',
		type: 'post',
		data: 'widgetSettings=' + encodeURIComponent(JSON.stringify(widgetEl.data('widget_settings'))),
		beforeSend: function () {
			showAjaxLoader(ajaxLoaderEl, 'large');
		},
		complete: function () {
			removeAjaxLoader(ajaxLoaderEl);
		},
		success: function (data) {
			showWidgetEditWindow({
				htmlTable: data,
				onHide: function (){
					$(this).find('.makeHtmlEditor:hidden').each(function (){
						$(this).ckeditorGet().destroy();
					});
				},
				onShow: function () {
					var self = this;
					$(self).find('.BrowseServerField, .fileManagerInput').filemanager({
						fileSource: jsConfig.get('DIR_FS_CATALOG_TEMPLATES') + getTemplateName()
					});

					$(self).find('.makeHtmlEditor').each(function (){
						if ($(this).hasClass('simple')){
							$(this).ckeditor({
								toolbar: 'Simple'
							});
						}else if ($(this).hasClass('basic')){
							$(this).ckeditor({
								toolbar: 'Basic'
							});
						}else{
							$(this).ckeditor({});
						}
					});
					$(self).find('.makeTabs').tabs();

					$(self).find('.cancelButton').click(function () {
						hideWidgetEditWindow.apply(self);
					});

					$(self).find('.saveButton').click(function () {
						var buttonEl = $(this);
						var urlParams = ['widgetCode=' + widgetEl.data('widget_code')];
						if (isLinkedWidget === true){
							urlParams.push('widgetId=' + widgetEl.data('widget_id'));
							urlParams.push('action=saveLinkedContainerWidget');
						}else{
							urlParams.push('action=saveWidget');
						}
						$.ajax({
							cache: false,
							url: js_app_link(getParams(urlParams)),
							dataType: 'json',
							data: $('.editWindow').find('input, textarea, select').serialize(),
							type: 'post',
							beforeSend: function () {
								showAjaxLoader(buttonEl, 'small');
							},
							complete: function () {
								removeAjaxLoader(buttonEl, 'small');
							},
							success: function (data) {
								widgetEl.data('widget_settings', data.widgetSettings);
								if (data.widgetPreview && data.widgetPreview !== false){
									widgetEl.find('.widgetName').html(data.widgetPreview);
								}
								hideWidgetEditWindow.apply(self);
								if (isLinkedWidget === false){
									showSaveLayout();
								}else{
									liveMessage('Linked Container Widget Has Been Saved')
								}
							}
						});
					});

					if (typeof WindowOnShow == 'function'){
						WindowOnShow.apply(self);
					}
				}
			});
		}
	});
}

function runLayoutAction(action) {
	var $Column = $('#construct');
	var rType = 'post';

	var getVars = [];
	getVars.push('appExt=templateManager');
	getVars.push('app=layout_manager');
	getVars.push('appPage=editLayout');
	getVars.push('rType=ajax');

	if (action == 'save'){
		getVars.push('layout_id=' + getLayoutId());
		getVars.push('action=saveLayout');
	}

	if (rType == 'post'){
		var postVars = [];
		postVars.push($('#templatePages *').serialize());
		postVars.push('templateData=' + escape(getMarkup()));
	}

	showAjaxLoader($Column, 'xlarge');
	$.ajax({
		cache: false,
		url: js_app_link(getVars.join('&')),
		dataType: 'json',//add pages to post
		type: rType,
		data: (rType == 'post' ? postVars.join('&') : null),
		success: function (data) {
			removeAjaxLoader($Column);
			if (action == 'save'){
				$.each(data.newElementInfo, function (type, tInfo){
					$.each(tInfo, function (tmId, elId){
						if (type == 'containers'){
							$('.container[tmid=' + tmId + ']').removeAttr('tmid').data('container_id', elId);
						}else if (type == 'columns'){
							$('.column[tmid=' + tmId + ']').removeAttr('tmid').data('column_id', elId);
						}else if (type == 'widgets'){
							$('.widget[tmid=' + tmId + ']').removeAttr('tmid').data('widget_id', elId);
						}
					});
				});
				hideSaveLayout();
			}
		}
	});
}

function containerAdd() {
	// append a new container to the #construct space
	var $newContainer = $(containerEl);
	$newContainer.attr('tmid', new Date().getTime());
	var sortOrder = 1;
	$('#construct > div').each(function (){
		$(this).attr('data-sort_order', sortOrder);
		sortOrder++;
	});

	$('#construct').append($newContainer);

	setupContainer($newContainer, false);
	$newContainer.trigger('click');
	showSaveLayout();
	$('#construct-addColumn').trigger('click');

	return false;
}

function columnAdd() {
	if ($(this).hasClass('ui-state-disabled')){
		return false;
	}

	var $parentContainer = $('.container.' + selectedClass).not('.wrapper');
	if ($parentContainer.parentsUntil('#construct').last().data('link_id')){
		return false;
	}

	var newColumnList = $(listEl);
	var $newColumn = $(columnEl).append(newColumnList);
	$newColumn.attr('tmid', new Date().getTime());
	var sortOrder = 1;
	$parentContainer.find('.column').each(function (){
		$(this).attr('data-sort_order', sortOrder);
		sortOrder++;
	});

	$parentContainer.append($newColumn);

	setupColumn($newColumn);
	$newColumn.trigger('click');

	showSaveLayout();

	return false;
}

function subColumnAdd(){
	if ($(this).hasClass('ui-state-disabled')){
		return false;
	}

	// if no selected container, just cancel
	if (!$(this).data('current_column')){
		return false;
	}

	var $parentContainer = $($(this).data('current_column'));
	if ($parentContainer.parentsUntil('#construct').last().data('link_id')){
		return false;
	}

	if ($parentContainer.find('.ui-sortable li').size() > 0){
		var response = confirm('Adding a column will remove all widgets currently in the column, are you sure?');
		if (!response){
			return false;
		}
	}

	$parentContainer.children('.ui-sortable:first').sortable('destroy').remove();
	$parentContainer.droppable('disable');

	var newColumnList = $(listEl);
	var $newColumn = $(columnEl).append(newColumnList);
	$newColumn.attr('tmid', new Date().getTime());
	var sortOrder = 1;
	$parentContainer.children('.column:first').each(function (){
		$(this).attr('data-sort_order', sortOrder);
		sortOrder++;
	});

	$parentContainer.append($newColumn);

	setupColumn($newColumn);
	$newColumn.trigger('click');

	showSaveLayout();

	return false;
}

function deleteElement(el, withChildren) {
	withChildren = withChildren || false;
	var $el = $(el);

	if ($el.hasClass('wrapper')){
		if (withChildren === true){
			$el.children().each(function () {
				deleteElement($(this));
			});
		}
		else {
			if ($el.parent().attr('id') == 'construct'){
				$($el.html()).insertAfter($el);
			}
			else {
				$el.parent().append($el.html());
			}
		}
		$el.next('.container').trigger('click');
		$el.remove();
		showSaveLayout();
	}
	else if ($el.hasClass('container')){
		if ($el.next().is('hr')){
			$el.next().remove();
		}

		$el.next('.container').trigger('click');
		$el.remove();

		if ($('.container.' + selectedClass).size() < 1){
			// we deleted the last container, so now select the "new" last container
			$('.container:last').trigger('click');
		}
		showSaveLayout();
	}
	else if ($el.hasClass('column')){
		if ($el.next('.column').size() == 0){
			$el.parent().addClass('needsMin');
		}else{
			// remove the selected column from the DOM and select the next one
			$el.next('.column').trigger('click');
		}
		$el.remove();

		if ($('.container.' + selectedClass + ' .column.' + selectedClass).size() < 1){
			// we deleted the last column, so now select the "new" last column and make it the last one
			$('.container.' + selectedClass + ' .column:last').trigger('click');
		}
		showSaveLayout();
	}
	else if ($el.hasClass('widget')){
		$el.parent().addClass('needsMin');
		$el.remove();
		updateBreadcrumb();
		showSaveLayout();
	}
}

function setupContainer($container, isWrapper) {
	isWrapper = isWrapper || false;

	if (isWrapper === false){
		if ($container.data('link_id') || $container.parentsUntil('#construct').last().data('link_id')){
		}else{
			$container.droppable({
				accept: function (el) {
					if ($(el).hasClass('column')){
						return true;
					}
					return false;
				},
				hoverClass: highlightClass,
				drop: function (e, ui) {
					$(this).append(ui.draggable);
					showSaveLayout();
				}
			});
		}
	}

	if ($container.attr('data-styles')){
		$container.data('styles', $.parseJSON(htmlspecialchars_decode($container.attr('data-styles'))));
		$container.removeAttr('data-styles');
	}else if (!$container.data('styles')){
		$container.data('styles', { });
	}

	if ($container.attr('data-inputs')){
		$container.data('inputs', $.parseJSON(htmlspecialchars_decode($container.attr('data-inputs'))));
		$container.removeAttr('data-inputs');
	}else if (!$container.data('inputs')){
		$container.data('inputs', { });
	}

	if (!$container.hasClass(selectedClass)){
		$container.fadeTo(0, unselectedOpacity);
	}

	if ($container.html() == ''){
		$container.addClass('needsMin');
	}else{
		$container.removeClass('needsMin');
	}
	$container.each(function (){
		//$(this).removeAttr('data-container_id');
		//$(this).removeAttr('data-styles');
		//$(this).removeAttr('data-inputs');
		//$(this).removeAttr('data-sort_order');
	});

	//$container.disableSelection();
}

function setupColumn($column) {
	if ($column.parentsUntil('#construct').last().data('link_id')){
		$('ul', $column).sortable({
			disabled: true
		});
	}else{
		if ($column.parent().hasClass('ui-droppable') && $column.parent().hasClass('column')){
			$column.parent().droppable('disable');
		}
		$column.droppable({
			accept: function (el) {
				if ($(el).hasClass('draggableField')){
					return true;
				}
				return false;
			},
			hoverClass: highlightClass,
			drop: function (e, ui) {
				if ($(ui.draggable).hasClass('draggableField')){
					addWidget(ui.draggable, $(this).children('.ui-sortable'));
				}
				showSaveLayout();
			}
		});

		$('ul', $column).sortable({
			helper: 'clone',
			containment: $('#construct'),
			connectWith: '.ui-sortable',
			forceHelperSize: true,
			forcePlaceholderSize: true,
			tolerance: 'pointer',
			placeholder: highlightClass,
			cursor: 'move',
			items: 'li',
			opacity: .5,
			revert: true,
			update: function (e, ui) {
				showSaveLayout();
			},
			recieve: function (e, ui) {
				alert('CHECKING');
			}
		});
	}

	if ($column.attr('data-styles')){
		$column.data('styles', $.parseJSON(htmlspecialchars_decode($column.attr('data-styles'))));
		$column.removeAttr('data-styles');
	}else if (!$column.data('styles')){
		$column.data('styles', { });
	}

	if ($column.attr('data-inputs')){
		$column.data('inputs', $.parseJSON(htmlspecialchars_decode($column.attr('data-inputs'))));
		$column.removeAttr('data-inputs');
	}else if (!$column.data('inputs')){
		$column.data('inputs', { });
	}

	if (!$column.hasClass(selectedClass)){
		$column.fadeTo(0, unselectedOpacity);
	}

	if ($column.html() == ''){
		$column.addClass('needsMin');
	}else{
		$column.removeClass('needsMin');
	}

	$column.find('.ui-sortable').each(function (){
		if ($(this).html() == ''){
			$(this).addClass('needsMin');
		}else{
			$(this).removeClass('needsMin');
		}
	});

	$column.find('.widget').each(function (){
		if ($(this).attr('data-widget_settings')){
			$(this).data('widget_settings', $.parseJSON(htmlspecialchars_decode($(this).attr('data-widget_settings'))));
			$(this).removeAttr('data-widget_settings');
		}else if (!$(this).data('widget_settings')){
			$(this).data('widget_settings', { });
		}

		if ($(this).attr('data-styles')){
			$(this).data('styles', $.parseJSON(htmlspecialchars_decode($(this).attr('data-styles'))));
			$(this).removeAttr('data-styles');
		}else if (!$(this).data('styles')){
			$(this).data('styles', { });
		}

		if ($(this).attr('data-inputs')){
			$(this).data('inputs', $.parseJSON(htmlspecialchars_decode($(this).attr('data-inputs'))));
			$(this).removeAttr('data-inputs');
		}else if (!$(this).data('inputs')){
			$(this).data('inputs', { });
		}

		if ($(this).html() == ''){
			$(this).addClass('needsMin');
		}else{
			$(this).removeClass('needsMin');
		}
	});

	//$column.disableSelection();
}

function setupDraggableFields() {
	$('.draggableField').each(function () {
		$(this).draggable({
			revert: 'invalid',
			scroll: false,
			containment: 'document',
			helper: 'clone',
			opacity: .5
		});
	});
}
/* Bind events for Construct interface */


function showAdjustWindow(elBeingAdjusted, insertAfterEl) {
	if ($('.adjustPopup').size() > 0){
		if ($('.adjustPopup').data('targetElement') == elBeingAdjusted){
			$('.adjustPopup .closeWindow').trigger('click');
			return;
		}
		else {
			$('.adjustPopup .closeWindow').trigger('click');
		}
	}

	var $adjustPopup = $('<div class="adjustPopup ui-widget ui-widget-content ui-corner-all"></div>')
		.css({ display: 'block', position: 'relative' })
		.data('targetElement', elBeingAdjusted)
		.append('<span class="ui-icon ui-icon-closethick closeWindow"></span>')
		.append($('#elementProperties').html())
		.insertAfter(insertAfterEl);


	$adjustPopup.LayoutDesigner({
		curElement: elBeingAdjusted
	});
}



function updateBackgroundImage() {
	if ($('.adjustPopup').find('select[name=background_type]').val() != 'image'){
		return;
	}

	var Color = $('.adjustPopup').find('input[name=background_color]').val();
	var Image = $('.adjustPopup').find('input[name=background_image]').val();
	var Repeat = $('.adjustPopup').find('select[name=background_repeat]').val();
	var Position_y = $('.adjustPopup').find('input[name=background_position_y]').val();
	var Position_x = $('.adjustPopup').find('input[name=background_position_x]').val();

	var adjustTargetElement = $('.adjustPopup').data('targetElement');
	adjustTargetElement.css({
		backgroundColor: Color,
		backgroundImage: 'url(' + Image + ')',
		backgroundRepeat: Repeat,
		backgroundPosition: Position_y + '% ' + Position_x + '%'
	});
	updateStylesInfo(adjustTargetElement, 'styles', 'background', Color + ' ' + 'url(' + Image + ') ' + Repeat + ' ' + Position_y + '% ' + Position_x + '%');
}

function updateBreadcrumb() {
	if ($('#construct').find('.' + selectedClass).first().data('link_id')){
		$('#construct-link').addClass('ui-state-disabled');
	}else{
		$('#construct-link').removeClass('ui-state-disabled');
	}
	var isLinkElement = ($('#construct').find('.' + selectedClass).first().data('link_id') ? true : false);

	var $bodyIcons = $('<span></span>').addClass('iconBlock');
	$bodyIcons.append('<span class="ui-icon ui-icon-comment showContainerData" tooltip="Show Element Data"></span>');
	$bodyIcons.append('<span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Element"></span>');
	var bodyBlock = $('<span><b>Body</b></span>').addClass('elementBlock').append($bodyIcons);

	$('.containerBreadcrumb').empty().append(bodyBlock);
	var El = $('#construct');
	$bodyIcons.find('.ui-icon').each(function () {
		$(this).data('element', El);
	});

	$('#construct').find('.' + selectedClass).each(function () {
		var $elementBlock = $('<span></span>').addClass('elementBlock');
		var $icons = $('<span></span>').addClass('iconBlock');

		if ($(this).hasClass('column')){
			$icons.append('<span class="ui-icon ui-icon-comment showColumnData" tooltip="Show Element Data"></span>');
		}else if ($(this).hasClass('widget')){
			$icons.append('<span class="ui-icon ui-icon-comment showWidgetContainerData" tooltip="Show Element Data"></span>');
			$icons.append('<span class="ui-icon ui-icon-wrench configureWidget" tooltip="Configure Widget"></span>');
			if (isLinkElement === false){
				$icons.append('<span class="ui-icon ui-icon-link linkWidget" tooltip="Create Linked Widget"></span>');
			}
		}else{
			$icons.append('<span class="ui-icon ui-icon-comment showContainerData" tooltip="Show Element Data"></span>');
		}

		if ($(this).hasClass('wrapper')){
			if (isLinkElement === false){
				$icons
					.append('<span class="ui-icon ui-icon-arrowreturnthick-1-w removeWrapper" tooltip="Remove Wrapper Element"></span>');
			}
		}
		else {
			if (!$(this).hasClass('column') && !$(this).hasClass('widget')){
				if (isLinkElement === false){
					$icons.append('<span class="ui-icon ui-icon-newwin wrapElement" tooltip="Wrap Element"></span>');
				}
				$icons
					.append('<span class="ui-icon ui-icon-arrowthick-1-n moveContainerUp" tooltip="Move Container And Wrappers Up"></span>');
				$icons
					.append('<span class="ui-icon ui-icon-arrowthick-1-s moveContainerDown" tooltip="Move Container And Wrappers Down"></span>');
			}
			if ($(this).hasClass('column')){
				$icons
					.append('<span class="ui-icon ui-icon-arrowthick-1-e moveColumnLeft" tooltip="Move Column And Widgets Left"></span>');
				$icons
					.append('<span class="ui-icon ui-icon-arrowthick-1-w moveColumnRight" tooltip="Move Column And Widgets Right"></span>');
			}
		}
		if (isLinkElement === false){
			$icons.append('<span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Styles"></span>');
			$icons
				.append('<span class="ui-icon ui-icon-closethick deleteElement" tooltip="Delete Element And Children"></span>');
		}
		else if (isLinkElement === true && $(this).parent().attr('id') == 'construct'){
			$icons
				.append('<span class="ui-icon ui-icon-closethick deleteElement" tooltip="Delete Element And Children"></span>');
		}

		var typeText = '';
		if ($(this).hasClass('wrapper')){
			typeText = 'Wrapper';
		}else if ($(this).hasClass('container')){
			typeText = 'Container';
		}else if ($(this).hasClass('column')){
			typeText = 'Column';
		}else if ($(this).hasClass('widget')){
			typeText = 'Widget';
		}

		var El = $(this);
		var typeLink = $('<a href="Javascript:void(0)"></a>')
			.html(typeText)
			.data('element', El)
			.click(function (){
				$(this).data('element').trigger('click');
			});
		$elementBlock.append(typeLink);

		if ($(this).hasClass('widget')){
			$elementBlock.append('<span> ( ' + $(this).data('widget_code') + ' )</span>');
		}

		if ($(this).attr('id')){
			$elementBlock.append(' ( <small>' + $(this).attr('id') + '</small> ) ');
		}
		$elementBlock.append($icons);

		if ($(this).data('is_table') === true){
			var table = $('<table border="1" width="100%">' +
				'<tr>' +
				'<td></td>' +
				'<td align="center">Element</td>' +
				'<td align="center">Row</td>' +
				'<td align="center">Column</td>' +
				'</tr>' +
				'<tr>' +
				'<td align="center">Table</td>' +
				'<td align="center"><span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Styles" data-selector="table"></span></td>' +
				'<td align="center"></td>' +
				'<td align="center"></td>' +
				'</tr>' +
				'<tr>' +
				'<td align="center">Header</td>' +
				'<td align="center"><!--<span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Styles" data-selector="thead"></span>--></td>' +
				'<td align="center"><!--<span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Styles" data-selector="thead tr">--></span></td>' +
				'<td align="center"><span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Styles" data-selector="thead tr th"></span></td>' +
				'</tr>' +
				'<tr>' +
				'<td align="center">Body</td>' +
				'<td align="center"><!--<span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Styles" data-selector="tbody"></span>--></td>' +
				'<td align="center"><!--<span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Styles" data-selector="tbody tr"></span>--></td>' +
				'<td align="center"><span class="ui-icon ui-icon-pencil editElement" tooltip="Edit Styles" data-selector="tbody tr td"></span></td>' +
				'</tr>' +
				'</table>');

			$elementBlock.append(table);
			table.find('.ui-icon').each(function () {
				$(this).data('element', El.find($(this).data('selector')));
			});
		}

		$('.containerBreadcrumb').append('<span class="crumbSeparator"> &raquo; </span>').append($elementBlock);
		$icons.find('.ui-icon').each(function () {
			$(this).data('element', El);
		});
	});
}

function rgb2hex(rgb) {
	if (rgb.length > 0){
		rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
		function hex(x) {
			return ("0" + parseInt(x).toString(16)).slice(-2);
		}

		return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
	}
}

function getLastParent($el) {
	if ($el.parent().attr('id') == 'construct'){
		var Container = $el;
	}
	else {
		var Container = $el.parentsUntil('#construct').last();
	}
	return Container;
}

function showSaveLayout() {
	$('#construct-save').removeClass('ui-state-disabled');
	setConfirmUnload(true);
}

function hideSaveLayout() {
	$('#construct-save').addClass('ui-state-disabled');
	setConfirmUnload(false);
}

function setConfirmUnload(on) {
	window.onbeforeunload = (on) ? unloadMessage : null;
}

function unloadMessage() {
	return 'The Layout Has Been Changed. If you navigate away from this page without first saving your data, the changes will be lost.';
}

$(document).ready(function() {
	$('#construct').each(function (){
		if ($(this).attr('data-styles')){
			$(this).data('styles', $.parseJSON(htmlspecialchars_decode($(this).attr('data-styles'))));
			$(this).removeAttr('data-styles');
		}else if (!$(this).data('styles')){
			$(this).data('styles', { });
		}

		if ($(this).attr('data-inputs')){
			$(this).data('inputs', $.parseJSON(htmlspecialchars_decode($(this).attr('data-inputs'))));
			$(this).removeAttr('data-inputs');
		}else if (!$(this).data('inputs')){
			$(this).data('inputs', { });
		}
	});

	$('#construct-addContainer').click(containerAdd);
	$('#construct-addColumn').click(columnAdd);
	$('#construct-addSubColumn').click(subColumnAdd);

	$('#construct-widgets').click(function() {
		if ($('#widgetsForm').css('display') != 'none'){
			$('#widgetsForm').hide();
		}
		else {
			$('#widgetsForm').show();
		}
		return false;
	});

	$('#hideWidgets').click(function() {
		if ($('.editWindow').find('.ui-dialog').size() > 0){
			hideWidgetEditWindow.apply($('.editWindow'));
		}
		else {
			$('#widgetsForm').hide();
		}
	});

	$('#construct-save').click(function(e) {
		e.preventDefault();
		runLayoutAction('save');
	});
	$('#noSaveLayout').click(function () { hideSaveLayout(); });

	/**
	 * Used to reset variables that are required due to event bubbling
	 */
	var selectedCleared = false;
	var isFirstClick = true;
	$('#construct').click(function (){
		selectedCleared = false;
		isFirstClick = true;
	});
	$('#construct').on({
		mouseover: function (e){
			if (($(this).hasClass('widget') || $(this).hasClass('column')) && !$(this).parentsUntil('#construct').last().data('link_id')){
				this.style.cursor = 'move';
			}
			$(this).addClass(hoverClass);
		},
		mouseout: function (e){
			if (($(this).hasClass('widget') || $(this).hasClass('column')) && !$(this).parentsUntil('#construct').last().data('link_id')){
				this.style.cursor = 'default';
			}
			$(this).removeClass(hoverClass);
		},
		click: function (e){
			if (selectedCleared === false){
				$('.container.' + selectedClass).fadeTo(0, unselectedOpacity).removeClass(selectedClass);
				$('.column.' + selectedClass).fadeTo(0, unselectedOpacity).removeClass(selectedClass);
				$('.widget.' + selectedClass).removeClass(selectedClass);
				selectedCleared = true;
			}

			if ($('.adjustPopup').size() > 0){
				$('.adjustPopup').find('.closeAdjustPopup').trigger('click');
			}

			$(this).removeClass(hoverClass).addClass(selectedClass).fadeTo(0, selectedOpacity);
			$('#construct-addColumn, #construct-addSubColumn').removeClass('ui-state-disabled');

			if (isFirstClick === true && $(this).hasClass('column')){
				$('#construct-addSubColumn').data('current_column', this);
				isFirstClick = false;
			}
			updateBreadcrumb();
		}
	}, '.container, .column, li.widget');

	$('.configureWidget').live('click', function (e) {
		if (!$(this).hasClass('editElement')){
			editWidget($(this).data('element'));
		}
	});

	$('.linkWidget').live('click', function (e) {
		e.preventDefault();
		var newWidget = $(this).data('element').clone(true);
		newWidget.insertAfter($(this).data('element'));
		newWidget.data('widget_settings', { "linked_to": newWidget.data('widget_id') });
		newWidget.attr('data-widget_settings', JSON.stringify({ "linked_to": newWidget.data('widget_id') }));
		newWidget.removeAttr('data-widget_id');
		newWidget.data('widget_id', '');
		newWidget.find('.ui-icon-link').remove();
		showSaveLayout();
	});

	$('.showWidgetData').live('click', function (){
		var El = $(this).parent().parent();
		$('<div title="Widget Data"></div>').html('<pre>' +
			'<b><u>Widget Id</u></b> :: ' + El.data('widget_id') + '<br>' +
			'<b><u>Widget Code</u></b> :: ' + El.data('widget_code') + '<br>' +
			'<b><u>Sort Order</u></b> :: ' + El.data('sort_order') + '<br>' +
			'<b><u>Widget Settings</u></b> :: ' + JSON.stringify(El.data('widget_settings'), null, '\t') + '<br>' +
			'</pre>')
			.dialog({
			            height: 500,
			            width: 600
			        });
	});

	$('.showContainerData').live('click', function (){
		var El = $(this).data('element');
		$('<div title="Container Data"></div>').html('<pre>' +
			'<b><u>Container Id</u></b> :: ' + El.data('container_id') + '<br>' +
			'<b><u>Sort Order</u></b> :: ' + El.data('sort_order') + '<br>' +
			'<b><u>Styles</u></b> :: ' + JSON.stringify(El.data('styles'), null, '\t') + '<br>' +
			'<b><u>Inputs</u></b> :: ' + JSON.stringify(El.data('inputs'), null, '\t') + '<br>' +
			'</pre>')
			.dialog({
			            height: 500,
			            width: 600
			        });
	});

	$('.showColumnData').live('click', function (){
		var El = $(this).data('element');
		$('<div title="Column Data"></div>').html('<pre>' +
			'<b><u>Column Id</u></b> :: ' + El.data('column_id') + '<br>' +
			'<b><u>Sort Order</u></b> :: ' + El.data('sort_order') + '<br>' +
			'<b><u>Styles</u></b> :: ' + JSON.stringify(El.data('styles'), null, '\t') + '<br>' +
			'<b><u>Inputs</u></b> :: ' + JSON.stringify(El.data('inputs'), null, '\t') + '<br>' +
			'</pre>')
			.dialog({
				height: 500,
				width: 600
			});
	});

	$('.showWidgetContainerData').live('click', function (){
		var El = $(this).data('element');

		$('<div title="Widget Data"></div>').html('<pre>' +
			'<b><u>Widget Id</u></b> :: ' + El.data('widget_id') + '<br>' +
			'<b><u>Sort Order</u></b> :: ' + El.data('sort_order') + '<br>' +
			'<b><u>Widget Settings</u></b> :: ' + JSON.stringify(El.data('widget_settings'), null, '\t') + '<br>' +
			'<b><u>Styles</u></b> :: ' + JSON.stringify(El.data('styles'), null, '\t') + '<br>' +
			'<b><u>Inputs</u></b> :: ' + JSON.stringify(El.data('inputs'), null, '\t') + '<br>' +
			'</pre>')
			.dialog({
				height: 500,
				width: 600
			});
	});

	$('.draggableField').live('mouseover mouseout', function (e) {
		switch(e.type){
			case 'mouseover':
				this.style.cursor = 'move';
				$(this).addClass(highlightClass);
				break;
			case 'mouseout':
				this.style.cursor = 'default';
				$(this).removeClass(highlightClass);
				break;
		}
	});

	//$('#construct-header').stickyBar();

	$('input[name=equal_heights]').live('click', function () {
		var adjustTargetElement = $('.adjustPopup').data('targetElement');
		if (this.checked){
			adjustTargetElement.addClass('equalHeights');
		}
		else {
			adjustTargetElement.removeClass('equalHeights');
		}
	});

	$('.adjustPopup .closeAdjustPopup').live('click', function () {
		$('.adjustPopup').remove();
		showSaveLayout();
	});

	$('.deleteElement').live('click', function () {
		if ($(this).data('element').hasClass('wrapper')){
			if ($(this).data('element').data('link_id') != ''){
				deleteElement($(this).data('element'), true);
			}else{
				deleteElement($(this).data('element'), false);
			}
		}
		else if ($(this).data('element').hasClass('widget')){
			deleteElement($(this).data('element'), false);
		}
		else {
			deleteElement($(this).data('element'), true);
		}
		updateBreadcrumb();
	});

	$('.removeWrapper').live('click', function () {
		deleteElement($(this).data('element'), false);
		updateBreadcrumb();
	});

	$('.moveContainerUp').live('click', function (e) {
		e.preventDefault();
		var Container = getLastParent($(this).data('element'));
		var prevDiv = Container.prev('.container');
		if (prevDiv){
			var newContainerSort = prevDiv.attr('data-sort_order');
			var newPrevDivSort = Container.attr('data-sort_order');
			Container.insertBefore(prevDiv);
			prevDiv.attr('data-sort_order', newPrevDivSort);
			Container.attr('data-sort_order', newContainerSort);
			showSaveLayout();
		}
	});

	$('.moveContainerDown').live('click', function (e) {
		e.preventDefault();
		var Container = getLastParent($(this).data('element'));
		var nextDiv = Container.next('.container');
		if (nextDiv){
			var newContainerSort = nextDiv.attr('data-sort_order');
			var newNextDivSort = Container.attr('data-sort_order');
			Container.insertAfter(nextDiv);
			nextDiv.attr('data-sort_order', newNextDivSort);
			Container.attr('data-sort_order', newContainerSort);
			showSaveLayout();
		}
	});

	$('.moveColumnLeft').live('click', function (e) {
		e.preventDefault();
		if ($(this).data('element').prev()){
			$(this).data('element').insertBefore($(this).data('element').prev());
			showSaveLayout();
		}
	});

	$('.moveColumnRight').live('click', function (e) {
		e.preventDefault();
		if ($(this).data('element').next()){
			$(this).data('element').insertAfter($(this).data('element').next());
			showSaveLayout();
		}
	});

	$('.wrapElement').live('click', function () {
		var $wrapper = $(wrapperEl);
		$wrapper.data('styles', {});
		$wrapper.data('inputs', {});
		$wrapper.attr('tmid', new Date().getTime());

		var Container = getLastParent($(this).data('element'));
		Container.wrap($wrapper);

		//$wrapper.disableSelection();

		Container.removeClass(selectedClass).trigger('click');
		showSaveLayout();
	});

	$('.editElement').live('click', function () {
		showAdjustWindow($(this).data('element'), $(this).parentsUntil('.inside').last());
	});

	$('.container').each(function () {
		setupContainer($(this), $(this).hasClass('wrapper'));
	});
	$('.column').each(function() {
		setupColumn($(this));
	});

	setupDraggableFields();
	$('.container.' + selectedClass).first().trigger('click');

	$('#construct-borders').click(function (e){
		e.preventDefault();
		if ($(this).html() == 'Show Outline'){
			$('#construct').addClass('showOutline');
			$(this).html('Hide Outline');
		}else{
			$('#construct').removeClass('showOutline');
			$(this).html('Show Outline');
		}
	});
	$('#construct-borders').trigger('click');

	$('.translateText').live('click', function (){
		var inputField = $(this).parent().find('input').first();
		showAjaxLoader(inputField, 'small');
		$.ajax({
			cache: false,
			url: js_app_link('app=languages&appPage=default&action=translateText'),
			dataType: 'json',
			type: 'post',
			data: 'fromLang=' + $(this).attr('data-from') + '&toLang=' + $(this).attr('data-to') + '&text=' + inputField.val(),
			success: function (data){
				inputField.val(data.translated[0]);
				removeAjaxLoader(inputField);
			}
		});
	});

	$('#construct-link').click(function (e){
		e.preventDefault();
		if ($(this).hasClass('ui-state-disabled')){
			return false;
		}

		var containerId = $('#construct').find('.' + selectedClass).first().data('container_id');
		$('<div><input type="text" name="link_name"></div>').dialog({
			title: 'Name Your Link',
			buttons: {
				'Create Link': function (){
					if ($(this).find('input[name=link_name]').val() == ''){
						alert('Your Link Must Have A Name.');
						return false;
					}
					var self = this;
					$.ajax({
						cache: false,
						url: js_app_link(getActionLinkParams(['action=createContainerLink'])),
						dataType: 'json',
						data: 'cID=' + containerId + '&link_name=' + $(this).find('input[name=link_name]').val(),
						type: 'post',
						success: function (){
							$(self).dialog('close');
							alert('Link Has Been Created.');
						}
					});
				},
				'Cancel': function (){
					$(this).dialog('close');
				}
			}
		});
	});

	$('#construct-importlink').click(function (e){
		e.preventDefault();
		if ($('select[name=link_id]').find('option').size() == 0){
			alert('No Links Available To Import');
			return false;
		}

		$('#importableLinks').dialog({
			buttons: {
				'Import Selected': function (){
					var self = this;
					$.ajax({
						cache: false,
						url: js_app_link(getActionLinkParams(['action=importLinkedContainer'])),
						dataType: 'html',
						data: 'layout_id=' + $(this).find('select').val(),
						type: 'post',
						success: function (html){
							if (html == 'false'){
								alert('There was a problem importing the selected container.');
							}else{
								var $html = $(html);
								$('#construct').append($html);

								$html.find('.container').each(function (){
									setupContainer($(this), $(this).hasClass('wrapper'));
								});
								$html.find('.column').each(function (){
									setupColumn($(this));
								});
								$html.find('.container').first().click();
								showSaveLayout();
							}
							$(self).dialog('close');
						}
					});
				},
				'Cancel': function (){
					$(this).dialog('close');
				}
			}
		});
		return false;
	});

	/*$('#construct-container').css({
		width: $('#construct-container').width() + 'px'
	});*/

	$.widget("ui.tmZoom", $.ui.mouse, {
		pageX: 0,
		pageY: 0,
		options: {
			distance: 1,
			delay: 0
		},
		_create: function() {
			var currentScaleX = 1;
			var currentScaleY = 1;
			$('#construct-zoomMode').click(function (){
				if ($(this).data('zoommode') == true){
					$(this).data('zoommode', false);
					$(this).html('Zoom Mode Off');
					$('#zoomOverlay').hide();
				}else{
					$(this).data('zoommode', true);
					$(this).html('Zoom Mode On');
					$('#zoomOverlay').show();
				}
				return false;
			});

			var containerOffset = $('#construct-container').position();
			$('#zoomOverlay').css({
				width: $('#construct-container').width() + 'px',
				height: $('#construct-container').height() + 'px',
				position: 'absolute',
				top: containerOffset.top,
				left: containerOffset.left,
				background: 'transparent',
				display: 'none'
			}).mousewheel(function (e, delta){
					e.preventDefault();
					if (delta > 0){
						currentScaleX += .1;
						currentScaleY += .1;
						var constructWidth = parseFloat($('#construct').width());
						var constructHeight = parseFloat($('#construct').height());
						var translateX = ((constructWidth * currentScaleX) - constructWidth) / 2;
						var translateY = ((constructHeight * currentScaleY) - constructHeight) / 2;
						$('#construct').css('-webkit-transform', 'translate(' + translateX + 'px, ' + translateY + 'px) scale(' + currentScaleX + ', ' + currentScaleY + ')');
					}else if (delta < 0){
						currentScaleX -= .1;
						currentScaleY -= .1;
						var constructWidth = parseFloat($('#construct').width());
						var constructHeight = parseFloat($('#construct').height());
						var translateX = ((constructWidth * currentScaleX) - constructWidth) / 2;
						var translateY = ((constructHeight * currentScaleY) - constructHeight) / 2;
						$('#construct').css('-webkit-transform', 'translate(' + translateX + 'px, ' + translateY + 'px) scale(' + currentScaleX + ', ' + currentScaleY + ')');
					}
				});

			this._mouseInit();
		},
		_mouseCapture: function(event) {
			this.element.addClass('mouseCapture');
			return true;
		},
		_mouseStart: function(event) {
			this.element.addClass('mouseStart');
			this.startingScrollTop = $('#construct-container').scrollTop();
			this.startingScrollLeft = $('#construct-container').scrollLeft();
			//this._mouseDrag(event, true); //Execute the drag once - this causes the helper not to be visible before getting its correct position
			return true;
		},
		_mouseStop: function(event) {
			this.element.addClass('mouseStop');
			return false;
		},
		_mouseDrag: function(event, noPropagation) {
			this.element.addClass('mouseDrag');

			if (this.pageX != event.pageX || this.pageY != event.pageY){
				this.pageX = event.pageX;
				this.pageY = event.pageY;

				var containerPosition = $('#construct-container').offset();
				var pageX = (event.pageX - containerPosition.left) - (this._mouseDownEvent.pageX - containerPosition.left);
				var pageY = (event.pageY - containerPosition.top) - (this._mouseDownEvent.pageY - containerPosition.top);

				var newX = (this.startingScrollLeft - pageX);
				if (newX <= 0){
					newX = 0;
				}
				var newY = (this.startingScrollTop - pageY);
				if (newY <= 0){
					newY = 0;
				}
				$('#construct-container').scrollLeft(newX);
				$('#construct-container').scrollTop(newY);
			}
			return false;
		}
	});

	$('#zoomOverlay').tmZoom();

	$('.widgetDraggable .ui-icon-info').click(function (){
		if ($(this).parent().find('.widgetInfo').is(':visible')){
			$('.widgetInfo').slideUp('normal');
			return;
		}
		$('.widgetInfo').slideUp('normal');
		$(this).parent().find('.widgetInfo').slideDown('normal');
	});
});


var LayoutDesigner = {
	TabPanel: null,
	tabs: {},
	options: {
		curElement: false
	},
	_init: function (o){
		var self = this;
		self.options = $.extend(self.options, o);
		self.TabPanel = $('#mainTabPanel');

		this.buildBackgroundColorPicker_RGBA(self.TabPanel.find('.makeColorPicker_RGBA'));
		this.buildColorPicker_RGB(self.TabPanel.find('.makeColorPicker'));

		$.each(self.tabs, function (){
			this.init.apply(self);
		});

		self.TabPanel.find('#font').tabs();
		self.TabPanel.tabs();
		self.TabPanel.find('.ui-tabs-nav li').each(function (k, el){
			var disableTab = false;
			var isContainerOnly = $(this).hasClass('containerOnly');
			var isColumnOnly = $(this).hasClass('columnOnly');
			var isWidgetOnly = $(this).hasClass('widgetOnly');

			if (self.options.curElement.is('table')){
				if (isContainerOnly || isWidgetOnly || isColumnOnly){
					disableTab = true;
				}
			} else if (self.options.curElement.hasClass('container')){
				if ((isColumnOnly || isWidgetOnly) && !isContainerOnly){
					disableTab = true;
				}
			} else if (self.options.curElement.hasClass('widget')){
				if ((isColumnOnly || isContainerOnly) && !isWidgetOnly){
					disableTab = true;
				}
			} else {
				if ((isContainerOnly || isWidgetOnly) && !isColumnOnly){
					disableTab = true;
				}
			}

			if (disableTab === true){
				if (self.TabPanel.tabs('option', 'selected') == k){
					self.TabPanel.tabs('select', k+1);
				}
				self.TabPanel.tabs('disable', k);
			}
		});
	},
	getCurrentElement: function () {
		return this.options.curElement;
	},
	getElStylesData: function (){
		var StyleVals = this.options.curElement.data('styles');
		if (typeof StyleVals == 'string'){
			StyleVals = $.parseJSON(htmlspecialchars_decode(StyleVals));
			this.options.curElement.data('styles', StyleVals);
		}
		else if (typeof StyleVals == 'undefined'){
			StyleVals = {};
		}
		return StyleVals;
	},
	getElInputData: function (){
		var InputVals = this.options.curElement.data('inputs');
		if (typeof InputVals == 'string'){
			InputVals = $.parseJSON(htmlspecialchars_decode(InputVals));
			this.options.curElement.data('inputs', InputVals);
		}
		else if (typeof InputVals == 'undefined'){
			InputVals = {};
		}
		return InputVals;
	},
	setElStylesData: function (data) {
		this.options.curElement.data('styles', data);
	},
	setElInputData: function (data) {
		this.options.curElement.data('inputs', data);
	},
	updateInputVal: function (key, val) {
		var inputVals = this.getElInputData();
		inputVals[key] = val;
		this.setElInputData(inputVals);
	},
	updateStylesVal: function (key, val, skipCssUpdate) {
		if (key == 'custom_css'){
			var currentCss = this.options.curElement.attr('style');
			if (this.options.curElement.data('custom_css')){
				var currentCustomCss = this.options.curElement.data('custom_css');
				currentCss = currentCss.replace(currentCustomCss, '');
			}
			this.options.curElement.data('custom_css', val);
			currentCss += val;
			if (!skipCssUpdate){
				this.options.curElement.attr('style', currentCss);
			}
		}else{
			if (!skipCssUpdate){
				this.options.curElement.css(key, val);
			}
		}

		var Styles = this.getElStylesData();
		Styles[key] = val;
		this.setElStylesData(Styles);
	},
	getBrowserInfo: function (){
		$.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
		$.browser.safari = ( $.browser.safari && !$.browser.chrome ) ? false : true;

		$.browser.gecko = /gecko/.test(navigator.userAgent.toLowerCase());
		$.browser.webkit = /applewebkit/.test(navigator.userAgent.toLowerCase());
		$.browser.trident = /trident/.test(navigator.userAgent.toLowerCase());
		$.browser.presto = /presto/.test(navigator.userAgent.toLowerCase());

		var userAgent = navigator.userAgent.toLowerCase();
		var engine = 'unknown';
		var version = 0;
		//alert(userAgent);
		if ($.browser.webkit){
			engine = 'webkit';
		}
		else {
			if ($.browser.gecko){
				engine = 'gecko';
			}
			else {
				if ($.browser.trident){
					engine = 'trident';
				}
				else {
					if ($.browser.presto){
						engine = 'presto';
					}
				}
			}
		}

		// Is this a version of IE?
		if ($.browser.msie){
			userAgent = $.browser.version;
			version = userAgent.substring(0, userAgent.indexOf('.'));
		}
		else {
			if ($.browser.chrome){
				userAgent = userAgent.substring(userAgent.indexOf('chrome/') + 7);
				version = userAgent.substring(0, userAgent.indexOf('.'));
				// If it is chrome then jQuery thinks it's safari so we have to tell it it isn't
				$.browser.safari = false;
			}
			else {
				if ($.browser.safari){
					userAgent = userAgent.substring(userAgent.indexOf('safari/') + 7);
					version = userAgent.substring(0, userAgent.indexOf('.'));
				}
				else {
					if ($.browser.mozilla){
						//Is it Firefox?
						if (navigator.userAgent.toLowerCase().indexOf('firefox') != -1){
							userAgent = userAgent.substring(userAgent.indexOf('firefox/') + 8);
							version = userAgent.substring(0, userAgent.indexOf('.'));
						}
						// If not then it must be another Mozilla
						else {
						}
					}
					else {
						if ($.browser.opera){
							userAgent = userAgent.substring(userAgent.indexOf('version/') + 8);
							version = userAgent.substring(0, userAgent.indexOf('.'));
						}
					}
				}
			}
		}

		return {
			engine: engine,
			version: version
		};
	},
	createPercentSlider: function ($el, options) {
		var defaults = {
			value: 0,
			slide: function () { },
			create: function () { },
			change: function () { },
			stop: function () { },
			start: function () { }
		};
		var o = $.extend(defaults, options);

		$el.slider({
				max: 100,
				min: 0,
				step: 1,
				value: o.value,
				create: o.create,
				change: o.change,
				start: o.start,
				stop: o.stop,
				slide: o.slide
			});
	},
	createAngleSlider: function ($el, options) {
		var defaults = {
			value: 0,
			slide: function () { },
			create: function () { },
			change: function () { },
			stop: function () { },
			start: function () { }
		};
		var o = $.extend(defaults, options);

		$el.slider({
				max: 360,
				min: 0,
				step: 45,
				value: o.value,
				create: o.create,
				change: o.change,
				start: o.start,
				stop: o.stop,
				slide: o.slide
			});
	},
	buildBackgroundColorPicker_RGBA: function($el, o) {
		$el.each(function () {
			var self = this;

			var inputR = o.inputR || $('.colorPickerRGBA_Red');
			var inputG = o.inputG || $('.colorPickerRGBA_Green');
			var inputB = o.inputB || $('.colorPickerRGBA_Blue');
			var inputA = o.inputA || $('.colorPickerRGBA_Alpha');

			$(self).miniColors({
				change: function (hex, rgb){
					inputR.val(rgb.r);
					inputG.val(rgb.g);
					inputB.val(rgb.b);

					updatePickerTriggerBackground();

					$(self).trigger('onChange');
				}
			});
			/*$(self).ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					inputR.val(rgb.r);
					inputG.val(rgb.g);
					inputB.val(rgb.b);

					$(self).ColorPickerHide();

					updatePickerTriggerBackground();

					$(self).trigger('onSubmit');
				},
				onBeforeShow: function () {
					$(self).ColorPickerSetColor({
						r: inputR.val(),
						g: inputG.val(),
						b: inputB.val()
					});

					$(self).trigger('onBeforeShow');
				},
				onChange: function (hsb, hex, rgb, el) {
					inputR.val(rgb.r);
					inputG.val(rgb.g);
					inputB.val(rgb.b);

					updatePickerTriggerBackground();

					$(self).trigger('onChange');
				}
			});*/

			function updatePickerTriggerBackground() {
				var rgbaStr = 'rgba(' +
					inputR.val() + ', ' +
					inputG.val() + ', ' +
					inputB.val() + ', ' +
					(parseInt(inputA.val()) / 100) +
					')';
				$(self).css('background-color', rgbaStr);
				$(self).trigger('onChange');
			}

			inputR.keyup(updatePickerTriggerBackground);
			inputG.keyup(updatePickerTriggerBackground);
			inputB.keyup(updatePickerTriggerBackground);
			inputA.keyup(updatePickerTriggerBackground);

			updatePickerTriggerBackground();
		});
	},
	buildColorPicker_RGB: function($el, o) {
		var thisCls = this;
		$el.each(function () {
			var self = this;
			$(this).miniColors({
				change: function (hex, rgb) {
					$(self).val(hex);
					$(self).trigger('onChange');
				}
			});

			/*$(this).ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(self).val('#' + hex);
					$(self).ColorPickerHide();

					$(self).trigger('onSubmit');
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);

					$(self).trigger('onBeforeShow');
				},
				onChange: function (hsb, hex, rgb, el) {
					$(self).val('#' + hex);

					$(self).trigger('onChange');
				}
			});*/
		});
	}
};
$.widget('ui.LayoutDesigner', LayoutDesigner);

function getBrowserInfo(){
	return Designer.getBrowserInfo();
}

$.newImageResizer = function ($appendTo, name){
	var newWidth = $('<input size="5">')
		.addClass('imageWidth')
		.attr('name', name + '[dimensions][width]');

	var newHeight = $('<input size="5">')
		.addClass('imageHeight')
		.attr('name', name + '[dimensions][height]');

	var newRatioLock = $('<span></span>')
		.addClass('linkSizes ui-icon ui-icon-link');

	var newImageSizerTable = $('<table></table>')
		.attr('cellpadding', 0)
		.attr('cellspacing', 0)
		.attr('border', 0);

	var newImageSizerTableBody = $('<tbody></tbody>');

	var RowOneColOne = $('<td></td>').append(newWidth);
	var RowOneColTwo = $('<td></td>').append(newRatioLock);
	var RowOneColThree = $('<td></td>').append(newHeight);
	var RowOne = $('<tr></tr>')
		.append(RowOneColOne)
		.append(RowOneColTwo)
		.append(RowOneColThree);

	var RowTwoColOne = $('<td></td>').attr('align', 'center').html('Width');
	var RowTwoColTwo = $('<td></td>').html('');
	var RowTwoColThree = $('<td></td>').attr('align', 'center').html('Height');
	var RowTwo = $('<tr></tr>')
		.append(RowTwoColOne)
		.append(RowTwoColTwo)
		.append(RowTwoColThree);

	newImageSizerTableBody
		.append(RowOne)
		.append(RowTwo);

	newImageSizerTable.append(newImageSizerTableBody);

	if (!$appendTo){
		return $('<div></div>').append(newImageSizerTable).html();
	}else{
		$appendTo.find('.imageResizerContainer')
			.append(newImageSizerTable);
	}
};
