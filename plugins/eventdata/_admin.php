<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

# Verify Settings
if ($core->blog->settings->event_option_active === null) {
	try {
		eventdataInstall::setSettings($core);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Admin menu
$_menu[($core->blog->settings->event_option_menu ? 'Blog' : 'Plugins')]->addItem(
	__('Events'),
	'plugin.php?p=eventdata',DC_ADMIN_URL.'?pf=eventdata/img/icon.png',
	preg_match('/plugin.php\?p=eventdata(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id));

# Add auth perm
$core->auth->setPermissionType('eventdata',__('manage events'));

# Load _widgets.php
require dirname(__FILE__).'/_widgets.php';

# Admin behaviors
$core->addBehavior('adminPostHeaders',array('eventdataAdminBehaviors','adminPostHeaders'));
$core->addBehavior('adminPostFormSidebar',array('eventdataAdminBehaviors','adminPostFormSidebar'));
$core->addBehavior('adminAfterPostUpdate',array('eventdataAdminBehaviors','adminAfterPostSave'));
$core->addBehavior('adminAfterPostCreate',array('eventdataAdminBehaviors','adminAfterPostSave'));
$core->addBehavior('adminBeforePostDelete',array('eventdataAdminBehaviors','adminBeforePostDelete'));
$core->addBehavior('adminPostsActionsCombo',array('eventdataAdminBehaviors','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('eventdataAdminBehaviors','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('eventdataAdminBehaviors','adminPostsActionsContent'));

# Rest functions
$core->rest->addFunction('getEvent',array('eventRest','getEvent'));
$core->rest->addFunction('delEvent',array('eventRest','delEvent'));
$core->rest->addFunction('setEvent',array('eventRest','setEvent'));

# Import/export
$core->addBehavior('exportFull',array('eventdataBackup','exportFull'));
$core->addBehavior('exportSingle',array('eventdataBackup','exportSingle'));
$core->addBehavior('importInit',array('eventdataBackup','importInit'));
$core->addBehavior('importSingle',array('eventdataBackup','importSingle'));
$core->addBehavior('importFull',array('eventdataBackup','importFull'));

# Uninstall
$core->addBehavior('pluginsBeforeDelete', array('eventdataInstall', 'pluginsBeforeDelete'));

class eventdataAdminBehaviors
{
	# JS for post.php (bad hack!)
	public static function adminPostHeaders()
	{
		return 
		'<script type="text/javascript" src="'.DC_ADMIN_URL.'?pf=eventdata/js/post.js"></script>'.
		'<script type="text/javascript" src="'.DC_ADMIN_URL.'?pf=eventdata/js/datepickerBC.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"datePickerB.prototype.months[0] = datePickerC.prototype.months[0] = '".html::escapeJS(__('January'))."'; ".
		"datePickerB.prototype.months[1] = datePickerC.prototype.months[1] = '".html::escapeJS(__('February'))."'; ".
		"datePickerB.prototype.months[2] = datePickerC.prototype.months[2] = '".html::escapeJS(__('March'))."'; ".
		"datePickerB.prototype.months[3] = datePickerC.prototype.months[3] = '".html::escapeJS(__('April'))."'; ".
		"datePickerB.prototype.months[4] = datePickerC.prototype.months[4] = '".html::escapeJS(__('May'))."'; ".
		"datePickerB.prototype.months[5] = datePickerC.prototype.months[5] = '".html::escapeJS(__('June'))."'; ".
		"datePickerB.prototype.months[6] = datePickerC.prototype.months[6] = '".html::escapeJS(__('July'))."'; ".
		"datePickerB.prototype.months[7] = datePickerC.prototype.months[7] = '".html::escapeJS(__('August'))."'; ".
		"datePickerB.prototype.months[8] = datePickerC.prototype.months[8] = '".html::escapeJS(__('September'))."'; ".
		"datePickerB.prototype.months[9] = datePickerC.prototype.months[9] = '".html::escapeJS(__('October'))."'; ".
		"datePickerB.prototype.months[10] = datePickerC.prototype.months[10] = '".html::escapeJS(__('November'))."'; ".
		"datePickerB.prototype.months[11] = datePickerC.prototype.months[11] = '".html::escapeJS(__('December'))."'; ".
		"datePickerB.prototype.days[0] = datePickerC.prototype.days[0] = '".html::escapeJS(__('Monday'))."'; ".
		"datePickerB.prototype.days[1] = datePickerC.prototype.days[1] = '".html::escapeJS(__('Tuesday'))."'; ".
		"datePickerB.prototype.days[2] = datePickerC.prototype.days[2] = '".html::escapeJS(__('Wednesday'))."'; ".
		"datePickerB.prototype.days[3] = datePickerC.prototype.days[3] = '".html::escapeJS(__('Thursday'))."'; ".
		"datePickerB.prototype.days[4] = datePickerC.prototype.days[4] = '".html::escapeJS(__('Friday'))."'; ".
		"datePickerB.prototype.days[5] = datePickerC.prototype.days[5] = '".html::escapeJS(__('Saturday'))."'; ".
		"datePickerB.prototype.days[6] = datePickerC.prototype.days[6] = '".html::escapeJS(__('Sunday'))."'; ".
		"datePickerB.prototype.img_src = datePickerC.prototype.img_src = 'images/date-picker.png'; ".
		"datePickerB.prototype.close_msg = datePickerC.prototype.close_msg = '".html::escapeJS(__('close'))."'; ".
		"datePickerB.prototype.now_msg = datePickerC.prototype.now_msg = '".html::escapeJS(__('now'))."'; ".
		"\n//]]>\n".
		"</script>\n".
		'<link rel="stylesheet" type="text/css" href="'.DC_ADMIN_URL.'?pf=eventdata/style.css" />';
	}
	# Sidebar for post.php
	public static function adminPostFormSidebar(&$post)
	{
		$post_id = $post ? (integer) $post->post_id : -1;
		# Know events
		$event = new dcEvent($GLOBALS['core']);
		$events = $event->getEvent('event',null,null,null,$post_id);
		$i = 0;
		if ($events->count()) {
			echo
			'<h3>'.__('Linked events:').'</h3>'.
			'<div class="p">';
			while ($events->fetch()) {
				echo 
				'<div class="events-list">'.
				'  <div class="action">'.
				       form::checkbox('events[]',$events->event_start.','.$events->event_end,'','','',false,' title="'.__('Check to delete').'"').
				'  </div>'.
				'  <span class="green">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$events->event_start).'</span><br />'.
				'  <span class="red">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$events->event_end).'</span>'.
				'</div>';
				$i++;
			}
			echo '</div>';
		}

		# New event
		$start = empty($_POST['event_start']) ? '' : $_POST['event_start'];
		$end = empty($_POST['event_end']) ? '' : $_POST['event_end'];
		echo 
		'<h3>'.__('Add event').'</h3>'.
		'<div class="p">'.
		'<p><label>'.__('Event start:').
		form::field('event_start',16,16,$start,'event-date-start',9).
		'</label></p>'.
		'<p><label>'.__('Event end:').
		form::field('event_end',16,16,$end,'event-date-end',10).
		'</label></p>'.
		'</div>';
	}
	# Save or update for post.php
	public static function adminAfterPostSave(&$cur,&$post_id)
	{
		$event = new dcEvent($GLOBALS['core']);

		# Add event
		if (isset($_POST['event_start']) && isset($_POST['event_end'])
			&& !empty($_POST['event_start']) && !empty($_POST['event_end'])) {
			$post_id = (integer) $post_id;

			if (FALSE === strtotime($_POST['event_start']))
				throw new Exception('Wrong date format');

			if (FALSE === strtotime($_POST['event_end']))
				throw new Exception('Wrong date format');

			if (strtotime($_POST['event_start']) > strtotime($_POST['event_end']))
				throw new Exception('Start date of event must be smaller than end date of event');

			$start = date('Y-m-d H:i:00',strtotime($_POST['event_start']));
			$end = date('Y-m-d H:i:00',strtotime($_POST['event_end']));

			$event->setEvent('event',$post_id,$start,$end);
		}

		# Delete events
		if (isset($_POST['events']) && is_array($_POST['events'])) {
			foreach($_POST['events'] AS $v) {
				$v = explode(',',$v);
				if (isset($v[0]) && isset($v[1]))
					$event->delEvent('event',$post_id,$v[0],$v[1]);

			}
		}
	}
	# Delete for post.php
	public static function adminBeforePostDelete(&$post_id)
	{
		$post_id = (integer) $post_id;
		$event = new dcEvent($GLOBALS['core']);
		$event->delEvent('event',$post_id);
	}
	# Combo action for posts.php or plugin index.php
	public static function adminPostsActionsCombo(&$args)
	{
		$E = new eventdata($GLOBALS['core']);
		if ($E->S->event_option_active && $E->checkPerm('pst')) {
			$args[0][__('add event')] = 'event_add';
			$args[0][__('remove events')] = 'event_remove';
		}
	}
	# Save for posts_action.php
	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		$event = new dcEvent($core);

		if ($action == 'event_add' && isset($_POST['event_start']) && isset($_POST['event_end'])
			&& !empty($_POST['event_start']) && !empty($_POST['event_end'])) {

			try {
				if (FALSE === strtotime($_POST['event_start']))
					throw new Exception('Wrong date format');

				if (FALSE === strtotime($_POST['event_end']))
					throw new Exception('Wrong date format');

				if (strtotime($_POST['event_start']) > strtotime($_POST['event_end'])) {
					throw new Exception('Start date of event must be smaller than end date of event');
				}
				$start = date('Y-m-d H:i:00',strtotime($_POST['event_start']));
				$end = date('Y-m-d H:i:00',strtotime($_POST['event_end']));

				while ($posts->fetch()) {
					$event->setEvent('event',$posts->post_id,$start,$end);
				}
				http::redirect($redir);
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		if ($action == 'event_remove') {
			try {
				while ($posts->fetch()) {
					$event->delEvent('event',$posts->post_id);
				}
				http::redirect($redir);
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
	}
	# Form for posts_actions.php
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action != 'event_add') return;

		$start = empty($_POST['event_start']) ? '' : $_POST['event_start'];
		$end = empty($_POST['event_end']) ? '' : $_POST['event_end'];

		echo 
		self::adminPostHeaders().
		'<link rel="stylesheet" type="text/css" href="style/date-picker.css" />'."\n".
		'<div id="edit-event">'.
		'<h3>'.__('Add event').'</h3>'.
		'<div class="p">'.
		'<form action="posts_actions.php" method="post">'.
		'<p><label>'.__('Event start:').
		form::field('event_start',16,16,$start,'event-date-start',9).
		'</label></p>'.
		'<p><label>'.__('Event end:').
		form::field('event_end',16,16,$end,'event-date-end',10).
		'</label></p>'.
		'</div>'.
		$hidden_fields.
		$core->formNonce().
		form::hidden(array('action'),'event_add').
		'<input type="submit" value="'.__('save').'" /></p>'.
		'</form>'.
		'</div>';
	}
}
# Import/export behaviors for Import/export plugin
class eventdataBackup
{
	public static function exportSingle(&$core,&$exp,$blog_id)
	{
		$exp->export('event',
			'SELECT event_start, event_end, event_type, E.post_id '.
			'FROM '.$core->prefix.'event E, '.$core->prefix.'post P '.
			'WHERE P.post_id = E.post_id '.
			"AND P.blog_id = '".$blog_id."'"
		);
	}

	public static function exportFull(&$core,&$exp)
	{
		$exp->exportTable('event');
	}

	public static function importInit(&$bk,&$core)
	{
		$bk->cur_event = $core->con->openCursor($core->prefix.'event');
		$bk->event = new dcEvent($core);
	}

	public static function importSingle(&$line,&$bk,&$core)
	{
		if ($line->__name == 'event' && isset($bk->old_ids['post'][(integer) $line->post_id])) {
			$line->post_id = $bk->old_ids['post'][(integer) $line->post_id];
			$bk->event->setEvent($line->event_type,$line->post_id,$line->event_start,$line->event_end);
		}
	}

	public static function importFull(&$line,&$bk,&$core)
	{
		if ($line->__name == 'event') {
			$bk->cur_event->clean();
			
			$bk->cur_event->event_start   = (string) $line->event_start;
			$bk->cur_event->event_end   = (string) $line->event_end;
			$bk->cur_event->event_type = (string) $line->event_type;
			$bk->cur_event->post_id   = (integer) $line->post_id;
			
			$bk->cur_event->insert();
		}
	}
}
?>