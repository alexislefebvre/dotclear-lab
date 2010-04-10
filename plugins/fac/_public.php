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
		$s = facSettings($core);

		# Not active or not a post
		if (!$s->fac_active 
		 || !$_ctx->exists('posts') || $_ctx->posts->post_type != 'post') {
			return;
		}
		# Not in page to show
		$types = @unserialize($s->fac_public_tpltypes);
		if (!is_array($types) || !in_array($core->url->type,$types)) {
			return;
		}
		# Get related feed
		$meta = new dcMeta($core);
		$rs = $meta->getMeta('fac',1,null,$_ctx->posts->post_id);
		# No feed
		if ($rs->isEmpty()) {
			return;
		}
		# Read feed url
		$cache = is_dir(DC_TPL_CACHE.'/fac') ? DC_TPL_CACHE.'/fac' : null;
		$feed = feedReader::quickParse($rs->meta_id,$cache);
		# No entries
		if (!$feed) {
			return;
		}
		# Feed title
		$feedtitle = '';
		if ('' != $s->fac_defaultfeedtitle) {
			$feedtitle = '<h3>'.html::escapeHTML(
				empty($feed->title) ? 
					str_replace('%T',__('a related feed'),$s->fac_defaultfeedtitle) : 
					str_replace('%T',$feed->title,$s->fac_defaultfeedtitle)
			).'</h3>';
		}
		# Feed desc
		$feeddesc = '';
		if ($s->fac_showfeeddesc && '' != $feed->description) {
			'<p>'.context::global_filter($feed->description,1,1,0,0,0).'</p>';
		}
		# Date format
		$dateformat = '' != $s->fac_dateformat ? 
			$s->fac_dateformat :
			$core->blog->settings->date_format.','.$core->blog->settings->time_format;
		# Enrties limit
		$entrieslimit = abs((integer) $s->fac_lineslimit);
		$uselimit = $entrieslimit > 0 ? true : false;

		echo 
		'<div class="post-fac">'.
		$feedtitle.$feeddesc.
		'<dl>';
		foreach ($feed->items as $item)
		{
			# Format date
			$date = dt::dt2str($dateformat,$item->pubdate);
			# Entries title
			$title = context::global_filter(
				str_replace(
					array('%D','%T','%A','%E','%C'),
					array($date,$item->title,$item->creator,$item->description,$item->content),
					$s->fac_linestitletext
				),
				0,1,abs((integer) $s->fac_linestitlelength),0,0
			);
			# Entries over title
			$overtitle = context::global_filter(
				str_replace(
					array('%D','%T','%A','%E','%C'),
					array($date,$item->title,$item->creator,$item->description,$item->content),
					$s->fac_linestitleover
				),
				0,1,350,0,0
			);
			# Entries description
			$description = '';
			if ($s->fac_showlinesdescription && '' != $item->description) {
				$description = '<dd>'.
				context::global_filter(
					$item->description,
					0,
					(integer) $s->fac_linesdescriptionnohtml,
					abs((integer) $s->fac_linesdescriptionlength),
					0,0
				).'</dd>';
			}
			# Entries content
			$content = '';
			if ($s->fac_showlinescontent && '' != $item->content) {
				$content = '<dd>'.
				context::global_filter(
					$item->content,
					0,
					(integer) $s->fac_linescontentnohtml,
					abs((integer) $s->fac_linescontentlength),
					0,0
				).'</dd>';
			}

			echo '<dt><a href="'.$item->link.'" title="'.$overtitle.'">'.$title.'</a></dt>'.$description.$content;

			$i++;
			if ($uselimit && $i == $entrieslimit) break;
		}
		echo '</dl></div>';
	}
}
?>