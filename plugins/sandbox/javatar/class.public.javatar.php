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

class publicJavatar
{
	public static function publicHeadContent()
	{
		global $core;
		
		if ($core->blog->settings->javatar_active)
		{
			$custom_css = $core->blog->settings->javatar_custom_css;		
			if (!empty($custom_css)) {
				if (strpos('/',$custom_css) === 0) {
					$css = $custom_css;
				}
				else {
					$css =
						$core->blog->settings->themes_url."/".
						$core->blog->settings->theme."/".
						$custom_css;
				}
			}
			else {
				$css = html::stripHostURL($core->blog->getQmarkURL().'pf=javatar/javatar-default.css');
			}
			echo
				'<style type="text/css" media="screen">@import url('.$css.');</style>'."\n";
		}
	}
	
	public static $c_info = array();
        
        public static function publicBeforeCommentCreate(&$cur)
        {
                global $core;
                $jabber = $_POST['c_jabber'];
		      $GLOBALS['_ctx']->comment_preview['jabber'] = $jabber;
                if (!empty($_POST['c_remember']))
                {
                        $c_cookie = array(
                                'name' => $cur->comment_author,
                                'mail' => $cur->comment_email,
                                'site' => $cur->comment_site,
                                'jabber' => $cur->comment_jabber);
                        $c_cookie = serialize($c_cookie);
                        setcookie('comment_info',$c_cookie,strtotime('+3 month'),'/');
                }
                $cur->comment_javatar = html::clean($javatar);
		if (!text::isEmail($cur->comment_jabber)) {
			throw new Exception(__('You must provide a valid jabber address.'));
		}
        }
        
        public static function coreBlogGetComments(&$c_rs)
        {
                $ids = array();
                while ($c_rs->fetch())
                {
                        if (!$c_rs->comment_trackback) {
                                $ids[] = $c_rs->comment_id;
                        }
                }
                if (empty($ids)) {
                        return;
                }
                
                $ids = implode(', ',$ids);
                
                $strReq =
                'SELECT comment_id, comment_jabber '.
                'FROM '.$c_rs->core->prefix.'comment '.
                'WHERE comment_id  IN ('.$ids.')';
                $rs = $c_rs->core->con->select($strReq);
                
                while ($rs->fetch())
                {
                        self::$c_info[$rs->comment_id] = array(
                                'javatar'=>$rs->comment_jabber
                                );
                }
                
                $c_rs->extend('rsExtCommentJavatar');
        }
}
?>
