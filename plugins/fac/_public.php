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

$core->addBehavior('publicEntryAfterContent',array('facPublic','publicEntryAfterContent'));

class facPublic
{
	public static function publicEntryAfterContent($core,$_ctx)
	{
		$core->blog->settings->addNamespace('fac');
		
		# Not active or not a post
		if (!$core->blog->settings->fac->fac_active 
		 || !$_ctx->exists('posts') || $_ctx->posts->post_type != 'post')
		{
			return;
		}
		# Not in page to show
		$types = @unserialize($core->blog->settings->fac->fac_public_tpltypes);
		if (!is_array($types) || !in_array($core->url->type,$types))
		{
			return;
		}
		# Get related feed
		$params = array('meta_type'=>'fac','post_id'=>$_ctx->posts->post_id,'limit'=>1);
		$fac_url = $core->meta->getMetadata($params);
		if ($fac_url->isEmpty()) return;

		# Get related format
		$params = array('meta_type'=>'facformat','post_id'=>$_ctx->posts->post_id,'limit'=>1);
		$fac_format = $core->meta->getMetadata($params);
		if ($fac_format->isEmpty())	return;
		
		# Get format info
		$default_format = array(
			'name' => 'default',
			'dateformat' => '',
			'lineslimit' => '5',
			'linestitletext' => '%T',
			'linestitleover' => '%D',
			'linestitlelength' => '150',
			'showlinesdescription' => '0',
			'linesdescriptionlength' => '350',
			'linesdescriptionnohtml' => '1',
			'showlinescontent' => '0',
			'linescontentlength' => '350',
			'linescontentnohtml' => '1'
		);
		
		$formats = @unserialize($core->blog->settings->fac->fac_formats);
		if (empty($formats) || !is_array($formats) || !isset($formats[$fac_format->meta_id]))
		{
			$format = $default_format;
		}
		else
		{
			$format = array_merge($default_format,$formats[$fac_format->meta_id]);
		}
		
		# Read feed url
		$cache = is_dir(DC_TPL_CACHE.'/fac') ? DC_TPL_CACHE.'/fac' : null;
		try
		{
			$feed = feedReader::quickParse($fac_url->meta_id,$cache);
		}
		catch (Exception $e)
		{
			$feed = null;
		}
		# No entries
		if (!$feed)
		{
			return;
		}
		# Feed title
		$feedtitle = '';
		if ('' != $core->blog->settings->fac->fac_defaultfeedtitle)
		{
			$feedtitle = '<h3>'.html::escapeHTML(empty($feed->title) ? 
				str_replace('%T',__('a related feed'),$core->blog->settings->fac->fac_defaultfeedtitle) : 
				str_replace('%T',$feed->title,$core->blog->settings->fac->fac_defaultfeedtitle)
			).'</h3>';
		}
		# Feed desc
		$feeddesc = '';
		if ($core->blog->settings->fac->fac_showfeeddesc && '' != $feed->description)
		{
			'<p>'.context::global_filter($feed->description,1,1,0,0,0).'</p>';
		}
		# Date format
		$dateformat = '' != $format['dateformat'] ? 
			$format['dateformat'] :
			$core->blog->settings->system->date_format.','.$core->blog->settings->system->time_format;
		# Enrties limit
		$entrieslimit = abs((integer) $format['lineslimit']);
		$uselimit = $entrieslimit > 0 ? true : false;
		
		echo 
		'<div class="post-fac">'.
		$feedtitle.$feeddesc.
		'<dl>';
		
		$i = 0;
		foreach ($feed->items as $item)
		{
			# Format date
			$date = dt::dt2str($dateformat,$item->pubdate);
			# Entries title
			$title = context::global_filter(
				str_replace(
					array('%D','%T','%A','%E','%C'),
					array($date,$item->title,$item->creator,$item->description,$item->content),
					$format['linestitletext']
				),
				0,1,abs((integer) $format['linestitlelength']),0,0
			);
			# Entries over title
			$overtitle = context::global_filter(
				str_replace(
					array('%D','%T','%A','%E','%C'),
					array($date,$item->title,$item->creator,$item->description,$item->content),
					$format['linestitleover']
				),
				0,1,350,0,0
			);
			# Entries description
			$description = '';
			if ($format['showlinesdescription'] && '' != $item->description) {
				$description = '<dd>'.
				context::global_filter(
					$item->description,
					0,
					(integer) $format['linesdescriptionnohtml'],
					abs((integer) $format['linesdescriptionlength']),
					0,0
				).'</dd>';
			}
			# Entries content
			$content = '';
			if ($format['showlinescontent'] && '' != $item->content) {
				$content = '<dd>'.
				context::global_filter(
					$item->content,
					0,
					(integer) $format['linescontentnohtml'],
					abs((integer) $format['linescontentlength']),
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