dotclear.postExpander = function(line) {
	var td = line.firstChild;
	
	var img = document.createElement('img');
	img.src = dotclear.img_plus_src;
	img.alt = dotclear.img_plus_alt;
	img.className = 'expand';
	$(img).css('cursor','pointer');
	img.line = line;
	img.onclick = function() { dotclear.viewPostContent(this,this.line); };
	
	td.insertBefore(img,td.firstChild);
};

dotclear.viewPostContent = function(img,line) {
	var postId = line.id.substr(1);
	var tr = document.getElementById('pe'+postId);
	
	if (!tr) {
		tr = document.createElement('tr');
		tr.id = 'pe'+postId;
		var td = document.createElement('td');
		td.colSpan = 8;
		td.className = 'expand';
		tr.appendChild(td);
		
		img.src = dotclear.img_minus_src;
		img.alt = dotclear.img_minus_alt;
		
		// Get post content
		$.get('plugin.php?p=superAdmin&file=services',{f:'getPostById', id: postId, post_type: ''},function(data) {
			var rsp = $(data).children('rsp')[0];
			
			if (rsp.attributes[0].value == 'ok') {
				var post = $(rsp).find('post_display_content').text();
				var post_excerpt = $(rsp).find('post_display_excerpt').text();
				var res = '';
				
				if (post) {
					if (post_excerpt) {
						res += post_excerpt + '<hr />';
					}
					res += post;
					$(td).append(res);
				}
			} else {
				alert($(rsp).find('message').text());
			}
		});
		
		$(line).toggleClass('expand');
		line.parentNode.insertBefore(tr,line.nextSibling);
	}
	else if (tr.style.display == 'none')
	{
		$(tr).toggle();
		$(line).toggleClass('expand');
		img.src = dotclear.img_minus_src;
		img.alt = dotclear.img_minus_alt;
	}
	else
	{
		$(tr).toggle();
		$(line).toggleClass('expand');
		img.src = dotclear.img_plus_src;
		img.alt = dotclear.img_plus_alt;
	}
};

$(function() {
	$('#form-entries tr.line').each(function() {
		dotclear.postExpander(this);
	});
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});
	$('#form-entries td input[type=checkbox]').enableShiftClick();
	dotclear.postsActionsHelper();
});