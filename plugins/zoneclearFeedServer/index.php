<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Check user perms
dcPage::check('admin');

# Objects
$s = $core->blog->settings->zoneclearFeedServer;
$zc = new zoneclearFeedServer($core);

# Default values
$show_filters = false;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

$header = 
dcPage::jsLoad('index.php?pf=zoneclearFeedServer/js/main.js').
'<script type="text/javascript">'."\n//<![CDATA[\n".
"jcToolsBox.prototype.text_wait = '".html::escapeJS(__('Please wait'))."';\n".
"\n//]]>\n</script>\n";

$footer = 
'<hr class="clear"/><p class="right">
<a class="button" href="'.$p_url.'&amp;part=setting">'.__('Settings').'</a> - 
zoneclearFeedServer - '.$core->plugins->moduleInfo('zoneclearFeedServer','version').'&nbsp;
<img alt="'.__('Feeds server').'" src="index.php?pf=zoneclearFeedServer/icon.png" />
</p>';

# Messages
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'savesetting' => __('Configuration successfully saved'),
	'deletepost' => __('Entries successuflly deleted'),
	'deletefeed' => __('Feeds successfully deleted'),
	'enablefeed' => __('Feeds successfully enabled'),
	'disablefeed' => __('Feeds successfully disabled'),
	'updatefeed' => __('Feeds successfully updated'),
	'updfeedcat' => __('Category of feeds successfully updated'),
	'updfeedint' => __('Frequency of feeds successfully updated'),
	'resetupdlast' => __('Last update of feeds successfully reseted'),
	'editfeed' => __('Feed successfully updated'),
	'createfeed' => __('Feed successfully created'),
	'postaction' => __('Actions on posts successfully completed')
);
if (isset($msg_list[$msg]))
{
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

# Pages
$start_part = $s->zoneclearFeedServer_active ? 'feeds' : 'setting';
$default_part = isset($_REQUEST['part']) ? $_REQUEST['part'] : $start_part;

if (!file_exists(dirname(__FILE__).'/inc/index.'.$default_part.'.php'))
{
	$default_part = 'setting';
}
include dirname(__FILE__).'/inc/index.'.$default_part.'.php';
?>