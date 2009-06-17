<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;

class postWidgetTextBackup
{
	public static function exportSingle(&$core,&$exp,$blog_id)
	{
		$exp->export('postwidgettext',
			'SELECT wtext_type, wtext_content, wtext_content_xhtml, wtext_xords, W.post_id '.
			'FROM '.$core->prefix.'post_wtext W, '.$core->prefix.'post P '.
			'WHERE P.post_id = W.post_id '.
			"AND P.blog_id = '".$blog_id."'"
		);
	}

	public static function exportFull(&$core,&$exp)
	{
		$exp->exportTable('post_wtext');
	}

	public static function importInit(&$bk,&$core)
	{
		$bk->cur_postwidgettext = $core->con->openCursor($core->prefix.'post_wtext');
		$bk->postwidgettext = new postWidgetText($core);
	}

	public static function importSingle(&$line,&$bk,&$core)
	{
		if ($line->__name == 'postwidgettext' && isset($bk->old_ids['post'][(integer) $line->post_id])) {
			$line->post_id = $bk->old_ids['post'][(integer) $line->post_id];

			$bk->postwidgettext->del($line->post_id,$line->wtext_type);

			$bk->cur_postwidgettext->clean();

			$bk->cur_postwidgettext->post_id =
				(integer) $line->post_id;
			$bk->cur_postwidgettext->wtext_type = 
				(string) $line->wtext_type;
			$bk->cur_postwidgettext->wtext_content = 
				(string) $line->wtext_content;
			$bk->cur_postwidgettext->wtext_content_xhtml = 
				(string) $line->wtext_content_xhtml;
			$bk->cur_postwidgettext->wtext_words = 
				(integer) $line->wtext_words;

			$bk->cur_postwidgettext->insert();
		}
	}

	public static function importFull(&$line,&$bk,&$core)
	{
		if ($line->__name == 'postwidgettext') {
			$bk->cur_postwidgettext->clean();

			$bk->cur_postwidgettext->post_id =
				(integer) $line->post_id;
			$bk->cur_postwidgettext->wtext_type = 
				(string) $line->wtext_type;
			$bk->cur_postwidgettext->wtext_content = 
				(string) $line->wtext_content;
			$bk->cur_postwidgettext->wtext_content_xhtml = 
				(string) $line->wtext_content_xhtml;
			$bk->cur_postwidgettext->wtext_words = 
				(integer) $line->wtext_words;

			$bk->cur_postwidgettext->insert();
		}
	}
}
?>