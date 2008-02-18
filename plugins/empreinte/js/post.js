/* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

/* Empreinte JavaScript, based on post.js in default Dotclear theme
   written by Olivier Meunier - http://www.dotclear.net
*/

function empreinteCheckbox() {
	$('#comment-form fieldset:has(input[type=submit][name=preview])').
		prepend(
			'<p style="margin:0pt 5px 0pt 140px;">' +
			'<input type="checkbox" id="no_empreinte" name="no_empreinte" style="width:auto;" />' +
			'<label for="no_empreinte">' + post_no_empreinte_str + '</label>' +
			'</p>'
		);
	
	var cookie = readCookie($.cookie('comment_no_empreinte'));
	
	if (cookie != false) {
		$('#no_empreinte').attr('checked','checked');
	}
	
	$('#no_empreinte').click(function() {
		if (this.checked) {
			$.cookie('comment_no_empreinte',1,{expires:60,path:'/'});
		} else {
			$.cookie('comment_no_empreinte',0,{expires:-30,path: '/'});
		}
	});
	
	function readCookie(c) {
		if (!c || c != 1) {
			return false;
		}
		return true;
	}
};

function addLoadListener(func) {
   if (window.addEventListener) {
      window.addEventListener("load", func, false);
   } else if (document.addEventListener) {
      document.addEventListener("load", func, false);
   } else if (window.attachEvent) {
      window.attachEvent("onload", func);
   }
}

addLoadListener(empreinteCheckbox);
