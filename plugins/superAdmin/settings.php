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

$core->blog->settings->addNamespace('superAdmin');
$settings =& $core->blog->settings->superAdmin;

$tab = 'settings';

$msg = (string)'';

try
{
	if (!empty($_POST['saveconfig']))
	{
		$core->blog->settings->addNamespace('superAdmin');

		$core->blog->settings->superAdmin->put('enable_content_edition',
			!empty($_POST['superadmin_enable_content_edition']),'boolean',
			'Enable content edition of other blogs from the plugin');
		
		http::redirect($p_url.'&file=settings&saveconfig=1');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
}

/* DISPLAY
-------------------------------------------------------- */

dcPage::open(__('Settings').' &laquo; '.__('Super Admin'),
	dcPage::jsPageTabs($tab));

echo('<h2>'.html::escapeHTML('Super Admin').' &rsaquo; '.__('Settings').'</h2>');

if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}

echo('<p><a href="'.$p_url.'&amp;file=posts" class="multi-part">'.
	__('Entries').'</a></p>');
echo('<p><a href="'.$p_url.'&amp;file=comments" class="multi-part">'.
	__('Comments').'</a></p>');
echo('<p><a href="'.$p_url.'&amp;file=cpmv_post" class="multi-part">'.
	__('Copy or move entry').'</a></p>');
echo('<p><a href="'.$p_url.'&amp;file=medias" class="multi-part">'.
	__('Media directories').'</a></p>');

echo('<div class="multi-part" id="settings" title="'.__('Settings').'">');

echo('<form method="post" action="'.$p_url.'">
		<fieldset>
			<legend>'.__('Super Admin').'</legend>
			<p>'.form::checkbox('superadmin_enable_content_edition',1,
				$core->blog->settings->superAdmin->enable_content_edition).
				'<label class="classic" for="superadmin_enable_content_edition">'.
					__('Enable content edition of other blogs from the plugin').
					'</label>
			</p>
		</fieldset>
		<p>'.$core->formNonce().'</p>
		<p>'.form::hidden('p','superAdmin').'</p>
		<p>'.form::hidden('file','settings').'</p>
		<p><input type="submit" name="saveconfig" value="'.
			__('Save configuration').'" /></p>
	</form>');

echo('</div>');

dcPage::helpBlock('change_blog');

dcPage::close();
?>