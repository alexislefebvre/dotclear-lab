<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of contextualMenu, a plugin for Dotclear.
# 
# Copyright (c) 2008 Frédéric Leroy
# bestofrisk@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

# Chemins des fichiers
$custom_file = dirname(__FILE__).'/template/custom_template.html';
$default_file = dirname(__FILE__).'/template/default_template.html';

# Si il n'y a pas de fichier custom on le créé à partir du fichier default
if (!file_exists($custom_file)) {
	if (!file_exists($default_file)) { // le fichier default n'existe pas non plus
		http::redirect($p_url.'&nofile=1');
	} else {
		copy($default_file, $custom_file);
		http::redirect($p_url.'&advanced_settings=1&custom_file_created=1');
	}
}

# Sauvegarde template si modifié
if (isset($_POST['template']) && ($_POST['template'] != '')) {
	$template = $_POST['template'];
	$ouvre=fopen($custom_file,"w+"); // ouverture en écriture
	fwrite($ouvre,$template);    // écriture fichier
	fclose($ouvre);			// fermeture fichier
	http::redirect($p_url.'&advanced_settings=1&custom_file_updated=1');
}

// Lecture du template
$template = '';
$fichier=fopen($custom_file, "r");
$i=1;
while (!feof($fichier)) {
	$template .= fgets($fichier);
	$i++;
}
fclose($fichier);


?>
<html>
<head>
  <title>Contextual Menu</title>
</head>

<body>
<?php 

if (!empty($_GET['custom_file_created'])) {
		echo '<p class="message">'.__('No custom template file found!<br />A custom template file has been created from the default template file.').'</p>';
}

if (!empty($_GET['custom_file_updated'])) {
		echo '<p class="message">'.__('The custom template file has been successfully updated').'</p>';
}

echo '<p><a href="'.$p_url.'">'.__('Return to menu').'</a></p>';

echo
	'<form action="'.$p_url.'&advanced_settings=1" method="post">'.
	'<fieldset><legend>'.__('Edit Template').'</legend>'.
	
	'<table class="noborder">'.
	
	'<tr>'.
	'<th><p><label class="required classic" title="'.__('Required field').'">'.__('Template:').'</p></th>'.
	'<td><p>'.
	form::textArea('template',150,15,html::escapeHTML($template)).'</label> '.	
	$core->formNonce().
	'</p></td>'.
	'</tr>'.
	
	'<tr>'.
	'<th></th>'.
	'<td><p>'.
	'<input type="submit" name="advanced_settings" class="submit" value="'.__('save').'"/>'.
	'</p></td>'.	
	'</tr>'.	
	
	'</table>'.
	
	'</fieldset>'.
	'</form>';

?>

</body>
</html>