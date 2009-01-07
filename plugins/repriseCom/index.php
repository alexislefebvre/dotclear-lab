<?php
/* 
--- BEGIN LICENSE BLOCK --- 
This file is part of repriseCom, a plugin for migrate comments 
for gallery from Dotclear1 to DotClear2.
Copyright (C) 2008 Benoit de Marne,  and contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
--- END LICENSE BLOCK ---
*/

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

require_once dirname(__FILE__).'/class.dc.repriseCom.php';

// initilisation des variables
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : null;

//$offset = !empty($_REQUEST['offset']) ? abs((integer) $_REQUEST['offset']) : 0;

$reprise = new dcRepriseCom($core);
$this_url = 'plugin.php?p=repriseCom';
$oldgallery = 'gallery sur dotclear 1';
$newgallery = 'gallery sur dotclear 2';
$step = 0;

$reprisecom_old_prefix= $core->blog->settings->reprisecom_old_prefix;
if ($reprisecom_old_prefix == null) {
	$reprisecom_old_prefix = $core->blog->prefix;
}

$reprisecom_limit_insert_nbcom= $core->blog->settings->reprisecom_limit_insert_nbcom;
if ($reprisecom_limit_insert_nbcom == null) {
	$reprisecom_limit_insert_nbcom = 120;
}


// Definition des traitements
if ($action == 'createreprisecom')
{
	// Creation des tables temporaires pour la reprise des commentaires
	try {
		$rs = $reprise->createTemporariesTable();
		http::redirect($p_url.'&createreprisecom=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
elseif ($action == 'updatetmp')
{
	// Préparation des tables temporaires
	try {
		$rs = $reprise->updateMedia_table_tmp();
		http::redirect($p_url.'&updatetmp=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
elseif ($action == 'insertcom')
{
	// Lancement de l'import des commentaires
	try {
		$rs = $reprise->importComments();
		//$rs = $core->countAllComments();
		http::redirect($p_url.'&insertcom=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
elseif ($action == 'nettoyagereprise')
{
	// Nettoyage des composants temporaires
	try {
		$rs = $reprise->deleteTemporariesTables();
		http::redirect($p_url.'&nettoyagereprise=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
elseif ($action == 'saveconfig')
{
	try {

		// définition du prefix des anciennes tables		
		$reprisecom_old_prefix = $_POST['reprisecom_old_prefix'];
		if (empty($_POST['reprisecom_old_prefix'])) {
		  throw new Exception(__('No old prefix table define.'));
		}

		// définition du nombre de commentaires à reprendre à chaque chargement
		$reprisecom_limit_insert_nbcom = $_POST['reprisecom_limit_insert_nbcom'];
		if (empty($_POST['reprisecom_limit_insert_nbcom'])) {
		  throw new Exception(__('Limit for insert not defined.'));
		}

		// Lancement de l'insertion
		$core->blog->settings->setNamespace('reprisecom');
		$core->blog->settings->put('reprisecom_old_prefix',$reprisecom_old_prefix,'string','Old prefix table');
		$core->blog->settings->put('reprisecom_limit_insert_nbcom',$reprisecom_limit_insert_nbcom,'string','Number of comments per insert');
		$core->blog->triggerBlog();

		http::redirect($p_url.'&saveconfig=1');
		
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

}

?>

<!-- Creation de la page HTML -->
<html>

<!-- Definition du header -->
<head>
  <title><?php echo __('RepriseCom'); ?></title>

  <?php
  		// Chargement des fonctions javascripts
		echo dcPage::jsLoad('index.php?pf=repriseCom/js/oXHR.js');
		echo dcPage::jsLoad('index.php?pf=repriseCom/js/_reprisecom.js');
	echo 
	'<script type="text/javascript">'."\n".
	"//<![CDATA[\n".
	"dotclear.msg.please_wait = '".html::escapeJS(__('Waiting...'))."';\n".
	"\n//]]>\n".
	"</script>\n";
  ?>	
  
</head>

<!-- Definition du body -->
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('RepriseCom'); ?></h2>
<h2><?php echo __('Reprise des commentaires du plugin '.$oldgallery.' vers '.$newgallery.''); ?></h2>

<fieldset>
	<legend><?php echo __('Information'); ?></legend>
	<?php
	// Notice d'information
	echo
	'<h3>'.__('Prerequisites').'</h3>'.
	'<p>'.
	''.__('Notice ligne 1').'<br>'.
	''.__('Notice ligne 2').'<br><br>'.
	'<b>'.__('The backup of database is recommended').'</b>'.
	'</p>';
	
	try {
		$reprise->printCounters();
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
	
	?>
</fieldset>

<fieldset>
	<legend><?php echo __('Message'); ?></legend>

<?php
if (!empty($_GET['createreprisecom'])) {
	echo '<p class="message">'.__('Create temporaries tables successful').'</p>';
}
if (!empty($_GET['updatetmp'])) {
	echo '<p class="message">'.__('Update temporaries tables successful').'</p>';
}
if (!empty($_GET['insertcom'])) {
	echo '<p class="message">'.__('Comments correctly inserted').'</p>';
}
if (!empty($_GET['nettoyagereprise'])) {
	echo '<p class="message">'.__('Cleaner correctly terminated').'</p>';
}
if (!empty($_GET['saveconfig'])) {
	echo '<p class="message">'.__('Save configuration successful').'</p>';
}
?>

    <div id="message"></div>

</fieldset>

<fieldset>
	<legend><?php echo __('List of treatments'); ?></legend>

	<?php
	
	echo
	'<h3>Step 1 : > '.__('Creating temporaries tables').'</h3>'.
	'<form action="plugin.php" method="post">'.
	'<p><input type="submit" value="'.__('Creating temporaries tables').'" onclick="affinfo(\''.__('Creating temporaries tables').'\')" /> '.
	$core->formNonce().
	form::hidden(array('action'),'createreprisecom').
	form::hidden(array('p'),'repriseCom').'</p>'.
	'</form>';
	
	echo
	'<h3>Step 2 : > '.__('Updating temporaries tables').'</h3>'.
	'<form action="plugin.php" method="post">'.
	'<p><input type="submit" value="'.__('Updating temporaries tables').'" onclick="affinfo(\''.__('Updating temporaries tables').'\')" /> '.
	$core->formNonce().
	form::hidden(array('action'),'updatetmp').
	form::hidden(array('p'),'repriseCom').'</p>'.
	'</form>';
	
	echo
	'<h3>Step 3 : > '.__('Insert old comments').'</h3>'.
	'<form action="plugin.php" method="post">'.
	'<p><input type="submit" value="'.__('Start insert command').'" onclick="affinfo(\''.__('Insert old comments').'\')" /> '.
	$core->formNonce().
	form::hidden(array('action'),'insertcom').
	form::hidden(array('p'),'repriseCom').'</p>'.
	'</form>';
	
	echo
	'<h3>Step 4 : > '.__('Cleaning temporary elements').'</h3>'.
	'<form action="plugin.php" method="post">'.
	'<p><input type="submit" value="'.__('Cleaner starts').'" onclick="affinfo(\''.__('Cleaning temporary elements').'\')" /> '.
	$core->formNonce().
	form::hidden(array('action'),'nettoyagereprise').
	form::hidden(array('p'),'repriseCom').'</p>'.
	'</form>';
	
	?>

<!--
	<div>
		<input type="button" value="start" onclick="loader()" />
	</div>
-->
</fieldset>

<!--
<fieldset>
	<legend><?php echo __('Result'); ?></legend>

    
    <div id="result"></div>
</fieldset>
-->

<?php

	# RepriseCom plugin configuration
	if ($core->auth->check('admin',$core->blog->id))
	{
		echo
		'<form method="post" action="plugin.php">'.
		'<fieldset>'.
		'<legend>'. __('General options').'</legend>'.
		
		'<p><label class="classic">'. __('old prefix table').' '.
		form::field('reprisecom_old_prefix', 30,256, $reprisecom_old_prefix).
		'</label></p>'.

		'<p><label class="classic">'. __('number comments limit for insert').' '.
		form::field('reprisecom_limit_insert_nbcom', 30,256, $reprisecom_limit_insert_nbcom).
		'</label></p>'.
		
		'<p><input type="submit" value="'.__('Save configuration').'" onclick="affinfo(\''.__('Save configuration').'\')" /> '.
		$core->formNonce().
		form::hidden(array('action'),'saveconfig').
		form::hidden(array('p'),'repriseCom').'</p>'.
		'</form>';
	}

?>

</body>
</html>