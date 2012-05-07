/*
 * Script from NETTUTS.com [by Mario Jimenez] V.2 (ENHANCED, WITH DATABASE!!!)
 * @requires jQuery($), jQuery UI & sortable/draggable UI modules & jQuery COOKIE plugin
 */

jQuery.fn.replaceWithCustom = function (replacement) {
	return this.each(function () {
		element = $(this);
		$(this).after(replacement).next().html(element.html());
		for(var i = 0; i < this.attributes.length; i++){
			element.next().attr(this.attributes[i].nodeName, this.attributes[i].nodeValue);
		}
		element.remove();
	})
}

var sesWidgets = {

	jQuery : $,

	settings : {
		columns         : 'ul.widget-column',
		widgetSelector  : 'li.ui-widget',
		handleSelector  : '.ui-widget-header:not(.gridButtonBar, tr)',
		contentSelector : 'div.ui-widget-content:first',

		/* If you don't want preferences to be saved change this value to
		 false, otherwise change it to the name of the cookie: */
		saveToCookie    : 'seswidgets-widget-preferences',

		widgetDefault : {
			movable      : true,
			removable    : true,
			collapsible  : true,
			editable     : true,
			colorClasses : ['color-yellow', 'color-yellowgradient', 'color-red', 'color-redgradient', 'color-blue', 'color-bluegradient', 'color-white', 'color-whitegradient', 'color-orange', 'color-orangegradient', 'color-green', 'color-greengradient']
		}
	},

	init : function () {
		//this.attachStylesheet(js_app_link('app=index&appPage=default')+'/default.js.css');
		this.sortWidgets();
		//this.addWidgetControls();
		//this.makeSortable();
	},

	getWidgetSettings : function (id) {
		var $ = this.jQuery,
			settings = this.settings;
		return (settings.widgetIndividual && settings.widgetIndividual[id]) ? $.extend({}, settings.widgetDefault, settings.widgetIndividual[id]) : settings.widgetDefault;
	},

	addWidgetControls : function () {
		var sesWidgets = this,
			$ = this.jQuery,
			settings = this.settings;

		$(settings.widgetSelector, $(settings.columns)).each(function () {
			var thisWidgetSettings = sesWidgets.getWidgetSettings(this.id);
			if (thisWidgetSettings.removable){
				$('<a href="#" class="ui-icon ui-icon-window-close remove">CLOSE</a>').mousedown(
					function (e) {
						/* STOP event bubbling */
						e.stopPropagation();
					}).click(
					function () {
						if (confirm('This widget will be removed, ok?')){
							$(this).parents(settings.widgetSelector).animate({
								opacity : 0
							}, function () {
								$(this).wrap('<div/>').parent().slideUp(function () {
									$(this).remove();
								});
							});
						}
						return false;
					}).appendTo($(settings.handleSelector, this));
			}

			if (thisWidgetSettings.editable){
				$('<a href="#" class="ui-icon ui-icon-wrench edit">EDIT</a>').mousedown(
					function (e) {
						/* STOP event bubbling */
						e.stopPropagation();
					}).toggle(
					function () {
						$(this).parents(settings.widgetSelector)
							.find('.edit-box').show().find('input').focus();
						return false;
					},
					function () {
						$(this).parents(settings.widgetSelector)
							.find('.edit-box').hide();
						return false;
					}).appendTo($(settings.handleSelector, this));

				var titleColorHtml = $('<ul></ul>');
				titleColorHtml.append('<li class="item"><label>Change the title?</label><input value="' + $('h3', this).text() + '"/></li>');

				var colorList = $('<li></li>')
					.addClass('item')
					.append('<label>Available colors:</label>');

				var colorsUl = $('<ul></ul>')
					.addClass('colors');
				$(thisWidgetSettings.colorClasses).each(function () {
					colorsUl.append('<li class="' + this + '"></li>');
				});
				colorList.append(colorsUl);
				titleColorHtml.append(colorList);

				$('<div class="edit-box" style="display:none;"/>')
					.append(titleColorHtml)
					.insertAfter($(settings.handleSelector, this));
			}

			if (thisWidgetSettings.collapsible){
				var className = 'ui-icon-circle-minus';
				if ($(this).hasClass('collapsed')){
					className = 'ui-icon-circle-plus';
				}
				$('<a href="#" class="ui-icon ' + className + '">COLLAPSE</a>').mousedown(
					function (e) {
						/* STOP event bubbling */
						e.stopPropagation();
					}).click(
					function () {
						$(this).parents(settings.widgetSelector).toggleClass('collapsed');
						$(this).toggleClass('ui-icon-circle-minus', !$(this).hasClass('ui-icon-circle-minus'));
						$(this).toggleClass('ui-icon-circle-plus', !$(this).hasClass('ui-icon-circle-plus'));
						/* Save prefs to cookie: */
						sesWidgets.savePreferences();
						return false;
					}).prependTo($(settings.handleSelector, this));
			}
		});

		$('.edit-box').each(function () {
			$('input', this).keyup(function () {
				$(this).parents(settings.widgetSelector).find('h3').text($(this).val().length > 20 ? $(this).val().substr(0, 20) + '...' : $(this).val());
				sesWidgets.savePreferences();
			});
			$('ul.colors li', this).click(function () {

				var colorStylePattern = /\bcolor-[\w]{1,}\b/,
					thisWidgetColorClass = $(this).parents(settings.widgetSelector).attr('class').match(colorStylePattern);
				if (thisWidgetColorClass){
					$(this).parentsUntil(settings.columns).last()
						.removeClass(thisWidgetColorClass[0])
						.addClass($(this).attr('class').match(colorStylePattern)[0]);
					/* Save prefs to cookie: */
					sesWidgets.savePreferences();
				}
				return false;

			});
		});

	},

	attachStylesheet : function (href) {
		var $ = this.jQuery;
		return $('<link href="' + href + '" rel="stylesheet" type="text/css" />').appendTo('head');
	},

	makeSortable : function () {
		var sesWidgets = this,
			$ = this.jQuery,
			settings = this.settings,
			$sortableItems = (function () {
				var notSortable = '';
				$(settings.widgetSelector, $(settings.columns)).each(function (i) {
					if (!sesWidgets.getWidgetSettings(this.id).movable){
						if (!this.id){
							this.id = 'widget-no-id-' + i;
						}
						notSortable += '#' + this.id + ',';
					}
				});
				var selector = '> li';
				if (notSortable != ''){
					selector += ':not(' + notSortable + ')';
				}
				return $(selector, settings.columns);
			})();

		$sortableItems.find(settings.handleSelector).css({
			cursor : 'move'
		}).mousedown(
			function (e) {
			}).mouseup(function () {
			});

		$(settings.columns).sortable({
			items                : $sortableItems,
			connectWith          : $(settings.columns),
			handle               : settings.handleSelector,
			placeholder          : 'widget-placeholder',
			forcePlaceholderSize : true,
			revert               : 300,
			delay                : 100,
			opacity              : 0.8,
			containment          : 'document',
			tolerance            : 'pointer',
			start                : function (e, ui) {
				$(ui.helper).addClass('dragging');
			},
			stop                 : function (e, ui) {
				$(ui.item).removeClass('dragging');
				$(settings.columns).sortable('enable');
				/* Save prefs to cookie: */
				sesWidgets.savePreferences();
			}
		});
	},

	savePreferences : function () {
		var sesWidgets = this,
			$ = this.jQuery,
			settings = this.settings,
			cookieString = '';

		if (!settings.saveToCookie){
			return;
		}

		/* Assemble the cookie string */
		$(settings.columns).each(function (i) {
			cookieString += (i === 0) ? '' : '|';
			$(settings.widgetSelector, this).each(function (i) {
				cookieString += (i === 0) ? '' : ';';
				/* ID of widget: */
				cookieString += $(this).attr('id') + ',';
				/* Color of widget (color classes) */
				cookieString += $(this).attr('class').match(/\bcolor-[\w]{1,}\b/) + ',';
				/* Title of widget (replaced used characters) */
				cookieString += $('h3:eq(0)', this).text().replace(/\|/g, '[-PIPE-]').replace(/,/g, '[-COMMA-]') + ',';
				/* Collapsed/not collapsed widget? : */
				cookieString += $(settings.contentSelector, this).css('display') === 'none' ? 'collapsed' : 'not-collapsed';
			});
		});
		showAjaxLoader($('body'), 'xlarge');
		$.ajax({
			cache    : false,
			url      : js_app_link('app=index&appPage=default&action=sesWidgets'),
			data     : 'config=' + cookieString,
			type     : 'post',
			dataType : 'json',
			success  : function (data) {
				removeAjaxLoader($('body'));
			}
		});

	},

	sortWidgets : function () {
		var sesWidgets = this,
			$ = this.jQuery,
			settings = this.settings;

		if (!settings.saveToCookie){
			$('body').css({background : '#000'});
			$(settings.columns).css({visibility : 'visible'});
			return;
		}

		showAjaxLoader($('body'), 'xlarge');
		$.ajax({
			cache    : false,
			url      : js_app_link('app=index&appPage=default&action=sesWidgets'),
			data     : '',
			type     : 'post',
			dataType : 'json',
			success  : function (data) {
				removeAjaxLoader($('body'));
				var cookie = data.config;
				if (cookie == ''){
					$('body').css({background : '#ffffff;'});
					$(settings.columns).css({visibility : 'visible'});
					sesWidgets.addWidgetControls();
					sesWidgets.makeSortable();
					return;
				}

				/* For each column */
				$(settings.columns).each(function (i) {

					var thisColumn = $(this),
						widgetData = cookie.split('|')[i].split(';');

					$(widgetData).each(function () {
						if (!this.length){
							return;
						}
						var thisWidgetData = this.split(',');

						var className = $('#' + thisWidgetData[0]).attr('class');
						if (/color-/.test(className)){
							var thisWidgetColorClass = className.match(/\bcolor-[\w]{1,}\b/);
							/* Add/Replace new colour class: */
							if (thisWidgetColorClass){
								if (thisWidgetData[1] != 'null'){
									$('#' + thisWidgetData[0]).removeClass(thisWidgetColorClass[0]).addClass(thisWidgetData[1]);
								}
							}
						}

						/* Add/replace new title (Bring back reserved characters): */
						$('#' + thisWidgetData[0]).find('h3:eq(0)').html(thisWidgetData[2].replace(/\[-PIPE-\]/g, '|').replace(/\[-COMMA-\]/g, ','));

						/* Modify collapsed state if needed: */
						if (thisWidgetData[3] === 'collapsed'){
							/* Set CSS styles so widget is in COLLAPSED state */
							$('#' + thisWidgetData[0]).addClass('collapsed');
						}
						$('#' + thisWidgetData[0]).appendTo($(thisColumn));
						//$('#' + thisWidgetData[0]).remove();
						//$('#' + thisWidgetData[0]).replaceWithCustom(clonedWidget.html());
						//$(thisColumn).append(clonedWidget);
					});
				});

				/* All done, remove loading gif and show columns: */
				$('body').css({background : '#ffffff;'});

				$(settings.columns).css({
					visibility : 'visible'
				});

				sesWidgets.addWidgetControls();
				sesWidgets.makeSortable();
			}
		});

	}

};
$(document).ready(function () {
	sesWidgets.init();

});