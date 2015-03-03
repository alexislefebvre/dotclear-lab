<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007,2015                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

/** @doc
	Arlequin public interface
*/

publicArlequinEngine::trigger($core->blog);
$core->addBehavior('publicBeforeDocument',array('publicArlequinEngine','adjustCache'));
$core->tpl->addValue('themesList',array('publicArlequinInterface','template'));

class publicArlequinEngine
{
	public static $cookie_theme;
	public static $cookie_upddt;
	
	public static function trigger($blog)
	{
		$cname = base_convert($blog->uid,16,36);
		self::$cookie_theme = 'dc_theme_'.$cname;
		self::$cookie_upddt = 'dc_user_upddt_'.$cname;
		
		if (!empty($_REQUEST['theme'])) {
			# Set cookie for 365 days
			setcookie(self::$cookie_theme,$_REQUEST['theme'],time()+31536000,'/');
			setcookie(self::$cookie_upddt,time(),time()+31536000,'/');
			
			# Redirect if needed
			if (isset($_GET['theme'])) {
				$p = '/(\?|&)theme(=.*)?$/';
				http::redirect(preg_replace($p,'',http::getSelfURI()));
			}
			
			# Switch theme
			self::switchTheme($blog,$_REQUEST['theme']);
		}
		elseif (!empty($_COOKIE[self::$cookie_theme])) {
			self::switchTheme($blog,$_COOKIE[self::$cookie_theme]);
		}
	}
	
	public static function adjustCache($core)
	{
		if (!empty($_COOKIE[self::$cookie_upddt])) {
			$GLOBALS['mod_ts'][] = (integer) $_COOKIE[self::$cookie_upddt];
		}
	}
	
	public static function switchTheme($blog,$theme)
	{
		if ($blog->settings->multitheme->mt_exclude) {
			if (in_array($theme,explode('/',$blog->settings->multitheme->mt_exclude))) {
				return;
			}
		}
		
		$GLOBALS['__theme'] = $blog->settings->system->theme = $theme;
	}
}

class publicArlequinInterface
{
	public static function arlequinWidget($w)
	{
		return self::getHTML($w);
	}
	
	public static function template($attr)
	{
		return '<?php echo publicArlequinInterface::getHTML(); ?>';
	}
	
	public static function getHTML($w=false)
	{
		global $core;

		if ($w->offline)
			return;
		
		$cfg = @unserialize($core->blog->settings->multitheme->get('mt_cfg'));

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}
		
		if ($cfg === false ||
			($names = self::getNames()) === false) {
			return;
		}
		
		# Current page URL and the associated query string. Note : the URL for
		# the switcher ($s_url) is different to the URL for an item ($e_url)
		$s_url = $e_url = http::getSelfURI();
		
		# If theme setting is already present in URL, we will replace its value
		$replace = preg_match('/(\\?|&)theme\\=[^&]*/',$e_url);
		
		# URI extension to send theme setting by query string
		if ($replace) {
			$ext = '';
		}
		elseif (strpos($e_url,'?') === false) {
			$ext = '?theme=';
		}
		else {
			$ext = (substr($e_url,-1) == '?' ? '' : '&amp;').'theme=';
		}
		
		$res = '';
		foreach ($names as $k=>$v)
		{
			if ($k == $GLOBALS['__theme']) {
				$format = $cfg['a_html'];
			} else {
				$format = $cfg['e_html'];
			}
			
			if ($replace) {
				$e_url = preg_replace(
					'/(\\?|&)(theme\\=)([^&]*)/',
					'$1${2}'.addcslashes($k,'$\\'),
					$e_url);
				$val = '';
			}
			else {
				$val = html::escapeHTML(rawurlencode($k));
			}
			$res .= sprintf($format,
				$e_url,$ext,$val,
				html::escapeHTML($v['name']),
				html::escapeHTML($v['desc']),
				html::escapeHTML($k));
		}
		
		# Nothing to display
		if (!trim($res)) {
			return;
		}

		$res = sprintf($cfg['s_html'],$s_url,$res);
		
		if ($w) {

		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		$res;

		return $w->renderDiv($w->content_only,'arlequin '.$w->class,'',$res);
		}
		
		return $res;
	}
	
	public static function getNames()
	{
		global $core;
		
		$mt_exclude = $core->blog->settings->multitheme->mt_exclude;
		$exclude = array();
		if (!empty($mt_exclude)) {
			$exclude = array_flip(explode('/',$core->blog->settings->multitheme->mt_exclude));
		}
		
		$names = array_diff_key($core->themes->getModules(),$exclude);
		
		return empty($names) ? false : $names;
	}
}