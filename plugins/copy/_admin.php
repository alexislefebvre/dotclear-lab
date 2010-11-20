<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Copy', a plugin for Dotclear 2                    *
 *                                                             *
 *  Copyright (c) 2010                                         *
 *  Alexandre Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Copy' (see COPYING.txt);               *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

if (basename($_SERVER['PHP_SELF']) != 'post.php' || empty($_REQUEST['id'])) {
	return;
}

$core->addBehavior('adminPostHeaders',array('copyBehaviors','jsLoad'));

if (empty($_POST['copy'])) {
	return;
}

$_REQUEST['copy_id'] = $_REQUEST['id'];
unset($_REQUEST['id']);
$_POST['save'] = true;

$core->addBehavior('adminBeforePostCreate',array('copyBehaviors','adminBeforePostCreate'));
$core->addBehavior('adminAfterPostCreate',array('copyBehaviors','adminAfterPostCreate'));

class copyBehaviors
{
	public static function jsLoad()
	{
		return
		'<script type="text/javascript" src="index.php?pf=copy/post.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('dotclear.msg.create_copy',__('create a copy')).
		dcPage::jsVar('dotclear.msg.save_as_new',__('save as a new post')).
		"\n//]]>\n".
		"</script>\n";
	}
	
	public static function adminBeforePostCreate($cur)
	{
		global $core;
		
		$params = array('post_id'=>$_REQUEST['copy_id']);
		$rs = $core->blog->getPosts($params);
		
		$cur->post_tz = $rs->post_tz;
		$cur->post_type = $rs->post_type;
		$cur->post_meta = $rs->post_meta;
		$cur->blog_id = $blog_id;
	}
	
	public static function adminAfterPostCreate($cur,$return_id)
	{
		global $core;
		
		$params = array('post_id'=>$_REQUEST['copy_id']);
		$rs = $core->blog->getPosts($params);
		
		# Update metadata
		$post_meta = @unserialize($rs->post_meta);
		
		if (!is_array($post_meta)) {
			return;
		}
		
		foreach($post_meta as $meta_type => $values)
		{
			foreach ($values as $meta_id)
			{
				$cur = $core->con->openCursor($core->prefix.'meta');
				$cur->meta_type = $meta_type;
				$cur->meta_id = $meta_id;
				$cur->post_id = $return_id;
				$cur->insert();
			}
		}
	}
}
?>