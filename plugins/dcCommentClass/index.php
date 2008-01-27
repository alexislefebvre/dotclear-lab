<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'dcCommentClass', a plugin for Dotclear 2          *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'dcCommentClass' (see COPYING.txt);     *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$messages = $errors = array();

/* Initialisation (des paramètres du formulaire)
--------------------------------------------------- */

$u_name = '';

/* Enregistrement des données (si le forumaire vient d'être envoyé)
--------------------------------------------------- */

if (isset($_POST['action_addrule'])) {
	$u_name = $_POST['u_name'];
}

/* Traitement des requêtes
--------------------------------------------------- */

if (isset($_POST['action_addrule'])) {
	if (empty($u_name)) {
		$errors[] = 'Nom d\'utilisateur vide. Veuillez le renseigner !';
	}
	else {
		$messages[] = 'Vous avez choisi l\'utilisateur <strong>'.html::escapeHTML($u_name).'</strong>.';
	}
}

/* DISPLAY
--------------------------------------------------- */

# En-têtes du plugin
echo '
<html><head>
<title>'.'Commentaires à distinguer'.'</title>
</head><body>';

# Affichage des notifications, s'il y en a
if (!empty($messages)) {
	if (count($messages) < 2) {
		echo '	<p class="message">'.end($messages)."</p>\n";
	}
	else {
		echo '<ul class="message">';
		foreach ($messages as $message)
		{
			echo '	<li>'.$message."</li>\n";
		}
		echo "</ul>\n";
	}
}

# Affichage des erreurs, s'il y en a
if (!empty($errors)) {
	echo '<div class="error"><strong>'.__('Errors:').'</strong><ul>';
	foreach ($errors as $message)
	{
		echo '	<li>'.$message."</li>\n";
	}
	echo "</ul></div>\n";
}

# Affichage du formualire
echo '
<h2>'.__('Add a new rule').'</h2>
<form action="'.$p_url.'" method="post">
<p><label>'.__('User name:').' '.
	form::field('u_name','20','255',html::escapeHTML($u_name)).'</label></p>
<p><input type="submit" name="action_addrule" value="'.__('Add a new rule').'" />'.
	$core->formNonce().'</p>
</form>
</body></html>';
?>
