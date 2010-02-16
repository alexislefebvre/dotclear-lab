<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of fac, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
if (!$core->plugins->moduleExists('metadata')){return;}

$core->addBehavior('publicEntryAfterContent',array('facPublic','publicEntryAfterContent'));

class facPublic
{
	public static function publicEntryAfterContent($core,$_ctx)
	{
		if (!$core->blog->settings->fac_active || !$_ctx->exists('posts') || $_ctx->posts->post_type != 'post') return;

		$title = (string) $core->blog->settings->fac_public_title;
		if (empty($title)) $title = __('Related feed');
		$limit = (integer) $core->blog->settings->fac_public_limit;
		if ($limit < 1) $limit = 10;
		$types = @unserialize($core->blog->settings->fac_public_tpltypes);
		if (!is_array($types) || !in_array($core->url->type,$types)) return;

		$f = new fac($core);
		$url = $f->getFac($_ctx->posts->pots_id);
		if ($url->isEmpty()) return;

		$cache = is_dir(DC_TPL_CACHE.'/fac') ? DC_TPL_CACHE.'/fac' : null;

		$url = $url->meta_id;
		$feed = feedReader::quickParse($url,$cache);
		if (!$feed) return;

		$feed_title = empty($feed->title) ? __('Related feed') : $feed->title;
		$title = str_replace('%s',$feed_title,$title);

		echo 
		'<div class="post_fac">'.
		'<h2>'.$title.'</h2>'.
		'<ul>';
		foreach ($feed->items as $item)
		{
			echo 
			'<li>'.
			'<a href="'.$item->link.'" title="'.dt::str($core->blog->settings->date_format.', '.$core->blog->settings->time_format,$item->TS).'">'.
			($item->title ? $item->title : text::cutString(html::clean($item->content ? $item->content : $item->description),60)).'</a></li>';
			$i++;
			if ($i == $limit) break;
		}
		echo '</ul></div>';
	}
}
?>