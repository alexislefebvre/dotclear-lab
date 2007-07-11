<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
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

require dirname(__FILE__).'/../../inc/admin/lib.pager.php';
require dirname(__FILE__).'/class.dc.gallerylists.php';

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('publish')] = 'publish';
	$combo_action[__('unpublish')] = 'unpublish';
	$combo_action[__('schedule')] = 'schedule';
	$combo_action[__('mark as pending')] = 'pending';
}
$combo_action[__('change category')] = 'category';
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('change author')] = 'author';
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('delete')] = 'delete';
}

# --BEHAVIOR-- adminPostsActionsCombo
/*$core->callBehavior('adminPostsActionsCombo',array(&$combo_action));*/

$default_tab = 'gal_list';
?>
<html>
<head>
  <title><?php echo __('Galleries'); ?></title>
  <?php echo dcPage::jsPageTabs($default_tab).
  	dcPage::jsLoad('index.php?pf=gallery/js/_gals_lists.js')?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php echo __('Galleries').' > '.__('Main menu'); ?></h2>
<?php
echo '<div class="multi-part" id="gal_list" title="'.__('Galleries').'">';

echo '<p><a href="plugin.php?p=gallery&m=gal">'.__('New gallery').'</a></p>';

$params['post_type']='gal';

# Get posts
try {
	$gals = $core->blog->getPosts($params);
	$counter = $core->blog->getPosts($params,true);
	$gal_list = new adminGalleryList($core,$gals,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
$core->meta = new dcMeta($core);




$page=1;
if (!$core->error->flag()) {
	
	echo
	# Show posts
	$gal_list->display($page,30,
	'<form action="plugin.php?p=gallery&m=galsactions" method="post" id="form-entries">'.
	'%s'.
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.__('Selected entries action:').
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" />'.
	$core->formNonce().'</p>'.
	'</div>'.
	'</form>'
	);
}
?>
<?php
	echo "</div>";
	echo '<br/><p><a href="plugin.php?p=gallery&amp;m=items" class="multi-part">'.__('Images').'</a></p>';
	echo '<br/><p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';
?>
</body>
</html>
