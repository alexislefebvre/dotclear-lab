<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!$core->auth->check('admin',$core->blog->id)) { return; }

# Var initialisation
$p_url 		= 'plugin.php?p=dcCron';
$tab			= isset($_GET['tab']) ? $_GET['tab'] : null;
$page		= isset($_GET['page']) ? $_GET['page'] : 1;
$status		= isset($_GET['status']) ? $_GET['status'] : null;
$nb_per_page	= 20;

# POST var
$ids			= isset($_POST['ids']) ? $_POST['ids'] : null;
$id			= isset($_POST['id']) ? html::escapeHTML($_POST['id']) : null;
$old_id		= isset($_POST['old_id']) ? html::escapeHTML($_POST['old_id']) : null;
$interval		= isset($_POST['interval']) ? html::escapeHTML($_POST['interval']) : null;
$class		= isset($_POST['class']) ? html::escapeHTML($_POST['class']) : null;
$function		= isset($_POST['function']) ? html::escapeHTML($_POST['function']) : null;
$first_run	= isset($_POST['first_run']) && !empty($_POST['first_run']) ? strtotime(html::escapeHTML($_POST['first_run'])) : null;

#  Create or edit task
if (isset($_POST['save']))
{
	$t_url = '&%1$s=%2$s';
	
	try {
		switch ($_POST['action'])
		{
			case 'new':
				$core->cron->put($id,$interval,array($class,$function),$first_run);
				$t_url = sprintf($t_url,'crea','1');
				break;
			case 'upd':
				$core->cron->put($id,$interval,array($class,$function),$first_run);
				if ($id != $old_id && $core->cron->taskExists($old_id)) {
					$core->cron->del($old_id);
				}
				$t_url = sprintf($t_url,'upd','2');
				break;
			case 'del':
				$core->cron->del($ids);
				$core->cron->unlockTasks($ids);
				$t_url = sprintf($t_url,'del','1');
				break;
			case 'enable':
				$core->cron->enable($ids);
				$core->cron->unlockTasks($ids);
				$t_url = sprintf($t_url,'upd','1');
				break;
			case 'disable':
				$core->cron->disable($ids);
				$t_url = sprintf($t_url,'upd','0');
				break;
		}
		http::redirect($p_url.$t_url);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Display
echo
'<html>'.
'<head>'.
	'<title>'.__('Cron').'</title>'.
	dcPage::jsDatePicker().
	dcPage::jsLoad('js/filter-controls.js').
	dcPage::jsLoad('index.php?pf=dcCron/js/dccron.js').
	'<script type="text/javascript">'.
	'//<![CDATA['."\n".
	dcPage::jsVar('dotclear.msg.confirm_delete_task',__('Are you sure you want to delete selected tasks?')).
	'//]]>'.
	'</script>'.
'</head>'."\n".
'<body>';

# Message
if (isset($_GET['crea']) || isset($_GET['upd']) || isset($_GET['del']) || isset($_GET['log'])) {
	if (isset($_GET['crea'])) {
		$msg = __('Task has been successfully created');
	}
	if (isset($_GET['del'])) {
		$msg = __('Selected tasks have been successfully deleted');
	}
	if (isset($_GET['upd']) && (integer) $_GET['upd'] === 0) {
		$msg = __('Selected tasks have been successfully disabled');
	}
	if (isset($_GET['upd']) && (integer) $_GET['upd'] === 1) {
		$msg = __('Selected tasks have been successfully enabled');
	}
	if (isset($_GET['upd']) && (integer) $_GET['upd'] === 2) {
		$msg = __('Task has been successfully updated');
	}
	
	echo !empty($msg) ? '<p class="message">'.$msg.'</p>' : '';
}

echo
'<h2>'.
html::escapeHTML($core->blog->name).' &rsaquo; '.
sprintf((isset($_GET['tab'])  ? '<a href="%2$s">%1$s</a> &rsaquo; ' : '%1$s - '),__('Cron'),$p_url).
sprintf((!isset($_GET['tab'])  ? '<a class="button" href="%2$s">%1$s</a>' : '%1$s'),(isset($_POST['edit']) ? __('Edit task') : __('New task')),$p_url.'&amp;tab=form').
'</h2>';

# Gets tasks & prepares display object
$params = $status !== null && (int) $status !== 2 ? array('status' => $status) : null;
$t_rs = $core->cron->getTasks($params);
$t_nb = count($t_rs);
$t_s_rs = staticRecord::newFromArray($t_rs);
$t_list = new dcCronList($core,$t_s_rs,$t_nb,$p_url);
# Gets logs count
$params = array('blog_id' => $core->blog->id,'log_table' => 'dcCron');
$l_nb = $core->log->getLogs($params,true)->f(0);

if ($tab === 'form') {
	if (isset($_GET['id']) && array_key_exists($_GET['id'],$t_rs)) {
		$t = $t_rs[$_GET['id']];
		$t['first_run'] = '';
		$label = __('Reschedule first run');
	}
	else {
		$t = array(
			'id' => isset($_POST['id']) ? $_POST['id'] : '',
			'callback' => array(
				isset($_POST['class']) ? $_POST['class'] : '',
				isset($_POST['function']) ? $_POST['function'] : ''
			),
			'interval' => isset($_POST['interval']) ? $_POST['interval'] : '',
			'first_run' => isset($_POST['first_run']) ? $_POST['first_run'] : '',
			'last_run' => isset($_POST['last_run']) ? $_POST['last_run'] : '',
		);
		$label = __('First run');
	}
	$url = $p_url.'&amp;tab=form'.(isset($_GET['id']) ? '&amp;id='.$_GET['id'] : '');
	
	echo
	'<h3>'.__('Task edit').'</h3>'.
	'<form action="'.$url.'" method="post">'.
	'<p><label class="required" for="id">'.__('Task id').'</label>'.
	form::field('id',40,255,$t['id']).'</p>'.
	'<p><label class="required" for="class">'.__('Class name').'</label>'.
	form::field('class',40,255,$t['callback'][0]).'</p>'.
	'<p><label class="required" for="function">'.__('Function name').'</label>'.
	form::field('function',40,255,$t['callback'][1]).'</p>'.
	'<p><label class="required" for="interval">'.__('Interval (in second)').'</label>'.
	form::field('interval',40,255,$t['interval']).'</p>'.
	'<p class="form-note">'.__('Put 0 for only one execution').'</p>'.
	'<p><label class="required" for="first_run">'.$label.'</label>'.
	form::field('first_run',20,255,$t['first_run']).'</p>'.
	'<p>'.
	'<p class="form-note">'.__('Leave blank for now').'</p>'.
	form::hidden('old_id',$t['id']).
	form::hidden('action',(isset($_GET['id']) ? 'upd' : 'new')).
	$core->formNonce().
	'<input class="save" name="save" value="'.__('Save configuration').'" type="submit" />'.
	'</p>'.
	'</form>';
}
else {
	$combo_action = array(
		__('enable') => 'enable',
		__('disable') => 'disable',
		__('delete') => 'del'
	);
	$combo_status = array(
		__('all') => 2,
		__('enabled') => 1,
		__('disabled') => 0,
		__('locked') => -1
	); 
	
	echo
	'<p><a id="filter-control" class="form-control" href="#">'.
	__('Filters').'</a></p>';
	
	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	form::hidden('p',basename(dirname(__FILE__))).
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="two-cols"><div class="col">'.
	'<p><label>'.__('Status:').
	form::combo('status',$combo_status,$status).'</label></p>'.
	'</div><div class="col">'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Entries per page').'</label> '.
	'<input type="submit" value="'.__('filter').'" /></p>'.
	'</div></div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	$t_list->display($page,$nb_per_page,
	'<form action="'.$p_url.'" method="post" id="form-tasks">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected tasks actions:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" name="save" /></p>'.
	$core->formNonce().
	'</div>'.
	'</form>',
	$p_url);
	
	if ($l_nb > 0) {
		$p_a = '<a href="%1$s">%2$s</a>';
		
		echo
		'<p class="error">'.
		__('There are error logs related to some of your tasks.').' ';
		
		if ($core->plugins->moduleExists('dcLog')) {
			echo sprintf($p_a,'plugin.php?p=dcLog&blog_id='.$core->blog->id.'&amp;component=dcCron',__('Go to see logs'));
		}
		elseif ($core->plugins->moduleExists('daInstaller')) {
			echo sprintf($p_a,'plugin.php?p=daInstaller&q=dcLog&mode=plugins',__('To see logs, please download dcLog plugin'));
		}
		else {
			echo sprintf($p_a,'http://plugins.dotaddict.org/dc2/dcLog',__('To see logs, please download dcLog plugin'));
		}
		
		echo '</p>';
	}
}

echo
'</body>'.
'</html>';

?>