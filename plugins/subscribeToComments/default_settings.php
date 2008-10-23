<?php 

if (!defined('DC_RC_PATH')) { return; }

$settings =& $core->blog->settings;

$settings->setNameSpace('subscribetocomments');

# Enable Subscribe to comments
$settings->put('subscribetocomments_active',true,
'boolean','Enable Subscribe to comments');

# Change From: header of outbound emails
$settings->put('subscribetocomments_email_from',
'dotclear@'.$_SERVER['HTTP_HOST'],
'string','Change From: header of outbound emails');

# Allowed post types
$settings->put('subscribetocomments_post_types',
serialize(subscribeToComments::getPostTypes()),
'string','Allowed post types');

$nl = "\n";
$nls = $nl.$nl;
$separator = '----------';
$foot_separator = '--';
$hello = __('Hello [email],');
$account = 
	__('To manage your subscriptions, change your email address or block emails, click here : [manageurl]');

# Account subject
$settings->put('subscribetocomments_account_subject',
	subscribeToComments::format($tags_account,__('Your account on [blogname]'),
		false,true),'text',
	'Email subject');
# Account content
$settings->put('subscribetocomments_account_content',
	subscribeToComments::format($tags_account,
		$hello.$nl.
		__('here are some informations about your account on [blogname] :').
		$nls.
		__('Email address : [email]').$nls.
		$account.$nls.
		$foot_separator.$nl.'[blogurl]',false,true)
	,'text','Email content');

# Send an email for each subscription
$settings->put('subscribetocomments_subscribe_active',
	false,'boolean','Send an email for each subscription');
# Subscription subject
$settings->put('subscribetocomments_subscribe_subject',
	subscribeToComments::format($tags_subscribe,
		__('Subscribed to [posttitle] - [blogname]'),false,true),'text',
		'Subscription subject');
# Subscription content
$settings->put('subscribetocomments_subscribe_content',
	subscribeToComments::format($tags_subscribe,
		$hello.$nl.
		__('you subscribed to [posttitle] : [posturl]').$nls.
		$separator.$nls.
		$account.$nls.
		$foot_separator.$nl.'[blogurl]',false,true)
	,'text','Subscription content');

# Comment subject
$settings->put('subscribetocomments_comment_subject',
	subscribeToComments::format($tags_comment,
	__('New comment on [posttitle] - [blogname]'),false,true),'text',
	'Comment subject');
# Comment content
$settings->put('subscribetocomments_comment_content',
	subscribeToComments::format($tags_comment,
		$hello.$nl.
		__('a new comment has been posted by [commentauthor] on [posttitle] :').
		$nls. 
		$separator.$nls.
		'[commentcontent]'.$nls.
		$separator.$nls.
		__('View the comment : [commenturl]').$nls.
		__('View the post : [posturl]').$nls.
		$separator.$nls.
		$account.$nls.
		$foot_separator.$nl.'[blogurl]',false,true)
	,'text','Comment content');

# Email subject
$settings->put('subscribetocomments_email_subject',
	subscribeToComments::format($tags_email,
		__('Change email address on [blogname]')),'text','Email subject',
		false,true);
# Email content
$settings->put('subscribetocomments_email_content',
	subscribeToComments::format($tags_email,
		$hello.$nl.
		__('you have requested to change the email address of your subscriptions to [newemail], click on this link : [emailurl]').
		$nls.
		__('This link is valid for 24 hours.').$nls.
		$separator.$nls.
		$account.$nls.
		$foot_separator.$nl.'[blogurl]',false,true)
	,'text','Email content');

# display
$settings->put('subscribetocomments_tpl_checkbox',true,
	'boolean','Checkbox in comment form');

$subscribetocomments_tpl_css = false;
$theme = $settings->theme;
if (($theme == 'default') OR ($theme == 'blueSilence'))
{
	$subscribetocomments_tpl_css = true;
}
$settings->put('subscribetocomments_tpl_css',
	$subscribetocomments_tpl_css,'boolean','Add CSS rule');

$settings->put('subscribetocomments_tpl_link',true,
	'boolean','Link to Subscribe to comments page');
		
?>