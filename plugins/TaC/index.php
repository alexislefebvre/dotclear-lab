<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of TaC, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('amdin');
$msg = '';

# Si vous ne souhaitez pas avoir vos propres tokens 
# il suffit de rediriger l'utilisteur sur cette page pour qu'il s'identifie.
# Pour utiliser oAuth pour vos plugins twitter avec TaC, 
# Voici un exemple dans une utilisation courante (une fois vérifié):
/*
if ($core->plugins->moduleExists('tac')) {
	$has_registry = $has_access = false;
	
	try {
		$plop = new tac($core,'TaC',null);
		$has_registry = $plop->checkRegistry();
		$has_access = $plop->checkAccess();
	}
	catch(Exception $e) {
		$has_registry = $has_access = false;
		$core->error->add($e->getMessage());
	}
	
	// Exemples d'echange avec Twitter
	if ($has_access) {
		try {
			// Envoyer de message
			$msg = $plop->tic->statusesUpdate('mon message vers twitter');
			// ou
			$msg = $plop->post('statuses/update',array('status'=>'mon message vers twitter'));
			if ('' != $msg->text) {
				echo '<p>Message envoyé</p>';
			}
			
			// Verifier les limits de tweet
			$rate = $plop->tic->accountRateLimitStatus()
			echo '<p>'.$content->remaining_hits.'</p>';
		}
		catch(Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}
*/
# Suivez le déroulement de cette page pour comprendre son utilistion et
# ainsi pourvoir l'intégrer à votre plugin.

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

# Ouvre un object pour le plugin TaC et pour tous les utilisateurs du blog (null)
try {
	$TaC = new tac($core,'TaC',null);
}
catch(Exception $e) {
	$core->error->add($e->getMessage());
}

# On test si le plugin est connu (si oui il sera chargé dans l'objet)
if (!$core->error->flag())
{
	try {
		$has_registry = $TaC->checkRegistry();
	}
	catch(Exception $e) {
		$has_registry = false;
		$core->error->add($e->getMessage());
	}
}

# Si le plugin est inconnu on l'ajoute
if (!$core->error->flag() && !$has_registry)
{
	$cur = $core->con->openCursor($core->prefix.'tac_registry');
	
	#
	# Exemple ici pour le plugin TaC
	#
	# Pour avoir vos propres tokens sur Twitter il suffit d'enregistrer 
	# votre plugin comme une application sur:
	# http://twitter.com/apps
	# - Pour l'url de callback il faut en renseigner 
	#   une même si elle sera réécrite ici.
	# - Pour le type d'accés TaC supporte le Read & Write
	#
	# Ensuite remplacer les infos ci-dessous par celle donner sur Twitter
	# Et lancer "tac" avec comme deuxiéme parametre l'id de votre plugin.
	# (de préférence car même si pour l'instant il n'y a pas de test sur l'id
	# cela risque de changer dans les versions futurs)
	#
	try {
		$cur->cr_id = 'TaC';
		$cur->cr_key = 'qXCQrZLAAEzNf7IYxQB6w';
		$cur->cr_secret = 'fbKB6VIcMnA5xhuLXq3wgaw76fKBDSAd98HjyjjZi6g';
		//$cur->cr_expiry = 0;
		//$cur->cr_sig_method = 'HMAC-SHA1';
		$cur->cr_url_request = 'http://twitter.com/oauth/request_token';
		$cur->cr_url_access = 'http://twitter.com/oauth/access_token';
		$cur->cr_url_autorize = 'http://twitter.com/oauth/authorize';
		$cur->cr_url_authenticate = 'https://api.twitter.com/oauth/authenticate';
		
		$TaC->addRegistry($cur);
		
		# Puis on retente de le charger
		$has_registry = $TaC->checkRegistry();
		
		# Si il n'est toujours pas reconnu, il y a un bug!
		if (!$has_registry) {
			throw new Exception(__('Failed to register plugin'));
		}
	}
	catch(Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# On test si l'utilisateur est connu (si oui il sera chargé dans l'objet)
if (!$core->error->flag())
{
	try {
		$has_access = $TaC->checkAccess();
	}
	catch(Exception $e) {
		$has_access = false;
		$core->error->add($e->getMessage());
	}
}

# 1ère phase de l'authentification
#
# Si l'utilisateur est inconnu on demarre le processus d'authentification oAuth
# avec le joli bouton de connexion
# voir plus bas: if (!$core->error->flag() && !$action && !$has_access)


# 2ème phase de l'authentification
#
# L'utilisateur souhaite s'authentifier
# On lance une requete pour avoir un token temporaire
# Si la requete est valide, on redirige l'utilisateur vers le serveur oAuth
# en lui précisant l'url de retour
if (!$core->error->flag() && $action == 'redirect')
{
	try {
		$url = $TaC->requestAccess(DC_ADMIN_URL.'plugin.php?p=TaC&action=callback');
		http::redirect($url);
	}
	catch(Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# 3éme phase de l'authentification
#
# L'utilisateur revient du seveur oAuth avec un code définitif
# Si il n'y a pas d'erreur on recharge la page pour tout prendre en compte
if (!$core->error->flag() && $action == 'callback')
{
	try {
		$has_grant = $TaC->grantAccess();
		
		# Sinon on efface toute les infos de l'utilisateur
		if (!$has_grant) {
			$TaC->cleanAccess();
		}
		http::redirect($p_url);
	}
	catch(Exception $e) {
		$has_grant = false;
		$core->error->add($e->getMessage());
	}
}

# Additionnel
# Nettoyer un acces
if (!$core->error->flag() && $action == 'clean')
{
	try {
		$TaC->cleanAccess();
		http::redirect($p_url);
	}
	catch(Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Additionnel
# Envoyer un message sur twitter
if (!$core->error->flag() && $action == 'sendtweet' && !empty($_POST['message']))
{
	$message = trim($_POST['message']);
	if (!empty($message)) {
		$params = array(
			'status' => (string) $message
		);
		$su = $TaC->post('statuses/update',$params);
		if ('' != $su->text) {
			$msg = '<p class="message">'.__('Your message have been successfully sent.').'</p>';
		}
		else {
			$msg = '<p class="message">'.__('Not sure that your message is sent').'</p>';
		}
	}
}

# Display
echo '
<html><head><title>TaC - '.__("Twitter Authentication Client").'</title></head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; TaC - '.__("Twitter Authentication Client").'</h2>';

if (!$core->error->flag() && $has_access)
{
	echo $msg;

	$user = $TaC->get('account/verify_credentials');
	$content = $TaC->get('account/rate_limit_status');
	
	echo '
	<ul>
	<li>'.sprintf(__('Your are connected as "%s"'),$user->screen_name).'</li>
	<li>'.sprintf(__('It remains %s API hits'),$content->remaining_hits).'</li>
	<li><a href="'.$p_url.'&action=clean">'.__('Disconnect and clean access').'</a></li>
	</ul>
	
	<form method="post" action="'.$p_url.'">
	<p><label>'.__('Quick send message to your twitter timeline:').' <br />'.
	form::field(array('message'),60,140,'').'</label></p>
	<p><input type="submit" name="save" value="'.__('send').'" />'.
	$core->formNonce().
	form::hidden(array('p'),'TaC').
	form::hidden(array('action'),'sendtweet').'
	</p>
	</form>';
}

# First part
if (!$core->error->flag() && !$action && !$has_access)
{
	echo '
	<fieldset><legend>'.__('Connect your blog with your twitter account through TaC').'</legend>
	<p><a href="'.$p_url.'&action=redirect"><img src="index.php?pf=TaC/img/tac_light.png" alt="Sign in with Twitter"/></a></p>
	</fieldset>';
}

if ($core->error->flag()) {
	echo '<p><a href="'.$p_url.'">'.__('Retry').'</a></p>';
}

echo '
<br class="clear"/>
<p class="right">
TaC - '.$core->plugins->moduleInfo('TaC','version').'&nbsp;
<img alt="TaC" src="index.php?pf=TaC/icon.png" />
</p>';
dcPage::helpBlock('TaC');
echo '</body></html>';
?>