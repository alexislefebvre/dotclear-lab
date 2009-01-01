/*function processRefreshGal(data) {
	$(data).find('gallery').each(function() {
		var id=$(this).attr('id');
		var name=$(this).attr('name');
		var filename=$(this).attr('file');
		var action_id = addLine(processid,name, dotclear.msg.refresh_gallery, dotclear.msg.please_wait);
		actions.push ({line_id: action_id, params: {f: "galRefreshGal", galId: id}});
	});
	doProcess();
}*/

$(document).ready(function(){
	for (var i=0; i< gals.length; i++) {
		var action_id = addLine(processid,gals[i], dotclear.msg.refresh_gallery+" : "+gals[i], dotclear.msg.please_wait);
		actions.push ({line_id: action_id, params: {f: "galUpdate", galId: gals[i]}});
	}
	
	doProcess();
});
