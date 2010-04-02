function update_galleries(gals) {

	for (var i=0; i< gals.length; i++) {
		var action_id = rd.addLine(" "+gals[i]+" : "+dotclear.msg.refresh_gallery);
			queuedManager.add({
				type: 'POST',
				url: 'services.php',
				data: {f: "galUpdate", galId: gals[i], xd_check: dotclear.nonce},
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