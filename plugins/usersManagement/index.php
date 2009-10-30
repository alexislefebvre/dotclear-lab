<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
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

/*
ini_set('display_errors',true);
error_reporting(E_ALL);
//*/

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

require dirname(__FILE__).'/class.blogUsers.php';
require dirname(__FILE__).'/class.usersList.php';

$blogUsers = new blogUsers($core->blog);

# For the creation of a new user
$user_id = '';
$user_super = '';
$user_pwd = '';
$user_name = '';
$user_firstname = '';
$user_displayname = '';
$user_email = '';
$user_url = '';
$user_lang = $core->auth->getInfo('user_lang');
$user_tz = $core->auth->getInfo('user_tz');
$user_post_status = '';

$user_options = $core->userDefaults();
$user_options['post_format']='xhtml'; // on active par defaut sur notre plateforme le mode de rédaction xhtml pour les utilisateurs

foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = $k;
}

# Language codes
foreach (l10n::getISOcodes(1) as $k => $v) {
	$lang_combo[] = new formSelectOption($k,$v,$v == 'en' || is_dir(DC_L10N_ROOT.'/'.$v) ? 'avail10n' : '');
}


# Add user
if (isset($_POST['user_name']))
{
	$cur = $core->con->openCursor($core->prefix.'user');

	$cur->user_id = $_POST['user_id'];
	$cur->user_super = 0;
	$cur->user_name = $user_name = $_POST['user_name'];
	$cur->user_firstname = $user_firstname = $_POST['user_firstname'];
	$cur->user_displayname = $user_displayname = $_POST['user_displayname'];
	$cur->user_email = $user_email = $_POST['user_email'];
	$cur->user_url = $user_url = $_POST['user_url'];
	$cur->user_lang = $user_lang = $_POST['user_lang'];
	$cur->user_tz = $user_tz = $_POST['user_tz'];
	$cur->user_post_status = $user_post_status = $_POST['user_post_status'];

	if (!empty($_POST['new_pwd'])) {
		if ($_POST['new_pwd'] != $_POST['new_pwd_c']) {
			$core->error->add(__("Passwords don't match"));
		} else {
			$cur->user_pwd = $_POST['new_pwd'];
		}
	}

	$user_options['post_format'] = $_POST['user_post_format'];
	$user_options['edit_size'] = (integer) $_POST['user_edit_size'];

	if ($user_options['edit_size'] < 1) {
		$user_options['edit_size'] = 10;
	}

	$cur->user_options = new ArrayObject($user_options);

	# Udate user : not use here
	if ($user_id)
	{

	}
	# Add user
	else
	{
		if (!$core->error->flag())
		{
			try
			{
				# --BEHAVIOR-- adminBeforeUserCreate
				$core->callBehavior('adminBeforeUserCreate',$cur);

				$new_id = $blogUsers->addUser($cur);

				# --BEHAVIOR-- adminAfterUserCreate
				$core->callBehavior('adminAfterUserCreate',$cur,$new_id);

				# on donne les droits de base à l utilisateur dans le blog courant
				$new_id = $blogUsers->addDefaultPerm($new_id,$_SESSION['sess_blog_id']);



			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
				$default_tab = 'add-user';
			}
		}
	}
}

if (!empty($_GET['searchExistingUsers'])) $default_tab = 'add-existing-user';


# Creating filter combo boxes
$sortby_combo = array(
__('User ID') => 'U.user_id',
__('Name') => 'user_name',
__('Firstname') => 'user_firstname',
__('Number of entries') => 'nb_post'
);

$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);


# Get existing users
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	$nb_per_page = $_GET['nb'];
}

$q = !empty($_GET['q']) ? $_GET['q'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'user_id';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'asc';

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

$show_filters = false;

# - Search filter
if ($q) {
	$params['q'] = $q;
	$show_filters = true;
}

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
		$show_filters = true;
	}
}

try {
	$rs = $core->getUsers($params);
	$counter = $core->getUsers($params,1);
	$user_list = new userList($core,$rs,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}


/* DISPLAY
-------------------------------------------------------- */
?>
<html>
<head>
  <title><?php echo __('blogUsers'); ?></title>
  <?php
  echo dcPage::jsPageTabs($default_tab);
  if (!$show_filters) {
		echo dcPage::jsLoad('js/filter-controls.js');
	}
  ?>
</head>

<body>
<?php

echo '<h2>'.__('blogUsers').'</h2>';

echo '<h2>'.__('Help Plugin UsersManagement').'</h2>';

if (!empty($_GET['add'])) {
		echo '<p class="message">'.__('User has been successfully created.').'</p>';
}

if($_POST['action']='setPermissions' && !empty($_POST['user_id'])) {
		echo '<p class="message">'.__('User permissions have been successfully modified.').'</p>';
}

echo '<div class="multi-part" title="'.__('blogUsers').'">';

try {
	$perm_types = $core->auth->getPermissionsTypes();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

echo
'<p>'.__('help delete user').'</p>'.
'<div class="two-cols">'.
'<div class="col">';

if (!empty($_REQUEST['action']))
{
	if ($_GET['action']='getPermissions' && !empty($_GET['user_id']))
	{
		$user_id=$_GET['user_id'];
		$user_perm = $core->getUserPermissions($user_id);

		$formulaire=$blogUsers->getFormPermission($user_perm,$perm_types,$user_id,$_SESSION['sess_blog_id']);

		echo
		'<fieldset><legend>'.__('Permissions of user').' « '.
		$user_id.' » '.__('on the blog').' « '.$_SESSION['sess_blog_id'].' »</legend><form method="post" action="plugin.php">'.
		$formulaire.''.
		form::hidden(array('p'),'usersManagement').
		form::hidden(array('action'),'setPermissions').
		'<p class="clear"><input type="submit" accesskey="s" value="'.__('Save').'" tabindex="15" />'.
		($user_id != '' ? form::hidden('user_id',$user_id) : '').
		$core->formNonce().
		'</p></form></fieldset>';

	}

	if($_POST['action']='setPermissions' && !empty($_POST['user_id']))
	{
		$user_id=$_POST['user_id'];
		$permissions=$_POST['perm'];
		$blogUsers->setPermission($user_id,$_SESSION['sess_blog_id'],$permissions);
	}
}
echo '</div>';
echo '<div class="col">';
try {
	$blogUsersPermissions = $core->Getblogpermissions($_SESSION['sess_blog_id'],false);
	$counter = count ($blogUsersPermissions);
	if ($counter==1) echo('<h3>'.$counter.' '.__("blogUser").'</h3>');
	elseif ($counter>1) echo('<h3>'.$counter.' '.__("blogUsers").'</h3>');
	else  echo('<h3>'.__("noBlogUsers").'</h3><p>'.__("You can add a new one.").'</p>');
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
foreach ($blogUsersPermissions as $k => $v)
{
	if (count($v['p']) > 0)
	{
		echo
		'<h4>('.html::escapeHTML(dcUtils::getUserCN(
			$k, $v['name'], $v['firstname'], $v['displayname']
		)).')';

		if (!$v['super'] ) {
			echo
			' - <a href="plugin.php?p=usersManagement&amp;action=getPermissions&amp;user_id='.$k.'">'
			.__('change permissions').'</a>';
		}

		echo '</h4>';

		echo '<ul>';
		if ($v['super']) {
			echo '<li>'.__('Super administrator').'</li>';
		} else {
			foreach ($v['p'] as $p => $V) {
				echo '<li>'.__($perm_types[$p]).'</li>';
			}
		}
		echo '</ul>';
	}
}

echo '</div>';
echo '</div>';
echo '</div>';


# add a new user form
echo '<div class="multi-part" id="add-user" title="'.__('Add a new user').'">';
echo '<h3>'.__('Help Add New User').'</h3>';

echo
'<form action="plugin.php" method="post" id="user-form">'.
form::hidden(array('p'),'usersManagement').
'<fieldset><legend>'.__('User information').'</legend>'.
'<div class="two-cols">'.
'<div class="col">'.
'<p><label class="required" title="'.__('Required field').'">'.__('Login:').' '.
form::field('user_id',20,255,html::escapeHTML($user_id),'',2).
'</label></p>'.
'<p class="form-note">'.__('At least 2 characters using letters, numbers or symbols.').'</p>'.

'<p><label class="required" title="'.__('Required field').'">'.($user_id!='' ? __('New password:') : __('Password:')).' '.
form::password('new_pwd',20,255,'','',3).
'</label></p>'.
'<p class="form-note">'.__('At least 6 characters using letters, numbers or symbols.').'</p>'.

'<p><label class="required" title="'.__('Required field').'">'.__('Confirm password:').' '.
form::password('new_pwd_c',20,255,'','',4).
'</label></p>'.

'<p><label title="'.__('Useful field').'">'.__('Name:').' '.
form::field('user_name',20,255,html::escapeHTML($user_name),'',5).
'</label></p>'.

'<p><label title="'.__('Useful field').'">'.__('Firstname:').' '.
form::field('user_firstname',20,255,html::escapeHTML($user_firstname),'',6).
'</label></p>'.

'<p><label title="'.__('Useful field').'">'.__('Display name:').' '.
form::field('user_displayname',20,255,html::escapeHTML($user_displayname),'',7).
'</label></p>'.

'<p><label title="'.__('Useful field').'">'.__('Email:').' '.
form::field('user_email',20,255,html::escapeHTML($user_email),'',8).
'</label></p>'.
'</div>'.

'<div class="col">'.
'<p><label>'.__('URL of the person:').' '.
form::field('user_url',30,255,html::escapeHTML($user_url),'',8).
'</label></p>'.
'<p><label>'.__('Preferred format:').' '.
form::combo('user_post_format',$formaters_combo,$user_options['post_format'],'',9).
'</label></p>'.

'<p><label>'.__('Default entry status:').' '.
form::combo('user_post_status',$status_combo,$user_post_status,'',10).
'</label></p>'.

'<p><label>'.__('Entry edit field height:').' '.
form::field('user_edit_size',5,4,(integer) $user_options['edit_size'],'',11).
'</label></p>'.

'<p><label>'.__('User language:').' '.
form::combo('user_lang',$lang_combo,$user_lang,'l10n',12).
'</label></p>'.

'<p><label>'.__('User timezone:').' '.
form::combo('user_tz',dt::getZones(true,true),$user_tz,'',13).
'</label></p>'.

form::hidden(array('user_super'),'0').

'</div>'.
'</div>'.
'</fieldset>';

# --BEHAVIOR-- adminUserForm
$core->callBehavior('adminUserForm',isset($rs) ? $rs : null);

echo
'<p class="clear"><input type="submit" accesskey="s" value="'.__('Save').'" tabindex="15" />'.
($user_id != '' ? form::hidden('id',$user_id) : '').
$core->formNonce().
'</p>'.

'</form>';

echo '</div>';


#list of existing users with linf to add them permission in the blog

echo '<div class="multi-part" id="add-existing-user" title="'.__('Add a user').'">';
echo '<h2>'.__('Users').'</h2>'.
'<div class="two-cols">';

if (!$show_filters) {
	echo '<p><a id="filter-control" class="form-control" href="#">'.__('Filter Search Users').'</a></p>';
}

echo
'<form action="plugin.php" method="get" id="filters-form">'.
'<input name="searchExistingUsers" value="true" type="hidden"/>'.
'<input name="p" value="usersManagement" type="hidden"/>'.
'<fieldset class="two-cols"><legend>'.__('Filters').'</legend>'.
'<div class="col">'.
'<p><label>'.__('Order by:').' '.
form::combo('sortby',$sortby_combo,$sortby).
'</label> '.
'<label>'.__('Sort:').' '.
form::combo('order',$order_combo,$order).
'</label></p>'.
'</div>'.

'<div class="col">'.
'<p><label>'.__('Search:').' '.
form::field('q',20,255,html::escapeHTML($q)).
'</label></p>'.
'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
__('Users per page').'</label> '.
'<input type="submit" value="'.__('filter').'" /></p>'.
'</div>'.

'<br class="clear" />'. //Opera sucks
'</div>'.
'</fieldset>'.
'</form>';

echo '<h3>'.__('Select the user').'</h3>';

# Show users
$user_list->displayUsers($page,$nb_per_page);

echo '</div>';


?>

</body>
</html>
