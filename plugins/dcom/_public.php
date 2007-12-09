<?php
/***************************************************************\
 *  This is 'Dcom', a plugin for Dotclear 2                    *
 *                                                             *
 *  Copyright (c) 2007                                         *
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
		
		$res = '<?php echo publicDcom::show(unserialize(\''.
			 addcslashes(serialize($attr),'\'\\').'\')); ?>';
		return $res;
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
		$s_url = (strpos($p['stringformat'],'%5$s') === false) ? false : true;
		$s_date = (strpos($p['stringformat'],'%1$s') === false) ? false : true;
		$s_title = (strpos($p['stringformat'],'%2$s') === false) ? false : true;
		$s_author = (strpos($p['stringformat'],'%3$s') === false) ? false : true;
		$s_comment = (strpos($p['stringformat'],'%4$s') === false) ? false : true;
		
		while ($rs->fetch())
		{
			if ($s_url) {
				$url = $rs->getPostURL().'#c'.$rs->comment_id;
			}
			if ($s_date) {
				$date = $rs->getTime($p['dateformat']);
			}
			if ($s_title) {
				$title = html::escapeHTML(self::cutString($rs->post_title,$p['t_limit']));
			}
			if ($s_author) {
				$author = html::escapeHTML($rs->comment_author);
			}
			if ($s_comment) {
				$comment = html::escapeHTML(self::cutString(html::decodeEntities(html::clean($rs->comment_content)),$p['co_limit']));
			}

			$res .= '<li>'.sprintf($p['stringformat'],$date,$title,$author,$comment,$url).'</li>';
		}
		
		$res .= '</ul>';
		
		return $res;
	}
	
	public static function cutString($str,$maxlength=false)
	{
		if (mb_strlen($str) > $maxlength && $maxlength)
			return self::myCutString($str,$maxlength).'...';
		return $str;
	}
	
	// Fonction cutString() de Dotclear écrite par Olivier Meunier
	// Corrigée pour supporter le UTF-8
	// https://clearbricks.org/svn/trunk/common/lib.text.php [72]
	public static function myCutString($str,$l)
	{
		$s = preg_split('/([\s]+)/u',$str,-1,PREG_SPLIT_DELIM_CAPTURE);
		
		$res = '';
		$L = 0;
		
		if (mb_strlen($s[0]) >= $l) {
			return mb_substr($s[0],0,$l);
		}
		
		foreach ($s as $v)
		{
			$L = $L+strlen($v);
			
			if ($L > $l) {
				break;
			} else {
				$res .= $v;
			}
		}
		
		return trim($res);
	}
}
?>
