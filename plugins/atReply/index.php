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

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$settings =& $core->blog->settings;

try
{
	if (!empty($_POST['saveconfig']))
	{
		$active = $settings->atreply_active;
		
		$color = trim($_POST['atreply_color']);
		
		$settings->setNameSpace('atreply');
		$settings->put('atreply_active',!empty($_POST['atreply_active']),
			'boolean','Enable @ Reply');
		$settings->put('atreply_display_title',!empty($_POST['atreply_display_title']),
			'boolean','Display a text when the cursor is hovering the arrow');
		$settings->put('atreply_color',$color,
			'string','@ Reply arrow\'s color');

		# inspirated from lightbox/admin.php
		$settings->setNameSpace('system');
		
		# from commentsWikibar/index.php
		$settings->put('wiki_comments',true,'boolean');
		
		# if there is a color
		if (!empty($color))
		{
			# create the image
			
			# inspirated from blowupConfig/lib/class.blowup.config.php
			$color = sscanf($color,'#%2X%2X%2X');
	
			$red = $color[0];
			$green = $color[1];
			$blue = $color[2];	
	
			$dir = path::real(path::fullFromRoot($settings->public_path,DC_ROOT).
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
		
		# only update the blog if the setting have changed
		if ($active == empty($_POST['atreply_active']))
		{
			$core->blog->triggerBlog();
			
			# delete the cache directory
			$core->emptyTemplatesCache();
		}
		
		http::redirect($p_url.'&saveconfig=1');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
}

$image_url = $core->blog->getQmarkURL().'pf=atReply/img/reply.png';

# personalized image
if (strlen($core->blog->settings->atreply_color) > 1)
{
	$personalized_image = $core->blog->settings->public_url.
		'/atReply/reply.png';
	if (file_exists(path::fullFromRoot($settings->public_path,
		DC_ROOT).'/atReply/reply.png'))
	{
		$image_url = $personalized_image;
		
		if (substr($settings->public_url,0,1) == '/')
		{
			# public_path is located at the root of the website
			$parsed_url = @parse_url($core->blog->url);
			
			$image_url = $parsed_url['scheme'].'://'.$parsed_url['host'].
				$personalized_image;
			
			unset($parsed_url);
		} else {
			$image_url = $core->blog->url.$personalized_image;
		}
	}
}

?><html>
<head>
	<title><?php echo(('@ Reply')); ?></title>
	<?php echo(dcPage::jsColorPicker()); ?>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.('@ Reply'); ?></h2>
	
	<?php 
		if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}
	?>
	
	<form method="post" action="<?php echo $p_url; ?>">
		<fieldset>
			<legend><?php echo(__('@ Reply')); ?></legend>
			
			<p><?php echo(form::checkbox('atreply_active',1,
				$settings->atreply_active)); ?>
				<label class="classic" for="atreply_active">
					<?php echo(__('Add arrows to easily reply to comments on the blog')); ?>
				</label>
			</p>
			<p class="form-note">
				<?php
					# from commentsWikibar/index.php
					echo(' '.__('Activating this plugin also enforces wiki syntax in blog comments.')); ?>
			</p>

			<p><?php echo(form::checkbox('atreply_display_title',1,
				$settings->atreply_display_title)); ?>
				<label class="classic" for="atreply_display_title">
					<?php echo(__('Display a text when the cursor is hovering the arrow')); ?>
				</label>
			</p>
			
			<p>
				<label class="classic" for="atreply_color">
					<?php echo(__('Create an image with another color')); ?>
				</label>
				<?php echo(form::field('atreply_color',7,7,
					$settings->atreply_color,'colorpicker')); ?>
			</p>
			<p class="form-note">
				<?php echo(__('Leave empty to cancel this feature.').' '.
					__('The default image will be used.')); ?>
			</p>
			
			<?php echo('<p>'.__('Preview :').' <img src="'.$image_url.
				'" alt="reply.png" /></p>'); ?>	 
		</fieldset>
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>

</body>
</html>