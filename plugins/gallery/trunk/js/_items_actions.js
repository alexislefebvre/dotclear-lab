function fix_exif(imgs) {
	for (var i=0; i< imgs.length; i++) {
		var action_id = rd.addLine(" "+imgs[i]+" : "+dotclear.msg.update_exif);
			queuedManager.add({
				type: 'POST',
				url: 'services.php',
				data: {f: "galFixImgExif", imgId: imgs[i], xd_check: dotclear.nonce},
				success: (function(id) { return function(data) {
						rd.setResult(data,id);
						};})(action_id)
				});
	}
}

function regenerate_thumbs(media) {
	for (key in media) {
		var action_id = rd.addLine(" "+key+" : "+dotclear.msg.update_exif);
			queuedManager.add({
				type: 'POST',
				url: 'services.php',
				data: {f: "galMediaCreateThumbs", mediaId: media[key], xd_check: dotclear.nonce},
				success: (function(id) { return function(data) {
						rd.setResult(data,id);
						};})(action_id)
				});
	}
}

$(document).ready(function(){
	rd = new requestDisplay("resulttable");

	$("input#abort").click(function() {
		queuedManager.clear();
		queuedManager.abort();
		nQueuedManager.clear();
		nQueuedManager.abort();
		$("#resulttable td.processing").parent("tr").remove();
	});

});

