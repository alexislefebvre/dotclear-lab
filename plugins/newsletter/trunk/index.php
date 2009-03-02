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
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/class.modele.php';
require_once dirname(__FILE__).'/class.admin.php';

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
		$plugin_tab = 'tab_settings';

	    try
	    {
	        if (!empty($_POST['active']))
	            pluginNewsletter::Activate();
	        else
	            pluginNewsletter::Inactivate();

			// notification de modification au blog et redirection
			pluginNewsletter::Trigger();
			pluginNewsletter::redirect(pluginNewsletter::admin().'&tab=settings&msg='.rawurldecode(__('Settings updated.')));
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
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
					pluginNewsletter::setEditorName($_POST['feditorname']);
				else 
					pluginNewsletter::clearEditorName();

				// email de l'éditeur
				pluginNewsletter::setEditorEmail($_POST['feditoremail']);

				// message d'introduction
				if (!empty($_POST['f_introductory_msg'])) 
					pluginNewsletter::setIntroductoryMsg($_POST['f_introductory_msg']);
				else 
					pluginNewsletter::clearIntroductoryMsg();

				// message de conclusion
				if (!empty($_POST['f_concluding_msg'])) 
					pluginNewsletter::setConcludingMsg($_POST['f_concluding_msg']);
				else 
					pluginNewsletter::clearConcludingMsg();

				// message de presentation
				if (!empty($_POST['f_presentation_msg'])) 
					pluginNewsletter::setPresentationMsg($_POST['f_presentation_msg']);
				else 
					pluginNewsletter::clearPresentationMsg();

				// message de presentation posts
				if (!empty($_POST['f_presentation_posts_msg'])) 
					pluginNewsletter::setPresentationPostsMsg($_POST['f_presentation_posts_msg']);
				else 
					pluginNewsletter::clearPresentationPostsMsg();

				// mode d'envoi
				if (!empty($_POST['fmode'])) 
					pluginNewsletter::setSendMode($_POST['fmode']);
				else 
					pluginNewsletter::clearSendMode();

				// nombre max. de billets
				if (!empty($_POST['fmaxposts'])) 
					pluginNewsletter::setMaxPosts($_POST['fmaxposts']);
				else 
					pluginNewsletter::clearMaxPosts();

				// envoi automatique
				if (!empty($_POST['fautosend'])) 
					pluginNewsletter::setAutosend($_POST['fautosend']);
				else 
					pluginNewsletter::setAutosend();

				// captcha
				if (!empty($_POST['fcaptcha'])) 
					pluginNewsletter::setCaptcha($_POST['fcaptcha']);
				else 
					pluginNewsletter::setCaptcha();

				// affichage du contenu du post
				if (!empty($_POST['f_view_content_post'])) 
					pluginNewsletter::setViewContentPost($_POST['f_view_content_post']);
				else 
					pluginNewsletter::setViewContentPost();

				// taille maximale du contenu du post
				if (!empty($_POST['f_size_content_post'])) 
					pluginNewsletter::setSizeContentPost($_POST['f_size_content_post']);
				else 
					pluginNewsletter::clearSizeContentPost();

				// message de présentation du formulaire
				if (!empty($_POST['f_msg_presentation_form'])) 
					pluginNewsletter::setMsgPresentationForm($_POST['f_msg_presentation_form']);
				else 
					pluginNewsletter::clearMsgPresentationForm();

				// notification de modification au blog et redirection
				pluginNewsletter::Trigger();
				pluginNewsletter::redirect(pluginNewsletter::admin().'&tab=settings&msg='.rawurldecode(__('Settings updated.')));
			}
		}
		catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	break;

	// vérification de mise à jour
/*	case 'update':
	{
		$plugin_tab = 'tab_settings';

	    try
	    {
			$msg = pluginNewsletter::htmlNewVersion(true);
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
				if (!dcNewsletter::add($email)) 
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
			
				if (!dcNewsletter::update($id, $email, $state, $regcode, $subscribed, $lastsent, $modesend)) 
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
				if (dcNewsletter::delete($ids)) 
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
				if (dcNewsletter::suspend($ids)) 
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
				if (dcNewsletter::enable($ids)) 
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
				if (dcNewsletter::disable($ids)) 
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
			$msg = dcNewsletter::sendNewsletter($ids);
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
	            if (dcNewsletter::lastsent($ids, 'clear')) $msg = __('Account(s) successfully changed.');
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
		
			adminNewsletter::Export( ($type=='blog') ? true : false );
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
		
			adminNewsletter::Import( ($type=='blog') ? true : false );
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
	        $msg = dcNewsletter::sendConfirm($ids);
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
	        $msg = dcNewsletter::sendSuspend($ids);
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
	        $msg = dcNewsletter::sendDisable($ids);
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
	        $msg = dcNewsletter::sendEnable($ids);
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
				if (dcNewsletter::changemodehtml($ids)) 
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
				if (dcNewsletter::changemodetext($ids)) 
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
				if (adminNewsletter::Adapt($_POST['fthemes'])) $msg = __('Template successfully adapted.');
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
		
			adminNewsletter::Uninstall();
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
	<?php echo dcPage::jsPageTabs($plugin_tab); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo pluginNewsletter::urldatas() ?>/admin_css/style.css" />
	<script src="<?php echo pluginNewsletter::urldatas() ?>/functions.js"></script>
</head>
<body>
<?php echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; <a href="'.pluginNewsletter::admin().'" title="'.$plugin_name.'">'.$plugin_name.' '.pluginNewsletter::dcVersion().'</a></h2>' ?>

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
	<div class="multi-part" id="tab_maintenance" title="<?php echo __('Maintenance'); ?>">
		<?php tabsNewsletter::Maintenance() ?>
	</div>
	
<?php dcPage::helpBlock('newsletter');?>
	
</body>
</html>
