<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Live Counter', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Live Counter' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('publicBeforeDocument',array('publicLiveCounter','adjustCache'));
$core->addBehavior('templateBeforeValue',array('publicLiveCounter','templateBeforeValue'));
$core->tpl->addValue('ConnectedUsers',array('publicLiveCounter','tplConnectedUsers'));
$core->tpl->addBlock('ConnectedUsersIf',array('publicLiveCounter','tplConnectedUsersIf'));

class publicLiveCounter
{
	private static $connectedCounter = false;
	
	public static function initCounters()
	{
		$timeout = (integer) $GLOBALS['core']->blog->settings->get('lc_timeout');
		$dir = $GLOBALS['core']->blog->settings->get('lc_cache_dir');
		if (!is_dir($dir)) {
			try {files::makeDir($dir,true);}
			catch (Exception $e) {return;}
		}
		$file = $dir.DIRECTORY_SEPARATOR.md5(DC_MASTER_KEY.$GLOBALS['core']->blog->uid);
		
		self::$connectedCounter = new connectedCounter($file,$timeout);
		self::$connectedCounter->count();
		self::$connectedCounter->writeNewData();
	}

	public static function showInWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$sets = &$core->blog->settings;
		if ($timeout = (int) $w->timeout and $timeout != $sets->lc_timeout) {
			$sets->setNamespace('livecounter');
			$sets->put('lc_timeout',$timeout);
		}
		
		if (!$c = self::$connectedCounter->count()
		or !$content = $c == 1 ? $w->content_one : $w->content) return;
		
		$show_users = '';
		if (strpos($content,'%2$s')) {
			$res = array();
			$pattern = $w->show_links ? '<a href="%2$s"%3$s>%1$s</a>' : '';
			$nofollow = $sets->comments_nofollow ? ' rel="nofollow"' : '';
			foreach (self::$connectedCounter->result as $v)
			{
				$res[] = sprintf($pattern,
					html::escapeHTML($v['name']),
					html::escapeURL($v['site']),
					$nofollow);
			}
			$res = array(implode(', ',$res));
			
			if (($d = $c-count(self::$connectedCounter->result)) > 0
			and $pattern = $d < 2 ? $w->one_unknown : $w->more_unknown) {
				$res[] = sprintf($pattern,$d);
			}
			$show_users = implode(' '.__('and').' ',$res);
		}
		
		$content = sprintf($content,(string) $c,$show_users);
		
		$res = '<div id="livecounter">'.
			($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
			'<p>'.$content.'</p></div>';
		
		return $res;
	}
	
	public static function tplConnectedUsers($attr)
	{
		return
		'<?php if (property_exists("publicLiveCounter","connectedCounter")) {'.
		'echo publicLiveCounter::$connectedCounter->count();} ?>';
	}
	
	public static function tplConnectedUsersIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['number'])) {
			$if[] = '$lcc === '.(int) $attr['number'];
		}
		if (isset($attr['min'])) {
			$if[] = '$lcc >= '.(int) $attr['min'];
		}
		if (isset($attr['max'])) {
			$if[] = '$lcc <= '.(int) $attr['max'];
		}
		
		$res = '<?php if((property_exists("publicLiveCounter","connectedCounter") &&'.
			'$lcc = publicLiveCounter::$connectedCounter->count())';
		if (!empty($if)) {
			$res .= ' && ('.implode(' '.$operator.' ',$if).')';
		}
		$res .=  ') : ?>'.$content.'<?php endif; ?>';
		
		return $res;
	}
	
	public static function templateBeforeValue(&$core,$id,$attr)
	{	
		if ($id == 'include' && isset($attr['src']) && $attr['src'] == '_head.html') {
			return
			'<?php if (method_exists("publicLiveCounter","initCounters")) {'.
			'publicLiveCounter::initCounters();} ?>';
		}
	}
	
	public static function adjustCache(&$core)
	{
		if ($core->blog->settings->get('lc_no_browser_cache')) {
			$GLOBALS['mod_ts'] = array(time());
		}
	}

	public static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
}
?>
