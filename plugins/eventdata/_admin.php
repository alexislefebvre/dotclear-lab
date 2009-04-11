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
if ($core->blog->settings->eventdata_option_active === null) {
	try {
		eventdataInstall::setSettings($core);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Admin menu
$_menu[($core->blog->settings->eventdata_option_menu ? 'Blog' : 'Plugins')]->addItem(
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
$core->rest->addFunction('getEventdata',array('eventdataRest','getEventdata'));
$core->rest->addFunction('delEventdata',array('eventdataRest','delEventdata'));
$core->rest->addFunction('setEventdata',array('eventdataRest','setEventdata'));

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
		$eventdata = new dcEventdata($GLOBALS['core']);
		$eventdatas = $eventdata->getEventdata('eventdata',null,null,null,$post_id);
		$i = 0;
		if ($eventdatas->count()) {
			echo
			'<h3 id="linked-eventdatas">'.__('Linked events:').'</h3>'.
			'<div class="p" id="linked-eventdatas-form">';
			while ($eventdatas->fetch()) {
				echo 
				'<div class="eventdatas-list">'.
				'  <div class="action">'.
				       form::checkbox('eventdatas[]',$eventdatas->eventdata_start.','.$eventdatas->eventdata_end,'','','',false,' title="'.__('Check to delete').'"').
				'  </div>'.
				'  '.__('Begin:').' <span class="green">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$eventdatas->eventdata_start).'</span><br />'.
				'  '.__('End:').' <span class="red">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$eventdatas->eventdata_end).'</span><br />'.
				(!empty($eventdatas->eventdata_location) ? '  '.__('Location:').'<span>'.text::cutString(html::escapeHTML($eventdatas->eventdata_location),40).'</span><br />' : '').
				'</div>';
				$i++;
			}
			echo '</div>';
		}

		# New event
		$start = empty($_POST['eventdata_start']) ? '' : $_POST['eventdata_start'];
		$end = empty($_POST['eventdata_end']) ? '' : $_POST['eventdata_end'];
		$location = empty($_POST['eventdata_location']) ? '' : $_POST['eventdata_location'];
		echo 
		'<h3 id="new-eventdata">'.__('Add event').'</h3>'.
		'<div class="p" id="new-eventdata-form">'.
		'<p><label>'.__('Event start:').
		form::field('eventdata_start',16,16,$start,'eventdata-date-start',9).
		'</label></p>'.
		'<p><label>'.__('Event end:').
		form::field('eventdata_end',16,16,$end,'eventdata-date-end',10).
		'</label></p>'.
		'<p><label>'.__('Event location:').
		form::field('eventdata_location',20,200,$location,'eventdata-date-location',10).
		'</label></p>'.
		'</div>';
	}
	# Save or update for post.php
	public static function adminAfterPostSave(&$cur,&$post_id)
	{
		$eventdata = new dcEventdata($GLOBALS['core']);

		# Add event
		if (isset($_POST['eventdata_start']) && isset($_POST['eventdata_end'])
			&& !empty($_POST['eventdata_start']) && !empty($_POST['eventdata_end'])) {
			$post_id = (integer) $post_id;

			if (FALSE === strtotime($_POST['eventdata_start']))
				throw new Exception('Wrong date format');

			if (FALSE === strtotime($_POST['eventdata_end']))
				throw new Exception('Wrong date format');

			if (strtotime($_POST['eventdata_start']) > strtotime($_POST['eventdata_end']))
				throw new Exception('Start date of event must be smaller than end date of event');

			$start = date('Y-m-d H:i:00',strtotime($_POST['eventdata_start']));
			$end = date('Y-m-d H:i:00',strtotime($_POST['eventdata_end']));
			$location = isset($_POST['eventdata_location']) ? $_POST['eventdata_location'] : '';

			$eventdata->setEventdata('eventdata',$post_id,$start,$end,$location);
		}

		# Delete events
		if (isset($_POST['eventdatas']) && is_array($_POST['eventdatas'])) {
			foreach($_POST['eventdatas'] AS $v) {
				$v = explode(',',$v);
				if (isset($v[0]) && isset($v[1]))
					$eventdata->delEventdata('eventdata',$post_id,$v[0],$v[1]);

			}
		}
	}
	# Delete for post.php
	public static function adminBeforePostDelete(&$post_id)
	{
		$post_id = (integer) $post_id;
		$eventdata = new dcEventdata($GLOBALS['core']);
		$eventdata->delEventdata('eventdata',$post_id);
	}
	# Combo action for posts.php or plugin index.php
	public static function adminPostsActionsCombo(&$args)
	{
		$E = new eventdata($GLOBALS['core']);
		if ($E->S->eventdata_option_active && $E->checkPerm('pst')) {
			$args[0][__('add event')] = 'eventdata_add';
			$args[0][__('remove events')] = 'eventdata_remove';
		}
	}
	# Save for posts_action.php
	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		$eventdata = new dcEventdata($core);

		if ($action == 'eventdata_add' && isset($_POST['eventdata_start']) && isset($_POST['eventdata_end'])
			&& !empty($_POST['eventdata_start']) && !empty($_POST['eventdata_end'])) {

			try {
				if (FALSE === strtotime($_POST['eventdata_start']))
					throw new Exception('Wrong date format');

				if (FALSE === strtotime($_POST['eventdata_end']))
					throw new Exception('Wrong date format');

				if (strtotime($_POST['eventdata_start']) > strtotime($_POST['eventdata_end'])) {
					throw new Exception('Start date of event must be smaller than end date of event');
				}
				$start = date('Y-m-d H:i:00',strtotime($_POST['eventdata_start']));
				$end = date('Y-m-d H:i:00',strtotime($_POST['eventdata_end']));
				$location = isset($_POST['eventdata_location']) ? $_POST['eventdata_location'] : '';

				while ($posts->fetch()) {
					$eventdata->setEventdata('eventdata',$posts->post_id,$start,$end,$location);
				}
				http::redirect($redir);
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		if ($action == 'eventdata_remove') {
			try {
				while ($posts->fetch()) {
					$eventdata->delEventdata('eventdata',$posts->post_id);
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
		if ($action != 'eventdata_add') return;

		$start = empty($_POST['eventdata_start']) ? '' : $_POST['eventdata_start'];
		$end = empty($_POST['eventdata_end']) ? '' : $_POST['eventdata_end'];
		$location = empty($_POST['eventdata_location']) ? '' : $_POST['eventdata_location'];

		echo 
		self::adminPostHeaders().
		'<link rel="stylesheet" type="text/css" href="style/date-picker.css" />'."\n".
		'<div id="edit-eventdata">'.
		'<h3>'.__('Add event').'</h3>'.
		'<div class="p">'.
		'<form action="posts_actions.php" method="post">'.
		'<p><label>'.__('Event start:').
		form::field('eventdata_start',16,16,$start,'eventdata-date-start',9).
		'</label></p>'.
		'<p><label>'.__('Event end:').
		form::field('eventdata_end',16,16,$end,'eventdata-date-end',10).
		'</label></p>'.
		'<p><label>'.__('Event location:').
		form::field('eventdata_location',20,200,$location,'eventdata-date-location',10).
		'</label></p>'.
		'</div>'.
		$hidden_fields.
		$core->formNonce().
		form::hidden(array('action'),'eventdata_add').
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
		$exp->export('eventdata',
			'SELECT eventdata_start, eventdata_end, eventdata_type, eventdata_location, E.post_id '.
			'FROM '.$core->prefix.'eventdata E, '.$core->prefix.'post P '.
			'WHERE P.post_id = E.post_id '.
			"AND P.blog_id = '".$blog_id."'"
		);
	}

	public static function exportFull(&$core,&$exp)
	{
		$exp->exportTable('eventdata');
	}

	public static function importInit(&$bk,&$core)
	{
		$bk->cur_eventdata = $core->con->openCursor($core->prefix.'eventdata');
		$bk->eventdata = new dcEventdata($core);
	}

	public static function importSingle(&$line,&$bk,&$core)
	{
		if ($line->__name == 'eventdata' && isset($bk->old_ids['post'][(integer) $line->post_id])) {
			$line->post_id = $bk->old_ids['post'][(integer) $line->post_id];
			$bk->eventdata->setEventdata($line->eventdata_type,$line->post_id,$line->eventdata_start,$line->eventdata_end,$line->eventdata_location);
		}
	}

	public static function importFull(&$line,&$bk,&$core)
	{
		if ($line->__name == 'eventdata') {
			$bk->cur_eventdata->clean();
			
			$bk->cur_eventdata->eventdata_start   = (string) $line->eventdata_start;
			$bk->cur_eventdata->eventdata_end   = (string) $line->eventdata_end;
			$bk->cur_eventdata->eventdata_type = (string) $line->eventdata_type;
			$bk->cur_eventdata->eventdata_location = (string) $line->eventdata_location;
			$bk->cur_eventdata->post_id   = (integer) $line->post_id;
			
			$bk->cur_eventdata->insert();
		}
	}
}
?>