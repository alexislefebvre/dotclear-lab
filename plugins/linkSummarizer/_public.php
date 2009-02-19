<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Link Summarizer.
# Copyright 2009 Moe (http://gniark.net/)
#
# Link Summarizer is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Link Summarizer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://wordpress.org/extend/plugins/link-summarizer/
#
# http://mac.partofus.org/macpress/2007/08/02/wordpress-plugin-link-summarizer/
# http://mac.partofus.org/macpress/forum/english-support-forum/dotclear-plugin/page-1/post-6/#p6
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

if ($core->blog->settings->linksummarizer_active)
{
	$core->addBehavior('publicEntryAfterContent',array('linkSummarizerBehaviors','publicEntryAfterContent'));
}

/**
@ingroup Link Summarizer
@brief Behaviors
*/
class linkSummarizerBehaviors
{
	/**
	publicEntryAfterContent behavior
	@return	<b>string</b> PHP block
	*/
	public static function publicEntryAfterContent()
	{		
		# Display Link Summarizer only in post context
		if (($GLOBALS['core']->blog->settings->linksummarizer_only_post)
			&& ($GLOBALS['core']->url->type != 'post')) {return;}
		
		$post = $GLOBALS['_ctx']->posts->post_excerpt_xhtml.
			$GLOBALS['_ctx']->posts->post_content_xhtml;
		
		$links = array();
		
		# get the name of the links
		if (preg_match_all('/<a(\W)(.*?)>(.*?)<\/a>/msu',$post,$ms))
		{
			$size = count($ms[0])-1;
			
			if ($size > 0)
			{
				for ($i = 0;$i <= $size;$i++)
				{
					$a = $ms[2][$i];
					
					preg_match('/href="(.*?)"/msu',$a,$href);
					$href = $href[1];
					
					if (strpos($href,'#') === 0)
					{
						continue;
					}
					
					$content = html::clean($ms[3][$i]);
					if (empty($content))
					{
						$content = text::cutString($href,80);
					}
					
					$links[] = '<a '.$a.'>'.$content.'</a>';
				}
			}
			
			if (!empty($links))
			{
				echo('<div id="link-summarizer">'.
					'<h3>'.__('Links').'</h3>'.
					'<ul class="posts-link"><li>'.
					implode('</li><li>',$links).
					'</li></ul>'.
					'</div>');
			}
		}
	}
}
?>