var map;
var marker;
jQuery(document).ready(function () {
	if (typeof EO_Venue != 'undefined') {
		postboxes.add_postbox_toggles(pagenow);
	}

    if (typeof google !== "undefined") {
        var eo_venue_Lat = jQuery("#eo_venue_Lat").val();
        var eo_venue_Lng = jQuery("#eo_venue_Lng").val();
        if (typeof eo_venue_Lat !== "undefined" && typeof eo_venue_Lng !== "undefined") {
		eo_initialize_map(eo_venue_Lat, eo_venue_Lng);

		if( eo_venue_Lat == 0 && eo_venue_Lng == 0 ){
			if( typeof EO_Venue != 'undefined'){
				var address = EO_Venue.location.split("/");	
				address = address[address.length-1];
			}
			if(  typeof address != 'undefined' && address ){
		                eventorganiser_code_address(address);
			}else{
				map.setZoom(1);
			}
		}
            jQuery(".eo_addressInput").change(function () {
                var address = "";
                jQuery(".eo_addressInput").each(function () {
                });
                eventorganiser_code_address(address)
            })
        }
    }
});

function eo_initialize_map(Lat, Lng) {

    if (typeof google !== "undefined") {
        var latlng = new google.maps.LatLng(Lat, Lng);

        var myOptions = {
            zoom: 15,
            center: latlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        map = new google.maps.Map(document.getElementById("venuemap"), myOptions);

        if (typeof EO_Venue != 'undefined') {
            var draggable = true
        } else {
            var draggable = false
        }

        marker = new google.maps.Marker({
            position: latlng,
            map: map,
            draggable: draggable
        });
	console.log('test');

        if (typeof EO_Venue != 'undefined') {
            google.maps.event.addListener(marker, 'dragend', function (evt) {
                jQuery("#eo_venue_Lat").val(evt.latLng.lat().toFixed(6));
                jQuery("#eo_venue_Lng").val(evt.latLng.lng().toFixed(6));
                map.setCenter(marker.position)
            })
	google.maps.event.addListener(map, 'rightclick', function(e) {
	var marker = new google.maps.Marker({
	    position: e.latLng, 
	    map: map
	});
	   eventorganiser_reverse_geocode(e.latLng);
	});
        }
    }
}

function eventorganiser_code_address(addrStr) {
    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({'address': addrStr}, function (results, status) {
		if ( status == google.maps.GeocoderStatus.OK){

			marker.setMap(null);
			map.setCenter(results[0].geometry.location);
			if (typeof EO_Venue != 'undefined') {
				var draggable = true
			} else {
				var draggable = false
			}
			marker = new google.maps.Marker({
               	 		map: map,
		                position: results[0].geometry.location,
                		draggable: draggable
            		});
			map.setZoom(15);
            	if (typeof EO_Venue != 'undefined') {
			google.maps.event.addListener(marker, 'dragend', function (evt) {
				jQuery("#eo_venue_Lat").val(evt.latLng.lat().toFixed(6));
				jQuery("#eo_venue_Lng").val(evt.latLng.lng().toFixed(6));
				map.setCenter(marker.position)
			})
		}
		jQuery("#eo_venue_Lat").val(results[0].geometry.location.lat());
		jQuery("#eo_venue_Lng").val(results[0].geometry.location.lng());
        }
})
}
function eventorganiser_reverse_geocode(latlng) {
  var geocoder = new google.maps.Geocoder();
  geocoder.geocode({'latLng': latlng, 'bounds': map.getBounds()}, function(result, status) {
    if (status == google.maps.GeocoderStatus.OK) {
	map.setCenter(latlng);
	var location = result[0].address_components;

	console.log(location);

	var address = result[0].formatted_address;
	jQuery("#eo-venue-address").val(address);

	var city = location.filter(function (component) {return jQuery.inArray("locality",component.types)!= -1 });
	city = (city.length>0 ? city[0].long_name : '' );
	jQuery("#eo-venue-city").val(city);

	var state = location.filter(function (component) {return jQuery.inArray("administrative_area_level_1",component.types)!= -1 });
	state = (state.length>0 ? state[0].long_name : '' );
	jQuery("#eo-venue-state").val(state);

	var postcode = location.filter(function (component) {return jQuery.inArray("postal_code",component.types)!= -1 });
	postcode = (postcode.length>0 ? postcode[0].long_name : '' );
	jQuery("#eo-venue-postcode").val(postcode);

	var country = location.filter(function (component) {return jQuery.inArray("country",component.types)!= -1 });
	country = (country.length>0 ? country[0].long_name : '' );
	jQuery("#eo-venue-country").val(country);
    }
  })
}


