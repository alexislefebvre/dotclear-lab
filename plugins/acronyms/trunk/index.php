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
if (!empty($_POST['p_edit']))
{
	$p_acronyms = !empty($_POST['p_acronyms']) && is_array($_POST['p_acronyms']) ? array_map('trim', $_POST['p_acronyms']) : array();

	$acronyms_list = array();
	foreach ($p_acronyms as $nk=>$nv)
	{
		if ($nv != '') {
			$acronyms_list[$nk] = $nv;
		}
		else {
			unset($acronyms_list[$nk]);
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
	#add_acronyms fieldset { position:relative; }
	.acroleft { display:block; width:14em; }
	.acroright { display:inline; left:15em; position:absolute; top:43px; }
	#listacro { height:200px; overflow:auto; }
	</style>
	<?php echo dcPage::jsModal(); ?>
	<script type="text/javascript">
	//<![CDATA[
	$(function() {
		$('#post-preview').modalWeb($(window).width()-40,$(window).height()-40);
	});
	//]]>
	</script>
</head>
<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Acronyms Manager'); ?>
<?php if ($core->blog->settings->acronyms->acronyms_public_enabled) {
	echo ' - <a id="post-preview" href="'.$core->blog->url.$core->url->getBase('acronyms').'" class="button">'.__('View the acronyms page').'</a>';
} ?></h2>

<?php
if (!empty($_GET['edited'])) {
	echo '<p class="message">'.__('Acronyms list successfully updated.').'</p>';
}
if (!empty($_GET['added'])) {
	echo '<p class="message">'.__('Acronym successfully added.').'</p>';
}
?>

<form id="edit_acronyms" action="plugin.php" method="post">
	<fieldset>
		<legend><?php echo __('Edit acronyms'); ?></legend>
		<div id="listacro">
		<?php
		$i = 1;
		foreach ($acronyms_list as $k=>$v)
		{
			echo
			'<p class="field">'."\n".
			'<label for="acronym_'.$i.'"><acronym title="'.$v.'">'.html::escapeHTML($k).'</acronym></label>'."\n".
			form::field(array('p_acronyms['.$k.']','acronym_'.$i),60,'',html::escapeHTML($v))."\n".
			'</p>'."\n\n";

			++$i;
		}
		?>
		</div><!-- #listacro -->
	</fieldset>
	<p class="clear"><?php echo form::hidden('p_edit', '1');
	echo form::hidden(array('p'),'acronyms');
	echo $core->formNonce(); ?>
	<input type="submit" class="submit" value="<?php echo __('Edit'); ?>" />
	</p>
</form>

<form id="add_acronyms" action="plugin.php" method="post">
	<fieldset>
		<legend><?php echo __('Add an acronym'); ?></legend>

		<p class="acroleft"><label for="a_acro"><?php echo __('Acronym'); ?></label>
		<?php echo form::field('a_acro',10,'',$a_acro,'',''); ?></p>

		<p class="acroright"><label for="a_title"><?php echo __('Entitled'); ?></label>
		<?php echo form::field('a_title',60,'',$a_title,'',''); ?></p>

	</fieldset>
	<p class="clear"><?php echo form::hidden('p_add', '1');
	echo form::hidden(array('p'),'acronyms');
	echo $core->formNonce(); ?>
	<input type="submit" class="submit" value="<?php echo __('Add'); ?>" /></p>
</form>
<?php dcPage::helpBlock('acronyms'); ?>
</body>
</html>
?>
