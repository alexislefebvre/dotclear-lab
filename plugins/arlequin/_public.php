<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007                                         *
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

/** @doc
	Arlequin public interface
*/

# Comportement fantôme qui est appelé avant le chargement des plugins
# (très utile, n'est-ce pas ?)
//$core->addBehavior('coreBlogConstruct',array('publicArlequinEngine','trigger'));
publicArlequinEngine::trigger($core->blog);
$core->addBehavior('publicPrepend',array('publicArlequinEngine','adjustDefault'));
$core->addBehavior('publicBeforeDocument',array('publicArlequinEngine','adjustCache'));
$core->tpl->addValue('themesList',array('publicArlequinInterface','template'));

class publicArlequinEngine
{
	public static function trigger(&$blog)
	{
		$cookie_theme = 'mt_blog_'.$blog->id.'_theme';
		$cookie_upddt = 'mt_blog_'.$blog->id.'_upddt';
		
		if (!empty($_REQUEST['theme']))
		{
			# Set cookie for 365 days
			setcookie($cookie_theme,$_REQUEST['theme'],time()+31536000,'/');
			setcookie($cookie_upddt,time(),time()+31536000,'/');
			self::switchTheme($blog,$_REQUEST['theme']);
		}
		elseif (!empty($_COOKIE[$cookie_theme]))
		{
			self::switchTheme($blog,$_COOKIE[$cookie_theme]);
		}
	}
	
	public static function adjustDefault(&$core)
	{
		global $mt_dbt,$mt_cbt;
		
		# Verify if the choosed theme exists
		if (isset($mt_dbt) && $mt_dbt != 'default' && $mt_cbt != 'default' &&
			$core->blog->settings->theme == 'default' &&
			$core->themes->moduleExists($mt_dbt))
		{
			$core->blog->settings->theme = $mt_dbt;
			$core->themes->loadNsFile($mt_dbt,'public');
		}
	}
	
	public static function adjustCache(&$core)
	{
		$cookie_upddt = 'mt_blog_'.$core->blog->id.'_upddt';
		
		if (!empty($_COOKIE[$cookie_upddt])) {
			$GLOBALS['mod_ts'][] = (int) $_COOKIE[$cookie_upddt];
		}
	}
	
	public static function switchTheme(&$blog,$theme)
	{
		global $mt_dbt,$mt_cbt;
		
		if ($blog->settings->theme != $theme)
		{
			$mt_dbt = $blog->settings->theme;
			$mt_cbt = $blog->settings->theme = $theme;
		}
	}
}

class publicArlequinInterface
{
	public static function widget($w)
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
		
		$cfg = @unserialize($core->blog->settings->get('mt_cfg'));
		
		if ($cfg === false ||
			($cfg['homeonly'] && $core->url->type != 'default') ||
			($names = self::getNames()) === false) {
			return;
		}
		
		# Current page URL and the associated query string. Note : the URL for
		# the switcher ($s_url) is different to the URL for an item ($e_url)
		$s_url = $e_url = http::getSelfURI();
		
		# If theme setting is already present in URL, we will replace it's value
		$replace = (bool) preg_match('/(\\?|&)theme\\=[^&]*/',$e_url);
		
		# URI extension to send theme setting by query string
		$ext = $replace
			? ''
			: (strpos($e_url,'?') === false
				? '?'
				: (empty($_SERVER['QUERY_STRING'])
					? ''
					: '&amp;')).'theme=';
		
		$res = '';
		foreach ($names as $k=>$v)
		{
			if ($k == $core->blog->settings->theme) {
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
				html::escapeHTML($v['desc']));
		}
		
		# Nothing to display
		if (!trim($res)) {
			return;
		}

		$res = sprintf($cfg['s_html'],$s_url,$res);
		
		if ($w) {
			$res = '<div id="arlequin">'.
				($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
				"\n".$res."</div>\n";
		}
		
		return $res;
	}
	
	public static function getNames()
	{
		global $core;
		
		$mt_exclude = $core->blog->settings->mt_exclude;
		$exclude = array();
		if (!empty($mt_exclude)) {
			$exclude = array_flip(explode('/',$core->blog->settings->mt_exclude));
		}
		
		$names = array_diff_key($core->themes->getModules(),$exclude);
		
		return empty($names) ? false : $names;
	}
}
?>
