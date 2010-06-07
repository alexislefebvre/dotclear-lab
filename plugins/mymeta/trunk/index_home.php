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
#require dirname(__FILE__).'/class.mymetalists.php';

$mymeta = new myMeta($core);
$dcmeta = new dcMeta($core);
if (!empty($_POST['action']) && !empty($_POST['entries']))
{
	$entries = $_POST['entries'];
	$action = $_POST['action'];
	if (preg_match('/^(enable|disable)$/',$action))
	{
		echo "set enable :".$action.":".$entries;
		$mymeta->setEnabled($entries,($action==="enable"));
	}
	elseif (preg_match('/^(delete)$/',$action)) {
		$mymeta->delete($entries);
	}
	$mymeta->store();
	http::redirect('plugin.php?p=mymeta');
	exit;
}
if (!empty($_POST['newsep']) && !empty($_POST['mymeta_section'])) {
	$section = $mymeta->newSection();
	$section->prompt = html::escapeHTML($_POST['mymeta_section']);
	$mymeta->update($section);
	$mymeta->store();
	http::redirect('plugin.php?p=mymeta');
	exit;
}
	
# Order links
$order = array();
if (empty($_POST['mymeta_order']) && !empty($_POST['order'])) {
	$order = $_POST['order'];
	asort($order);
	$order = array_keys($order);
} elseif (!empty($_POST['mymeta_order'])) {
	$order = explode(',',$_POST['mymeta_order']);
}

if (!empty($_POST['saveorder']) && !empty($order))
{
	$mymeta->reorder($order);
	$mymeta->store();

	http::redirect($p_url.'&neworder=1');
}
$types = $mymeta->getTypesAsCombo();


$combo_action = array();
$combo_action[__('enable')] = 'enable';
$combo_action[__('disable')] = 'disable';
$combo_action[__('delete')] = 'delete';
?>
<html>
<head>
  <title><?php echo __('My Metadata'); ?></title>
  <?php echo dcPage::jsToolMan(); ?>
  <?php echo dcPage::jsPageTabs('mymeta').
  	dcPage::jsLoad('index.php?pf=mymeta/js/_meta_lists.js');

  ?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php echo __('My Metadata').' > '.__('Main menu'); ?></h2>
<div class="multi-part" id="mymeta" title="<?php echo __('My Metadata')?>">
<form method="post" action="plugin.php">
<?php 
echo '<p>'.__('New Metadata').' : '.
form::combo('mymeta_type', $types,'').
'&nbsp;<input type="submit" name="new" value="'.__('Create Metadata').'" />'.
form::hidden(array('p'),'mymeta').
form::hidden(array('m'),'edit').$core->formNonce();
?>
</p>
</form>
<form method="post" action="plugin.php">
<?php 
echo '<p>'.__('New section').' : '.
form::field('mymeta_section', '','').
'&nbsp;<input type="submit" name="newsep" value="'.__('Create section').'" />'.
form::hidden(array('p'),'mymeta').
$core->formNonce();
?>
</p>
</form>
<form action="plugin.php" method="post" id="mymeta-form">
<table class="dragable">
<thead>
<tr>
  <th colspan="4"><?php echo __('ID'); ?></th>
  <th><?php echo __('Type'); ?></th>
  <th><?php echo __('Prompt'); ?></th>  
  <th><?php echo __('Post types'); ?></th>
  <th><?php echo __('Number of Posts'); ?></th>
  <th colspan="2"><?php echo __('Status'); ?></th>
</tr>
</thead>
<tbody id="mymeta-list">
<?php
$metaStat = $mymeta->getMyMetaStats();
$stats = array();
while ($metaStat->fetch()) {
	$stats[$metaStat->meta_type]=$metaStat->count;
}

$allMeta = $mymeta->getAll();
foreach ($allMeta as $meta) {
	if ($meta instanceof myMetaSection) {
		echo 
		'<tr class="line" id="l_'.$meta->id.'">'.
		 '<td class="handle minimal">'.
		form::field(array('order['.$meta->id.']'),2,5,$meta->pos).'</td>'.
		'<td class="minimal">'.form::checkbox(array('entries[]'),$meta->id).'</td>'.
		'<td class="nowrap minimal"><a href="plugin.php?p=mymeta&amp;m=editsection&amp;id='.$meta->id.'">'.
		'<img src="images/menu/edit.png" alt="'.__('edit Metadata').'" /></td>'.
		'<td class="nowrap" colspan="6">'.
		'<strong>Section: '.html::escapeHTML($meta->prompt).'</strong></td>'.
		'</tr>';
	} else {
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		if ($meta->enabled) {
			$img_status = sprintf($img,__('published'),'check-on.png');
		} else {
			$img_status = sprintf($img,__('unpublished'),'check-off.png');
		}
		$st = (isset($stats[$meta->id]))?$stats[$meta->id]:0;
		$restrictions = $meta->getRestrictions();
		if (!$restrictions)
			$restrictions=__('All');
		echo 
		'<tr class="line'.($meta->enabled ? '' : ' offline').'" id="l_'.$meta->id.'">'.
		 '<td class="handle minimal">'.
		form::field(array('order['.$meta->id.']'),2,5,$meta->pos).'</td>'.
		'<td class="minimal">'.form::checkbox(array('entries[]'),$meta->id).'</td>'.
		'<td class="nowrap minimal"><a href="plugin.php?p=mymeta&amp;m=edit&amp;id='.$meta->id.'">'.
		'<img src="images/menu/edit.png" alt="'.__('edit Metadata').'" /></td>'.
		'<td class="nowrap"><a href="plugin.php?p=mymeta&amp;m=view&amp;id='.$meta->id.'">'.
		html::escapeHTML($meta->id).'</a></td>'.
		'<td class="nowrap">'.$meta->getMetaTypeDesc().'</td>'.
		'<td class="nowrap">'.$meta->prompt.'</td>'.
		'<td>'.$restrictions.'</td><td class="nowrap">'.
		$st.' '.(($st<=1)?__('entry'):__('entries')).'</td>'.
		'<td class="nowrap minimal">'.$img_status.'</td>'.
		'</tr>';
	}
}
?>
</tbody>
</table>
<div class="two-cols">
<p class="col">
<?php 
	echo form::hidden('mymeta_order','');
	echo form::hidden(array('p'),'mymeta');
	echo $core->formNonce();
?>
<input type="submit" name="saveorder" value="<?php echo __('Save order'); ?>" />
</p>
<p class="col right">
<?php
echo
	__('Selected metas action:').
	form::combo('action',$combo_action);
?>
<input type="submit" value="<?php echo __('ok'); ?>" />
</p>
</div>
</form>
</div>
</body>
</html>
