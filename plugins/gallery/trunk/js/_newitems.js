var media = {newMedia: [], withoutPost: [], currentMedia: []};
var newMediaError='';
var waitingImage = '<img src="index.php?pf=gallery/progress.gif"/>';

var media_dir='';
var lineId=1;
var update_ts="no";
var nb_orphan_media=0;
var nb_orphan_item=0;


var rd;


function cleanup () {
media = {newMedia: [], withoutPost: [], currentMedia: []};
newMediaError='';

$("#nborphanmedia").html(waitingImage);
$("#nborphanitems").html(waitingImage);
$("#nbnewmedia").html(waitingImage);
$("#nbmediawithoutpost").html(waitingImage);
$("#nbcurmedia").html(waitingImage);

}

function onGetOrphanMedia (data) {
	if ($(data).find('rsp').attr('status') == 'ok') {
		nb_orphan_media=$(data).text();;
		$("#nborphanmedia").text(""+nb_orphan_media);
	} else {
		newMediaError=$(data).find('message').text();
		$("#nborphanmedia").text("#ERR#"+newMediaError);
	}
}

function onGetOrphanItems (data) {
	if ($(data).find('rsp').attr('status') == 'ok') {
		nb_orphan_item=$(data).text();;
		$("#nborphanitems").text(""+nb_orphan_item);
	} else {
		newMediaError=$(data).find('message').text();
		$("#nborphanitems").text("#ERR#"+newMediaError);
	}
}

function onGetNewMedia(data) {
	if ($(data).find('rsp').attr('status') == 'ok') {
		var files=$(data).find('file');
		files.each(function() {
			var filename=$(this).attr('name');
			media.newMedia.push (filename);
			//media.withoutPost.push (filename);
		});
		$("#nbnewmedia").text(""+media.newMedia.length);
	} else {
		newMediaError=$(data).find('message').text();
		$("#nbnewmedia").text("#ERR#"+newMediaError);
	}

}
function onGetCurrentMedia(data) {
	if ($(data).find('rsp').attr('status') == 'ok') {
		var files=$(data).find('media');
		files.each(function() {
			var id=$(this).attr('id');
			var filename=$(this).attr('name');
			media.currentMedia.push ({"id": id , "name": filename});
		});
		$("#nbcurmedia").text(""+media.currentMedia.length);
	} else {
		newMediaError=$(data).find('message').text();
		$("#nbcurmedia").text("#ERR#"+newMediaError);
	}

}

function onGetWithoutPost(data) {
	if ($(data).find('rsp').attr('status') == 'ok') {
		var files=$(data).find('media');
		files.each(function() {
			media.withoutPost.push ({id: $(this).attr('id'), name: $(this).attr('file')});
		});
		$("#nbmediawithoutpost").text(""+media.withoutPost.length);
		$("#nbmediawithoutpost2").text(""+(media.withoutPost.length+media.newMedia.length));
	} else {
		newMediaError=$(data).find('message').text();
		$("#nbmediawithoutpost").text("#ERR# "+newMediaError);
	}
}


function onGetWithoutThumb(data) {
	if ($(data).find('rsp').attr('status') == 'ok') {
		var files=$(data).find('file');
		files.each(function() {
			var filename=$(this).attr('name');
			media.withoutPost.push (filename);
		});
		$("#nbmediawithoutthumb").text(""+media.withoutPost.length);
	} else {
		newMediaError=$(data).find('message').text();
		$("#nbmediawithoutthumb").text("#ERR# "+newMediaError);
	}
}

$(document).ready(function(){
rd = new requestDisplay("resulttable");
$("#dir-form input.proceed").click(function() {
	cleanup();
	media_dir = $("#media_dir")[0].value;
	$("#directory").html(media_dir);
	nQueuedManager.add({
		type: 'GET', 
		url: 'services.php', 
		data: {f: 'galGetOrphanMediaCount', mediaDir: media_dir}, 
		success: onGetOrphanMedia
	});

	nQueuedManager.add({
		type: 'GET', 
		url: 'services.php', 
		data: {f: 'galGetOrphanItemsCount', mediaDir: media_dir}, 
		success: onGetOrphanItems
	});

	nQueuedManager.add({
		type: 'GET', 
		url: 'services.php', 
		data: {f: 'galGetNewMedia', mediaDir: media_dir}, 
		success: onGetNewMedia
	});

	nQueuedManager.add({
		type: 'GET', 
		url: 'services.php', 
		data: {f: 'galGetMediaWithoutPost', "mediaDir": media_dir}, 
		success: onGetWithoutPost
	});
	nQueuedManager.add({
		type: 'GET', 
		url: 'services.php', 
		data: {f: 'galGetCurrentMedia', "mediaDir": media_dir}, 
		success: onGetCurrentMedia
	});
});

$("#actions-form input.proceed").click(function() {
	update_ts = document.getElementById("update_ts").checked;
	if (update_ts) {
		update_ts = "yes";
	} else {
		update_ts="no";
	}
	for (i=0; i<media.newMedia.length; i++) {
		for (j=0; j<media.currentMedia.length; j++) {
			if (media.currentMedia[j].name == media.newMedia[i]) {
				media.currentMedia.splice(j,1);
			}
		}
	}

	if ($('#delete_orphan_media')[0].checked && nb_orphan_media != 0) {
		var id=rd.addLine(dotclear.msg.deleting_orphan_media);
		nQueuedManager.add({
			type: 'POST',
			url: 'services.php',
			data: {f: 'galDeleteOrphanMedia', mediaDir: media_dir, xd_check: dotclear.nonce},
			success: (function(id) { return function(data) {
						rd.setResult(data,id);
					};})(id)
			});
			
	}
	if ($('#delete_orphan_items')[0].checked && nb_orphan_item != 0) {
		var id=rd.addLine(dotclear.msg.deleting_orphan_items);
		nQueuedManager.add({
			type: 'POST',
			url: 'services.php',
			data: {f: 'galDeleteOrphanItems', mediaDir: media_dir, confirm: "yes", xd_check: dotclear.nonce},
			success: (function(id) { return function(data) {
						rd.setResult(data,id);
					};})(id)
			});
			
	}
	if ($('#create_new_media')[0].checked) {
		while (media.newMedia.length != 0) {
			var id='';
			filename=media.newMedia.shift();
			id = rd.addLine(dotclear.msg.creating_media.replace(/%s/,filename));
			queuedManager.add({
				type: 'POST',
				url: 'services.php',
				data: {f: "galMediaCreate", mediaDir: media_dir, mediaName: filename, withPost: 1, xd_check: dotclear.nonce},
				success: (function(id) { return function(data) {
						var media_id=$(data).text();
						rd.setResult(data,id);
						};})(id)
				});
		}
	}
	if ($('#create_img_post')[0].checked) {
		while (media.withoutPost.length != 0) {
			var id='';
			var item=media.withoutPost.shift();
			media_id=item.id;
			id = rd.addLine(dotclear.msg.creating_item.replace(/%s/,item.name));
			queuedManager.add({
				type: 'POST',
				url: 'services.php',
				data: {f: "galCreateImgForMedia", mediaId: media_id, updateTimeStamp: update_ts, xd_check: dotclear.nonce},
				success: (function(id) { return function(data) {
						rd.setResult(data,id);
						};})(id)
				});
		}
	}
	if ($('#force_thumbnails')[0].checked) {
		while (media.currentMedia.length != 0) {
			var item=media.currentMedia.shift();
			id = rd.addLine(dotclear.msg.creating_thumbnail.replace(/%s/,item.name));
			queuedManager.add({
				type: 'POST',
				url: 'services.php',
				data: {f: "galCreateImgForMedia", mediaId: item.id, updateTimeStamp: update_ts, xd_check: dotclear.nonce},
				success: (function(id) { return function(data) {
						rd.setResult(data,id);
						};})(id)
				});
		}
	}
});

$("input#abort").click(function() {
	queuedManager.clear();
	queuedManager.abort();
	nQueuedManager.clear();
	nQueuedManager.abort();
	$("#resulttable td.processing").parent("tr").remove();
});
});

