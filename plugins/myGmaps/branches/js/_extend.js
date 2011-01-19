google.maps.LatLng.prototype.distanceFrom = function(latlng) {
	var lat = [this.lat(), latlng.lat()]
	var lng = [this.lng(), latlng.lng()]

	//var R = 6371; // km (change this constant to get miles)
	var R = 6378137; // In meters
	var dLat = (lat[1]-lat[0]) * Math.PI / 180;
	var dLng = (lng[1]-lng[0]) * Math.PI / 180;
	var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
	Math.cos(lat[0] * Math.PI / 180 ) * Math.cos(lat[1] * Math.PI / 180 ) *
	Math.sin(dLng/2) * Math.sin(dLng/2);
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
	var d = R * c;

	return Math.round(d);
}
google.maps.Polygon.prototype.Bounds = function() {
	var bounds = new google.maps.LatLngBounds();
	for (var i=0; i < this.getPath().getLength(); i++) {
		bounds.extend(this.getPath().getAt(i));
	}
	return bounds;
}

google.maps.Polygon.prototype.getLength = function() {
	var d = 0;
	var path = this.getPath();
	var latlng, first;

	if (path.getLength()) {
		first = path.getAt(1);
	}

	for (var i = 0; i < path.getLength(); i++) {
		if (i < path.getLength()-1) {
			latlng = [path.getAt(i), path.getAt(i+1)];
		} else {
			if (first == path.getAt[i]) {
				break;
			} else {
				latlng = [path.getAt(i), path.getAt(0)];
			}
		}
		d += latlng[0].distanceFrom(latlng[1]);
	}

	return d;
}
google.maps.Polygon.prototype.getArea = function() {
	var a = 0;
	var j = 0;
	var b = this.Bounds();
	var x0 = b.getSouthWest().lng();
	var y0 = b.getSouthWest().lat();
	for (var i=0; i < this.getPath().getLength(); i++) {
		j++;
		if (j == this.getPath().getLength()) {j = 0;}
		var x1 = this.getPath().getAt(i).distanceFrom(new google.maps.LatLng(this.getPath().getAt(i).lat(),x0));
		var x2 = this.getPath().getAt(j).distanceFrom(new google.maps.LatLng(this.getPath().getAt(j).lat(),x0));
		var y1 = this.getPath().getAt(i).distanceFrom(new google.maps.LatLng(y0,this.getPath().getAt(i).lng()));
		var y2 = this.getPath().getAt(j).distanceFrom(new google.maps.LatLng(y0,this.getPath().getAt(j).lng()));
		a += x1*y2 - x2*y1;
	}
	return Math.abs(a * 0.5);
}

google.maps.Polyline.prototype.getLength = google.maps.Polygon.prototype.getLength;
