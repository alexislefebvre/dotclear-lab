<?php
/***************************************************************\
 *  This is 'Not Evil Ads', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Not evil ads' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

include_once dirname(__FILE__).'/lib/class.notevilads.php';

$core->addBehavior('publicBeforeDocument',array('publicNotEvilAds','adjustCache'));

$core->url->register('notEvilAdsXML','notEvilAdsXML','^notEvilAdsXML.*$',array('publicNotEvilAds','XMLinterface'));

$core->tpl->addValue('notEvilAdsShowAd', array('publicNotEvilAds','showAd'));
$core->tpl->addValue('notEvilAdsShowTrigger', array('publicNotEvilAds','showTrigger'));
$core->tpl->addValue('neaTriggerURI',array('publicNotEvilAds','tplTriggerURI'));
$core->tpl->addBlock('neaIf',array('publicNotEvilAds','tplStatusIf'));

global $__notEvilAds;
$nea_settings = notEvilAds::loadSettings($core->blog->settings->get('nea_settings'));
$nea_ads = notEvilAds::loadAds($core->blog->settings->get('nea_ads'));

if (!is_array($nea_ads) || !is_array($nea_settings)) {
	$__notEvilAds = null;
	return;
}

$__notEvilAds = new notEvilAds($nea_settings,$nea_ads);

# This will work if JavaScript fail
if (!empty($_REQUEST['notEvilAdsTriggerOn'])) {
	$__notEvilAds->setStatus(true);
	publicNotEvilAds::triggerUserPrefs();
}
elseif (!empty($_REQUEST['notEvilAdsTriggerOff'])) {
	$__notEvilAds->setStatus(false);
	publicNotEvilAds::triggerUserPrefs();
}

if (isset($_REQUEST['showAds']))
{
	$__notEvilAds->setStatus((bool) $_REQUEST['showAds']);
	publicNotEvilAds::triggerUserPrefs();
}

class publicNotEvilAds
{
	public static function triggerUserPrefs()
	{
		# Set cookie for 365 days
		setcookie('user_upddt',time(),time()+31536000,'/');
	}
	
	public static function adjustCache(&$core)
	{
		if (!empty($_COOKIE['user_upddt'])) {
			$GLOBALS['mod_ts'][] = (int) $_COOKIE['user_upddt'];
		}
	}

	private static function getOperator($op)
	{
		switch (strtolower($op)) {
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
	
	public static function tplStatusIf($args,$content)
	{
		$if = array();
		
		if (isset($args['status'])) {
			$sign = (boolean) $args['status'] ? '' : '!';
			$if[] = $sign.'$__notEvilAds->getStatus()';
		}
		
		if (empty($if)) {
			return $content;
		}
		else {
			$op = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
			return '<?php if ($__notEvilAds !== null && ('.implode(' '.$op.' ',$if).')) : ?>'.$content.'<?php endif; ?>';
		}
	}
	
	public static function triggerURI($status=null)
	{
		global $__notEvilAds;
		
		if ($__notEvilAds === null) {
			$status = false;
		}
		elseif ($status === null) {
			$status = !$GLOBALS['__notEvilAds']->getStatus();
		}
		$status = $status ? '1' : '0';
		
		$uri = http::getSelfURI();
		
		# If showAds setting is already present in URI, we will replace its value
		if (preg_match('/(\\?|&)showAds\\=[^&]*/',$uri)) {
			$uri = preg_replace(
				'/(\\?|&)(showAds\\=)([^&]*)/',
				'$1${2}'.$status,
				$uri);
		}
		else {
			$ext =
				(strpos($uri,'?') === false
				? '?'
				: (empty($_SERVER['QUERY_STRING'])
					? ''
					: '&amp;')).
				'showAds=';
			$uri = $uri.$ext.$status;
		}
		
		return $uri;
	}
	
	public static function tplTriggerURI($args)
	{
		if (isset($args['status'])) {
			$status = $args['status'] ? 'true' : 'false';
		}
		else {
			$status = 'null';
		}
		return '<?php echo publicNotEvilAds::triggerURI('.$status.'); ?>';
	}
	
	public static function showAdsInWidgets(&$w,$i)
	{
		global $core,$__notEvilAds;
		
		$ad = $__notEvilAds->showAd($w->identifier);
		
		# Show nothing on error or if not allowed
		if ($ad === null || $ad['disable'] || ($ad['nothome'] && $core->url->type == 'default'))
			return '';
		
		$optTitle = $ad['title'] ? '<h2>'.$ad['title'].'</h2>'."\n" : '';
		
		return ($__notEvilAds->getStatus() || !$ad['notevil'])
				? "<div id=\"".$ad['identifier']."\" ".$ad['attr'].">\n".
				$optTitle.$ad['htmlcode'].
				"\n</div>"
				: '';
	}
	
	public static function triggerAdsInWidgets(&$w)
	{
		global $core,$__notEvilAds;
		
		if ($__notEvilAds->getStatus())
		{	$submitName = 'Off'; $submitValue=html::escapeHTML($w->hValue); }
		else
		{	$submitName = 'On'; $submitValue=html::escapeHTML($w->sValue); }
		
		return '
		<div id="notEvilAdsTriggerDiv" style="text-align:center">
		<form id="notEvilAdsForm" action="'.http::getSelfURI().'" method="post">
		<p><input type="submit" name="notEvilAdsTrigger'.$submitName.'" id="notEvilAdsTrigger" value="'.$submitValue.'"/></p>
		</form>
		</div>';
	}

	public static function showAd($attr)
	{
		global $core,$__notEvilAds;
		
		if (empty($attr['id']))
			return '<p><em>'.__('No identifier specified.').'</em></p>';
	
		$res = 
		"\$id = \"".addslashes($attr['id'])."\";
		
		\$ad = \$__notEvilAds->showAd(\$id);
		
		# Show nothing on error or if not allowed
		if (\$ad === null
			|| \$ad['disable']
			|| (\$ad['nothome'] && \$core->url->type == 'default'))
			echo '';
		else
			echo
			'<div id=\"'.\$ad['identifier'].'\" '.\$ad['attr'].'>'.
			(\$__notEvilAds->getStatus()
				? \$ad['htmlcode'] : '').
			'</div>';";
		
		return '<?php '.$res.' ?>';
	}
	
	public static function showTrigger($attr)
	{
		global $core,$__notEvilAds;
		
		if (is_array($attr))
		{
			$attr = isset($attr['extra']) ? ' '.$attr['extra'] : '';
			$hValue = isset($attr['hide']) ? $attr['hide'] : __('Hide ads');
			$sValue = isset($attr['show']) ? $attr['show'] : __('Show me ads');
		}
		else
		{
			$attr = '';
			$hValue = __('Hide ads');
			$sValue = __('Show me ads');
		}
		$res =
		"
		if (\$__notEvilAds->getStatus())
		{	\$submitName = 'Off'; \$submitValue=html::escapeHTML(\"".$hValue."\"); }
		else
		{	\$submitName = 'On'; \$submitValue=html::escapeHTML(\"".addslashes($sValue)."\"); }
		
		echo '<div id=\"notEvilAdsTriggerDiv\"".addcslashes($attr,"'").">
		<form id=\"notEvilAdsForm\" action=\"'.http::getSelfURI().'\" method=\"post\">
		<p><input type=\"submit\" name=\"notEvilAdsTrigger'.\$submitName.'\" id=\"notEvilAdsTrigger\" value=\"'.\$submitValue.'\"/></p>
		</form>
		</div>';";
		
		return '<?php '.$res.' ?>';
	}

	public static function XMLinterface()
	{
		global $core,$__notEvilAds;

		if (isset($_REQUEST['notEvilAdsGetContent']))
		{
			$id = (string) $_REQUEST['notEvilAdsGetContent'];
			echo $__notEvilAds->sendHTMLCode($id);
		}
		else
		{
			echo $__notEvilAds->sendXMLStatus();
		}
	}
}
?>
