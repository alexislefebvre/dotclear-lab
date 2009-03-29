<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Private mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2009 Osku and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

// Setting default parameters if missing configuration
if (is_null($core->blog->settings->private_flag)) {
	try {
			$core->blog->settings->setNameSpace('private');

			// Private mode is not active by default
			$core->blog->settings->put('private_flag',false,'boolean');
			$core->blog->settings->put('private_conauto',false,'boolean');
			$core->blog->triggerBlog();
			http::redirect(http::getSelfURI());
		}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// Getting current parameters
$private_flag		= (boolean)$core->blog->settings->private_flag;
$private_conauto		= (boolean)$core->blog->settings->private_conauto;
$blog_private_title	= $core->blog->settings->blog_private_title;
$blog_private_msg	= $core->blog->settings->blog_private_msg;

if ($blog_private_title === null) {
	$blog_private_title = __('Private blog');
}

if ($blog_private_msg === null) {
	$blog_private_msg = __('<p class="message">You need the password to view this blog.</p>');
}

if (is_null($core->blog->settings->blog_private_pwd))
{
	$err = __('No password set.');
}

if (!empty($_POST['saveconfig']))
{
	try
	{
		$private_flag = (empty($_POST['private_flag']))?false:true;
		$private_conauto = (empty($_POST['private_conauto']))?false:true;
		$blog_private_title = $_POST['blog_private_title'];
		$blog_private_msg = $_POST['blog_private_msg'];
		$blog_private_pwd = md5($_POST['blog_private_pwd']);

		if (empty($_POST['blog_private_title'])) {
			throw new Exception(__('No page title.'));
		}

		if (empty($_POST['blog_private_msg'])) {
			throw new Exception(__('No private message.'));
		}

		$core->blog->settings->setNamespace('private');
 		$core->blog->settings->put('private_flag',$private_flag,'boolean','Protect your blog with a password');
 		$core->blog->settings->put('private_conauto',$private_conauto,'boolean','Allow automatic connection');
		$core->blog->settings->put('blog_private_title',$blog_private_title,'string','Private page title');
		$core->blog->settings->put('blog_private_msg',$blog_private_msg,'string','Private message');

		if (!empty($_POST['blog_private_pwd'])) {
			if ($_POST['blog_private_pwd'] != $_POST['blog_private_pwd_c']) {
				throw new Exception(__("Passwords don't match"));
			}
			$core->blog->settings->put('blog_private_pwd',$blog_private_pwd,'string','Private blog password');
		}

		$core->blog->triggerBlog();

		$msg = __('Configuration successfully updated.');
	}

	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Private mode'); ?></title>
</head>
<body>
<h2 style="padding:8px 0 8px 34px;background:url(index.php?pf=private/icon_32.png) no-repeat;">
<?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; <?php echo __('Private mode'); ?></h2>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<?php if (!empty($err)) echo '<p class="error">'.$err.'</p>'; ?>

<div id="private_options">
	<form method="post" action="plugin.php">
		<fieldset>
			<legend><?php echo __('Plugin activation'); ?></legend>
				<div class="two-cols">
				<div class="col">
				<p class="field">
					<?php echo form::checkbox('private_flag', 1, $private_flag); ?>
					<label class=" classic" for="private_flag"> <?php echo __('Enable Private mode');?></label>
				</p>
				<p><label class="required" title="<?php echo __('Required field');?>">
					<?php echo __('New password:'); ?>
					<?php echo form::password('blog_private_pwd',30,255); ?>
				</label></p>
				<p><label class="required" title="<?php echo __('Required field');?>">
					<?php echo __('Confirm password:'); ?>
					<?php echo form::password('blog_private_pwd_c',30,255); ?>
				</label></p>
				</div>
				<div class="col">
				<p>
					<?php echo form::checkbox('private_conauto', 1, $private_conauto); ?>
					<label class=" classic" for="private_conauto"> <?php echo __('Propose automatic connection to visitors');?></label>
				</p>
				<p class="form-note">
				<?php echo __('With this option, the password could be stored in a cookie.'); ?>
				<?php echo __('But it still remains a choice for the visitor.'); ?>
				</p>
				<p><?php  echo sprintf(__('Don\'t forget to add a <a href="%s">widget</a> allowing disconnection from the blog.'),'plugin.php?p=widgets'); ?></p>
				</div>
				</div>
		</fieldset>
		<fieldset class="constrained">
			<legend><?php echo __('Presentation options'); ?></legend>
				<p class="col"><label class="required" title="<?php echo __('Required field');?>">
					<?php echo __('Private page title:');?>
					<?php echo form::field('blog_private_title',20,255,html::escapeHTML($blog_private_title),'maximal'); ?>
				</label></p>
				<p class="area"><label class="required" title="<?php echo __('Required field');?>">
					<?php echo __('Private message:');?>
					<?php echo form::textarea('blog_private_msg',30,4,html::escapeHTML($blog_private_msg)); ?>
				</label></p>
		</fieldset>

		<p><input type="hidden" name="p" value="private" />
		<?php echo $core->formNonce(); ?>
		<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
</div>
</body>
</html>
