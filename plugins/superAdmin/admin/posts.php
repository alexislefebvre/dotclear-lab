<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009, 2011 Moe (http://gniark.net/)
#
# Super Admin is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Super Admin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

dcPage::checkSuper();

$tab = 'posts';

# Getting authors
try {
	$users = superAdmin::getPostsUsers();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Getting dates
try {
	$dates = superAdmin::getDates(array('type'=>'month'));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Getting langs
try {
	$langs = superAdmin::getLangs();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Creating filter combo boxes
if (!$core->error->flag())
{
	# Filter form we'll put in html_block
	
	$blog_combo = array('-' => '');
	
	# from /dotclear/inc/admin/lib.dc.page.php
	$rs = $core->getBlogs(array('order'=>'LOWER(blog_name)'));
	while ($rs->fetch()) {
		$blog_combo[html::escapeHTML($rs->blog_name.' ('.$rs->blog_id.')')]
			= $rs->blog_id;
	}
	# /from /dotclear/inc/admin/lib.dc.page.php
	unset($rs);
	
	$users_combo = array();
	$users_combo['-'] = '';
	while ($users->fetch())
	{
		$user_cn = dcUtils::getUserCN($users->user_id,$users->user_name,
		$users->user_firstname,$users->user_displayname);
		
		if ($user_cn != $users->user_id) {
			$user_cn .= ' ('.$users->user_id.')';
		}
		
		$users_combo[$user_cn] = $users->user_id; 
	}
	
	$status_combo = array(
	'-' => ''
	);
	foreach ($core->blog->getAllPostStatus() as $k => $v) {
		$status_combo[$v] = (string) $k;
	}
	
	$type_combo = superAdmin::getAllPostTypes();
	
	$selected_combo = array(
	'-' => '',
	__('selected') => '1',
	__('not selected') => '0'
	);
	
	# Months array
	$dt_m_combo['-'] = '';
	while ($dates->fetch()) {
		$dt_m_combo[dt::str('%B %Y',$dates->ts())] = $dates->year().$dates->month();
	}
	
	$lang_combo['-'] = '';
	while ($langs->fetch()) {
		$lang_combo[$langs->post_lang] = $langs->post_lang;
	}
	
	$sortby_combo = array(
	__('Date') => 'post_dt',
	__('Title') => 'post_title',
	__('Status') => 'post_status',
	__('Selected') => 'post_selected'
	);
	
	$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
	);
}

# Actions combo box
$combo_action = array();

$combo_action[__('Status')] = array(
	__('Publish') => 'publish',
	__('Unpublish') => 'unpublish',
	__('Schedule') => 'schedule',
	__('Mark as pending') => 'pending'
);

$combo_action[__('Mark')] = array(
	__('Mark as selected') => 'selected',
	__('Mark as unselected') => 'unselected'
);

$combo_action[__('Delete')] = array(__('Delete') => 'delete');

/* Get posts
-------------------------------------------------------- */
$blog_id = isset($_GET['blog_id']) ?	$_GET['blog_id'] : '';

$user_id = !empty($_GET['user_id']) ?	$_GET['user_id'] : '';
$status = isset($_GET['status']) ?	$_GET['status'] : '';
$type = isset($_GET['type']) ?	$_GET['type'] : '';

$q = !empty($_GET['q']) ? $_GET['q'] : '';

$last_visit = (!empty($_GET['last_visit'])
	&& ($_GET['last_visit'] == 1));

$selected = isset($_GET['selected']) ?	$_GET['selected'] : '';
$month = !empty($_GET['month']) ?		$_GET['month'] : '';
$lang = !empty($_GET['lang']) ?		$_GET['lang'] : '';

$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';

$show_filters = false;

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

# - Blog filter
if ($blog_id !== '' && in_array($blog_id,$blog_combo)) {
	$params['blog_id'] = $blog_id;
	$show_filters = true;
}

# - User filter
if ($user_id !== '' && in_array($user_id,$users_combo)) {
	$params['user_id'] = $user_id;
	$show_filters = true;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['post_status'] = $status;
	$show_filters = true;
}

# - Type filter
if ($type !== '' && in_array($type,$type_combo)) {
	$params['post_type'] = $type;
	$show_filters = true;
}

# - Search
if ($q !== '') {
	$params['search'] = $q;
	$show_filters = true;
}

# - Selected filter
if ($selected !== '' && in_array($selected,$selected_combo)) {
	$params['post_selected'] = $selected;
	$show_filters = true;
}

# - Month filter
if ($month !== '' && in_array($month,$dt_m_combo)) {
	$params['post_month'] = substr($month,4,2);
	$params['post_year'] = substr($month,0,4);
	$show_filters = true;
}

# - Last visit
if ($last_visit) {
	$params['post_month'] = null;
	$params['post_year'] = null;
	
	$params['sql'] = 'AND (post_creadt >= \''.
		dt::str('%Y-%m-%d %T',$_SESSION['superadmin_lastvisit']).'\')';
	
	$show_filters = true;
}

# - Lang filter
if ($lang !== '' && in_array($lang,$lang_combo)) {
	$params['post_lang'] = $lang;
	$show_filters = true;
}

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	
	if ($sortby != 'post_dt' || $order != 'desc') {
		$show_filters = true;
	}
}

# Get posts
try {
	$posts = superAdmin::getPosts($params);
	$counter = superAdmin::getPosts($params,true);
	$post_list = new superAdminPostList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

/* DISPLAY
-------------------------------------------------------- */
$starting_script = dcPage::jsLoad('index.php?pf=superAdmin/js/_posts_list.pack.js').
	dcPage::jsPageTabs($tab);
if (!$show_filters) {
	$starting_script .= dcPage::jsLoad('js/filter-controls.js');
}

dcPage::open(__('Entries').' &laquo; '.__('Super Admin'),
	$starting_script.
	"<script type=\"text/javascript\">
  //<![CDATA[
  ".
  	dcPage::jsVar('dotclear.msg.confirm_change_blog',
  	__('Are you sure you want to change the current blog?').' '.
  		__('See the help for more information.')).
  	"
  	$(function() {
			$('.superAdmin-change-blog').click(function() {
				return window.confirm(dotclear.msg.confirm_change_blog);
			});
		});
  //]]>
  </script>");

if (!$core->error->flag())
{
	echo '<h2>'.__('Super Admin').' &rsaquo; '.__('Entries').'</h2>';
	
	echo('<div class="multi-part" id="posts" title="'.__('Entries').'">');
	
	if ((!isset($_COOKIE['superadmin_default_tab']))
		OR ((isset($_COOKIE['superadmin_default_tab']))
			&& ($_COOKIE['superadmin_default_tab'] != 'posts')))
	{
		echo('<p><a href="'.$p_url.'&amp;file=posts&amp;default_tab=posts" class="button">'.
			__('Make this tab my default tab').'</a></p>');
	}
	
	if (!$show_filters) {
		echo '<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}
	
	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	form::hidden('p','superAdmin').
	form::hidden('file','posts').
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols clear">'.
		'<div class="col">'.
		'<p><label>'.__('Blog:').
		form::combo('blog_id',$blog_combo,$blog_id).'</label></p> '.
		'</div>'.
		
		'<div class="col">'.
		'<p><label>'.__('Search:').' '.
			form::field('q',30,255,html::escapeHTML($q)).'</label></p> '.
		'</div>'.
	'</div>'.
	
	'<div>'.
		'<p><label class="classic">'.form::checkbox('last_visit',1,
			$last_visit).' '.
			sprintf(__('Since my last visit, on %s'),
				dt::str(__('%A, %B %e %Y, %H:%M'),
				$_SESSION['superadmin_lastvisit'],
				$core->auth->getInfo('user_tz'))).
		'</label></p>'.
	'</div>'.
	
	'<div class="three-cols clear">'.
	'<div class="col">'.
	'<label>'.__('Author:').
	form::combo('user_id',$users_combo,$user_id).'</label> '.
	'<label>'.__('Status:').
	form::combo('status',$status_combo,$status).'</label> '.
	'<label>'.__('Post type:').
	form::combo('type',$type_combo,$type).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<p><label>'.__('Selected:').
	form::combo('selected',$selected_combo,$selected).'</label> '.
	'<label>'.__('Month:').
	form::combo('month',$dt_m_combo,$month).'</label> '.
	'<label>'.__('Lang:').
	form::combo('lang',$lang_combo,$lang).'</label></p> '.
	'</div>'.
	
	'<div class="col">'.
	'<p><label>'.__('Order by:').
	form::combo('sortby',$sortby_combo,$sortby).'</label> '.
	'<label>'.__('Sort:').
	form::combo('order',$order_combo,$order).'</label></p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Entries per page').'</label> '.
	'<input type="submit" value="'.__('filter').'" /></p>'.
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	# from /dotclear/admin/search.php
	if ($counter->f(0) > 0) {
		printf('<h3>'.
		($counter->f(0) == 1 ? __('%d entry found') : __('%d entries found')).
		'</h3>',$counter->f(0));
	}
	
	# Show posts
	$post_list->display($page,$nb_per_page,
	'<form action="'.$p_url.'" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected entries action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden('p','superAdmin').
	form::hidden('file','posts_actions').
	form::hidden(array('blog_id'),$blog_id).
	form::hidden(array('q'),$q).
	form::hidden(array('user_id'),$user_id).
	form::hidden(array('status'),$status).
	form::hidden(array('type'),$type).
	form::hidden(array('selected'),$selected).
	form::hidden(array('month'),$month).
	form::hidden(array('lang'),$lang).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	$core->formNonce().
	'</div>'.
	'</form>'
	);
	
	echo('</div>');
	
	echo('<p><a href="'.$p_url.'&amp;file=comments" class="multi-part">'.
		__('Comments').'</a></p>');
	echo('<p><a href="'.$p_url.'&amp;file=cpmv_post" class="multi-part">'.
		__('Copy or move entry').'</a></p>');
	echo('<p><a href="'.$p_url.'&amp;file=medias" class="multi-part">'.
		__('Media directories').'</a></p>');
}

dcPage::helpBlock('change_blog', 'core_posts_sa');

dcPage::close();
?>