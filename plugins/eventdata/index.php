<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

# Main class
$E = new eventdata($core);

/** Init some values **/

# General
$msg = isset($_REQUEST['done']) ? __('Configuration saved') : '';
$img_green = '<img alt="%s" src="'.DC_ADMIN_URL.'?pf=eventdata/img/green.png" />';
$img_red = '<img alt="%s" src="'.DC_ADMIN_URL.'?pf=eventdata/img/red.png" />';
$img_orange = '<img alt="%s" src="'.DC_ADMIN_URL.'?pf=eventdata/img/orange.png" />';
$img_scheduled = '<img alt="%s" src="'.DC_ADMIN_URL.'?pf=eventdata/img/scheduled.png" />';

# Menu
$tab = array('about' => __('About'));
if ($E->checkPerm('pst')) $tab['pst'] = __('Entries');
if ($E->checkPerm('cat')) $tab['cat'] = __('Categories');
if ($E->checkPerm('tpl')) $tab['tpl'] = __('Templates');
if ($E->checkPerm('adm')) $tab['adm'] = __('Administration');
if ($core->auth->isSuperAdmin()) $tab['uninstall'] = __('Uninstall');

# Entries
$show_filters = false;
$user_id = !empty($_GET['user_id']) ? $_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ? $_GET['cat_id'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$selected = isset($_GET['selected']) ? $_GET['selected'] : '';
$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';
$period = !empty($_GET['period']) ? $_GET['period'] : '';

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) $show_filters = true;
	$nb_per_page = (integer) $_GET['nb'];
}

# Categories
$cats_reordered = @unserialize($E->S->event_tpl_cats);
if (!is_array($cats_reordered)) $cats_reordered = array();
$cats_unlisted = @unserialize($E->S->event_no_cats);
if (!is_array($cats_unlisted)) $cats_unlisted = array();

# Templates
$default_tpl = $default_thm = '';
$default_xst = false;

# Uninstall
$understand = isset($_POST['s']['understand']) ? $_POST['s']['understand'] : 0;
$delete_table = isset($_POST['s']['delete_table']) ? $_POST['s']['delete_table'] : 0;
$delete_templates = isset($_POST['s']['delete_templates']) ? $_POST['s']['delete_templates'] : 0;
$delete_settings = isset($_POST['s']['delete_settings']) ? $_POST['s']['delete_settings'] : 0;


/** Combo array **/

# Actions combo
$combo_action = array();
if ($core->auth->check('delete,contentadmin',$GLOBALS['core']->blog->id)) {
	$combo_action[__('remove events')] = 'event_remove';
}
if ($core->auth->check('publish,contentadmin',$core->blog->id)) {
	$combo_action[__('publish')] = 'publish';
	$combo_action[__('unpublish')] = 'unpublish';
	$combo_action[__('schedule')] = 'schedule';
	$combo_action[__('mark as pending')] = 'pending';
}
if ($E->checkPerm('pst')) {
	$combo_action[__('mark as selected')] = 'selected';
	$combo_action[__('mark as unselected')] = 'unselected';
}
$combo_action[__('change category')] = 'category';
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('change author')] = 'author';
}
if ($core->auth->check('delete,contentadmin',$core->blog->id)) {
	$combo_action[__('delete')] = 'delete';
}

# Categories combo
$categories_combo = array('-'=>'');
try {
	$categories = $core->blog->getCategories(array('post_type'=>'post')); //$categories also used by tab['cat']
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
while ($categories->fetch()) {
	$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
		html::escapeHTML($categories->cat_title)] = $categories->cat_id;
}

# Categories actions combo
$categories_actions_combo = array(
	__('Mark as reordered') => 'reorder_cats',
	__('Mark as not reordered') => 'unreorder_cats',
	__('Mark as unlisted') => 'unlist_cats',
	__('Mark as listed') => 'list_cats'
);

# Status combo
$status_combo = array('-' => '');
foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# Selected combo
$selected_combo = array(
'-' => '',
__('selected') => '1',
__('not selected') => '0'
);

# Sortby combo
$sortby_combo = array(
__('Date') => 'post_dt',
__('Event start') => 'event_start',
__('Event end') => 'event_end',
__('Title') => 'post_title',
__('Category') => 'cat_title',
__('Author') => 'user_id',
__('Status') => 'post_status',
__('Selected') => 'post_selected'
);

# Order combo
$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);

# Period combo
$period_combo = array(
'-' => '',
__('Not started') => 'notstarted',
__('Started') => 'started',
__('Finished') => 'finished',
__('Not finished') => 'notfinished',
__('Ongoing') => 'ongoing',
__('Outgoing') => 'outgoing'
);

# Templates Combo
foreach($E->getThemes() AS $k => $v) {
	if ($v['selected']) {
		$combo_templates[__('Current blog theme').' '.$v['name']] = '';
		$default_thm = $k;
		$default_adt = $v['template_exists'];
		$default_xst = !empty($v['theme_file']);
	} elseif ($v['template_file']) {
		$combo_templates[__('Plugin').' '.$v['name'].' ('.$k.')'] = $k;
	}
}


/** "Static" params **/

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;
$params['event_type'] = 'event';
$params['post_type'] = '';


/** Filters **/

# Categories filter
if ($cat_id !== '' && in_array($cat_id,$categories_combo)) {
	$params['cat_id'] = $cat_id;
	$show_filters = true;
}

# Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['post_status'] = $status;
	$show_filters = true;
}

# Selected filter
if ($selected !== '' && in_array($selected,$selected_combo)) {
	$params['post_selected'] = $selected;
	$show_filters = true;
}

# Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}	
	if ($sortby != 'post_dt' || $order != 'desc') {
		$show_filters = true;
	}
}

# Period filter
if ($period !== '' && in_array($period,$period_combo)) {
	$params['period'] = $period;
	$show_filters = true;
}


/** Display **/

# Default menu
$request_tab = isset($_REQUEST['t']) ? $_REQUEST['t'] : '';
if (!$E->S->event_option_active && empty($request_tab)) $request_tab = 'adm';
if ($E->S->event_option_active && empty($request_tab)) $request_tab = 'pst';
if (!array_key_exists($request_tab,$tab)) $request_tab = 'about';

echo 
'<html>'.
' <head>'.
'  <title>'.__('Events').'</title>'.
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsPageTabs($request_tab).
' </head>'.
' <body>'.
' <h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Events').' &gt; '.$tab[$request_tab].'</h2>'.
 (!empty($msg) ? '<p class="message">'.$msg.'</p>' : '');

/**************
** Entries
**************/

if (isset($tab['pst'])) {

	# Event entries list
	try {
		$posts = $E->getPostsByEvent($params);
		$counter = $E->getPostsByEvent($params,true);
		$post_list = new eventdataEventList($core,$posts,$counter->f(0));

	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	echo 
	'<div class="multi-part" id="pst" title="'.$tab['pst'].'">'.
	'<p>'.__('This is the list of all entries with event').'</p>';
	if (!$show_filters) { 
		echo dcPage::jsLoad('js/filter-controls.js').'<p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>';
	}
	echo '
	<form action="'.$E->url.'" method="get" id="filters-form">
	<fieldset><legend>'.__('Filters').'</legend>
		<div class="three-cols">
		<div class="col">
		<label>'.__('Category:').form::combo('cat_id',$categories_combo,$cat_id).'</label> 
		<label>'.__('Status:').form::combo('status',$status_combo,$status).'</label> 
		<label>'.__('Selected:').form::combo('selected',$selected_combo,$selected).'</label> 
		</div>
		<div class="col">
		<label>'.__('Order by:').form::combo('sortby',$sortby_combo,$sortby).'</label> 
		<label>'.__('Sort:').form::combo('order',$order_combo,$order).'</label>
		</div>
		<div class="col">
		<p><label>'.__('Period:').form::combo('period',$period_combo,$period).'</label></p> 
		<p><label class="classic">'.form::field('nb',3,3,$nb_per_page).' '.__('Entries per page').'</label> 
		<input type="submit" value="'.__('filter').'" /></p>
		</div>
		</div>
		<br class="clear" />
	</fieldset>'.
	form::hidden('p','eventdata').
	form::hidden('t','pst').
	$core->formNonce().'
	</form>';

	$post_list->display($page,$nb_per_page,
		'<form action="posts_actions.php" method="post" id="form-entries">'.
		'%s'.
		'<div class="two-cols">'.
		'<p class="col checkboxes-helpers"></p>'.
		'<p class="col right">'.__('Selected entries action:').' '.
		form::combo('action',$combo_action).
		'<input type="submit" value="'.__('ok').'" /></p>'.
		form::hidden(array('cat_id'),$cat_id).
		form::hidden(array('status'),$status).
		form::hidden(array('selected'),$selected).
		form::hidden(array('sortby'),$sortby).
		form::hidden(array('order'),$order).
		form::hidden(array('page'),$page).
		form::hidden(array('nb'),$nb_per_page).
		form::hidden(array('redir'),$E->url.'&t=pst').
		$core->formNonce().
		'</div>'.
		'</form>'
	);
	echo '</div>';
}

/**************
** Categories
**************/

if (isset($tab['cat'])) {

	# Save redirected categories list
	if (!empty($_POST['save']['cat'])) {
		try {

			if ($_POST['action'] == 'reorder_cats') $cats_reordered = array_merge($cats_reordered,$_POST['entries']);
			if ($_POST['action'] == 'unreorder_cats') $cats_reordered = array_diff($cats_reordered,$_POST['entries']);

			if ($_POST['action'] == 'unlist_cats') $cats_unlisted = array_merge($cats_unlisted,$_POST['entries']);
			if ($_POST['action'] == 'list_cats') $cats_unlisted = array_diff($cats_unlisted,$_POST['entries']);

			$s = array(
				'event_tpl_cats'=>serialize(array_unique($cats_reordered)),
				'event_no_cats'=>serialize(array_unique($cats_unlisted)),
			);

			$E->setSettings($s);

			http::redirect($E->url.'&t=cat&done=1');
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

	echo '
	<div class="multi-part" id="cat" title="'.$tab['cat'].'">
	<p>'.__('This is a list of all the categories that can be rearranged by dates of events').'</p>
	<form action="'.$E->url.'" method="post" id="form-entries">
	<table class="clear"><tr>
	<th colspan="2">'.__('Title').'</th>
	<th>'.__('Id').'</th>
	<th>'.__('Level').'</th>
	<th>'.__('Entries').'</th>
	<th>'.__('Reordered').'</th>
	<th>'.__('Unlisted').'</th>
	</tr>';
	while ($categories->fetch()) {
		echo
		'<tr class="line">'.
		'<td class="nowrap">'.form::checkbox(array('entries[]'),$categories->cat_id,'','','',false).'</td>'.
		'<td class="maximal"><a href="'.$E->url.'&t=pst&cat_id='.$categories->cat_id.'">
			'.html::escapeHTML($categories->cat_title).'</a></td>'.
		'<td class="nowrap">'.$categories->cat_id.'</td>'.
		'<td class="nowrap">'.$categories->level.'</td>'.
		'<td class="nowrap">'.$categories->nb_post.'</td>'.
		'<td class="nowrap">'.((in_array($categories->cat_id,$cats_reordered) || in_array($categories->cat_title,$cats_reordered)) ? 
			sprintf($img_green,__('Reordered')) : sprintf($img_red,__('Normal'))).
		'</td>'.
		'<td class="nowrap">'.((in_array($categories->cat_id,$cats_unlisted) || in_array($categories->cat_title,$cats_unlisted)) ? 
			sprintf($img_red,__('Unlisted')) : sprintf($img_green,__('Normal'))).
		'</td>'.
		'</tr>';
	}
	echo '
	</table>
	<div class="two-cols">
	<p class="col checkboxes-helpers"></p>
	<p class="col right">'.__('Selected categories action:').' '.
	form::combo('action',$categories_actions_combo).'
	<input type="submit" name="save[cat]" value="'.__('ok').'" /></p>'.
	form::hidden('p','eventdata').
	form::hidden('t','cat').
	$core->formNonce().'
	</p>
	</div>
	</form>
	</div>';
}

/**************
** Templates 
**************/

if (isset($tab['tpl'])) {

	# Save tpl options
	if (!empty($_POST['save']['tpl']) && isset($_POST['s'])) {
		try {
			$E->setSettings($_POST['s']);
			http::redirect($E->url.'&t=tpl&done=1');
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

	$default_tpl = in_array($E->S->event_tpl_theme,$combo_templates) ? $E->S->event_tpl_theme : '';

	echo '
	<div class="multi-part" id="tpl" title="'.$tab['tpl'].'">
	<p>'.__('This is the management of the public page').'</p>
	'.(!$E->S->event_option_public ? '<p class="error">'.__('Public page is disable').'</p>' : '').'
	<form method="post" action="'.$E->url.'">
	<h2>'.__('Description').'</h2>
		<p class="col"><label class=" classic">'.
			__('Title').'<br />'.
			form::field('s[event_tpl_title]', 20,255,html::escapeHTML($E->S->event_tpl_title),'maximal').'
		</label></p>
		<p class="area"><label class=" classic">'.
			__('Description').'<br />'.
			form::textArea('s[event_tpl_desc]', 50,5,html::escapeHTML($E->S->event_tpl_desc)).'
		</label></p>
	<h2>'.__('Theme').'</h2>
		<p><ul>
		<li>'.__('Current blog theme:').' <strong>'.$default_thm.'</strong></li>
		<li>'.__('Adapted template exists:').' <strong>'.($default_adt ? __('Yes') : __('No')).'</strong></li>
		<li>'.__('Template on current theme exists:').' <strong>'.($default_xst ? __('Yes') : __('No')).'</strong></li>
		<li>'.__('Alternate template:').' <strong>'.$default_tpl.'</strong></li>
		<li>'.__('Public URL:').' <a href="'.$core->blog->url.$E->S->event_tpl_url.'">'.$core->blog->url.$E->S->event_tpl_url.'</a></li>
		</ul></p>
		<p><label class=" classic">'.
			__('URL prefix:').'<br />'.
			form::field('s[event_tpl_url]', 20,32,html::escapeHTML($E->S->event_tpl_url)).'
		</label></p>
		<p><label class=" classic">'.
			__('Choose predefined page template in case where theme of blog does not have it').'<br />'.
			form::combo('s[event_tpl_theme]',$combo_templates,$default_tpl).'
		</label></p>
		<p><label class=" classic">'.
			__('Disable list of dates of event on an entry').'<br />'.
			form::combo('s[event_tpl_dis_bhv]',array(__('No')=>'0',__('Yes')=>'1'),$E->S->event_tpl_dis_bhv).' 
		</label></p>'.
	form::hidden('p','eventdata').
	form::hidden('t','tpl').
	$core->formNonce().'
	<input type="submit" name="save[tpl]" value="'.__('Save configuration').'" />
	</form>
	</div>';
}

/**************
** Options 
**************/

if (isset($tab['adm'])) {

	echo '<div class="multi-part" id="adm" title="'.$tab['adm'].'">';

	# Save admin options
	if (!empty($_POST['save']['adm']) && isset($_POST['s'])) {
		try {
			$E->setSettings($_POST['s']);
			http::redirect($E->url.'&t=adm&done=1');
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

	# Display
	echo '
	<p>'.__('Plugin admistration options on this blog').'</p>
	<form method="post" action="'.$E->url.'">
	<h2>'.__('General').'</h2>
	<p>
	<table class="clear"><tr class="line">
	<th class="nowrap">'.__('Enable plugin').'</th>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_option_active]',0, !$E->S->event_option_active).' '.__('No').'</label></td>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_option_active]',1, $E->S->event_option_active).' '.__('Yes').'</label></td>
	</tr><tr class="line">
	<th class="nowrap">'.__('Plugin icon in Blog menu').'</th>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_option_menu]',0, !$E->S->event_option_menu).' '.__('No').'</label></td>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_option_menu]',1, $E->S->event_option_menu).' '.__('Yes').'</label></td>
	</tr><tr class="line">
	<th class="nowrap">'.__('Enable public page').'</th>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_option_public]',0, !$E->S->event_option_public).' '.__('No').'</label></td>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_option_public]',1, $E->S->event_option_public).' '.__('Yes').'</label></td
	</tr></table></p>
	<h2>'.__('Permissions').'</h2>
	<p>
	<table class="clear"><tr class="line">
	<th class="nowrap">'.__('Manage events dates on entries').'</th>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_perm_pst]',0,!$E->S->event_perm_pst).' '.__('admin').'</label></td>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_perm_pst]',1,$E->S->event_perm_pst).' '.__('admin,usage,contentadmin,eventdata').'</label></td>
	</tr><tr class="line">
	<th class="nowrap">'.__('Manage list of reordered categories').'</th>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_perm_cat]',0,!$E->S->event_perm_cat).' '.__('admin').'</label></td>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_perm_cat]',1,$E->S->event_perm_cat).' '.__('admin,categories,eventdata').'</label></td>
	</tr><tr class="line">
	<th class="nowrap">'.__('Manage public page').'</th>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_perm_tpl]',0,!$E->S->event_perm_tpl).' '.__('admin').'</label></td>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_perm_tpl]',1,$E->S->event_perm_tpl).' '.__('admin,eventdata').'</label></td>
	</tr><tr class="line">
	<th class="nowrap">'.__('Manage plugin').'</th>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_perm_adm]',0,!$E->S->event_perm_adm).' '.__('admin').'</label></td>
	<td class="nowrap"><label class=" classic">'.form::radio('s[event_perm_adm]',1,$E->S->event_perm_adm).' '.__('admin,eventdata').'</label></td>
	</tr></table></p>
	<p>'.
	form::hidden('p','eventdata').
	form::hidden('t','adm').
	$core->formNonce().'
	<input type="submit" name="save[adm]" value="'.__('Save configuration').'" /></p>
	</form>
	</div>';
}

/**************
** Uninstall 
**************/

if (isset($tab['uninstall'])) {

	echo '<div class="multi-part" id="uninstall" title="'.$tab['uninstall'].'">';

	# Save admin options
	if (!empty($_POST['save']['validate']) && isset($_POST['s'])) {

		try {
			if (1 != $understand)
				throw new Exception(__('You must check warning in order to delete plugin.'));

			if (1 == $delete_table)
				eventdataInstall::delTable($core);

			if (1 == $delete_templates)
				eventdataInstall::delTemplates($core);

			if (1 == $delete_settings)
				eventdataInstall::delSettings($core);

			eventdataInstall::delVersion($core);
			eventdataInstall::delModule($core);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}

		if (!$core->error->flag())
			http::redirect('plugins.php?removed=1');
	}
	# Confirm options
	if (!empty($_POST['save']['uninstall']) && isset($_POST['s']) && 1 == $understand) {
		echo '
		<p>'.__('In order to properly uninstall this plugin, you must specify the actions to perform').'</p>
		<form method="post" action="'.$E->url.'">
		<h2>'.__('Validate').'</h2>
		<p>
		<label class=" classic">'.sprintf(($understand ? $img_green : $img_red),'-').
			__('You understand that if you delete this plugin, the other plugins that use there table and class will no longer work.').'</label><br />
		<label class=" classic">'.sprintf($img_green,'-').
			__('Delete plugin files').'</label><br />
		<label class=" classic">'.sprintf(($delete_table ? $img_green : $img_red),'-').
			__('Delete plugin database table').'</label><br />
		<label class=" classic">'.sprintf(($delete_templates ? $img_green : $img_red),'-').
			__('Delete plugin public templates').'</label><br />
		<label class=" classic">'.sprintf(($delete_settings ? $img_green : $img_red),'-').
			__('Delete plugin settings').'</label><br />
		</p>'.
		form::hidden('p','eventdata').
		form::hidden('t','uninstall').
		form::hidden('s[understand]',$understand).
		form::hidden('s[delete_table]',$delete_table).
		form::hidden('s[delete_templates]',$delete_templates).
		form::hidden('s[delete_settings]',$delete_settings).
		$core->formNonce().'
		<input type="submit" name="save[validate]" value="'.__('Uninstall').'" />
		<input type="submit" name="save[back]" value="'.__('Back').'" />
		</form>';

	# Option form
	} else {
		if (!empty($_POST['save']['uninstall']) && 1 != $understand)
			$core->error->add(__('You must check warning in order to delete plugin.'));

		echo '
		<p>'.__('In order to properly uninstall this plugin, you must specify the actions to perform').'</p>
		<form method="post" action="'.$E->url.'">
		<h2>'.__('Uninstall "eventdata" plugin').'</h2>
		<p>
		<label class=" classic">'.form::checkbox('s[understand]',1,$understand).
		__('You understand that if you delete this plugin, the other plugins that use there table and class will no longer work.').'</label><br />
		<label class=" classic">'.form::checkbox('s[delete_table]',1,$delete_table).
		__('Delete plugin database table').'</label><br />
		<label class=" classic">'.form::checkbox('s[delete_templates]',1,$delete_templates).
		__('Delete plugin public templates').'</label><br />
		<label class=" classic">'.form::checkbox('s[delete_settings]',1,$delete_settings).
		__('Delete plugin settings').'</label><br />
		</p>'.
		form::hidden('p','eventdata').
		form::hidden('t','uninstall').
		$core->formNonce().'
		<input type="submit" name="save[uninstall]" value="'.__('Uninstall').'" />
		</form>';
	}
	echo '</div>';
}

/**************
** About 
**************/

echo '
<div class="multi-part" id="about" title="'.$tab['about'].'">
<p style="color:Red"><strong>'.__('Warning').':</strong> This is a alpha version,we recommand that you do not use it in production.</p>
<h2>'.__('About').'</h2>
<h3>'.__('Version:').'</h3>
<p>eventdata '.$core->plugins->moduleInfo('eventdata','version').'</p>
<h3>'.__('Support:').'</h3><p>
 <a href="http://blog.jcdenis.com/?q=dotclear+plugin+eventdata">
 http://blog.jcdenis.com/?q=dotclear+plugin+eventdata</a><br />
 <a href="http://forum.dotclear.net/index.php">
 http://forum.dotclear.net</a><br />
 There is a full README file in French available at the root of this extension.</p>
<h3>'.__('Copyrights:').'</h3><p>
These files are parts of eventdata, a plugin for Dotclear 2.<br />
Copyright (c) 2009 JC Denis and contributors<br />
Licensed under the GPL version 2.0 license.<br />
<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html">http://www.gnu.org/licenses/old-licenses/gpl-2.0.html</a></p>
<br />
<p>Some icons from Silk icon set 1.3 by Mark James at:<br />
<a href="http://www.famfamfam.com/lab/icons/silk/">http://www.famfamfam.com/lab/icons/silk/</a><br />
under a Creative Commons Attribution 2.5 License<br />
<a href="http://creativecommons.org/licenses/by/2.5/">http://creativecommons.org/licenses/by/2.5/</a>.</p>
<br />
<p>Traduced with plugin langOmatic,<br />Packaged with plugin Packager.</p>
</div>
'.dcPage::helpBlock('eventdata').'
 </body>
</html>';
?>