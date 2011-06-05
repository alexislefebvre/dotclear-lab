<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of @ Reply, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010,2011 Moe (http://gniark.net/) and buns
#
# @ Reply is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# @ Reply is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# Inspired by @ Reply for WordPress :
# <http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('templateBeforeValue',array('AtReplyTpl','templateBeforeValue'));
$core->addBehavior('templateAfterValue',array('AtReplyTpl','templateAfterValue'));

$core->blog->settings->addNameSpace('atreply');

if ($core->blog->settings->atreply->atreply_active)
{
	$core->addBehavior('publicHeadContent',array('AtReplyTpl','publicHeadContent'));
	$core->addBehavior('publicCommentBeforeContent',array('AtReplyTpl','publicCommentBeforeContent'));
}

class AtReplyTpl
{
	public static function templateBeforeValue($core,$v,$attr)
	{
		if ($v == 'CommentAuthorLink')
		{
			return('<span class="commentAuthor" '.
				'id="atreply_<?php echo $_ctx->comments->comment_id; ?>" '.
				'title="<?php echo(html::escapeHTML($_ctx->comments->comment_author)); ?>">');
		}
	}

	public static function templateAfterValue($core,$v,$attr)
	{
		if ($v == 'CommentAuthorLink')
		{
			return('</span>');
		}
	}
	
	public static function publicHeadContent($core)
	{
		$set = $core->blog->settings->atreply;
		
		$QmarkURL = $core->blog->getQmarkURL();
		
		# personalized image
		if ((strlen($set->atreply_color) > 1)
			&& (file_exists($core->blog->public_path.'/atReply/reply.png')))
		{
			$image_url = $core->blog->settings->system->public_url.
				'/atReply/reply.png';
		}
		else
		{
			# default image
			$image_url = $QmarkURL.'pf=atReply/img/reply.png';
		}
		
		$entry_url = '';
		
		# simple and efficient test on entry, from dcTemplate::SysIf())
		if ($GLOBALS['_ctx']->posts !== null)
		{
			$entry_url = $GLOBALS['_ctx']->posts->getURL();
		}
		
		$title = (($set->atreply_display_title) ? '1' : '0');
		
		# Javascript
		echo(
			'<script type="text/javascript">'."\n".
			'//<![CDATA['."\n".
			'var atReplyEntryURL = \''.
				html::escapeHTML($entry_url).'\';'."\n".
			'var atReplyDisplayTitle = '.$title.';'."\n".
			'var atReplyTitle = \''.
				html::escapeHTML(__('Reply to this comment by {author}')).'\';'."\n".
			'var atReplyImage = \''.$image_url.'\';'."\n".
			'var atReply_switch_text = \''.
				html::escapeHTML(__('Threaded comments')).'\';'."\n".
			'var atReplyLink = \' <a href="#" title="\'+atReplyTitle+\'" class="at_reply_link">\'+'.
			'\'<img src="\'+atReplyImage+\'" alt="\'+atReplyTitle+\'" /> \'+'.
			'\'<span class="at_reply_title" style="display:none;">\'+'.
				'atReplyTitle+\'</span></a>\';'."\n".
			'var atreply_append = '.($set->atreply_append ? '1' : '0').';'."\n".
			'var atreply_show_switch = '.($set->atreply_show_switch ? '1' : '0').';'."\n".
			'//]]>'."\n".
			'</script>'."\n"
		);
		
		if ($set->atreply_append)
		{
			echo ( 
				'<script type="text/javascript" src="'.$QmarkURL.
				'pf=atReply/js/atReplyThread.js'.'"></script>'."\n".
				'<style type="text/css">
				<!--
				#atReplySwitch {
					margin:20px 10px 0 0;
					padding:0;
					float:right;	
					color:#999999;
					font-style:italic;
				}
				.repliedCmt, .replyCmt {
					border-left: 1px solid #666; 
				}
				dd.repliedCmt, dd.replyCmt  {
					border-bottom: 1px solid #666;
				}
				-->
				</style>'."\n"
			);
		}
		
		echo('<script type="text/javascript" src="'.$QmarkURL.
			'pf=atReply/js/atReply.js'.'"></script>'."\n");
	}
	
	public static function publicCommentBeforeContent($core,$ctx)
	{
			echo '<span id="atReplyComment'.$ctx->comments->f('comment_id').
				'" style="display:none;"></span>';
	}
}

?>