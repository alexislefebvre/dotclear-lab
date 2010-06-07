<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
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

if (!empty($_POST['mymeta_id'])) {
	$mymetaid = preg_replace('#[^a-zA-Z0-9_-]#','',$_POST['mymeta_id']);
	$mymetaEntry = $mymeta->newMyMeta($_POST['mymeta_type'],$mymetaid);
	$mymetaEntry->id = $mymetaid;
	$mymetaEntry->post_types = false;
	if (isset($_POST['mymeta_restrict']) && $_POST['mymeta_restrict']=='yes') {
		if (isset($_POST['mymeta_restricted_types'])) {
			$post_types = explode(',',$_POST['mymeta_restricted_types']);
			array_walk($post_types,create_function('&$v','$v=trim(html::escapeHTML($v));'));
			$mymetaEntry->post_types = $post_types;
		}
	}
	
	
	$mymetaEntry->adminUpdate($_POST);
	$mymeta->update($mymetaEntry);
	$mymeta->store();
	http::redirect('plugin.php?p=mymeta');
	exit;
}

if (array_key_exists('id',$_REQUEST)) {
	$page_title=__('Edit Metadata');
	$mymetaid = $_REQUEST['id'];
	$mymetaentry=$mymeta->getByID($_REQUEST['id']);
	if ($mymetaentry == null) {
		http::redirect($p_url);
		exit;
	}
	$mymeta_type = $mymetaentry->getMetaTypeId();
	$lock_id=true;
} elseif (!empty($_REQUEST['mymeta_type'])) {
	$mymeta_type = html::escapeHTML($_REQUEST['mymeta_type']);
	$page_title=__('New Metadata');
	$mymetaentry = $mymeta->newMyMeta($mymeta_type);
	$mymetaid = '';
	$lock_id=false;
}
$types = $mymeta->getTypesAsCombo();
$type_label = array_search ($mymeta_type,$types);
if (!$type_label)
	http::redirect('plugin.php?p=mymeta');


?>
<html>
<head>
  <title><?php echo __('My metadata'); ?></title>
  <?php echo dcPage::jsPageTabs('mymeta');
  ?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php echo __('My Metadata').' &gt; '.$page_title; ?></h2>
<?php
echo '<p><a href="plugin.php?p=mymeta" class="multi-part">'.__('My metadata').'</a></p>';
# echo '<p><a href="plugin.php?p=mymeta&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
echo '<div class="multi-part" id="mymeta" title="'.$page_title.'">';

if (!$core->error->flag()) {?>
	<form method="post" action="plugin.php">
		<fieldset>
			<legend><?php echo __('Metadata definition'); ?></legend>
			<p>
				<label class="required"><?php echo __('Identifier (as stored in meta_type in database):').' '; ?>
				<?php echo form::field(array('mymeta_id'), 20, 255, $mymetaid, '','',$lock_id); ?>
				</label>
			</p>
			<p>
				<label><?php echo __('Metadata type').' : '; ?>
				<?php echo __($mymeta_type); ?>
				</label>
			</p>
			<p>
				<label><?php echo __('Prompt').' : '; ?>
				<?php echo form::field(array('mymeta_prompt'), 40, 255, $mymetaentry->prompt); ?>
				</label>
			</p>
			<?php echo $mymetaentry->adminForm();?>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Metadata restrictions'); ?></legend>
			<p>
				<?php 
				echo '<label class="classic">'.form::radio(array('mymeta_restrict'),'none',$mymetaentry->isRestrictionEnabled()).
				__('Display meta field for any post type').'</label><br />';
				echo '<label class="classic">'.form::radio(array('mymeta_restrict'),'yes',!$mymetaentry->isRestrictionEnabled()).
				__('Restrict to the following post types :').'</label><br />';
				$restrictions = $mymetaentry->getRestrictions();
				echo form::field('mymeta_restricted_types', 40, 255, $restrictions?$restrictions:''); ?>
			</p>
		</fieldset>
		<p>
			<input type="hidden" name="p" value="mymeta" />
			<input type="hidden" name="m" value="edit" />
			<?php 
				if ($lock_id)
					echo form::hidden(array('mymeta_id'),$mymetaid);
				echo form::hidden(array('mymeta_enabled'),$mymetaentry->enabled);
				echo form::hidden(array('mymeta_type'),$mymeta_type);
				echo $core->formNonce()
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
