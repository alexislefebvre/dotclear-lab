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

	function subscribeToCommentsIsActive($attr,$content)
	{
		return '<?php if ($core->blog->settings->subscribetocomments_active) : ?>'.
			$content.
			'<?php endif; ?>';
	}
	$core->tpl->addBlock('SubscribeToCommentsIsActive',
			'subscribeToCommentsIsActive');

	if ($core->blog->settings->subscribetocomments_active)
	{
		require_once(dirname(__FILE__).'/lib.subscribeToComments.php');
		require_once(dirname(__FILE__).'/class.subscriber.php');

		# load locales for the blog language
		$file = dirname(__FILE__).'/locales/'.$core->blog->settings->lang.
			'/public.php';
		if (file_exists($file)) {require_once($file);}

		/**
		@ingroup Subscribe to comments
		@brief Document
		*/
		class subscribeToCommentsDocument extends dcUrlHandlers
		{
			/**
			serve the document
			*/
			public static function page()
			{
				global $core;

				$session_id = session_id();
				if (empty($session_id)) {session_start();}
		
				# from /dotclear/inc/admin/prepend.php, modified
				# Check nonce from POST requests
				if (!empty($_POST))
				{
					# post but no nonce : when someone post a comment in a post with 
					# subscribetocomments in the URL
					if ((empty($_POST['subscribeToCommentsNonce'])) ||
						($_POST['subscribeToCommentsNonce'] != 
							crypt::hmac(DC_MASTER_KEY,session_id()))
					)
					{
						http::head(412);
						header('Content-Type: text/html');
						echo 'Precondition Failed';
						echo '<br /><a href="'.subscribeToComments::url().'">Reload the page</a>';
						exit;
					}
				}
				# /from /dotclear/inc/admin/prepend.php, modified
			
				try {
					subscribeToComments::cleanKeys();
		
					if (((isset($_GET['post_id']))) && (!is_numeric($_GET['post_id'])))
					{
						throw new Exception(__('Invalid post ID.'));
					}
		
					if (isset($_POST['logout'])) {
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

					$_ctx =& $GLOBALS['_ctx'];

					# messages
					$_ctx->subscribeToCommentsMessage = null; 
					if (isset($_GET['message']))
					{
						$messages = array(
					      'informationsresent' => __('Account informations sent'),
					      'removedsubscriptions' => __('Subscriptions removed'),
					      'loggedout' => __('Logged out'),
					      'loggedin' => __('Logged in'),
					      'emailsblocked' => __('Emails blocked'),
					      'emailsallowed' => __('Emails allowed'),
					      'requestsent' => 
					      	__('An email has been sent to the new email address'),
					      'updatedemail' => __('Email address changed'),
					      'accountdeleted' => __('Account deleted'),
					      'subscribed' => __('Subscribed to the entry')
					   );
						if (array_key_exists($_GET['message'],$messages))
						{
							$_ctx->subscribeToCommentsMessage = $messages[$_GET['message']];
						}
					}

					# email address
					$_ctx->subscribeToCommentsEmail = '';
					if (isset($_COOKIE['comment_info']))
					{
						$_ctx->subscribeToCommentsEmail = explode("\n",$_COOKIE['comment_info']);
						$_ctx->subscribeToCommentsEmail = $_ctx->subscribeToCommentsEmail['1'];
					}

					# subscriber is logged in
					if (subscriber::checkCookie())
					{
						$subscriber = new subscriber(
						subscriber::getCookie('email'));
						$_ctx->subscribeToCommentsEmail = $subscriber->email;
			
						if ((isset($_POST['requestChangeEmail'])) AND (isset($_POST['new_email'])))
						{
							subscribeToComments::checkEmail($_POST['new_email']);
							$subscriber->requestUpdateEmail($_POST['new_email']);
							subscribeToComments::redirect('requestsent');	
						}
						elseif ((isset($_POST['remove'])) AND (isset($_POST['entries']))) {
							$subscriber->removeSubscription($_POST['entries']);
							subscribeToComments::redirect('removedsubscriptions');
						}
						elseif (isset($_POST['deleteAccount'])) {
							$subscriber->deleteAccount();
							subscribeToComments::redirect('accountdeleted');
						}
						elseif (isset($_POST['blockEmails'])) {
							$subscriber->blockEmails(true);
							subscribeToComments::redirect('emailsblocked');
						}
						elseif (isset($_POST['allowEmails'])) {
							$subscriber->blockEmails(false);
							subscribeToComments::redirect('emailsallowed');
						}
					}
				}
				catch (Exception $e)
				{
					$_ctx->subscribeToCommentsError = $e->getMessage();
				}

				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates/');

				self::serveDocument('subscribetocomments.html','text/html',false,false);
			}

			/**
			behavior publicAfterCommentCreate
			subscribe if requested and send emails to subscribers
			@param	cur <b>cursor</b> Cursor
			*/
			public static function publicAfterCommentCreate($cur,$comment_id)
			{
				if (isset($_POST['subscribeToComments']))
				{
					$subscriber = new subscriber($cur->comment_email);
					$subscriber->subscribe($cur->post_id);
				}
				subscribeToComments::send($cur,$comment_id);
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
			if there is an error
			@param	attr	<b>array</b>	Attribute
			@param	content	<b>string</b>	Content
			@return	<b>string</b> PHP block
			*/
			public static function ifError($attr,$content)
			{
				return
				"<?php if (\$_ctx->subscribeToCommentsError !== null) : ?>"."\n".
				$content.
				"<?php endif; ?>";
			}

			/**
			display an error
			@return	<b>string</b> PHP block
			*/
			public static function error()
			{
				return("<?php if (\$_ctx->subscribeToCommentsError !== null) :"."\n".
				"echo(\$_ctx->subscribeToCommentsError);".
				"endif; ?>");
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
				"<?php if (\$_ctx->subscribeToCommentsMessage !== null) : ?>"."\n".
				$content.
				"<?php endif; ?>";
			}

			/**
			display a message
			@return	<b>string</b> PHP block
			*/
			public static function message()
			{
				return("<?php if (\$_ctx->subscribeToCommentsMessage !== null) :"."\n".
				"echo(\$_ctx->subscribeToCommentsMessage);".
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
				"'post_open_comment' => 1)".
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
				return('<?php if (!subscriber::checkCookie()) : ?>'."\n".
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
				return('<?php if (subscriber::checkCookie()) : ?>'."\n".
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
				return('<?php if (!subscriber::blocked()) : ?>'."\n".
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
				return('<?php if (subscriber::blocked()) : ?>'."\n".
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
				"'no_content' => true));".
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
				return('<?php echo($_ctx->subscribeToCommentsEmail); ?>');	
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
				$checked = null;

				# if checkbox if unchecked, don't check it
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
				'id="subscribeToComments"'.$checked.' />'.
				'<label for="subscribeToComments">'.
				__('Receive following comments by email').'</label>'.
				$logged.
				'</p>';
			}
			
			/**
			display a CSS rule for defaults themes
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
			@param	core	<b>array</b>	b ?
			@param	attr	<b>array</b>	attributes
			*/
			public static function templateAfterBlock(&$core,$b,$attr)
			{
				if ($b == 'EntryIf' && isset($attr['comments_active'])
					&& $attr['comments_active'] == 1 && !isset($attr['pings_active']))
				{
					return 
					'<?php if ($core->blog->settings->subscribetocomments_active) : ?>
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
					<?php endif; ?>';
					# strings
					__("Subscribe to receive following comments by email or manage subscriptions");
					__("Subscribe to receive following comments by email");
				}
			}
		}

		$core->url->register('subscribetocomments','subscribetocomments',
			'^subscribetocomments(/.+)?$',array('subscribeToCommentsDocument','page'));
	
		# behaviors
		$core->addBehavior('publicAfterCommentCreate',
			array('subscribeToCommentsDocument','publicAfterCommentCreate'));
	
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

		# error
		$core->tpl->addBlock('SubscribeToCommentsIfError',
			array('subscribeToCommentsTpl','ifError'));
		$core->tpl->addValue('SubscribeToCommentsError',
			array('subscribeToCommentsTpl','error'));

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