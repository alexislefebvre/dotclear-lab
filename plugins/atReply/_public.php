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
# ***** END LICENSE BLOCK *****

	if (!defined('DC_RC_PATH')) {return;}

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

		public static function publicHeadContent(&$core)
		{
			if ($core->url->type != 'post') {return;}

			$suffix = $core->blog->url.
				(($core->blog->settings->url_scan == 'path_info')?'?':'');

			$image_url = $suffix.'pf=atReply/img/reply.png';

			# personalized image
			if (strlen($core->blog->settings->atreply_color) > 1)
			{
				$image_url = $core->blog->settings->public_url.'/atReply/reply.png';
			}

			echo(
				'<script type="text/javascript">'.
				'//<![CDATA['."\n".
				'var atReply = \''.
				' <img src="'.$image_url.'" class="at_reply" style="cursor:pointer;" '.
				'alt="'.__('Reply to this comment').'" '.
				'title="'.__('Reply to this comment').'" />'.
				'\';'."\n".
				'//]]>'.
				'</script>'.
				'<script type="text/javascript" src="'.
				$suffix.
				'pf=atReply/atReply.js'.
				'"></script>'
			);
		}
	}

?>