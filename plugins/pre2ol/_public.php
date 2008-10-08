<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'pre2ol', a plugin for Dotclear 2                  *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Carnaval' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent',array('pre2olPublic','publicHeadContent'));

class pre2olPublic
{
	public static function publicHeadContent(&$core)
	{
		if (!$core->blog->settings->pre2ol_enabled) {
			return;
		}
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo
		'<script type="text/javascript" src="'.$url.'/js/pre2ol.js"></script>'."\n".
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/pre2ol.css);'."\n".
		"</style>\n".
		'<style type="text/css">'."\n".self::pre2olStyleHelper()."\n</style>\n";
	}
	
	public static function pre2olStyleHelper()
	{
		$bgc1 = $GLOBALS['core']->blog->settings->pre2ol_bgcolor1;
		$bgc2 = $GLOBALS['core']->blog->settings->pre2ol_bgcolor2;
		$c = $GLOBALS['core']->blog->settings->pre2ol_color;
		
		$css = array();
		
		self::prop($css,'div.code,textarea.code,.code-container,.code,ol.pre2ol li,.code-tools a ','background-color',$bgc1);
		self::prop($css,'ol.pre2ol li.lizebra ','background-color',$bgc2);
		self::prop($css,'textarea.code,ol.pre2ol li span,.code-tools a ','color',$c);
		$res = '';
		foreach ($css as $selector => $values) {
			$res .= $selector." {\n";
			foreach ($values as $k => $v) {
				$res .= $k.':'.$v.";\n";
			}
			$res .= "}\n";
		}
		return $res;
	}
	
	protected static function prop(&$css,$selector,$prop,$value)
	{
		if ($value) {
			$css[$selector][$prop] = $value;
		}
	}
}
?>
