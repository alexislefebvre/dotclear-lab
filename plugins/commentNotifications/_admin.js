/*
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentNotifications, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
*/
var notificator = {
	css: {
		'menu': {
			'background': 'transparent url(index.php?pf=commentNotifications/img/bubble.png) no-repeat top 20px',
			'height': '24px',
			'width': '24px',
			'overflow': 'hidden',
			'position': 'absolute',
			'text-align': 'center',
			'font-weight': 'bold',
			'display': 'none'
		}
	},
	cookie: 'dc_nb_comments',
	msg: {},
	
	init: function () {
		/* Set cookie */
		if ($.cookie(notificator.cookie) == null || notificator.reload_nb_comments == 'true') {
			$.cookie(notificator.cookie,notificator.nb_comments);
		}
		/* Set menu */
		var p = {
			'top': $('li a[href="comments.php"]').position().top - 20,
			'left': $('li a[href="comments.php"]').position().left + $('li a[href="comments.php"]').width() - 25
		};
		notificator.css.menu = $.extend(notificator.css.menu,p);
		$('li a[href="comments.php"]').parent().append('<li class="new" rel="comments"></li>');
		$('li[rel="comments"]').css(notificator.css.menu);
		
		notificator.getNbComments();
	},
	
	getNbComments: function () {
		$.get('services.php',{f:'getNbComments'},function(data) {
			var rsp = $(data).children('rsp')[0];
			
			if (rsp.attributes[0].value == 'ok') {
				var delta = $(rsp).find('comments').text() - $.cookie(notificator.cookie);
				
				if (delta > 0) {
					notificator.applyChanges(delta);
				}
			};
			setTimeout('notificator.getNbComments()',10000);
		});
	},
	
	applyChanges: function (num) {
		/* Changes for the dasboard */
		var legend = $('span a[href="comments.php"]');
		var img = $('a[href="comments.php"] img');
		var html = "";
		html += (notificator.nb_comments + num) + " ";
		html += notificator.nb_comments > 1 ? notificator.msg.comments : notificator.msg.comment;
		html += " (" + num + " ";
		html += (num > 1 ? notificator.msg.recents : notificator.msg.recent) + ")";
		legend.html(html);
		img.attr("src","index.php?pf=commentNotifications/img/comments-b.png");
		
		/* Changes for the menu */
		$('li[rel="comments"]').html(num);
		$('li[rel="comments"]').fadeIn("slow");
	}
};


$(document).ready(function() {
	notificator.init();
});