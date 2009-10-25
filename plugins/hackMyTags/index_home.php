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


if (!empty($_POST['action']) && !empty($_POST['entries']))
{
	$entries = $_POST['entries'];
	$action = $_POST['action'];
	if (preg_match('/^(enable|disable)$/',$action))
	{
		$dcHackMyTags->setEnabled($entries,($action==="enable"));
	}
	elseif (preg_match('/^(delete)$/',$action)) {
		$dcHackMyTags->delete($entries);
	}
	http::redirect('plugin.php?p=hackMyTags');
	exit;
}
	

$combo_action = array();
$combo_action[__('enable')] = 'enable';
$combo_action[__('disable')] = 'disable';
$combo_action[__('delete')] = 'delete';
?>
<html>
<head>
  <title><?php echo __('Hack My Tags'); ?></title>
  <?php echo dcPage::jsToolMan(); ?>
  <?php echo dcPage::jsPageTabs('mymeta');

  ?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php echo __('Hack My Tags').' > '.__('Main menu'); ?></h2>
<div class="multi-part" id="mymeta" title="<?php echo __('My tags hacks')?>">
<form method="post" action="plugin.php">
<?php 
echo '<p><a class="button" href="plugin.php?p=hackMyTags&m=edit">'.__('New tag hack').'</a></p>';
?>
</p>
</form>
<form action="plugin.php" method="post" id="hmt-form">
<table>
<thead>
<tr>
  <th colspan="2"><?php echo __('Tag'); ?></th>
  <th><?php echo __('Modes'); ?></th>
  <th><?php echo __('Status'); ?></th>
</tr>
</thead>
<tbody id="hmt-list">
<?php
$hacks = $dcHackMyTags->getAll();
foreach ($hacks as $hack) {
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		if ($hack->enabled) {
			$img_status = sprintf($img,__('published'),'check-on.png');
		} else {
			$img_status = sprintf($img,__('unpublished'),'check-off.png');
		}
		$modes = join(',',$hack->modes);
		if ($modes == '')
			$modes = __('all');
		if ($hack->type == 'v')
			$name=''.$hack->id.' : '.'{{tpl:'.html::escapeHTML($hack->tag).'}}';
		else
			$name=''.$hack->id.' : '.'&lt;tpl:'.html::escapeHTML($hack->tag).'&gt;';
			
		echo 
		'<tr class="line'.($hack->enabled ? '' : ' offline').'" id="hmt_'.$hack->id.'">'.
		'<td class="minimal">'.form::checkbox(array('entries[]'),$hack->id).'</td>'.
		'<td class="nowrap"><a href="plugin.php?p=hackMyTags&amp;m=edit&amp;id='.$hack->id.'">'.
		$name.'</a></td>'.
		'<td class="nowrap">'.$modes.'</td>'.
		'<td class="nowrap minimal">'.$img_status.'</td>'.
		'</tr>';
	}
?>
</tbody>
</table>
<p>
<?php
echo
	__('Selected hacks action:').
	form::combo('action',$combo_action).
	form::hidden('p','hackMyTags').
	$core->formNonce();
?>
<input type="submit" value="<?php echo __('ok'); ?>" />
</p>
</div>
</form>
</div>
</body>
</html>