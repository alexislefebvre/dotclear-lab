<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Remote LaTeX', a plugin for Dotclear              *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Remote LaTeX' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem('LaTeX','plugin.php?p=remotelatex',null,
	preg_match('/plugin.php\?p=remotelatex(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('contentadmin',$core->blog->id));

$core->addBehavior('adminPostHeaders',array('adminRemoteLatex','adminPostHeaders'));

class adminRemoteLatex
{
	public static function adminPostHeaders()
	{
		global $can_edit_post, $post_content_xhtml, $post_excerpt_xhtml;
		
		$post_content_xhtml = remoteLatex::parseContent($post_content_xhtml);
		$post_excerpt_xhtml = remoteLatex::parseContent($post_excerpt_xhtml);
	}
}
?>