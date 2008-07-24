<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class rsExtCommentEmpreinte
{
	public static function getAuthorLink(&$rs)
	{
		global $core;
		
		$res = rsExtComment::getAuthorLink($rs);
		
		if (!( $mask = $core->blog->settings->empreinte_authorlink_mask
		and isset(publicEmpreinte::$c_info[$rs->comment_id]['browser'])
		and isset(publicEmpreinte::$c_info[$rs->comment_id]['system']) ))
		{
			return $res;
		}
		
		return sprintf($mask,$res,tplEmpreinte::PluginFileURL(),
			$rs->getBrowser(),$rs->getBrowser(1),
			$rs->getSystem(),$rs->getSystem(1));
	}
	
	public static function getBrowser(&$rs,$lcase=true)
	{
		if ($res = @publicEmpreinte::$c_info[$rs->comment_id]['browser'])
		{
			if ($lcase) {
				return strtolower($res);
			}
			return $res;
		}
		if ($lcase) {
			return 'unknown';
		}
		return __('Unknown');
	}
	
	public static function getSystem(&$rs,$lcase=false)
	{
		if ($res = @publicEmpreinte::$c_info[$rs->comment_id]['system'])
		{
			if ($lcase) {
				return strtolower($res);
			}
			return $res;
		}
		if ($lcase) {
			return 'unknown';
		}
		return __('Unknown');
	}
}
?>