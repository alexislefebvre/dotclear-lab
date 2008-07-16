<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Javatar', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Javatar' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class rsExtCommentJavatar
{
	public static function getJID()
	{
		global $core;
		return md5(rsExtCommentJavatar::getAuthorJabber(publicJavatar::$c_info[$rs->comment_id]['javatar']));
	}

        public static function getAuthorJabber(&$rs,$lcase=true)
        {
		global $core;
                if ($res = @publicJavatar::$c_info[$rs->comment_id]['javatar'])
                {
                        if ($lcase) {
                                return strtolower($res);
                        }
                        return $res;
                }
                if ($lcase) {
                        return ;
                }
                return ;
        }
}
?>
