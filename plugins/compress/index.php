<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of CompreSS.
# Copyright 2008 Moe (http://gniark.net/)
#
# CompreSS is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# CompreSS is distributed in the hope that it will be useful,
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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

	require_once(dirname(__FILE__).'/class.compress.php');
	require_once(dirname(__FILE__).'/class.table.php');

	$default_tab = 'css_list';
	$keep_comments = $core->blog->settings->compress_keep_comments;
	$create_backup_every_time = $core->blog->settings->compress_create_backup_every_time;
	$text_beginning = $core->blog->settings->compress_text_beginning;

	$errors = array();

	if ($core->blog->settings->compress_keep_comments === null)
	{
		try 
		{
			// Default settings
			$core->blog->settings->setNameSpace('compress');
			$core->blog->settings->put('compress_keep_comments',false,'boolean','Keep comments when compressing');
			$core->blog->settings->put('compress_create_backup_every_time',false,'boolean','Create an unique backup of CSS file every time a CSS backup file is compressed');
			$core->blog->settings->put('compress_text_beginning','/* compressed with CompreSS */','text','Text to include at the beginning of the compressed file');
			http::redirect($p_url);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}

	if (!empty($_POST['saveconfig']))
	{
		try
		{
			$core->blog->settings->setNameSpace('compress');
			# keep comments
			$keep_comments = (empty($_POST['compress_keep_comments']))?false:true;
			$core->blog->settings->put('compress_keep_comments',$keep_comments,'boolean','Keep comments when compressing');
			# create backup every time
			$create_backup_every_time = (empty($_POST['compress_create_backup_every_time']))?false:true;
			$core->blog->settings->put('compress_create_backup_every_time',
				$create_backup_every_time,'boolean','Create an unique backup of CSS file every time a CSS backup file is compressed');
			# text beginning
			$text_beginning = $_POST['compress_text_beginning'];
			$core->blog->settings->put('compress_text_beginning',$text_beginning,'text','Text to include at the beginning of the compressed file');
			$core->blog->triggerBlog();
	
			$msg = __('Configuration successfully updated.');
			$default_tab = 'compress_options';
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}

	if (!is_executable(path::real($core->blog->themes_path))) {$errors[] = path::real($core->blog->themes_path).' '.__('is not executable');}

	# actions
	if ((isset($_POST['compress'])) AND (isset($_POST['file'])))
	{
		$file = $_POST['file'];
		$compress = compress::compress_file($file);
		if ($compress === true)
		{
			clearstatcache();
			$percent = (' ('.compress::percent($file).'% '.__('of the original size').')');
			$msg = (__('The file&nbsp;:').' '.$file.' '.__('has been compressed').'<br />'.$percent);
		}
		else {$errors[] = $compress;}
	}
	elseif ((isset($_POST['delete'])) AND (isset($_POST['file'])))
	{
		$file = $_POST['file'];
		$delete = compress::delete($file);
		if ($delete === true)
		{
			$msg = (__('The backup file&nbsp;:').' '.$file.' '.__('has been deleted').$from);
		}
		else {$errors[] = $delete;}
	}
	elseif (isset($_POST['compress_all']))
	{
		$compress = compress::compress_all();
		if ($compress === true) {$msg = (__('All CSS files have been compressed'));}
		else {$errors[] = $compress;}
	}
	elseif (isset($_POST['delete_all_backups']))
	{
		$delete = compress::delete_all_backups();
		if ($delete === true) {$msg = (__('All CSS backup files have been deleted'));}
		else {$errors[] = $delete;}
	}
	elseif (isset($_POST['replace_compressed_files']))
	{
		$replace = compress::replace_compressed_files();
		if ($replace === true) {$msg = (__('All CSS compressed files have been replaced'));}
		else {$errors[] = $replace;}
	}

?>
<html>
<head>
  <title><?php echo __('CompreSS'); ?></title>
  <?php echo dcPage::jsPageTabs($default_tab); ?>
  <link rel="stylesheet" type="text/css" href="index.php?pf=compress/style.css" title="CompreSS" />
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('CompreSS'); ?></h2>

	<?php if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';} ?>
	<?php if (!empty($errors))
	{
		(string)$errors_list = '';
		foreach ($errors as $error)
		{
			$errors_list .= '<li>'.$error.'</li>'."\n";
		}
		echo '<div class="error"><strong>'.__('Errors:').'</strong><ul>'.$errors_list.'</ul></div>';
	}
	?>

	<div class="multi-part" id="css_list" title="<?php echo __('Compress CSSs'); ?>">
		<form action="<?php echo(http::getSelfURI()); ?>" method="post">
			<fieldset>
				<legend><?php echo __('All files'); ?></legend>
				<p>
					<input type="submit" name="compress_all" value="<?php echo __('Compress CSS files'); ?>" />
					<input type="submit" name="delete_all_backups" value="<?php echo __('Delete backups files'); ?>" />
					<input type="submit" name="replace_compressed_files" value="<?php echo __('Replace compressed files with original files'); ?>" />
				</p>
			</fieldset>
			<p><?php echo $core->formNonce(); ?></p>
		</form>
		<?php 
			compress::css_table();
		?>
	</div>

	<div class="multi-part" id="compress_options" title="<?php echo __('Options'); ?>">
		<form method="post" action="<?php echo(http::getSelfURI()); ?>">
			<fieldset>
				<legend><?php echo(__('Options')); ?></legend>
				<?php echo(form::checkbox('compress_keep_comments',1,$keep_comments).
					'&nbsp;<label for="compress_keep_comments">'.__('Keep comments when compressing').'</label>'); ?>
				<br />
				<?php echo(form::checkbox('compress_create_backup_every_time',1,$create_backup_every_time).
					'&nbsp;<label for="compress_create_backup_every_time">'.
					__('Create an unique backup of CSS file every time a CSS backup file is compressed').'</label>'); ?>
				<br />
				<label for="compress_text_beginning"><?php echo(__('Text to include at the beginning of the compressed file:').' ('.__('optional').')'); ?></label>
				<br />
				<?php echo(form::field('compress_text_beginning',80,1024,$text_beginning)); ?>
			</fieldset>
			<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
			<p><?php echo $core->formNonce(); ?></p>
		</form>
	</div>

	<div class="multi-part" id="help" title="<?php echo __('Help'); ?>">
		<p><?php echo(__('A copy of the original file (.css.bak) is created when a CSS file is compressed for the first time.')); ?></p>
		<p>
			<?php echo(__('To modify a CSS file, edit the original file (.css.bak), save it and then compress this file by clicking on')); ?> 
			<input type="submit" name="compress" value="<?php echo(__('compress to')); ?>" />
		</p>
		<p><input type="submit" name="delete" value="<?php echo(__('delete')); ?>" /> 
			<?php echo(__('delete the file and replace the compressed file by the original file if the file is original.')); ?>
		</p>
	</div>

</body>
</html>