/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.contentsCss = [DIR_WS_CATALOG + 'extensions/templateManager/catalog/globalFiles/stylesheet.php'];
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	config.filebrowserBrowseUrl = jsConfig.get('CKEDITOR_FILEBROWSER_URL') + '?filesSource=' + jsConfig.get('DIR_FS_CATALOG_TEMPLATES') + '&' + sessionName + '=' + sessionId
	config.scayt_autoStartup = false;
	config.disableNativeSpellChecker = false;

	config.extraPlugins = 'streaming';
	config.toolbar_Full.push(['Streaming']);
	config.toolbar_Basic.push(['Streaming']);
	config.toolbar_Simple = [
		['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-'],
		['Undo', 'Redo', '-'],
		['Image', 'Table', 'SpecialChar', 'PageBreak'],
		'/',
		['Styles', 'Format'],
		['Bold', 'Italic', 'Strike'],
		['NumberedList', 'BulletedList', '-'],
		['Link', 'Unlink', 'Anchor']

	];

	/* Allow php code in fck - not needed right now
		config.protectedSource.push( /<\?[\s\S]*?\?>/g ) ;
		config.protectedSource.push( /<\?php[\s\S]*?\?>/g ) ;
	*/
};
