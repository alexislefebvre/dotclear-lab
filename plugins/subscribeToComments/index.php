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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# format tables' tbody
function tbody ($array)
{
	foreach ($array as $k => $v)
	{
		echo('<tr><td><code>'.$k.'</code></td><td>'.$v['name'].'</td></tr>');
	}
}

# code for template files
$post_form = '<tpl:SubscribeToCommentsIsActive>
<p>
	<input type="checkbox" name="subscribeToComments" id="subscribeToComments"
		{{tpl:SubscribeToCommentsFormChecked}} />
	<label for="subscribeToComments">{{tpl:lang Receive following comments by email}}</label>
	<tpl:SubscribeToCommentsLoggedIf>
		(<strong><a href="{{tpl:SubscribeToCommentsFormLink}}">{{tpl:lang Logged in}}</a></strong>)
	</tpl:SubscribeToCommentsLoggedIf>
</p>
</tpl:SubscribeToCommentsIsActive>';

$post_css = '#comment-form #subscribeToComments {
width:auto;
border:0;
margin:0 5px 0 140px;
}';

$post_link = '<tpl:SubscribeToCommentsIsActive>
<div id="subscribetocomments_block">
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
</div>
</tpl:SubscribeToCommentsIsActive>';

# tags to format emails
$tags_global = array(
	'[blogname]' => array('name'=>__('Blog name'),'tag'=>'%1$s'),
	'[blogurl]' => array('name'=>__('Blog URL'),'tag'=>'%2$s'),
	'[email]' => array('name'=>__('Email address'),'tag'=>'%3$s'),
	'[manageurl]' => array(
	'name'=> sprintf(__('%s\'s page URL'),__('Subscribe to comments')),'tag'=>'%4$s')
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
	l10n::set(dirname(__FILE__).'/locales/'.$core->blog->settings->lang.
		'/default_settings');

	require_once(dirname(__FILE__).'/default_settings.php');

	http::redirect($p_url.'&saveconfig=1');
}

try
{
	if (isset($_POST['test']))
	{
		# mail
		$title = sprintf(__('Test email from your blog - %s'),$core->blog->name);
		$content = sprintf(__('The plugin % works.'),__('Subscribe to comments'));
		subscribeToComments::checkEmail($_POST['test_email']);
		subscribeToComments::mail($_POST['test_email'],$title,$content);
		http::redirect($p_url.'&test=1');
	}
	elseif (!empty($_POST['saveconfig']))
	{
		$core->blog->settings->setNameSpace('subscribetocomments');
		# Activate Subscribe to comments
		$core->blog->settings->put('subscribetocomments_active',
			(!empty($_POST['subscribetocomments_active'])),'boolean',
			'Activate Subscribe to comments');

		# Allowed post types
		$core->blog->settings->put('subscribetocomments_post_types',
			serialize($_POST['post_types']),
			'string','Allowed post types');

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
	elseif (!empty($_POST['saveconfig_display']))
	{
		$core->blog->settings->setNameSpace('subscribetocomments');
		# display
		$core->blog->settings->put('subscribetocomments_tpl_checkbox',
			(!empty($_POST['subscribetocomments_tpl_checkbox'])),'boolean',
			'Checkbox in comment form');
		$core->blog->settings->put('subscribetocomments_tpl_css',
			(!empty($_POST['subscribetocomments_tpl_css'])),'boolean',
			'Add CSS rule');
		$core->blog->settings->put('subscribetocomments_tpl_link',
			(!empty($_POST['subscribetocomments_tpl_link'])),'boolean',
			'Link to Subscribe to comments page');

		http::redirect($p_url.'&saveconfig=1&tab=display');
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
elseif (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
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
		p.code {
			border:1px solid #ccc;
			width:100%;
			overflow:auto;
			white-space:pre;
		}
		textarea {width:100%;}
	</style>
	<script type="text/javascript">
	//<![CDATA[
		$(document).ready(function() {
			/*$('.checkboxes-helpers').each(function() {
				dotclear.checkboxesHelpers(this);
			});*/
			$('div.code').hide();
			$('#display input[type="checkbox"]').each(function() {
				$(this).css({margin:'10px',background:'Red'});
				$(this).click(function() {
					if ($(this).attr('checked')) {
						$('#'+$(this).attr('id').replace('subscribetocomments','code')).slideUp("slow");
					} else {
						$('#'+$(this).attr('id').replace('subscribetocomments','code')).slideDown("slow");
					}
				});
			});
		});
	//]]>
	</script>
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

	<div class="multi-part" id="settings" title="<?php echo __('Settings'); ?>">
		<form method="post" action="<?php echo http::getSelfURI(); ?>">
			<p>
				<?php echo(form::checkbox('subscribetocomments_active',1,
					$core->blog->settings->subscribetocomments_active)); ?>
				<label class="classic" for="subscribetocomments_active">
				<?php printf(__('Activate %s'),__('Subscribe to comments')); ?></label>
			</p>

			<h3><?php echo(__('Post types')); ?></h3>
			<p><?php printf(__('Activate %s with the following post types :'),
				__('Subscribe to comments')); ?></p>
			<ul>
				<?php
					$available_post_types = subscribeToComments::getPostTypes();
					$post_types = subscribeToComments::getAllowedPostTypes();
					foreach ($available_post_types as $type)
					{
						echo('<li>'.form::checkbox(array('post_types[]',$type),$type,
							in_array($type,$post_types)).
						' <label class="classic" for="'.$type.'">'.$type.
						'</label></li>');
					}
				?>
			</ul>
			<!--<p class="checkboxes-helpers"></p>-->

			<h3><?php echo(__('Email formatting')); ?></h3>
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
					<?php echo(__('Send an email for each subscription to the comments of a post')); ?></label>
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

	<div class="multi-part" id="display" title="<?php echo __('Display'); ?>">
		<h3><?php echo(__('Display')); ?></h3>
		<p><?php echo(
			__('This plugin needs to add some code on the post page.').' '.
			__('This can be done automatically by checking the following checkboxes.')); ?></p>
		<p><?php echo(__('If you want to customize the display on the post page (the post.hml file of your theme), uncheck the following checkboxes and follow the instructions under each checkbox :')); ?></p>
		<p><?php printf(__('You can use the plugin <strong>%s</strong> to edit the file <strong>post.html</strong>.'),
			__('Theme Editor')); ?></p>
		<form method="post" action="<?php echo http::getSelfURI(); ?>">
			<fieldset>
				<legend><?php printf(__('Install %s on the post page.'),
					__('Subscribe to comments')); ?></legend>
				<p>
					<?php echo(form::checkbox('subscribetocomments_tpl_checkbox',1,
						$core->blog->settings->subscribetocomments_tpl_checkbox)); ?>
					<label class="classic" for="subscribetocomments_tpl_checkbox">
						<?php printf(__('Add the <strong>%s</strong> checkbox in the comment form'),
							__('Receive following comments by email')); ?>
					</label>
				</p>
				<div class="code" id="code_tpl_checkbox">
					<h4><?php echo(__('or')); ?></h4>
					<p><?php echo(__('insert this in the comment form (suggestion : in the <code>&lt;fieldset&gt;</code> before the <code>&lt;/form&gt;</code> tag) :')); ?></p>
					<p class="code"><code><?php 
						echo html::escapeHTML($post_form);
					?></code></p>
				</div>
				<hr />
				<p>
					<?php printf(__('If the <strong>%s</strong> checkbox is not displayed correctly and the blog use Blowup or Blue Silence theme, check this :'),
							__('Receive following comments by email')); ?>
				</p>
				<p>
					<?php echo(form::checkbox('subscribetocomments_tpl_css',1,
						$core->blog->settings->subscribetocomments_tpl_css)); ?>
					<label class="classic" for="subscribetocomments_tpl_css">
						<?php printf(__('Add a CSS rule to style the <strong>%1$s</strong> checkbox'),
							__('Receive following comments by email')); ?>
					</label>
				</p>
				<div class="code" id="code_tpl_css">
					<h4><?php echo(__('or')); ?></h4>
					<p><?php echo(__('add this CSS rule at the end of the file <strong>style.css</strong>')); ?></p>
					<p class="code"><code><?php 
						echo($post_css);
					?></code></p>
				</div>
				<hr />
				<p>
					<?php echo(form::checkbox('subscribetocomments_tpl_link',1,
						$core->blog->settings->subscribetocomments_tpl_link)); ?>
					<label class="classic" for="subscribetocomments_tpl_link">
						<?php printf(__('Add a link to the <strong>%s</strong> page between the comments and the trackbacks'),
						__('Subscribe to comments')); ?>
					</label>
				</p>
				<div class="code" id="code_tpl_link">
					<h4><?php echo(__('or')); ?></h4>
					<p><?php echo __('insert this anywhere on the page (suggestion : just after the <code>&lt;/form&gt;</code> tag) :'); ?></p>
					<p class="code"><code><?php
						echo html::escapeHTML($post_link);
					?></code></p>
				</div>
			</fieldset>

			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="saveconfig_display" value="<?php echo __('Save configuration'); ?>" /></p>
		</form>
	</div>

	<div id="help" title="<?php echo __('Help'); ?>">
		<div class="help-content">
			<h2><?php echo(__('Help')); ?></h2>
			<p><?php printf(__('%s send notification emails to the subscribers of a post when :'),__('Subscribe to comments')); ?></p>
			<ul>
				<li><?php echo(__('a comment is posted and published immediatly')); ?></li>
				<li><?php echo(__('a pending comment is published')); ?>
			</ul>
			<p><?php echo __('If this weblog is hosted by free.fr, create a <code>/sessions/</code> directory in the root directory of the website.'); ?></p>
			<p><?php echo __('To use this plugin, you have to test if the server can send emails :'); ?></p>
			<form method="post" action="<?php echo http::getSelfURI(); ?>">
				<fieldset>
					<legend><?php echo __('Test'); ?></legend>
					<p>
						<label for="test_email"><?php echo(__('Email address')); ?></label>
						<?php echo(form::field('test_email',40,255,
							html::escapeHTML($core->auth->getInfo('user_email')))); ?>
					</p>
					<p><?php printf(
						__('This will send a email, if you don\'t receive it, try to <a href="%s">change the way Dotclear send emails</a>.'),
							'http://doc.dotclear.net/2.0/admin/install/config-envoi-mail'); ?></p>
					<p><?php echo $core->formNonce(); ?></p>
					<p><input type="submit" name="test" value="<?php echo __('Try to send an email'); ?>" /></p>
				</fieldset>
			</form>
			<hr />
			<p><?php printf(__('Inspired by <a href="%1$s">%2$s</a>'),
				'http://txfx.net/code/wordpress/subscribe-to-comments/',
				__('Subscribe to comments for WordPress')); ?></p>
		</div>
	</div>

</body>
</html>