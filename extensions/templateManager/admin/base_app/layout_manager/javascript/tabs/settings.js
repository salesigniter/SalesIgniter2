(function($, undefined) {

	$.extend($.ui.LayoutDesigner.prototype.tabs, {
		settings: {
			init: function () {
				var $Tab = $('#mainTabPanel').find('#settings');
				var parentCls = this;
				var thisCls = parentCls.tabs.settings;
				var InputVals = parentCls.getElInputData();

				var values = {
					id: '',
					width: '30',
					width_unit: 'px',
					height: '30',
					height_unit: 'auto'
				};

				$.extend(true, values, InputVals);

				$Tab.find('input[name=id]')
					.val(values.id)
					.keyup(function () { thisCls.processInputs.apply(parentCls); });

				$Tab.find('input[name=width]')
					.val(values.width)
					.keyup(function () { $Tab.find('.widthSlider').slider('value', $(this).val());thisCls.processInputs.apply(parentCls); });

				$Tab.find('input[name=height]')
					.val(values.height)
					.keyup(function () { $Tab.find('.heightSlider').slider('value', $(this).val());thisCls.processInputs.apply(parentCls); });

				var WidthUnitChange = function (){
					if ($(this).val() == 'auto'){
						$Tab.find('input[name=width]').attr('disabled', 'disabled');
						$Tab.find('.widthSlider').slider('disable');
					}else if ($(this).val() == '%'){
						$Tab.find('input[name=width]').removeAttr('disabled');
						$Tab.find('.widthSlider').slider('enable');
						$Tab.find('.widthSlider').slider('option', 'min', 0);
						$Tab.find('.widthSlider').slider('option', 'max', 100);
						$Tab.find('.widthSlider').slider('value', $Tab.find('input[name=width]').val());
					}else{
						$Tab.find('input[name=width]').removeAttr('disabled');
						$Tab.find('.widthSlider').slider('enable');
						$Tab.find('.widthSlider').slider('option', 'min', 30);
						$Tab.find('.widthSlider').slider('option', 'max', parentCls.getCurrentElement().parent().width());
					}
					thisCls.processInputs.apply(parentCls);
				};

				var HeightUnitChange = function (){
					if ($(this).val() == 'auto'){
						$Tab.find('input[name=height]').attr('disabled', 'disabled');
						$Tab.find('.heightSlider').slider('disable');
					}else if ($(this).val() == '%'){
						$Tab.find('input[name=height]').removeAttr('disabled');
						$Tab.find('.heightSlider').slider('enable');
						$Tab.find('.heightSlider').slider('option', 'min', 0);
						$Tab.find('.heightSlider').slider('option', 'max', 100);
						$Tab.find('.heightSlider').slider('value', $Tab.find('input[name=height]').val());
					}else{
						$Tab.find('input[name=height]').removeAttr('disabled');
						$Tab.find('.heightSlider').slider('enable');
						$Tab.find('.heightSlider').slider('option', 'min', 30);
						$Tab.find('.heightSlider').slider('option', 'max', parentCls.getCurrentElement().parent().height());
					}
					thisCls.processInputs.apply(parentCls);
				};

				$Tab.find('select[name=width_unit]')
					.val(values.width_unit)
					.change(WidthUnitChange);

				$Tab.find('select[name=height_unit]')
					.val(values.height_unit)
					.change(HeightUnitChange);

				$Tab.find('.widthSlider').slider({
					value: 0,
					max: 100,
					min: 30,
					step: 1,
					slide: function (e, ui) {
						$Tab.find('input[name=width]').val(ui.value);
						thisCls.processInputs.apply(parentCls);
					}
				});

				$Tab.find('.heightSlider').slider({
					value: 0,
					max: 100,
					min: 30,
					step: 1,
					slide: function (e, ui) {
						$Tab.find('input[name=height]').val(ui.value);
						thisCls.processInputs.apply(parentCls);
					}
				});

				if (values.width_unit == 'auto'){
					$Tab.find('input[name=width]').attr('disabled', 'disabled');
					$Tab.find('.widthSlider').slider('disable');
				}
				else {
					if (values.width_unit == '%'){
						$Tab.find('.widthSlider').slider('option', 'min', 0);
						$Tab.find('.widthSlider').slider('option', 'max', 100);
						$Tab.find('.widthSlider').slider('value', values.width);
					}
					else {
						var parentWidth = parentCls.getCurrentElement().parent().width();
						parentWidth -= parseFloat(parentCls.getCurrentElement().css('marginLeft'));
						parentWidth -= parseFloat(parentCls.getCurrentElement().css('marginRight'));

						$Tab.find('.widthSlider').slider('option', 'min', 30);
						$Tab.find('.widthSlider').slider('option', 'max', parentWidth);
						if (values.width > parentWidth){
							$Tab.find('input[name=width]').val(parentWidth);
							$Tab.find('.widthSlider').slider('value', parentWidth);
						}else{
							$Tab.find('.widthSlider').slider('value', values.width);
						}
					}
				}

				if (values.height_unit == 'auto'){
					$Tab.find('input[name=height]').attr('disabled', 'disabled');
					$Tab.find('.heightSlider').slider('disable');
				}
				else {
					if (values.height_unit == '%'){
						$Tab.find('.heightSlider').slider('option', 'min', 0);
						$Tab.find('.heightSlider').slider('option', 'max', 100);
						$Tab.find('.heightSlider').slider('value', values.height);
					}
					else {
						var parentHeight = parentCls.getCurrentElement().parent().height();
						$Tab.find('.heightSlider').slider('option', 'min', 30);
						$Tab.find('.heightSlider').slider('option', 'max', parentHeight);
						if (values.height > parentHeight){
							$Tab.find('input[name=height]').val(parentHeight);
							$Tab.find('.heightSlider').slider('value', parentHeight);
						}else{
							$Tab.find('.heightSlider').slider('value', values.height);
						}
					}
				}
			},
			processInputs: function () {
				var $Tab = $('#mainTabPanel').find('#settings');
				var parentCls = this;

				this.updateInputVal('id', $Tab.find('input[name=id]').val());
				this.updateInputVal('width', $Tab.find('input[name=width]').val());
				this.updateInputVal('width_unit', $Tab.find('select[name=width_unit]').val());
				this.updateInputVal('height', $Tab.find('input[name=height]').val());
				this.updateInputVal('height_unit', $Tab.find('select[name=height_unit]').val());

				if ($Tab.find('select[name=width_unit]').val() == 'auto'){
					this.updateStylesVal('width', 'auto');
				}
				else {
					this.updateStylesVal('width', $Tab.find('input[name=width]').val() + $Tab.find('select[name=width_unit]').val());
				}
				if ($Tab.find('select[name=height_unit]').val() == 'auto'){
					this.updateStylesVal('height', 'auto');
				}
				else {
					this.updateStylesVal('height', $Tab.find('input[name=height]').val() + $Tab.find('select[name=height_unit]').val());
				}
			}
		}
	});

})(jQuery);
