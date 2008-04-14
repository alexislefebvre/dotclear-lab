<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2008 Bruno Hondelatte,  and contributors. 
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
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

require DC_ROOT.'/inc/admin/lib.pager.php';
require dirname(__FILE__).'/class.mymetalists.php';

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
	http::redirect('plugin.php?p=mymeta');
	exit;
}
	

?>
<html>
<head>
  <title><?php echo __('My Metadata'); ?></title>
  <?php echo dcPage::jsPageTabs('mymeta').
  	dcPage::jsLoad('index.php?pf=mymeta/js/_meta_lists.js');

  ?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php echo __('My Metadata').' > '.__('Main menu'); ?></h2>
<?php

echo '<div class="multi-part" id="mymeta" title="'.__('My Metadata').'">';

echo '<p><a href="plugin.php?p=mymeta&amp;m=edit">'.__('New Metadata').'</a></p>';

$mymeta = new myMeta($core);
$combo_action = array();
$combo_action[__('enable')] = 'enable';
$combo_action[__('disable')] = 'disable';
$combo_action[__('delete')] = 'delete';

if (!$core->error->flag()) {
	$mymeta_list = new adminMymetaList($core,$mymeta->getAll());
	$mymeta_list->display(1,30,
        '<form action="plugin.php?p=mymeta" method="post" id="form-entries">'.
        '%s'.
        '<div class="two-cols">'.
        '<p class="col checkboxes-helpers"></p>'.
        '<p class="col right">'.__('Selected metas action:').
        form::combo('action',$combo_action).
        '<input type="submit" value="'.__('ok').'" />'.
        $core->formNonce().'</p>'.
        '</div>'.
        '</form>');

	
}
?>
<?php
	echo "</div>";
	#echo '<br/><p><a href="plugin.php?p=mymeta&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
?>
</body>
</html>
