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

$tab = 'comments';

# Creating filter combo boxes
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

$status_combo = array(
'-' => ''
);
foreach ($core->blog->getAllCommentStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

$type_combo = array(
'-' => '',
__('comment') => 'co',
__('trackback') => 'tb'
);

$sortby_combo = array(
__('Date') => 'comment_dt',
__('Entry title') => 'post_title',
__('Author') => 'comment_author',
__('Status') => 'comment_status'
);

$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);


/* Get comments
-------------------------------------------------------- */
$blog_id = isset($_GET['blog_id']) ?	$_GET['blog_id'] : '';

$author = isset($_GET['author']) ?	$_GET['author'] : '';
$status = isset($_GET['status']) ?		$_GET['status'] : '';

$q = !empty($_GET['q']) ? $_GET['q'] : '';

$last_visit = (!empty($_GET['last_visit'])
	&& ($_GET['last_visit'] == 1));

$type = !empty($_GET['type']) ?		$_GET['type'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'comment_dt';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';
$ip = !empty($_GET['ip']) ?			$_GET['ip'] : '';

$with_spam = $author || $status || $type || $sortby != 'comment_dt' || $order != 'desc' || $ip;

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

# Author filter
if ($author !== '') {
	$params['q_author'] = $author;
	$show_filters = true;
}

# - Type filter
if ($type == 'tb' || $type == 'co') {
	$params['comment_trackback'] = ($type == 'tb');
	$show_filters = true;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['comment_status'] = $status;
	$show_filters = true;
} elseif (!$with_spam) {
	$params['comment_status_not'] = -2;
}

# - Search
if ($q !== '') {
	$params['search'] = $q;
	$show_filters = true;
}

# - IP filter
if ($ip) {
	$params['comment_ip'] = $ip;
	$show_filters = true;
}

# - Last visit
if ($last_visit) {
	$params['post_month'] = null;
	$params['post_year'] = null;
	
	$params['sql'] = 'AND (comment_dt >= \''.
		dt::str('%Y-%m-%d %T',$_SESSION['superadmin_lastvisit']).'\')';
	
	$show_filters = true;
}

# Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	
	if ($sortby != 'comment_dt' || $order != 'desc') {
		$show_filters = true;
	}
}

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('publish')] = 'publish';
	$combo_action[__('unpublish')] = 'unpublish';
	$combo_action[__('mark as pending')] = 'pending';
	$combo_action[__('mark as junk')] = 'junk';
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('delete')] = 'delete';
}


/* Get comments
-------------------------------------------------------- */
try {
	$comments = superAdmin::getComments($params);
	$counter = superAdmin::getComments($params,true);
	$comment_list = new superAdminCommentList($core,$comments,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

/* DISPLAY
-------------------------------------------------------- */
$starting_script = dcPage::jsLoad('index.php?pf=superAdmin/js/_comments.pack.js').
	dcPage::jsPageTabs($tab);
if (!$show_filters) {
	$starting_script .= dcPage::jsLoad('js/filter-controls.js');
}
# --BEHAVIOR-- adminCommentsHeaders
$starting_script .= $core->callBehavior('adminCommentsHeaders');

dcPage::open(__('Comments').' &laquo; '.__('Super Admin'),
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

echo '<h2>'.html::escapeHTML('Super Admin').' &rsaquo; '.__('Comments').'</h2>';

if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}

echo('<p><a href="'.$p_url.'&amp;file=posts" class="multi-part">'.
	__('Entries').'</a></p>');

echo('<div class="multi-part" id="comments" title="'.__('Comments').'">');

if ((!isset($_COOKIE['superadmin_default_tab']))
		OR ((isset($_COOKIE['superadmin_default_tab']))
			&& ($_COOKIE['superadmin_default_tab'] != 'comments')))
	{
		echo('<p><a href="'.$p_url.'&amp;file=comments&amp;default_tab=comments" class="button">'.
			__('Make this tab my default tab').'</a></p>');
	}

if (!$core->error->flag())
{
	# Filters
	if (!$show_filters) {
		echo '<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}
	
	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	form::hidden('p','superAdmin').
	form::hidden('file','comments').
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
	'<div class="col">'.
	'<p><label class="classic">'.form::checkbox('last_visit',1,
		$last_visit).' '.
		sprintf(__('Since my last visit, on %s'),
			dt::str(__('%A, %B %e %Y, %H:%M'),
			$_SESSION['superadmin_lastvisit'],
			$core->auth->getInfo('user_tz'))).
	'</label></p>'.
	'</div>'.
	'</div>'.
	'<div class="three-cols clear">'.
	'<div class="col">'.
	'<label>'.__('Type:').' '.
	form::combo('type',$type_combo,$type).
	'</label> '.
	'<p><label>'.__('Status:').' '.
	form::combo('status',$status_combo,$status).
	'</label></p>'.
	'</div>'.
	
	'<div class="col">'.
	'<p><label>'.__('Order by:').' '.
	form::combo('sortby',$sortby_combo,$sortby).
	'</label> '.
	'<label>'.__('Sort:').' '.
	form::combo('order',$order_combo,$order).
	'</label></p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Comments per page').'</label></p>'.
	'</div>'.
	
	'<div class="col">'.
	'<p><label>'.__('Comment author:').' '.
	form::field('author',20,255,html::escapeHTML($author)).
	'</label>'.
	'<label>'.__('IP address:').' '.
	form::field('ip',20,39,html::escapeHTML($ip)).
	'</label></p>'.
	'<p><input type="submit" value="'.__('filter').'" /></p>'.
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	if (!$with_spam) {
		$spam_count = superAdmin::getComments(array('comment_status'=>-2),true)->f(0);
		if ($spam_count == 1) {
			echo '<p>'.sprintf(__('You have one spam comment.'),'<strong>'.$spam_count.'</strong>').' '.
			'<a href="'.$p_url.'&amp;file=comments&amp;status=-2">'.__('Show it.').'</a></p>';
		} elseif ($spam_count > 1) {
			echo '<p>'.sprintf(__('You have %s spam comments.'),'<strong>'.$spam_count.'</strong>').' '.
			'<a href="'.$p_url.'&amp;file=comments&amp;status=-2">'.__('Show them.').'</a></p>';
		}
	}
	
	# from /dotclear/admin/search.php
	if ($counter->f(0) > 0) {
		printf('<h3>'.
		($counter->f(0) == 1 ? __('%d comment found') : __('%d comments found')).
		'</h3>',$counter->f(0));
	}
	
	# Show comments
	$comment_list->display($page,$nb_per_page,
	'<form action="'.$p_url.'" method="post" id="form-comments">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected comments action:').' '.
	form::combo('action',$combo_action).
	$core->formNonce().
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden('p','superAdmin').
	form::hidden('file','comments_actions').
	form::hidden(array('blog_id'),$blog_id).
	form::hidden(array('q'),$q).
	form::hidden(array('type'),$type).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('author'),preg_replace('/%/','%%',$author)).
	form::hidden(array('status'),$status).
	form::hidden(array('ip'),preg_replace('/%/','%%',$ip)).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	'</div>'.
	
	'</form>'
	);
}

echo('</div>');

echo('<p><a href="'.$p_url.'&amp;file=cpmv_post" class="multi-part">'.
	__('Copy or move entry').'</a></p>');
echo('<p><a href="'.$p_url.'&amp;file=medias" class="multi-part">'.
	__('Media directories').'</a></p>');

dcPage::helpBlock('change_blog', 'core_comments');
dcPage::close();
?>