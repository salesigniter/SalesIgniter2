var geocoder, map, marker;

$(document).ready(function () {
	$('#setStore').click(function (){
		$.ajax({
			cache: false,
			dataType: 'json',
			url: js_app_link('appExt=rentalProducts&app=chooseLocation&appPage=default&action=setLocation&location_id=' + $('input[name=my_store]:checked').val()),
			success: function (data){
				js_redirect(data.storeUrl);
			}
		});
	});

	$('#findStores').click(function () {
		geocoder = new google.maps.Geocoder();
		var customerPostcode = $('input[name=postcode]').val();
		$('#mapsHolder table>tbody').html('');

		$.ajax({
			cache: false,
			dataType: 'json',
			url: js_app_link('appExt=rentalProducts&app=chooseLocation&appPage=default&action=getPostcodes'),
			success: function (data) {
				var processing = 0;
				var addresses = [];
				geocoder.geocode({ address: customerPostcode }, function (response, status) {
						if (status != google.maps.GeocoderStatus.OK){
							alert("Geocode was not successful for the following reason: " + status);
						}
						else {
							var location1 = {
								lat: response[0].geometry.location.lat(),
								lng: response[0].geometry.location.lng(),
								address: response[0].formatted_address
							};

							map = new google.maps.Map(document.getElementById("map_canvas"), {
								zoom: 8,
								center: new google.maps.LatLng(location1.lat, location1.lng),
								mapTypeId: google.maps.MapTypeId.ROADMAP
							});

							marker = new google.maps.Marker({
								position: new google.maps.LatLng(location1.lat, location1.lng),
								map: map,
								title: 'You Are Here'
							});

							var interval = setInterval(function () {
								if (processing == 0){
									clearInterval(interval);

									addresses.sort(function (a, b) {
										var returnVal = 0;
										if (a.rows[0].elements[0].distance.value < b.rows[0].elements[0].distance.value){
											returnVal = -1;
										}
										else {
											if (a.rows[0].elements[0].distance.value > b.rows[0].elements[0].distance.value){
												returnVal = 1;
											}
										}
										return returnVal;
									});

									$.each(addresses, function (key, jsonObj) {
										$('#mapsHolder table>tbody').append(
											'<tr>' +
												'<td valign="top"><input type="radio" name="my_store" value="' + jsonObj.storesId + '"' + (key == 0 ? ' checked="checked"' : '') + '></td>' +
												'<td>' +
												'<strong>From: </strong>' + jsonObj.originAddresses[0] + '<br />' +
												'<strong>To: </strong>' + jsonObj.destinationAddresses[0] + '<br />' +
												'<strong>Distance: </strong>' + jsonObj.rows[0].elements[0].distance.text +
												'</td>' +
												'</tr>'
										);
									});

									$('#setStore').show();
								}
							}, 1000);

							$.each(data.postcodes, function () {
								var self = this;
								processing = processing + 1;

								geocoder.geocode({ address: self.address }, function (response, status) {
										if (status != google.maps.GeocoderStatus.OK){
											alert("Geocode was not successful for the following reason: " + status);
										}
										else {
											var location2 = {
												lat: response[0].geometry.location.lat(),
												lng: response[0].geometry.location.lng(),
												address: response[0].formatted_address
											};

											marker = new google.maps.Marker({
												position: new google.maps.LatLng(location2.lat, location2.lng),
												map: map,
												title: location2.address
											});
											google.maps.event.addListener(marker, 'click', function() {
												$('input[value=' + self.id + ']:radio').click();
											});

											var orig = new google.maps.LatLng(location1.lat, location1.lng);
											var dest = new google.maps.LatLng(location2.lat, location2.lng);

											var service = new google.maps.DistanceMatrixService();
											service.getDistanceMatrix(
												{
													origins: [orig],
													destinations: [dest],
													travelMode: google.maps.TravelMode.DRIVING,
													unitSystem: google.maps.UnitSystem.IMPERIAL,
													avoidHighways: false,
													avoidTolls: false
												}, function (response, status) {
													response.storesId = self.id;
													addresses.push(response);
													processing = processing - 1;
												});
										}
									});

							});
						}

					});
			}
		});
	});
});