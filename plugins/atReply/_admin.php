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

	$core->addBehavior('adminBlogPreferencesHeaders',
		array('AtReplyAdmin','adminBlogPreferencesHeaders'));
	$core->addBehavior('adminBeforeBlogSettingsUpdate',
		array('AtReplyAdmin','adminBeforeBlogSettingsUpdate'));
	$core->addBehavior('adminBlogPreferencesForm',
		array('AtReplyAdmin','adminBlogPreferencesForm'));
	$core->addBehavior('adminAfterCommentDesc',
		array('AtReplyAdmin','adminAfterCommentDesc'));

	class AtReplyAdmin
	{
		public static function adminBlogPreferencesHeaders()
		{
			return dcPage::jsColorPicker();
		}

		public static function adminBeforeBlogSettingsUpdate(&$settings)
		{
			global $core;

			$active = $core->blog->settings->atreply_active;

			$settings->setNameSpace('atreply');
			$settings->put('atreply_active',!empty($_POST['atreply_active']),'boolean');
			$settings->put('atreply_color',$_POST['atreply_color'],'string');

			# inspirated from lightbox/admin.php
			$settings->setNameSpace('system');

			# only update the blog if the setting have changed
			if ($active == empty($_POST['atreply_active']))
			{
				$core->blog->triggerBlog();
			}

			# return if there is no color
			if (empty($_POST['atreply_color']))
			{
				return;
			}

			# create the image
			
			# inspirated from blowupConfig/lib/class.blowup.config.php
			$color = sscanf($_POST['atreply_color'],'#%2X%2X%2X');

			$red = $color[0];
			$green = $color[1];
			$blue = $color[2];	

			$dir = path::real(path::fullFromRoot($core->blog->settings->public_path,DC_ROOT).
				'/atReply',false);
			files::makeDir($dir,true);
			$file_path = $dir.'/reply.png';

			# create the image
			$img = imagecreatefrompng(dirname(__FILE__).'/img/transparent_16x16.png');

			$source = imagecreatefrompng(dirname(__FILE__).'/img/reply.png');
			imagealphablending($source,true);

			# copy image pixel per pixel, changing color but not the alpha channel
			for ($x=0;$x <= 15;$x++)
			{
				for ($y=0;$y <= 15;$y++)
				{
					$rgb = imagecolorat($source,$x,$y);
					$rgba = $rgb;
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					# alpha is an undocumented feature, see
					# http://php.net/manual/en/function.imagecolorat.php#79116
					$alpha = ($rgba & 0x7F000000) >> 24;
					if ($r > 0)
					{
						imageline($img,$x,$y,$x,$y,
							imagecolorallocatealpha($img,$red,$green,$blue,$alpha));
					}
				}
			}
			
			imagedestroy($source);

			imagesavealpha($img,true);
			if (is_writable($dir))
			{
				imagepng($img,$file_path);
			}
			else
			{
				throw new Exception(sprintf(__('%s is not writable'),$dir));
			}
			imagedestroy($img);
		}

		public static function adminBlogPreferencesForm(&$core)
		{
			echo '<fieldset>'.
			'<legend>'.__('@ Reply').'</legend>'.
			'<p>'.
			form::checkbox('atreply_active',1,$core->blog->settings->atreply_active).
			'<label class="classic" for="atreply_active">'.
			sprintf(__('Activate %s'),__('@ Reply')).
			'</label>'.
			'</p>'.
			'<p class="form-note">'.
			sprintf(__('%s add arrows to reply to comments'),__('@ Reply')).' '.
			__('Wiki syntax for comments must be activated.').
			'</p>'.
			'<p>'.
			'<label class="classic" for="atreply_color">'.
			__('Create an image with another color').
			'</label> '.
			form::field('atreply_color',7,7,
			$core->blog->settings->atreply_color,'colorpicker').
			'</p>'.
			'<p class="form-note">'.__('Leave empty to cancel this feature.').' '.
			__('The default image will be used.').
			'</p>'.
			( (strlen($core->blog->settings->atreply_color) > 1) &&
				file_exists(path::fullFromRoot($core->blog->settings->public_path,DC_ROOT).
					'/atReply/reply.png')
			 ? '<p>'.__('Preview :').' <img src="'.$core->blog->settings->public_url.
			 	'/atReply/reply.png" alt="reply.png" /></p>'
			 : '').
			'</fieldset>';
		}

		public static function adminAfterCommentDesc($rs)
		{
			# ignore trackbacks
			if ($rs->comment_trackback == 1) {return;}

			global $core;

			return('<p><strong>'.__('@ Reply').'</strong> : '.
				__('Copy this, switch the comment editor to source mode then paste it in the comment :').
				' <code>@&lt;a href="#c'.
				$rs->comment_id.'"&gt;'.$rs->comment_author.'&lt;/a&gt;&nbsp;:&nbsp;</code></p>');
		}
	}

?>