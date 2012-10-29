<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2011 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class newsletterTabs
{
	/**
	* display tab settings
	*/
	public static function displayTabSettings()
	{
		global $core;
		
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$system_settings =& $core->blog->settings->system;
		} else {
			$system_settings->system_settings =& $core->blog->settings;
		}	
		
		try {
			$blog = &$core->blog;
				
			$mode_combo = array(__('text') => 'text',
				__('html') => 'html');
			
			$date_combo = array(__('creation date') => 'post_creadt',
				__('update date') => 'post_upddt',
				__('publication date') => 'post_dt');
							
			$size_thumbnails_combo = array(__('medium') => 'm', 
				__('small') => 's', 
				__('thumbnail') => 't', 
				__('square') => 'sq',
				__('original') => 'o');

			/*
			$sortby_combo = array(
				__('Date') => 'post_dt',
				__('Title') => 'post_title',
				__('Category') => 'cat_title',
				__('Author') => 'user_id',
				__('Status') => 'post_status',
				__('Selected') => 'post_selected'
				);
			*/
			$sortby_combo = array(
				__('Date') => 'post_dt',
				__('Title') => 'post_title',
				__('Author') => 'user_id',
				__('Selected') => 'post_selected'
				);
				
			$order_combo = array(__('Ascending') => 'asc',
				__('Descending') => 'desc' );

			$newsletter_settings = new newsletterSettings($core);

			// initialisation des variables
			$feditorname = $newsletter_settings->getEditorName();
			$feditoremail = $newsletter_settings->getEditorEmail();
			$fcaptcha = $newsletter_settings->getCaptcha();
			$fmode = $newsletter_settings->getSendMode();
			$f_use_default_format = $newsletter_settings->getUseDefaultFormat();
			$fmaxposts = $newsletter_settings->getMaxPosts();
			$fminposts = $newsletter_settings->getMinPosts();
			$f_excerpt_restriction = $newsletter_settings->getExcerptRestriction();
			$f_view_content_post = $newsletter_settings->getViewContentPost();
			$f_size_content_post = $newsletter_settings->getSizeContentPost();
			$f_view_content_in_text_format = $newsletter_settings->getViewContentInTextFormat();
			$f_view_thumbnails = $newsletter_settings->getViewThumbnails();
			$f_size_thumbnails = $newsletter_settings->getSizeThumbnails();
			$fautosend = $newsletter_settings->getAutosend();
			$f_check_notification = $newsletter_settings->getCheckNotification();
			$f_check_use_suspend = $newsletter_settings->getCheckUseSuspend();
			$f_order_date = $newsletter_settings->getOrderDate();
			$f_send_update_post = $newsletter_settings->getSendUpdatePost();
			$f_check_agora_link = $newsletter_settings->getCheckAgoraLink();
			$f_check_subject_with_date = $newsletter_settings->getCheckSubjectWithDate();
			$f_date_format_post_info = $newsletter_settings->getDateFormatPostInfo();
			$f_auto_confirm_subscription = $newsletter_settings->getAutoConfirmSubscription();
			$f_nb_newsletters_per_public_page = $newsletter_settings->getNbNewslettersPerPublicPage();
			$f_newsletters_public_page_sort = $newsletter_settings->getNewslettersPublicPageSort();
			$f_newsletters_public_page_order = $newsletter_settings->getNewslettersPublicPageOrder();
			
			$rs = $core->blog->getCategories(array('post_type'=>'post'));
			$categories = array('' => '', __('Uncategorized') => 'null');
			while ($rs->fetch()) {
				$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.html::escapeHTML($rs->cat_title)] = $rs->cat_id;
			}
			$f_category = $newsletter_settings->getCategory();
			$f_check_subcategories = $newsletter_settings->getCheckSubCategories();

			if ($newsletter_settings->getDatePreviousSend()) { 
				$f_date_previous_send = date('Y-m-j H:i',$newsletter_settings->getDatePreviousSend()+ dt::getTimeOffset($system_settings->blog_timezone)); 
			} else {
				$f_date_previous_send = 'not sent';
			}
		
			// gestion des paramètres du plugin
			echo	
				'<form action="plugin.php?p=newsletter&amp;m=settings" method="post" id="settings">'.
					'<fieldset id="advanced">'.
						'<legend>'.__('Advanced Settings').'</legend>'.
						'<p class="field">'.
							'<label for="feditorname" class="classic required" title="'.__('Required field').'">'.__('Editor name').'</label>'.
							form::field('feditorname',50,255,html::escapeHTML($feditorname)).
						'</p>'.
						'<p class="field">'.
							'<label for="feditoremail" class="classic required" title="'.__('Required field').'">'.__('Editor email').'</label>'.
							form::field('feditoremail',50,255,html::escapeHTML($feditoremail)).
						'</p>'.
						'<p class="field">'.
							'<label for="fcaptcha" class="classic">'.__('Captcha').'</label>'.
							form::checkbox('fcaptcha',1,$fcaptcha).
						'</p>'.
						'<p class="field">'.
							'<label for="f_auto_confirm_subscription" class="classic">'.__('Automatically activate the account').'</label>'.
							form::checkbox('f_auto_confirm_subscription',1,$f_auto_confirm_subscription).
						'</p>'.							
						'<p class="field">'.
							'<label for="f_use_default_format" class="classic">'.__('Use default format for sending').'</label>'.
							form::checkbox('f_use_default_format',1,$f_use_default_format).
						'</p>'.
						'<p class="field">'.
							'<label for="fmode" class="classic">'.__('Default format for sending').'</label>'.
							form::combo('fmode',$mode_combo,$fmode).
						'</p>'.						
						'<p class="field">'.
							'<label for="f_check_notification" class="classic">'.__('Notification sending').'</label>'.
							form::checkbox('f_check_notification',1,$f_check_notification).
						'</p>'.
						'<p class="field">'.
							'<label for="f_excerpt_restriction" class="classic">'.__('Restrict the preview only to excerpt of posts').'</label>'.
							form::checkbox('f_excerpt_restriction',1,$f_excerpt_restriction).
						'</p>'.
						'<p class="field">'.
							'<label for="f_view_content_post" class="classic">'.__('View contents posts').'</label>'.
							form::checkbox('f_view_content_post',1,$f_view_content_post).
						'</p>'.
						'<p class="field">'.
							'<label for="f_size_content_post" class="classic">'.__('Size contents posts').'</label>'.
							form::field('f_size_content_post',4,4,$f_size_content_post).
						'</p>'.
						'<p class="field">'.
							'<label for="f_view_content_in_text_format" class="classic">'.__('View content in text format').'</label>'.
							form::checkbox('f_view_content_in_text_format',1,$f_view_content_in_text_format).
						'</p>'.							
						'<p class="field">'.
							'<label for="f_view_thumbnails" class="classic">'.__('View thumbnails').'</label>'.
							form::checkbox('f_view_thumbnails',1,$f_view_thumbnails).
						'</p>'.
						'<p class="field">'.
							'<label for="f_size_thumbnails" class="classic">'.__('Size of thumbnails').'</label>'.
							form::combo('f_size_thumbnails',$size_thumbnails_combo,$f_size_thumbnails).
						'</p>'.	
						'<p class="field">'.
							'<label for="f_check_use_suspend" class="classic">'.__('Use suspend option').'</label>'.
							form::checkbox('f_check_use_suspend',1,$f_check_use_suspend).
						'</p>'.
						'<p class="field">'.
							'<label for="f_order_date" class="classic">'.__('Date selection for sorting posts').'</label>'.
							form::combo('f_order_date',$date_combo,$f_order_date).
						'</p>'.
						'<p class="field">'.
							'<label for="f_check_agora_link" class="classic">'.__('Automaticly create a subscriber when an user is added in the plugin Agora').'</label>'.
							form::checkbox('f_check_agora_link',1,$f_check_agora_link).
						'</p>'.
						'<p class="field">'.
							'<label for="f_date_format_post_info" class="classic">'.__('Date format for post info').'</label>'.
							form::field('f_date_format_post_info',20,20,$f_date_format_post_info).
						'</p>'.
					'</fieldset>'.

					'<fieldset id="advanced">'.
						'<legend>'.__('Settings for auto letter').'</legend>'.
						'<p class="field">'.
							'<label for="f_date_previous_send" class="classic">'.__('Date for the previous sent').'</label>'.
							form::field('f_date_previous_send',20,20,$f_date_previous_send).
						'</p>'.
						'<p class="field">'.
							'<label for="fautosend" class="classic">'.__('Automatic send when create post').'</label>'.
							form::checkbox('fautosend',1,$fautosend).
						'</p>'.
						'<p class="field">'.
							'<label for="f_send_update_post" class="classic">'.__('Automatic send when update post').'</label>'.
							form::checkbox('f_send_update_post',1,$f_send_update_post).
						'</p>'.
						'<p class="field">'.
							'<label for="fminposts" class="classic">'.__('Minimum posts').'</label>'.
							form::field('fminposts',4,4,$fminposts).
						'</p>'.
						'<p class="field">'.
							'<label for="fmaxposts" class="classic">'.__('Maximum posts').'</label>'.
							form::field('fmaxposts',4,4,$fmaxposts).
						'</p>'.
						'<p class="field">'.
							'<label for="f_category" class="classic">'.__('Category').'</label>'.
							form::combo('f_category',$categories,$f_category).
						'</p>'.
						'<p class="field">'.
							'<label for="f_check_subcategories" class="classic">'.__('Include sub-categories').'</label>'.
							form::checkbox('f_check_subcategories',1,$f_check_subcategories).
						'</p>'.
						'<p class="field">'.
							'<label for="f_check_subject_with_date" class="classic">'.__('Add the date in the title of the letter').'</label>'.
							form::checkbox('f_check_subject_with_date',1,$f_check_subject_with_date).
						'</p>'.							
					'</fieldset>'.
					'<fieldset id="newsletters_public_page">'.
						'<legend>'.__('Settings for the public page for a list of newsletters').'</legend>'.
						'<p class="field">'.
							'<label for="f_nb_newsletters_per_public_page" class="classic">'.__('Number per page').'</label>'.
							form::field('f_nb_newsletters_per_public_page',4,4,$f_nb_newsletters_per_public_page).
						'</p>'.
						'<p class="field">'.
							'<label for="f_newsletters_public_page_sort" class="classic">'.__('Sort by').'</label>'.
							form::combo('f_newsletters_public_page_sort',$sortby_combo,$f_newsletters_public_page_sort).
						'</p>'.
						'<p class="field">'.
							'<label for="f_newsletters_public_page_order" class="classic">'.__('Order').'</label>'.
							form::combo('f_newsletters_public_page_order',$order_combo,$f_newsletters_public_page_order).
						'</p>'.
					'</fieldset>'.
					// boutons du formulaire
					'<p>'.
						'<input type="submit" name="save" value="'.__('Save').'" /> '.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					'<p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'settings').
						$core->formNonce().
					'</p>'.
				'</form>'.
			'';
			
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* display tab messages
	*/
	public static function displayTabMessages()
	{
		global $core;
		try {
			$blog = &$core->blog;

			$newsletter_settings = new newsletterSettings($core);

			# en vrac
			$f_txt_link_visu_online = $newsletter_settings->getTxtLinkVisuOnline();
			$f_style_link_visu_online = $newsletter_settings->getStyleLinkVisuOnline();
			$f_style_link_read_it = $newsletter_settings->getStyleLinkReadIt();
			
			# newsletter
			$f_newsletter_subject = $newsletter_settings->getNewsletterSubject();
			$f_introductory_msg = $newsletter_settings->getIntroductoryMsg();
			$f_concluding_msg = $newsletter_settings->getConcludingMsg();
			$f_msg_presentation_form = $newsletter_settings->getMsgPresentationForm();
			$f_presentation_msg = $newsletter_settings->getPresentationMsg();
			$f_presentation_posts_msg = $newsletter_settings->getPresentationPostsMsg();

			# confirm
			$f_confirm_subject = $newsletter_settings->getConfirmSubject();
			$f_txt_intro_confirm = $newsletter_settings->getTxtIntroConfirm();
			$f_txtConfirm = $newsletter_settings->getTxtConfirm();
			$f_style_link_confirm = $newsletter_settings->getStyleLinkConfirm();
			$f_confirm_msg = $newsletter_settings->getConfirmMsg();
			$f_concluding_confirm_msg = $newsletter_settings->getConcludingConfirmMsg();

			# disable
			$f_disable_subject = $newsletter_settings->getDisableSubject();
			$f_txt_intro_disable = $newsletter_settings->getTxtIntroDisable();
			$f_txtDisable = $newsletter_settings->getTxtDisable();
			$f_style_link_disable = $newsletter_settings->getStyleLinkDisable();
			$f_disable_msg = $newsletter_settings->getDisableMsg();
			$f_concluding_disable_msg = $newsletter_settings->getConcludingDisableMsg();
			$f_txt_disabled_msg = $newsletter_settings->getTxtDisabledMsg();

			# enable
			$f_txt_intro_enable = $newsletter_settings->getTxtIntroEnable();
			$f_txtEnable = $newsletter_settings->getTxtEnable();
			$f_style_link_enable = $newsletter_settings->getStyleLinkEnable();
			$f_enable_subject = $newsletter_settings->getEnableSubject();
			$f_enable_msg = $newsletter_settings->getEnableMsg();
			$f_concluding_enable_msg = $newsletter_settings->getConcludingEnableMsg();
			$f_txt_enabled_msg = $newsletter_settings->getTxtEnabledMsg();

			# suspend
			$f_suspend_subject = $newsletter_settings->getSuspendSubject();
			$f_suspend_msg = $newsletter_settings->getSuspendMsg();
			$f_txt_suspended_msg = $newsletter_settings->getTxtSuspendedMsg();
			$f_concluding_suspend_msg = $newsletter_settings->getConcludingSuspendMsg();
			$f_txt_intro_suspend = $newsletter_settings->getTxtIntroSuspend();
			$f_txtSuspend = $newsletter_settings->getTxtSuspend();
			$f_style_link_suspend = $newsletter_settings->getStyleLinkSuspend();

			# changemode
			$f_change_mode_subject = $newsletter_settings->getChangeModeSubject();
			$f_header_changemode_msg = $newsletter_settings->getHeaderChangeModeMsg();
			$f_footer_changemode_msg = $newsletter_settings->getFooterChangeModeMsg();
			$f_changemode_msg = $newsletter_settings->getChangeModeMsg();

			# resume
			$f_resume_subject = $newsletter_settings->getResumeSubject();
			$f_header_resume_msg = $newsletter_settings->getHeaderResumeMsg();
			$f_footer_resume_msg = $newsletter_settings->getFooterResumeMsg();
				
			# subscribe
			$f_form_title_page = $newsletter_settings->getFormTitlePage();
			$f_txt_subscribed_msg = $newsletter_settings->getTxtSubscribedMsg();				


			echo	
				'<form action="plugin.php" method="post" id="messages">'.
				
					# form buttons
					'<fieldset>'.
					'<p>'.
					__('Update messages').
					'</p>'.
					'<p>'.
						'<input type="submit" name="save" value="'.__('Update').'" /> '.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					'</fieldset>'.

					'<fieldset id="define_newsletter">'.
						'<legend>'.__('Define message content Newsletter').'</legend>'.
						'<p>'.
							'<label for="f_newsletter_subject">'.__('Subject of the Newsletter').'</label>'.
							form::field('f_newsletter_subject',50,255,html::escapeHTML($f_newsletter_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_presentation_msg">'.__('Message presentation').'</label>'.
							form::field('f_presentation_msg',50,255,html::escapeHTML($f_presentation_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_introductory_msg">'.__('Introductory message').' : </label>'.
							form::textarea('f_introductory_msg',30,4,html::escapeHTML($f_introductory_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_presentation_posts_msg">'.__('Presentation message for posts').'</label>'.
							form::field('f_presentation_posts_msg',50,255,html::escapeHTML($f_presentation_posts_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_msg">'.__('Concluding message').' : </label>'.
							form::textarea('f_concluding_msg',30,4, html::escapeHTML($f_concluding_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_link_visu_online">'.__('Set the link text viewing online').'</label>'.
							form::field('f_txt_link_visu_online',50,255,html::escapeHTML($f_txt_link_visu_online)).
						'</p>'.
						'<p>'.
							'<label for="f_style_link_visu_online">'.__('Set the link style viewing online').'</label>'.
							form::field('f_style_link_visu_online',50,255,html::escapeHTML($f_style_link_visu_online)).
						'</p>'.
						'<p>'.
							'<label for="f_style_link_read_it">'.__('Set the link style read it').'</label>'.
							form::field('f_style_link_read_it',50,255,html::escapeHTML($f_style_link_read_it)).
						'</p>'.
						'<p>'.
							'<label for="f_style_link_confirm">'.__('Set the link style confirm').'</label>'.
							form::field('f_style_link_confirm',50,255,html::escapeHTML($f_style_link_confirm)).
						'</p>'.
						'<p>'.
							'<label for="f_style_link_disable">'.__('Set the link style disable').'</label>'.
							form::field('f_style_link_disable',50,255,html::escapeHTML($f_style_link_disable)).
						'</p>'.
						'<p>'.
							'<label for="f_style_link_enable">'.__('Set the link style enable').'</label>'.
							form::field('f_style_link_enable',50,255,html::escapeHTML($f_style_link_enable)).
						'</p>'.
						'<p>'.
							'<label for="f_style_link_suspend">'.__('Set the link style suspend').'</label>'.
							form::field('f_style_link_suspend',50,255,html::escapeHTML($f_style_link_suspend)).
						'</p>'.
					'</fieldset>'.
					'<fieldset id="define_subscribe">'.					
						'<legend>'.__('Define formulary content Subscribe').'</legend>'.
						'<p>'.
							'<label for="f_form_title_page">'.__('Title page of the subscribe form').'</label>'.
							form::field('f_form_title_page',50,255,html::escapeHTML($f_form_title_page)).
						'</p>'.
						'<p>'.
							'<label for="f_msg_presentation_form">'.__('Message presentation form').' : </label>'.
							form::textarea('f_msg_presentation_form',30,4,html::escapeHTML($f_msg_presentation_form)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_subscribed_msg">'.__('Subcribed message').'</label>'.
							form::field('f_txt_subscribed_msg',50,255,html::escapeHTML($f_txt_subscribed_msg)).
						'</p>'.
					'</fieldset>'.
					'<fieldset id="define_confirm">'.
						'<legend>'.__('Define message content Confirm').'</legend>'.
						'<p>'.
							'<label for="f_confirm_subject">'.__('Subject of the mail Confirm').'</label>'.
							form::field('f_confirm_subject',50,255,html::escapeHTML($f_confirm_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_confirm_msg">'.__('Confirm message').'</label>'.
							form::field('f_confirm_msg',50,255,html::escapeHTML($f_confirm_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_intro_confirm">'.__('Introductory confirm message').'</label>'.
							form::field('f_txt_intro_confirm',50,255,html::escapeHTML($f_txt_intro_confirm)).
						'</p>'.
						'<p>'.
							'<label for="f_txtConfirm">'.__('Title confirmation link').'</label>'.
							form::field('f_txtConfirm',50,255,html::escapeHTML($f_txtConfirm)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_confirm_msg">'.__('Concluding confirm message').'</label>'.
							form::field('f_concluding_confirm_msg',50,255,html::escapeHTML($f_concluding_confirm_msg)).
						'</p>'.
					'</fieldset>'.
					'<fieldset id="define_disable">'.
						'<legend>'.__('Define message content Disable').'</legend>'.
						'<p>'.
							'<label for="f_txt_disabled_msg">'.__('Txt disabled msg').
							form::field('f_txt_disabled_msg',50,255,html::escapeHTML($f_txt_disabled_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_disable_subject">'.__('Subject of the mail Disable').'</label>'.
							form::field('f_disable_subject',50,255,html::escapeHTML($f_disable_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_disable_msg">'.__('Disable message').'</label>'.
							form::field('f_disable_msg',50,255,html::escapeHTML($f_disable_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_intro_disable">'.__('Introductory disable message').'</label>'.
							form::field('f_txt_intro_disable',50,255,html::escapeHTML($f_txt_intro_disable)).
						'</p>'.
						'<p>'.
							'<label for="f_txtDisable">'.__('Title disable link').'</label>'.
							form::field('f_txtDisable',50,255,html::escapeHTML($f_txtDisable)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_disable_msg">'.__('Concluding disable msg').'</label>'.
							form::field('f_concluding_disable_msg',50,255,html::escapeHTML($f_concluding_disable_msg)).
						'</p>'.
					'</fieldset>'.					
					'<fieldset id="define_enable">'.
						'<legend>'.__('Define message content Enable').'</legend>'.
						'<p>'.
							'<label for="f_txt_enabled_msg">'.__('Texte enabled message').'</label>'.
							form::field('f_txt_enabled_msg',50,255,html::escapeHTML($f_txt_enabled_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_enable_subject">'.__('Subject of the mail Enable').'</label>'.
							form::field('f_enable_subject',50,255,html::escapeHTML($f_enable_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_enable_msg">'.__('Enable message').'</label>'.
							form::field('f_enable_msg',50,255,html::escapeHTML($f_enable_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_intro_enable">'.__('Introductory enable message').'</label>'.
							form::field('f_txt_intro_enable',50,255,html::escapeHTML($f_txt_intro_enable)).
						'</p>'.
						'<p>'.
							'<label for="f_txtEnable">'.__('Title enable link').'</label>'.
							form::field('f_txtEnable',50,255,html::escapeHTML($f_txtEnable)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_enable_msg">'.__('Concluging enable message').'</label>'.
							form::field('f_concluding_enable_msg',50,255,html::escapeHTML($f_concluding_enable_msg)).
						'</p>'.
					'</fieldset>'.	
					'<fieldset id="define_suspend">'.
						'<legend>'.__('Define message content Suspend').'</legend>'.
						'<p>'.
							'<label for="f_txt_suspended_msg">'.__('Txt suspended msg').'</label>'.
							form::field('f_txt_suspended_msg',50,255,html::escapeHTML($f_txt_suspended_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_suspend_subject">'.__('Subject of the mail Suspend').'</label>'.
							form::field('f_suspend_subject',50,255,html::escapeHTML($f_suspend_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_suspend_msg">'.__('Suspend message').'</label>'.
							form::field('f_suspend_msg',50,255,html::escapeHTML($f_suspend_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_txt_intro_suspend">'.__('Introductory suspend message').'</label>'.
							form::field('f_txt_intro_suspend',50,255,html::escapeHTML($f_txt_intro_suspend)).
						'</p>'.
						'<p>'.
							'<label for="f_txtSuspend">'.__('Title suspend link').'</label>'.
							form::field('f_txtSuspend',50,255,html::escapeHTML($f_txtSuspend)).
						'</p>'.
						'<p>'.
							'<label for="f_concluding_suspend_msg">'.__('Concluding suspend message').'</label>'.
							form::field('f_concluding_suspend_msg',50,255,html::escapeHTML($f_concluding_suspend_msg)).
						'</p>'.
					'</fieldset>'.
					'<fieldset id="define_changemode">'.
						'<legend>'.__('Define message content Changemode').'</legend>'.
						'<p>'.
							'<label for="f_changemode_msg">'.__('Change mode message').'</label>'.
							form::field('f_changemode_msg',50,255,html::escapeHTML($f_changemode_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_change_mode_subject">'.__('Subject of the mail Changing mode').'</label>'.
							form::field('f_change_mode_subject',50,255,html::escapeHTML($f_change_mode_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_header_changemode_msg">'.__('Introductory change mode message').'</label>'.
							form::field('f_header_changemode_msg',50,255,html::escapeHTML($f_header_changemode_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_footer_changemode_msg">'.__('Concludind change mode message').'</label>'.
							form::field('f_footer_changemode_msg',50,255,html::escapeHTML($f_footer_changemode_msg)).
						'</p>'.
					'</fieldset>'.					
					'<fieldset id="define_resume">'.
						'<legend>'.__('Define message content Resume').'</legend>'.
						'<p>'.
							'<label for="f_resume_subject">'.__('Subject of the mail Resume').'</label>'.
							form::field('f_resume_subject',50,255,html::escapeHTML($f_resume_subject)).
						'</p>'.
						'<p>'.
							'<label for="f_header_resume_msg">'.__('Introductory resume message').'</label>'.
							form::field('f_header_resume_msg',50,255,html::escapeHTML($f_header_resume_msg)).
						'</p>'.
						'<p>'.
							'<label for="f_footer_resume_msg">'.__('Concluding resume message').'</label>'.
							form::field('f_footer_resume_msg',50,255,html::escapeHTML($f_footer_resume_msg)).
						'</p>'.
					'</fieldset>'.					

					// boutons du formulaire
					'<fieldset>'.
					'<p>'.
					__('Update messages').
					'</p>'.					
					'<p>'.
						'<input type="submit" name="save" value="'.__('Update').'" /> '.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					'</fieldset>'.
					'<p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'messages').
						$core->formNonce().
					'</p>'.
				'</form>'.
			'';
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* edit CSS for letters
	*/
	public static function displayTabEditCSS()
	{
		global $core;

		try {
			$blog = &$core->blog;

			$letter_css = new newsletterCSS($core);
			$f_name = $letter_css->getFilenameCSS();
			$f_content = $letter_css->getLetterCSS();
			$f_editable = $letter_css->isEditable();
				
			$default_folder = path::real(newsletterPlugin::folder().'..');
				
			if ($default_folder == $letter_css->getPathCSS())
				$f_editable = false;
				
			echo	
				'<form action="plugin.php" method="post" id="file-form">'.

					'<fieldset id="edit_css_file">'.
						'<legend>'.__('File editor').'</legend>'.
						'<p>'.sprintf(__('Editing file %s'),'<strong>'.$f_name).'</strong></p>'.
						'<p>'.
							form::textarea('f_content',72,25,html::escapeHTML($f_content),'maximal','',!$f_editable).
						'</p>';

					if(!$f_editable)
						echo '<p>'.__('This file is not writable. Please check your theme files permissions.').'<p>'.
						     '<p>NB: '.__('If you want edit the file CSS, please copy the default file style_letter.css in your current theme folder.').'</p>';
					else {
					echo
					'<p>'.
						'<input type="submit" name="write" value="'.__('save').' (s)" accesskey="s" /> '.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'write_css').
						$core->formNonce().
					'</p>';
					}
					echo
					'</fieldset>'.
				'</form>'.
			'';
						

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

	}	

	/**
	* planning options
	*/
	public static function displayTabPlanning()
	{
		global $core;
		try {
			$blog = &$core->blog;

				// Utilisation de dcCron
				if($core->plugins->moduleExists('dcCron') && !isset($core->plugins->getDisabledModules['dcCron'])) {
					
					$newsletter_cron=new newsletterCron($core);
					$newsletter_settings = new newsletterSettings($core);

					$f_check_schedule = $newsletter_settings->getCheckSchedule();
					$f_interval = ($newsletter_cron->getTaskInterval() ? $newsletter_cron->getTaskInterval() : 604800);
					$f_first_run = ($newsletter_cron->getFirstRun() ? $newsletter_cron->getFirstRun() : '');
						
			     	echo
						'<fieldset>'.
						'<legend>'.__('Planning newsletter').'</legend>'.
							'<form action="plugin.php" method="post" name="planning">'.
								'<p class="field">'.
								'<label class="classic" for="f_interval">'.__('Interval time in seconds between 2 runs').
								form::field('f_interval',20,20,$f_interval).
								'</label></p>'.
								'<p class="comments">'.
								__('samples').' : ( 1 '.__('day').' = 86400s / 1 '.__('week').' = 604800s / 28 '.__('days').' =  2420000s )'.
								'</p>'.
								'<p class="field">'.
								'<label for="f_first_run" class="classic">'.__('Date for the first run').'</label>'.
								form::field('f_first_run',20,20,$f_first_run).
								'</p>'.

								# form buttons
								'<p>'.
									'<input type="submit" name="submit" value="'.(($f_check_schedule)?__('Unschedule'):__('Schedule')).'" /> '.
									'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
								'</p>'.
								form::hidden(array('p'),newsletterPlugin::pname()).
								form::hidden(array('op'),(($f_check_schedule)?'unschedule':'schedule')).
								$core->formNonce().
							'</form>'.
						'</fieldset>'.
						'';
						
					if ($f_check_schedule) {
							
						$f_task_state = $newsletter_cron->getState();
							
						echo
							'<fieldset>'.
							'<legend>'.__('Scheduled task').' : '.html::escapeHTML($newsletter_cron->getTaskName()).'</legend>'.
							'<table summary="resume" class="minimal">'.
							'<thead>'.
							'<tr>'.
		  						'<th>'.__('Name').'</th>'.
		  						'<th>'.__('Value').'</th>'.
							'</tr>'.
							'</thead>'.
							'<tbody id="classes-list">'.
							'<tr class="line">'.
							'<td>'.__('State').'</td>'.
							'<td>'.html::escapeHTML(__($newsletter_cron->printState())).'</td>'.
							'</tr>'.
							'<tr class="line">'.
							'<td>'.__('Interval').'</td>'.
							'<td>'.html::escapeHTML($newsletter_cron->printTaskInterval()).'</td>'.
							'</tr>'.
							'<tr class="line">'.
							'<td>'.__('Last run').'</td>'.
							'<td>'.html::escapeHTML($newsletter_cron->printLastRunDate()).'</td>'.
							'</tr>'.
							'<tr class="line">'.
							'<td>'.__('Next run').'</td>'.
							'<td>'.html::escapeHTML($newsletter_cron->printNextRunDate()).'</td>'.
							'</tr>'.
							'<tr class="line">'.
							'<td>'.__('Remaining Time').'</td>'.
							'<td>'.html::escapeHTML($newsletter_cron->printRemainingTime()).'</td>'.
							'</tr>'.
							'</tbody>'.
							'</table>'.										

							'<form action="plugin.php" method="post" name="taskplanning">'.

							# form buttons
							'<p>'.
								'<input type="submit" name="submit" value="'.(($f_task_state)?__('Disable'):__('Enable')).'" /> '.
							'</p>'.
							form::hidden(array('p'),newsletterPlugin::pname()).
							form::hidden(array('op'),(($f_task_state)?'disabletask':'enabletask')).
							$core->formNonce().
							'</form>'.
							
							'</fieldset>'.
							'';
					}					
				} else {
					echo
						'<fieldset>'.
							'<legend>'.__('Planning newsletter').'</legend>'.
							'<p>'.
								'<label class="classic">'.__('Install the plugin dcCron for using planning').'</label>'.
							'</p>'.
						'</fieldset>';
				}
			

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

	}

	/**
	* Maintenance tab
	*/
	public static function displayTabMaintenance()
	{
		global $core;
		
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$blog_settings =& $core->blog->settings->newsletter;
			$system_settings =& $core->blog->settings->system;
		} else {
			$blog_settings =& $core->blog->settings;
			$system_settings->system_settings =& $core->blog->settings;
		}		
		
		try {
			$blog = &$core->blog;
			$auth = &$core->auth;
			$url = &$core->url;
			$blogurl = &$blog->url;
			$urlBase = newsletterTools::concatURL($blogurl, $url->getBase('newsletter'));			
			
			$export_format_combo = array(__('text file') => 'txt',
				__('data file') => 'dat');
			$f_export_format = 'dat';
			
			$core->themes = new dcThemes($core);
			$core->themes->loadModules($core->blog->themes_path,null);
			$bthemes = array();
			foreach ($core->themes->getModules() as $k => $v)
			{
				if (file_exists($blog->themes_path . '/' . $k . '/tpl/home.html'))
					$bthemes[html::escapeHTML($v['name'])] = $k;
			}			
			$theme = $system_settings->theme;

			$sadmin = (($auth->isSuperAdmin()) ? true : false);

			echo
			'<fieldset>'.
				'<legend>'.__('Plugin state').'</legend>'.
				'<form action="plugin.php" method="post" id="state">'.
					'<p class="field">'.
						'<label for="newsletter_flag" class="classic">'.__('Enable plugin').'</label>'.
						form::checkbox('newsletter_flag', 1, $blog_settings->newsletter_flag).
					'</p>'.
					'<p>'.
						'<input type="submit" value="'.__('Save').'" /> '.
						'<input type="reset" value="'.__('Cancel').'" /> '.
					'</p>'.
					form::hidden(array('p'),newsletterPlugin::pname()).
					form::hidden(array('op'),'state').
					$core->formNonce().
				'</form>'.
			'</fieldset>'.
			'';

			if ($blog_settings->newsletter_flag) {
				# export / import	
				echo
				'<fieldset>'.
					'<legend>'.__('Import/Export subscribers list').'</legend>'.
					'<form action="plugin.php" method="post" id="export" name="export">'.
						'<p class="classic">'.
							'<label for="f_export_format" class="classic">'.__('Export format').'&nbsp;:&nbsp;</label>'.
							form::combo('f_export_format',$export_format_combo,$f_export_format).
						'</p>'.
						'<p>'.
							'<label class="classic">'.
							form::radio(array('type'),'blog',(!$sadmin) ? true : false).__('This blog only').
							'</label>'.
							'<br />'.
							'<label class="classic">'.
							form::radio(array('type'),'all',($sadmin) ? true : false,'','',(!$sadmin) ? true : false).__('All datas').
							'</label>'.
						'</p>'.
						'<p>'.
							'<input type="submit" value="'.__('Export').'" />'.
						'</p>'.
							form::hidden(array('p'),newsletterPlugin::pname()).
							form::hidden(array('op'),'export').
							$core->formNonce().											
					'</form>'.
						
					'<form action="plugin.php" method="post" id="import" name="import">'.
						'<p>'.
							'<label class="classic">'.
							form::radio(array('type'),'blog',(!$sadmin) ? true : false).__('This blog only').
							'</label>'.
							'<br />'.
							'<label class="classic">'.
							form::radio(array('type'),'all',($sadmin) ? true : false,'','',(!$sadmin) ? true : false).__('All datas').
							'</label>'.
						'</p>'.
						'<p>'.
							'<input type="submit" value="'.__('Import').'" />'.
						'</p>'.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),'import').
						$core->formNonce().											
					'</form>'.					
						
				'</fieldset>'.
				'';
	
				# Importing a subscribers list from a file
				echo
				'<fieldset>'.
					'<legend>'.__('Importing a subscribers list from a file text in the current blog').'</legend>'.				
						'<form action="plugin.php" method="post" id="reprise" name="reprise" enctype="multipart/form-data">'.
							'<p>'.
								'<label class="classic required" title="'.__('Required field').'">'.
								__('File to import :').' '.
								'<input type="file" name="file_reprise" />'.
								'</label>'.
							'</p>'.
							'<p>'.
								'<label class="classic required" title="'.__('Required field').'">'.
								__('Your password:').' '.
								form::password(array('your_pwd'),20,255).'</label>'.
							'</p>'.
							'<p>'.
								'<input type="submit" value="'.__('Launch reprise').'" />'.
							'</p>'.
							form::hidden(array('p'),newsletterPlugin::pname()).
							form::hidden(array('op'),'reprise').
							$core->formNonce().
						'</form>'.
				'</fieldset>'.
				'';
					
				if ($sadmin) {
					# adapt template
					echo
					'<fieldset>'.
						'<legend>'.__('Adapt the template for the theme').'</legend>'.
							'<form action="plugin.php" method="post" id="adapt">'.
								'<p>'.__('<strong>Caution :</strong> use the theme adapter only if you experience some layouts problems when viewing newsletter form on your blog.')."</p>".
								'<p><label class="classic" for="fthemes">'.__('Theme name').' : '.
								form::combo('fthemes',$bthemes,$theme).
								'</label></p>'.
								'<p>'.
								'<input type="submit" value="'.__('Adapt').'" />'.
								'</p>'.
								'<p>'.
								'<a href="'.$urlBase.'/test'.'" target="_blank">'.__('Clic here to test the template.').'</a>'.
								'</p>'.
								form::hidden(array('p'),newsletterPlugin::pname()).
								form::hidden(array('op'),'adapt_theme').
								$core->formNonce().
							'</form>'.
					'</fieldset>'.
					'';
				}
	
				if ($sadmin) {
					echo
					'<fieldset>'.
						'<legend>'.__('Erasing all informations about newsletter in database').'</legend>'.
							'<form action="plugin.php" method="post" id="erasingnewsletter">'.
								'<p>'.__('<strong>Caution :</strong> please backup your database before use this option.')."</p>".
								'</p>'.
								'<p>'.
								//'<input type="submit" value="'.__('Erasing').'" name="delete" class="delete" onclick="erasingnewsletterConfirm(); return false" />'.
									'<input type="submit" value="'.__('Erasing').'" name="delete" class="delete"/>'.
								'</p>'.
								form::hidden(array('p'),newsletterPlugin::pname()).
								form::hidden(array('op'),'erasingnewsletter').
								$core->formNonce().
							'</form>'.
					'</fieldset>'.
					'';
				}
				
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* form subscriber
	*/
	public static function AddEdit()
	{
		global $core;
		try
		{
			$blog = &$core->blog;
			$settings = &$blog->settings;
			$allowed = true;
			$mode_combo = array(__('text') => 'text',
							__('html') => 'html');

			$state_combo = array(__('pending') => 'pending',
							__('enabled') => 'enabled',
							__('suspended') => 'suspended',
							__('disabled') => 'disabled');


				# test adding or editing
				if (!empty($_GET['id']))
				{
					$id = (integer)$_GET['id'];
					$datas = newsletterCore::get($id);
					if ($datas == null) {
						$allowed = false;
					} else {
						$email = $datas->f('email');
						$subscribed = $datas->f('subscribed');
						$lastsent = $datas->f('lastsent');
						$modesend = $datas->f('modesend');
						$regcode = $datas->f('regcode');
						$state = $datas->f('state');
						$form_title = __('Edit a subscriber');
						$form_op = 'edit';
						$form_libel = __('Update');
						$form_id = '<input type="hidden" name="id" value="'.$id.'" />';

						if ($subscribed != null) 
							$subscribed = dt::dt2str($settings->date_format, $subscribed).' @'.dt::dt2str($settings->time_format, $subscribed);
						else 
							$subscribed = __('Never');
				
						if ($lastsent != null) 
							$lastsent = dt::dt2str($settings->date_format, $lastsent).' @'.dt::dt2str($settings->time_format, $lastsent);
						else 
							$lastsent = __('Never');

						$form_update =
						'<p class="field">'.
							'<label for="fsubscribed" class="classic">'.__('Subscribed:').'</label>'.
							form::field('fsubscribed',50,255,$subscribed,'','',true).
						'</p>'.
						'<p class="field">'.
							'<label for="flastsent" class="classic">'.__('Last sent:').'</label>'.
							form::field('flastsent',50,255,$lastsent,'','',true).
						'</p>'.
						'<p class="field">'.
							'<label for="fmodesend" class="classic">'.__('Mode send').'</label>'.
							form::combo('fmodesend',$mode_combo,$modesend).
						'</p>'.
						'<p class="field">'.
							'<label for="fregcode" class="classic">'.__('Registration code:').'</label>'.
							form::field('fregcode',50,255,$regcode,'','',true).
						'</p>'.
						'<p class="field">'.
							'<label for "fstate" class="classic">'.__('Status:').'</label>'.
							form::combo('fstate',$state_combo,$state).
						'</p>'.
						'';
					}
			
				} else {
					$id = -1;
					$email = '';
					$subscribed = '';
					$lastsent = '';
					$modesend = '';
					$status = '';
					$form_title = __('Add a subscriber');
					$form_op = 'add';
					$form_libel = __('Add');
					$form_id = '';
					$form_update = '';
				}

				if (!$allowed) {
					echo __('Not allowed.');
				} else {
					echo 
					'<form action="plugin.php?p=newsletter&amp;m=subscribers" method="post" id="addedit">'.
					'<fieldset id="addedit">'.
						'<legend>'.$form_title.'</legend>'.
							'<p class="field">'.
								'<label for="femail" class="classic">'.__('Email:').'</label>'.
								form::field('femail',50,255, $email).
								
							'</p>'.
							$form_update.
					'</fieldset>'.	
					'<p>'.
						'<input type="submit" value="'.$form_libel.'" />'.
						'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
						form::hidden(array('p'),newsletterPlugin::pname()).
						form::hidden(array('op'),$form_op).
						$form_id.
						$core->formNonce().
					'</p>'.
					
					'</form>'.
					'';
				}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* print statistics
	*/
	public static function displayTabResume()
	{
		global $core;
		try {
			$blog = &$core->blog;

			echo newsletterSubscribersList::fieldsetResumeSubscribers();
			echo newsletterLettersList::fieldsetResumeLetters();
			
			# todo : statistics sending letters (bounce)

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

	}
}

?>