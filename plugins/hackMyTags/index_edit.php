<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hackMyTags,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }


if (array_key_exists('id',$_REQUEST)) {
	$page_title=__('Edit Tag Hack');
	$hmtid = (integer) $_REQUEST['id'];
	$hack = $dcHackMyTags->getHack($hmtid);
} else {
	$page_title=__('New Tag Hack');
	$hmtid = -1;
	$hack = $dcHackMyTags->newHack();
}

if (!empty($_POST['hmt_tag'])) {
	$hack->id = (integer)($_REQUEST['id']);
	$hack->tag = html::escapeHTML($_REQUEST['hmt_tag']);
	$hack->type = html::escapeHTML($_REQUEST['hmt_type']);
	$hack->override = !empty($_REQUEST['hmt_override']);
	$hack->attr = $dcHackMyTags->valuesToArray(html::escapeHTML($_REQUEST['hmt_attr']));
	$modes=html::escapeHTML($_REQUEST['hmt_modes']);
	$hack->modes = array();
	if (trim($modes) != '') {
		foreach (explode(',',$modes) as $mode)
			$hack->modes[] = trim($mode);
	}
	
	$dcHackMyTags->updateHack($hack);
}

$hmt_types_combo = array (__('Block') => 'b', __('Value') => 'v');

?>
<html>
<head>
  <title><?php echo __('Hack My Tags'); ?></title>
  <?php echo dcPage::jsPageTabs('hmt');
  ?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php echo __('Hack My Tags').' > '.$page_title; ?></h2>
<?php
echo '<p><a href="plugin.php?p=hackMyTags" class="multi-part">'.__('Hack My Tags').'</a></p>';
echo '<div class="multi-part" id="hmt" title="'.$page_title.'">';

if (!$core->error->flag()) {?>
	<form method="post" action="plugin.php">
		<fieldset>
			<legend><?php echo __('Hack definition'); ?></legend>
			<p>
				<label class="required"><?php echo __('Tag to hack (omit "tpl:" and block markers)'); ?>
				<?php echo form::field('hmt_tag', 20, 255, $hack->tag); ?>
				</label>
			</p>
			<p>
				<label class="required"><?php echo __('Tag type'); ?>
				<?php echo form::combo('hmt_type', $hmt_types_combo, $hack->type); ?>
				</label>
			</p>
			<p>
				<label class="required"><?php echo __('Override attributes if present'); ?>
				<?php echo form::checkbox('hmt_override', 1 , $hack->override); ?>
				</label>
			</p>
			<p>
				<label class="required"><?php echo __('Attributes to override (use 1 line per attribute, each line containing attribute=value; do not mention quotes in values. Example : selected=1)'); ?>
				<?php echo form::textArea('hmt_attr', 40,10 , $dcHackMyTags->arrayToValues($hack->attr)); ?>
				</label>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Conditions'); ?></legend>
				
			<p>
				<label><?php echo __('Allowed modes (comma separated values, use default for home template, specify no value for all modes)'); ?>
				<?php echo form::field('hmt_modes', 40,255, join(",",$hack->modes)); ?>
				</label>
			</p>
			<p>
				<label><?php echo __('Position ranges to allow'); ?>
				<?php echo form::field('hmt_cond', 40,255, $hack->cond['position']); ?>
				</label>
			</p>
			<?php
				echo "<p>".__('Enter coma-separated ranges for position of tag in file to apply')."</p>".
					"<p>".__('Examples')."</p>".
					"<ul><li>1- (".__("or blank").") : ".__("will hack all tags occurrences in the file.")."</li>".
					"<li>1,3  : ".__("will hack 1st and 3rd occurrences in the file.")."</li>".
					"<li>1,2-5,8- : ".__("will hack 1st, 2nd to 5th and any occurrence after 8th occurrence in the file.")."</li></ul>";
			?>
			
		</fieldset>
		<p>
			<input type="hidden" name="p" value="hackMyTags" />
			<input type="hidden" name="m" value="edit" />
			<?php 
				if ($hmtid != -1)
					echo form::hidden('id',$hmtid);
				echo $core->formNonce();
			?>
			<input type="submit" name="saveconfig" value="<?php echo __('Save'); ?>" />
		</p>
	</form>
	
<?php
}
	echo "</div>";
?>
</body>
</html>