<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$mytypes = muppet::getPostTypes();
if (!empty($mytypes))
{
	$core->addBehavior('initWidgets',array('muppetWidgets','initWidgetsMuppetBestof'));
	$core->addBehavior('initWidgets',array('muppetWidgets','initWidgetsMuppetLastPosts'));
}

class muppetWidgets
{
	public static function initWidgetsMuppetBestof($w)
	{
		$w->create('bestofMuppetWidget',__('Muppet: selected entries'),array('widgetsMuppet','bestofWidget'));
		$w->bestofMuppetWidget->setting('title',__('Title:'),__('Best of me'));
		$w->bestofMuppetWidget->setting('homeonly',__('Home page only'),1,'check');
		$ty = muppet::getPostTypes();
		$types = array('' => '');
		foreach ($ty as $k =>$v) {
			$types[ucfirst($v['name'])] = $k;
		}
		$w->bestofMuppetWidget->setting('posttype',__('Type:'),'','combo',$types);
		unset($ty,$types);
	}
	public static function initWidgetsMuppetLastPosts($w)
	{
		global $core;
		$w->create('lastthreadsMuppetWidget',__('Muppet: last posts'),array('widgetsMuppet','lastpostsWidget'));;
		$w->lastthreadsMuppetWidget->setting('title',__('Title:'),__('Last entries'));
		$rs = $core->blog->getCategories();
		$categories = array('' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$w->lastthreadsMuppetWidget->setting('category',__('Category:'),'','combo',$categories);
		unset($rs,$categories);
		$w->lastthreadsMuppetWidget->setting('limit',__('Entries limit:'),10);
		$w->lastthreadsMuppetWidget->setting('homeonly',__('Home page only'),1,'check');
		$ty = muppet::getPostTypes();
		$types = array('' => '');
		foreach ($ty as $k =>$v) {
			$types[ucfirst($v['name'])] = $k;
		}
		$w->lastthreadsMuppetWidget->setting('posttype',__('Type:'),'','combo',$types);
		unset($ty,$types);
	}
}
?>