$(document).ready(function () {
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

function showOptionEntry(el) {
	if (el.value != 'select'){
		$('#selectOptions').hide();
	}
	else {
		$('#selectOptions').show();
	}
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
