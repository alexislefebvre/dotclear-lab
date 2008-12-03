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
		if ($core->blog->settings->googlestuff_verify != "") {
			$res = '<meta name="verify-v1" content="'.$core->blog->settings->googlestuff_verify.'" />'."\n";
			echo $res;
		}
	}
	
	public static function publicFooterContent(&$core)
	{
		if ($core->blog->settings->googlestuff_uacct != "") {
			$res = '<script type="text/javascript">'."\n".
				'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");'."\n".
				'document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));'."\n".
				'</script>'."\n".
				'<script type="text/javascript">'."\n".
				'if (typeof(_gat) !=  "undefined") {'."\n".
				'	var pageTracker = _gat._getTracker("'.
				$core->blog->settings->googlestuff_uacct.
				'");'."\n".
				'	pageTracker._initData();'."\n".
				'	pageTracker._trackPageview();'."\n".
				'}'."\n".
				'</script>'."\n";
			echo $res;
		}
	}
	
}
?>