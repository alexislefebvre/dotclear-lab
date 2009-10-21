
$(document).ready(function(){
	for (var i=0; i< imgs.length; i++) {
		var action_id = addLine(processid,imgs[i], dotclear.msg.update_exif, dotclear.msg.please_wait);
		actions.push ({line_id: action_id, params: {f: "galFixImgExif", imgId: imgs[i]}});
	}
	
	doProcess();
});

