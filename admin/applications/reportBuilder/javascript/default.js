$(document).ready(function (){
	function genReport(){
		$.post(js_app_link('app=reportBuilder&appPage=default&action=genReport'), $('select, input:checked').serialize(), function (html){
			$('#ReportBuilderOutput').html(html);
		}, 'html');
	}

	function getTableInfo(table, checkbox){
		$.getJSON(js_app_link('app=reportBuilder&appPage=default&action=getTableInfo&baseModel=' + $('select[name=baseTable]').val() + '&model=' + table), function (data){
			var list = $('<ul></ul>');
			$.each(data.columns, function (){
				list.append('' +
					'<li>' +
					'	<input class="tableColumn" type="checkbox" name="model[' + table + '][column][]" value="' + this + '">' + this +
					'	<ul>' +
					'		<li>' +
					'			<input class="groupBy" type="checkbox" name="group_by[]" value="' + table + '.' + this + '">Group By' +
					'		</li>' +
					'		<li>' +
					'			<input class="groupBy" type="checkbox" name="sum[' + this + ']" value="' + table + '.' + this + '">Sum' +
					'		</li>' +
					'	</ul>' +
					'</li>' +
					'');
			});
			$.each(data.relations, function (){
				list.append('<li><input class="relation" type="checkbox" name="model[' + table + '][relation][]" value="' + this + '">' + this + '</li>');
			});

			if (checkbox){
				list.appendTo($(checkbox).parent());
			}else{
				$('#ReportBuilder').html(list);
			}
		});
	}

	$('select[name=baseTable]').change(function (){
		getTableInfo($(this).val());
		genReport();
	});

	$('.relation').live('click', function (){
		if (this.checked){
			getTableInfo($(this).val(), this);
		}else{
			$(this).parent().find('ul, li').remove();
		}
		genReport();
	});

	$('.tableColumn').live('click', function (){
		genReport();
	});

	$('.groupBy, .sum').live('click', function (){
		genReport();
	});
});
