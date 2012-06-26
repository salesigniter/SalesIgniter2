if (thisApp != 'login'){
	var sessionTimeout = setTimeout("expiredSessionWindow()", (60 * 1000) * 60 * 12);
}

function showTimer(i) {
	$('#expiredSessionWindow p').html('Login Expired<br />Redirecting In: ' + parseInt(i / 1000) + ' Seconds');
	setTimeout("showTimer(" + parseInt(i - 1000) + ")", 1000);
}

function expiredSessionWindow() {
	$('#expiredSessionWindow').dialog({
		allowClose : false,
		modal      : true,
		buttons    : {
			'Ok' : function () {
				window.location = js_app_link('app=login&appPage=default');
			}
		}
	});
	setTimeout(function () {
		window.location = js_app_link('app=login&appPage=default');
	}, (30 * 1000));

	setTimeout("showTimer(30000)", 1000);
}

function getActionLinkParams(addVars, isAjax) {
	var getVars = [];
	getVars.push('app=' + thisApp);
	getVars.push('appPage=' + thisAppPage);
	if (thisAppExt != ''){
		getVars.push('appExt=' + thisAppExt);
	}
	if (isAjax){
		getVars.push('rType=ajax');
	}

	if (addVars){
		for(var i = 0; i < addVars.length; i++){
			getVars.push(addVars[i]);
		}
	}
	return getVars.join('&');
}

function print_r(array, return_val) {
	// Prints out or returns information about the specified variable
	//
	// version: 1107.2516
	// discuss at: http://phpjs.org/functions/print_r
	// +   original by: Michael White (http://getsprink.com)
	// +   improved by: Ben Bryan
	// +      input by: Brett Zamir (http://brett-zamir.me)
	// +      improved by: Brett Zamir (http://brett-zamir.me)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// -    depends on: echo
	// *     example 1: print_r(1, true);
	// *     returns 1: 1
	var output = '',
		pad_char = ' ',
		pad_val = 4,
		d = this.window.document,
		getFuncName = function (fn) {
			var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);
			if (!name){
				return '(Anonymous)';
			}
			return name[1];
		},
		repeat_char = function (len, pad_char) {
			var str = '';
			for(var i = 0; i < len; i++){
				str += pad_char;
			}
			return str;
		},
		formatArray = function (obj, cur_depth, pad_val, pad_char) {
			if (cur_depth > 0){
				cur_depth++;
			}

			var base_pad = repeat_char(pad_val * cur_depth, pad_char);
			var thick_pad = repeat_char(pad_val * (cur_depth + 1), pad_char);
			var str = '';

			if (typeof obj === 'object' && obj !== null && obj.constructor && getFuncName(obj.constructor) !== 'PHPJS_Resource'){
				str += 'Array\n' + base_pad + '(\n';
				for(var key in obj){
					if (Object.prototype.toString.call(obj[key]) === '[object Array]'){
						str += thick_pad + '[' + key + '] => ' + formatArray(obj[key], cur_depth + 1, pad_val, pad_char);
					}
					else {
						str += thick_pad + '[' + key + '] => ' + obj[key] + '\n';
					}
				}
				str += base_pad + ')\n';
			}
			else if (obj === null || obj === undefined){
				str = '';
			}
			else { // for our "resource" class
				str = obj.toString();
			}

			return str;
		};

	output = formatArray(array, 0, pad_val, pad_char);

	if (return_val !== true){
		if (d.body){
			this.echo(output);
		}
		else {
			try {
				d = XULDocument; // We're in XUL, so appending as plain text won't work; trigger an error out of XUL
				this.echo('<pre xmlns="http://www.w3.org/1999/xhtml" style="white-space:pre;">' + output + '</pre>');
			} catch(e){
				this.echo(output); // Outputting as plain text may work in some plain XML
			}
		}
		return true;
	}
	return output;
}

function urldecode(str) {
	// Decodes URL-encoded string
	//
	// version: 1004.2314
	// discuss at: http://phpjs.org/functions/urldecode    // +   original by: Philip Peterson
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +      input by: AJ
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   improved by: Brett Zamir (http://brett-zamir.me)    // +      input by: travc
	// +      input by: Brett Zamir (http://brett-zamir.me)
	// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   improved by: Lars Fischer
	// +      input by: Ratheous    // +   improved by: Orlando
	// +      reimplemented by: Brett Zamir (http://brett-zamir.me)
	// +      bugfixed by: Rob
	// %        note 1: info on what encoding functions to use from: http://xkr.us/articles/javascript/encode-compare/
	// %        note 2: Please be aware that this function expects to decode from UTF-8 encoded strings, as found on    // %        note 2: pages served as UTF-8
	// *     example 1: urldecode('Kevin+van+Zonneveld%21');
	// *     returns 1: 'Kevin van Zonneveld!'
	// *     example 2: urldecode('http%3A%2F%2Fkevin.vanzonneveld.net%2F');
	// *     returns 2: 'http://kevin.vanzonneveld.net/'    // *     example 3: urldecode('http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a');
	// *     returns 3: 'http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a'
	var returnVal = '';
	if (typeof str != 'object' && str.length > 0){
		returnVal = decodeURIComponent(str.replace(/\+/g, '%20'));
	}
	return returnVal;
}

function parse_str(str, array) {
	// Parses GET/POST/COOKIE data and sets global variables
	//
	// version: 1004.2314
	// discuss at: http://phpjs.org/functions/parse_str
	// +   original by: Cagri Ekin
	// +   improved by: Michael White (http://getsprink.com)
	// +    tweaked by: Jack
	// +   bugfixed by: Onno Marsman
	// +   reimplemented by: stag019
	// +   bugfixed by: Brett Zamir (http://brett-zamir.me)
	// +   bugfixed by: stag019
	// -    depends on: urldecode
	// %        note 1: When no argument is specified, will put variables in global scope.
	// *     example 1: var arr = {};
	// *     example 1: parse_str('first=foo&second=bar', arr);
	// *     results 1: arr == { first: 'foo', second: 'bar' }
	// *     example 2: var arr = {};
	// *     example 2: parse_str('str_a=Jack+and+Jill+didn%27t+see+the+well.', arr);
	// *     results 2: arr == { str_a: "Jack and Jill didn't see the well." }
	var glue1 = '=',
		glue2 = '&',
		array2 = String(str).split(glue2),
		i,
		j,
		chr,
		tmp,
		key,
		value,
		bracket,
		keys,
		evalStr,
		that = this,
		fixStr = function (str) {
			return that.urldecode(str).replace(/([\\"'])/g, '\\$1').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
		};

	if (!array){
		array = this.window;
	}

	for(i = 0; i < array2.length; i++){
		tmp = array2[i].split(glue1);
		if (tmp.length < 2){
			tmp = [tmp, ''];
		}
		key = fixStr(tmp[0]);
		value = fixStr(tmp[1]);
		while(key.charAt(0) === ' '){
			key = key.substr(1);
		}
		if (key.indexOf('\0') !== -1){
			key = key.substr(0, key.indexOf('\0'));
		}
		if (key && key.charAt(0) !== '['){
			keys = [];
			bracket = 0;
			for(j = 0; j < key.length; j++){
				if (key.charAt(j) === '[' && !bracket){
					bracket = j + 1;
				}
				else if (key.charAt(j) === ']'){
					if (bracket){
						if (!keys.length){
							keys.push(key.substr(0, bracket - 1));
						}
						keys.push(key.substr(bracket, j - bracket));
						bracket = 0;
						if (key.charAt(j + 1) !== '['){
							break;
						}
					}
				}
			}
			if (!keys.length){
				keys = [key];
			}
			for(j = 0; j < keys[0].length; j++){
				chr = keys[0].charAt(j);
				if (chr === ' ' || chr === '.' || chr === '['){
					keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1);
				}
				if (chr === '['){
					break;
				}
			}
			evalStr = 'array';
			for(j = 0; j < keys.length; j++){
				key = keys[j];
				if ((key !== '' && key !== ' ') || j === 0){
					key = "'" + key + "'";
				}
				else {
					key = eval(evalStr + '.push([]);') - 1;
				}
				evalStr += '[' + key + ']';
				if (j !== keys.length - 1 && eval('typeof ' + evalStr) === 'undefined'){
					eval(evalStr + ' = [];');
				}
			}
			evalStr += " = '" + value + "';\n";
			eval(evalStr);
		}
	}
}

function js_get_all_get_params(exclude) {
	exclude = exclude || [];

	var get_url = '';
	var getVars = {};
	parse_str(allGetParams, getVars);
	$.each(getVars, function (k, v) {
		if (k != sessionName && k != 'error' && $.inArray(k, exclude) == -1){
			get_url = get_url + k + '=' + v + '&';
		}
	});

	return get_url;
}

function js_redirect(url) {
	window.location = url;
}

function js_href_link(page, params, connection) {
	connection = connection || 'NONSSL';
	params = params || '';
	if (page == ''){
		alert('Error:: Unable to determine the page link!' + "\n\n" + 'Function used: js_href_link(\'' + page + '\', \'' + params + '\', \'' + connection + '\')');
	}

	var link;
	link = 'http://' + jsConfig.get('SERVER_NAME') + jsConfig.get('DIR_WS_ADMIN');
	if (connection == 'SSL'){
		if (jsConfig.get('ENABLE_SSL') == 'true'){
			link = 'https://' + jsConfig.get('SERVER_NAME') + jsConfig.get('DIR_WS_ADMIN');
		}
	}

	if (params == ''){
		link = link + page + '?' + SID;
	}
	else {
		link = link + page + '?' + params + '&' + SID;
	}

	while((link.substr(-1) == '&') || (link.substr(-1) == '?')){
		link = link.substr(0, link.length - 1);
	}

	return link;
}

function js_app_link(params, connection) {
	connection = connection || jsConfig.get('REQUEST_TYPE') || 'SSL';
	params = params || '';

	var protocol = 'http';
	if (connection == 'SSL'){
		if (jsConfig.get('ENABLE_SSL') == 'true'){
			protocol = protocol + 's';
		}
	}
	var link = protocol + '://' + jsConfig.get('SERVER_NAME') + jsConfig.get('DIR_WS_ADMIN');

	if ($_GET['showErrors']){
		if (params == ''){
			params = 'showErrors=true';
		}
		else {
			params += '&showErrors=true';
		}
	}

	if (params == ''){
		link = link + 'application.php?' + SID;
	}
	else {
		var paramsObj = {};
		parse_str(params, paramsObj);
		if (paramsObj.appExt){
			link = link + paramsObj.appExt + '/';
		}
		link = link + paramsObj.app + '/' + paramsObj.appPage + '.php';

		var linkParams = [];
		var requireSID = false;
		$.each(paramsObj, function (k, v) {
			if (k == 'app' || k == 'appPage' || k == 'appExt'){
				return;
			}
			if (k == 'rType' && v == 'ajax'){
				requireSID = true;
			}
			linkParams.push(k + '=' + v);
		});

		if (linkParams.length > 0){
			link = link + '?' + linkParams.join('&') + '&' + (requireSID === true ? sessionName + '=' + sessionId : SID);
		}
		else {
			link = link + '?' + (requireSID === true ? sessionName + '=' + sessionId : SID);
		}
	}

	while((link.substr(-1) == '&') || (link.substr(-1) == '?')){
		link = link.substr(0, link.length - 1);
	}

	return link;
}

function js_catalog_app_link(params, connection) {
	connection = connection || jsConfig.get('REQUEST_TYPE') || 'SSL';
	params = params || '';

	var protocol = 'http';
	if (connection == 'SSL'){
		if (jsConfig.get('ENABLE_SSL') == 'true'){
			protocol = protocol + 's';
		}
	}
	var link = protocol + '://' + jsConfig.get('SERVER_NAME') + jsConfig.get('DIR_WS_CATALOG');

	if (params == ''){
		link = link + 'application.php?' + SID;
	}
	else {
		var paramsObj = {};
		parse_str(params, paramsObj);
		if (paramsObj.appExt){
			link = link + paramsObj.appExt + '/';
		}
		link = link + paramsObj.app + '/' + paramsObj.appPage + '.php';

		var linkParams = [];
		var requireSID = false;
		$.each(paramsObj, function (k, v) {
			if (k == 'app' || k == 'appPage' || k == 'appExt'){
				return;
			}
			if (k == 'rType' && v == 'ajax'){
				requireSID = true;
			}
			linkParams.push(k + '=' + v);
		});

		if (linkParams.length > 0){
			link = link + '?' + linkParams.join('&') + '&' + (requireSID === true ? sessionName + '=' + sessionId : SID);
		}
		else {
			link = link + '?' + (requireSID === true ? sessionName + '=' + sessionId : SID);
		}
	}

	while((link.substr(-1) == '&') || (link.substr(-1) == '?')){
		link = link.substr(0, link.length - 1);
	}

	return link;
}

function makeTabsVertical(selector) {
	$(selector).wrap('<div style="position:relative;"></div>');
	var $verticalContainer = $(selector + ' > ul.ui-tabs-nav').addClass('ui-vertical-tabs-nav').insertBefore($(selector));
	$verticalContainer.css({
		width    : '200px',
		position : 'absolute',
		top      : 0,
		left     : 0
	});

	$(selector).css({
		marginLeft : '210px'
	});

	$('li', $verticalContainer).removeClass('ui-corner-top').addClass('ui-corner-all').css({
		padding : '.3em',
		margin  : '.3em'
	}).click(function () {
			$('a', this).trigger('click');
		});

	$(selector).parent().css({
		'min-height' : $verticalContainer.outerHeight(true)
	});
}

function showToolTip(settings) {
	var elOffset = settings.el.offset();
	var pxFromEl = 0;

	var $toolTip = $('<div>')
		.addClass('ui-widget')
		.addClass('ui-widget-content')
		.addClass('ui-corner-all')
		.css({
			position   : 'absolute',
			left       : elOffset.left,
			top        : elOffset.top,
			zIndex     : 9999,
			padding    : '5px',
			whiteSpace : 'nowrap'
		})
		.html(settings.tipText)
		.appendTo($(document.body));

	$toolTip.css('left', (elOffset.left + settings.el.outerWidth() + pxFromEl));
	$toolTip.css('top', (elOffset.top - $toolTip.outerHeight()) - pxFromEl);

	//alert((settings.offsetLeft + 200) + ' >= ' + $(window).width());
	if ((elOffset.left + 300) >= $(window).width()){
		$toolTip.css('left', (elOffset.left - $toolTip.outerWidth() - pxFromEl));
	}
	if ((elOffset.top - $toolTip.height()) <= 0){
		$toolTip.css('top', (elOffset.top + settings.el.outerHeight() + $toolTip.outerHeight() + pxFromEl));
	}
	return $toolTip;
}

function showAjaxLoader($el, size, placement) {
	$el.each(function () {
		var $self = $(this);
		var selfLeft = $self.position().left;
		var selfTop = $self.position().top;
		var selfWidth = $self.outerWidth(true);
		var selfHeight = $self.outerHeight(true);

		var $overlay = $('<div></div>').addClass('ui-widget-overlay').css({
			position : 'absolute',
			width    : selfWidth,
			height   : selfHeight,
			left     : selfLeft,
			top      : selfTop,
			zIndex   : 2
		});
		if (placement && placement == 'append'){
			$overlay.css({
				top    : 0,
				width  : $self.width(),
				height : $self.height()
			});
			$overlay.appendTo($self);
		}
		else {
			$overlay.insertAfter($self);
		}

		var $ajaxLoader = $('<div></div>').addClass('ui-ajax-loader').addClass('ui-ajax-loader-' + size).css({
			position : 'absolute',
			left     : selfLeft,
			top      : selfTop,
			zIndex   : 3
		});
		if (placement && placement == 'append'){
			$ajaxLoader.appendTo($overlay);
		}
		else {
			$ajaxLoader.insertAfter($overlay);
		}

		$ajaxLoader.css({
			left : selfLeft + (parseInt($overlay.width()) / 2) - (parseInt($ajaxLoader.width()) / 2),
			top  : selfTop + (parseInt($overlay.height()) / 2) - (parseInt($ajaxLoader.height()) / 2)
		});

		/*$ajaxLoader.position({
		 my: 'center center',
		 at: 'center center',
		 offset: '0 0',
		 of: $overlay,
		 collision: 'fit'
		 });*/

		$self.watch('height,offsetTop', function (w, i) {
			if (w.props[i] == 'height'){
				selfTop = $self.position().top;
				selfHeight = $self.outerHeight(true);
				$overlay.css({
					height : selfHeight
				});

				$ajaxLoader.css({
					top : selfTop + (parseInt($overlay.height()) / 2) - (parseInt($ajaxLoader.height()) / 2)
				});
			}
			else if (w.props[i] == 'offsetTop'){
				selfTop = $self.position().top;
				$overlay.css({
					top : selfTop
				});

				$ajaxLoader.css({
					top : selfTop + (parseInt($overlay.height()) / 2) - (parseInt($ajaxLoader.height()) / 2)
				});
			}
		}, 100, '_' + $self.attr('id'));

		$self.data('ajaxOverlay', $overlay);
		$self.data('ajaxLoader', $ajaxLoader);
		$self.data('ajaxOverlay').show();
		$self.data('ajaxLoader').show();
	});
}

function hideAjaxLoader($el) {
	$el.each(function () {
		removeAjaxLoader($(this));
	});
}

function removeAjaxLoader($el) {
	$el.each(function () {
		var $self = $(this);
		$self.data('ajaxOverlay').remove();
		$self.data('ajaxLoader').remove();
		$self.removeData('ajaxOverlay');
		$self.removeData('ajaxLoader');
		$self.unwatch('_' + $(this).attr('id'));
	});
}

function showInfoBox(infoboxId) {
	$('.infoboxContainer').hide();
	$('#infobox_' + infoboxId).show();
}

function StripTags(strMod) {
	if (arguments.length < 3){
		strMod = strMod.replace(/<\/?(?!\!)[^>]*>/gi, '');
	}
	else {
		var IsAllowed = arguments[1];
		var Specified = eval("[" + arguments[2] + "]");
		if (IsAllowed){
			var strRegExp = '</?(?!(' + Specified.join('|') + '))\b[^>]*>';
			strMod = strMod.replace(new RegExp(strRegExp, 'gi'), '');
		}
		else {
			var strRegExp = '</?(' + Specified.join('|') + ')\b[^>]*>';
			strMod = strMod.replace(new RegExp(strRegExp, 'gi'), '');
		}
	}
	return strMod;
}

function liveMessage(message, timeout) {
	$('.sysMsgBlock').show();
	timeout = timeout || 2500;

	var SysMsgBlockMessage = $('<div class="sysMsgBlock_message ui-corner-all ui-state-active"></div>');
	SysMsgBlockMessage.html(message);
	SysMsgBlockMessage.css({
		margin     : '.3em',
		lineHeight : '3em',
		display    : 'none',
		background : '#595353',
		color      : '#ffffff',
		fontSize   : '1.3em'
	});

	SysMsgBlockMessage
		.hide()
		.appendTo($('.sysMsgBlock'))
		.fadeIn('fast', function () {
			setTimeout(function () {
				SysMsgBlockMessage.fadeOut('slow', function () {
					$(this).remove();
					if ($('.sysMsgBlock_message').size() <= 0){
						$('.sysMsgBlock').hide();
					}
				});
			}, timeout);
		});
}

function confirmDialog(options) {
	var o = options;

	if (o.onConfirm){
		var onConfirm = function () {
			o.onConfirm.apply(this);
			$(this).dialog('close').remove();
		};
	}

	if (o.onCancel){
		var onCancel = function () {
			o.onCancel.apply(this);
			$(this).dialog('close').remove();
		};
	}

	$('<div></div>').html(o.content).attr('title', o.title || 'Please Confirm').dialog({
		resizable  : false,
		allowClose : false,
		modal      : true,
		buttons    : [
			{
				text  : jsLanguage.get('TEXT_BUTTON_CONFIRM'),
				icon  : 'ui-icon-check',
				click : onConfirm || function () {
					var dialogEl = this;
					showAjaxLoader($(dialogEl), 'large', false);
					$.ajax({
						cache    : false,
						url      : o.confirmUrl,
						dataType : o.dataType || 'json',
						type     : o.type || 'get',
						data     : o.data || null,
						success  : function (data) {
							if (data.success == true){
								if (o.success){
									o.success.apply(dialogEl, [data]);
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
			},
			{
				text  : jsLanguage.get('TEXT_BUTTON_CANCEL'),
				icon  : 'ui-icon-closethick',
				click : onCancel || function () {
					$(this).dialog('close').remove();
				}
			}
		]
	});
}

function popupWindowFavorites(w, h) {
	$('<div id="favoritesDialog"></div>').dialog({
		title    : 'Add To Favorites',
		autoOpen : true,
		width    : w,
		height   : h,
		close    : function (e, ui) {
			$(this).dialog('destroy').remove();
		},
		open     : function (e, ui) {
			var getParams = js_get_all_get_params(['app', 'appPage', 'appExt', 'action', 'noCache']);
			getParams = getParams.substr(0, getParams.length - 1);

			var html = '<table cellpadding="2" cellspacing="0"><tbody>';
			if (thisAppExt != null){
				html += '<tr><td>Extension Name: </td><td><input type="hidden" name="settings[appExt]" value="' + thisAppExt + '">' + thisAppExt + '</td></tr>';
			}
			html += '<tr><td>Application: </td><td><input type="hidden" name="settings[app]" value="' + thisApp + '">' + thisApp + '</td></tr>';
			html += '<tr><td>Application Page: </td><td><input type="hidden" name="settings[appPage]" value="' + thisAppPage + '">' + thisAppPage + '</td></tr>';
			html += '<tr><td>Other Params: </td><td><input type="hidden" name="settings[get]" value="' + getParams + '">' + getParams + '</td></tr>';
			html += '<tr><td>Link Name: </td><td><input type="text" name="settings[name]" /></td></tr>';
			html += '</tbody></table>';

			$(this).html(html);
		},
		buttons  : {
			'Save' : function () {
				//ajax call to save comment on success
				dialog = $(this);
				showAjaxLoader($('#favoritesDialog'), 'xlarge');
				$.ajax({
					cache    : false,
					url      : js_app_link('app=index&appPage=default&action=addToFavorites'),
					data     : dialog.find('input').serialize(),
					type     : 'post',
					dataType : 'json',
					success  : function (data) {
						hideAjaxLoader($('#favoritesDialog'));
						dialog.dialog('close');
					}
				});
			},
			Cancel : function () {
				$(this).dialog('close');
			}
		}
	});
	return false;
}

function gridWindow(options) {
	var self = options.buttonEl;
	showAjaxLoader($(self), 'small');

	$.ajax({
		cache    : false,
		url      : options.contentUrl,
		dataType : 'html',
		success  : function (htmlData) {
			options.gridEl.effect('fade', {
				mode : 'hide'
			}, function () {
				var $newWindow = $('<div class="newWindowContainer"></div>')
					.html(htmlData);

				if (options.onBeforeShow){
					options.onBeforeShow.apply($newWindow, [
						{
							triggerEl : self
						}
					]);
				}

				$newWindow.insertAfter(options.gridEl).effect('fade', {
					mode : 'show'
				}, function () {
					$newWindow.find('button').button();

					if (options.onShow){
						options.onShow.apply($newWindow, [
							{
								triggerEl : self
							}
						]);
					}
					else {
						var windowEl = this;
						$(windowEl).find('.cancelButton').click(function () {
							$(windowEl).effect('fade', {
								mode : 'hide'
							}, function () {
								options.gridEl.effect('fade', {
									mode : 'show'
								}, function () {
									$(windowEl).remove();
								});
							});
						});

						$(windowEl).find('.saveButton').click(function () {
							$.ajax({
								cache    : false,
								url      : options.saveUrl,
								dataType : 'json',
								data     : $(windowEl).find('*').serialize(),
								type     : 'post',
								success  : function (data) {
									if (data.success){
										if (typeof options.onSaveSuccess == 'undefined'){
											var getVars = [];
											getVars.push('app=' + thisApp);
											getVars.push('appPage=' + thisAppPage);
											if (thisAppExt != ''){
												getVars.push('appExt=' + thisAppExt);
											}
											js_redirect(js_app_link(getVars.join('&')));
										}
										else {
											if (options.onSaveSuccess.action == 'redirect'){
												js_redirect(options.onSaveSuccess.url);
											}
										}
									}
								}
							});
						});

						if (typeof editWindowOnLoad != 'undefined'){
							editWindowOnLoad.apply(windowEl);
						}
					}

					removeAjaxLoader($(self));
				});
			});
		}
	});
}

function configurationGridWindow(options) {
	gridWindow({
		buttonEl   : options.buttonEl,
		gridEl     : options.gridEl,
		contentUrl : options.contentUrl,
		onShow     : function () {
			var self = this;

			var fieldNameError = false;
			var origValues = [];
			$(self).find('input, select, textarea').each(function () {
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
				}
				else if ($(this).attr('type') == 'radio'){
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
						}
						else if (this.checked === false && $.inArray($(this).val(), origValues[inputName]) > -1){
							edited = true;
						}
					}
					else if (origValues[inputName] != $(this).val()){
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

			if (fieldNameError === true){
				alert('Editing of some fields has been disabled due to an input naming error, please notify the cart administrator.');
			}

			$(self).find('.cancelButton').click(function () {
				var process = false;
				var hideWindow = function () {
					$(self).effect('fade', {
						mode : 'hide'
					}, function () {
						options.gridEl.effect('fade', {
							mode : 'show'
						}, function () {
							$(self).remove();
						});
					});
				};

				if ($(self).find('.edited').size() > 0){
					confirmDialog({
						title     : jsLanguage.get('TEXT_HEADER_CONFIRM_LOST_CHANGES'),
						content   : jsLanguage.get('TEXT_INFO_LOST_CHANGES'),
						onConfirm : hideWindow
					});
				}
				else {
					hideWindow();
				}
			});

			$(self).find('.saveButton').click(function () {
				showAjaxLoader($(self).find('.edited'), 'small');
				var emptyCheckboxes = [];
				$(self).find('.edited').each(function () {
					if ($(this).attr('type') == 'checkbox'){
						if (this.checked === false){
							if ($.inArray($(this).attr('name'), emptyCheckboxes) == -1){
								emptyCheckboxes.push($(this).attr('name'));
							}
						}
						else if ($.inArray($(this).attr('name'), emptyCheckboxes) > -1){
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
				$.post(options.saveUrl, addPost + $(self).find('.edited').serialize(), function (data, textStatus, jqXHR) {
					if (data.success === true){
						removeAjaxLoader($(self).find('.edited'));
						$(self).find('.edited').removeClass('edited').addClass('notEdited');
						if (options.onSaveSuccess){
							options.onSaveSuccess.apply();
						}
					}
				}, 'json');
			});

			$(self).find('.makeModFCK').each(function () {
				CKEDITOR.replace(this, {
					toolbar : 'Simple'
				});
			});

			$(self).find('.makeTabPanel').tabs();
			$(self).find('.makeTabsVertical').each(function () {
				makeTabsVertical('#' + $(this).attr('id'));
			});

			if (typeof editWindowOnLoad != 'undefined'){
				editWindowOnLoad.apply(self);
			}
		}
	});
}

function setConfirmUnload(on, callback) {
	window.onbeforeunload = (on) ? function () { return jsLanguage.get('TEXT_INFO_LOST_CHANGES') } : null;
	if (callback){
		callback.apply();
	}
}

$(document).ready(function () {
	$('#addToFavorites').click(function () {
		return popupWindowFavorites(300, 200);
	});
	$('a', $('.headerMenuHeadingBlock')).each(function () {
		var $link = $(this);
		$($link.parent()).hover(
			function () {
				$link.css('cursor', 'pointer').addClass('ui-state-hover');

				if ($('ul', $(this)).size() > 0){
					var $menuList = $('ul:first', $(this));
					var offSetLeft = $(this).width();

					var leftMenu = $(this).parent().offset(false).left + (offSetLeft + $(this).width());
					if (leftMenu > $(window).width()){
						offSetLeft = -($menuList.width() + 5);
					}
					$menuList.css({
						visibility      : 'visible',
						left            : offSetLeft,
						backgroundColor : '#FFFFFF',
						zIndex          : 9999
					});
				}
			},
			function () {
				$link.css({cursor : 'default'}).removeClass('ui-state-hover');

				if ($('ul', this).size() > 0){
					$('ul:first', this).css({
						visibility : 'hidden'
					});
				}
			}).click(function () {
				document.location = $('a:first', this).attr('href');
			});
	});

	$('.headerMenuHeadingBlock').hover(function () {
		var headingBlock = this;
		var $spanObj = $('.headerMenuHeading', headingBlock);
		$spanObj.addClass('ui-state-hover ui-corner-top').css({
			cursor       : 'default',
			fontWeight   : 'bold',
			border       : '1px solid #aaaaaa',
			borderBottom : 'none'
		});

		var offSet = $(headingBlock).offset(false);
		$('div:first', $(headingBlock)).each(function () {
			$(this).css({
				position        : 'absolute',
				width           : 'auto',
				top             : offSet.top + $(headingBlock).height(),
				left            : $(this).parent().position().left + 2,
				backgroundColor : '#FFFFFF',
				zIndex          : 9998
			}).show();

			$('ul:first', $(this)).css('visibility', 'visible');
		});
	}, function () {
		var $spanObj = $('.headerMenuHeading', this);
		$spanObj.removeClass('ui-state-hover').css({
			cursor : 'default',
			border : '1px solid transparent'
		});
		$('.ui-menu-flyout:first', $(this)).hide();
	});

	/* Navigation Menu --BEGIN-- */
	$('#headerMenu.ui-navigation-menu').each(function () {
		var Roots = [];
		$(this).find('li').each(function () {
			$(this).addClass('ui-state-default');
			$(this).hover(
				function () {
					$(this).addClass('ui-state-hover');

					if ($(this).children('ol').size() > 0){
						var self = $(this);

						$(this).find('ol:first').each(function (i, el) {
							var cssSettings = {
								top    : 0,
								left   : 0,
								zIndex : self.parent().css('z-index') + 1
							};

							if (self.hasClass('root')){
								cssSettings.top = self.innerHeight();
							}
							else {
								cssSettings.left = '98%';
							}

							$(this).css(cssSettings).show();

							$(this).find('.ui-icon.ui-icon-triangle-1-s').each(function () {
								$(this).removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e').css({
									position : 'absolute',
									right    : 0,
									top      : (self.innerHeight() / 2) - ($(this).outerHeight() / 2)
								});
							});
						});
					}
				}, function () {
					$(this).removeClass('ui-state-hover');

					if ($(this).children('ol').size() > 0){
						$(this).children('ol').hide();
					}
				});

			if ($(this).find('.ui-icon:first').size() > 0){
				$(this).find('.ui-icon:first').each(function () {
					$(this).css({
						position : 'absolute',
						right    : 0,
						top      : ($(this).parent().parent().parent().innerHeight() / 2) - ($(this).outerHeight(true) / 2)
					});
				});
			}

			if ($(this).hasClass('root')){
				Roots.push(this);
			}
		});
	});
	/* Navigation Menu --END-- */

	$('.gridContainer').newGrid();
	$('.fileManager').filemanager();
	$('.fileManager, .fileManagerInput').live('click', function () {
		if ($(this).hasClass('ui-filemanager-input') === false){
			$(this).filemanager();
			$(this).click();
		}
	});

	$('[tooltip]').live('mouseover mouseout click', function (e) {
		if (e.type == 'mouseover'){
			this.Tooltip = showToolTip({
				el      : $(this),
				tipText : $(this).attr('tooltip')
			});
		}
		else {
			if (this.Tooltip){
				this.Tooltip.remove();
			}
		}
	});

	$('.ui-icon').live('mouseover mouseout click', function (e) {
		if (e.type == 'mouseover'){
			this.style.cursor = 'pointer';
		}
		else if (e.type == 'mouseout'){
			this.style.cursor = 'default';
		}
		else if (e.type == 'click'){
		}
	});

	$('button, a[type="button"]').button();

	$('.phpTraceView').live('click', function (e) {
		e.preventDefault();

		var traceTable = $(this).parent().parent().find('table.phpTrace');
		if (traceTable.is(':visible')){
			traceTable.hide();
			$(this).html('View Trace');
		}
		else {
			traceTable.show();
			$(this).html('Hide Trace');
		}
	});

	$('a.passProtect, button.passProtect').each(function () {
		$(this).click(function (e) {
			var self = this;
			if ($(self).data('validated') && $(self).data('validated') == 'true'){
				$(self).removeData('validated');
				return true;
			}

			$('#validationPopup').remove();
			var PopupBlock = $('<div id="validationPopup"></div>')
				.addClass('ui-widget ui-widget-content ui-corner-all')
				.html('<span style="position:absolute;top:.2em;right:.2em;" class="ui-icon ui-icon-closethick"></span>Enter Password<br><input type="password" name="password" size="13"><br><button type="button" style="font-size:.7em;"><span>Submit</span></button>')
				.css({
					position   : 'absolute',
					background : '#cccccc',
					boxShadow  : '0px 3px 4px 0px #CCC',
					padding    : '.5em',
					top        : $(this).offset().top + $(this).height(),
					left       : $(this).offset().left - $(this).width(),
					width      : '10em'
				}).appendTo(document.body);

			if ((PopupBlock.offset().left + PopupBlock.width()) >= $(window).width()){
				PopupBlock.css('left', $(this).offset().left - PopupBlock.width() + $(this).width());
			}

			var validatePass = function (val) {
				liveMessage(jsLanguage.get('TEXT_VALIDATING_OVERRIDE'));
				$.ajax({
					cache    : false,
					url      : js_app_link('app=admin_members&appPage=default&action=validateOverride'),
					dataType : 'json',
					type     : 'post',
					data     : {
						password : val
					},
					success  : function (Resp) {
						PopupBlock.remove();
						if (Resp.status == true){
							liveMessage(jsLanguage.get('TEXT_OVERRIDE_VALIDATED'));
							$(self).data('validated', 'true');
							$(self).trigger('click');
						}
						else {
							liveMessage(jsLanguage.get('TEXT_OVERRIDE_NOT_VALIDATED'));
							$(self).data('validated', 'false');
						}
					}
				});
			};

			PopupBlock.find('.ui-icon-closethick').click(function () {
				PopupBlock.remove();
			});

			PopupBlock.find('button').click(
				function () {
					validatePass(PopupBlock.find('input[name=password]').val());
				}).button();

			PopupBlock.find('input[name=password]').keypress(function (event) {
				if (event.which == '13'){
					validatePass($(this).val());
				}
			});

			if (!$(self).data('validated') || $(self).data('validated') == 'false'){
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				return false;
			}
		});
	});

	$(document).on('blur', '*[data-validate="true"]', function () {
		if (this.validity){
			if (this.checkValidity() === false){
				var Message = jsLanguage.get('VALIDATION_ERROR_UNKNOWN');
				var FieldName = $(this).attr('name').toUpperCase();
				if (this.validity.valueMissing){
					Message = jsLanguage.get(FieldName + '_VALIDATION_ERROR_VALUE_MISSING');
				}
				else if (this.validity.typeMismatch){
					Message = jsLanguage.get(FieldName + '_VALIDATION_ERROR_TYPE_MISMATCH');
				}
				else if (this.validity.patternMismatch){
					Message = jsLanguage.get(FieldName + '_VALIDATION_ERROR_PATTERN_MISMATCH');
				}
				else if (this.validity.tooLong){
					Message = jsLanguage.get(FieldName + '_VALIDATION_ERROR_TOO_LONG');
				}
				else if (this.validity.rangeUnderflow){
					Message = jsLanguage.get(FieldName + '_VALIDATION_ERROR_RANGE_UNDERFLOW');
				}
				else if (this.validity.rangeOverflow){
					Message = jsLanguage.get(FieldName + '_VALIDATION_ERROR_RANGE_OVERFLOW');
				}
				else if (this.validity.stepMismatch){
					Message = jsLanguage.get(FieldName + '_VALIDATION_ERROR_STEP_MISMATCH');
				}
				this.setCustomValidity(Message);
				$(this).addClass('invalid');
			}
			else {
				this.setCustomValidity('');
				$(this).removeClass('invalid');
			}
		}
		else {
			if ($(this).attr('pattern')){
				var Pattern = new RegExp($(this).attr('pattern'));
				if (Pattern.test($(this).val()) === false){
					$(this).addClass('invalid');
				}
				else {
					$(this).removeClass('invalid');
				}
			}

			if ($(this).hasClass('invalid') === false){
				if ($(this).attr('minlength')){
					if ($(this).val().length < $(this).attr('minlength')){
						$(this).addClass('invalid');
					}
					else {
						$(this).removeClass('invalid');
					}
				}
			}
		}
	});

	$(document).bind('validateForm', function (e, originalE) {
		$(this).find('*[data-validate="true"]').trigger('blur');
		if ($(this).find('.invalid').size() > 0){
			originalE.stopPropagation();
			originalE.stopImmediatePropagation();

			var $Firstfield = $(this).find('.invalid').first();

			$(document.body).each(function () {
				var $popup = $('<div class="inputErrorPopup"></div>');
				$popup.html('<div class="ui-widget-content arrowOutside">' +
					'<div class="arrowInside"></div>' +
					'</div>' +
					'<div class="ui-widget-content ui-corner-all mainContent">' +
					'<span class="ui-icon ui-icon-error"></span>' +
					'<span class="ui-widget-content-text">This Field Has An Error</span>' +
					'</div>');
				$(this).append($popup);

				$popup.position({
					my        : 'left top',
					at        : 'left bottom',
					of        : $Firstfield,
					collision : 'flip flip',
					offset    : '0px -3px'
				});

				setTimeout(function () {
					$popup.remove();
				}, 5500);
			});
		}
	});

	$(window).scroll(function (e) {
		if ($('.ApplicationPageMenu').size() == 0){
			return;
		}
		var scrollTop = $(this).scrollTop();
		var buttonContainer = $('.ApplicationPageMenu');
		if (scrollTop > buttonContainer.offset().top){
			buttonContainer.width(buttonContainer.width());
			buttonContainer.data('originalOffset', buttonContainer.offset().top);
			buttonContainer.addClass('fixed');
		}
		else if (scrollTop < buttonContainer.data('originalOffset')){
			buttonContainer.width('auto');
			buttonContainer.removeClass('fixed');
		}
	});

	$(document).on('submit', 'form', function (e) {
		$(this).trigger('validateForm', [e]);
	});

	$('.multipleTextInput').on('click', '.addInput, .removeInput, .undoRemove', function () {
		var Row = $(this).parentsUntil('tbody').last();
		if ($(this).hasClass('addInput')){
			var ClonedRow = Row.clone();
			ClonedRow.find('input').val('');
			ClonedRow.find('.addInput').hide();
			ClonedRow.find('.undoRemove').hide();
			ClonedRow.find('.removeInput').show();

			ClonedRow.appendTo($(this).parentsUntil('table').last());
		}
		else if ($(this).hasClass('removeInput')) {
			var Col = Row.find('td').first();
			Col.addClass('ui-state-disabled');
			Col.find('input').attr('disabled', 'disabled');
			$(this).hide();
			Row.find('.undoRemove').show();
		}else {
			var Col = Row.find('td').first();
			Col.removeClass('ui-state-disabled');
			Col.find('input').removeAttr('disabled');
			$(this).hide();
			Row.find('.removeInput').show();
		}
	});

	$('.ui-selectbox-searchable').on('mouseover mouseout mouseup mousedown keyup', '.ui-selectbox-searchable-value-box, .ui-selectbox-searchable-option, .ui-selectbox-searchable-search-input', function (e){
		var $this = $(this);
		var $SelectBox = $(e.delegateTarget);

		if (e.type == 'keyup'){
			if ($this.hasClass('ui-selectbox-searchable-search-input')){
				$SelectBox.find('.ui-selectbox-searchable-option').hide();
				$SelectBox.find('.ui-selectbox-searchable-option').filter(function (){
					var htmlCheck = $(this).html();
					var Pattern = new RegExp('^' + $this.val(), 'i');
					return (Pattern.test(htmlCheck));
				}).show();
			}
			return true;
		}else if ($this.hasClass('ui-selectbox-searchable-search-input')){
			return;
		}

		switch(e.type){
			case 'mouseover':
				$this.addClass('ui-state-hover');
				break;
			case 'mouseout':
				$this.removeClass('ui-state-focus').removeClass('ui-state-hover');
				break;
			case 'mousedown':
				$this.addClass('ui-state-focus');
				break;
			case 'mouseup':
				$this.removeClass('ui-state-focus').addClass('ui-state-active');

				if ($this.hasClass('ui-selectbox-searchable-option')){
					$SelectBox.find('.ui-selectbox-searchable-value').html($this.html());
					$SelectBox.find('input:hidden, select:hidden').val($(this).val()).change();
					$SelectBox.find('.ui-selectbox-searchable-value-box').removeClass('ui-state-active');
					$SelectBox.find('.ui-selectbox-searchable-value-box').removeClass('ui-corner-top');
					$SelectBox.find('.ui-selectbox-searchable-search-input').hide();
					$SelectBox.find('.ui-selectbox-searchable-options-box').hide();
					$this.removeClass('ui-state-active');
				}else{
					$SelectBox.find('.ui-selectbox-searchable-options-box').show();
					$SelectBox.find('.ui-selectbox-searchable-search-input').show();
					$SelectBox.find('.ui-selectbox-searchable-value-box').addClass('ui-corner-top');
					$(document).one('mousedown', function (e){
						if (
							$(e.toElement).hasClass('ui-selectbox-searchable-option') === false &&
							$(e.toElement).hasClass('ui-selectbox-searchable-search-input') === false &&
							$(e.toElement).hasClass('ui-selectbox-searchable-options-box') === false
						){
							$SelectBox.find('.ui-selectbox-searchable-value-box').removeClass('ui-corner-top');
							$SelectBox.find('.ui-selectbox-searchable-options-box').hide();
							$SelectBox.find('.ui-selectbox-searchable-search-input').hide();
							$this.removeClass('ui-state-active');
						}
					});
				}
				break;
		}
	});
});

$.fn.watch = function (props, func, interval, id) {
	/// <summary>
	/// Allows you to monitor changes in a specific
	/// CSS property of an element by polling the value.
	/// when the value changes a function is called.
	/// The function called is called in the context
	/// of the selected element (ie. this)
	/// </summary>
	/// <param name="prop" type="String">CSS Property to watch. If not specified (null) code is called on interval</param>
	/// <param name="func" type="Function">
	/// Function called when the value has changed.
	/// </param>
	/// <param name="func" type="Function">
	/// optional id that identifies this watch instance. Use if
	/// if you have multiple properties you're watching.
	/// </param>
	/// <param name="id" type="String">A unique ID that identifies this watch instance on this element</param>
	/// <returns type="jQuery" />
	if (!interval){
		interval = 200;
	}
	if (!id){
		id = "_watcher";
	}

	return this.each(function () {
		var _t = this;
		var el = $(this);
		var fnc = function () { __watcher.call(_t, id) };
		var itId = null;

		if (typeof (this.onpropertychange) == "object"){
			el.bind("propertychange." + id, fnc);
		}
		else if ($.browser.mozilla){
			el.bind("DOMAttrModified." + id, fnc);
		}
		else {
			itId = setInterval(fnc, interval);
		}

		var data = { id : itId,
			props       : props.split(","),
			func        : func,
			vals        : []
		};
		if (data.props){
			$.each(data.props, function (i) {
				if (data.props[i] == 'offsetTop'){
					data.vals[i] = el.offset().top;
				}
				else if (data.props[i] == 'offsetLeft'){
					data.vals[i] = el.offset().left;
				}
				else {
					data.vals[i] = el.css(data.props[i]);
				}
			});
		}
		el.data(id, data);
	});

	function __watcher(id) {
		var el = $(this);
		var w = el.data(id);

		var changed = false;
		var i = 0;
		if (w && w.props){
			for(i; i < w.props.length; i++){
				if (w.props[i] == 'offsetTop'){
					var newVal = el.offset().top;
				}
				else if (w.props[i] == 'offsetLeft'){
					var newVal = el.offset().left;
				}
				else {
					var newVal = el.css(w.props[i]);
				}
				if (w.vals[i] != newVal){
					w.vals[i] = newVal;
					changed = true;
					break;
				}
			}
			if (changed && w.func){
				var _t = this;
				w.func.apply(_t, [w, i])
			}
		}
	}
}
$.fn.unwatch = function (id) {
	this.each(function () {
		var w = $(this).data(id);
		var el = $(this);
		el.removeData(id);

		if (typeof (this.onpropertychange) == "object"){
			el.unbind("propertychange." + w.id);
		}
		else if ($.browser.mozilla){
			el.unbind("DOMAttrModified." + w.id);
		}
		else {
			clearInterval(w.id);
		}
	});
	return this;
}