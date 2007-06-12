var actions;
var currentRetrieve;
var currentAction;
var nbActions;
var retrieves;
$(document).ready(function(){
var media_dir,scan_media,create_posts,update_gals;

function processNext(data) {
	var action = actions[currentAction];
	if ($(data).find('rsp').attr('status') == 'ok') {
		$("#"+action.line_id).html('<img src="images/check-on.png" alt="OK" />');
	} else {
		$("#"+action.line_id).html('<img src="images/check-off.png" alt="KO" />');
	}
	currentAction++;
	doProcess();

}

function doProcess() {
	if (currentAction < actions.length) {
		var action = actions[currentAction];
		$("#"+action.line_id).html('<img src="index.php?pf=gallery/progress.gif" alt="please wait" />');
		$.post("services.php",action.params,processNext);
	} else {
		doRetrieve();
	}
}

function addLine(id,desc,status_desc, status_id) {
		var tr = document.createElement('tr');
		var td_id = document.createElement('td');
		td_id.appendChild(document.createTextNode(id));
		var td_action = document.createElement("td");
		td_action.appendChild(document.createTextNode(desc));
		var td_status = document.createElement("td");
		td_status.appendChild(document.createTextNode(status_desc));
		td_status.id="action_"+nbActions;
		tr.appendChild(td_id);
		tr.appendChild(td_action);
		tr.appendChild(td_status);
		document.getElementById("process").appendChild(tr);
		nbActions++;
		return td_status.id;
}

function processNewMedia(data) {
	$(data).find('file').each(function() {
		var filename=$(this).attr('name');
		var action_id = addLine(filename, "Create media", "waiting...");
		actions.push ({line_id: action_id, params: {f: "galMediaCreate", mediaDir: media_dir, mediaName: filename}});
	});
	doProcess();
}

function processPostMedia(data) {
	$(data).find('media').each(function() {
		var id=$(this).attr('id');
		var filename=$(this).attr('file');
		var action_id = addLine(filename, "Create post for media", "waiting...");
		actions.push ({line_id: action_id, params: {f: "galCreateImgForMedia", mediaId: id}});
	});
	doProcess();
}

function processRefreshGal(data) {
	$(data).find('gallery').each(function() {
		var id=$(this).attr('id');
		var name=$(this).attr('name');
		var filename=$(this).attr('file');
		var action_id = addLine(name, "Refresh gallery", "waiting...");
		actions.push ({line_id: action_id, params: {f: "galRefreshGal", galId: id}});
	});
	doProcess();
}
function doRetrieve() {
	if (currentRetrieve < retrieves.length) {
		var retrieve = retrieves[currentRetrieve];
		var params = retrieve.request;
		$.get("services.php",retrieve.request,retrieve.callback);
	}
	currentRetrieve++;

}

$("input.proceed").click(function() {
	/*$("#actions-form").hide();*/
	actions=[];
	currentAction=0;
	currentRetrieve=0;
	nbActions=0;
	retrieves=[];

	media_dir = document.getElementById("media_dir").value;
	scan_media = document.getElementById("scan_media").checked;
	create_posts = document.getElementById("create_posts").checked;
	update_gals = document.getElementById("update_gals").checked;
	if (scan_media){
		retrieves.push({request: {f: 'galGetNewMedia', mediaDir: media_dir}, callback: processNewMedia});
	}
	if (create_posts) {
		retrieves.push({request: {f: 'galGetMediaWithoutPost', mediaDir: media_dir}, callback: processPostMedia});
	}
	if (update_gals) {
		retrieves.push({request: {f: 'galGetGalFromMediaDir', mediaDir: media_dir}, callback: processRefreshGal});
	}
	doRetrieve();

});
});
