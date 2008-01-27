<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'dcCommentClass', a plugin for Dotclear 2          *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'dcCommentClass' (see COPYING.txt);     *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$_menu['Plugins']->addItem(__('Class for comments'),'plugin.php?p=dcCommentClass',
	'',
	preg_match('/plugin.php\?p=dcCommentClass(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id));

?>
