<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Google Stuff', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  xave and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

	$core->addBehavior('publicHeadContent',array('googlestuffPublicBehaviours','publicHeadContent'));
	$core->addBehavior('publicFooterContent',array('googlestuffPublicBehaviours','publicFooterContent'));


class googlestuffPublicBehaviours
{
	public static function publicHeadContent(&$core)
	{
		$res = '';
		
		if ($core->blog->settings->googlestuff_verify != "") {
			$res .= '<meta name="google-site-verification" content="'.$core->blog->settings->googlestuff_verify.'" />'."\n";
		}
		
		if ($core->blog->settings->googlestuff_uacct != "") {
			$res .= '<script type="text/javascript">'."\n".
				'var _gaq = _gaq || [];'."\n".
				'_gaq.push([\'_setAccount\', \''.$core->blog->settings->googlestuff_uacct.'\']);'."\n".
				'_gaq.push([\'_trackPageview\']);'."\n".
				'</script>'."\n";
		}
	
	echo $res;
	}
	
	public static function publicFooterContent(&$core)
	{
		if ($core->blog->settings->googlestuff_uacct != "") {
			$res = '<script type="text/javascript">'."\n".
				'(function() {'."\n".
				'var ga = document.createElement(\'script\');'."\n".
				'ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' :'.
					'\'http://www\') + \'.google-analytics.com/ga.js\';'."\n".
				'ga.setAttribute(\'async\', \'true\');'."\n".
				'document.documentElement.firstChild.appendChild(ga);'."\n".
				'})();'."\n".
				'</script>';
				
			echo $res;
		}
	}
	
}
?>