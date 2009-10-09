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
require_once dirname(__FILE__).'/inc/class.newsletter.settings.php';
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';
require_once dirname(__FILE__).'/inc/class.newsletter.admin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.cron.php';
require_once dirname(__FILE__).'/inc/class.newsletter.tools.php';

// paramétrage des variables
$plugin_name	= __('Newsletter');
$id 			= null;
$p_url		= 'plugin.php?p=newsletter';

try {

if (!empty($_REQUEST['m'])) {
	$m=(string) rawurldecode($_REQUEST['m']);
} else {
	$m='subscribers';
}
$plugin_tab = 'tab_'.$m;


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

// message àafficher (en cas de redirection)
if (!empty($_GET['msg'])) 
	$msg = (string) rawurldecode($_GET['msg']);
else if (!empty($_POST['msg'])) 
	$msg = (string) rawurldecode($_POST['msg']);

// action en fonction de l'opération
switch ($plugin_op)
{
	// modification de l'état d'activation du plugin
	case 'state':
	{
		$m = 'maintenance';

		(!empty($_POST['active']) ? newsletterPlugin::activate() : newsletterPlugin::inactivate());

		// notification de modification au blog et redirection
		newsletterPlugin::triggerBlog();

		$msg = __('Activation updated.');
		newsletterTools::redirection($m,$msg);
	}
	break;

	// modification du paramétrage
	case 'settings':
	{
		$m='settings';

		if (!empty($_POST['feditoremail']) && !empty($_POST['feditorname'])) {
			$newsletter_settings = new newsletterSettings($core);
			// nom de l'éditeur
			$newsletter_settings->setEditorName($_POST['feditorname']);

			// email de l'éditeur
			$newsletter_settings->setEditorEmail($_POST['feditoremail']);

			// --------- advanced settings -------------

			// captcha
			(!empty($_POST['fcaptcha']) ? $newsletter_settings->setCaptcha($_POST['fcaptcha']) : $newsletter_settings->clearCaptcha());

			// mode d'envoi
			(!empty($_POST['fmode']) ? $newsletter_settings->setSendMode($_POST['fmode']) : $newsletter_settings->clearSendMode());

			// sélection du format d'envoi par utilisateur ou global
			(!empty($_POST['f_use_default_format']) ? $newsletter_settings->setUseDefaultFormat($_POST['f_use_default_format']) : $newsletter_settings->clearUseDefaultFormat());

			// envoi automatique
			(!empty($_POST['fautosend']) ? $newsletter_settings->setAutosend($_POST['fautosend']) : $newsletter_settings->clearAutosend());

			// nombre min. de billets
			(!empty($_POST['fminposts']) ? $newsletter_settings->setMinPosts($_POST['fminposts']) : $newsletter_settings->clearMinPosts());

			// nombre max. de billets
			(!empty($_POST['fmaxposts']) ? $newsletter_settings->setMaxPosts($_POST['fmaxposts']) : $newsletter_settings->clearMaxPosts());
				
			// affichage du contenu du post
			(!empty($_POST['f_view_content_post']) ? $newsletter_settings->setViewContentPost($_POST['f_view_content_post']) : $newsletter_settings->clearViewContentPost());

			// taille maximale du contenu du post
			(!empty($_POST['f_size_content_post']) ? $newsletter_settings->setSizeContentPost($_POST['f_size_content_post']) : $newsletter_settings->clearSizeContentPost());

			// filtre de catégorie
			(!empty($_POST['f_category']) ? $newsletter_settings->setCategory($_POST['f_category']) : $newsletter_settings->clearCategory());

			// option sur les sous catégories
			(!empty($_POST['f_check_subcategories']) ? $newsletter_settings->setCheckSubCategories($_POST['f_check_subcategories']) : $newsletter_settings->clearCheckSubCategories());

			// envoi automatique
			(!empty($_POST['f_check_notification']) ? $newsletter_settings->setCheckNotification($_POST['f_check_notification']) : $newsletter_settings->clearCheckNotification());

			// option suspend
			(!empty($_POST['f_check_use_suspend']) ? $newsletter_settings->setCheckUseSuspend($_POST['f_check_use_suspend']) : $newsletter_settings->clearCheckUseSuspend());

			// sélection de la date pour le tri des billets de la newsletter
			(!empty($_POST['f_order_date']) ? $newsletter_settings->setOrderDate($_POST['f_order_date']) : $newsletter_settings->clearOrderDate());
			
			// notification de modification au blog et redirection
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

	// modification des messages
	case 'messages':
	{
		$m='messages';
		$newsletter_settings = new newsletterSettings($core);
			
		// newsletter
		(!empty($_POST['f_introductory_msg']) ? $newsletter_settings->setIntroductoryMsg($_POST['f_introductory_msg']) : $newsletter_settings->clearIntroductoryMsg());
		(!empty($_POST['f_concluding_msg']) ? $newsletter_settings->setConcludingMsg($_POST['f_concluding_msg']) : $newsletter_settings->clearConcludingMsg());
		(!empty($_POST['f_presentation_msg']) ? $newsletter_settings->setPresentationMsg($_POST['f_presentation_msg']) : $newsletter_settings->clearPresentationMsg());
		(!empty($_POST['f_presentation_posts_msg']) ? $newsletter_settings->setPresentationPostsMsg($_POST['f_presentation_posts_msg']) : $newsletter_settings->clearPresentationPostsMsg());
		(!empty($_POST['f_newsletter_subject']) ? $newsletter_settings->setNewsletterSubject($_POST['f_newsletter_subject']) : $newsletter_settings->clearNewsletterSubject());

		// confirm
		(!empty($_POST['f_txt_intro_confirm']) ? $newsletter_settings->setTxtIntroConfirm($_POST['f_txt_intro_confirm']) : $newsletter_settings->clearTxtIntroConfirm());
		(!empty($_POST['f_txtConfirm']) ? $newsletter_settings->setTxtConfirm($_POST['f_txtConfirm']) : $newsletter_settings->clearTxtConfirm());
		(!empty($_POST['f_confirm_subject']) ? $newsletter_settings->setConfirmSubject($_POST['f_confirm_subject']) : $newsletter_settings->clearConfirmSubject());
		(!empty($_POST['f_confirm_msg']) ? $newsletter_settings->setConfirmMsg($_POST['f_confirm_msg']) : $newsletter_settings->clearConfirmMsg());
		(!empty($_POST['f_concluding_confirm_msg']) ? $newsletter_settings->setConcludingConfirmMsg($_POST['f_concluding_confirm_msg']) : $newsletter_settings->clearConcludingConfirmMsg());

		// disable
		(!empty($_POST['f_txt_intro_disable']) ? $newsletter_settings->setTxtIntroDisable($_POST['f_txt_intro_disable']) : $newsletter_settings->clearTxtIntroDisable());
		(!empty($_POST['f_txtDisable']) ? $newsletter_settings->setTxtDisable($_POST['f_txtDisable']) : $newsletter_settings->clearTxtDisable());
		(!empty($_POST['f_disable_subject']) ? $newsletter_settings->setDisableSubject($_POST['f_disable_subject']) : $newsletter_settings->clearDisableSubject());
		(!empty($_POST['f_disable_msg']) ? $newsletter_settings->setDisableMsg($_POST['f_disable_msg']) : $newsletter_settings->clearDisableMsg());
		(!empty($_POST['f_concluding_disable_msg']) ? $newsletter_settings->setConcludingDisableMsg($_POST['f_concluding_disable_msg']) : $newsletter_settings->clearConcludingDisableMsg());
		(!empty($_POST['f_txt_disabled_msg']) ? $newsletter_settings->setTxtDisabledMsg($_POST['f_txt_disabled_msg']) : $newsletter_settings->clearTxtDisabledMsg());

		// enable
		(!empty($_POST['f_txt_intro_enable']) ?	$newsletter_settings->setTxtIntroEnable($_POST['f_txt_intro_enable']) : $newsletter_settings->clearTxtIntroEnable());
		(!empty($_POST['f_txtEnable']) ? $newsletter_settings->setTxtEnable($_POST['f_txtEnable']) : $newsletter_settings->clearTxtEnable());
		(!empty($_POST['f_enable_subject']) ? $newsletter_settings->setEnableSubject($_POST['f_enable_subject']) : $newsletter_settings->clearEnableSubject());
		(!empty($_POST['f_enable_msg']) ? $newsletter_settings->setEnableMsg($_POST['f_enable_msg']) : $newsletter_settings->clearEnableMsg());
		(!empty($_POST['f_concluding_enable_msg']) ? $newsletter_settings->setConcludingEnableMsg($_POST['f_concluding_enable_msg']) : $newsletter_settings->clearConcludingEnableMsg());
		(!empty($_POST['f_txt_enabled_msg']) ? $newsletter_settings->setTxtEnabledMsg($_POST['f_txt_enabled_msg']) : $newsletter_settings->clearTxtEnabledMsg());
			
		// suspend
		(!empty($_POST['f_txt_intro_suspend']) ? $newsletter_settings->setTxtIntroSuspend($_POST['f_txt_intro_suspend']) : $newsletter_settings->clearTxtIntroSuspend());
		(!empty($_POST['f_txtSuspend']) ? $newsletter_settings->setTxtSuspend($_POST['f_txtSuspend']) : $newsletter_settings->clearTxtSuspend());
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

		// notification de modification au blog et redirection
		$newsletter_settings->save();
		newsletterTools::redirection($m,rawurldecode(__('Messages updated.')));
	}

	// mise en place de la planification
	case 'planning':
	{
		$plugin_tab = 'tab_planning';
		newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=planning&msg='.rawurldecode(__('Planning updated.')));
	}

	case 'schedule':
	{
		$m='planning';

		if (isset($core->blog->dcCron)) {
			$newsletter_cron = new newsletterCron($core);	
			$newsletter_settings = new newsletterSettings($core);
				
			// ajout de la tache planifiée
			$interval = (($_POST['f_interval']) ? $_POST['f_interval'] : 604800);
			$f_first_run = (($_POST['f_first_run']) ? strtotime(html::escapeHTML($_POST['f_first_run'])) : time() + dt::getTimeOffset($core->blog->settings->blog_timezone));
			if ($newsletter_cron->add($interval, $f_first_run)) {
				$newsletter_settings->setCheckSchedule(true);
				
				// notification de modification au blog
				$newsletter_settings->save();
				
				$msg = __('Planning updated.');
			} else {
				throw new Exception(__('Error during create planning task.'));
			}
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	case 'unschedule':
	{
		$m='planning';

		if (isset($core->blog->dcCron)) {
			$newsletter_cron=new newsletterCron($core);	
			$newsletter_settings = new newsletterSettings($core);
			$newsletter_settings->setCheckSchedule(false);
			
			// suppression de la tache planifiée
			$newsletter_cron->del();

			// notification de modification au blog
			$newsletter_settings->save();
			
			$msg = __('Planning updated.');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// mise en place de la planification
	case 'enabletask':
	{
		$m='planning';

		if (isset($core->blog->dcCron)) {
			$newsletter_cron=new newsletterCron($core);
			$newsletter_settings = new newsletterSettings($core);
				
			// activation de la tache planifiée
			$newsletter_cron->enable();

			// notification de modification au blog
			$newsletter_settings->save();
			
			$msg = __('Planning updated.');
		}
		newsletterTools::redirection($m,$msg);

	}
	break;


	// mise en place de la planification
	case 'disabletask':
	{
		$m='planning';

		if (isset($core->blog->dcCron)) {
			$newsletter_cron=new newsletterCron($core);
			$newsletter_settings = new newsletterSettings($core);
				
			// désactivation de la tache planifiée
			$newsletter_cron->disable();

			// notification de modification au blog
			$newsletter_settings->save();
			
			$msg = __('Planning updated.');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// ajout d'un abonné
	case 'add':
	{
		$m='addedit';
		
		if (!empty($_POST['femail'])) {
			$email = $_POST['femail'];

			if (newsletterCore::add($email)) {
				$msg = __('Subscriber added.');
			} else {
				throw new Exception(__('Error adding subscriber.'));
			}
		} else  {
			throw new Exception(__('You must input a valid email !'));
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	// mise à jour d'un abonné
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

	// suppression d'un ou plusieurs abonnés
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

	// suspension des comptes d'un ou plusieurs abonnés
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

	// activation des comptes d'un ou plusieurs abonnés
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

	// désactivation des comptes d'un ou plusieurs abonnés
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

	// envoi de la newsletter d'un ou plusieurs abonnés
	case 'send':
	{
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				// on verifie que les utilisateurs sont enabled
				if ($subscriber = newsletterCore::get((integer) $v)){
					if ($subscriber->state == 'enabled') {
						$ids[$k] = (integer) $v;
					}
				}
			}
			$msg = newsletterCore::send($ids,'newsletter');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;

	case 'sending':
	{

	}
	break;

	// réinitialisation de l'information 'dernier envoi'
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
	
	// envoi du mail de confirmation d'inscription
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
	
	// envoi du mail de notification de suspension de compte
	case 'sendsuspend':
	{
		if (is_array($_POST['subscriber'])) {
			$ids = array();
			foreach ($_POST['subscriber'] as $k => $v) {
				$ids[$k] = (integer) $v;
			}

			$msg = newsletterCore::send($ids,'suspend');
		}
		newsletterTools::redirection($m,$msg);
	}
	break;
	
	// envoi du mail de notification de désactivation de compte
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
	
	// envoi du mail de notification de validation de compte
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

	// changement du format d'envoi en html pour un ou plusieurs abonnés
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

	// changement du format d'envoi en html pour un ou plusieurs abonnés
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

	// export des données
	case 'export':
	{
		$m = 'maintenance';

		if (!empty($_POST['type'])) 
			$type = $_POST['type'];
		else 
			$type = 'blog';
		
		$msg = newsletterAdmin::Export( ($type=='blog') ? true : false );
		
		newsletterTools::redirection($m,$msg);
	}
	break;

	// import des données
	case 'import':
	{
		$m = 'maintenance';

		if (!empty($_POST['type'])) 
			$type = $_POST['type'];
		else 
			$type = 'blog';
		
		$msg = newsletterAdmin::Import( ($type=='blog') ? true : false );
		
		newsletterTools::redirection($m,$msg);
	}
	break;

	// import email addresses from a file
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

	// adaptation du template au thème
	case 'adapt':
	{
		$m = 'maintenance';

		$msg = __('No template adapted.');
		if (!empty($_POST['fthemes'])) {
			if (newsletterAdmin::adapt($_POST['fthemes'])) 
				$msg = __('Template successfully adapted.');
			else
				throw new Exception(__('Error to adapt template'));
		}
		
		newsletterTools::redirection($m,$msg);
	}
	break;
	
	// suppression des informations de newsletter en base
	case 'erasingnewsletter':	
	{
		$m = 'maintenance';

		// suppression de la tache planifiée	
		if (isset($core->blog->dcCron)) {
			$newsletter_cron=new newsletterCron($core);	
			$newsletter_settings = new newsletterSettings($core);
			$newsletter_settings->setCheckSchedule(false);
			
			// suppression de la tache planifiée
			$newsletter_cron->del();

			// notification de modification au blog
			$newsletter_settings->save();
		}
			
		newsletterAdmin::uninstall();
		
		$redir = 'plugin.php?p=aboutConfig';
		http::redirect($redir);			
	}
	break;	
    
	case '-':
	{
		newsletterTools::redirection($m,$msg);
	}
	break;
	    
	case 'none':
	default:
		break;
}

} catch (Exception $e) {
	if(isset($core->blog->dcNewsletter)) {
		$core->blog->dcNewsletter->addError($e->getMessage());
		$core->blog->dcNewsletter->save();
	}
}

# Display errors
if(isset($core->blog->dcNewsletter)) {
	foreach ($core->blog->dcNewsletter->getErrors() as $k => $v) {
		$core->error->add($v);
		$core->blog->dcNewsletter->delError($k);
	}
	$core->blog->dcNewsletter->save();
}

?>
<html>
<head>
	<title><?php echo $plugin_name ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo newsletterPlugin::urldatas() ?>/style.css" />
	
	<?php 
	if (newsletterPlugin::isActive()) {
		echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=newsletter/js/_newsletter.js');

		if ($plugin_tab == 'tab_planning') {
			if (isset($core->blog->dcCron)) {
				echo dcPage::jsDatePicker();				
				echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=newsletter/js/_newsletter.cron.js');
			}
		} else if ($plugin_tab == 'tab_subscribers') {
			echo dcPage::jsLoad('js/filter-controls.js');
		}
	}
	?>
	<script type="text/javascript">
	//<![CDATA[
	<?php echo dcPage::jsVar('dotclear.msg.confirm_erasing_datas',__('Are you sure you want to delete all informations about newsletter in database ?')); ?>
	<?php echo dcPage::jsVar('dotclear.msg.confirm_import',__('Are you sure you want to import a backup file ?')); ?>
	//]]>
	</script>	
	
	<?php echo dcPage::jsPageTabs($plugin_tab); ?>	
</head>
<body>

<?php 
echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; <a href="'.newsletterPlugin::admin().'" title="'.$plugin_name.'">'.$plugin_name.' '.newsletterPlugin::dcVersion().'</a></h2>';

if (!empty($msg)) {
	echo '<p class="message">'.$msg.'</p>';
}

$edit_subscriber = (!empty($_GET['id']) ? __('Edit') : __('Add'));

switch ($plugin_tab) {
	case 'tab_subscribers' :
		echo '<div class="multi-part" id="tab_subscribers" title="'.__('Subscribers').'">';
		newsletterSubscribersList::tabSubscribersList();
		echo '</div>';	
		echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
		break;
	case 'tab_addedit' :
		echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
		echo '<div class="multi-part" id="tab_addedit" title="'.$edit_subscriber.'">';
		tabsNewsletter::AddEdit();
		echo '</div>';	
		echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
		break;
	case 'tab_settings' :
		echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
		echo '<div class="multi-part" id="tab_settings" title="'.__('Settings').'">';	
		tabsNewsletter::Settings();
		echo '</div>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
		break;
	case 'tab_messages' :
		echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
		echo '<div class="multi-part" id="tab_messages" title="'.__('Messages').'">';
		tabsNewsletter::Messages();
		echo '</div>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
		break;
	case 'tab_planning':
		echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
		echo '<div class="multi-part" id="tab_planning" title="'.__('Planning').'">';
		tabsNewsletter::Planning();
		echo '</div>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
		break;
	case 'tab_maintenance':
		echo '<p><a href="plugin.php?p=newsletter&amp;m=subscribers" class="multi-part">'.__('Subscribers').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=addedit" class="multi-part">'.$edit_subscriber.'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=messages" class="multi-part">'.__('Messages').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=settings" class="multi-part">'.__('Settings').'</a></p>';
		echo '<p><a href="plugin.php?p=newsletter&amp;m=planning" class="multi-part">'.__('Planning').'</a></p>';
		echo '<div class="multi-part" id="tab_maintenance" title="'.__('Maintenance').'">';
		tabsNewsletter::Maintenance();
		echo '</div>';
		break;
}

?>
	
<?php dcPage::helpBlock('newsletter');?>

</body>
</html>
