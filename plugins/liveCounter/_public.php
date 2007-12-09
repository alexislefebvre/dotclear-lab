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

$__autoload['liveCounter'] = dirname(__FILE__).'/class.livecounter.php';
$core->addBehavior('publicBeforeDocument',array('publicLiveCounter','adjustCache'));
$core->addBehavior('templateBeforeValue',array('publicLiveCounter','templateBeforeValue'));
$core->tpl->addValue('ConnectedUsers',array('publicLiveCounter','tplConnectedUsers'));
$core->tpl->addBlock('ConnectedUsersIf',array('publicLiveCounter','tplConnectedUsersIf'));

class publicLiveCounter
{
	public static function showInWidget($w)
	{
		global $core;
		
		# Home page only
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$c = self::countConnected();
		
		# Live Counter error
		if (!$c) {
			return;
		}
		
		if ($c === 1) {
			$content = $w->content_one;
		}
		else {
			$content = $w->content;
		}
		
		# Nothing to display
		if (empty($content)) {
			return;
		}
		
		$content = sprintf($content,(string) $c);
		
		$res = '<div id="livecounter">'.
			($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
			$content.
			'</div>';
		
		return $res;
	}
	
	public static function tplConnectedUsers($attr)
	{
		return
		'<?php if (method_exists("publicLiveCounter","countConnected")) {'.
		'echo publicLiveCounter::countConnected();} ?>';
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
		
		$res = '<?php if((method_exists("publicLiveCounter","countConnected") &&'.
			'$lcc = publicLiveCounter::countConnected())';
		if (!empty($if)) {
			$res .= ' && ('.implode(' '.$operator.' ',$if).')';
		}
		$res .=  ') : ?>'.$content.'<?php endif; ?>';
		
		return $res;
	}
	
	public static function templateBeforeValue(&$core,$id,$attr)
	{	
		if ($id == 'include' && isset($attr['src']) && $attr['src'] = '_head.html') {
			return
			'<?php if (method_exists("publicLiveCounter","countConnected")) {'.
			'publicLiveCounter::countConnected();} ?>';
		}
	}

	public static function countConnected($readonly=false,&$changed=false)
	{
		static $c = false;
		
		# Already counted
		if ($c) {
			return $c;
		}
		
		$dir = $GLOBALS['core']->blog->settings->get('lc_cache_dir');
		$e = (integer) $GLOBALS['core']->blog->settings->get('lc_timeout');
		if (!is_dir($dir)) {
			try {files::makeDir($dir,true);}
			catch (Exception $e) {return null;}
		}
		$f = $dir.DIRECTORY_SEPARATOR.md5(DC_MASTER_KEY.$GLOBALS['core']->blog->uid);
		$count = liveCounter::getConnected($f,$e,$readonly,$changed);
		
		# Force counting on next call if read only is set
		return $readonly ? $count : $c = $count;
	}
	
	public static function adjustCache(&$core)
	{
		self::countConnected(true,$changed);
		if ($core->blog->settings->get('lc_no_browser_cache') && $changed) {
			# Data changed, cache refresh needed
			$GLOBALS['mod_ts'] = array(time());
		}
		else {
			# Do not forget write data
			self::countConnected();
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
