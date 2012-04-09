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

function urldecode(str){
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

function parse_str (str, array){
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
		key   = fixStr(tmp[0]);
		value = fixStr(tmp[1]);
		while(key.charAt(0) === ' '){
			key = key.substr(1);
		}
		if (key.indexOf('\0') !== -1){
			key = key.substr(0, key.indexOf('\0'));
		}
		if (key && key.charAt(0) !== '['){
			keys    = [];
			bracket = 0;
			for(j = 0; j < key.length; j++){
				if (key.charAt(j) === '[' && !bracket){
					bracket = j + 1;
				}else if (key.charAt(j) === ']'){
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
			for(j=0; j<keys[0].length; j++){
				chr = keys[0].charAt(j);
				if (chr === ' ' || chr === '.' || chr === '['){
					keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1);
				}
				if (chr === '[') {
					break;
				}
			}
			evalStr = 'array';
			for(j=0; j<keys.length; j++){
				key = keys[j];
				if ((key !== '' && key !== ' ') || j === 0){
					key = "'" + key + "'";
				}else{
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

function number_format (number, decimals, dec_point, thousands_sep) {
	// http://kevin.vanzonneveld.net
	// +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +     bugfix by: Michael White (http://getsprink.com)
	// +     bugfix by: Benjamin Lupton
	// +     bugfix by: Allan Jensen (http://www.winternet.no)
	// +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// +     bugfix by: Howard Yeend
	// +    revised by: Luke Smith (http://lucassmith.name)
	// +     bugfix by: Diogo Resende
	// +     bugfix by: Rival
	// +      input by: Kheang Hok Chin (http://www.distantia.ca/)
	// +   improved by: davook
	// +   improved by: Brett Zamir (http://brett-zamir.me)
	// +      input by: Jay Klehr
	// +   improved by: Brett Zamir (http://brett-zamir.me)
	// +      input by: Amir Habibi (http://www.residence-mixte.com/)
	// +     bugfix by: Brett Zamir (http://brett-zamir.me)
	// +   improved by: Theriault
	// +      input by: Amirouche
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// *     example 1: number_format(1234.56);
	// *     returns 1: '1,235'
	// *     example 2: number_format(1234.56, 2, ',', ' ');
	// *     returns 2: '1 234,56'
	// *     example 3: number_format(1234.5678, 2, '.', '');
	// *     returns 3: '1234.57'
	// *     example 4: number_format(67, 2, ',', '.');
	// *     returns 4: '67,00'
	// *     example 5: number_format(1000);
	// *     returns 5: '1,000'
	// *     example 6: number_format(67.311, 2);
	// *     returns 6: '67.31'
	// *     example 7: number_format(1000.55, 1);
	// *     returns 7: '1,000.6'
	// *     example 8: number_format(67000, 5, ',', '.');
	// *     returns 8: '67.000,00000'
	// *     example 9: number_format(0.9, 0);
	// *     returns 9: '1'
	// *    example 10: number_format('1.20', 2);
	// *    returns 10: '1.20'
	// *    example 11: number_format('1.20', 4);
	// *    returns 11: '1.2000'
	// *    example 12: number_format('1.2000', 3);
	// *    returns 12: '1.200'
	// *    example 13: number_format('1 000,50', 2, '.', ' ');
	// *    returns 13: '100 050.00'
	// Strip all characters but numerical ones.
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function (n, prec) {
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if ((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}

function compareGetVar(varName, compareVal, compareType){
	var param = js_get_url_param(varName);
	if (param !== false){
		switch(compareType){
			case 'isEqual':
				return (param.value == compareVal);
				break;
			case 'isNotEqual':
				return (param.value != compareVal);
				break;
			case 'greaterThan':
				return (param.value > compareVal);
				break;
			case 'greaterOrEqual':
				return (param.value >= compareVal);
				break;
			case 'lessThan':
				return (param.value < compareVal);
				break;
			case 'lessOrEqual':
				return (param.value <= compareVal);
				break;
			case 'isset':
				return true;
				break;
			default:
				return false;
				break;
		}
	}else{
		return false;
	}
}

function js_get_url_param(name){
	var getVars = {}, returnVal = false;
	if (window.location.href.indexOf('?') > -1){
		parse_str(window.location.href.slice(window.location.href.indexOf('?') + 1), getVars);
		$.each(getVars, function (k, v){
			if (k == name){
				returnVal = {
					name: hash[0],
					value: hash[1]
				};
				return;
			}
		});
	}
	return returnVal;
}

function js_get_all_get_params(exclude){
	exclude = exclude || [];

	var get_url = [];
	var getVars = {};
	if (window.location.href.indexOf('?') > -1){
		parse_str(window.location.href.slice(window.location.href.indexOf('?') + 1), getVars);
		$.each(getVars, function (k, v){
			if (k != sessionName && k != 'error' && $.inArray(k, exclude) == -1){
				get_url.push(k + '=' + v);
			}
		});
	}

	return (get_url.length > 0 ? get_url.join('&') + '&' : '');
}

function js_redirect(url){
	window.location = url;
}

function js_href_link(page, params, connection){
	connection = connection || 'NONSSL';
	params = params || '';
	if (page == '') {
		alert('Error:: Unable to determine the page link!' + "\n\n" + 'Function used: js_href_link(\'' + page + '\', \'' + params + '\', \'' + connection + '\')');
	}

	var link;
	link = 'http://' + jsConfig.get('SERVER_NAME') + jsConfig.get('DIR_WS_CATALOG');
	if (connection == 'SSL') {
		if (jsConfig.get('ENABLE_SSL') == 'true') {
			link = 'https://' + jsConfig.get('SERVER_NAME') + jsConfig.get('DIR_WS_CATALOG');
		}
	}

	if (params == '') {
		link = link + page + '?' + SID;
	} else {
		link = link + page + '?' + params + '&' + SID;
	}

	while ( (link.substr(-1) == '&') || (link.substr(-1) == '?') ) link = link.substr(0, link.length - 1);

	return link;
}

function js_app_link(params, connection){
	connection = connection || request_type || 'SSL';
	params = params || '';

	var protocol = 'http';
	if (connection == 'SSL') {
		if (jsConfig.get('ENABLE_SSL') == 'true') {
			protocol = protocol + 's';
		}
	}
	var link = protocol + '://' + jsConfig.get('SERVER_NAME') + jsConfig.get('DIR_WS_CATALOG');

	if (params == '') {
		link = link + 'application.php?' + SID;
	} else {
		var paramsObj = {};
		parse_str(params, paramsObj);
		if (paramsObj.appExt){
			link = link + paramsObj.appExt + '/';
		}
		link = link + paramsObj.app + '/' + paramsObj.appPage + '.php';

		var linkParams = [];
		var requireSID = false;
		$.each(paramsObj, function (k, v){
			if (k == 'app' || k == 'appPage' || k == 'appExt') return;
			if (k == 'rType' && v == 'ajax') requireSID = true;
			linkParams.push(k + '=' + v);
		});

		if (linkParams.length > 0){
			link = link + '?' + linkParams.join('&') + '&' + (requireSID === true ? sessionName + '=' + sessionId : SID);
		}else{
			link = link + '?' + (requireSID === true ? sessionName + '=' + sessionId : SID);
		}
	}

	while ( (link.substr(-1) == '&') || (link.substr(-1) == '?') ) link = link.substr(0, link.length - 1);

	return link;
}

function js_catalog_app_link(params, connection){
	return js_app_link(params, connection);
}

function showAjaxLoader($el, size, placement){
	if($el.position() != null){
		if (!$el.data('ajaxOverlay')){
			var $overlay = $('<div></div>').addClass('ui-widget-overlay').css({
				position: 'absolute',
				width: $el.outerWidth(),
				height: $el.outerHeight(),
				left: $el.position().left,
				top: $el.position().top,
				zIndex: $el.zIndex() + 1
			});
			if (placement && placement == 'append'){
				$overlay.appendTo($el);
			}else{
				$overlay.insertAfter($el);
			}
			var $ajaxLoader;
			if (placement == 'dialog'){
				$ajaxLoader = $('<div></div>').addClass('ui-ajax-loader-back').css({
					position: 'absolute',
					left: $el.position().left,
					top: $el.position().top,
					zIndex: $overlay.zIndex() + 1
				});
				var $ajaxLoader2 = $('<div></div>').addClass('ui-ajax-loader').addClass('ui-ajax-loader-' + size).addClass('ui-ajax-loader-dialog');
				$ajaxLoader2.appendTo($ajaxLoader);
				//$ajaxLoader.css({top:'50%',left:'50%',margin:'-'+($ajaxLoader.height() / 2)+'px 0 0 -'+($ajaxLoader.width() / 2)+'px'});
			}else{
				$ajaxLoader = $('<div></div>').addClass('ui-ajax-loader').addClass('ui-ajax-loader-' + size).css({
					position: 'absolute',
					left: $el.position().left,
					top: $el.position().top,
					zIndex: $overlay.zIndex() + 1
				});
			}

			if (placement && placement == 'append'){
				$ajaxLoader.appendTo($el);
			}else{
				$ajaxLoader.insertAfter($el);
			}

			$ajaxLoader.position({
				my: 'center center',
				at: 'center center',
				offset: '0 0',
				of: $overlay,
				collision: 'fit'
			});

			$el.data('ajaxOverlay', $overlay);
			$el.data('ajaxLoader', $ajaxLoader);
		}


		/*var $curOverlay = $el.data('ajaxOverlay');
		 if ($curOverlay.outerWidth() != $el.outerWidth() || $curOverlay.outerHeight() != $el.outerHeight()){
		 $curOverlay.css({
		 height: $el.outerHeight(),
		 width: $el.outerWidth()
		 });
		 $el.data('ajaxOverlay', $curOverlay);
		 }*/

		$el.data('ajaxOverlay').show();
		$el.data('ajaxLoader').show();
	}
}

function hideAjaxLoader($el){
	if ($el.data('ajaxOverlay')){
		$el.data('ajaxOverlay').hide();
		$el.data('ajaxLoader').hide();
	}
}

function removeAjaxLoader($el){
	if ($el.data('ajaxOverlay')){
		$el.data('ajaxOverlay').remove();
		$el.removeData('ajaxOverlay');
	}
	if ($el.data('ajaxLoader')){
		$el.data('ajaxLoader').remove();
		$el.removeData('ajaxLoader');
	}
}

function popupWindow(url, w, h, p) {
	$('<div class="popupWindow"></div>').dialog({
		autoOpen: true,
		width: w || 'auto',
		height: h || 'auto',
		position: p || 'center',
		close: function (e, ui){
			$(this).dialog('destroy').remove();
		},
		open: function (e, ui){
			$(e.target).html('<div class="ui-ajax-loader ui-ajax-loader-xlarge" style="margin-left:auto;margin-right:auto;"></div>');
			$.ajax({
				cache: false,
				url: url,
				dataType: 'html',
				success: function (data){
					$(e.target).html(data);
				}
			});
		}
	});
	return false;
}

function alertWindow(message){
	$('<div class="alertWindow"></div>').dialog({
		autoOpen: true,
		modal: true,
		width: 'auto',
		height: 'auto',
		position: 'center',
		title: 'Alert',
		close: function (e, ui){
			$(this).dialog('destroy').remove();
		},
		open: function (e, ui){
			$(e.target).html(message);
		}
	});
}

function showToolTip(settings){
	var elOffset = settings.el.offset();

	var $toolTip = $('<div>')
		.addClass('ui-widget')
		.addClass('ui-widget-content')
		.addClass('ui-corner-all')
		.css({
			position: 'absolute',
			left: elOffset.left,
			top: elOffset.top,
			zIndex: 9999,
			padding: '5px',
			whiteSpace: 'nowrap'
		})
		.html(settings.tipText)
		.appendTo($(document.body));

	$toolTip.css('left', (elOffset.left + settings.el.width()));
	$toolTip.css('top', (elOffset.top - $toolTip.height()));

	//alert((settings.offsetLeft + 200) + ' >= ' + $(window).width());
	if ((elOffset.left + 200) >= $(window).width()){
		$toolTip.css('left', (elOffset.left - $toolTip.width()));
	}
	if ((elOffset.top - $toolTip.height()) <= 0){
		$toolTip.css('top', (elOffset.top + settings.el.height() + $toolTip.height()));
	}
	return $toolTip;
}

function SetFocus(TargetFormName) {
	var target = 0;
	if (TargetFormName != "") {
		for (i=0; i<document.forms.length; i++) {
			if (document.forms[i].name == TargetFormName) {
				target = i;
				break;
			}
		}
	}

	var TargetForm = document.forms[target];

	for (i=0; i<TargetForm.length; i++) {
		if ( (TargetForm.elements[i].type != "image") && (TargetForm.elements[i].type != "hidden") && (TargetForm.elements[i].type != "reset") && (TargetForm.elements[i].type != "submit") ) {
			TargetForm.elements[i].focus();

			if ( (TargetForm.elements[i].type == "text") || (TargetForm.elements[i].type == "password") ) {
				TargetForm.elements[i].select();
			}

			break;
		}
	}
}

function RemoveFormatString(TargetElement, FormatString) {
	if (TargetElement.value == FormatString) {
		TargetElement.value = "";
	}

	TargetElement.select();
}

function CheckDateRange(from, to) {
	if (Date.parse(from.value) <= Date.parse(to.value)) {
		return true;
	} else {
		return false;
	}
}

function IsValidDate(DateToCheck, FormatString) {
	var strDateToCheck;
	var strDateToCheckArray;
	var strFormatArray;
	var strFormatString;
	var strDay;
	var strMonth;
	var strYear;
	var intday;
	var intMonth;
	var intYear;
	var intDateSeparatorIdx = -1;
	var intFormatSeparatorIdx = -1;
	var strSeparatorArray = new Array("-"," ","/",".");
	var strMonthArray = new Array("jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec");
	var intDaysArray = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	strDateToCheck = DateToCheck.toLowerCase();
	strFormatString = FormatString.toLowerCase();

	if (strDateToCheck.length != strFormatString.length) {
		return false;
	}

	for (i=0; i<strSeparatorArray.length; i++) {
		if (strFormatString.indexOf(strSeparatorArray[i]) != -1) {
			intFormatSeparatorIdx = i;
			break;
		}
	}

	for (i=0; i<strSeparatorArray.length; i++) {
		if (strDateToCheck.indexOf(strSeparatorArray[i]) != -1) {
			intDateSeparatorIdx = i;
			break;
		}
	}

	if (intDateSeparatorIdx != intFormatSeparatorIdx) {
		return false;
	}

	if (intDateSeparatorIdx != -1) {
		strFormatArray = strFormatString.split(strSeparatorArray[intFormatSeparatorIdx]);
		if (strFormatArray.length != 3) {
			return false;
		}

		strDateToCheckArray = strDateToCheck.split(strSeparatorArray[intDateSeparatorIdx]);
		if (strDateToCheckArray.length != 3) {
			return false;
		}

		for (i=0; i<strFormatArray.length; i++) {
			if (strFormatArray[i] == 'mm' || strFormatArray[i] == 'mmm') {
				strMonth = strDateToCheckArray[i];
			}

			if (strFormatArray[i] == 'dd') {
				strDay = strDateToCheckArray[i];
			}

			if (strFormatArray[i] == 'yyyy') {
				strYear = strDateToCheckArray[i];
			}
		}
	} else {
		if (FormatString.length > 7) {
			if (strFormatString.indexOf('mmm') == -1) {
				strMonth = strDateToCheck.substring(strFormatString.indexOf('mm'), 2);
			} else {
				strMonth = strDateToCheck.substring(strFormatString.indexOf('mmm'), 3);
			}

			strDay = strDateToCheck.substring(strFormatString.indexOf('dd'), 2);
			strYear = strDateToCheck.substring(strFormatString.indexOf('yyyy'), 2);
		} else {
			return false;
		}
	}

	if (strYear.length != 4) {
		return false;
	}

	intday = parseInt(strDay, 10);
	if (isNaN(intday)) {
		return false;
	}
	if (intday < 1) {
		return false;
	}

	intMonth = parseInt(strMonth, 10);
	if (isNaN(intMonth)) {
		for (i=0; i<strMonthArray.length; i++) {
			if (strMonth == strMonthArray[i]) {
				intMonth = i+1;
				break;
			}
		}
		if (isNaN(intMonth)) {
			return false;
		}
	}
	if (intMonth > 12 || intMonth < 1) {
		return false;
	}

	intYear = parseInt(strYear, 10);
	if (isNaN(intYear)) {
		return false;
	}
	if (IsLeapYear(intYear) == true) {
		intDaysArray[1] = 29;
	}

	if (intday > intDaysArray[intMonth - 1]) {
		return false;
	}

	return true;
}

function IsLeapYear(intYear) {
	if (intYear % 100 == 0) {
		if (intYear % 400 == 0) {
			return true;
		}
	} else {
		if ((intYear % 4) == 0) {
			return true;
		}
	}

	return false;
}
