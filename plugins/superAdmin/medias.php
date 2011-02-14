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

function cleanRoot($path)
{
	return(path::real(path::fullFromRoot($path,DC_ROOT),false));
}

$tab = 'medias';

$msg = (string)'';

$media_id = ( (isset($_REQUEST['media_id']))
	? (integer) $_REQUEST['media_id'] : null);

unset($rs);

$query = 'SELECT setting_value '.
	'FROM '.$core->prefix.'setting '.
	'WHERE (blog_id IS NULL) '.
	'AND (setting_id = \'public_path\')';

$default_public_path = cleanRoot($core->con->select($query)->f(0));

$query = 'SELECT B.blog_id, blog_uid, blog_url, blog_name, '.
	'blog_desc, blog_creadt, blog_upddt, blog_status, '.
	'S.setting_value AS public_path '.
	'FROM '.$core->prefix.'blog B '.
	'LEFT OUTER JOIN '.$core->prefix.'setting S '.
	'ON (B.blog_id = S.blog_id) AND '.
	'(S.setting_id = \'public_path\')'.
	'ORDER BY public_path ASC ';

$rs = $core->con->select($query);

# blogs list
$blogs = array();

while ($rs->fetch())
{
	$public_path = $rs->public_path;
	
	# when public_path is not different from the global public_path
	# setting, its value is empty and it uses this global setting
	if (empty($public_path)) {$public_path = $default_public_path;}
	else {$public_path = cleanRoot($rs->public_path);}
	
	if (empty($previous_public_path))
	{
		$previous_public_path = $public_path;
	}
	
	$blogs[$public_path][] = array(
		'name' => $rs->blog_name,
		'id' => $rs->blog_id,
	);
}

unset($rs);

/* DISPLAY
-------------------------------------------------------- */

dcPage::open(__('Media directories'),
	dcPage::jsPageTabs($tab));

echo('<h2>'.html::escapeHTML('Super Admin').' &rsaquo; '.__('Media directories').'</h2>');

if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}

echo('<p><a href="'.$p_url.'&amp;file=posts" class="multi-part">'.
	__('Entries').'</a></p>');
echo('<p><a href="'.$p_url.'&amp;file=comments" class="multi-part">'.
	__('Comments').'</a></p>');
echo('<p><a href="'.$p_url.'&amp;file=cpmv_post" class="multi-part">'.
	__('Copy or move entry').'</a></p>');

echo('<div class="multi-part" id="medias" title="'.__('Media directories').'">');

if ((!isset($_COOKIE['superadmin_default_tab']))
	OR ((isset($_COOKIE['superadmin_default_tab']))
		&& ($_COOKIE['superadmin_default_tab'] != 'medias')))
{
	echo('<p><a href="'.$p_url.'&amp;file=medias&amp;default_tab=medias" class="button">'.
		__('Make this tab my default tab').'</a></p>');
}

# table
$t = new table('class="clear"');

# thead
$t->part('head');
$t->row();
$t->header(__('Blog name'));
$t->header(__('Blog ID'));
$t->header(__('Public directory'));
# /thead

# tbody
$t->part('body');

foreach ($blogs as $public_path => $blogs_by_pp)
{
	$i = 0;
	foreach ($blogs_by_pp as $blog)
	{
		# row
		$t->row();
		$t->cell('<a href="'.$p_url.'&amp;file=posts&amp;blog_id='.
			$blog['id'].'">'.$blog['name'].'</a>');
		$t->cell($blog['id']);
		if ($i == 0)
		{
			$t->cell($public_path,
				'rowspan="'.count($blogs[$public_path]).'" '.
				'style="vertical-align:middle;"');
		}
		$i++;
		# /row
	}
}
# /tbody

# /table

echo($t);

echo('</div>');

dcPage::close();
?>