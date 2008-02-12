<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Carnaval', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Carnaval' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$forms = array();

$forms['form_fields'] = '
<p class="col"><label class="required" title="'.__('Required field').'">'.__('Name:').' '.
	form::field('comment_author',30,255,html::escapeHTML($comment_author),'',2).
'</label></p>
<p class="col"><label>'.__('Mail:').' '.
	form::field('comment_author_mail',30,255,html::escapeHTML($comment_author_mail),'',3).
'</label></p>
<p class="col"><label class="required" title="'.__('Required field').'">'.__('CSS Class:').' '.
	form::field('comment_class',30,255,html::escapeHTML($comment_class),'',5).
'</label></p>
<p class="col"><label>'.__('URL:').' '.
	form::field('comment_author_site',30,255,html::escapeHTML($comment_author_site),'',4).
'</label></p>
';
?>
