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

if (!defined('DC_RC_PATH')) {return;}

$core->tpl->addBlock('SubscribeToCommentsIsActive',
		'subscribeToCommentsIsActive');

function subscribeToCommentsIsActive($attr,$content)
{
	return '<?php if ($core->blog->settings->subscribetocomments_active) : ?>'.
		$content.
		'<?php endif; ?>';
}

/**
@ingroup Subscribe to comments
@brief Document
*/
class subscribeToCommentsDocument extends dcUrlHandlers
{
	/**
	serve the document
	*/
	public static function page($args)
	{
		global $core;

		if (!$core->blog->settings->subscribetocomments_active) {self::p404();}
		
		$session_id = session_id();
		if (empty($session_id)) {session_start();}
		
		$_ctx =& $GLOBALS['_ctx'];
		
		$_ctx->subscribeToComments = new ArrayObject();
		$_ctx->subscribeToComments->email = '';
		$_ctx->subscribeToComments->checkCookie = false;
		$_ctx->subscribeToComments->blocked = false;
		
		try {
			subscribeToComments::cleanKeys();

			if (((isset($_GET['post_id']))) && (!is_numeric($_GET['post_id'])))
			{
				throw new Exception(__('Invalid post ID.'));
			}

			if (isset($_POST['logout'])) {
				subscriber::checkNonce();
				subscriber::logout();
				subscribeToComments::redirect('loggedout');
			}
			# login with key
			elseif ((isset($_GET['email'])) AND (isset($_GET['key'])))
			{
				subscribeToComments::checkEmail($_GET['email']);
				subscribeToComments::checkKey($_GET['key']);
				subscriber::loginKey($_GET['email'],$_GET['key']);
				subscribeToComments::redirect('loggedin');
			}
			# subscribe
			elseif ((isset($_POST['subscribe'])) AND (isset($_POST['post_id'])))
			{
				subscriber::checkNonce();
				if (isset($_POST['email']))
				{
					subscribeToComments::checkEmail($_POST['email']);
					$email = $_POST['email'];
				}
				elseif (subscriber::checkCookie())
				{
					$email = subscriber::getCookie('email');
				}
				if (!empty($email))
				{
					$subscriber = new subscriber($email);
					$subscriber->subscribe($_POST['post_id']);
					subscribeToComments::redirect('subscribed');
				}
			}
			# request account informations
			elseif ((isset($_POST['resend'])) AND (isset($_POST['email'])))
			{
				subscriber::checkNonce();
				subscribeToComments::checkEmail($_POST['email']);
				subscriber::resendInformations($_POST['email']);
				subscribeToComments::redirect('informationsresent');
			}
			# update the email address
			elseif ((isset($_GET['new_email'])) AND (isset($_GET['temp_key'])))
			{
				subscribeToComments::checkEmail($_GET['new_email']);
				subscribeToComments::checkKey($_GET['temp_key']);
				subscriber::updateEmail($_GET['new_email'],$_GET['temp_key']);
				subscribeToComments::redirect('updatedemail');
			}
			
			# email address
			$_ctx->subscribeToComments->email = '';
			if (isset($_COOKIE['comment_info']))
			{
				$email = explode("\n",$_COOKIE['comment_info']);
				$_ctx->subscribeToComments->email = $email['1'];
				unset($email);
			}

			# subscriber is logged in
			$_ctx->subscribeToComments->checkCookie = subscriber::checkCookie();
			if ($_ctx->subscribeToComments->checkCookie)
			{
				$subscriber = new subscriber(subscriber::getCookie('email'));
				$_ctx->subscribeToComments->email = $subscriber->email;
	
				if ((isset($_POST['requestChangeEmail'])) AND (isset($_POST['new_email'])))
				{
					subscriber::checkNonce();
					subscribeToComments::checkEmail($_POST['new_email']);
					$subscriber->requestUpdateEmail($_POST['new_email']);
					subscribeToComments::redirect('requestsent');	
				}
				elseif ((isset($_POST['remove'])) AND (isset($_POST['entries'])))
				{
					subscriber::checkNonce();
					$subscriber->removeSubscription($_POST['entries']);
					subscribeToComments::redirect('removedsubscriptions');
				}
				elseif (isset($_POST['deleteAccount'])) {
					subscriber::checkNonce();
					$subscriber->deleteAccount();
					subscribeToComments::redirect('accountdeleted');
				}
				elseif (isset($_POST['blockEmails'])) {
					subscriber::checkNonce();
					$subscriber->blockEmails(true);
					subscribeToComments::redirect('emailsblocked');
				}
				elseif (isset($_POST['allowEmails'])) {
					subscriber::checkNonce();
					$subscriber->blockEmails(false);
					subscribeToComments::redirect('emailsallowed');
				}
			}
		}
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}
		
		$_ctx->subscribeToComments->blocked = subscriber::blocked();
		
		# message
		# inspirated by contactMe/_public.php
		switch($args)
		{
			case 'informationsresent' :
				$msg = __('Account informations sent');
				break;
			case 'removedsubscriptions' :
				$msg = __('Subscriptions removed');
				break;
			case 'loggedout' :
				$msg = __('Logged out');
				break;
			case 'loggedin' :
				$msg = __('Logged in');
				break;
			case 'emailsblocked' :
				$msg = __('Emails blocked');
				break;
			case 'emailsallowed' :
				$msg = __('Emails allowed');
				break;
			case 'requestsent' :
				$msg = __('An email has been sent to the new email address');
				break;
			case 'updatedemail' :
				$msg = __('Email address changed');
				break;
			case 'accountdeleted' :
				$msg = __('Account deleted');
				break;
			case 'subscribed' :
				$msg = __('Subscribed to the entry');
				break;
			 default :
			 	$msg = null;
			 	break;
		}
		
		$_ctx->subscribeToComments->message = $msg;
		# /message
		
		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/');
		
		self::serveDocument('subscribetocomments.html','text/html',false,false);
	}
}

/**
@ingroup Subscribe to comments
@brief Template
*/
class subscribeToCommentsTpl
{
	/**
	check the box on post.html if a cookie is present
	@return	<b>string</b> PHP block
	*/
	public static function formChecked()
	{
		return("<?php ".
		"if (isset(\$_POST['subscribeToComments'])) {echo(' checked=\"checked\" ');}".
		"elseif (isset(\$_COOKIE['subscribetocomments']))".
		"{echo(' checked=\"checked\" ');}".
		" ?>");
	}

	/**
	get link from post.html to subscriptions page
	@return	<b>string</b> text and PHP block
	*/
	public static function formLink()
	{
		global $core;

		if ($core->blog->settings->subscribetocomments_active)
		{
			return("<?php echo(subscribeToComments::url().".
			"((\$core->blog->settings->url_scan == 'query_string') ? '&amp;' : '?').".
			"'post_id='.\$_ctx->posts->post_id); ?>");
		}
	}
		
	/**
	if there is a message
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifMessage($attr,$content)
	{
		return
		"<?php if (\$_ctx->subscribeToComments->message !== null) : ?>"."\n".
		$content.
		"<?php endif; ?>";
	}

	/**
	display a message
	@return	<b>string</b> PHP block
	*/
	public static function message()
	{
		return("<?php if (\$_ctx->subscribeToComments->message !== null) :"."\n".
		"echo(\$_ctx->subscribeToComments->message);".
		"endif; ?>");
	}

	/**
	get nonce
	@return	<b>string</b> Nonce
	*/
	public static function getNonce()
	{
		return "<?php echo(crypt::hmac(DC_MASTER_KEY,session_id())); ?>";
	}

	/**
	if it's a post
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function entryIf($attr,$content)
	{
		return
		"<?php if ((isset(\$_GET['post_id'])) AND ".
		"(is_numeric(\$_GET['post_id']))) : "."\n".
		"\$_ctx->posts = \$core->blog->getPosts(".
		"array('no_content' => true, 'post_id' => \$_GET['post_id'],".
		"'post_open_comment' => 1,".
		"'post_type' => subscribeToComments::getAllowedPostTypes())".
		"); "."\n".
		"if (!\$_ctx->posts->isEmpty()) : ?>"."\n".
		$content.
		"<?php unset(\$_ctx->posts); ".
		"endif;"."\n".
		"endif; ?>";
	}

	/**
	if user is not logged in
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function loggedIfNot($attr,$content)
	{
		return('<?php if (!$_ctx->subscribeToComments->checkCookie) : ?>'."\n".
		$content."\n".
		"<?php endif; ?>");
	}

	/**
	if user is logged in
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function loggedIf($attr,$content)
	{
		return('<?php if ($_ctx->subscribeToComments->checkCookie) : ?>'."\n".
		$content."\n".
		"<?php endif; ?>");
	}

	/**
	if user is not blocked
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function blockedIfNot($attr,$content)
	{
		return('<?php if (!$_ctx->subscribeToComments->blocked) : ?>'."\n".
		$content."\n".
		"<?php endif; ?>");
	}

	/**
	if user is blocked
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function blockedIf($attr,$content)
	{
		return('<?php if ($_ctx->subscribeToComments->blocked) : ?>'."\n".
		$content."\n".
		"<?php endif; ?>");
	}

	/**
	loop on posts
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function entries($attr,$content)
	{
		return("<?php ".
		'$_ctx->meta = new dcMeta($core);'.
		"\$_ctx->posts = \$_ctx->meta->getPostsByMeta(array(".
		"'meta_type' => 'subscriber','meta_id' => ".
		"subscriber::getCookie('id'),".
		"'no_content' => true,".
		"'post_type' => subscribeToComments::getAllowedPostTypes()));".
		"if (!\$_ctx->posts->isEmpty()) :"."\n".
		"while (\$_ctx->posts->fetch()) : ?>"."\n".
		$content.
		"<?php endwhile; "."\n".
		" endif;"."\n".
		'unset($_ctx->meta);'.
		"unset(\$_ctx->posts); ?>");
	}

	/**
	get email address
	@return	<b>string</b> PHP block
	*/
	public static function email()
	{
		return('<?php echo($_ctx->subscribeToComments->email); ?>');	
	}

	/**
	get the URL of the subscriptions page
	@return	<b>string</b> URL
	*/
	public static function url()
	{
		return("<?php echo(subscribeToComments::url()); ?>");
	}

	/**
	display checkbox to subscribe to comments
	*/
	public static function publicCommentFormAfterContent()
	{
		global $_ctx;

		if (subscribeToComments::getPost($_ctx->posts->post_id) == false)
		{return;}

		$checked = null;

		# if checkbox is unchecked, don't check it
		if (isset($_POST['subscribeToComments'])) 
			{$checked = true;}
		elseif (isset($_COOKIE['subscribetocomments']))
			{$checked = true;}
		if ($checked) {$checked =  ' checked="checked" ';}

		$logged = 
		(subscriber::checkCookie())
		?
			$logged = ' (<strong><a href="'.subscribeToComments::url().'">'.
				__('Logged in').'</a></strong>)'
		: '';

		echo '<p>'.
		'<input type="checkbox" name="subscribeToComments" '.
		'id="subscribeToComments"'.$checked.' /> '.
		'<label for="subscribeToComments">'.
		__('Receive following comments by email').'</label>'.
		$logged.
		'</p>';
	}
	
	/**
	display a CSS rule for default themes
	*/
	public static function publicHeadContent()
	{
		echo '<style type="text/css" media="screen">'."\n".
		'#comment-form #subscribeToComments '.
		'{width:auto;border:0;margin:0 5px 0 140px;}'."\n".
		'</style>';
	}

	/**
	add tpl code after the <tpl:EntryIf comments_active="1">...</tpl:EntryIf> tag
	@param	core	<b>core</b>	Dotclear core
	@param	b	<b>array</b>	tag
	@param	attr	<b>array</b>	attributes
	*/
	public static function templateAfterBlock(&$core,$b,$attr)
	{
		global $_ctx;

		if ($core->url->type == 'feed') {return;}

		if ($b == 'EntryIf' && isset($attr['comments_active'])
			&& $attr['comments_active'] == 1 && !isset($attr['pings_active']))
		{
			if ((!is_numeric($_ctx->posts->post_id)) OR
			(subscribeToComments::getPost($_ctx->posts->post_id) == false))
			{
				return;
			}
			# else
			return 
			'<?php if (($core->blog->settings->subscribetocomments_active) &&
				$_ctx->posts->commentsActive()) : ?>
				<div id="subscribetocomments_block">
					<h3><?php echo __("Subscribe to comments"); ?></h3>
					<p>
						<a href="<?php echo(subscribeToComments::url().
						(($core->blog->settings->url_scan == "query_string") ? "&amp;" : "?").
						"post_id=".$_ctx->posts->post_id); ?>">
							<!-- # If the subscriber is logged in -->
							<?php if (subscriber::checkCookie()) : ?>
								<?php echo __("Subscribe to receive following comments by email or manage subscriptions"); ?>
							<?php endif; ?>
							<!-- # If the subscriber is not logged in -->
							<?php if (!subscriber::checkCookie()) : ?>
								<?php echo __("Subscribe to receive following comments by email"); ?>
							<?php endif; ?>
						</a>
					</p>
				</div>
			<?php endif; ?>';
			# strings
			__("Subscribe to receive following comments by email or manage subscriptions");
			__("Subscribe to receive following comments by email");
		}
	}
}

if ($core->blog->settings->subscribetocomments_active)
{
	# behaviors
	$core->addBehavior('coreAfterCommentCreate',array('subscribeToComments',
		'coreAfterCommentCreate'));

	# post.html
	$core->tpl->addValue('SubscribeToCommentsFormChecked',
		array('subscribeToCommentsTpl','formChecked'));
	$core->tpl->addValue('SubscribeToCommentsFormLink',
		array('subscribeToCommentsTpl','formLink'));

	# blocks
	$core->tpl->addBlock('SubscribeToCommentsLoggedIf',
		array('subscribeToCommentsTpl','loggedIf'));
	$core->tpl->addBlock('SubscribeToCommentsLoggedIfNot',
		array('subscribeToCommentsTpl','loggedIfNot'));
	$core->tpl->addBlock('SubscribeToCommentsBlockedIf',
		array('subscribeToCommentsTpl','blockedIf'));
	$core->tpl->addBlock('SubscribeToCommentsBlockedIfNot',
		array('subscribeToCommentsTpl','blockedIfNot'));

	# nonce
	$core->tpl->addValue('SubscribeToCommentsNonce',
		array('subscribeToCommentsTpl','getNonce'));

	# page
	$core->tpl->addBlock('SubscribeToCommentsEntryIf',
		array('subscribeToCommentsTpl','entryIf'));
	
	# message
	$core->tpl->addBlock('SubscribeToCommentsIfMessage',
		array('subscribeToCommentsTpl','ifMessage'));
	$core->tpl->addValue('SubscribeToCommentsMessage',
		array('subscribeToCommentsTpl','message'));

	# form	
	$core->tpl->addValue('SubscribeToCommentsURL',
		array('subscribeToCommentsTpl','url'));
	$core->tpl->addValue('SubscribeToCommentsEmail',
		array('subscribeToCommentsTpl','email'));

	# posts
	$core->tpl->addBlock('SubscribeToCommentsEntries',
		array('subscribeToCommentsTpl','entries'));

	# add code to post.html
	if ($core->blog->settings->subscribetocomments_tpl_checkbox === true)
	{
		$core->addBehavior('publicCommentFormAfterContent',
			array('subscribeToCommentsTpl','publicCommentFormAfterContent'));
	}
	if ($core->blog->settings->subscribetocomments_tpl_css === true)
	{
		$core->addBehavior('publicHeadContent',
			array('subscribeToCommentsTpl','publicHeadContent'));
	}
	if ($core->blog->settings->subscribetocomments_tpl_link === true)
	{
		$core->addBehavior('templateAfterBlock',
			array('subscribeToCommentsTpl','templateAfterBlock'));
	}
}

?>