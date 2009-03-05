<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget SnapMe for DotClear.
# Copyright (c) 2007 Ludovic Toinel, All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$settings =& $core->blog->settings;
		
if (!empty($_POST['saveconfig'])) {
	try
	{
		$active = (empty($_POST['active']))?false:true;
		
		$settings->setNameSpace('googlespy');
		$settings->put('num_posts',$_POST['num_posts'],'integer');
		$settings->put('num_keywords',$_POST['num_keywords'],'integer');
		$settings->put('title',$_POST['title'],'string');
		$settings->put('description',$_POST['description'],'string');
		$settings->put('ignored_words',$_POST['ignored_words'],'string');
		$settings->put('active',$active,'boolean');
		
		if ($active == empty($_POST['active']))
		{
			$core->blog->triggerBlog();
			
			# delete the cache directory
			$core->emptyTemplatesCache();
		}
		
		http::redirect($p_url.'&saveconfig=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
} 

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
}
?>

<html>
<head>
  <title><?php echo __('GoogleSpy'); ?></title>
  <?php echo dcPage::jsPageTabs(); ?>
</head>
  
<body>
<h2><?php echo __('GoogleSpy'); ?></h2>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<div class="multi-part" title="<?php echo __('Configuration'); ?>">
	<form method="post" action="plugin.php">
	<fieldset>
		<legend><?php echo __('Plugin activation'); ?></legend>
		<p class="field">
		<label class=" classic"><?php echo form::checkbox('active', 1, $settings->active); ?>&nbsp;
		<?php echo __('Plugin enabled');?>
		</label>
		</p>
	</fieldset>
	<fieldset>
		<legend><?php echo __('Advanced options'); ?></legend>

		<p><label class="classic"><?php echo __('Number of purposed posts'); ?> : 
		<?php echo form::field('num_posts', 2, 2, $settings->num_posts); ?>
		</label></p>
		
		<p><label class="classic"><?php echo __('Number of analysed keywords'); ?> : 
		<?php echo form::field('num_keywords', 2, 2, $settings->num_keywords); ?>
		</label></p>
		
		<p><label class="classic"><?php echo __('Title'); ?> : 
		<?php echo form::field('title', 30, 64, $settings->title); ?>
		</label></p>
		
		<p><label for="description" class="classic"><?php echo __('Description'); ?> :</label> 
		<br/>
		<?php echo form::textarea('description',60, 3, $settings->description); ?></p>
		
		<p><label for="ignored_words" class="classic"><?php echo __('Ignored words'); ?> :</label>
		<br/> 
		<?php echo form::textarea('ignored_words', 60, 6, $settings->ignored_words); ?></p>
		
		
	</fieldset>
	<p>
		<input type="hidden" name="p" value="googleSpy" />
		<?php echo $core->formNonce(); ?>
		<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</p>
	</form>

</div>


<div class="multi-part" title="<?php echo __('About'); ?>">

<p><?php echo __('Plugin GoogleSpy v0.8 by Ludovic Toinel'); ?></p>

<ul>
	<li><a href="http://www.geeek.org/post/2007/09/02/Plugin-Dotclear2-%3A-GoogleSpy-v01"><?php echo __('Support'); ?></a></li>
	<li><a href="http://lab.dotclear.org/wiki/plugin/googleSpy"><?php echo __('Lab Dotclear'); ?></a></li>
</ul>

</div>

</body>
</html>