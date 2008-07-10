<?php 

if (!defined('DC_RC_PATH')) { return; }

$settings = new dcSettings($core,$core->blog->id);

$core->blog->settings->setNameSpace('subscribetocomments');
# Activate Subscribe to comments
$core->blog->settings->put('subscribetocomments_active',true,
'boolean','Activate Subscribe to comments');

$nl = "\n";
$nls = $nl.$nl;
$separator = '----------';
$foot_separator = '--';
$hello = __('Hello [email],');
$account = 
	__('To manage your subscriptions, change your email address or block emails, click here : [manageurl]');
 
# Account subject
$core->blog->settings->put('subscribetocomments_account_subject',
	format($tags_account,__('Your account on [blogname]')),'text',
	'Email subject');
# Account content
$core->blog->settings->put('subscribetocomments_account_content',
	format($tags_account,
		$hello.$nl.
		__('here are some informations about your account on [blogname] :').$nls.
		__('Email address : [email]').$nls.
		$account.$nls.
		$foot_separator.$nl.'[blogurl]'
),'text','Email content');

# Send an email for each subscription
$core->blog->settings->put('subscribetocomments_subscribe_active',
	false,'boolean','Send an email for each subscription');
# Subscription subject
$core->blog->settings->put('subscribetocomments_subscribe_subject',
	format($tags_subscribe,__('Subscribed to [posttitle] - [blogname]')),
		'text','Subscription subject');
# Subscription content
$core->blog->settings->put('subscribetocomments_subscribe_content',
	format($tags_subscribe,
		$hello.$nl.
		__('you subscribed to [posttitle] : [posturl]').$nls.
		$separator.$nls.
		$account.$nls.
		$foot_separator.$nl.'[blogurl]'
),'text','Subscription content');

# Comment subject
$core->blog->settings->put('subscribetocomments_comment_subject',
	format($tags_comment,__('New comment on [posttitle] - [blogname]')),'text',
	'Comment subject');
# Comment content
$core->blog->settings->put('subscribetocomments_comment_content',
	format($tags_comment,
		$hello.$nl.
		__('a new comment has been posted by [commentauthor] on [posttitle] :').$nls. 
		$separator.$nls.
		'[commentcontent]'.$nls.
		$separator.$nls.
		__('View the comment : [commenturl]').$nls.
		__('View the post : [posturl]').$nls.
		$separator.$nls.
		$account.$nls.
		$foot_separator.$nl.'[blogurl]'
),'text','Comment content');

# Email subject
$core->blog->settings->put('subscribetocomments_email_subject',
	format($tags_email,__('Change email address on [blogname]')),'text',
	'Email subject');
# Email content
$core->blog->settings->put('subscribetocomments_email_content',
	format($tags_email,
		$hello.$nl.
		__('you have requested to change the email address of your subscriptions to [newemail], click on this link : [emailurl]').$nls.
		__('This link is valid for 24 hours.').$nls.
		$separator.$nls.
		$account.$nls.
		$foot_separator.$nl.'[blogurl]'
),'text','Email content');

# display
$core->blog->settings->put('subscribetocomments_tpl_checkbox',true,
	'boolean','Checkbox in comment form');

$subscribetocomments_tpl_css = false;
$theme = $core->blog->settings->theme;
if (($theme == 'default') OR ($theme == 'blueSilence'))
{
	$subscribetocomments_tpl_css = true;
}
$core->blog->settings->put('subscribetocomments_tpl_css',
	$subscribetocomments_tpl_css,'boolean','Add CSS rule');

$core->blog->settings->put('subscribetocomments_tpl_link',true,
	'boolean','Link to Subscribe to comments page');
		
?>