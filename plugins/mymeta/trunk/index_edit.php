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

function filterTplFile($file,$default) {
	$f = trim($file);
	if (preg_match('#[/\\]#',$f) || $f == $default)
		return '';
	else
		return $f;
}


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
	$mymetaEntry->url_list_enabled = isset($_POST['enable_list']);
	$mymetaEntry->url_single_enabled = isset($_POST['enable_single']);
	$mymetaEntry->tpl_single = filterTplFile($_POST['single_tpl'],"mymeta.html");
	$mymetaEntry->tpl_list = filterTplFile($_POST['list_tpl'],"mymetas.html");
	
	$mymetaEntry->adminUpdate($_POST);
	$mymeta->update($mymetaEntry);
	$mymeta->store();
	http::redirect($p_url.'&status=mmupd');
	exit;
}

if (array_key_exists('id',$_REQUEST)) {
	$page_title=__('Edit MyMeta');
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
	$page_title=__('New MyMeta');
	$mymetaentry = $mymeta->newMyMeta($mymeta_type);
	$mymetaid = '';
	$lock_id=false;
}
$types = $mymeta->getTypesAsCombo();
$type_label = array_search ($mymeta_type,$types);
if (!$type_label)
	http::redirect($p_url);


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
			<legend><?php echo __('MyMeta definition'); ?></legend>
			<p>
				<label class="required"><?php echo __('Identifier (as stored in meta_type in database):').' '; ?>
				<?php echo form::field(array('mymeta_id'), 20, 255, $mymetaid, '','',$lock_id); ?>
				</label>
			</p>
			<p>
				<label><?php echo __('MyMeta type').' : '; ?>
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
			<legend><?php echo __('MyMeta URLs'); ?></legend>
				<?php 
				$base_url = $core->blog->url.$core->url->getBase("mymeta").'/'.$mymetaentry->id;
				$tpl_single = $mymetaentry->tpl_single;
				$tpl_list = $mymetaentry->tpl_list;
				echo 
					'<p><label class="classic">'.
					form::checkbox(array('enable_list'),1,$mymetaentry->url_list_enabled).
					__('Enable MyMeta values list public page').'</label><br />'.
					'<label class="classic">'.__('List template file (leave empty for default mymetas.html)').' : '.
					form::field(array('list_tpl'), 40, 255, empty($tpl_list)?'mymetas.html':$tpl_list).
					'</label></p>'.
					'<p><label class="classic">'.
					form::checkbox(array('enable_single'),1,$mymetaentry->url_single_enabled).
					__('Enable single mymeta value public page').
					'</label><br />'.
					'<label class="classic">'.__('Single template file (leave empty for default mymeta.html)').' : '.
					form::field(array('single_tpl'), 40, 255, empty($tpl_single)?'mymeta.html':$tpl_single).
					'</label></p>'; ?>
		</fieldset>
		<fieldset>
			<legend><?php echo __('MyMeta restrictions'); ?></legend>
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
