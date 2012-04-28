$(document).ready(function () {
	$('.checkoutFormButton').button();

	$('.ui-button').each(function () {
		var disable = false;
		if ($(this).hasClass('ui-state-disabled')){
			disable = true;
		}
		$(this).button({
			disabled : disable
		}).click(function (e) {
				if ($(this).hasClass('ui-state-disabled')){
					e.preventDefault();
					return false;
				}
			});
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
						visibility : 'visible',
						left : offSetLeft,
						zIndex : 9999
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
		$spanObj.addClass('ui-state-hover').addClass('ui-corner-all').css({
			cursor : 'default',
			fontWeight : 'bold'
		});

		var offSet = $(headingBlock).offset();
		$('div:first', $(headingBlock)).each(function () {
			$(this).css({
				position : 'absolute',
				width : 'auto',
				top : offSet.top + $(headingBlock).parent().height(),
				left : offSet.left,
				zIndex : 9998
			}).show();

			$('ul:first', $(this)).css('visibility', 'visible');
		});
	}, function () {
		var $spanObj = $('.headerMenuHeading', this);
		$spanObj.removeClass('ui-state-hover').css({
			cursor : 'default'
		});
		$('.ui-menu-flyout:first', $(this)).hide();
	});

	$('#categoriesBoxMenu').accordion({
		header : 'h3',
		collapsible : true,
		autoHeight : false,
		active : $('.currentCategory', $('#categoriesBoxMenu')),
		icons : {
			header : 'ui-icon-circle-triangle-s',
			headerSelected : 'ui-icon-circle-triangle-n'
		}
	});

	$('a', $('#categoriesBoxMenu')).each(function () {
		var $link = $(this);
		$($link.parent()).hover(
			function () {
				$link.css('cursor', 'pointer').addClass('ui-state-hover');

				var linkOffset = $link.parent().offset();
				var boxOffset = $('#categoriesBoxMenu').offset();
				if ($('ul', $(this)).size() > 0){
					var $menuList = $('ul:first', $(this));
					$menuList.css({
						position : 'absolute',
						top : $link.parent().position().top,
						left : $link.parent().position().left + $link.parent().innerWidth() - 5,
						zIndex : 9999
					}).show();
				}
			},
			function () {
				$link.css({cursor : 'default'}).removeClass('ui-state-hover');

				if ($('ul', this).size() > 0){
					$('ul:first', this).hide();
				}
			}).click(function () {
				document.location = $('a:first', this).attr('href');
			});
	});

	$('a[type=button], button').each(function () {
		var disable = false;
		if ($(this).hasClass('ui-state-disabled')){
			disable = true;
		}
		$(this).button({
			disabled : disable
		}).click(function (e) {
				if ($(this).hasClass('ui-state-disabled')){
					e.preventDefault();
					return false;
				}
			});
	});

	$('.searchShowMoreLink a').click(function () {
		$('li', $(this).parent().parent()).show();
		$(this).parent().remove();
		return false;
	});

	$('.phpTraceView').click(function (e) {
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

	$('[tooltip]').live('mouseover mouseout click', function (e) {
		if (e.type == 'mouseover'){
			this.Tooltip = showToolTip({
				el : $(this),
				tipText : $(this).attr('tooltip')
			});
		}
		else {
			this.Tooltip.remove();
		}
	});

	$('[required=true]').each(function () {
		$('<a style="display: inline-block;" tooltip="Input Required" class="ui-icon ui-icon-gear ui-icon-required"></a>').insertAfter(this);
	});

	if ($.keyboard){
		$('body').keyboard({
			keyboard : 'qwerty',
			plugin : 'form'
		});
	}
});
