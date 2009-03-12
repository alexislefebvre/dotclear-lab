<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Newsletter, a plugin for Dotclear 2.
# Copyright (C) 2009 Benoit de Marne, and contributors. All rights
# reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) exit;
dcPage::check('usage,admin');

// chargement des librairies
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';
require_once dirname(__FILE__).'/inc/class.newsletter.admin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.cron.php';

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

// action en fonction de l'opération
switch ($plugin_op)
{
	// modification de l'état d'activation du plugin
	case 'state':
	{
		$plugin_tab = 'tab_maintenance';

		try {
	        if (!empty($_POST['active']))
	            newsletterPlugin::Activate();
	        else
	            newsletterPlugin::Inactivate();

			// notification de modification au blog et redirection
			newsletterPlugin::Trigger();
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=maintenance&msg='.rawurldecode(__('Settings updated.')));
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
				if (!empty($_POST['feditorname'])) 
					newsletterPlugin::setEditorName($_POST['feditorname']);
				else 
					newsletterPlugin::clearEditorName();

				// email de l'éditeur
				newsletterPlugin::setEditorEmail($_POST['feditoremail']);

				// message d'introduction
				if (!empty($_POST['f_introductory_msg'])) 
					newsletterPlugin::setIntroductoryMsg($_POST['f_introductory_msg']);
				else 
					newsletterPlugin::clearIntroductoryMsg();

				// message de conclusion
				if (!empty($_POST['f_concluding_msg'])) 
					newsletterPlugin::setConcludingMsg($_POST['f_concluding_msg']);
				else 
					newsletterPlugin::clearConcludingMsg();

				// message de presentation
				if (!empty($_POST['f_presentation_msg'])) 
					newsletterPlugin::setPresentationMsg($_POST['f_presentation_msg']);
				else 
					newsletterPlugin::clearPresentationMsg();

				// message de presentation posts
				if (!empty($_POST['f_presentation_posts_msg'])) 
					newsletterPlugin::setPresentationPostsMsg($_POST['f_presentation_posts_msg']);
				else 
					newsletterPlugin::clearPresentationPostsMsg();

				// message de présentation du formulaire
				if (!empty($_POST['f_msg_presentation_form'])) 
					newsletterPlugin::setMsgPresentationForm($_POST['f_msg_presentation_form']);
				else 
					newsletterPlugin::clearMsgPresentationForm();

				// Introduction au lien de confirmation
				if (!empty($_POST['f_txt_intro_confirm'])) 
					newsletterPlugin::setTxtIntroConfirm($_POST['f_txt_intro_confirm']);
				else 
					newsletterPlugin::clearTxtIntroConfirm();

				// Titre du lien de confirmation
				if (!empty($_POST['f_txtConfirm'])) 
					newsletterPlugin::setTxtConfirm($_POST['f_txtConfirm']);
				else 
					newsletterPlugin::clearTxtConfirm();

				// Introduction au lien de desactivation
				if (!empty($_POST['f_txt_intro_disable'])) 
					newsletterPlugin::setTxtIntroDisable($_POST['f_txt_intro_disable']);
				else 
					newsletterPlugin::clearTxtIntroDisable();

				// Titre du lien de desactivation
				if (!empty($_POST['f_txtDisable'])) 
					newsletterPlugin::setTxtDisable($_POST['f_txtDisable']);
				else 
					newsletterPlugin::clearTxtDisable();

				// Introduction au lien d'activation
				if (!empty($_POST['f_txt_intro_enable'])) 
					newsletterPlugin::setTxtIntroEnable($_POST['f_txt_intro_enable']);
				else 
					newsletterPlugin::clearTxtIntroEnable();

				// Titre du lien d'activation
				if (!empty($_POST['f_txtEnable'])) 
					newsletterPlugin::setTxtEnable($_POST['f_txtEnable']);
				else 
					newsletterPlugin::clearTxtEnable();

				// Introduction au lien de suspension
				if (!empty($_POST['f_txt_intro_suspend'])) 
					newsletterPlugin::setTxtIntroSuspend($_POST['f_txt_intro_suspend']);
				else 
					newsletterPlugin::clearTxtIntroSuspend();

				// Titre du lien de suspension
				if (!empty($_POST['f_txtSuspend'])) 
					newsletterPlugin::setTxtSuspend($_POST['f_txtSuspend']);
				else 
					newsletterPlugin::clearTxtSuspend();

				// --------- advanced settings -------------

				// captcha
				if (!empty($_POST['fcaptcha'])) 
					newsletterPlugin::setCaptcha($_POST['fcaptcha']);
				else 
					newsletterPlugin::setCaptcha();

				// mode d'envoi
				if (!empty($_POST['fmode'])) 
					newsletterPlugin::setSendMode($_POST['fmode']);
				else 
					newsletterPlugin::clearSendMode();

				// sélection du format d'envoi par utilisateur ou global
				if (!empty($_POST['f_use_default_format'])) 
					newsletterPlugin::setUseDefaultFormat($_POST['f_use_default_format']);
				else 
					newsletterPlugin::clearUseDefaultFormat();

				// envoi automatique
				if (!empty($_POST['fautosend'])) 
					newsletterPlugin::setAutosend($_POST['fautosend']);
				else 
					newsletterPlugin::clearAutosend();

				// nombre max. de billets
				if (!empty($_POST['fmaxposts'])) 
					newsletterPlugin::setMaxPosts($_POST['fmaxposts']);
				else 
					newsletterPlugin::clearMaxPosts();
				
				// affichage du contenu du post
				if (!empty($_POST['f_view_content_post'])) 
					newsletterPlugin::setViewContentPost($_POST['f_view_content_post']);
				else 
					newsletterPlugin::clearViewContentPost();

				// taille maximale du contenu du post
				if (!empty($_POST['f_size_content_post'])) 
					newsletterPlugin::setSizeContentPost($_POST['f_size_content_post']);
				else 
					newsletterPlugin::clearSizeContentPost();

				// filtre de catégorie
				if (!empty($_POST['f_category'])) 
					newsletterPlugin::setCategory($_POST['f_category']);
				else 
					newsletterPlugin::clearCategory();
			
				// notification de modification au blog et redirection
				newsletterPlugin::Trigger();
				newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=settings&msg='.rawurldecode(__('Settings updated.')));
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// mise en place de la planification
	case 'planning':
	{
		$plugin_tab = 'tab_planning';
		try {
			newsletterPlugin::redirect(newsletterPlugin::admin().'&tab=settings&msg='.rawurldecode(__('Settings updated.')));
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

	// activation des comptes d'un ou plusieurs abonnés
	case 'send':
	{
		$plugin_tab = 'tab_listblog';

		try {
			$ids = array();
			foreach (array_keys($_POST['subscriber']) as $id) 
			{ 
				$ids[] = $id; 
			}
			$msg = newsletterCore::sendNewsletter($ids);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// réinitialisation de l'information 'dernier envoi'
	case 'lastsent':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
    	    $msg = __('No account changed.');
    	    if (is_array($_POST['subscriber']))
    	    {
    	        $ids = array();
	            foreach (array_keys($_POST['subscriber']) as $id) { $ids[] = $id; }
	            if (newsletterCore::lastsent($ids, 'clear')) $msg = __('Account(s) successfully changed.');
	        }
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
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

	// envoi du mail de confirmation d'inscription
	case 'sendconfirm':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
	        $ids = array();
	        foreach (array_keys($_POST['subscriber']) as $id) { $ids[] = $id; }
	        $msg = newsletterCore::sendConfirm($ids);
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;
	
	// envoi du mail de notification de suspension de compte
	case 'sendsuspend':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
	        $ids = array();
	        foreach (array_keys($_POST['subscriber']) as $id) { $ids[] = $id; }
	        $msg = newsletterCore::sendSuspend($ids);
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;
	
	// envoi du mail de notification de désactivation de compte
	case 'senddisable':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
	        $ids = array();
	        foreach (array_keys($_POST['subscriber']) as $id) { $ids[] = $id; }
	        $msg = newsletterCore::sendDisable($ids);
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;
	
	// envoi du mail de notification de validation de compte
	case 'sendenable':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
	        $ids = array();
	        foreach (array_keys($_POST['subscriber']) as $id) { $ids[] = $id; }
	        $msg = newsletterCore::sendEnable($ids);
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
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
				if (newsletterAdmin::Adapt($_POST['fthemes'])) $msg = __('Template successfully adapted.');
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
	<link rel="stylesheet" type="text/css" href="<?php echo newsletterPlugin::urldatas() ?>/admin_css/style.css" />
	
	<?php echo dcPage::jsDatePicker(); ?>
	
	<?php echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=newsletter/_newsletter.js'); ?>
	<?php if (isset($core->blog->dcCron)) 
		echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=newsletter/_newsletter.cron.js'); 
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
	<div class="multi-part" id="tab_planning" title="<?php echo __('Planning'); ?>">
		<?php tabsNewsletter::Planning() ?>
	</div>
	<div class="multi-part" id="tab_maintenance" title="<?php echo __('Maintenance'); ?>">
		<?php tabsNewsletter::Maintenance() ?>
	</div>
	
<?php dcPage::helpBlock('newsletter');?>
	
</body>
</html>
