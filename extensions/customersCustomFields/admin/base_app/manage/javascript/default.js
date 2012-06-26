function addOption(el){
	var nextId = parseInt($(el).data('next_id'));
	$(el).data('next_id', nextId+1);

	var sortOrder = parseInt($(el).data('next_sort'));
	$(el).data('next_sort', sortOrder+1);

	$(el).parentsUntil('#inputOptions').last().find('tbody').append('<tr>' +
		'<td>' +
		'<input class="text" type="text" name="option_name[' + nextId + ']" value="">' +
		'</td>' +
		'<td>' +
		//'<span class="ui-icon ui-icon-wrench editData" tooltip="Edit Data"></span>' +
		'<span class="ui-icon ui-icon-arrowthick-1-n moveOptionUp" tooltip="Move Up"></span>' +
		'<span class="ui-icon ui-icon-arrowthick-1-s moveOptionDown" tooltip="Move Down"></span>' +
		'<span class="ui-icon ui-icon-circle-minus removeOption" tooltip="Remove Option"></span>' +
		'<input class="sort" type="hidden" name="option_sort[' + nextId + ']" value="' + sortOrder + '">' +
		'<input class="data" type="hidden" name="option_data[' + nextId + ']" value="">' +
		'</td>' +
		'</tr>');
}

function removeOption(el){
	var nextTd = $(el).parentsUntil('tbody').last().next();
	while(nextTd.size() > 0){
		nextTd.find('.sort').val(parseInt(nextTd.find('.sort').val()) - 1);
		nextTd = nextTd.next();
	}
	$(el).parentsUntil('tbody').last().remove();
}

function showOptionEntry(el) {
	if (el.value != 'select' && el.value != 'radio' && el.value != 'checkbox'){
		$('#inputOptions').hide();
	}
	else {
		$('#inputOptions').show();
	}
}

function moveOptionUp(el){
	var Row = $(el).parentsUntil('tbody').last();
	var SortField = Row.find('.sort');
	Row.prev().find('.sort').val(SortField.val());
	SortField.val(parseInt(SortField.val()) - 1);
	Row.insertBefore(Row.prev());
}

function moveOptionDown(el){
	var Row = $(el).parentsUntil('tbody').last();
	var SortField = Row.find('.sort');
	Row.next().find('.sort').val(SortField.val());
	SortField.val(parseInt(SortField.val()) + 1);
	Row.insertAfter(Row.next());
}

function makeGroupDroppable($el) {
	$el.each(function () {
		var $groupBox = $(this);
		$(this).droppable({
			accept     : '.draggableField',
			hoverClass : 'ui-state-highlight',
			drop       : function (e, ui) {
				var $this = $(this);
				$.ajax({
					cache      : false,
					url        : js_app_link('appExt=customersCustomFields&app=manage&appPage=default&action=addFieldToGroup&group_id=' + $this.attr('group_id') + '&field_id=' + $('.fieldName', ui.draggable).attr('field_id')),
					dataType   : 'json',
					beforeSend : function () {
						showAjaxLoader($this, 'xlarge');
					},
					complete   : function () {
						hideAjaxLoader($this);
					},
					success    : function (data) {
						if (data.success == true){
							var $newLi = $('<li></li>')
								.attr('id', 'field_' + $('.fieldName', ui.draggable).attr('field_id'))
								.css('font-size', '.8em')
								.html($('.fieldName', ui.draggable).html());
							$newLi.hover(function () {
								this.style.cursor = 'move';
							}, function () {
								this.style.cursor = 'default';
							});
							$('ul', $this).append($newLi);
							$('.sortableList', $this).sortable('refresh');
						}
						else {
							alert('That field already belongs to this group');
						}
					}
				});
			}
		});
	});
}

$(document).ready(function () {
	$('.addOption').live('click', function (){
		addOption(this);
	});

	$('.removeOption').live('click', function (){
		removeOption(this);
	});

	$('.moveOptionUp').live('click', function (){
		moveOptionUp(this);
	});

	$('.moveOptionDown').live('click', function (){
		moveOptionDown(this);
	});

	$('.editData').live('click', function (){
		editOptionData(this);
	});

	var $FieldsGrid = $('#fields_grid');
	$FieldsGrid.newGrid('option', 'buttons', ['new', 'edit', 'delete']);

	var $GroupsGrid = $('#groups_grid');
	$GroupsGrid.newGrid('option', 'buttons', [
		{
			selector          : '.newButton',
			disableIfNone     : false,
			disableIfMultiple : false,
			click             : function (e, GridClass) {
				GridClass.clearSelected();
				GridClass.showWindow({
					buttonEl    : this,
					contentUrl  : GridClass.buildActionWindowLink('newGroup'),
					buttons     : [
						{
							type  : 'cancel',
							click : GridClass.windowButtonEvent('cancel', {
								onBeforeHide : function () {
									$('#fieldToGroup').button('disable');
								}
							})
						},
						'save'
					],
					onAfterShow : function () {
						$(this).find('.sortableList').sortable({
							containment : $(this).parent(),
							cursor      : 'move',
							items       : 'li',
							opacity     : .5,
							revert      : true
						});

						$(this).find('.ui-icon-trash').parent().droppable({
							accept     : 'li',
							hoverClass : 'ui-state-highlight',
							drop       : function (e, ui) {
								var $this = $(this);
								$(ui.draggable).remove();
								$('.sortableList', $this).sortable('refresh');
							}
						});

						$('#fieldToGroup').button('enable');
					}
				});
			}
		},
		{
			selector          : '.editButton',
			disableIfNone     : true,
			disableIfMultiple : true,
			click             : function (e, GridClass) {
				GridClass.showWindow({
					buttonEl    : this,
					contentUrl  : GridClass.buildActionWindowLink('newGroup', true),
					buttons     : [
						{
							type  : 'cancel',
							click : GridClass.windowButtonEvent('cancel', {
								onBeforeHide : function () {
									$('#fieldToGroup').button('disable');
								}
							})
						},
						{
							type  : 'save',
							click : GridClass.windowButtonEvent('save', {
								data : function () {
									return $(this).find('*').serialize() + '&' + $(this).find('.ui-sortable').sortable('serialize');
								}
							})
						}
					],
					onAfterShow : function () {
						$(this).find('.sortableList').sortable({
							containment : $(this).parent(),
							cursor      : 'move',
							items       : 'li',
							opacity     : .5,
							revert      : true
						});

						$(this).find('.ui-icon-trash').parent().droppable({
							accept     : 'li',
							hoverClass : 'ui-state-highlight',
							drop       : function (e, ui) {
								var $this = $(this);
								$(ui.draggable).remove();
								$('.sortableList', $this).sortable('refresh');
							}
						});
					}
				});

				$('#fieldToGroup').button('enable');
			}
		},
		'delete'
	]);

	$('#fieldToGroup').click(function () {
		if ($GroupsGrid.parent().find('.newWindowContainer').size() == 1){
			var SelectedFields = $FieldsGrid.newGrid('getSelectedRows');

			if (SelectedFields.size() == 0){
				alert('Error');
				return false;
			}

			var GroupSortable = $GroupsGrid.parent().find('.newWindowContainer .sortableList');
			SelectedFields.each(function () {
				if (GroupSortable.find('#field_' + $(this).data('field_id')).size() == 0){
					GroupSortable.append('<li ' +
						'id="field_' + $(this).data('field_id') + '"' +
						'sort_order=""' +
						'>' + $(this).find('td').first().html() +
						'</li>');
				}
			});
			GroupSortable.sortable('refreah');
		}
		else {
			var SelectedFields = $FieldsGrid.newGrid('getSelectedRows');
			var SelectedGroups = $GroupsGrid.newGrid('getSelectedRows');

			if (SelectedFields.size() == 0 || SelectedGroups.size() == 0){
				alert('Error');
				return false;
			}

			SelectedGroups.each(function () {
				var GroupSortable = $(this).find('.sortableList');
				if (GroupSortable.find('#field_' + $(this).data('field_id')).size() == 0){
					SelectedFields.each(function () {
						GroupSortable.append('<li ' +
							'id="field_' + $(this).data('field_id') + '"' +
							'sort_order=""' +
							'>' + $(this).find('td').first().html() +
							'</li>');
					});
				}
			});
		}
	});
});
