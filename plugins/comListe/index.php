<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of comListe, a plugin for Dotclear.
# 
# Copyright (c) 2008-2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

// initilisation des variables
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : null;

// Setting default parameters if missing configuration
if (is_null($core->blog->settings->comliste_enable)) {
	try {
		$core->blog->settings->setNameSpace('comListe');

		// Carnaval is not active by default
		$core->blog->settings->put('comliste_enable',false,'boolean','Enable comListe');
		$core->blog->triggerBlog();
		http::redirect(http::getSelfURI());
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// Getting current parameters
$comliste_enable = (boolean)$core->blog->settings->comliste_enable;
$comliste_page_title = $core->blog->settings->comliste_page_title;
$comliste_nb_comments_per_page = $core->blog->settings->comliste_nb_comments_per_page;
$comliste_comments_order = $core->blog->settings->comliste_comments_order;

if ($comliste_page_title === null) {
	$comliste_page_title = __('List of comments');
}
if ($comliste_nb_comments_per_page === null) {
	$comliste_nb_comments_per_page = 10;
}
if ($comliste_comments_order === null) {
	$comliste_comments_order = 'desc';
}

// Saving new configuration
if ($action == 'saveconfig')
{
	try {
		
		// Enable plugin
		$comliste_enable = (empty($_POST['comliste_enable']))?false:true;
		
		// Title page
		$comliste_page_title = $_POST['comliste_page_title'];
		if (empty($_POST['comliste_page_title'])) {
		  throw new Exception(__('No page title.'));
		}

		//  Number of comments per page
		$comliste_nb_comments_per_page = !empty($_POST['comliste_nb_comments_per_page'])?$_POST['comliste_nb_comments_per_page']:$comliste_nb_comments_per_page;
		
		// Order
		$comliste_comments_order = !empty($_POST['comliste_comments_order'])?$_POST['comliste_comments_order']:$comliste_comments_order;
		
		// Insert settings values
		$core->blog->settings->setNamespace('comListe');
		$core->blog->settings->put('comliste_enable',$comliste_enable,'boolean','Enable comListe');
		$core->blog->settings->put('comliste_page_title',$comliste_page_title,'string','Title page');
		$core->blog->settings->put('comliste_nb_comments_per_page',$comliste_nb_comments_per_page,'integer','Number of comments per page');
		$core->blog->settings->put('comliste_comments_order',$comliste_comments_order,'string','Comments order');
		
		$core->blog->triggerBlog();

		http::redirect($p_url.'&saveconfig=1');
		
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}	
}

?>

<!-- Creating HTML page -->
<html>

<!-- header -->
<head>
  <title><?php echo __('ComListe'); ?></title>
</head>

<!-- body -->
<body>

<?php
if (!empty($_GET['saveconfig'])) {
	echo '<p class="message">'.__('Save configuration successful').'</p>';
}
?>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('ComListe'); ?></h2>

<?php

$order_combo = array(__('Ascending') => 'asc',
__('Descending') => 'desc' );
	
	// comListe plugin configuration
	if ($core->auth->check('admin',$core->blog->id))
	{
		echo
		'<form method="post" action="plugin.php">'.
		'<fieldset><legend>'. __('Plugin activation').'</legend>'.
		'<p class="field">'.
		form::checkbox('comliste_enable', 1, $comliste_enable).
		'<label class=" classic" for="active">'.__('Enable comListe').'</label></p>'.
		'</fieldset>'.
		'<fieldset><legend>'. __('General options').'</legend>'.
		'<p><label class="classic">'. __('Title page').' : '.
		form::field('comliste_page_title', 30,256, $comliste_page_title).
		'</label></p>'.
		'<p><label class=" classic">'. __('Number of comments per page').' : '.
		form::field('comliste_nb_comments_per_page', 4, 4, $comliste_nb_comments_per_page).
		'</label></p>'.
		'<p><label class=" classic">'. __('Comments order').' : '.
		form::combo('comliste_comments_order', $order_combo, $comliste_comments_order).
		'</label></p>'.
		'</fieldset>'.
		'<p><input type="submit" value="'.__('Save configuration').'" onclick="affinfo(\''.__('Save configuration').'\')" /> '.
		$core->formNonce().
		form::hidden(array('action'),'saveconfig').
		form::hidden(array('p'),'comListe').'</p>'.
		'</form>';
	}

?>

<?php dcPage::helpBlock('comListe');?>

</body>
</html>