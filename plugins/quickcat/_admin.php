<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "QuickCat" plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }
$core->addBehavior('adminPostHeaders',array('quickCatBehaviors', 'adminPostHeaders'));
$core->rest->addFunction('createCategory',array('quickCatBehaviors','createCategory'));
$core->rest->addFunction('getCategoriesAsSelect',array('quickCatBehaviors','getCategoriesAsSelect'));


class quickCatBehaviors {

	public static function adminPostHeaders() {
		return
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			"dotclear.msg.new_category = '".html::escapeJS(__('New category...'))."';\n".
			"dotclear.msg.cat_title = '".html::escapeJS(__('Title :'))."';\n".
			"dotclear.msg.parent_category = '".html::escapeJS(__('Parent category :'))."';\n".
			"dotclear.msg.create_cat = '".html::escapeJS(__('Create category'))."';\n".
			"\n//]]>\n".
			'</script>'.
			'<script type="text/javascript" src="index.php?pf=quickcat/quickcat.js"></script>'."\n";
	}
	
	public static function createCategory($core,$get,$post) {
		if (empty($post['cat_title'])) {
			throw new Exception(__('No category title provided'));
		}
		$cur = $core->con->openCursor($core->prefix.'category');
		$cur->cat_title = $cat_title = $post['cat_title'];
		
		if (isset($post['cat_desc'])) {
			$cur->cat_desc = $post['cat_desc'];
		}
		
		if (isset($post['cat_url'])) {
			$cur->cat_url = $post['cat_url'];
		} else {
			$cur->cat_url = '';
		}
		if (isset($post['parent_cat'])) {
			$parent_cat = $post['parent_cat'];
		} else {
			$parent_cat = '';
		}
		
		# --BEHAVIOR-- adminBeforeCategoryCreate
		$core->callBehavior('adminBeforeCategoryCreate',$cur);
			
		$id = $core->blog->addCategory($cur,(integer) $parent_cat);
			
		# --BEHAVIOR-- adminAfterCategoryCreate
		$core->callBehavior('adminAfterCategoryCreate',$cur,$id);
		return $id;
		
	}

	public static function getCategoriesAsSelect($core,$get) {
		if (!empty($get['select'])) {
			$selected = (integer) $get['select'];
		} else {
			$selected=0;
		}
		$categories_combo = array('&nbsp;' => '');
		$categories = $core->blog->getCategories(array('post_type'=>'post'));
		while ($categories->fetch()) {
			$categories_combo[] = new formSelectOption(
				str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.html::escapeHTML($categories->cat_title),
				$categories->cat_id
			);
		}
		return 	form::combo('cat_id',$categories_combo,$selected,'maximal',3);
	}
}

?>