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

# On surchage les fonctions template
$core->tpl->addValue('CommentIfMe',array('tplCarnaval','CommentIfMe'));
$core->tpl->addValue('PingIfOdd',array('tplCarnaval','PingIfOdd'));

class tplCarnaval
{
	public static function CommentIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->isMe()) { '.
		"echo '".addslashes($ret)."'; } ".
		"echo tplCarnaval::getCommentClass(); ?>";
	}
	
	public static function PingIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->pings->index()+1)%2) { '.
		"echo '".addslashes($ret)."'; } ".
		"echo tplCarnaval::getPingClass(); ?>";
	}
	
	public static function getCommentClass()
	{
		global $core,$_ctx;
		
		$carnaval = new dcCarnaval($core->blog);
		$classe_perso = $carnaval->getCommentClass($_ctx->comments->getEmail(false)); 
		$classe_perso = html::escapeHTML($classe_perso);
		return html::escapeHTML($classe_perso);
	}
	
	public static function getPingClass()
	{
		global $core,$_ctx;
		
		$carnaval = new dcCarnaval($core->blog);
		$classe_perso = $carnaval->getPingClass($_ctx->pings->getAuthorURL()); 
		$classe_perso = html::escapeHTML($classe_perso);
		return html::escapeHTML($classe_perso);
	}
}
?>
