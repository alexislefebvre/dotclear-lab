<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

# Objects
$s = $core->blog->settings->periodical;
$per = new periodical($core);

# Default values
$echo = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

# Messages
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'savesetting' => __('Configuration successfully saved'),
	'deleteperiods' => __('Periods successfully deleted'),
	'emptyperiods' => __('Periods successfully emptied'),
	'updateperiod' => __('Period successfully updated'),
	'createperiod' => __('Period successfully created'),
	'publish' => __('Entries successfully published'),
	'unpublish' => __('Entries successfully unpublished'),
	'remove_post_periodical' => __('Entries successfully removed from periodical')
);
if (isset($msg_list[$msg]))
{
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

# Pages
$parts = array(
	'setting' => __('Settings'),
	'periods' => __('Periods'),
	'editperiod' => __('Edit period'),
	'addperiod' => __('New period')
);
$start_part = $s->periodical_active ? 'periods' : 'setting';
$default_part = isset($_REQUEST['part']) && isset($parts[$_REQUEST['part']]) ? $_REQUEST['part'] : $start_part;

require dirname(__FILE__).'/inc/index.'.$default_part.'.php';

# Footer
dcPage::helpBlock('periodical');
echo '
<hr class="clear"/>
<p class="right">
<a class="button" href="'.$p_url.'&amp;part=setting">'.__('Settings').'</a> - 
periodical - '.$core->plugins->moduleInfo('periodical','version').'&nbsp;
<img alt="'.__('Periodical').'" src="index.php?pf=periodical/icon.png" />
</p></body></html>';
?>