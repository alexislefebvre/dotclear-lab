<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Copy, a plugin for Dotclear.
# 
# Copyright (c) 2010,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

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
		$cur->blog_id = $rs->blog_id;
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