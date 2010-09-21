<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLog, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

# Get out not superAdmin!
if (!$core->auth->isSuperAdmin()) { return; }

# Var initialisation
$p_url 		= 'plugin.php?p=dcLog';
$page		= isset($_GET['page']) ? $_GET['page'] : 1;
$status		= isset($_GET['status']) ? $_GET['status'] : null;
# filter initialisation
$blog_id		= isset($_GET['blog_id']) ? $_GET['blog_id'] : null;
$user_id		= isset($_GET['user_id']) ? $_GET['user_id'] : null;
$table		= isset($_GET['table']) ? $_GET['table'] : null;
$ip			= isset($_GET['ip']) ? $_GET['ip'] : null;
$nb			= isset($_GET['nb']) ? $_GET['nb'] : 20;
# form initialisation
$ids			= isset($_POST['ids']) ? $_POST['ids'] : null;
$del_all_log	= isset($_POST['del_all_logs']) ? true : false;

#  Delete logs
if (isset($_POST['del_logs']) || isset($_POST['del_all_logs']))
{
	try {
		$core->log->delLogs($ids,$del_all_log);
		$status = $del_all_log ? '2' : '1';
		http::redirect($p_url.'&del='.$status);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Gets logs & prepares display object
$params = array(
	'blog_id' => $blog_id,
	'user_id' => !is_null($user_id) && $user_id !== '' ? explode(',',$user_id) : $user_id,
	'log_table' => !is_null($table) && $table !== '' ? explode(',',$table) : $table,
	'log_ip' => !is_null($ip) && $ip !== '' ? explode(',',$ip) : $ip
);
$l_rs = $core->log->getLogs($params);
$l_nb = $l_rs->count();
$l_list = new dcLogList($core,$l_rs,$l_nb);

# Display
echo
'<html>'.
'<head>'.
	'<title>'.__('Log').'</title>'.
	dcPage::jsLoad('js/filter-controls.js').
	dcPage::jsLoad('index.php?pf=dcLog/js/dclog.js').
	'<script type="text/javascript">'.
	'//<![CDATA['."\n".
	dcPage::jsVar('dotclear.msg.confirm_delete_selected_log',__('Are you sure you want to delete selected logs?')).
	dcPage::jsVar('dotclear.msg.confirm_delete_all_log',__('Are you sure you want to delete all logs?')).
	'//]]>'.
	'</script>'.
'</head>'."\n".
'<body>';

# Message
if (isset($_GET['del'])) {
	$msg = '';
	
	if ((integer) $_GET['del'] === 1) {
		$msg = __('Selected logs have been successfully deleted');
	}
	if ((integer) $_GET['del'] === 2) {
		$msg = __('All logs have been successfully deleted');
	}
	
	echo !empty($msg) ? '<p class="message">'.$msg.'</p>' : '';
}

# Combo blog
$combo_blog = array(__('All blogs') => 'all');
$blogs = $core->getBlogs();
while ($blogs->fetch()) {
	$combo_blog[sprintf('%s (%s)',$blogs->blog_name,$blogs->blog_id)] = $blogs->blog_id;
}

echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Log').'</h2>'.
'<p><a id="filter-control" class="form-control" href="#">'.
__('Filters').'</a></p>'.
'<form action="'.$p_url.'" method="get" id="filters-form">'.
	form::hidden('p','dcLog').
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="two-cols"><div class="col">'.
	'<p><label>'.__('Blog:').
	form::combo('blog_id',$combo_blog,$blog_id).'</label></p>'.
	'<p><label>'.__('User:').
	form::field('user_id',20,50,$user_id).'</label></p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb).' '.
	__('Logs per page').'</label>&nbsp;'.
	'<input type="submit" value="'.__('filter').'" /></p>'.
	'</div><div class="col">'.
	'<p><label>'.__('IP:').
	form::field('ip',20,50,$ip).'</label></p>'.
	'<p><label>'.__('Component:').
	form::field('table',20,50,$table).'</label></p>'.
	'</div></div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
'</form>';

$l_list->display($page,$nb,
	'<form action="'.$p_url.'" method="post" id="form-logs">'.

	'%s'.

	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.

	'<p class="col right"><input type="submit" value="'.
	__('Delete selected logs').'" name="del_logs" />&nbsp;'.
	'<input type="submit" value="'.__('Delete all logs').'" '.
	'name="del_all_logs" /></p>'.
	$core->formNonce().
	'</div>'.
	'</form>'
);

echo
'</body>'.
'</html>';

?>