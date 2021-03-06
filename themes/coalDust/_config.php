<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Coal Dust, a Dotclear 2 theme.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

$hreflang = $core->blog->settings->themes->coaldust_hreflang;

if (!empty($_POST))
{
	$core->blog->settings->addNameSpace('themes');
	$core->blog->settings->themes->put('coaldust_hreflang',
			!empty($_POST['coaldust_hreflang']),
			'boolean', 'Display links\'s language after links');
	
	# update setting
	$hreflang = (!empty($_POST['coaldust_hreflang']));
	
	$core->blog->triggerBlog();
	
	dcPage::success(__('Theme configuration has been successfully updated.'));
}

echo '<div class="fieldset"><h4>'.__('Coal Dust').'</h4>';

echo '<p>'.
	form::checkbox('coaldust_hreflang',1,$hreflang).
	'<label class="classic" for="coaldust_hreflang">'.
		__('Display links\'s language after links').
	'</label>'.
'</p>';

echo '</div>';