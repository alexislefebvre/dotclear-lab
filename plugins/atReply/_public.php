<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of @ Reply.
# Copyright 2008 Moe (http://gniark.net/)
#
# @ Reply is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# @ Reply is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/
#
# ***** END LICENSE BLOCK *****

	if (!defined('DC_RC_PATH')) {return;}

	# always add tags, useful if a blog generate a cache
	# and the plugin is disabled
	$core->addBehavior('templateBeforeValue',array('AtReplyTpl','templateBeforeValue'));
	$core->addBehavior('templateAfterValue',array('AtReplyTpl','templateAfterValue'));
		
	if ($core->blog->settings->atreply_active)
	{
		$core->addBehavior('publicHeadContent',array('AtReplyTpl','publicHeadContent'));
	}
	
	class AtReplyTpl
	{
		public static function templateBeforeValue(&$core,$v,$attr)
		{
			if ($v == 'CommentAuthorLink')
			{
				return('<span class="commentAuthor">');
			}
		}

		public static function templateAfterValue(&$core,$v,$attr)
		{
			if ($v == 'CommentAuthorLink')
			{
				return('</span>');
			}
		}

		public static function publicHeadContent(&$core,$_ctx)
		{
			$settings = $core->blog->settings;
			
			// die('<pre>'.print_r($settings,true).'</pre>');
			// fixme : où est public_url ?
			// seulement dispo avec DC 2.1.3 ? WTF ??
			// die('<pre>'.print_r($settings->public_url,true).'</pre>');
			
			
			# personalized image
			if (strlen($settings->atreply_image_filename) > 1)
			{
				$image_url = $settings->public_url.'/atReply/'.
					$settings->atreply_image_filename.'.png';
			}
			elseif (strlen($settings->atreply_color) > 1)
			{
				$image_url = $settings->public_url.'/atReply/reply.png';
			}
			# default image
			else
			{
				$image_url = $core->blog->getQmarkURL().'pf=atReply/img/reply.png';
			}

			echo(
				'<script type="text/javascript">'.
				'//<![CDATA['."\n".
				'var atReply = \''.
				' <a href="#" class="at_reply" title="'.__('Reply to this comment').'">'.
					'<img src="'.$image_url.'" '.
						'alt="'.__('Reply to this comment').'" /> '.
					'<span class="at_reply_title" style="display:none;">'.
						__('Reply to this comment').'</span>'.
				'</a>'.'\';'."\n".
				'//]]>'.
				'</script>'.
				'<script type="text/javascript" src="'.
					$core->blog->getQmarkURL().'pf=atReply/atReply.js'.
				'"></script>'
			);
		}
	}

?>