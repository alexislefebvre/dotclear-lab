dotclear.commentExpander = function(line) {
	var td = line.firstChild;
	
	var img = document.createElement('img');
	img.src = dotclear.img_plus_src;
	img.alt = dotclear.img_plus_alt;
	img.className = 'expand';
	$(img).css('cursor','pointer');
	img.line = line;
	img.onclick = function() { dotclear.viewCommentContent(this,this.line); };
	
	td.insertBefore(img,td.firstChild);
};

dotclear.viewCommentContent = function(img,line) {
	var commentId = line.id.substr(1);
	
	var tr = document.getElementById('ce'+commentId);
	
	if (!tr) {
		tr = document.createElement('tr');
		tr.id = 'ce'+commentId;
		var td = document.createElement('td');
		td.colSpan = 6;
		td.className = 'expand';
		tr.appendChild(td);
		
		img.src = dotclear.img_minus_src;
		img.alt = dotclear.img_minus_alt;
		
		// Get comment content
		$.get('plugin.php?p=superAdmin&file=services',{f:'getCommentById',id: commentId},function(data) {
			var rsp = $(data).children('rsp')[0];
			
			if (rsp.attributes[0].value == 'ok') {
				var comment = $(rsp).find('comment_display_content').text();
				
				if (comment) {
					$(td).append(comment);
					var comment_email = $(rsp).find('comment_email').text();
					var comment_site = $(rsp).find('comment_site').text();
					var comment_ip = $(rsp).find('comment_ip').text();
					var comment_spam_disp = $(rsp).find('comment_spam_disp').text();
					
					$(td).append('<p><strong>' + dotclear.msg.website +
					'</strong> ' + comment_site + '<br />' +
					'<strong>' + dotclear.msg.email + '</strong> ' +
					comment_email + '<br />' + comment_spam_disp + '</p>');
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
	if (!document.getElementById) { return; }
	
	if (document.getElementById('edit-entry'))
	{
		// Get document format and prepare toolbars
		var formatField = $('#post_format').get(0);
		$(formatField).change(function() {
			excerptTb.switchMode(this.value);
			contentTb.switchMode(this.value);
		});
		
		var excerptTb = new jsToolBar(document.getElementById('post_excerpt'));
		var contentTb = new jsToolBar(document.getElementById('post_content'));
		excerptTb.context = contentTb.context = 'post';
	}
	
	if (document.getElementById('comment_content')) {
		var commentTb = new jsToolBar(document.getElementById('comment_content'));
	}
	
	// Post preview
	$('#post-preview').modalWeb($(window).width()-40,$(window).height()-40);
	
	// Tabs events
	$('#edit-entry').onetabload(function() {
		dotclear.hideLockable();
		
		// Add date picker
		var post_dtPick = new datePicker($('#post_dt').get(0));
		post_dtPick.img_top = '1.5em';
		post_dtPick.draw();
		
		// Confirm post deletion
		$('input[name="delete"]').click(function() {
				return window.confirm(dotclear.msg.confirm_delete_post);
		});
		
		// Hide some fields
		$('#notes-area label').toggleWithLegend($('#notes-area').children().not('label'),{
			cookie: 'dcx_post_notes',
			hide: $('#post_notes').val() == ''
		});
		$('#post_lang').parent().toggleWithLegend($('#post_lang'),{
			cookie: 'dcx_post_lang'
		});
		$('#post_password').parent().toggleWithLegend($('#post_password'),{
			cookie: 'dcx_post_password',
			hide: $('#post_password').val() == ''
		});
		
		// We load toolbar on excerpt only when it's ready
		$('#excerpt-area label').toggleWithLegend($('#excerpt-area').children().not('label'),{
			fn: function() { excerptTb.switchMode(formatField.value); },
			cookie: 'dcx_post_excerpt',
			hide: $('#post_excerpt').val() == ''
		});
		
		// Load toolbars
		contentTb.switchMode(formatField.value);
		
		// Markup validator
		var p = document.createElement('p');
		var a = document.createElement('a');
		a.href = '#';
		$(a).click(function() {
			var div = document.createElement('div');
			div.id = 'markup-validator';
			
			if ($('#markup-validator').length > 0) {
				$('#markup-validator').remove();
			}
			
			var params = {
				xd_check: dotclear.nonce,
				f: 'validatePostMarkup',
				excerpt: $('#post_excerpt').text(),
				content: $('#post_content').text(),
				format: $('#post_format').get(0).value,
				lang: $('#post_lang').get(0).value
			};
			
			$.post('services.php',params,function(data) {
				if ($(data).find('rsp').attr('status') != 'ok') {
					alert($(data).find('rsp message').text());
					return false;
				}
				
				if ($(data).find('valid').text() == 1) {
					$(div).addClass('message');
					$(div).text(dotclear.msg.xhtml_valid);
					$(div).insertAfter(p);
					$(div).backgroundFade({sColor:'#ffffff',eColor:'#ffcc00',steps:50},function() {
							$(this).backgroundFade({sColor:'#ffcc00',eColor:'#ffffff'});
					});
				} else {
					$(div).addClass('error');
					$(div).html('<p><strong>' + dotclear.msg.xhtml_not_valid + '</strong></p>' + $(data).find('errors').text());
					$(div).insertAfter(p);
					$(div).backgroundFade({sColor:'#ffffff',eColor:'#ff9999',steps:50},function() {
							$(this).backgroundFade({sColor:'#ff9999',eColor:'#ffffff'});
					});
				}
				
				return false;
			});
			
			return false;
		});
		
		a.appendChild(document.createTextNode(dotclear.msg.xhtml_validator));
		p.appendChild(a);
		$(p).appendTo('#entry-content');
	});
	
	$('#comments').onetabload(function() {
		$('.comments-list tr.line').each(function() {
			dotclear.commentExpander(this);
		});
		$('.checkboxes-helpers').each(function() {
			dotclear.checkboxesHelpers(this);
		});
		
		dotclear.commentsActionsHelper();
	});
	
	$('#add-comment').onetabload(function() {
		commentTb.draw('xhtml');
	});
});
