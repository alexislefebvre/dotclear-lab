<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Puny-friendly', a plugin for Dotclear 2           *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Puny-friendly' (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('coreBlogGetPosts',
	create_function('&$rs','$rs->extend("punyFriendlyRsExtPost");'));

class punyFriendlyRsExtPost
{
	public static function getTrackbackData(&$rs)
	{
		$r = array(
			"<!--\n" => "<![CDATA[>\n<!--[\n",
			"-->\n" => "<!]]><!---->\n"
		);
		
		$res = rsExtPost::getTrackbackData($rs);
		return str_replace(array_keys($r),array_values($r),$res);
	}
}
?>