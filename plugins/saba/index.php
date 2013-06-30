<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of saba, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('amdin');

$filters_list = array(
	'options','orders','ages','categories','authors','types'
);

$core->blog->settings->addNamespace('saba');

$active = (boolean) $core->blog->settings->saba->active;
$filters = (string) $core->blog->settings->saba->filters;
$filters = @unserialize($filters);
if (!is_array($filters)) {
	$filters = array();
}


if (isset($_POST['save']))
{
	try
	{
		$active = !empty($_POST['active']);
		$filters = array();
		foreach($filters_list as $filter)
		{
			if (!empty($_POST['filter_'.$filter])) {
				$filters[] = $filter;
			}
		}
		
		$core->blog->settings->saba->put('active',$active,'boolean','Enable extension');
		$core->blog->settings->saba->put('filters',serialize($filters),'string','Diabled filters');
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=saba&done=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__("Search across blog's archive").'</title></head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <span class="page-title">'.__("Search across blog's archive").'</span></h2>'.
(!empty($_REQUEST['done']) ? dcPage::message(__('Configuration successfully updated')) : '').'
<form method="post" action="plugin.php">

<fieldset><legend>'.__('Settings').'</legend>

<p><label class="classic">'.
form::checkbox('active','1',$active).__('Enable extension').'</label></p>

</fieldset>
<fieldset><legend>'.__('Advanced search').'</legend>

<p><label class="classic">'.
form::checkbox('filter_options','1',in_array('options',$filters)).
__('Disable filter on post options').'</label></p>

<p><label class="classic">'.
form::checkbox('filter_orders','1',in_array('orders',$filters)).
__('Disable filter on order').'</label></p>

<p><label class="classic">'.
form::checkbox('filter_ages','1',in_array('ages',$filters)).
__('Disable filter on age').'</label></p>

<p><label class="classic">'.
form::checkbox('filter_categories','1',in_array('categories',$filters)).
__('Disable filter on categories').'</label></p>

<p><label class="classic">'.
form::checkbox('filter_authors','1',in_array('authors',$filters)).
__('Disable filter on authors').'</label></p>

<p><label class="classic">'.
form::checkbox('filter_types','1',in_array('types',$filters)).
__('Disable filter on post types').'</label></p>

</fieldset>

<p><input type="submit" name="save" value="'.__('Save').'" />'.
$core->formNonce().form::hidden(array('p'),'saba').'</p>
</form>
<br class="clear"/>
<p class="right">
<i>&ldquo; Saba (pronounced /ˈseɪbə/) is the smallest island of the Netherlands Antilles. &rdquo;</i><br />
saba - '.$core->plugins->moduleInfo('saba','version').'&nbsp;
<img alt="'.__("Search across blog's archive").'" src="index.php?pf=saba/icon.png" />
</p>';
dcPage::helpBlock('saba');
echo '</body></html>';
?>