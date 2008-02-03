<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Carnaval', a plugin for Dotclear 2          *
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

# On surchage le tpl CommentIfMe avec une nouvelle fonctionnalité
# tplDcCommentClass : récupère la classe CSS définie dans la 
# partie admin du blog.
$core->tpl->addValue('CommentIfMe',array('tplDcCommentClass','CommentIfMe'));

class tplDcCommentClass
{
	public static function CommentIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->isMe()) { '.
		"echo '".addslashes($ret)."'; } ".
		 "echo tplDcCommentClass::getCssClass(); ?>";
	}
	
	public static function getCssClass()
	{
		global $core,$_ctx;
		
		$carnaval = new dcCarnaval($core->blog);
		$classe_perso = $carnaval->getAuthorClass($_ctx->comments->getEmail(false)); 
		$classe_perso = html::escapeHTML($classe_perso);
		return html::escapeHTML($classe_perso);
	}
}
?>
