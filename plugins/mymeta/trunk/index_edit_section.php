<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require DC_ROOT.'/inc/admin/lib.pager.php';

if (!empty($_POST['saveconfig'])) {
	$mymetaid = html::escapeHTML($_POST['mymeta_id']);
	$mymetaprompt = html::escapeHTML($_POST['mymeta_prompt']);

	$mymetaSection = $mymeta->getByID($mymetaid);
	if ($mymetaSection instanceof mymetaSection) {
		$mymetaSection->prompt = $mymetaprompt;
		$mymeta->update($mymetaSection);
		$mymeta->store();
	}
	http::redirect($p_url.'&status=secupd');
	exit;
}

if (array_key_exists('id',$_REQUEST)) {
	$page_title=__('Edit section');
	$mymetaid = $_REQUEST['id'];
	$mymetasection=$mymeta->getByID($_REQUEST['id']);
	if (!($mymetasection instanceof myMetaSection)) {
		http::redirect($p_url);
		exit;
	}

} else {
	http::redirect($p_url);
	exit;
}
?>

<html>
<head>
  <title><?php echo __('My metadata'); ?></title>
  <?php echo dcPage::jsPageTabs('mymeta');
  ?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php echo __('My Metadata').' > '.$page_title; ?></h2>
<?php
echo '<p><a href="plugin.php?p=mymeta" class="multi-part">'.__('My metadata').'</a></p>';
# echo '<p><a href="plugin.php?p=mymeta&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
echo '<div class="multi-part" id="mymeta" title="'.$page_title.'">';

if (!$core->error->flag()):?>
	<form method="post" action="plugin.php">
		<fieldset>
			<legend><?php echo __('MyMeta definition'); ?></legend>
			<p>
				<label class="required"><?php echo __('Title').' '; ?>
				<?php echo form::field('mymeta_prompt', 20, 255, $mymetasection->prompt, '',''); ?>
				</label>
			</p>
		</fieldset>
		<p>
			<input type="hidden" name="p" value="mymeta" />
			<input type="hidden" name="m" value="editsection" />
			<?php 
				echo form::hidden('mymeta_id',$mymetaid).
					$core->formNonce()
			?>
			<input type="submit" name="saveconfig" value="<?php echo __('Save'); ?>" />
		</p>
	</form>
	
<?php
endif;
?>
</div>
</body>
</html>
