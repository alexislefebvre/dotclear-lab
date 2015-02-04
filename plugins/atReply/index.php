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
# Big icon (icon-big.png) come from Dropline Neu! :
# <http://art.gnome.org/themes/icon?sort=popularity>
#
# Inspired by @ Reply for WordPress :
# <http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

if (!$core->auth->check('admin',$core->blog->id))
{
	echo('<html><head><title>Error</title></head>'.
		'<body><p class="error">'.
		__('Invalid permission.').
		'</p></body></html>');
	return;
}

$page_title = __('@ Reply');

$core->blog->settings->addNameSpace('atreply');

$settings =& $core->blog->settings->atreply;

try
{
	if (!empty($_POST['saveconfig']))
	{
		$active = $settings->atreply_active;
		
		$color = trim($_POST['atreply_color']);
		
		$settings->put('atreply_active',!empty($_POST['atreply_active']),
			'boolean','Enable @ Reply');
		if (!empty($_POST['atreply_active']))
		{
			# from commentsWikibar/index.php
			$core->blog->settings->system->put('wiki_comments',true,'boolean');
		}
		
		$settings->put('atreply_display_title',!empty($_POST['atreply_display_title']),
			'boolean','Display a text when the cursor is hovering the arrow');
		$settings->put('atreply_color',$color,
			'string','@ Reply arrow\'s color');
		$settings->put('atreply_append',!empty($_POST['atreply_append']),
			'boolean','Append replies to appropriate comments');
		$settings->put('atreply_show_switch',!empty($_POST['atreply_show_switch']),
			'boolean','Display a switch to toggle threading');

		$settings->put('atreply_subscribe_replied_comment',
			!empty($_POST['atreply_subscribe_replied_comment']),
			'boolean','Subscribe replied comments to "Subscribe to comments"');
		
		# if there is a color
		if (!empty($color))
		{
			# create the image
			
			# inspired by blowupConfig/lib/class.blowup.config.php
			$color = sscanf($color,'#%2X%2X%2X');
	
			$red = $color[0];
			$green = $color[1];
			$blue = $color[2];	
	
			$dir = path::real($core->blog->public_path.'/atReply',false);
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

$system = $core->blog->settings->system;

# personalized image
if (strlen($settings->atreply_color) > 1)
{
	$personalized_image = $system->public_url.
		'/atReply/reply.png'.'?time='.time();
	
	if (file_exists(path::fullFromRoot($system->public_path,
		DC_ROOT).'/atReply/reply.png'))
	{
		$image_url = $personalized_image;
		
		if (substr($system->public_url,0,1) == '/')
		{
			# public_path is located at the root of the website
			$image_url = $core->blog->host.'/'.$personalized_image;
		}
		else if (substr($system->public_url,0,4) == 'http')
		{
			$image_url = $personalized_image;
		}
		else
		{
			$image_url = $core->blog->url.$personalized_image;
		}
	}
}

?><html>
<head>
	<title><?php echo($page_title); ?></title>
	<?php echo(dcPage::jsColorPicker()); ?>
</head>
<body>
	
	<?php
	if (is_callable(array('dcPage', 'breadcrumb')))
	{
		echo dcPage::breadcrumb(
			array(
				html::escapeHTML($core->blog->name) => '',
				'<span class="page-title">'.$page_title.'</span>' => ''
			));
	}
	else
	{
		echo('<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
			$page_title.'</h2>');
	}
	?>
	
	<?php 
		if (!empty($msg))
		{
			if (is_callable(array('dcPage', 'success')))
			{
				dcPage::success($msg);
			}
			else
			{
				dcPage::message($msg);
			}
		}
	?>
	
	<form method="post" action="<?php echo $p_url; ?>">
		<div class="fieldset"><h4><?php echo(__('Activation')); ?></h4>
    <p><?php echo(form::checkbox('atreply_active',1,
			$settings->atreply_active)); ?>
			<label class="classic" for="atreply_active">
				<?php echo(__('Add arrows to easily reply to comments on the blog')); ?>
			</label>
		</p>
		<p class="info">
			<?php
				# from commentsWikibar/index.php
				echo(' '.__('Activating this plugin also enforces wiki syntax in blog comments.')); ?>
		</p>
		</div>
		
		<div class="fieldset"><h4><?php echo(__('Settings')); ?></h4>
		<p><?php echo(form::checkbox('atreply_display_title',1,
			$settings->atreply_display_title)); ?>
			<label class="classic" for="atreply_display_title">
				<?php echo(__('Display a text when the cursor is hovering the arrow')); ?>
			</label>
		</p>
		
		<p><?php echo(form::checkbox('atreply_append',1,
			$settings->atreply_append)); ?>
			<label class="classic" for="atreply_append">
				<?php echo(__('Append replies to appropriate comments')); ?>
			</label>
		</p>
		
		<p><?php echo(form::checkbox('atreply_show_switch',1,
			$settings->atreply_show_switch)); ?>
			<label class="classic" for="atreply_show_switch">
				<?php echo(__('Display a switch to toggle threading')); ?>
			</label>
		</p>
		<p class="info">
			<?php printf(__('Requires "%s".'),
				__('Append replies to appropriate comments')); ?>
		</p>

		<p><?php echo(form::checkbox('atreply_subscribe_replied_comment',1,
			$settings->atreply_subscribe_replied_comment)); ?>
			<label class="classic" for="atreply_subscribe_replied_comment">
				<?php printf(__('When clicking on the "%s" button in a comment list of the administration, subscribe to comments the email address of the replied comment with the %s plugin'),
					__('Reply to this comment'),__('Subscribe to comments')); ?>
			</label>
		</p>
		<p class="info">
			<?php printf(__('Requires the %s plugin.'),
				__('Subscribe to comments')); ?>
		</p>
		
		<p>
			<label class="classic" for="atreply_color">
				<?php echo(__('Create an image with another color')); ?>
			</label>
			<?php echo(form::field('atreply_color',7,7,
				$settings->atreply_color,'colorpicker')); ?>
		</p>
		<p class="info">
			<?php echo(__('Leave blank to disable this feature.').' '.
				__('The default image will be used.')); ?>
		</p>
		
		<?php echo('<p>'.__('Preview:').' <img src="'.$image_url.
			'" alt="reply.png" /></p>'); ?>
		
		<p class="info"><?php echo(__('Visitors may see the old image if their browser still use it.')); ?></p>
		</div>
		
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig" value="<?php echo __('Save'); ?>" /></p>
	</form>

</body>
</html>