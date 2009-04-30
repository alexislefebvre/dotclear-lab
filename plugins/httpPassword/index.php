<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of httpPassword, a plugin for Dotclear.
#
# Copyright (c) 2007-2009 Frederic PLE
# dotclear@frederic.ple.name
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

function htpasswd_crypt(&$core,$secret)
{
	switch ($core->blog->settings->httppassword_crypt) {
		case "plaintext":
			$saltlen = -1;
			break;
		case "crypt_std_des":
			$saltlen = 2;
			$salt = "";
			break;
		case "crypt_ext_des":
			$saltlen = 9;
			$salt = "";
			break;
		case "crypt_md5":
			$saltlen = 12;
			$salt = '$1$';
			break;
		case "crypt_blowfish":
			$saltlen = 16;
			$salt = '$2$';
			break;
		default:
			return(false);
	}

	if ($saltlen > 0) {
		$salt .= substr(
			sha1($core->getNonce() . date('U')),
			2,
			$saltlen - strlen($salt)
		);
		$secret = crypt($secret,$salt);
	}

	return($secret);
}

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$crypt_algo = array(
	'plaintext' => 'Aucun',
);
if (CRYPT_STD_DES == 1)
	$crypt_algo['crypt_std_des'] = 'Crypt DES standard';

if (CRYPT_EXT_DES == 1)
	$crypt_algo['crypt_ext_des'] = 'Crypt DES &eacute;tendu';

if (CRYPT_MD5 == 1)
	$crypt_algo['crypt_md5'] = 'Crypt MD5';

if (CRYPT_BLOWFISH == 1)
	$crypt_algo['crypt_blowfish'] = 'Crypt Blowfish';

$htpasswdfile = $core->blog->public_path . '/.htpasswd' ;
$htp = file($htpasswdfile);
if (!is_array($htp)) $htp = array();
sort($htp);

$u = array();
$v = array();

foreach($htp as $ligne) {
	list($login, $pwd) = explode(':', $ligne, 2);
	$u[trim($login)] = trim($pwd);
}
unset($ftp);

$txt = !empty($_POST['txt']) ? $_POST['txt'] : null;
$action = !empty($_POST['httppasswordaction'])
	? $_POST['httppasswordaction']
	: null;

$core->blog->settings->setNamespace('httppassword');

switch($action) {
	case "mod":
		// traitement des donnees du formulaire
		foreach(preg_split('/\n/m',$txt) as $ligne)
		{
			if (strpos($ligne, ':') === false)
				$ligne = trim($ligne) . ':';
			list($login, $pwd) = explode(':', $ligne);
			$v[trim($login)] = trim($pwd);
		}
	
		// Rechercher les suppressions
		foreach(array_keys($u) as $login)
		{
			if (!isset($v[$login]))
				unset($u[$login]);
		}
	
		// Rechercher les modifs + nouveaux
		foreach(array_keys($v) as $login)
		{
			if ($v[$login] != "") {
				$u[$login] = htpasswd_crypt(
					$core,
					$v[$login]
				);
				if ($u[$login] === false)
					unset($u[$login]);
			}
		}
	
		$txt = "";
		foreach(array_keys($u) as $login)
			$txt .= $login.":".$u[$login]."\r\n";
		file_put_contents($htpasswdfile,$txt);
		break;

	case "desactive":
	case "active":
		$active = !$core->blog->settings->httppassword_active;
		$core->blog->settings->put(
			'httppassword_active',
			$active,
			'boolean'
		);
		$core->blog->settings->httppassword_active = $active;
		break;

	case "cryptfunc":
		$httppassword_crypt = trim($_POST['cryptage']);
		if (in_array($httppassword_crypt,array_keys($crypt_algo))) {
			$core->blog->settings->put(
				'httppassword_crypt',
				$httppassword_crypt,
				'string'
			);
			$core->blog->settings->httppassword_crypt =
				$httppassword_crypt;
		}
		break;
	
	case "auth_message":
		$message = htmlspecialchars($_POST['auth_message']);
		$core->blog->settings->put(
			'httppassword_message',
			$message,
			'string'
		);
		$core->blog->settings->httppassword_message = $message;
		break;
}

$fic = $core->blog->public_path . '/.lastlogin';
$httpPasswordLastLogin = array();
if (is_file($fic))
	$httpPasswordLastLogin = unserialize(file_get_contents($fic));

$form_block=' style="display: none;"';
if (strlen($core->blog->settings->httppassword_crypt) > 0) $form_block="";
?><html>
<head>
	<title>httpPassword</title>
	<?php echo dcPage::jsPageTabs($part); ?>
	<style type="text/css">
	.ns-name {
		background: #ccc;
		color: #000;
		padding-top: 0.3em;
		padding-bottom: 0.3em;
		font-size: 1.1em;
	}
	.fp-code {
		border-left: #d0d0d0 solid 4px;
		margin-left: 40px;
		padding: 5px;
	}
	tr.row-odd { background: #bbbbbb; }
	tr.row-even { background: #dddddd; }
	h3 { border-top: #d0d0d0 dotted 2px; margin-top: 15px; }
	</style>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; httpPasswd</h2>

<div id="local" class="multi-part" title="Gestion des acc&egrave;s restreints">
<table><tr>
	<td>
	<div<?php echo $form_block; ?>>
	<h3>Activation du plugin</h3>
<?php
$canwrite = true;
foreach(array('.htpasswd','.lastlogin') as $f) {
	$fp = fopen(dirname($htpasswdfile) . '/' . $f,'a+');
	if ($fp === false)
		$canwrite = false;
	else
		fclose($fp);
}

$fichier_existe = is_file(dirname($htpasswdfile) . '/.htpasswd');
$fichier_modifier = true;

if (!$canwrite) { ?>
<p><b>Pour utiliser cette extension, vous devez avoir les permissions
pour &eacute;crire dans les fichiers :</b></p>
<ul>
	<li><tt><?php echo $htpasswdfile; ?></tt></li>
	<li><tt><?php echo dirname($htpasswdfile) . '/.lastlogin'; ?></tt></li>
</ul>
<?php } elseif ($core->blog->settings->httppassword_active) { ?>
<p><b>Protection ACTIV&Eacute;E</b></p>
<form method="post">
		Cliquer sur ce bouton pour d&eacute;sactiver la protection :
		<input type="submit" value="D&eacute;sactiver" /> 
<?php
echo
	$core->formNonce() .
	form::hidden(array('p'),'httpPassword') .
	form::hidden(array('httppasswordaction'),'desactive');
?>
</form>

<?php } else {	?>
	utilisateur valide !</b></p>
	<form method="post">
		Cliquer sur ce bouton pour activer la protection :
		<input type="submit" value="Activer" /> 
<?php
echo
	$core->formNonce().
	form::hidden(array('p'),'httpPassword').
	form::hidden(array('httppasswordaction'),'active');
?>
	</form>

<?php } ?>
	</div>

	<div>
	<h3>S&eacute;curit&eacute; des mots de passe</h3>
	<p>Pour modifier la fonction de "cryptage".</p>
	<p><b>Attention, le changement de 
	cryptage s'appliquera individuellement &agrave; la prochaine modification 
	de chacun des comptes (cr&eacute;tion ou changement de mot de passe)</b></p>
	<form method="post">
<?php
foreach($crypt_algo as $algo_code => $algo_libelle) {
	echo '<input type="radio" name="cryptage" value="' . $algo_code . '" ';
	if ($core->blog->settings->httppassword_crypt == $algo_code)
		echo 'checked ';
	echo '/>&nbsp;' . $algo_libelle . '<br />';
} ?><input type="submit" value="Modifier" /> 
<?php
echo
	$core->formNonce().
	form::hidden(array('p'),'httpPassword').
	form::hidden(array('httppasswordaction'),'cryptfunc');
?>
	</form>
	</div>

	<div<?php echo $form_block; ?>>
	<h3>Message d'authentification</h3>
	<form method="post">
	<p><input type="text" name="auth_message" size="50"
		value="<?php echo $core->blog->settings->httppassword_message; ?>" />
		<br />
	<input type="submit" value="Modifier"/> 
<?php
echo
	$core->formNonce().
	form::hidden(array('p'),'httpPassword').
	form::hidden(array('httppasswordaction'),'auth_message');
?>	</p>
	</form>
</td>
<td>
	<div<?php echo $form_block; ?>>
	<form method="post">
	<p><textarea name="txt" rows="30" cols="30">
<?php
foreach(array_keys($u) as $login)
	echo "$login\n";
?>
	</textarea><br />
	<input type="submit" value="Modifier" /> 
<?php
echo
	$core->formNonce().
	form::hidden(array('p'),'httpPassword').
	form::hidden(array('httppasswordaction'),'mod');
?>	</p>
	</form>
	</div>
</td></tr></table>
</div>

<div id="histo" class="multi-part"
	title="Historique des derni&egrave;res connexions">
	<p>Nous sommes le <?php echo date('d-m-Y H:i'); ?></p>
	<table>
<?php
if (count($httpPasswordLastLogin)>0) {
	$i = 0;
	$logins = array_keys($httpPasswordLastLogin);
	sort($logins);
	foreach($logins as $login)
		echo '<tr style="row-' .
			(($i++ % 2 == 0) ? 'odd' : 'even') .
			'"><td>' . $login . '</td><td>' .
			$httpPasswordLastLogin[$login] .
			'</td></tr>' . "\n";
}
?>
	</table>
</div>

<div id="aide" class="multi-part" title="Aide">
	<h3>Gestion des acc&egrave;s restreints</h3>
	<p>Ce plugin permet la gestion d'identifiants et de mots de
	passe pour limiter les acc&egrave;s &agrave; votre blog aux
	personnes que vous aurez choisies.</p>
	<p>Le formulaire de droite pr&eacute;sente la liste des
	utilisateurs existants (sans leur mot de passe)</p>
	<h3>Ajout d'un utilisateur</h3>
	<p>Pour ajouter un utilisateur, ajouter une nouvelle ligne
	de la forme:</p>
	<p class="fp-code"><tt>login:motdepasse</tt></p>
	<h3>Modifier un mot de passe</h3>
	<p>Pour modifier un mot de passe d'un utilisateur, ajouter
	&agrave; la suite de son identifiant (sur la m&ecirc;me ligne)
	le texte suivant:</p>
	<p class="fp-code"><tt>:motdepasse</tt></p>
	<h3>Suppression d'un utilisateur</h3>
	<p>Pour supprimer un utilisateur, supprimer la ligne de
	l'utilisateur.</p>
</div>

<div id="credits" class="multi-part" title="Cr&eacute;dits">
<h3>Plugin</h3>
<ul>
<li><a href="http://frederic.ple.name/DC2-plugin-httpPassword">
	Plugin Dotclear httpPassword</a></li>
<li>Ce plugin est distribu&eacute; sous licence GPLv2</li>
</ul>

<h3>D&eacute;veloppeur</h3>
<ul>
<li>Fr&eacute;d&eacute;ric PL&Eacute; &lt;dotclear@frederic.ple.name&gt;</li>
</ul>
<h3>Remerciements</h3>
<ul>
<li>Aux d&eacute;veloppeurs de Dotclear pour la grande qualit&eacute; du code</li>
<li>A Tomtom33, Moe, et les autres qui m'ont aid&eacute; sur le
<a href="http://forum.dotclear.net/">forum</a></li>
<li>A Pep de <a href="http://www.dotaddict.org/">Dotaddict</a></li>
<li>Stephanie "piloue" pour ses tests, ses suggestions et sa patience</li>
<li>Gabriel Recope pour ses tests et reports de bugs.</li>
</ul>

<div style="text-align: right; font-size: 0.8em; border-top: dashed 3px #d0d0d0; padding: 2px 10px 0 0;margin-top: 15px;">Plugin r&eacute;alis&eacute; v.<?php echo $core->getVersion('httpPassword'); ?> par <a href="http://frederic.ple.name/" style="text-decoration: none; color: black;">Fr&eacute;d&eacute;ric PL&Eacute;</a>
 &lt;dotclear@frederic.ple.name&gt;</div>
</div>

</body>
</html>
