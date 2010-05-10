<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of wikioWorld, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicHeadContent',
	array('wikioWorldPublicBehavior','publicHeadContent')
);
$core->addBehavior('publicEntryBeforeContent',
	array('wikioWorldPublicBehavior','publicEntryBeforeContent')
);
$core->addBehavior('publicEntryAfterContent',
	array('wikioWorldPublicBehavior','publicEntryAfterContent')
);
$core->addBehavior('publicFooterContent',
	array('wikioWorldPublicBehavior','publicFooterContent')
);

class wikioWorldPublicBehavior
{
	public static function publicHeadContent($core)
	{
		echo wikioWorld::cssEntryVote();
	}
	
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		return self::publicEntryContent($core,$_ctx,'before');
	}
	
	public static function publicEntryAfterContent($core,$_ctx)
	{
		return self::publicEntryContent($core,$_ctx,'after');
	}
	
	protected static function publicEntryContent($core,$_ctx,$place)
	{
		$s = wikioWorldSettings($core);
		
		if (!$s->wikioWorld_active
		 || 'post.html' != $_ctx->current_tpl 
		 || $place != $s->wikioWorld_entryvote_place 
		) { return; }
		
		return wikioWorld::buttonEntryVote($_ctx->posts->getURL(),$s->wikioWorld_entryvote_style);
	}
	
	public static function publicFooterContent($core,$_ctx)
	{
		$s = wikioWorldSettings($core);
		if (!$s->wikioWorld_active) { return; }
		
		$url = wikioWorld::cleanURL($core->blog->url.$core->url->getBase('feed').'/atom');
		
		$res = '';
		if ($s->wikioWorld_addwikio_active)
		{
			$res .= 
			'<a href="http://www.wikio.fr/subscribe?url='.$url.'">'.
			'<img src="http://www.wikio.fr/shared/images/add-rss.gif" '.
			'style="border: none;" alt="http://www.wikio.fr"/></a>';
		}
		
		if ($s->wikioWorld_blogrss_active)
		{
			if ('' == $s->wikioWorld_blogrss_style)
			{
				$res .= 
				'<a target="_blank" href="http://www.wikio.fr/subscribethis?'.
				'url='.$url.'" class="wikio-popup-button">Wikio</a>'.
				'<script type="text/javascript" src="http://www.wikio.fr/wikiothispopupv2?'.
				'services=wikio+netvives+google+yahoo+bloglines+aol+msn+newsgator+pagflakes+live+webwag+rss'.
				'&widgets=&url='.$url.'"></script>';
			}
			else
			{
				$res .= 
				'<a target="_blank" href="http://www.wikio.fr/subscribethis?'.'url='.$url.'">'.
				'<img src="http://www.wikio.fr/shared/images/wikiothis/buttons/wikio_btn_abo-univ_'.
				$s->wikioWorld_blogrss_style.'_'.wikioWorldSettings($core,'system')->lang.
				'.gif" style="border: none;" alt="http://www.wikio.fr"/></a>';
			}
		}
		
		if ($s->wikioWorld_toprank_active && '' != $s->wikioWorld_toprank_cat)
		{
			$cat = wikioWorld::cleanURL($s->wikioWorld_toprank_cat);
			
			$res .= 
			'<a href="http://www.wikio.fr/blogs/top/'.$cat.'">'.
			'<img src="http://external.wikio.fr/blogs/top/getrank?url='.
			wikioWorld::cleanURL($core->blog->url).'&cat='.$cat.
			'" border=0 alt="Wikio - Top des blogs"/></a>';
		}
		
		if (empty($res)) { return; }
		
		echo '<div class="wikioworld-footer">'.$res.'</div>';
	}
}
?>