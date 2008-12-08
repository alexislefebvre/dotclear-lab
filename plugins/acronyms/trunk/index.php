<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of acronyms, a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$acronyms = new dcAcronyms($core);
$acronyms_list = $acronyms->getList();

$a_acro = '';
$a_title = '';

# modfication de la liste des acronymes
if (isset($_POST['p_edit']))
{
	$post_array = array_map('trim', $_POST);

	$acronyms_list = array();
	foreach ($post_array as $nk=>$nv)
	{
		$prefix = substr($nk, 0, 8);
		if (($prefix == 'acronym_') && ($nv != '')) {
			$acronyms_list[substr($nk, 8)] = $nv;
		}
	}
	ksort($acronyms_list);

	$acronyms->writeFile($acronyms_list);
	http::redirect($p_url.'&edited=1');
}

# ajout d'un acronyme
if (!empty($_POST['p_add']))
{
	try
	{
		$a_acro = !empty($_POST['a_acro']) ? trim($_POST['a_acro']) : '';
		$a_title = !empty($_POST['a_title']) ? trim($_POST['a_title']) : '';
	
		if ($a_acro == '') {
			throw new Exception(__('You must give an acronym'));
		}
	
		if ($a_title == '') {
			throw new Exception(__('You must give a title'));
		}
	
		if (isset($acronyms_list[$a_acro])) {
			throw new Exception(__('This acronym already exists'));
		}
		
		$acronyms_list[$a_acro] = $a_title;
		ksort($acronyms_list);
	
		$acronyms->writeFile($acronyms_list);
		http::redirect($p_url.'&added=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
	<title><?php echo __('Acronyms Manager'); ?></title>
	<style type="text/css">
	.acroleft { display:inline; width: 20%; float: left; padding: 0; margin: 0; }
	.acroright { display:inline; width: 75%; padding: 0; margin: 0; }
	#listacro { height:200px; overflow:auto; }
	</style>
</head>
<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Acronyms Manager'); ?></h2>

<?php
if (!empty($_GET['edited'])) {
	echo '<p class="message">'.__('Acronyms list successfully updated.').'</p>';
}
if (!empty($_GET['added'])) {
	echo '<p class="message">'.__('Acronym successfully added.').'</p>';
}
?>

<form id="add_acronyms" action="plugin.php" method="post">
	<fieldset>
		<legend><?php echo __('Add an acronym'); ?></legend>

		<p class="acroleft"><label for="a_acro"><?php echo __('Acronym'); ?></label>
		<?php echo form::field('a_acro',10,'',$a_acro,'',''); ?></p>
	
		<p class="acroright"><label for="a_title"><?php echo __('Title'); ?></label>
		<?php echo form::field('a_title',60,'',$a_title,'',''); ?></p>

	</fieldset>
	<p class="clear"><?php echo form::hidden('p_add', '1');
	echo form::hidden(array('p'),'acronyms');
	echo $core->formNonce(); ?>
	<input type="submit" class="submit" value="<?php echo __('Add'); ?>" /></p>
</form>

<form id="edit_acronyms" action="plugin.php" method="post">
	<fieldset>
		<legend><?php echo __('Edit acronyms'); ?></legend>
		<div id="listacro">
		<?php
		foreach ($acronyms_list as $k=>$v)
		{
			echo
			'<p class="field">'."\n".
			'<label for="acronym_'.$k.'"><acronym title="'.$v.'">'.html::escapeHTML($k).'</acronym></label>'."\n".
			form::field('acronym_'.$k,60,'',html::escapeHTML($v))."\n".
			'</p>'."\n\n";
		}
		?>
		</fieldset>
		<p class="clear"><?php echo form::hidden('p_edit', '1');
		echo form::hidden(array('p'),'acronyms');
		echo $core->formNonce(); ?>
		<input type="submit" class="submit" value="<?php echo __('Edit'); ?>" /></p>
	</form>

	<p><strong><?php echo __('Note'); ?> :</strong> <?php echo __('To remove an acronym, just empty its title.'); ?></p>
</body>
</html>
