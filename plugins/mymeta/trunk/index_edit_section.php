<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2009 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
	http::redirect('plugin.php?p=mymeta');
	exit;
}

if (array_key_exists('id',$_REQUEST)) {
	$page_title=__('Edit section');
	$mymetaid = $_REQUEST['id'];
	$mymetasection=$mymeta->getByID($_REQUEST['id']);
	if (!($mymetasection instanceof myMetaSection)) {
		http::redirect('plugin.php?p=mymeta');
		exit;
	}

} else {
	http::redirect('plugin.php?p=mymeta');
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
			<legend><?php echo __('Metadata definition'); ?></legend>
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
