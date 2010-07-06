<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of TwitterPost.
# Copyright (c) 2009 Hadrien Lanneau.
# All rights reserved.
#
# Pixearch is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# Pixearch is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Pixearch; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# http://www.alti.info/pages/TwitterPost
#
# ***** END LICENSE BLOCK *****
$core->blog->settings->setNamespace('twitterpost');

// Twitter
if (!empty($_POST['twitterpost_username']))
{
	$username  = $_POST['twitterpost_username'];
	$password = $_POST['twitterpost_password'];

	$core->blog->settings->put(
		'twitterpost_username',
		$username,
		'string',
		__('Twitter login'),
		true,
		false
	);
	$core->blog->settings->put(
		'twitterpost_password',
		$password,
		'string',
		__('Twitter password'),
		true,
		false
	);
	$core->blog->settings->put(
		'twitterpost_status',
		$status,
		'string',
		__('Twitter status'),
		true,
		false
	);

	$msg = __('Configuration successfully updated');
}
else
{

	$username = $core->blog->settings->twitterpost->twitterpost_username;
	$password = $core->blog->settings->twitterpost->twitterpost_password;
	$status = $core->blog->settings->twitterpost->twitterpost_status;
}

// Identica
if (!empty($_POST['twitterpost_username_identica']))
{
	$username_identica  = $_POST['twitterpost_username_identica'];
	$password_identica = $_POST['twitterpost_password_identica'];

	$core->blog->settings->put(
		'twitterpost_username_identica',
		$username_identica,
		'string',
		__('Identi.ca login'),
		true,
		false
	);
	$core->blog->settings->put(
		'twitterpost_password_identica',
		$password_identica,
		'string',
		__('Identi.ca password'),
		true,
		false
	);

	$msg = __('Configuration successfully updated');
}
else
{
	$username_identica = $core->blog->settings->twitterpost->twitterpost_username_identica;
	$password_identica = $core->blog->settings->twitterpost_password_identica;
}

// Tr.im
if (!empty($_POST['twitterpost_username_trim']))
{
	$username_trim  = $_POST['twitterpost_username_trim'];
	$password_trim = $_POST['twitterpost_password_trim'];

	$core->blog->settings->put(
		'twitterpost_username_trim',
		$username_trim,
		'string',
		__('Tr.im login'),
		true,
		false
	);
	$core->blog->settings->put(
		'twitterpost_password_trim',
		$password_trim,
		'string',
		__('Tr.im password'),
		true,
		false
	);

	$msg = __('Configuration successfully updated');
}
else
{
	$username_trim = $core->blog->settings->twitterpost_username_trim;
	$password_trim = $core->blog->settings->twitterpost_password_trim;
}

// Status
if (isset($_POST['twitterpost_status']))
{
	$status = $_POST['twitterpost_status'];

	$core->blog->settings->put(
		'twitterpost_status',
		$status,
		'string',
		__('Twitter status'),
		true,
		false
	);

	$msg = __('Configuration successfully updated');
}
else
{
	$status = $core->blog->settings->twitterpost_status;
}

?>
<html>
<head>
	<title><?php echo __('Twitter Post'); ?></title>
</head>

<body>
	<h2>
		<?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Twitter Post'); ?>
	</h2>
	<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>
	<div id="overblog_options">
		<form action="plugin.php?p=twitterPost" method="post" accept-charset="utf-8">

			<fieldset>
				<legend><?php echo __('Twit'); ?></legend>
				<p class="label">
					<label class="classic">
						<?php echo __('Your twit :'); ?>
						<?php echo form::field(
							'twitterpost_status',
							50,
							null,
							html::escapeHTML($status)
						); ?>&nbsp;
					</label>
				</p>
				<p>
					<?php echo __('Help'); ?>
				</p>
			</fieldset>

			<fieldset>
				<legend><?php echo __('Twitter authentification'); ?></legend>
				<p class="label">
					<label class="classic">
						<?php echo __('Login :'); ?>
						<?php echo form::field(
							'twitterpost_username',
							50,
							null,
							$username
						); ?>&nbsp;
					</label>
				</p>
				<p class="label">
					<label class="classic">
						<?php echo __('Password :'); ?>
						<?php echo form::password(
							'twitterpost_password',
							50,
							null,
							$password
						); ?>&nbsp;
					</label>
				</p>
			</fieldset>

			<fieldset>
				<legend><?php echo __('Identi.ca authentification'); ?></legend>
				<p class="label">
					<label class="classic">
						<?php echo __('Login :'); ?>
						<?php echo form::field(
							'twitterpost_username_identica',
							50,
							null,
							$username_identica
						); ?>&nbsp;
					</label>
				</p>
				<p class="label">
					<label class="classic">
						<?php echo __('Password :'); ?>
						<?php echo form::password(
							'twitterpost_password_identica',
							50,
							null,
							$password_identica
						); ?>&nbsp;
					</label>
				</p>
			</fieldset>

			<input type="hidden" name="p" value="twitterPost" />
			<?php echo $core->formNonce(); ?>
			<input type="submit" name="saveconfig" value="<?php echo __('save'); ?>" />
		</form>
	</div>
</body>