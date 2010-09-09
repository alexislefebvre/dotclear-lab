var nb_posts;
var current_offset=0;
var scan_method;
var post_ids = [];

var queuedManager = $.manageAjax.create('queued', { 
	queue: true,  
	cacheResponse: false,
}); 

function showError(text,data) {
	$("#progress").hide();
	$("#abort-form").hide();
	if (data != undefined) {
		$(data).find('message').each(function() {
			text += ' ['+$(this).text()+']';
		});
	}
	$("#error").text(text);
	$("#error").show("slow");
}

function fetchPostIDs(data) {
	if ((data == null) ||  $(data).find('rsp').attr('status') == 'ok') {
		if (data != null){
			current_offset += dotclear.get_ids_limit;
			var posts = $(data).find('post');
			posts.each(function() {
				post_ids.push($(this).attr('id'));
			});
		}
		$("#pb").progressBar (2+Math.floor(8.0*current_offset/nb_posts));
		if (current_offset < nb_posts) {
			queuedManager.add({
				type: 'GET', 
				url: 'services.php', 
				data: {f: 'getPostIDs', 
					count_only: 0, 
					all_posts: scan_method,
					offset: current_offset,
					limit: dotclear.get_ids_limit}, 
				success: fetchPostIDs
			});
		} else {
			// all ids fetched
			current_offset = 0;
			refreshPosts(null);;
		}
	} else {
		// Error management
		showError(dotclear.msg.error_retrieve_post,data);
	}
}

function refreshPosts(data) {
	if ((data == null) ||  $(data).find('rsp').attr('status') == 'ok') {
		if (data != null){
			current_offset += dotclear.refresh_limit;
		}
		$("#pb").progressBar (10+Math.floor(90.0*current_offset/nb_posts));
		if (current_offset < nb_posts) {
			queuedManager.add({
				type: 'POST', 
				url: 'services.php', 
				data: {f: 'updResources', 
					ids: post_ids.slice(current_offset,current_offset+dotclear.refresh_limit).join(','), 
					xd_check: dotclear.nonce}, 
				success: refreshPosts
			});
		} else {
			// all posts refreshed
			$("#pb").progressBar(100);
			$("#abort-form").hide();
		}
	} else {
		// Error management
		showError (dotclear.msg.error_refresh_post,data);
	}
}

function onGetCount(data) {
	if ($(data).find('rsp').attr('status') == 'ok') {
		$("#pb").progressBar (2);
		nb_posts = $(data).text();
		$("#result-nb").text(""+nb_posts);
		if (nb_posts > 0) {
			fetchPostIDs(null);
		} else {
			$("#progress").hide();
			$("#abort-form").hide();
		}
	} else {
		$("#pb").progressBar(100,{barImage : 'index.php?pf=grumph/images/progressbg_red.gif'});

		$("#result-nb").text("<strong>ERROR</strong>");
		showError(dotclear.msg.error_retrieve_post,data);
	}
}

$(document).ready(function(){
	$("#pb").progressBar({
		boxImage : 'index.php?pf=grumph/images/progressbar.gif', 
		barImage : 'index.php?pf=grumph/images/progressbg_green.gif'
	});
	$("#scan-form input.proceed").click(function() {
		$("#scan-form").hide("slow");
		$("#results").show("slow");
		scan_method = ($('#scan_method')[0].value == 'all')?1:0;
		queuedManager.add({
			type: 'GET', 
			url: 'services.php', 
			data: {f: 'getPostIDs', count_only: 1, all_posts:scan_method}, 
			success: onGetCount
		});
	});
	
	$("input#abort").click(function() {
		queuedManager.clear();
		queuedManager.abort();
		showError('Aborted');
	});
});