$(document).ready(function(){
var media_dir,scan_media,create_posts,delete_orphan_items,delete_orphan_media,create_thumbs,update_ts,recurse_dir;

function processNewMedia(data) {
	var retrieve = retrieves[currentRetrieve-1];
	if ($(data).find('rsp').attr('status') == 'ok') {
		var files=$(data).find('file');
		$("#"+retrieve.line_id).html('<img src="images/check-on.png" alt="OK" />&nbsp;'+dotclear.msg.entries_found.replace(/%s/,files.length));
		files.each(function() {
			var filename=$(this).attr('name');
			var action_id = addLine(processid,filename, dotclear.msg.create_media, dotclear.msg.please_wait);
			actions.push ({line_id: action_id, params: {f: "galMediaCreate", mediaDir: media_dir, mediaName: filename}});
		});
	} else {
		$("#"+retrieve.line_id).html('<img src="images/check-off.png" alt="KO" /> '+$(data).find('message').text());
	}
	doProcess();
}
function processGalleries(data) {
	var retrieve = retrieves[currentRetrieve-1];
	if ($(data).find('rsp').attr('status') == 'ok') {
		var gals=$(data).find('gallery');
		$("#"+retrieve.line_id).html('<img src="images/check-on.png" alt="OK" />&nbsp;'+dotclear.msg.entries_found.replace(/%s/,gals.length));
		gals.each(function() {
			var gal_id=$(this).attr('id');
			var gal_title=$(this).attr('title');
			var action_id = addLine(processid,gal_title, dotclear.msg.refresh_gallery+" : "+$(this).attr('title'), dotclear.msg.please_wait);
			actions.push ({line_id: action_id, params: {f: "galUpdate", galId: gal_id}});
		});
	} else {
		$("#"+retrieve.line_id).html('<img src="images/check-off.png" alt="KO" /> '+$(data).find('message').text());
	}
	doProcess();
}

function processPostMedia(data) {
	var retrieve = retrieves[currentRetrieve-1];
	if ($(data).find('rsp').attr('status') == 'ok') {
		var media=$(data).find('media');
		$("#"+retrieve.line_id).html('<img src="images/check-on.png" alt="OK" />&nbsp;'+dotclear.msg.entries_found.replace(/%s/,media.length));
		media.each(function() {
			var id=$(this).attr('id');
			var filename=$(this).attr('file');
			var action_id = addLine(processid,filename, dotclear.msg.create_post_for_media, dotclear.msg.please_wait);
			actions.push ({line_id: action_id, params: {f: "galCreateImgForMedia", mediaId: id, updateTimeStamp: update_ts}});
		});
	} else {
		$("#"+retrieve.line_id).html('<img src="images/check-off.png" alt="KO" /> '+$(data).find('message').text());
	}
	doProcess();
}

function processMediaThumb(data) {
	var retrieve = retrieves[currentRetrieve-1];
	if ($(data).find('rsp').attr('status') == 'ok') {
		var media=$(data).find('media');
		$("#"+retrieve.line_id).html('<img src="images/check-on.png" alt="OK" />&nbsp;'+dotclear.msg.entries_found.replace(/%s/,media.length));
		media.each(function() {
			var id=$(this).attr('id');
			var filename=$(this).attr('file');
			var action_id = addLine(processid,filename, dotclear.msg.create_thumb_for_media, dotclear.msg.please_wait);
			actions.push ({line_id: action_id, params: {f: "galMediaCreateThumbs", mediaId: id}});
		});
	} else {
		$("#"+retrieve.line_id).html('<img src="images/check-off.png" alt="KO" /> '+$(data).find('message').text());
	}
	doProcess();
}

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
function doRetrieve() {
	if ((currentRetrieve < retrieves.length) && !cancel) {
		var retrieve = retrieves[currentRetrieve];
		var params = retrieve.request;
		$("#"+retrieve.line_id).html('<img src="index.php?pf=gallery/progress.gif" alt="'+dotclear.msg.please_wait+'" />');
		$.get("services.php",retrieve.request,retrieve.callback);
	} else {
		// That's all folks !
		$("input,select").attr("disabled",false);
		$("input#cancel").hide();
	}

	currentRetrieve++;

}

$("input#cancel").click(function() {
	cancel = true;
});

$("input#proceed").click(function() {
	$(".keepme").siblings().remove();
	$(processid).empty();
	$(requestid).empty();
	actions=[];
	currentAction=0;
	currentRetrieve=0;
	nbActions=0;
	retrieves=[];

	delete_orphan_media = document.getElementById("delete_orphan_media").checked;
	delete_orphan_items = document.getElementById("delete_orphan_items").checked;
	media_dir = document.getElementById("media_dir").value;
	media_dir = document.getElementById("media_dir").value;
	scan_media = document.getElementById("scan_media").checked;
	create_posts = document.getElementById("create_posts").checked;
	create_thumbs = document.getElementById("create_thumbs").checked;
	update_ts = document.getElementById("update_ts").checked;
	if (update_ts) {
		update_ts = "yes";
	} else {
		update_ts="no";
	}
	recurse_dir = document.getElementById("recurse_dir").checked;
	if (recurse_dir) {
		recurse_dir = "yes";
	} else {
		recurse_dir="no";
	}
	cancel = false;
	$("input#cancel").show();
	$("input#cancel").attr("disabled",false);

	if (delete_orphan_media) {
		var action_id = addLine(processid,media_dir, dotclear.msg.delete_orphan_media, dotclear.msg.please_wait);
		actions.push({line_id: action_id, params: {f: 'galDeleteOrphanMedia', mediaDir: media_dir}});
	}
	if (delete_orphan_items) {
		var action_id = addLine(processid,dotclear.msg.whole_blog, dotclear.msg.delete_orphan_items, dotclear.msg.please_wait);
		actions.push({line_id: action_id, params: {f: 'galDeleteOrphanItems', confirm: "yes"}});
	}
	if (scan_media){
		var action_id = addLine(requestid,media_dir, dotclear.msg.fetch_new_media, dotclear.msg.please_wait);
		retrieves.push({line_id: action_id, request: {f: 'galGetNewMedia', mediaDir: media_dir}, callback: processNewMedia});
	}
	if (create_posts) {
		var action_id = addLine(requestid,media_dir, dotclear.msg.fetch_media_without_post, dotclear.msg.please_wait);
		retrieves.push({line_id: action_id,request: {f: 'galGetMediaWithoutPost', "mediaDir": media_dir,"recurse_dir": recurse_dir}, callback: processPostMedia});
	}
	if (create_thumbs) {
		var action_id = addLine(requestid,media_dir, dotclear.msg.fetch_media_without_thumbnails, dotclear.msg.please_wait);
		retrieves.push({line_id: action_id,request: {f: 'galGetMediaWithoutThumbs', "mediaDir": media_dir}, callback: processMediaThumb});
	}
	doProcess();

});
$("input#proceedgal").click(function() {
	$(".keepme").siblings().remove();
	$(processid).empty();
	$(requestid).empty();
	$("input,select").attr("disabled",true);
	actions=[];
	currentAction=0;
	currentRetrieve=0;
	nbActions=0;
	retrieves=[];

	$("input#cancel").show();
	$("input#cancel").attr("disabled",false);

	var action_id = addLine(requestid,"all", dotclear.msg.retrieve_galleries, dotclear.msg.please_wait);
	retrieves.push({line_id: action_id, request: {f: 'galGetGalleries'}, callback: processGalleries});
	doProcess();

});
});
