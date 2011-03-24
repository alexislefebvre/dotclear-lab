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

if (!defined('DC_CONTEXT_ADMIN')) exit;
//dcPage::check('usage,admin');
dcPage::check('newsletter,contentadmin');

# Settings compatibility test
if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
	$blog_settings =& $core->blog->settings->newsletter;
	$system_settings =& $core->blog->settings->system;
} else {
	$blog_settings =& $core->blog->settings;
	$system_settings =& $core->blog->settings;
}

// Loading librairies
require_once dirname(__FILE__).'/inc/class.newsletter.settings.php';
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';
require_once dirname(__FILE__).'/inc/class.newsletter.admin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.cron.php';
require_once dirname(__FILE__).'/inc/class.newsletter.tools.php';
require_once dirname(__FILE__).'/inc/class.newsletter.letter.php';

// setting variables
$plugin_name	= __('Newsletter');
$id 			= null;
$p_url		= 'plugin.php?p=newsletter';

try {

$newsletter_flag = (boolean)$blog_settings->newsletter_flag;
	
// Recovery module
$m = (!empty($_REQUEST['m'])) ? (string) rawurldecode($_REQUEST['m']) : 'subscribers';

// Recovery tab
$plugin_tab = 'tab_'.$m;

$edit_subscriber = ('addedit'==$m && !empty($_GET['id'])) ? __('Edit') : __('Add');

if (!empty($_POST['tab'])) 
	$plugin_tab = 'tab_'.(string)$_POST['tab'];
else if (!empty($_GET['tab'])) 
	$plugin_tab = 'tab_'.(string)$_GET['tab'];

// Recovery parameter operation
$plugin_op = (!empty($_POST['op'])) ? (string)$_POST['op'] : 'none';

// Recovery parameter action on letters
$action =  (!empty($_POST['action'])) ? (string) $_POST['action'] : 'none';

// Recovery the message to print
if (!empty($_GET['msg'])) 
	$msg = (string) rawurldecode($_GET['msg']);
else if (!empty($_POST['msg'])) 
	$msg = (string) rawurldecode($_POST['msg']);

/* ----------
 * operations
 * ----------
*/
switch ($plugin_op)
{
	###############################################
	# STATE
	###############################################
	
	// Modified the activation state of the plugin
	case 'state':
	{
		$m = 'maintenance';

		if (empty($_POST['newsletter_flag']) !== null) {
			
			$newsletter_flag = (empty($_POST['newsletter_flag']))?false:true;
			$blog_settings->put('newsletter_flag',$newsletter_flag,'boolean','Plugin status newsletter');
			$msg = __('Plugin status updated.');
		}
		
		// notification of changes to blog
		newsletterPlugin::triggerBlog();

		//$msg = __('Plugin status updated.');
		newsletterTools::redirection($m,$msg);
	}
	break;

	###############################################
	# SETTINGS
	###############################################

	// Modified settings
	case 'settings':
	{
		$m = 'settings';

		if (!empty($_POST['feditoremail']) && !empty($_POST['feditorname'])) {
			$newsletter_settings = new newsletterSettings($core);

			$newsletter_settings->setEditorName($_POST['feditorname']);
			$newsletter_settings->setEditorEmail($_POST['feditoremail']);
				
			(!empty($_POST['fcaptcha']) ? $newsletter_settings->setCaptcha($_POST['fcaptcha']) : $newsletter_settings->clearCaptcha());
			(!empty($_POST['f_auto_confirm_subscription']) ? $newsletter_settings->setAutoConfirmSubscription($_POST['f_auto_confirm_subscription']) : $newsletter_settings->clearAutoConfirmSubscription());
			(!empty($_POST['fmode']) ? $newsletter_settings->setSendMode($_POST['fmode']) : $newsletter_settings->clearSendMode());
			(!empty($_POST['f_use_default_format']) ? $newsletter_settings->setUseDefaultFormat($_POST['f_use_default_format']) : $newsletter_settings->clearUseDefaultFormat());
			(!empty($_POST['fautosend']) ? $newsletter_settings->setAutosend($_POST['fautosend']) : $newsletter_settings->clearAutosend());
			(!empty($_POST['f_send_update_post']) ? $newsletter_settings->setSendUpdatePost($_POST['f_send_update_post']) : $newsletter_settings->clearSendUpdatePost());
			
			/*if ($_POST['fautosend']==true || $_POST['f_send_update_post']==true) {
				$newsletter_settings->clearMinPosts();
				$newsletter_settings->clearMaxPosts();
			} else {*/
				(!empty($_POST['fminposts']) ? $newsletter_settings->setMinPosts($_POST['fminposts']) : $newsletter_settings->clearMinPosts());
				(!empty($_POST['fmaxposts']) ? $newsletter_settings->setMaxPosts($_POST['fmaxposts']) : $newsletter_settings->clearMaxPosts());
			//}
			
			if (!empty($_POST['f_excerpt_restriction'])) {
				$newsletter_settings->setExcerptRestriction($_POST['f_excerpt_restriction']);
				// dependency
				$newsletter_settings->clearViewContentPost();
			} else {
				$newsletter_settings->clearExcerptRestriction();
				(!empty($_POST['f_view_content_post']) ? $newsletter_settings->setViewContentPost($_POST['f_view_content_post']) : $newsletter_settings->clearViewContentPost());
			}
			
			(!empty($_POST['f_view_content_in_text_format']) ? $newsletter_settings->setViewContentInTextFormat($_POST['f_view_content_in_text_format']) : $newsletter_settings->clearViewContentInTextFormat());
			(!empty($_POST['f_view_thumbnails']) ? $newsletter_settings->setViewThumbnails($_POST['f_view_thumbnails']) : $newsletter_settings->clearViewThumbnails());
			(!empty($_POST['f_size_content_post']) ? $newsletter_settings->setSizeContentPost($_POST['f_size_content_post']) : $newsletter_settings->clearSizeContentPost());
			(!empty($_POST['f_size_thumbnails']) ? $newsletter_settings->setSizeThumbnails($_POST['f_size_thumbnails']) : $newsletter_settings->clearSizeThumbnails());
			(!empty($_POST['f_category']) ? $newsletter_settings->setCategory($_POST['f_category']) : $newsletter_settings->clearCategory());
			(!empty($_POST['f_check_subcategories']) ? $newsletter_settings->setCheckSubCategories($_POST['f_check_subcategories']) : $newsletter_settings->clearCheckSubCategories());
			(!empty($_POST['f_check_notification']) ? $newsletter_settings->setCheckNotification($_POST['f_check_notification']) : $newsletter_settings->clearCheckNotification());
			(!empty($_POST['f_check_use_suspend']) ? $newsletter_settings->setCheckUseSuspend($_POST['f_check_use_suspend']) : $newsletter_settings->clearCheckUseSuspend());
			(!empty($_POST['f_order_date']) ? $newsletter_settings->setOrderDate($_POST['f_order_date']) : $newsletter_settings->clearOrderDate());
			(!empty($_POST['f_date_previous_send']) ? $newsletter_settings->setDatePreviousSend($_POST['f_date_previous_send']) : $newsletter_settings->setDatePreviousSend());
			(!empty($_POST['f_check_agora_link']) ? $newsletter_settings->setCheckAgoraLink($_POST['f_check_agora_link']) : $newsletter_settings->clearCheckAgoraLink());
			(!empty($_POST['f_check_subject_with_date']) ? $newsletter_settings->setCheckSubjectWithDate($_POST['f_check_subject_with_date']) : $newsletter_settings->clearCheckSubjectWithDate());
			(!empty($_POST['f_date_format_post_info']) ? $newsletter_settings->setDateFormatPostInfo($_POST['f_date_format_post_info']) : $newsletter_settings->clearDateFormatPostInfo());
			
			// notification of changes to blog
			$newsletter_settings->save();
			newsletterTools::redirection($m,rawurldecode(__('Settings updated.')));
		} else {
			if (empty($_POST['feditoremail']))
				throw new Exception(__('You must input a valid email'));
				
			if (empty($_POST['feditorname']))
				throw new Exception(__('You must input an editor')); 
		}
	}
	break;

	// Modified messages
	case 'messages':
	{
		$m = 'messages';
		$newsletter_settings = new newsletterSettings($core);
		
		// en vrac
		(!empty($_POST['f_txt_link_visu_online']) ? $newsletter_settings->setTxtLinkVisuOnline($_POST['f_txt_link_visu_online']) : $newsletter_settings->clearTxtLinkVisuOnline());
		(!empty($_POST['f_style_link_visu_online']) ? $newsletter_settings->setStyleLinkVisuOnline($_POST['f_style_link_visu_online']) : $newsletter_settings->clearStyleLinkVisuOnline());
		(!empty($_POST['f_style_link_read_it']) ? $newsletter_settings->setStyleLinkReadIt($_POST['f_style_link_read_it']) : $newsletter_settings->clearStyleLinkReadIt());
		
		// newsletter
		(!empty($_POST['f_introductory_msg']) ? $newsletter_settings->setIntroductoryMsg($_POST['f_introductory_msg']) : $newsletter_settings->clearIntroductoryMsg());
		(!empty($_POST['f_concluding_msg']) ? $newsletter_settings->setConcludingMsg($_POST['f_concluding_msg']) : $newsletter_settings->clearConcludingMsg());
		(!empty($_POST['f_presentation_msg']) ? $newsletter_settings->setPresentationMsg($_POST['f_presentation_msg']) : $newsletter_settings->clearPresentationMsg());
		(!empty($_POST['f_presentation_posts_msg']) ? $newsletter_settings->setPresentationPostsMsg($_POST['f_presentation_posts_msg']) : $newsletter_settings->clearPresentationPostsMsg());
		(!empty($_POST['f_newsletter_subject']) ? $newsletter_settings->setNewsletterSubject($_POST['f_newsletter_subject']) : $newsletter_settings->clearNewsletterSubject());

		// confirm
		(!empty($_POST['f_txt_intro_confirm']) ? $newsletter_settings->setTxtIntroConfirm($_POST['f_txt_intro_confirm']) : $newsletter_settings->clearTxtIntroConfirm());
		(!empty($_POST['f_txtConfirm']) ? $newsletter_settings->setTxtConfirm($_POST['f_txtConfirm']) : $newsletter_settings->clearTxtConfirm());
		(!empty($_POST['f_style_link_confirm']) ? $newsletter_settings->setStyleLinkConfirm($_POST['f_style_link_confirm']) : $newsletter_settings->clearStyleLinkConfirm());
		(!empty($_POST['f_confirm_subject']) ? $newsletter_settings->setConfirmSubject($_POST['f_confirm_subject']) : $newsletter_settings->clearConfirmSubject());
		(!empty($_POST['f_confirm_msg']) ? $newsletter_settings->setConfirmMsg($_POST['f_confirm_msg']) : $newsletter_settings->clearConfirmMsg());
		(!empty($_POST['f_concluding_confirm_msg']) ? $newsletter_settings->setConcludingConfirmMsg($_POST['f_concluding_confirm_msg']) : $newsletter_settings->clearConcludingConfirmMsg());

		// disable
		(!empty($_POST['f_txt_intro_disable']) ? $newsletter_settings->setTxtIntroDisable($_POST['f_txt_intro_disable']) : $newsletter_settings->clearTxtIntroDisable());
		(!empty($_POST['f_txtDisable']) ? $newsletter_settings->setTxtDisable($_POST['f_txtDisable']) : $newsletter_settings->clearTxtDisable());
		(!empty($_POST['f_style_link_disable']) ? $newsletter_settings->setStyleLinkDisable($_POST['f_style_link_disable']) : $newsletter_settings->clearStyleLinkDisable());
		(!empty($_POST['f_disable_subject']) ? $newsletter_settings->setDisableSubject($_POST['f_disable_subject']) : $newsletter_settings->clearDisableSubject());
		(!empty($_POST['f_disable_msg']) ? $newsletter_settings->setDisableMsg($_POST['f_disable_msg']) : $newsletter_settings->clearDisableMsg());
		(!empty($_POST['f_concluding_disable_msg']) ? $newsletter_settings->setConcludingDisableMsg($_POST['f_concluding_disable_msg']) : $newsletter_settings->clearConcludingDisableMsg());
		(!empty($_POST['f_txt_disabled_msg']) ? $newsletter_settings->setTxtDisabledMsg($_POST['f_txt_disabled_msg']) : $newsletter_settings->clearTxtDisabledMsg());

		// enable
		(!empty($_POST['f_txt_intro_enable']) ?	$newsletter_settings->setTxtIntroEnable($_POST['f_txt_intro_enable']) : $newsletter_settings->clearTxtIntroEnable());
		(!empty($_POST['f_txtEnable']) ? $newsletter_settings->setTxtEnable($_POST['f_txtEnable']) : $newsletter_settings->clearTxtEnable());
		(!empty($_POST['f_style_link_enable']) ? $newsletter_settings->setStyleLinkEnable($_POST['f_style_link_enable']) : $newsletter_settings->clearStyleLinkEnable());
		(!empty($_POST['f_enable_subject']) ? $newsletter_settings->setEnableSubject($_POST['f_enable_subject']) : $newsletter_settings->clearEnableSubject());
		(!empty($_POST['f_enable_msg']) ? $newsletter_settings->setEnableMsg($_POST['f_enable_msg']) : $newsletter_settings->clearEnableMsg());
		(!empty($_POST['f_concluding_enable_msg']) ? $newsletter_settings->setConcludingEnableMsg($_POST['f_concluding_enable_msg']) : $newsletter_settings->clearConcludingEnableMsg());
		(!empty($_POST['f_txt_enabled_msg']) ? $newsletter_settings->setTxtEnabledMsg($_POST['f_txt_enabled_msg']) : $newsletter_settings->clearTxtEnabledMsg());
			
		// suspend
		(!empty($_POST['f_txt_intro_suspend']) ? $newsletter_settings->setTxtIntroSuspend($_POST['f_txt_intro_suspend']) : $newsletter_settings->clearTxtIntroSuspend());
		(!empty($_POST['f_txtSuspend']) ? $newsletter_settings->setTxtSuspend($_POST['f_txtSuspend']) : $newsletter_settings->clearTxtSuspend());
		(!empty($_POST['f_style_link_suspend']) ? $newsletter_settings->setStyleLinkSuspend($_POST['f_style_link_suspend']) : $newsletter_settings->clearStyleLinkSuspend());
		(!empty($_POST['f_suspend_subject']) ? $newsletter_settings->setSuspendSubject($_POST['f_suspend_subject']) : $newsletter_settings->clearSuspendSubject());
		(!empty($_POST['f_suspend_msg']) ? $newsletter_settings->setSuspendMsg($_POST['f_suspend_msg']) : $newsletter_settings->clearSuspendMsg());
		(!empty($_POST['f_concluding_suspend_msg']) ? $newsletter_settings->setConcludingSuspendMsg($_POST['f_concluding_suspend_msg']) : $newsletter_settings->clearConcludingSuspendMsg());
		(!empty($_POST['f_txt_suspended_msg']) ? $newsletter_settings->setTxtSuspendedMsg($_POST['f_txt_suspended_msg']) : $newsletter_settings->clearTxtSuspendedMsg());
			
		// changemode
		(!empty($_POST['f_change_mode_subject']) ? $newsletter_settings->setChangeModeSubject($_POST['f_change_mode_subject']) : $newsletter_settings->clearChangeModeSubject());
		(!empty($_POST['f_header_changemode_msg']) ? $newsletter_settings->setHeaderChangeModeMsg($_POST['f_header_changemode_msg']) : $newsletter_settings->clearHeaderChangeModeMsg());
		(!empty($_POST['f_footer_changemode_msg']) ? $newsletter_settings->setFooterChangeModeMsg($_POST['f_footer_changemode_msg']) : $newsletter_settings->clearFooterChangeModeMsg());
		(!empty($_POST['f_changemode_msg']) ? $newsletter_settings->setChangeModeMsg($_POST['f_changemode_msg']) : $newsletter_settings->clearChangeModeMsg());
			
		// resume
		(!empty($_POST['f_resume_subject']) ? $newsletter_settings->setResumeSubject($_POST['f_resume_subject']) : $newsletter_settings->clearResumeSubject());
		(!empty($_POST['f_header_resume_msg']) ? $newsletter_settings->setHeaderResumeMsg($_POST['f_header_resume_msg']) : $newsletter_settings->clearHeaderResumeMsg());
		(!empty($_POST['f_footer_resume_msg']) ? $newsletter_settings->setFooterResumeMsg($_POST['f_footer_resume_msg']) : $newsletter_settings->clearFooterResumeMsg());
				
		// subscribe
		(!empty($_POST['f_msg_presentation_form']) ? $newsletter_settings->setMsgPresentationForm($_POST['f_msg_presentation_form']) : $newsletter_settings->clearMsgPresentationForm());
		(!empty($_POST['f_form_title_page']) ? $newsletter_settings->setFormTitlePage($_POST['f_form_title_page']) : $newsletter_settings->clearFormTitlePage());
		(!empty($_POST['f_txt_subscribed_msg']) ? $newsletter_settings->setTxtSubscribedMsg($_POST['f_txt_subscribed_msg']) : $newsletter_settings->clearTxtSubscribedMsg());

		// notification of changes to blog
		$newsletter_settings->save();
		
		$msg = __('Messages updated.');
		newsletterTools::redirection($m,$msg);
	}
	break;

	###############################################
	# CSS
	###############################################
	
	// write the new CSS
	case 'write_css':	
	{
		$m = 'editCSS';
		
		if (!empty($_POST['write']))
		{
			$letter_css = new newsletterCSS($core);
			$msg=$letter_css->setLetterCSS($_POST['f_content']);
		}		

		newsletterTools::redirection($m,$msg);
	}
	break;		
	
	###############################################
	# PLANIFICATION
	###############################################
	
	// schedule newsletter
	case 'schedule':
	{
		$m = 'planning';

		if ($core->blog->dcCron instanceof dcCron) {
			$newsletter_cron = new newsletterCron($core);	
			
			// adding scheduled task
			$interval = (($_POST['f_interval']) ? $_POST['f_interval'] : 604800);
			$f_first_run = (($_POST['f_first_run']) ? strtotime(html::escapeHTML($_POST['f_first_run'])) : time() + dt::getTimeOffset($system_settings->blog_timezone));
			
			if ($newsletter_cron->add($interval, $f_first_run)) {
				// notification of changes to blog
				$newsletter_settings = new newsletterSettings($core);
				$newsletter_settings->setCheckSchedule(true);
				$newsletter_settings->save();
				$msg = __('Planning updated.');
			} else {
				throw new Exception(__('Error during create planning task.'));
			}
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// unschedule newsletter
	case 'unschedule':
	{
		$m = 'planning';

		if ($core->blog->dcCron instanceof dcCron) {
			$newsletter_cron=new newsletterCron($core);	
			$newsletter_settings = new newsletterSettings($core);
			$newsletter_settings->setCheckSchedule(false);
			
			// delete scheduled task
			$newsletter_cron->del();

			// notification of changes to blog
			$newsletter_settings->save();
			
			$msg = __('Planning updated.');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// enable schedule task
	case 'enabletask':
	{
		$m = 'planning';

		if ($core->blog->dcCron instanceof dcCron) {
			$newsletter_cron=new newsletterCron($core);
			$newsletter_settings = new newsletterSettings($core);
				
			// enable scheduled task
			$newsletter_cron->enable();

			// notification of changes to blog
			$newsletter_settings->save();
			
			$msg = __('Planning updated.');
		}
		newsletterTools::redirection($m,$msg);

	}
	break;

	// disable schedule task
	case 'disabletask':
	{
		$m = 'planning';

		if ($core->blog->dcCron instanceof dcCron) {
			$newsletter_cron=new newsletterCron($core);
			$newsletter_settings = new newsletterSettings($core);
				
			// disable scheduled task
			$newsletter_cron->disable();

			// notification of changes to blog
			$newsletter_settings->save();
			
			$msg = __('Planning updated.');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	###############################################
	# SUBSCRIBERS
	###############################################

	// add subscriber
	case 'add':
	{
		$m = 'addedit';
		
		$email = !empty($_POST['femail']) ? $_POST['femail'] : null;

		if (newsletterCore::add($email)) {
			$msg = __('Subscriber added.');
		} else {
			throw new Exception(__('Error adding subscriber.'));
		}

		newsletterTools::redirection($m,$msg);
	}
	break;

	// Modify subscriber
	case 'edit':
	{
		$id = (!empty($_POST['id']) ? $_POST['id'] : null);
		$email = (!empty($_POST['femail']) ? $_POST['femail'] : null);
		$subscribed = (!empty($_POST['fsubscribed']) ? $_POST['fsubscribed'] : null);
		$lastsent = (!empty($_POST['flastsent']) ? $_POST['flastsent'] : null);
		$modesend = (!empty($_POST['fmodesend']) ? $_POST['fmodesend'] : null);
		$state = (!empty($_POST['fstate']) ? $_POST['fstate'] : null);

		if ($email == null) {
			if ($id == null) {
				throw new Exception(__('Missing informations'));
			} else {
				$plugin_tab = 'tab_addedit';
			}
		} else {
			$regcode = null;
			if (!newsletterCore::update($id, $email, $state, $regcode, $subscribed, $lastsent, $modesend)) {
				throw new Exception(__('Error in modify subscriber'));
			} else {
				$msg = __('Subscriber updated.');
			}
		}
		newsletterTools::redirection($m,$msg);			
	}
	break;

	// remove subscribers
	case 'remove':
	{
		$msg = __('No account removed.');
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			if (newsletterCore::delete($ids)) {
				$msg = __('Account(s) successfully removed.');
			} else {
				throw new Exception(__('Error to remove account(s)'));
			}
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// suspend subscribers
	case 'suspend':
	{
		$msg = __('No account suspended.');
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			if (newsletterCore::suspend($ids)) {
				$msg = __('Account(s) successfully suspended.');
			} else {
				throw new Exception(__('Error to suspend account(s)'));
			}			
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// activate subscribers
	case 'enable':
	{
		$msg = __('No account enabled.');
		if (is_array($_POST['subscriber'])) {
			$ids = array();

			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}
			if (newsletterCore::enable($ids)) 
				$msg = __('Account(s) successfully enabled.');
			else
				throw new Exception(__('Error to enable account(s)'));
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// disable subscribers
	case 'disable':
	{
		$msg = __('No account disabled.');
		if (is_array($_POST['subscriber'])) {
			$ids = array();

			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			if (newsletterCore::disable($ids)) 
				$msg = __('Account(s) successfully disabled.');
			else
				throw new Exception(__('Error to disable account(s)'));
		}
		newsletterTools::redirection($m,$msg);	
	}
	break;

	// set mail format to html
	case 'changemodehtml':
	{
		$msg = __('No account(s) updated.');
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			if (newsletterCore::changemodehtml($ids)) 
				$msg = __('Format sending for account(s) successfully updated to html.');
			else
				throw new Exception(__('Error in modification format'));
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// set mail format to text
	case 'changemodetext':
	{
		$msg = __('No account(s) updated.');
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			if (newsletterCore::changemodetext($ids)) 
				$msg = __('Format sending for account(s) successfully updated to text.');
			else
				throw new Exception(__('Error in modification format'));
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	###############################################
	# MAILING
	###############################################

	// send newsletter
	case 'send':
	{
		if(!empty($_POST['subscriber'])) {
			$subscribers_id = array();
			$subscribers_id = $_POST['subscriber'];
			
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				// check if users are enabled
				if ($subscriber = newsletterCore::get((integer) $v)){
					if ($subscriber->state == 'enabled') {
						$ids[$k] = (integer) $v;
					}
				}
			}
			$subscribers_id = $ids;
		} else {
			throw new Exception(__('no user selected'));
			//newsletterTools::redirection($m,$msg);
		}
	}
	break;

	// initialize field lastsent
	case 'lastsent':
	{
		$msg = __('No account changed.');
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			if (newsletterCore::lastsent($ids, 'clear')) 
				$msg = __('Account(s) successfully changed.');
			else
				throw new Exception(__('Error in modification of field last sent'));
		}
		newsletterTools::redirection($m,$msg);
	}
	break;
	
	// send confirmation mail
	case 'sendconfirm':
	{
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			$msg = newsletterCore::send($ids,'confirm');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;
	
	// send disable mail
	case 'senddisable':
	{
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			$msg = newsletterCore::send($ids,'disable');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;
	
	// send enable mail
	case 'sendenable':
	{
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			$msg = newsletterCore::send($ids,'enable');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	###############################################
	# MAINTENANCE
	###############################################

	// exporting list of subscribers
	case 'export':
	{
		$m = 'maintenance';

		$export_format = (!empty($_POST['f_export_format']) ? $_POST['f_export_format'] : 'dat');
		$type = (!empty($_POST['type']) ? $_POST['type'] : 'blog');
		$msg = newsletterAdmin::exportToBackupFile((($type=='blog') ? true : false), $export_format);

		newsletterTools::redirection($m,$msg);
	}
	break;

	// importing list of subscribers
	case 'import':
	{
		$m = 'maintenance';
		$blogid = (string)$core->blog->id;
		$type = (!empty($_POST['type'])) ? $_POST['type'] : 'blog';
				
		// read the content of file
		$onlyblog=($type=='blog') ? true : false;
		
		if ($onlyblog)
			$filename=$core->blog->public_path.'/'.$blogid.'-'.newsletterPlugin::pname().'.dat';
		else 
			$filename=$core->blog->public_path.'/'.newsletterPlugin::pname().'.dat';
	
		$retour = newsletterAdmin::importFromBackupFile($filename);
		if($retour) {
			$msg = __('Datas imported from').' '.$filename.' : '.$retour;
		}
		//newsletterTools::redirection($m,$msg);
	}
	break;

	// importing email addresses from a file
	case 'reprise':
	{
		$m = 'maintenance';

		if (empty($_POST['your_pwd']) || !$core->auth->checkPassword(crypt::hmac(DC_MASTER_KEY,$_POST['your_pwd']))) {
			throw new Exception(__('Password verification failed'));
		}
		$retour = newsletterAdmin::importFromTextFile($_FILES['file_reprise']);
		if($retour) {
			$msg = __('Datas imported from').' '.$_FILES['file_reprise']['name'].' : '.$retour;
		}
		
		newsletterTools::redirection($m,$msg);
	}
	break;

	// adaptation of the template
	case 'adapt_theme':
	{
		$m = 'maintenance';
		
		if (!empty($_POST['fthemes'])) {
			if (newsletterAdmin::adaptTheme($_POST['fthemes'])) {
				$msg = __('Template successfully adapted.');
			} else {
				throw new Exception(__('Error to adapt template'));
			}
		} else {
			$msg = __('No template adapted.');
		}
		
		newsletterTools::redirection($m,$msg);
	}
	break;
	
	case 'erasingnewsletter':	
	{
		$m = 'maintenance';

		// delete scheduled task
		if (isset($core->blog->dcCron)) {
			$newsletter_cron=new newsletterCron($core);	
			$newsletter_settings = new newsletterSettings($core);
			$newsletter_settings->setCheckSchedule(false);
			
			$newsletter_cron->del();

			// notification of changes to blog
			$newsletter_settings->save();
		}

		newsletterAdmin::uninstall();
		
		$redir = 'plugin.php?p=aboutConfig';
		http::redirect($redir);			
	}
	break;	

	case 'none':
	default:
		break;
} // end switch

/* ---------------------
 * action on the letters
 * ---------------------
*/
switch ($action)
{
	case 'publish':
	case 'unpublish':
	case 'pending':
	case 'delete':
	{
		$letters_id = array();
		if(!empty($_POST['letters_id'])) $letters_id = $_POST['letters_id'];
		newsletterLettersList::lettersActions($letters_id);
	}
	break;

	case 'send_old':
	{
		$subscribers_id = array();
		$letters_id = array();
		$newsletter_mailing = new newsletterMailing($core);
		$newsletter_settings = new newsletterSettings($core);
		$letters_id[] = newsletterCore::insertMessageNewsletter($newsletter_mailing,$newsletter_settings);
		
		if($letters_id[0]==0) {
			$t_msg='';
			$t_msg.=date('Y-m-j H:i',time() + dt::getTimeOffset($system_settings->blog_timezone)).': ';
			$t_msg.=__('not enough posts for sending');
			throw new Exception($t_msg);
		}

		if(!empty($_POST['subscribers_id'])) {
			$subscribers_id = $_POST['subscribers_id'];
		}
	}
	break;
	
	case 'send':
	case 'author':
	{
		$subscribers_id = array();
		if(!empty($_POST['letters_id'])) 
			$letters_id = $_POST['letters_id'];
		else 
			throw new Exception(__('no letter selected'));
		
		if(!empty($_POST['subscribers_id'])) {
			$subscribers_id = $_POST['subscribers_id'];
		}
	}
	break;
	
	case 'associate':
	case 'unlink':
	{
		$newsletterLetter = new newsletterLetter($core);
		$newsletterLetter->letterActions();
	}
	break;
	
	case 'none':
	default:
		break;
} // end switch


} catch (Exception $e) {
	if(isset($core->blog->dcNewsletter)) {
		$core->blog->dcNewsletter->addError($e->getMessage());
		$core->blog->dcNewsletter->save();
		newsletterTools::redirection($m,$msg);
	}
}


if(isset($core->blog->dcNewsletter)) {
	# Display errors
	foreach ($core->blog->dcNewsletter->getErrors() as $k => $v) {
		$core->error->add($v);
		$core->blog->dcNewsletter->delError($k);
	}

	# Get messages	
	foreach ($core->blog->dcNewsletter->getMessages() as $k => $v) {
		$msg .= $v.'<br />';
		$core->blog->dcNewsletter->delMessage($k);
	}
		
	# Save le traitement des messages
	$core->blog->dcNewsletter->save();
}

/* -------------------------------
 * define heading of the html page
 * -------------------------------
*/
echo
	'<html>'.
	'<head>'.
	'<title>'.$plugin_name.'</title>'.
	'<link rel="stylesheet" type="text/css" href="index.php?pf=newsletter/style.css" />';

	if ($plugin_tab == 'tab_planning') {
		if (isset($core->blog->dcCron)) {
			echo 
				dcPage::jsDatePicker().
				dcPage::jsLoad('index.php?pf=newsletter/js/_newsletter.cron.js').
				dcPage::jsLoad('index.php?pf=newsletter/js/_newsletter.js');
		}
		echo dcPage::jsPageTabs($plugin_tab);
	
	} else if (($plugin_tab == 'tab_letters' || $plugin_tab == 'tab_subscribers') 
				&& ($action == 'send' || $action == 'send_old')) {
		echo 
			dcPage::jsLoad('index.php?pf=newsletter/js/_sequential_ajax.js').
			dcPage::jsLoad('index.php?pf=newsletter/js/_letters_actions.js');
		
		echo 
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			"var letters = [".implode(',',$letters_id)."];\n".
			"var subscribers = [".implode(',',$subscribers_id)."];\n".
			"dotclear.msg.search_subscribers_for_letter = '".html::escapeJS(__('Search subscribers for letter'))."';\n".
			"dotclear.msg.subject = '".html::escapeJS(__('Subject'))."';\n".
			"dotclear.msg.to_user = '".html::escapeJS(__('to user'))."';\n".
			"dotclear.msg.please_wait = '".html::escapeJS(__('Waiting...'))."';\n".
			"dotclear.msg.subscribers_found = '".html::escapeJS(__('%s subscribers found'))."';\n".
			"dotclear.msg.confirm_delete_subscribers = '".html::escapeJS(__('Are you sure you want to delete selected subscribers?'))."';\n".
			"\n//]]>\n".
			"</script>\n";
		
		echo dcPage::jsPageTabs($plugin_tab);

	} else if ($plugin_tab == 'tab_subscribers' || $plugin_tab == 'tab_letters') {
		echo 
			dcPage::jsLoad('index.php?pf=newsletter/js/_newsletter.js').
			dcPage::jsLoad('js/filter-controls.js');
		echo 
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			"dotclear.msg.confirm_delete_letters = '".html::escapeJS(__('Are you sure you want to delete selected letters?'))."';\n".
			"dotclear.msg.confirm_delete_subscribers = '".html::escapeJS(__('Are you sure you want to delete selected subscribers?'))."';\n".
			"\n//]]>\n".
			"</script>\n";
			echo dcPage::jsPageTabs($plugin_tab);

	} else if ($plugin_tab == 'tab_letter') {
		echo dcPage::jsDatePicker().
			dcPage::jsToolBar().
			dcPage::jsModal().
			dcPage::jsLoad('js/_post.js');
			//dcPage::jsConfirmClose('entry-form','comment-form').
			dcPage::jsConfirmClose('entry-form');
			# --BEHAVIOR-- adminPageHeaders
		 	$core->callBehavior('adminLetterHeaders');

		 // Bidouille pour que l'affichage des outils JS de Dotclear se fasse correctement
		 echo dcPage::jsPageTabs('edit-entry');
	} else if ($plugin_tab == 'tab_letter_associate') {
		echo 
			dcPage::jsLoad('index.php?pf=newsletter/js/_newsletter.js').
			dcPage::jsLoad('js/filter-controls.js');
		echo dcPage::jsPageTabs('tab_letter');
	} else {
		echo dcPage::jsLoad('index.php?pf=newsletter/js/_newsletter.js');
		echo 
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			"dotclear.msg.confirm_erasing_datas = '".html::escapeJS(__('Are you sure you want to delete all informations about newsletter in database?'))."';\n".
			"dotclear.msg.confirm_import_backup = '".html::escapeJS(__('Are you sure you want to import a backup file?'))."';\n".
			"\n//]]>\n".
			"</script>\n";
		echo dcPage::jsPageTabs($plugin_tab);
	}

echo	'</head>'.
	'<body>';

/* -------------------------------
 * define content of the html page
 * -------------------------------
*/
echo '<h2>'.
	html::escapeHTML($core->blog->name).' &gt; <a href="'.newsletterPlugin::admin().'" title="'.$plugin_name.'">'.$plugin_name.' '.newsletterPlugin::dcVersion().'</a>'.
	'</h2>';

// print information message
if (!empty($msg)) {
	echo '<p class="message">'.$msg.'</p>';
}

if ( $newsletter_flag == 0) {
	tabsNewsletter::displayTabMaintenance();
} else {
	// define the presentation of tabs
	switch ($plugin_tab) {
		case 'tab_subscribers':
		{
			echo '<div class="multi-part" id="tab_subscribers" title="'.__('Subscribers').'">';
			
			if($plugin_op == 'send') {
				newsletterSubscribersList::subcribersActions();
			} else {
				newsletterSubscribersList::tabSubscribersList();
			}
			echo '</div>';	
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
		
		case 'tab_addedit':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<div class="multi-part" id="tab_addedit" title="'.$edit_subscriber.'">';
			tabsNewsletter::AddEdit();
			echo '</div>';	
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
		
		case 'tab_letters':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<div class="multi-part" id="tab_letters" title="'.__('Letters').'">';
			if($action == 'author' || $action == 'send' || $action == 'send_old') {
				newsletterLettersList::lettersActions($letters_id);
			} else {
				newsletterLettersList::displayTabLettersList();
			}
			echo '</div>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
			
		case 'tab_letter':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			//echo '<div class="multi-part" id="tab_letter" title="'.__('Letter').'">';
			echo '<div class="multi-part" id="edit-entry" title="'.__('Letter').'">';
			$nltr = new newsletterLetter($core);
			$nltr->displayTabLetter();
			echo '</div>';			
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
	
		case 'tab_letter_associate':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<div class="multi-part" id="tab_letter" title="'.__('Letter').'">';
			$nltr = new newsletterLetter($core);
			$nltr->displayTabLetterAssociate();
			echo '</div>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
	
	
		case 'tab_settings':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';
			echo '<div class="multi-part" id="tab_settings" title="'.__('Settings').'">';	
			tabsNewsletter::displayTabSettings();
			echo '</div>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
	
		case 'tab_messages':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<div class="multi-part" id="tab_messages" title="'.__('Messages').'">';
			tabsNewsletter::displayTabMessages();
			echo '</div>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
	
		case 'tab_editCSS':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<div class="multi-part" id="tab_editCSS" title="'.__('CSS for letters').'">';
			tabsNewsletter::displayTabEditCSS();
			echo '</div>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
			
		case 'tab_planning':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<div class="multi-part" id="tab_planning" title="'.__('Planning').'">';
			tabsNewsletter::displayTabPlanning();
			echo '</div>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
	
		case 'tab_maintenance':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';		
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<div class="multi-part" id="tab_maintenance" title="'.__('Maintenance').'">';
			tabsNewsletter::displayTabMaintenance();
			echo '</div>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=resume" class="multi-part">'.__('Resume').'</a></p>';
		}
		break;
		
		case 'tab_resume':
		{
			echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=letters" class="multi-part">'.__('Letters').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=editCSS" class="multi-part">'.__('CSS for letters').'</a></p>';		
			echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
			echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
			echo '<div class="multi-part" id="tab_resume" title="'.__('Resume').'">';
			tabsNewsletter::displayTabResume();
			echo '</div>';
		}
		break;		
	
		default:
		break;	
	} // end switch

}
echo dcPage::helpBlock('newsletter');

echo '</body>';
echo '</html>';

?>