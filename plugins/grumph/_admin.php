<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Grumph,
# a plugin for DotClear2.
#
# Copyright (c) 2010 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }


$_menu['Plugins']->addItem(__('Grumph'),'plugin.php?p=grumph','index.php?pf=grumph/icon.png',
		preg_match('/plugin.php\?p=grumph(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));


$core->addBehavior('adminBeforePostCreate',array('grumphBehaviors','setResources'));
$core->addBehavior('adminBeforePostUpdate',array('grumphBehaviors','setResources'));

$core->addBehavior('adminBeforePageCreate',array('grumphBehaviors','setResources'));
$core->addBehavior('adminBeforePageUpdate',array('grumphBehaviors','setResources'));

$core->rest->addFunction('getPostIDs',array('grumphRest','getPostIDs'));
$core->rest->addFunction('updResources',array('grumphRest','updResources'));

# BEHAVIORS
class grumphBehaviors
{
	public static function setResources($cur,$post_id=null)
	{
		$grumph = new dcGrumph($GLOBALS['core']);
		$cur->post_res = serialize($grumph->grabResources($cur));
	}
}

class grumphRest
{
	public static function getPostIDs($core,$get,$post) {
		$count_only = !empty($get['count_only']);
		$offset = isset($get['offset'])?((integer)$get['offset']):0;
		$limit = isset($get['limit'])?((integer)$get['limit']):30;
		$params = array();
		if (!$count_only) {
			$params["limit"] = array($offset,$limit);
		}
		if (empty($get['all_posts'])) {
				$params['sql'] = " and P.post_res is NULL ";
		}
		$rs = $core->blog->getPosts($params,$count_only);
		if ($count_only)
			return $rs->f(0);
		$rsp = new xmlTag();
		while ($rs->fetch()) {
			$post = new xmlTag("post");
			$post->id=$rs->post_id;
			$rsp->insertNode($post);
		}
		return $rsp;
		
	}
	
	public static function updResources($core,$get,$post) {
		if (empty($post['ids'])) {
			throw new Exception('No post ID');
		}
		$ids = explode(',',$post['ids']);
		$rs = $core->blog->getPosts(array("post_id" => $ids));
		$grumph = new dcGrumph($core);
		while($rs->fetch()) {
			$grumph->updatePostResources($rs);				
		}
		return true;
	}

}

?>
