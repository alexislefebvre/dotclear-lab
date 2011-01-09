<?php

// librairies
require_once dirname(__FILE__).'/class.annuaire.admin.php';

$default_tab = 'annuaire_list';
$page_name = __('Directory');
$category_id = null;

// test d'installation du plugin
$installed = true;
if (dcAnnuaire::isInstalled() === FALSE) {
	$installed = false;
	$default_tab = 'annuaire_install';
}

// suppression de categories
if (!empty($_POST['do_remove'])) {
	try 	{
		$n = 0;
		foreach (array_keys($_POST['category']) as $category_id) {
			if (dcAnnuaire::delete($category_id) === TRUE)
				$n++;
		}
		
		if ($n <= 0)
			$msg = __('No category deleted');
		else
			$msg = __('Category(s) successfully deleted');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// installation
else if (!empty($_GET['op']) && $_GET['op'] == 'install') {
	if (dcAnnuaireAdmin::Install() === TRUE)
		$msg = __('Sucessfull install');
	else
		$msg = __('Error install');

	$redir = "plugin.php?p=annuaire&annuaire_msg=".rawurlencode($msg);
	http::redirect($redir);			
}

// désinstallation
else if (!empty($_GET['op']) && $_GET['op'] == 'uninstall') {
	dcAnnuaireAdmin::Uninstall();

	$redir = "?";
	http::redirect($redir);			
}

// édition de categorie
else if (!empty($_GET['op']) && $_GET['op'] == 'edit' && !empty($_GET['id'])) {
	$default_tab = 'annuaire_edit';
	$category_id = (integer) $_GET['id'];
}

// mise à jour de categorie
else if (!empty($_POST['do_update'])) {
	try {
		$id = $_POST['category_id'];
		$title = $_POST['title'];
				
		if (dcAnnuaire::update($id, $title) === TRUE)
			$msg = __('Category updated');
		else
			$msg = __('Category not updated');
		
		$redir = "plugin.php?p=annuaire&annuaire_msg=".rawurlencode($msg);
		http::redirect($redir);			
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// ajout de categorie
else if (!empty($_POST['do_add'])) {
	try {
		$title = $_POST['title'];
				
		if (dcAnnuaire::add($title) === TRUE)
			$msg = __('Category created');
		else
			$msg = __('Category not created');
		
		$redir = "plugin.php?p=annuaire&annuaire_msg=".rawurlencode($msg);
		http::redirect($redir);			
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// message à afficher (en cas de redirection)
if (!empty($_GET['annuaire_msg']))
	$msg = rawurldecode($_GET['annuaire_msg']);

?>
<html>
<head>
	<title><?php echo __('Directory'); ?></title>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
</head>
<body>
<?php echo '<h2>' . $page_name . '</h2>'; ?>
<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<div class="multi-part" id="annuaire_install" title="<?php echo __('Install'); ?>">
	<?php dcAnnuaireAdmin::displayTabInstall(); ?>
</div>


<div class="multi-part" id="annuaire_list" title="<?php echo __('Categories list'); ?>">
	<?php dcAnnuaireAdmin::displayTabList(); ?>
</div>

<div class="multi-part" id="annuaire_add" title="<?php echo __('Add category'); ?>">
	<?php dcAnnuaireAdmin::displayTabAdd(); ?>
</div>

<div class="multi-part" id="annuaire_edit" title="<?php echo __('Edit category'); ?>">
	<?php 	dcAnnuaireAdmin::displayTabEdit($category_id);	?>
</div>

</body>
</html>