<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Dcom', a plugin for Dotclear 2                    *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk, Jean-François Michaud and contributors.*
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Dcom' (see COPYING.txt);                    *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }
require dirname(__FILE__).'/_widgets.php';

$core->tpl->addValue('showDcom',array('publicDcom','showTpl'));

class publicDcom
{
	public static function showWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$p = array();
		$p['t_limit'] = $w->t_limit;
		$p['c_limit'] = $w->c_limit;
		$p['co_limit'] = $w->co_limit;
		$p['title'] = $w->title;
		$p['stringformat'] = $w->stringformat;
		$p['dateformat'] = $w->dateformat;
		
		commonDcom::adjustDefaults($p);
		
		$res = '<div class="lastcomments">'.
			($p['title'] ? '<h2>'.html::escapeHTML($p['title']).'</h2>' : '').
			self::show($p).
			'</div>';
		
		return $res;
	}
	
	public static function showTpl($attr)
	{
		commonDcom::adjustDefaults($attr);
		
		return '<?php echo publicDcom::show(unserialize(\''.
			 addcslashes(serialize($attr),'\'\\').'\')); ?>';
	}
	
	public static function show($p)
	{
		global $core;
		
		$params = array();
		$params['limit'] = $p['c_limit'];
		$params['order'] = 'comment_dt desc';
		$rs = $core->blog->getComments($params);
		unset($params);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$res = '<ul>';
		
		$url =
		$date = $title = $author = $comment = '';
		$s_url = strpos($p['stringformat'],'%5$s') === false ? false : true;
		$s_date = strpos($p['stringformat'],'%1$s') === false ? false : true;
		$s_title = strpos($p['stringformat'],'%2$s') === false ? false : true;
		$s_author = strpos($p['stringformat'],'%3$s') === false ? false : true;
		$s_comment = strpos($p['stringformat'],'%4$s') === false ? false : true;
		
		while ($rs->fetch())
		{
			if ($s_url) {
				$url = $rs->getPostURL().'#c'.$rs->comment_id;
			}
			if ($s_date) {
				$date = $rs->getTime($p['dateformat']);
			}
			if ($s_title) {
				$title = self::truncate($rs->post_title,$p['t_limit'],false);
			}
			if ($s_author) {
				$author = html::escapeHTML($rs->comment_author);
			}
			if ($s_comment) {
				$comment = self::truncate($rs->comment_content,$p['co_limit']);
			}

			$res .= '<li>'.sprintf($p['stringformat'],$date,$title,$author,$comment,$url).'</li>';
		}
		
		$res .= '</ul>';
		
		return $res;
	}
	
	public static function truncate($str,$maxlength,$html=true)
	{
		# On rend la chaîne lisible
		if ($html) {
			$str = html::decodeEntities(html::clean($str));
		}
		
		# On coupe la chaîne si elle est trop longue
		if (mb_strlen($str) > $maxlength) {
			$str = text::cutString($str,$maxlength).'…';
		}
		
		# On encode la chaîne pour pouvoir l'insérer dans un document HTML
		return html::escapeHTML($str);
	}
}
?>