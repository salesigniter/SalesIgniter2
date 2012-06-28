/*
 http://www.JSON.org/json2.js
 2011-01-18

 Public Domain.

 NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.

 See http://www.JSON.org/js.html


 This code should be minified before deployment.
 See http://javascript.crockford.com/jsmin.html

 USE YOUR OWN COPY. IT IS EXTREMELY UNWISE TO LOAD CODE FROM SERVERS YOU DO
 NOT CONTROL.


 This file creates a global JSON object containing two methods: stringify
 and parse.

 JSON.stringify(value, replacer, space)
 value       any JavaScript value, usually an object or array.

 replacer    an optional parameter that determines how object
 values are stringified for objects. It can be a
 function or an array of strings.

 space       an optional parameter that specifies the indentation
 of nested structures. If it is omitted, the text will
 be packed without extra whitespace. If it is a number,
 it will specify the number of spaces to indent at each
 level. If it is a string (such as '\t' or '&nbsp;'),
 it contains the characters used to indent at each level.

 This method produces a JSON text from a JavaScript value.

 When an object value is found, if the object contains a toJSON
 method, its toJSON method will be called and the result will be
 stringified. A toJSON method does not serialize: it returns the
 value represented by the name/value pair that should be serialized,
 or undefined if nothing should be serialized. The toJSON method
 will be passed the key associated with the value, and this will be
 bound to the value

 For example, this would serialize Dates as ISO strings.

 Date.prototype.toJSON = function (key) {
 function f(n) {
 // Format integers to have at least two digits.
 return n < 10 ? '0' + n : n;
 }

 return this.getUTCFullYear()   + '-' +
 f(this.getUTCMonth() + 1) + '-' +
 f(this.getUTCDate())      + 'T' +
 f(this.getUTCHours())     + ':' +
 f(this.getUTCMinutes())   + ':' +
 f(this.getUTCSeconds())   + 'Z';
 };

 You can provide an optional replacer method. It will be passed the
 key and value of each member, with this bound to the containing
 object. The value that is returned from your method will be
 serialized. If your method returns undefined, then the member will
 be excluded from the serialization.

 If the replacer parameter is an array of strings, then it will be
 used to select the members to be serialized. It filters the results
 such that only members with keys listed in the replacer array are
 stringified.

 Values that do not have JSON representations, such as undefined or
 functions, will not be serialized. Such values in objects will be
 dropped; in arrays they will be replaced with null. You can use
 a replacer function to replace those with JSON values.
 JSON.stringify(undefined) returns undefined.

 The optional space parameter produces a stringification of the
 value that is filled with line breaks and indentation to make it
 easier to read.

 If the space parameter is a non-empty string, then that string will
 be used for indentation. If the space parameter is a number, then
 the indentation will be that many spaces.

 Example:

 text = JSON.stringify(['e', {pluribus: 'unum'}]);
 // text is '["e",{"pluribus":"unum"}]'


 text = JSON.stringify(['e', {pluribus: 'unum'}], null, '\t');
 // text is '[\n\t"e",\n\t{\n\t\t"pluribus": "unum"\n\t}\n]'

 text = JSON.stringify([new Date()], function (key, value) {
 return this[key] instanceof Date ?
 'Date(' + this[key] + ')' : value;
 });
 // text is '["Date(---current time---)"]'


 JSON.parse(text, reviver)
 This method parses a JSON text to produce an object or array.
 It can throw a SyntaxError exception.

 The optional reviver parameter is a function that can filter and
 transform the results. It receives each of the keys and values,
 and its return value is used instead of the original value.
 If it returns what it received, then the structure is not modified.
 If it returns undefined then the member is deleted.

 Example:

 // Parse the text. Values that look like ISO date strings will
 // be converted to Date objects.

 myData = JSON.parse(text, function (key, value) {
 var a;
 if (typeof value === 'string') {
 a =
 /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)Z$/.exec(value);
 if (a) {
 return new Date(Date.UTC(+a[1], +a[2] - 1, +a[3], +a[4],
 +a[5], +a[6]));
 }
 }
 return value;
 });

 myData = JSON.parse('["Date(09/09/2001)"]', function (key, value) {
 var d;
 if (typeof value === 'string' &&
 value.slice(0, 5) === 'Date(' &&
 value.slice(-1) === ')') {
 d = new Date(value.slice(5, -1));
 if (d) {
 return d;
 }
 }
 return value;
 });


 This is a reference implementation. You are free to copy, modify, or
 redistribute.
 */

/*jslint evil: true, strict: false, regexp: false */

/*members "", "\b", "\t", "\n", "\f", "\r", "\"", JSON, "\\", apply,
 call, charCodeAt, getUTCDate, getUTCFullYear, getUTCHours,
 getUTCMinutes, getUTCMonth, getUTCSeconds, hasOwnProperty, join,
 lastIndex, length, parse, prototype, push, replace, slice, stringify,
 test, toJSON, toString, valueOf
 */


// Create a JSON object only if one does not already exist. We create the
// methods in a closure to avoid creating global variables.

var JSON;
if (!JSON) {
	JSON = {};
}

(function () {
	"use strict";

	function f(n) {
		// Format integers to have at least two digits.
		return n < 10 ? '0' + n : n;
	}

	if (typeof Date.prototype.toJSON !== 'function') {

		Date.prototype.toJSON = function (key) {

			return isFinite(this.valueOf()) ?
				this.getUTCFullYear()     + '-' +
					f(this.getUTCMonth() + 1) + '-' +
					f(this.getUTCDate())      + 'T' +
					f(this.getUTCHours())     + ':' +
					f(this.getUTCMinutes())   + ':' +
					f(this.getUTCSeconds())   + 'Z' : null;
		};

		String.prototype.toJSON      =
			Number.prototype.toJSON  =
				Boolean.prototype.toJSON = function (key) {
					return this.valueOf();
				};
	}

	var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
		escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
		gap,
		indent,
		meta = {    // table of character substitutions
			'\b': '\\b',
			'\t': '\\t',
			'\n': '\\n',
			'\f': '\\f',
			'\r': '\\r',
			'"' : '\\"',
			'\\': '\\\\'
		},
		rep;


	function quote(string) {

// If the string contains no control characters, no quote characters, and no
// backslash characters, then we can safely slap some quotes around it.
// Otherwise we must also replace the offending characters with safe escape
// sequences.

		escapable.lastIndex = 0;
		return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
			var c = meta[a];
			return typeof c === 'string' ? c :
				'\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
		}) + '"' : '"' + string + '"';
	}


	function str(key, holder) {

// Produce a string from holder[key].

		var i,          // The loop counter.
			k,          // The member key.
			v,          // The member value.
			length,
			mind = gap,
			partial,
			value = holder[key];

// If the value has a toJSON method, call it to obtain a replacement value.

		if (value && typeof value === 'object' &&
			typeof value.toJSON === 'function') {
			value = value.toJSON(key);
		}

// If we were called with a replacer function, then call the replacer to
// obtain a replacement value.

		if (typeof rep === 'function') {
			value = rep.call(holder, key, value);
		}

// What happens next depends on the value's type.

		switch (typeof value) {
			case 'string':
				return quote(value);

			case 'number':

// JSON numbers must be finite. Encode non-finite numbers as null.

				return isFinite(value) ? String(value) : 'null';

			case 'boolean':
			case 'null':

// If the value is a boolean or null, convert it to a string. Note:
// typeof null does not produce 'null'. The case is included here in
// the remote chance that this gets fixed someday.

				return String(value);

// If the type is 'object', we might be dealing with an object or an array or
// null.

			case 'object':

// Due to a specification blunder in ECMAScript, typeof null is 'object',
// so watch out for that case.

				if (!value) {
					return 'null';
				}

// Make an array to hold the partial results of stringifying this object value.

				gap += indent;
				partial = [];

// Is the value an array?

				if (Object.prototype.toString.apply(value) === '[object Array]') {

// The value is an array. Stringify every element. Use null as a placeholder
// for non-JSON values.

					length = value.length;
					for (i = 0; i < length; i += 1) {
						partial[i] = str(i, value) || 'null';
					}

// Join all of the elements together, separated with commas, and wrap them in
// brackets.

					v = partial.length === 0 ? '[]' : gap ?
						'[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']' :
						'[' + partial.join(',') + ']';
					gap = mind;
					return v;
				}

// If the replacer is an array, use it to select the members to be stringified.

				if (rep && typeof rep === 'object') {
					length = rep.length;
					for (i = 0; i < length; i += 1) {
						k = rep[i];
						if (typeof k === 'string') {
							v = str(k, value);
							if (v) {
								partial.push(quote(k) + (gap ? ': ' : ':') + v);
							}
						}
					}
				} else {

// Otherwise, iterate through all of the keys in the object.

					for (k in value) {
						if (Object.hasOwnProperty.call(value, k)) {
							v = str(k, value);
							if (v) {
								partial.push(quote(k) + (gap ? ': ' : ':') + v);
							}
						}
					}
				}

// Join all of the member texts together, separated with commas,
// and wrap them in braces.

				v = partial.length === 0 ? '{}' : gap ?
					'{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}' :
					'{' + partial.join(',') + '}';
				gap = mind;
				return v;
		}
	}

// If the JSON object does not yet have a stringify method, give it one.

	if (typeof JSON.stringify !== 'function') {
		JSON.stringify = function (value, replacer, space) {

// The stringify method takes a value and an optional replacer, and an optional
// space parameter, and returns a JSON text. The replacer can be a function
// that can replace values, or an array of strings that will select the keys.
// A default replacer method can be provided. Use of the space parameter can
// produce text that is more easily readable.

			var i;
			gap = '';
			indent = '';

// If the space parameter is a number, make an indent string containing that
// many spaces.

			if (typeof space === 'number') {
				for (i = 0; i < space; i += 1) {
					indent += ' ';
				}

// If the space parameter is a string, it will be used as the indent string.

			} else if (typeof space === 'string') {
				indent = space;
			}

// If there is a replacer, it must be a function or an array.
// Otherwise, throw an error.

			rep = replacer;
			if (replacer && typeof replacer !== 'function' &&
				(typeof replacer !== 'object' ||
					typeof replacer.length !== 'number')) {
				throw new Error('JSON.stringify');
			}

// Make a fake root object containing our value under the key of ''.
// Return the result of stringifying the value.

			return str('', {'': value});
		};
	}


// If the JSON object does not yet have a parse method, give it one.

	if (typeof JSON.parse !== 'function') {
		JSON.parse = function (text, reviver) {

// The parse method takes a text and an optional reviver function, and returns
// a JavaScript value if the text is a valid JSON text.

			var j;

			function walk(holder, key) {

// The walk method is used to recursively walk the resulting structure so
// that modifications can be made.

				var k, v, value = holder[key];
				if (value && typeof value === 'object') {
					for (k in value) {
						if (Object.hasOwnProperty.call(value, k)) {
							v = walk(value, k);
							if (v !== undefined) {
								value[k] = v;
							} else {
								delete value[k];
							}
						}
					}
				}
				return reviver.call(holder, key, value);
			}


// Parsing happens in four stages. In the first stage, we replace certain
// Unicode characters with escape sequences. JavaScript handles many characters
// incorrectly, either silently deleting them, or treating them as line endings.

			text = String(text);
			cx.lastIndex = 0;
			if (cx.test(text)) {
				text = text.replace(cx, function (a) {
					return '\\u' +
						('0000' + a.charCodeAt(0).toString(16)).slice(-4);
				});
			}

// In the second stage, we run the text against regular expressions that look
// for non-JSON patterns. We are especially concerned with '()' and 'new'
// because they can cause invocation, and '=' because it can cause mutation.
// But just to be safe, we want to reject all unexpected forms.

// We split the second stage into 4 regexp operations in order to work around
// crippling inefficiencies in IE's and Safari's regexp engines. First we
// replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
// replace all simple value tokens with ']' characters. Third, we delete all
// open brackets that follow a colon or comma or that begin the text. Finally,
// we look to see that the remaining characters are only whitespace or ']' or
// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

			if (/^[\],:{}\s]*$/
				.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
				.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
				.replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

// In the third stage we use the eval function to compile the text into a
// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
// in JavaScript: it can begin a block or an object literal. We wrap the text
// in parens to eliminate the ambiguity.

				j = eval('(' + text + ')');

// In the optional fourth stage, we recursively walk the new structure, passing
// each name/value pair to a reviver function for possible transformation.

				return typeof reviver === 'function' ?
					walk({'': j}, '') : j;
			}

// If the text is not JSON parseable, then a SyntaxError is thrown.

			throw new SyntaxError('JSON.parse');
		};
	}
}());

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

function htmlspecialchars_decode (string, quote_style) {
	// Convert special HTML entities back to characters
	//
	// version: 1109.2015
	// discuss at: http://phpjs.org/functions/htmlspecialchars_decode
	// +   original by: Mirek Slugen
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   bugfixed by: Mateusz "loonquawl" Zalega
	// +      input by: ReverseSyntax
	// +      input by: Slawomir Kaniecki
	// +      input by: Scott Cariss
	// +      input by: Francois
	// +   bugfixed by: Onno Marsman
	// +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   bugfixed by: Brett Zamir (http://brett-zamir.me)
	// +      input by: Ratheous
	// +      input by: Mailfaker (http://www.weedem.fr/)
	// +      reimplemented by: Brett Zamir (http://brett-zamir.me)
	// +    bugfixed by: Brett Zamir (http://brett-zamir.me)
	// *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
	// *     returns 1: '<p>this -> &quot;</p>'
	// *     example 2: htmlspecialchars_decode("&amp;quot;");
	// *     returns 2: '&quot;'
	var optTemp = 0,
		i = 0,
		noquotes = false;
	if (typeof quote_style === 'undefined') {
		quote_style = 2;
	}
	string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
	var OPTS = {
		'ENT_NOQUOTES': 0,
		'ENT_HTML_QUOTE_SINGLE': 1,
		'ENT_HTML_QUOTE_DOUBLE': 2,
		'ENT_COMPAT': 2,
		'ENT_QUOTES': 3,
		'ENT_IGNORE': 4
	};
	if (quote_style === 0) {
		noquotes = true;
	}
	if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
		quote_style = [].concat(quote_style);
		for (i = 0; i < quote_style.length; i++) {
			// Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
			if (OPTS[quote_style[i]] === 0) {
				noquotes = true;
			} else if (OPTS[quote_style[i]]) {
				optTemp = optTemp | OPTS[quote_style[i]];
			}
		}
		quote_style = optTemp;
	}
	if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
		string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
		// string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
	}
	if (!noquotes) {
		string = string.replace(/&quot;/g, '"');
	}
	// Put this in last place to avoid escape being double-decoded
	string = string.replace(/&amp;/g, '&');

	return string;
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
					name: k,
					value: v
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
