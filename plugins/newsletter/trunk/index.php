<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
if (!empty($_POST['tab'])) $plugin_tab = 'tab_'.(string)$_POST['tab'];
else if (!empty($_GET['tab'])) $plugin_tab = 'tab_'.(string)$_GET['tab'];

// récupération de l'opération (si possible)
if (!empty($_POST['op'])) $plugin_op = (string)$_POST['op'];
else $plugin_op = 'none';

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
			if (empty($_POST['feditoremail'])) 
				$msg = __('You must input a valid email !');
			else
			{
				// nom de l'éditeur
				if (!empty($_POST['feditorname'])) 
					pluginNewsletter::setEditorName($_POST['feditorname']);
				else 
					pluginNewsletter::clearEditorName();

				// email de l'éditeur
				pluginNewsletter::setEditorEmail($_POST['feditoremail']);

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

				// notification de modification au blog et redirection
				pluginNewsletter::Trigger();
				pluginNewsletter::redirect(pluginNewsletter::admin().'&tab=settings&msg='.rawurldecode(__('Settings updated.')));
			}
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
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
			
				if (!dcNewsletter::update($id, $email, $state, $regcode, $subscribed, $lastsent)) 
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
		$plugin_tab = 'tab_settings';

	    try
	    {
	        if (!empty($_POST['type'])) $type = $_POST['type'];
	        else $type = 'blog';
		
			adminNewsletter::Export( ($type=='blog') ? true : false );
			$msg = __('Datas exported.');
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;

	// import des données
	case 'import':
	{
		$plugin_tab = 'tab_settings';

	    try
	    {
	        if (!empty($_POST['type'])) $type = $_POST['type'];
	        else $type = 'blog';
		
			adminNewsletter::Import( ($type=='blog') ? true : false );
			$msg = __('Datas imported.');
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
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
	
	// adaptation du template au thème
	case 'adapt':
	{
		$plugin_tab = 'tab_settings';

	    try
	    {
    	    $msg = __('No template adapted.');
    	    if (!empty($_POST['fthemes']))
    	    {
	            if (adminNewsletter::Adapt($_POST['fthemes'])) $msg = __('Template(s) successfully adapted.');
	        }
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
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
	<link rel="stylesheet" type="text/css" href="<?php echo pluginNewsletter::urldatas() ?>/style.css" />
	<script src="<?php echo pluginNewsletter::urldatas() ?>/functions.js"></script>
</head>
<body>
<?php echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; <a href="'.pluginNewsletter::admin().'" title="'.$plugin_name.'">'.$plugin_name.' '.pluginNewsletter::dcVersion().'</a></h2>' ?>
<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>' ?>
	<div class="multi-part" id="tab_settings" title="<?php echo __('Settings'); ?>">
		<?php tabsNewsletter::Settings() ?>
	</div>
	<div class="multi-part" id="tab_listblog" title="<?php echo __('Subscribers'); ?>">
		<?php tabsNewsletter::ListBlog() ?>
	</div>
	<div class="multi-part" id="tab_addedit" title="<?php echo (!empty($_POST['id'])) ? __('Edit') : __('Add'); ?>">
		<?php tabsNewsletter::AddEdit() ?>
	</div>
	

<?php dcPage::helpBlock('newsletter');?>
	
</body>
</html>
