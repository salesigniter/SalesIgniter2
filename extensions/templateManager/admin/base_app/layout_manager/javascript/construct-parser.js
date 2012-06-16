/* construct-parser.js */
// simple function for parsing element and its children recursively
function parseElement(el){
	if ($(el).data('styles')){
		var stylesInfo = $(el).data('styles');
		$(el).attr('data-styles', JSON.stringify(stylesInfo));
	}

	if ($(el).data('inputs')){
		var inputsInfo = $(el).data('inputs');
		$(el).attr('data-inputs', JSON.stringify(inputsInfo));
	}

	if ($(el).data('widget_settings')){
		var widgetSettings = $(el).data('widget_settings');
		$(el).attr('data-widget_settings', JSON.stringify(widgetSettings));
	}

	if ($(el).data('widget_code')){
		$(el).attr('data-widget_code', $(el).data('widget_code'));
	}

	if ($(el).data('container_id')){
		$(el).attr('data-container_id', $(el).data('container_id'));
	}

	if ($(el).data('column_id')){
		$(el).attr('data-column_id', $(el).data('column_id'));
	}

	if ($(el).data('widget_id')){
		$(el).attr('data-widget_id', $(el).data('widget_id'));
	}

	var sortOrder = 1;
	$(el).children().each(function(){
		parseElement(this);
		$(this).attr('data-sort_order', sortOrder);
		sortOrder++;
	});
}

// wrapper function to process all elements in #construct
function getMarkup(){
	pageMarkup = $('#construct').clone(true);
	parseElement(pageMarkup);
	return $('<div></div>').append(pageMarkup).html();
}

// defining custom trim functions (found in comments at http://www.codestore.net/store.nsf/unid/BLOG-20060313, thanks John Z Marshall)
String.prototype.trim = function() {
	return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,"");
}
String.prototype.fulltrim = function() {
	return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,"").replace(/\s+/g," ");
}
