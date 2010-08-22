<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
#
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }
$type = (!empty($_REQUEST['type'])) ? $_REQUEST['type'] : '';

if (!muppet::typeExists($type))
{
	return;
}

dcPage::check($my_types[$type]['perm'].',contentadmin');

$plural = empty($my_types[$type]['plural']) ? ucfirst($my_types[$type]['name'].'s') : ucfirst($my_types[$type]['plural']);

/* Pager class
-------------------------------------------------------- */
class adminItemList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No entry').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';

			$html_block =
			'<table class="clear"><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Comments').'</th>'.
			'<th>'.__('Trackbacks').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';

			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';

			$blocks = explode('%s',$html_block);

			echo $blocks[0];

			while ($this->rs->fetch())
			{
				echo $this->postLine();
			}

			echo $blocks[1];

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function postLine()
	{
		if ($this->core->auth->check('categories',$this->core->blog->id)) {
			$cat_link = '<a href="category.php?id=%s">%s</a>';
		} else {
			$cat_link = '%2$s';
		}

		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
			case -1:
				$img_status = sprintf($img,__('scheduled'),'scheduled.png');
				break;
			case -2:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
		}
		if ($this->rs->cat_title) {
			$cat_title = sprintf($cat_link,$this->rs->cat_id,
			html::escapeHTML($this->rs->cat_title));
		} else {
			$cat_title = __('None');
		}

		$protected = '';
		if ($this->rs->post_password) {
			$protected = sprintf($img,__('protected'),'locker.png');
		}

		$selected = '';
		if ($this->rs->post_selected) {
			$selected = sprintf($img,__('selected'),'selected.png');
		}

		$attach = '';
		$nb_media = $this->rs->countMedia();
		if ($nb_media > 0) {
			$attach_str = $nb_media == 1 ? __('%d attachment') : __('%d attachments');
			$attach = sprintf($img,sprintf($attach_str,$nb_media),'attach.png');
		}

		$res = '<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').'"'.
		' id="p'.$this->rs->post_id.'">';

		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal"><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$cat_title.'</td>'.
		'<td class="nowrap">'.$this->rs->user_id.'</td>'.
		'<td class="nowrap">'.$this->rs->nb_comment.'</td>'.
		'<td class="nowrap">'.$this->rs->nb_trackback.'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.' '.$attach.'</td>'.
		'</tr>';

		return $res;
	}
}

/* Getting Posts
-------------------------------------------------------- */
$params = array(
	'post_type' => $type
);

# Getting categories
try {
	$categories = $core->blog->getCategories(array('post_type'=>$type));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Getting authors
try {
	$users = $core->blog->getPostsUsers($type);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Getting dates
try {
	$dates = $core->blog->getDates(array('type'=>'month','post_type'=>$type));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Getting langs
try {
	$langs = $core->blog->getLangs();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Creating filter combo boxes
if (!$core->error->flag())
{
	# Filter form we'll put in html_block
	$users_combo = $categories_combo = array();
	$users_combo['-'] = $categories_combo['-'] = '';
	while ($users->fetch())
	{
		$user_cn = dcUtils::getUserCN($users->user_id,$users->user_name,
		$users->user_firstname,$users->user_displayname);

		if ($user_cn != $users->user_id) {
			$user_cn .= ' ('.$users->user_id.')';
		}

		$users_combo[$user_cn] = $users->user_id;
	}

	while ($categories->fetch()) {
		$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
			html::escapeHTML($categories->cat_title).
			' ('.$categories->nb_post.')'] = $categories->cat_id;
	}

	$status_combo = array(
	'-' => ''
	);
	foreach ($core->blog->getAllPostStatus() as $k => $v) {
		$status_combo[$v] = (string) $k;
	}

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
	__('Category') => 'cat_title',
	__('Author') => 'user_id',
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
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('Status')] = array(
		__('Publish') => 'publish',
		__('Unpublish') => 'unpublish',
		__('Schedule') => 'schedule',
		__('Mark as pending') => 'pending'
	);
}
$combo_action[__('Mark')] = array(
	__('Mark as selected') => 'selected',
	__('Mark as unselected') => 'unselected'
);
$combo_action[__('Change')] = array(__('Change category') => 'category');
if ($core->auth->check('admin',$core->blog->id))
{
	$combo_action[__('Change')] = array_merge($combo_action[__('Change')],
		array(__('Change author') => 'author'));
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('Delete')] = array(__('Delete') => 'delete');
}

/* Get posts
-------------------------------------------------------- */
$user_id = !empty($_GET['user_id']) ?	$_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ?	$_GET['cat_id'] : '';
$status = isset($_GET['status']) ?	$_GET['status'] : '';
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

# - User filter
if ($user_id !== '' && in_array($user_id,$users_combo)) {
	$params['user_id'] = $user_id;
	$show_filters = true;
}

# - Categories filter
if ($cat_id !== '' && in_array($cat_id,$categories_combo)) {
	$params['cat_id'] = $cat_id;
	$show_filters = true;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['post_status'] = $status;
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

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;
//$params['order'] = 'post_position ASC, post_title ASC';

try {
	$pages = $core->blog->getPosts($params);
	$counter = $core->blog->getPosts($params,true);
	$post_list = new adminItemList($core,$pages,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

/* DISPLAY
-------------------------------------------------------- */

# --BEHAVIOR-- adminPagesActionsCombo
$core->callBehavior('adminPostsActionsCombo',array(&$combo_action));

/* Display
-------------------------------------------------------- */
?>
<html>
<head>
  <title><?php echo $plural; ?></title>
<?php
 echo dcPage::jsLoad('js/_posts_list.js');
 if (!$show_filters) {
	echo dcPage::jsLoad('js/filter-controls.js');
}?>
  <script type="text/javascript">
  //<![CDATA[
  <?php echo dcPage::jsVar('dotclear.msg.confirm_delete_posts',__("Are you sure you want to delete selected entries?")); ?>
  //]]>
  </script>
</head>
<body>
<?php

echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.$plural.
' - <a class="button" href="'.$p_url.'&amp;type='.$type.'">'.__('New').'</a></h2>';

if (!$core->error->flag())
{
	if (!$show_filters) {
		echo '<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}

	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols">'.
	'<div class="col">'.
	'<label>'.__('Author:').
	form::combo('user_id',$users_combo,$user_id).'</label> '.
	'<label>'.__('Category:').
	form::combo('cat_id',$categories_combo,$cat_id).'</label> '.
	'<label>'.__('Status:').
	form::combo('status',$status_combo,$status).'</label> '.
	'</div>'.

	'<div class="col">'.
	'<label>'.__('Selected:').
	form::combo('selected',$selected_combo,$selected).'</label> '.
	'<label>'.__('Month:').
	form::combo('month',$dt_m_combo,$month).'</label> '.
	'<label>'.__('Lang:').
	form::combo('lang',$lang_combo,$lang).'</label> '.
	'</div>'.

	'<div class="col">'.
	'<p><label>'.__('Order by:').
	form::combo('sortby',$sortby_combo,$sortby).'</label> '.
	'<label>'.__('Sort:').
	form::combo('order',$order_combo,$order).'</label></p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Entries per page').'</label> '.
	'<p><input type="hidden" name="p" value="megapost" />'.
	'<input type="hidden" name="type" value="'.$type.'" />'.
	'<input type="hidden" name="list" value="ok" />'.
	'<input type="submit" value="'.__('filter').'" /></p>'.
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';

	# Show pages
	$post_list->display($page,$nb_per_page,
	'<form action="posts_actions.php" method="post" id="form-entries">'.

	'%s'.

	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.

	'<p class="col right">'.__('Selected entries action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('post_type'),$type).
	form::hidden(array('redir'),html::escapeHTML($_SERVER['REQUEST_URI'])).
	$core->formNonce().
	'</div>'.
	'</form>');
}
?>
</body>
</html>