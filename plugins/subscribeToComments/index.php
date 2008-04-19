<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Subscribe to comments.
# Copyright 2008 Moe (http://gniark.net/)
#
# Subscribe to comments is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Subscribe to comments is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://txfx.net/code/wordpress/subscribe-to-comments/
#
# ***** END LICENSE BLOCK *****

	if (!defined('DC_CONTEXT_ADMIN')) { exit; }

	# format tables' tbody
	function tbody ($array)
	{
		foreach ($array as $k => $v)
		{
			echo('<tr><td><code>'.$k.'</code></td><td>'.$v['name'].'</td></tr>');
		}
	}

	# code for template files
	$post_form =
'		<tpl:SubscribeToCommentsIsActive>
		<p>
   		<input type="checkbox" name="subscribeToComments" id="subscribeToComments"
   		style="width:auto;border:0;margin:0 5px 0 140px;" {{tpl:SubscribeToCommentsFormChecked}} />
			<label for="subscribeToComments">{{tpl:lang Receive following comments by email}}</label>
		</p>
		</tpl:SubscribeToCommentsIsActive>';
	$post_link =
'	<tpl:SubscribeToCommentsIsActive>
	<h3>{{tpl:lang Subscribe to comments}}</h3>
	<p>
		<a href="{{tpl:SubscribeToCommentsFormLink}}">
			<!-- # If the subscriber is logged in -->
			<tpl:SubscribeToCommentsLoggedIf>
				{{tpl:lang Subscribe to receive following comments by email or manage subscriptions}}
			</tpl:SubscribeToCommentsLoggedIf>
			<!-- # If the subscriber is not logged in -->
			<tpl:SubscribeToCommentsLoggedIfNot>
				{{tpl:lang Subscribe to receive following comments by email}}
			</tpl:SubscribeToCommentsLoggedIfNot>
		</a>
	</p>
	</tpl:SubscribeToCommentsIsActive>';

	# path to theme files
   $theme_path = path::fullFromRoot($core->blog->settings->themes_path.'/'.
		$core->blog->settings->theme.'/',DC_ROOT);
	# themes from Dotclear > 7.3
	if (file_exists($theme_path.'/tpl/')) {$theme_path .= '/tpl/';}
   $post_tpl_path = $theme_path.'post.html';
  	$subscribetocomments_tpl_path = $theme_path.'subscribetocomments.html';

	# tags to format emails
	$tags_global = array(
		'[blogname]' => array('name'=>__('Blog name'),'tag'=>'%1$s'),
		'[blogurl]' => array('name'=>__('Blog URL'),'tag'=>'%2$s'),
		'[email]' => array('name'=>__('Email address'),'tag'=>'%3$s'),
		'[manageurl]' => array(
		'name'=> sprintf(__('%s&#39;s page URL'),__('Subscribe to comments')),'tag'=>'%4$s')
	);
	$tags_account = array();
	$tags_subscribe = array(
		'[posttitle]' => array('name'=>__('Post title'),'tag'=>'%5$s'),
		'[posturl]' => array('name'=>__('Post URL'),'tag'=>'%6$s'),
	);
	$tags_comment = array(
		'[posttitle]' => array('name'=>__('Post title'),'tag'=>'%5$s'),
		'[posturl]' => array('name'=>__('Post URL'),'tag'=>'%6$s'),
		'[commenturl]' => array('name'=>__('URL to new comment'),'tag'=>'%7$s'),
		'[commentauthor]' => array('name'=>__('Comment author'),'tag'=>'%8$s'),
		'[commentcontent]' => array('name'=>__('Comment content'),'tag'=>'%9$s'),
	);
	$tags_email = array(
		'[newemail]' => array('name'=>
			__('New email address'),'tag'=>'%5$s'),
		'[emailurl]' => array('name'=>
			__('URL to confirm the change of email address'),'tag'=>'%6$s')
		
	);

	function format($tags,$str,$flip=false)
	{
		global $tags_global;
		$array = array();
		foreach ($tags_global as $k => $v)
		{
			$array[$k] = $v['tag'];
		}
		if (empty($tags)) {$tags = array();}
		foreach ($tags as $k => $v)
		{
			$array[$k] = $v['tag'];
		}
		if ($flip) {$array = array_flip($array);}
		$str = str_replace(array_keys($array),array_values($array),$str);

		return($str);
	}

	$msg = '';

	$default_tab = 'settings';

	$available_tags = array();

	# if there is no settings
	if ($core->blog->settings->subscribetocomments_subscribe_active === null)
	{
		# load locales for the blog language
		$file = dirname(__FILE__).'/locales/'.$core->blog->settings->lang.
			'/default_settings.php';
		if (file_exists($file)) {require_once($file);}

		$settings = new dcSettings($core,$core->blog->id);
	
		$core->blog->settings->setNameSpace('subscribetocomments');
		# Activate Subscribe to comments
		$core->blog->settings->put('subscribetocomments_active',true,
		'boolean','Activate Subscribe to comments');

# Account subject
$core->blog->settings->put('subscribetocomments_account_subject',
format($tags_account,__('Your account on [blogname]')),'text','Email subject');
# Account content
$core->blog->settings->put('subscribetocomments_account_content',
format($tags_account,__('Hello [email],

here are some informations about your account on [blogname] :

Email address : [email]

Manage your subscriptions and account : [manageurl]

--

[blogurl]')),'text','Email content');

# Send an email for each subscription
$core->blog->settings->put('subscribetocomments_subscribe_active',
false,'boolean','Send an email for each subscription');
# Subscription subject
$core->blog->settings->put('subscribetocomments_subscribe_subject',
format($tags_subscribe,__('Subscribed to [posttitle] - [blogname]')),'text','Subscription subject');
# Subscription content
$core->blog->settings->put('subscribetocomments_subscribe_content',
format($tags_subscribe,__('Hello [email],

you subscribed to [posttitle] : [posturl]

----------

Your account :

Email address : [email]

Manage your subscriptions and account : [manageurl]

--

[blogurl]')),'text','Subscription content');

# Comment subject
$core->blog->settings->put('subscribetocomments_comment_subject',
format($tags_comment,__('New comment on [posttitle] - [blogname]')),'text','Comment subject');
# Comment content
$core->blog->settings->put('subscribetocomments_comment_content',
format($tags_comment,__('Hello [email],

a new comment has been posted by [commentauthor] on [posttitle] : 

----------

[commentcontent]

----------

View the comment : [commenturl]

View the post : [posturl]

----------

Your account :

Email address : [email]

Manage your subscriptions and account : [manageurl]

--

[blogurl]')),'text','Comment content');

# Email subject
$core->blog->settings->put('subscribetocomments_email_subject',
format($tags_email,__('Change email address on [blogname]')),'text','Email subject');
# Email content
$core->blog->settings->put('subscribetocomments_email_content',
format($tags_email,__('Hello [email],

you have requested to change the email address of your subscriptions to [newemail], click on this link : [emailurl]

This link is valid for 24 hours.

----------

Your account :

Email address : [email]

Manage your subscriptions and account : [manageurl]

--

[blogurl]')),'text','Email content');

		http::redirect($p_url.'&saveconfig=1&tab=help');
	}

	try
	{
		if (isset($_POST['test']))
		{
			# mail
			$title = sprintf(__('Test email from your blog - %s'),$core->blog->name);
			$content = sprintf(__('% works \o/'),__('Subscribe to comments'));
			subscribeToComments::mail($_POST['test_email'],$title,$content);
			http::redirect($p_url.'&test=1&tab=help');
		}
		elseif (isset($_POST['copy_file']))
		{
			if ((isset($_POST['file'])) && ($_POST['file'] == 'subscribetocomments'))
			{
				$file = dirname(__FILE__).'/tpl/'.'subscribetocomments.html';
				$dest_file = $subscribetocomments_tpl_path;
				if (file_exists($dest_file))
				{throw new Exception(__('Destination file already exists.'));}
				copy($file,$dest_file);
				http::redirect($p_url.'&copyfile=1&tab=subscribetocomments');
			}
			elseif ((isset($_POST['file'])) && ($_POST['file'] == 'post'))
			{
				$file = path::fullFromRoot($core->blog->settings->themes_path.
					'/default/'.'post.html',DC_ROOT);
				$dest_file = $post_tpl_path;
				if (file_exists($dest_file))
				{throw new Exception(__('Destination file already exists.'));}
				copy($file,$dest_file);
				http::redirect($p_url.'&copyfile=1&tab=post');
			}
			else
			{
				throw new Exception(__('No file to copy.'));
			}
		}
		elseif (isset($_POST['save_file']))
		{
			if ((isset($_POST['file'])) && ($_POST['file'] == 'subscribetocomments'))
			{
				files::putContent($subscribetocomments_tpl_path,$_POST['code']);
				http::redirect($p_url.'&filesaved=1&tab=subscribetocomments');
			}
			elseif ((isset($_POST['file'])) && ($_POST['file'] == 'post'))
			{
				files::putContent($post_tpl_path,$_POST['code']);
				http::redirect($p_url.'&filesaved=1&tab=post');
			}
			else
			{
				throw new Exception(__('No file to save.'));
			}
				
		}
		elseif (!empty($_POST['saveconfig']))
		{
			$core->blog->settings->setNameSpace('subscribetocomments');
			# Activate Subscribe to comments
			$core->blog->settings->put('subscribetocomments_active',
				(!empty($_POST['subscribetocomments_active'])),'boolean',
				'Activate Subscribe to comments');
	
			# Account subject
			$core->blog->settings->put('subscribetocomments_account_subject',
				format($available_tags,$_POST['account_subject']),
				'text','Account subject');
			# Account content
			$core->blog->settings->put('subscribetocomments_account_content',
				format($available_tags,$_POST['account_content']),
				'text','Account content');
	
			$available_tags = $tags_subscribe;
			# Send an email for each subscription
			$core->blog->settings->put('subscribetocomments_subscribe_active',
				(!empty($_POST['subscribetocomments_subscribe_active'])),'boolean',
				'Send an email for each subscription');
			# Subscription subject
			$core->blog->settings->put('subscribetocomments_subscribe_subject',
				format($available_tags,$_POST['subscribe_subject']),'text','Subscription subject');
			# Subscription content
			$core->blog->settings->put('subscribetocomments_subscribe_content',
				format($available_tags,$_POST['subscribe_content']),'text','Subscription content');
	
			$available_tags = $tags_comment;
			# Comment subject
			$core->blog->settings->put('subscribetocomments_comment_subject',
				format($available_tags,$_POST['comment_subject']),'text','Comment subject');
			# Comment content
			$core->blog->settings->put('subscribetocomments_comment_content',
				format($available_tags,$_POST['comment_content']),'text','Comment content');
	
			$available_tags = $tags_email;
			# Email subject
			$core->blog->settings->put('subscribetocomments_email_subject',
				format($available_tags,$_POST['email_subject']),'text','Email subject');
			# Email content
			$core->blog->settings->put('subscribetocomments_email_content',
				format($available_tags,$_POST['email_content']),'text','Email content');
	
			http::redirect($p_url.'&saveconfig=1');
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	if (isset($_GET['test']))
	{
		$msg = __('Test email sent.');
	}
	elseif (isset($_GET['copyfile']))
	{
		$msg = __('File copied to theme directory.');
	}
	elseif (isset($_GET['saveconfig']))
	{
		$msg = __('Configuration successfully updated.');
	}
	elseif (isset($_GET['filesaved']))
	{
		$msg = __('File succesfully saved.');
	}

	if (isset($_GET['tab']))
	{
		$default_tab = $_GET['tab'];
	}

?>
<html>
<head>
	<title><?php echo __('Subscribe to comments'); ?></title>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<style type="text/css">
		p.code {border:1px solid #ccc;}
		textarea {width:100%;}
	</style>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &gt '.__('Subscribe to comments'); ?></h2>

	<?php 
		if (!empty($msg)) {echo '<div class="message">'.$msg.'</div><p></p>';}
		if (!$GLOBALS['core']->plugins->moduleExists('metadata')) {
			echo 
			'<div class="error"><strong>'.__('Error:').'</strong><ul><li>'.
			__('Unable to find metadata plugin').'</li></ul></div>';
		}
	?>

	<div class="multi-part" id="settings" title="<?php echo __('settings'); ?>">
		<form method="post" action="<?php echo http::getSelfURI(); ?>">
			<p>
				<?php echo(form::checkbox('subscribetocomments_active',1,
					$core->blog->settings->subscribetocomments_active)); ?>
				<label class="classic" for="subscribetocomments_active">
				<?php printf(__('Activate %s'),__('Subscribe to comments')); ?></label>
			</p>

			<p><?php echo(__('You can format the emails using the following tags.').' '.
			__('Each tag will be replaced by the associated value.')); ?></p>
			<h3><?php echo(__('Tags available in all the contexts')); ?></h3>

			<table class="clear">
				<thead>
					<tr><th><?php echo(__('Tag')); ?></th><th><?php echo(__('Value')); ?></th></tr>
				</thead>
				<tbody>
					<?php tbody($tags_global); ?>
				</tbody>
			</table>

			<fieldset>
				<legend><?php echo(__('Email sent when an account is created or if a subscriber request it')); ?></legend>
				<p class="field">
					<label for="account_subject"><?php echo(__('Subject')); ?></label>
					<?php echo(form::field('account_subject',80,255,
						html::escapeHTML(format($tags_global,
						$core->blog->settings->subscribetocomments_account_subject,true)))); ?>
				</p>
				<p class="field">
					<label for="account_content"><?php echo(__('Content')); ?></label>
					<?php echo(form::textarea('account_content',80,15,
						html::escapeHTML(format($tags_global,
						$core->blog->settings->subscribetocomments_account_content,true)))); ?>
				</p>
			</fieldset>

			<fieldset>
				<legend><?php echo(__('Email sent when a subscriber subscribe to the comments of a post')); ?></legend>
				<p>
					<?php echo(form::checkbox('subscribetocomments_subscribe_active',1,
						$core->blog->settings->subscribetocomments_subscribe_active)); ?>
					<label class="classic" for="subscribetocomments_subscribe_active">
					<?php echo(__('Send an email for each subscription')); ?></label>
				</p>
				<h3><?php echo(__('Available tags')); ?></h3>
				<table class="clear">
					<thead>
						<tr><th><?php echo(__('Tag')); ?></th><th><?php echo(__('Value')); ?></th></tr>
					</thead>
					<tbody>
						<?php tbody($tags_subscribe); ?>
					</tbody>
				</table>
				<p class="field">
					<label for="subscription_subject"><?php echo(__('Subject')); ?></label>
					<?php echo(form::field('subscribe_subject',80,255,
						html::escapeHTML(format($tags_subscribe,
						$core->blog->settings->subscribetocomments_subscribe_subject,true)))); ?>
				</p>
				<p class="field">
					<label for="subscription_content"><?php echo(__('Content')); ?></label>
					<?php echo(form::textarea('subscribe_content',80,15,
						html::escapeHTML(format($tags_subscribe,
						$core->blog->settings->subscribetocomments_subscribe_content,true)))); ?>
				</p>
			</fieldset>

			<fieldset>
				<legend><?php echo(__('Email sent when a new comment is published')); ?></legend>
				<h3><?php echo(__('Available tags')); ?></h3>
				<table class="clear">
					<thead>
						<tr><th><?php echo(__('Tag')); ?></th><th><?php echo(__('Value')); ?></th></tr>
					</thead>
					<tbody>
						<?php tbody($tags_comment); ?>
					</tbody>
				</table>
				<p class="field">
					<label for="comment_subject"><?php echo(__('Subject')); ?></label>
					<?php echo(form::field('comment_subject',80,255,
						html::escapeHTML(format($tags_comment,
						$core->blog->settings->subscribetocomments_comment_subject,true)))); ?>
				</p>
				<p class="field">
					<label for="comment_content"><?php echo(__('Content')); ?></label>
					<?php echo(form::textarea('comment_content',80,15,
						html::escapeHTML(format($tags_comment,
						$core->blog->settings->subscribetocomments_comment_content,true)))); ?>
				</p>
			</fieldset>

			<fieldset>
				<legend><?php echo(__('Email sent when a subscriber want to change his email address')); ?></legend>
				<h3><?php echo(__('Available tags')); ?></h3>
					<table class="clear">
					<thead>
						<tr><th><?php echo(__('Tag')); ?></th><th><?php echo(__('Value')); ?></th></tr>
					</thead>
					<tbody>
						<?php tbody($tags_email); ?>
					</tbody>
				</table>
				<p class="field">
					<label for="email_subject"><?php echo(__('Subject')); ?></label>
					<?php echo(form::field('email_subject',80,255,
						html::escapeHTML(format($tags_email,
						$core->blog->settings->subscribetocomments_email_subject,true)))); ?>
				</p>
				<p class="field">
					<label for="email_content"><?php echo(__('Content')); ?></label>
					<?php echo(form::textarea('email_content',80,15,
						html::escapeHTML(format($tags_email,
						$core->blog->settings->subscribetocomments_email_content,true)))); ?>
				</p>
			</fieldset>

			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
		</form>
	</div>

	<div class="multi-part" id="help" title="<?php echo __('help'); ?>">
		<form method="post" action="<?php echo http::getSelfURI(); ?>">
			<fieldset>
				<legend><?php echo __('Test'); ?></legend>
				<p class="field">
					<label for="test_email"><?php echo(__('Email address')); ?></label>
					<?php echo(form::field('test_email',80,255,
						html::escapeHTML($core->auth->getInfo('user_email')))); ?>
				</p>
				<p><?php printf(
					__('This will send a email, if you don&#39;t receive it, try to <a href="%s">change the way Dotclear send emails</a>.'),
						'http://doc.dotclear.net/2.0/admin/install/config-envoi-mail'); ?></p>
				<p><?php echo $core->formNonce(); ?></p>
				<p><input type="submit" name="test" value="<?php echo __('Try to send an email'); ?>" /></p>
			</fieldset>
		</form>
		<h3><?php echo __('Installation'); ?></h3>
		<p><?php echo __('To use this plugin, edit the file <strong>post.html</strong> by clicking on the tab <strong>post.html</strong>.'); ?></p>
		<p><?php printf(__('%s send emails to the subscribers of a post when :'),__('Subscribe to comments')); ?></p>
		<ul>
			<li><?php echo(__('a comment is posted on a post and published immediatly')); ?></li>
			<li><?php echo(__('publishing a pending comment from the administration backend')); ?></li>
		</ul>
		<hr />
  		<p><?php printf(__('Inspired by <a href="%1$s">%1$s</a>'),
	  		'http://txfx.net/code/wordpress/subscribe-to-comments/'); ?></p>
	</div>

	<div class="multi-part" id="subscribetocomments" title="subscribetocomments.html">
		<h2>subscribetocomments.html</h2>
		<fieldset>
			<form method="post" action="<?php echo http::getSelfURI(); ?>">
				<p><?php echo $core->formNonce(); ?></p>
				<?php 
					if (!file_exists($subscribetocomments_tpl_path))
					{
						echo '<p>'.
							form::hidden(array('file','post'),'subscribetocomments').
							'<input type="submit" name="copy_file" value="'.
							sprintf(__('Copy the file %s into the theme directory'),
							'subscribetocomments.html').'" />'.
							'</p>';
					} else {
					echo '<h3>'.sprintf(__('Edit %s'),'subscribetocomments.html').'</h3>'.
						'<p class="field">'.
						form::textarea(array('code','code_subscribetocomments'),100,30,
						html::escapeHTML(file_get_contents($subscribetocomments_tpl_path))).'</p>'.
						'<p>'.form::hidden(array('file','subscribetocomments'),
						'subscribetocomments').'</p>'.
						'<p><input type="submit" name="save_file" value="'.__('Save file').'" /></p>';
				} ?>
			</form>
		</fieldset>
	</div>

	<div class="multi-part" id="post" title="post.html">
		<h2>post.html</h2>
		<fieldset>
			<form method="post" action="<?php echo http::getSelfURI(); ?>">
				<p><?php echo $core->formNonce(); ?></p>
				<?php 
					if (!file_exists($post_tpl_path))
					{
						echo '<p>'.
							form::hidden(array('file','post'),'post').
							'<input type="submit" name="copy_file" value="'.
							sprintf(__('Copy the file %s into the theme directory'),
							'post.html').'" />'.
							'</p>';
					} else { ?>
				<h3><?php echo __('Checkbox to subscribe to comments when posting a comment'); ?></h3>
				<p><?php echo __('Insert this in the comment form (suggestion : in the <code>&lt;fieldset&gt;</code> before the <code>&lt;/form&gt;</code> tag) :'); ?></p>
				<p class="code"><code><?php 
					echo nl2br(html::escapeHTML($post_form));
				?>
				</code></p>
				<h3><?php printf(__('Link to the %s page'),__('Subscribe to comments')); ?></h3>
				<p><?php echo __('Insert this anywhere on the page (suggestion : just after the <code>&lt;/form&gt;</code> tag) :'); ?></p>
				<p class="code"><code><?php
					echo nl2br(html::escapeHTML($post_link));
				?>
				</code></p>
	  		<?php 
					echo '<h3>'.sprintf(__('Edit %s'),'post.html').'</h3>'.
						'<p class="field">'.
						form::textarea(array('code','code_post'),100,30,
						html::escapeHTML(file_get_contents($post_tpl_path))).'</p>'.
						'<p>'.form::hidden(array('file','post'),'post').'</p>'.
						'<p><input type="submit" name="save_file" value="'.__('Save file').'" /></p>';
				} ?>
			</form>
		</fieldset>
	</div>

</body>
</html>