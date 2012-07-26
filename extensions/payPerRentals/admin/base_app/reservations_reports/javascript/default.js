$(document).ready(function (){
	var ProductsGrid = $('.gridContainer');
	ProductsGrid.newGrid('option', 'onRowClick', function (e, GridClass){
		$.post(js_app_link('appExt=payPerRentals&app=reservations_reports&appPage=default&action=getReservations'), {
			product_id : GridClass.getSelectedData()
		}, function (data){
			$('.calendarColumn').fullCalendar('removeEvents');
			$('.calendarColumn').fullCalendar('addEventSource', data.events);
		}, 'json');
	});

	$('.calendarColumn').fullCalendar({
		weekMode: 'liquid',
		height: $('.calendarColumn').innerHeight(),
		buttonText: {
			month: 'View Month',
			agendaWeek: 'View Week w/Time',
			agendaDay: 'View Day w/Time'
		},
		header: {
			left: 'title',
			center: 'prev,today,next',
			right: 'month,agendaWeek,agendaDay'
		}
	});
});