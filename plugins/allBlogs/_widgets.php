<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of All Blogs List, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

$core->addBehavior('initWidgets', array('allBlogsBehavior', 'initWidgets'));

class allBlogsBehavior
{
	public static function initWidgets(&$w) {
		global $core;
		$w->create('allBlogs',__('All Blogs'),array('publicAllBLogs','allBlogs'));
		$w->allBlogs->setting('title',__('Title:'),__('All Blogs'));
		$w->allBlogs->setting('homeonly',__('Home page only'),1,'check');
		$w->allBlogs->setting('combo',__('Display list in combo box'),0,'check');
		$w->allBlogs->setting('excluded',__('Excluded blogs:'),'','text');
		$rs = $core->getBlogs();
		if (!$rs->isEmpty()) {
	        while ($rs->fetch()) {
				if($rs->nb_post > 0) {
					$w->allBlogs->setting($rs->blog_id,html::escapeHTML($rs->blog_name),
					false, 'check');			
				}
			}
		}
	}
}
?>