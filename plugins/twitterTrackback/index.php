<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 Olivier Meunier and contributors.
# All rights reserved.
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
# This file is part of TwitterComments
# Hadrien Lanneau http://www.alti.info/
#

// Check twitterPost plugin

$core->blog->settings->setNamespace('twittertrackback');

// API Key
if (!empty($_POST['saveconfig']))
{
	$apikey = $_POST['twittertrackback_apikey'];
	$core->blog->settings->put(
		'twittertrackback_apikey',
		$apikey,
		'string',
		__('API Key')
	);
	
	$prevent = $_POST['twittertrackback_prevent'];
	
	$core->blog->settings->put(
		'twittertrackback_preventmytweets',
		$prevent,
		'boolean',
		__('Prevent my tweets')
	);
	
	$msg = __('Configuration successfully updated');
}
else
{
	$apikey = $core->blog->settings->get(
		'twittertrackback_apikey'
	);
	$prevent = $core->blog->settings->get(
		'twittertrackback_preventmytweets'
	);
}

?>
<html>
<head>
	<title><?php echo __('Twitter Trackback'); ?></title>
</head>

<body>
	<?php
	echo dcPage::jsModal();
	?>
	<h2>
		<?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Twitter Trackback'); ?>
	</h2>
	<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>
	<form	action="plugin.php?p=twitterTrackback"
			method="post"
			accept-charset="utf-8">
		<fieldset>
			<legend><?php echo __('Backtype'); ?></legend>
			<p>
				<?php echo __('Backtype is the webservice used to get related tweets. You have to register an API key to start using it.'); ?>
			</p>
			<p class="label">
				<label class="classic">
					<?php echo __('Your API Key');
					echo form::field(
						'twittertrackback_apikey',
						50,
						null,
						$apikey
					);
					?>
				</label>
			</p>
			<p>
				<a href="http://www.backtype.com/developers" class="modal">
					<?php echo __('I don\'t have any API Key'); ?>
				</a>
			</p>
		</fieldset>
		<?php
		// If twitterPost is installed
		if ($core->plugins->moduleExists(
				'twitterPost'
			))
		{
			?>
		<fieldset>
			<legend><?php echo __('TwitterPost'); ?></legend>
			<p class="label">
				<label class="classic">
					<?php
					echo form::checkbox(
						array(
							'twittertrackback_prevent'
						),
						'twittertrackback_prevent',
						$prevent
					);
					?>
					<?php echo __('Prevent my tweets'); ?>
				</label>
			</p>
		</fieldset>
			<?
		}
		?>
		
		<p>
			<input type="hidden" name="p" value="twitterTrackback" />
			<?php echo $core->formNonce(); ?>
			<input type="submit" name="saveconfig" value="<?php echo __('save'); ?>" />
		</p>
		
		<fieldset>
			<legend><?php echo __('Templates'); ?></legend>
			<p>
				<?php echo __('You can use the following templates values and block :'); ?>
			</p>
			<ul>
				<li>
					<em>&lt;tpl:CommentIsTweet&gt;</em> :
					<?php echo __('Block to put inside a Comments block. Useful for exemple to add a specific classname to tweets.'); ?>
					<br />
					<?php echo __('Example : '); ?>
					<pre>&lt;dd class="{{tpl:CommentIfMe}} {{tpl:CommentIfOdd}} {{tpl:CommentIfFirst}} &lt;tpl:CommentIsTweet&gt; tweet &lt;/tpl:CommentIsTweet&gt;"&gt;</pre>
				</li>
				<li>
					<em>{{tpl:TwitterAvatar}}</em> :
					<?php echo __('Value to put inside a Comment block. Display the tweetter\'s avatar.'); ?>
					<br />
					<?php echo __('Example : '); ?>
					<pre>{{tpl:TwitterAvatar classname="gravatar_img" size=48}}</pre>
				</li>
			</ul>
		</fieldset>
	</form>
	
	<script type="text/javascript" charset="utf-8">
	//<![CDATA[
		$(function()
		{
			if ($('a.modal').modalWeb)
			{
				$('a.modal').each(
					function()
					{
						if (this.className.indexOf(
								'newwindow'
							) != -1)
						{
							this.onclick = function()
							{
								window.open(this);
								return false;
							}
						}
						else
						{
							$(this).modalWeb(
								$(window).width()-60,
								$(window).height()-60
							);
						}
					}
				);
			}
			else
			{
				$('a.modal').each(
					function()
					{
						this.onclick = function()
						{
							window.open(this);
							return false;
						}
					}
				);
			}
		});

	//]]>
	</script>
</body>