<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) exit;
dcPage::check('usage,admin');

// chargement des librairies
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';
require_once dirname(__FILE__).'/inc/class.newsletter.admin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.cron.php';
require_once dirname(__FILE__).'/inc/class.newsletter.tools.php';

// paramétrage des variables
$plugin_name = __('Newsletter');
$plugin_tab = 'tab_listblog';
$id = null;

// récupération de l'onglet (si disponible)
if (!empty($_POST['tab'])) 
	$plugin_tab = 'tab_'.(string)$_POST['tab'];
else if (!empty($_GET['tab'])) 
	$plugin_tab = 'tab_'.(string)$_GET['tab'];

// récupération de l'opération (si possible)
if (!empty($_POST['op'])) 
	$plugin_op = (string)$_POST['op'];
else 
	$plugin_op = 'none';

// Setting default parameters if missing configuration
if (!newsletterCore::isInstalled()) {
	try {
		if (newsletterAdmin::Install()) {
			http::redirect(http::getSelfURI());
		} else {
			$core->error->add(__('Unable to install Newsletter.'));
			return false;
		}			
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// action en fonction de l'opération
switch ($plugin_op)
{
	// modification de l'état d'activation du plugin
	case 'state':
	{
		$plugin_tab = 'tab_maintenance';

		try {
			(!empty($_POST['active']) ? newsletterPlugin::Activate() : newsletterPlugin::Inactivate());

			// notification de modification au blog et redirection
			newsletterPlugin::Trigger();
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=maintenance&msg='.rawurldecode(__('Activation updated.')));
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// modification du paramétrage
	case 'settings':
	{
		$plugin_tab = 'tab_settings';

	    try
	    {
			if (empty($_POST['feditoremail'])) {
				//$msg = __('You must input a valid email !');
				$core->error->add(__('You must input a valid email !')); 
			} else {
				// nom de l'éditeur
				(!empty($_POST['feditorname']) ? newsletterPlugin::setEditorName($_POST['feditorname']) : newsletterPlugin::clearEditorName());

				// email de l'éditeur
				newsletterPlugin::setEditorEmail($_POST['feditoremail']);

				// --------- advanced settings -------------

				// captcha
				(!empty($_POST['fcaptcha']) ? newsletterPlugin::setCaptcha($_POST['fcaptcha']) : newsletterPlugin::setCaptcha());

				// mode d'envoi
				(!empty($_POST['fmode']) ? newsletterPlugin::setSendMode($_POST['fmode']) : newsletterPlugin::clearSendMode());

				// sélection du format d'envoi par utilisateur ou global
				(!empty($_POST['f_use_default_format']) ? newsletterPlugin::setUseDefaultFormat($_POST['f_use_default_format']) : newsletterPlugin::clearUseDefaultFormat());

				// envoi automatique
				(!empty($_POST['fautosend']) ? newsletterPlugin::setAutosend($_POST['fautosend']) : newsletterPlugin::clearAutosend());

				// nombre min. de billets
				(!empty($_POST['fminposts']) ? newsletterPlugin::setMinPosts($_POST['fminposts']) : newsletterPlugin::clearMinPosts());

				// nombre max. de billets
				(!empty($_POST['fmaxposts']) ? newsletterPlugin::setMaxPosts($_POST['fmaxposts']) : newsletterPlugin::clearMaxPosts());
				
				// affichage du contenu du post
				(!empty($_POST['f_view_content_post']) ? newsletterPlugin::setViewContentPost($_POST['f_view_content_post']) : newsletterPlugin::clearViewContentPost());

				// taille maximale du contenu du post
				(!empty($_POST['f_size_content_post']) ? newsletterPlugin::setSizeContentPost($_POST['f_size_content_post']) : newsletterPlugin::clearSizeContentPost());

				// filtre de catégorie
				(!empty($_POST['f_category']) ? newsletterPlugin::setCategory($_POST['f_category']) : newsletterPlugin::clearCategory());

				// envoi automatique
				(!empty($_POST['f_check_notification']) ? newsletterPlugin::setCheckNotification($_POST['f_check_notification']) : newsletterPlugin::clearCheckNotification());

				// option suspend
				(!empty($_POST['f_check_use_suspend']) ? newsletterPlugin::setCheckUseSuspend($_POST['f_check_use_suspend']) : newsletterPlugin::setCheckUseSuspend(false));
			
				// notification de modification au blog et redirection
				newsletterPlugin::Trigger();
				newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=settings&msg='.rawurldecode(__('Settings updated.')));
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// modification des messages
	case 'messages':
	{
		$plugin_tab = 'tab_messages';
		try {
			// newsletter
			(!empty($_POST['f_introductory_msg']) ? newsletterPlugin::setIntroductoryMsg($_POST['f_introductory_msg']) : newsletterPlugin::clearIntroductoryMsg());
			(!empty($_POST['f_concluding_msg']) ? newsletterPlugin::setConcludingMsg($_POST['f_concluding_msg']) : newsletterPlugin::clearConcludingMsg());
			(!empty($_POST['f_presentation_msg']) ? newsletterPlugin::setPresentationMsg($_POST['f_presentation_msg']) : newsletterPlugin::clearPresentationMsg());
			(!empty($_POST['f_presentation_posts_msg']) ? newsletterPlugin::setPresentationPostsMsg($_POST['f_presentation_posts_msg']) : newsletterPlugin::clearPresentationPostsMsg());
			(!empty($_POST['f_newsletter_subject']) ? newsletterPlugin::setNewsletterSubject($_POST['f_newsletter_subject']) : newsletterPlugin::clearNewsletterSubject());

			// confirm
			(!empty($_POST['f_txt_intro_confirm']) ? newsletterPlugin::setTxtIntroConfirm($_POST['f_txt_intro_confirm']) : newsletterPlugin::clearTxtIntroConfirm());
			(!empty($_POST['f_txtConfirm']) ? newsletterPlugin::setTxtConfirm($_POST['f_txtConfirm']) : newsletterPlugin::clearTxtConfirm());
			(!empty($_POST['f_confirm_subject']) ? newsletterPlugin::setConfirmSubject($_POST['f_confirm_subject']) : newsletterPlugin::clearConfirmSubject());
			(!empty($_POST['f_confirm_msg']) ? newsletterPlugin::setConfirmMsg($_POST['f_confirm_msg']) : newsletterPlugin::clearConfirmMsg());
			(!empty($_POST['f_concluding_confirm_msg']) ? newsletterPlugin::setConcludingConfirmMsg($_POST['f_concluding_confirm_msg']) : newsletterPlugin::clearConcludingConfirmMsg());

			// disable
			(!empty($_POST['f_txt_intro_disable']) ? newsletterPlugin::setTxtIntroDisable($_POST['f_txt_intro_disable']) : newsletterPlugin::clearTxtIntroDisable());
			(!empty($_POST['f_txtDisable']) ? newsletterPlugin::setTxtDisable($_POST['f_txtDisable']) : newsletterPlugin::clearTxtDisable());
			(!empty($_POST['f_disable_subject']) ? newsletterPlugin::setDisableSubject($_POST['f_disable_subject']) : newsletterPlugin::clearDisableSubject());
			(!empty($_POST['f_disable_msg']) ? newsletterPlugin::setDisableMsg($_POST['f_disable_msg']) : newsletterPlugin::clearDisableMsg());
			(!empty($_POST['f_concluding_disable_msg']) ? newsletterPlugin::setConcludingDisableMsg($_POST['f_concluding_disable_msg']) : newsletterPlugin::clearConcludingDisableMsg());
			(!empty($_POST['f_txt_disabled_msg']) ? newsletterPlugin::setTxtDisabledMsg($_POST['f_txt_disabled_msg']) : newsletterPlugin::clearTxtDisabledMsg());

			// enable
			(!empty($_POST['f_txt_intro_enable']) ?	newsletterPlugin::setTxtIntroEnable($_POST['f_txt_intro_enable']) : newsletterPlugin::clearTxtIntroEnable());
			(!empty($_POST['f_txtEnable']) ? newsletterPlugin::setTxtEnable($_POST['f_txtEnable']) : newsletterPlugin::clearTxtEnable());
			(!empty($_POST['f_enable_subject']) ? newsletterPlugin::setEnableSubject($_POST['f_enable_subject']) : newsletterPlugin::clearEnableSubject());
			(!empty($_POST['f_enable_msg']) ? newsletterPlugin::setEnableMsg($_POST['f_enable_msg']) : newsletterPlugin::clearEnableMsg());
			(!empty($_POST['f_concluding_enable_msg']) ? newsletterPlugin::setConcludingEnableMsg($_POST['f_concluding_enable_msg']) : newsletterPlugin::clearConcludingEnableMsg());
			(!empty($_POST['f_txt_enabled_msg']) ? newsletterPlugin::setTxtEnabledMsg($_POST['f_txt_enabled_msg']) : newsletterPlugin::clearTxtEnabledMsg());
			
			// suspend
			(!empty($_POST['f_txt_intro_suspend']) ? newsletterPlugin::setTxtIntroSuspend($_POST['f_txt_intro_suspend']) : newsletterPlugin::clearTxtIntroSuspend());
			(!empty($_POST['f_txtSuspend']) ? newsletterPlugin::setTxtSuspend($_POST['f_txtSuspend']) : newsletterPlugin::clearTxtSuspend());
			(!empty($_POST['f_suspend_subject']) ? newsletterPlugin::setSuspendSubject($_POST['f_suspend_subject']) : newsletterPlugin::clearSuspendSubject());
			(!empty($_POST['f_suspend_msg']) ? newsletterPlugin::setSuspendMsg($_POST['f_suspend_msg']) : newsletterPlugin::clearSuspendMsg());
			(!empty($_POST['f_concluding_suspend_msg']) ? newsletterPlugin::setConcludingSuspendMsg($_POST['f_concluding_suspend_msg']) : newsletterPlugin::clearConcludingSuspendMsg());
			(!empty($_POST['f_txt_suspended_msg']) ? newsletterPlugin::setTxtSuspendedMsg($_POST['f_txt_suspended_msg']) : newsletterPlugin::clearTxtSuspendedMsg());
			
			// changemode
			(!empty($_POST['f_change_mode_subject']) ? newsletterPlugin::setChangeModeSubject($_POST['f_change_mode_subject']) : newsletterPlugin::clearChangeModeSubject());
			(!empty($_POST['f_header_changemode_msg']) ? newsletterPlugin::setHeaderChangeModeMsg($_POST['f_header_changemode_msg']) : newsletterPlugin::clearHeaderChangeModeMsg());
			(!empty($_POST['f_footer_changemode_msg']) ? newsletterPlugin::setFooterChangeModeMsg($_POST['f_footer_changemode_msg']) : newsletterPlugin::clearFooterChangeModeMsg());
			(!empty($_POST['f_changemode_msg']) ? newsletterPlugin::setChangeModeMsg($_POST['f_changemode_msg']) : newsletterPlugin::clearChangeModeMsg());
			
			// resume
			(!empty($_POST['f_resume_subject']) ? newsletterPlugin::setResumeSubject($_POST['f_resume_subject']) : newsletterPlugin::clearResumeSubject());
			(!empty($_POST['f_header_resume_msg']) ? newsletterPlugin::setHeaderResumeMsg($_POST['f_header_resume_msg']) : newsletterPlugin::clearHeaderResumeMsg());
			(!empty($_POST['f_footer_resume_msg']) ? newsletterPlugin::setFooterResumeMsg($_POST['f_footer_resume_msg']) : newsletterPlugin::clearFooterResumeMsg());
				
			// subscribe
			(!empty($_POST['f_msg_presentation_form']) ? newsletterPlugin::setMsgPresentationForm($_POST['f_msg_presentation_form']) : newsletterPlugin::clearMsgPresentationForm());
			(!empty($_POST['f_form_title_page']) ? newsletterPlugin::setFormTitlePage($_POST['f_form_title_page']) : newsletterPlugin::clearFormTitlePage());
			(!empty($_POST['f_txt_subscribed_msg']) ? newsletterPlugin::setTxtSubscribedMsg($_POST['f_txt_subscribed_msg']) : newsletterPlugin::clearTxtSubscribedMsg());

			// notification de modification au blog et redirection
			newsletterPlugin::Trigger();
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=messages&msg='.rawurldecode(__('Messages updated.')));
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	// mise en place de la planification
	case 'planning':
	{
		$plugin_tab = 'tab_planning';
		try {
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=planning&msg='.rawurldecode(__('Planning updated.')));
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	case 'schedule':
	{
		$plugin_tab = 'tab_planning';

		try {

			if (isset($core->blog->dcCron)) {
				$newsletter_cron=new newsletterCron($core);	
				
				// ajout de la tache planifiée
				$interval = (($_POST['f_interval']) ? $_POST['f_interval'] : 604800);
				$f_first_run = (($_POST['f_first_run']) ? strtotime(html::escapeHTML($_POST['f_first_run'])) : time() + dt::getTimeOffset($core->blog->settings->blog_timezone));
				if ($newsletter_cron->add($interval, $f_first_run)) {
					$msg = __('Planning updated.');
					
					newsletterPlugin::setCheckSchedule(true);
					
					// notification de modification au blog
					newsletterPlugin::Trigger();
				
				} else {
					$msg = __('Error during create planning task.');
				}

			}
			// redirection
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=planning&msg='.rawurldecode($msg));

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	case 'unschedule':
	{
		$plugin_tab = 'tab_planning';

		try {

			if (isset($core->blog->dcCron)) {
				$newsletter_cron=new newsletterCron($core);	
				
				newsletterPlugin::setCheckSchedule(false);
				
				// suppression de la tache planifiée
				$newsletter_cron->del();

				// notification de modification au blog
				newsletterPlugin::Trigger();
			}			
			// redirection
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=planning&msg='.rawurldecode(__('Planning updated.')));

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// mise en place de la planification
	case 'enabletask':
	{
		$plugin_tab = 'tab_planning';

		try {

			if (isset($core->blog->dcCron)) {
				$newsletter_cron=new newsletterCron($core);	
				
				// activation de la tache planifiée
				$newsletter_cron->enable();

				// notification de modification au blog
				newsletterPlugin::Trigger();
			}
			// redirection
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=planning&msg='.rawurldecode(__('Planning updated.')));

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;


	// mise en place de la planification
	case 'disabletask':
	{
		$plugin_tab = 'tab_planning';

		try {

			if (isset($core->blog->dcCron)) {
				$newsletter_cron=new newsletterCron($core);	
				
				// désactivation de la tache planifiée
				$newsletter_cron->disable();

				// notification de modification au blog
				newsletterPlugin::Trigger();
			}
			// redirection
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=planning&msg='.rawurldecode(__('Planning updated.')));

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;


	// vérification de mise à jour
	/*	
 	case 'update':
	{
		$plugin_tab = 'tab_settings';

	    try
	    {
			$msg = newsletterPlugin::htmlNewVersion(true);
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;
	//*/

	// ajout d'un abonné
	case 'add':
	{
		$plugin_tab = 'tab_listblog';

		try {
			if (!empty($_POST['femail'])) 
				$email = $_POST['femail'];
			else 
				$email = null;

			if ($email == null) {
				$msg = __('Missing informations.');
			} else {
				if (!newsletterCore::add($email)) 
					$msg = __('Error adding subscriber.');
				else 
					$msg = __('Subscriber added.');
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// mise à jour d'un abonné
	case 'edit':
	{
		$plugin_tab = 'tab_listblog';

		try {
			if (!empty($_POST['id'])) 
				$id = $_POST['id'];
			else 
				$id = null;

			if (!empty($_POST['femail'])) 
				$email = $_POST['femail'];
			else 
				$email = null;

			if (!empty($_POST['fsubscribed'])) 
				$subscribed = $_POST['fsubscribed'];
			else 
				$subscribed = null;

			if (!empty($_POST['flastsent'])) 
				$lastsent = $_POST['flastsent'];
			else 
				$lastsent = null;

			if (!empty($_POST['fmodesend'])) 
				$modesend = $_POST['fmodesend'];
			else 
				$modesend = null;

			if (!empty($_POST['fstate'])) 
				$state = $_POST['fstate'];
			else 
				$state = null;

			if ($email == null) {
				if ($id == null) 
					$msg = __('Missing informations.');
				else 
					$plugin_tab = 'tab_addedit';
			} else {
				$regcode = null;
			
				if (!newsletterCore::update($id, $email, $state, $regcode, $subscribed, $lastsent, $modesend)) 
					$msg = __('Error updating subscriber.');
				else 
					$msg = __('Subscriber updated.');
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// suppression d'un ou plusieurs abonnés
	case 'remove':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$msg = __('No account removed.');
			if (is_array($_POST['subscriber'])) {
				$ids = array();
				foreach (array_keys($_POST['subscriber']) as $id) 
				{ 
					$ids[] = $id; 
				}
				if (newsletterCore::delete($ids)) 
					$msg = __('Account(s) successfully removed.');
			}	
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// suspension des comptes d'un ou plusieurs abonnés
	case 'suspend':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$msg = __('No account suspended.');
			if (is_array($_POST['subscriber'])) {
				$ids = array();
				foreach (array_keys($_POST['subscriber']) as $id) 
				{ 
					$ids[] = $id; 
				}
				if (newsletterCore::suspend($ids)) 
					$msg = __('Account(s) successfully suspended.');
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// activation des comptes d'un ou plusieurs abonnés
	case 'enable':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$msg = __('No account enabled.');
			if (is_array($_POST['subscriber'])) {
				$ids = array();
				foreach (array_keys($_POST['subscriber']) as $id) 
				{ 
					$ids[] = $id; 
				}
				if (newsletterCore::enable($ids)) 
					$msg = __('Account(s) successfully enabled.');
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// désactivation des comptes d'un ou plusieurs abonnés
	case 'disable':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$msg = __('No account disabled.');
			if (is_array($_POST['subscriber'])) {
				$ids = array();
				foreach (array_keys($_POST['subscriber']) as $id) 
				{ 
					$ids[] = $id; 
				}
				if (newsletterCore::disable($ids)) 
					$msg = __('Account(s) successfully disabled.');
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// envoi de la newsletter d'un ou plusieurs abonnés
	case 'send':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$ids = array();
			foreach (array_keys($_POST['subscriber']) as $id) 
			{ 
				$ids[] = $id; 
			}
			$msg = newsletterCore::send($ids,'newsletter');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage());
		}
	}
	break;

	case 'sending':
	{

	}
	break;

	// réinitialisation de l'information 'dernier envoi'
	case 'lastsent':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
			$msg = __('No account changed.');
			if (is_array($_POST['subscriber'])) {
				$ids = array();
				foreach (array_keys($_POST['subscriber']) as $id) { $ids[] = $id; }
				{
					if (newsletterCore::lastsent($ids, 'clear')) 
						$msg = __('Account(s) successfully changed.');
				}
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;
	
	// export des données
	case 'export':
	{
		$plugin_tab = 'tab_maintenance';

		try {
			if (!empty($_POST['type'])) 
				$type = $_POST['type'];
			else 
				$type = 'blog';
		
			newsletterAdmin::Export( ($type=='blog') ? true : false );
			$msg = __('Datas exported.');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// import des données
	case 'import':
	{
		$plugin_tab = 'tab_maintenance';

		try
		{
			if (!empty($_POST['type'])) 
				$type = $_POST['type'];
			else 
				$type = 'blog';
		
			newsletterAdmin::Import( ($type=='blog') ? true : false );
			$msg = __('Datas imported.');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// import email addresses from a file
	case 'reprise':
	{
		$plugin_tab = 'tab_maintenance';

		try
		{
			if (empty($_POST['your_pwd']) || !$core->auth->checkPassword(crypt::hmac(DC_MASTER_KEY,$_POST['your_pwd']))) {
				throw new Exception(__('Password verification failed'));
			}
			
			$retour = newsletterAdmin::importFromTextFile($_FILES['file_reprise']);
			if($retour) {
				$msg = __('Datas imported from ').$_FILES['file_reprise']['name'].' : '.$retour;
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// envoi du mail de confirmation d'inscription
	case 'sendconfirm':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$ids = array();
			foreach (array_keys($_POST['subscriber']) as $id) 
			{ 
				$ids[] = $id; 
			}
			$msg = newsletterCore::send($ids,'confirm');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;
	
	// envoi du mail de notification de suspension de compte
	case 'sendsuspend':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$ids = array();
			foreach (array_keys($_POST['subscriber']) as $id) 
			{ 
				$ids[] = $id; 
			}
			$msg = newsletterCore::send($ids,'suspend');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;
	
	// envoi du mail de notification de désactivation de compte
	case 'senddisable':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$ids = array();
			foreach (array_keys($_POST['subscriber']) as $id) 
			{ 
				$ids[] = $id; 
			}
			$msg = newsletterCore::send($ids,'disable');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;
	
	// envoi du mail de notification de validation de compte
	case 'sendenable':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$ids = array();
			foreach (array_keys($_POST['subscriber']) as $id) 
			{ 
				$ids[] = $id; 
			}
			$msg = newsletterCore::send($ids,'enable');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// changement du format d'envoi en html pour un ou plusieurs abonnés
	case 'changemodehtml':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$msg = __('No account(s) updated.');
			if (is_array($_POST['subscriber'])) {
				$ids = array();
				foreach (array_keys($_POST['subscriber']) as $id) 
				{ 
					$ids[] = $id; 
				}
				if (newsletterCore::changemodehtml($ids)) 
					$msg = __('Format sending for account(s) successfully updated to html.');
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// changement du format d'envoi en html pour un ou plusieurs abonnés
	case 'changemodetext':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$msg = __('No account(s) updated.');
			if (is_array($_POST['subscriber'])) {
				$ids = array();
				foreach (array_keys($_POST['subscriber']) as $id) 
				{ 
					$ids[] = $id; 
				}
				if (newsletterCore::changemodetext($ids)) 
					$msg = __('Format sending for account(s) successfully updated to text.');
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// adaptation du template au thème
	case 'adapt':
	{
		$plugin_tab = 'tab_maintenance';

		try {
			$msg = __('No template adapted.');
			if (!empty($_POST['fthemes'])) {
				if (newsletterAdmin::Adapt($_POST['fthemes'])) 
					$msg = __('Template successfully adapted.');
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;
	
	// suppression des informations de newsletter en base
	case 'erasingnewsletter':	
	{
		$plugin_tab = 'tab_maintenance';
		try {
			newsletterAdmin::Uninstall();
			$msg = __('Erasing complete.');
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;	
    
	case '-':
	{
		$plugin_tab = 'tab_listblog';
	}
	break;
	    
	case 'none':
	default:
		break;
}

// message à  afficher (en cas de redirection)
if (!empty($_GET['msg'])) $msg = (string) rawurldecode($_GET['msg']);
?>
<html>
<head>
	<title><?php echo $plugin_name ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo newsletterPlugin::urldatas() ?>/style.css" />
	
	<?php if (newsletterPlugin::isActive()) {
		echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=newsletter/js/_newsletter.js');
		if (isset($core->blog->dcCron)) {
			echo dcPage::jsDatePicker();				
			echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=newsletter/js/_newsletter.cron.js'); 
		}
	}
	?>
	<script type="text/javascript">
	//<![CDATA[
	<?php echo dcPage::jsVar('dotclear.msg.confirm_erasing_task',__('Are you sure you want to delete all informations about newsletter in database ?')); ?>
	//]]>
	</script>	
	
	<?php echo dcPage::jsPageTabs($plugin_tab); ?>	
</head>
<body>
<?php echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; <a href="'.newsletterPlugin::admin().'" title="'.$plugin_name.'">'.$plugin_name.' '.newsletterPlugin::dcVersion().'</a></h2>' ?>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>' ?>

	<div class="multi-part" id="tab_listblog" title="<?php echo __('Subscribers'); ?>">
		<?php tabsNewsletter::ListBlog() ?>
	</div>
	<div class="multi-part" id="tab_addedit" title="<?php echo (!empty($_POST['id'])) ? __('Edit') : __('Add'); ?>">
		<?php tabsNewsletter::AddEdit() ?>
	</div>
	<div class="multi-part" id="tab_settings" title="<?php echo __('Settings'); ?>">
		<?php tabsNewsletter::Settings() ?>
	</div>
	<div class="multi-part" id="tab_messages" title="<?php echo __('Messages'); ?>">
		<?php tabsNewsletter::Messages() ?>
	</div>
	<div class="multi-part" id="tab_planning" title="<?php echo __('Planning'); ?>">
		<?php tabsNewsletter::Planning() ?>
	</div>
	<div class="multi-part" id="tab_maintenance" title="<?php echo __('Maintenance'); ?>">
		<?php tabsNewsletter::Maintenance() ?>
	</div>
	
<?php dcPage::helpBlock('newsletter');?>
	
</body>
</html>
