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
$(document).ready(function() {
	var css = {
		background: 'transparent url(index.php?pf=commentNotifications/img/bubble.png) no-repeat top 20px',
		height: '24px',
		width: '24px',
		overflow: 'hidden',
		position: 'absolute',
		'text-align': 'center',
		'font-weight': 'bold',
		display: 'none',
		top: $('li a[href="comments.php"]').position().top - 20,
		left: $('li a[href="comments.php"]').position().left + $('li a[href="comments.php"]').width() - 25
	};
	$('li a[href="comments.php"]').parent().append('<li class="new" rel="comments"></li>');
	$('li[rel="comments"]').css(css);
	
	var name = 'dc_nb_comments=';
	var ca = document.cookie.split(';');
	var nb = '9999999999';
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(name) == 0) nb = c.substring(name.length,c.length);
	}
	if (nb < nb_comments) {
		$('li[rel="comments"]').html(nb_comments-nb);
		$('li[rel="comments"]').fadeIn("slow");
	}
	
	getNbComments();
});

function getNbComments()
{
	$.get('services.php',{f:'getNbComments'},function(data) {
		var rsp = $(data).children('rsp')[0];
		
		if (rsp.attributes[0].value == 'ok') {
			var nb = $(rsp).find('comments').text();
			if (nb > nb_comments) {
				$('li[rel="comments"]').html(nb-nb_comments);
				$('li[rel="comments"]').fadeIn("slow");
			}
		};
		setTimeout('getNbComments()',10000);
	});
}