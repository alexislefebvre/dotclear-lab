<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('usage,contentadmin');

# Widgets list
class adminPostWidgetTextList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No widget').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';

			$blocks = $enclose_block ? explode('%s',$enclose_block) : array(0=>'',1=>'');

			echo $blocks[0].
			'<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>'.
			'<table class="clear">'.
			'<thead>'.
			'<tr>'.
			'<th colspan="2" class="nowrap">'.__('Post title').'</th>'.
			'<th class="nowrap">'.__('Post date').'</th>'.
			'<th class="nowrap">'.__('Widget title').'</th>'.
			'<th class="nowrap">'.__('Widget date').'</th>'.
			'<th class="nowrap">'.__('Author').'</th>'.
			'<th class="nowrap">'.__('Type').'</th>'.
			'</tr></thead><tbody>';

			while ($this->rs->fetch())
			{
				$w_title = html::escapeHTML($this->rs->option_title);
				if ($w_title == '') {
					$w_title = '<em>'.context::global_filter($this->rs->option_content,1,1,80,0,0).'</em>';
				}
				echo
				'<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').'"'.
				' id="p'.$this->rs->post_id.'">'.
				'<td class="nowrap">'.
				form::checkbox(array('widgets[]'),$this->rs->option_id,'','','',!$this->rs->isEditable()).'</td>'.
				'<td class="maximal"><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'#post-wtext-form">'.
				html::escapeHTML($this->rs->post_title).'</a></td>'.
				'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
				'<td class="nowrap">'.$w_title.'</td>'.
				'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->option_upddt).'</td>'.
				'<td class="nowrap">'.$this->rs->user_id.'</td>'.
				'<td class="nowrap">'.$this->rs->post_type.'</td>'.
				'</tr>';
			}

			echo 
			'</tbody></table>'.
			'<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>'.
			$blocks[1];
		}
	}
}

# Objects
$s = $core->blog->settings;
$pwt = new postWidgetText($core);

# Default values
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

# Messages
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'savesetting' => __('Configuration successfully saved'),
	'deletepostwidget' => __('Widgets successfully deleted')
);
if (isset($msg_list[$msg])) {
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

# Pages
$start_part = $s->postwidgettext_active ? 'posts' : 'setting';
$default_part = isset($_REQUEST['part']) && in_array($_REQUEST['part'],array('setting','posts')) ? $_REQUEST['part'] : $start_part;

# List
if ($default_part == 'posts')
{
	# Delete widgets
	if ($action == 'deletepostwidget' && !empty($_POST['widgets']))
	{
		try
		{
			foreach($_POST['widgets'] as $k => $id) {
				$id = (integer) $id;
				$pwt->delWidget($id);
			}
			http::redirect($p_url.'&part=posts&msg='.$action);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}

	# Combos
	$sortby_combo = array(
	__('Post title') => 'post_title',
	__('Post date') => 'post_dt',
	__('Widget title') => 'option_title',
	__('Widget date') => 'option_upddt',
	);

	$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
	);

	# Filters
	$show_filters = false;
	$nb_per_page =  30;

	$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_dt';
	$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';
	$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;

	if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
		if ($nb_per_page != $_GET['nb']) {
			$show_filters = true;
		}
		$nb_per_page = (integer) $_GET['nb'];
	}
	$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

	if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
		if ($order !== '' && in_array($order,$order_combo)) {
			$params['order'] = $sortby.' '.$order;
		}
		
		if ($sortby != 'post_dt' || $order != 'desc') {
			$show_filters = true;
		}
	}

	# Get posts with text widget
	try {
		$posts = $pwt->getWidgets($params);
		$counter = $pwt->getWidgets($params,true);
		$posts_list = new adminPostWidgetTextList($core,$posts,$counter->f(0));
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	# Display
	echo '
	<html><head><title>'.__('Post widget text').'</title>';
	if (!$show_filters) {
		echo dcPage::jsLoad('js/filter-controls.js');
	}
	echo '
	</head>
	<body>
	<h2>'.
	html::escapeHTML($core->blog->name).
	' &rsaquo; '.__('Post widget text').
	' &rsaquo; '.__('List').
	'</h2>'.$msg.'

	<p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>

	<form action="'.$p_url.'" method="get" id="filters-form">'.
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols">'.
	'<div class="col">'.
	'<p><label>'.__('Order by:').
	form::combo('sortby',$sortby_combo,$sortby).'</label></p>'.
	'</div>'.

	'<div class="col">'.
	'<p><label>'.__('Sort:').
	form::combo('order',$order_combo,$order).'</label></p>'.
	'</div>'.

	'<div class="col">'.
	'<p><label class="classic">'.form::field('nb',3,3,$nb_per_page).' '.
	__('Entries per page').'</label> '.
	'<input type="submit" value="'.__('filter').'" />'.
	form::hidden(array('p'),'postWidgetText').
	form::hidden(array('part'),'posts').'</p>'.
	'</div>'.

	'</div>'.
	'<br class="clear" />'.
	'</fieldset>'.
	'</form>';
	$posts_list->display($page,$nb_per_page,
		'<form action="'.$p_url.'" method="post" id="form-periods">'.

		'%s'.

		'<div class="two-cols">'.
		'<p class="col checkboxes-helpers"></p>'.
		'<p class="col right">'.
		'<input type="submit" value="'.__('Delete selected widgets').'" /></p>'.
		form::hidden('action','deletepostwidget').
		form::hidden(array('sortby'),$sortby).
		form::hidden(array('order'),$order).
		form::hidden(array('page'),$page).
		form::hidden(array('nb'),$nb_per_page).
		form::hidden(array('p'),'postWidgetText').
		form::hidden(array('part'),'posts').
		$core->formNonce().
		'</div>'.
		'</form>'
	);
}
# Setting
else
{
	$s_active = (boolean) $s->postwidgettext_active;
	$s_importexport_active = (boolean) $s->postwidgettext_importexport_active;

	if ($action == 'savesetting')
	{
		try {
			$s->setNameSpace('postwidgettext');
			$s->put('postwidgettext_active',!empty($_POST['s_active']));
			$s->put('postwidgettext_importexport_active',!empty($_POST['s_importexport_active']));
			$s->setNameSpace('system');
			$core->blog->triggerBlog();

			http::redirect('plugin.php?p=postWidgetText&part=setting&msg='.$action);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}

	echo '
	<html><head><title>'.__('Post widget text').'</title></head>
	<body>
	<h2>'.
	html::escapeHTML($core->blog->name).
	' &rsaquo; '.__('Post widget text').
	' &rsaquo; <a href="'.$p_url.'&amp;part=posts">'.__('List').'</a>'.
	' &rsaquo; '.__('Settings').
	'</h2>'.$msg.'

	<form method="post" action="'.$requests->p_url.'">
	<p class="field"><label>'.
	form::checkbox(array('s_active'),'1',$s_active).
	__('Enable plugin').'</label></p>
	<p class="field"><label>'.
	form::checkbox(array('s_importexport_active'),'1',$s_importexport_active).
	__('Enable import/export behaviors').'</label></p>
	<div class="clear">
	<p><input type="submit" name="save" value="'.__('save').'" />'.
	$core->formNonce().
	form::hidden(array('p'),'postWidgetText').
	form::hidden(array('part'),'setting').
	form::hidden(array('action'),'savesetting').'
	</p></div>
	</form>';
}

# Footer
dcPage::helpBlock('postWidgetText');
echo '
<hr class="clear"/>
<p class="right">
<a class="button" href="'.$p_url.'&amp;part=setting">'.__('Settings').'</a> - 
postWidgetText - '.$core->plugins->moduleInfo('postWidgetText','version').'&nbsp;
<img alt="postWidgetText" src="index.php?pf=postWidgetText/icon.png" />
</p></body></html>';
?>