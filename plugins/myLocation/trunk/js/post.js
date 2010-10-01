/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of myLocation, a plugin for Dotclear.
#
# Copyright (c) 2010 Tomtom and contributors
# http://blog.zenstyle.fr/
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
$(document).ready(function() {
	if (navigator.geolocation) {
		$('#comment-form p:has(input[type=submit][name=preview])').
			before(
				'<p id="location"><input type="checkbox" id="c_location" name="c_location" /> ' +
				'<input type="hidden" id="c_location_longitude" name="c_location_longitude" /> ' +
				'<input type="hidden" id="c_location_latitude" name="c_location_latitude" /> ' +
				'<input type="hidden" id="c_location_address" name="c_location_address" /> ' +
				'<label for="c_location">' + post_location_checkbox + '</label>' +
				'</p>'
			);
		
		if ($.cookie('comment_location') != null) {
			getLocation();
			$('#c_location').attr('checked','checked');
		}
			
		$('#c_location').click(function() {
			if (this.checked) {
				getLocation();
				setLocationCookie();
			} else {
				updLocationLabel('','');
				dropLocationCookie();
			}
		});
		
		function getLocation() {
			if (post_location_longitude == '' && post_location_latitude == '') {
				$('input[type="submit"]').attr('disabled','disabled');
				updLocationLabel(post_location_search,'search');
				navigator.geolocation.getCurrentPosition(succesCallback,errorCallback);
			}
			else {
				$('#c_location_longitude').val(post_location_longitude);
				$('#c_location_latitude').val(post_location_latitude);
				if (post_location_address.length > 0) {
					$('#c_location_address').val(post_location_address);
					updLocationLabel(post_location_address,'success');
				}
			}
		}
		
		function setLocationCookie() {
			$.cookie('comment_location', '1', {expires: 60, path: '/'});
		}
		
		function dropLocationCookie() {
			$.cookie('comment_location','',{expires: -30, path: '/'});
		}
		
		function succesCallback(position) {
			$('input[type="submit"]').removeAttr('disabled');
			var address;
			var geocoder = new google.maps.Geocoder();
			var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
			geocoder.geocode({'latLng': latlng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					for (var i = 2; i < results.length; i++) {
						if (results[i]) {
							address = results[i].formatted_address;
							break;
						}
					}
				}
				$('#c_location_longitude').val(position.coords.longitude);
				$('#c_location_latitude').val(position.coords.latitude);
				if (address.length > 0) {
					$('#c_location_address').val(address);
					updLocationLabel(address,'success');
				}
			});
		}
		
		function errorCallback(error) {
			$('input[type="submit"]').removeAttr('disabled');
			switch(error.code) {
				case error.TIMEOUT:
					doFallback();
					navigator.geolocation.getCurrentPosition(successCallback,errorCallback);
					break;
				case error.PERMISSION_DENIED:
					updLocationLabel(post_location_error_denied,'denied');
					break;
				case error.POSITION_UNAVAILABLE:
					updLocationLabel(post_location_error_unavailable,'unavailable');
					break;
			};
		}
		
		function updLocationLabel(message,class) {
			$('label[for="c_location"]').html(
				post_location_checkbox+' <span class="' +
				class + '">(' + message + ')</span>'
			);
		}
	}
});