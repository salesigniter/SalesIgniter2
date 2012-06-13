var map;
var geocoder;
var Polygon;
var markers = new Array();
var lineColor = "#0000af";
var fillColor = "#335599";
var lineWeight = 3;
var lineOpacity = .8;
var fillOpacity = .2;

function geocodeResult(Results, Status) {
	if (Status == google.maps.GeocoderStatus.OK){
		$('#addLon').html(Results[0].geometry.location.lat);
		$('#addLat').html(Results[0].geometry.location.lng);
		map.setCenter(Results[0].geometry.location);
	}
	else {
		alert("Geocode was not successful for the following reason: " + status);
	}
}

function recenterMap() {
	if ($('textarea[name="google_zones_address"]').val()){
		geocoder = new google.maps.Geocoder();
		geocoder.geocode({ address : $('textarea[name="google_zones_address"]').val() }, geocodeResult);
	}
}

function drawOverlay() {
	if (!Polygon){
		Polygon = new google.maps.Polygon();
		Polygon.setMap(map);
	}

	var polyPaths = [];
	$.each(markers, function (k, v) {
		var ThisPosition = this.getPosition();

		polyPaths.push(ThisPosition);
		$('input[name="poly_point[' + k + '][lat]"]').val(ThisPosition.lat());
		$('input[name="poly_point[' + k + '][lng]"]').val(ThisPosition.lng());
	});

	Polygon.setOptions({
		paths         : polyPaths,
		strokeColor   : lineColor,
		strokeWeight  : lineWeight,
		strokeOpacity : lineOpacity,
		fillColor     : fillColor,
		fillOpacity   : fillOpacity
	});
}

function setupMarker(Marker){
	// Drag listener
	google.maps.event.addListener(Marker, "drag", function () {
		drawOverlay();
	});

	// Second click listener
	google.maps.event.addListener(Marker, "rightclick", function () {
		$('.polyPoint').remove();
		$.each(markers, function (k, v) {
			if (v == Marker){
				Marker.setMap(null);

				markers.splice(k, 1);
			}
		});

		if (markers.length > 0){
			$.each(markers, function (k, v) {
				var ThisPosition = this.getPosition();

				$('#mapHolder').append(
					'<input class="polyPoint" type="hidden" name="poly_point[' + k + '][lat]" value="' + ThisPosition.lat() + '">' +
					'<input class="polyPoint" type="hidden" name="poly_point[' + k + '][lng]" value="' + ThisPosition.lng() + '">'
				);
			});

			drawOverlay();
		}
	});
}

function leftClick(Position, Map) {
	if (Position){
		var Marker = new google.maps.Marker({
			position  : Position,
			map       : Map,
			draggable : true
		});

		markers.push(Marker);

		$('#mapHolder').append(
			'<input class="polyPoint" type="hidden" name="poly_point[' + (markers.length - 1) + '][lat]" value="">' +
			'<input class="polyPoint" type="hidden" name="poly_point[' + (markers.length - 1) + '][lng]" value="">'
		);

		setupMarker(Marker);
		drawOverlay();
	}
}

$(document).ready(function () {
	$('.gridBody > .gridBodyRow').click(function () {
		if ($(this).hasClass('state-active')){
			return;
		}

		$('.gridButtonBar').find('button').button('enable');
	});

	$('.gridButtonBar').find('.newButton, .editButton').click(function () {
		var $Button = $(this);

		var urlGetVars = [];
		urlGetVars.push('rType=ajax');
		urlGetVars.push('app=zones');
		urlGetVars.push('appPage=default');
		urlGetVars.push('action=getActionWindow');
		urlGetVars.push('window=new');
		if ($(this).hasClass('editButton')){
			urlGetVars.push('zID=' + $('.gridBodyRow.state-active').data('zone_id'));
		}

		gridWindow({
			buttonEl   : this,
			gridEl     : $('.gridContainer'),
			contentUrl : js_app_link(urlGetVars.join('&')),
			onShow     : function (ui) {
				var self = this;

				map = new google.maps.Map($(self).find('#googleMap')[0], {
					zoom      : 8,
					mapTypeId : google.maps.MapTypeId.ROADMAP
				});
				google.maps.event.addListener(map, "click", function (e) {
					leftClick(e.latLng, map);
				});

				$(self).find('.cancelButton').click(function () {
					$(self).effect('fade', {
						mode : 'hide'
					}, function () {
						$('.gridContainer').effect('fade', {
							mode : 'show'
						}, function () {
							$(self).remove();
						});
					});
				});

				var urlGetVars = [];
				urlGetVars.push('rType=ajax');
				urlGetVars.push('app=zones');
				urlGetVars.push('appPage=default');
				urlGetVars.push('action=save');
				if ($Button.hasClass('editButton')){
					urlGetVars.push('zID=' + $('.gridBodyRow.state-active').data('zone_id'));
				}
				$(self).find('.saveButton').click(function () {
					$.ajax({
						cache    : false,
						url      : js_app_link(urlGetVars.join('&')),
						dataType : 'json',
						data     : $(self).find('*').serialize(),
						type     : 'post',
						success  : function (data) {
							js_redirect(js_app_link('app=zones&appPage=default'));
						}
					});
				});

				$(self).find('textarea[name="google_zones_address"]').blur(function () {
					recenterMap();
				});

				$(self).find('.makeFCK').each(function () {
					CKEDITOR.replace(this);
				});

				var latLng = [];
				$('.polyPoint').each(function (){
					var markerNum = $(this).data('marker_number');
					var whichSetting = $(this).data('which');
					if (!latLng[markerNum]){
						latLng[markerNum] = [];
					}

					latLng[markerNum][whichSetting] = $(this).val();
				});

				$.each(latLng, function (){
					var Marker = new google.maps.Marker({
						position  : new google.maps.LatLng(this.lat, this.lng),
						map       : map,
						draggable : true
					});

					markers.push(Marker);

					setupMarker(Marker);
				});
				drawOverlay();
				recenterMap();
			}
		});
	});

	$('.gridButtonBar').find('.deleteButton').click(function () {
		var zoneId = $('.gridBodyRow.state-active').data('zone_id');

		confirmDialog({
			confirmUrl   : js_app_link('app=zones&appPage=default&action=deleteConfirm&zID=' + zoneId),
			title        : 'Confirm Zone Delete',
			content      : 'Are you sure you want to delete this zone?',
			errorMessage : 'This zone could not be deleted.',
			success      : function () {
				$('.gridBodyRow.state-active').remove();
				$('.gridBodyRow').first().trigger('click');
			}
		});
	});
});