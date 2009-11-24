


$(document).ready(function(){
	rd = new requestDisplay("resulttable");

	$("input#abort").click(function() {
		queuedManager.clear();
		queuedManager.abort();
		nQueuedManager.clear();
		nQueuedManager.abort();
		$("#resulttable td.processing").parent("tr").remove();
	});

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
});

