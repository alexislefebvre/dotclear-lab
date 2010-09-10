<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class notifications
{
	protected $core;
	protected $components;
	protected $permissions_types;

	/**
	 * Public constructor
	 * 
	 * @param:	$core	dcCore object
	 *
	 */	 	
	public function __construct($core)
	{
		$this->core			= $core;
		$this->components		= array();
		$this->permissions_types	= array(
			'new' => 'publish',
			'upd' => 'publish',
			'del' => 'contentadmin',
			'msg' => 'usage',
			'err' => 'admin',
			'spm' => 'admin'
		);
		
		# --BEHAVIOR-- notificationsRegister
		$this->core->callBehavior('notificationsRegister',$this);
		# --BEHAVIOR-- notificationsSender
		$this->core->callBehavior('notificationsSender',$this);
	}
	
	/**
	 * Register a new component in the system
	 * 
	 * @param:	$id		String
	 * @param:	$name	String
	 * @param:	$icon	String	 	 
	 *
	 */
	public function registerComponent($id,$name,$icon = '')
	{
		if ($id !== '' && $name !== '') {
			$icon = $icon === '' ? sprintf('index.php?pf=%s/icon.png',$id) : $icon;
			$this->components[$id] = array(
				'id' => $id,
				'name' => $name,
				'icon' => $icon
			);
		}
	}
	
	/**
	 * Returns all registered components	 	 
	 *
	 */
	public function getComponents()
	{
		return $this->components;
	}
	
	/**
	 * Returns all registered types	 	 
	 *
	 */
	public function getPermissionsTypes($component = null)
	{
		if (is_null($component)) {
			return $this->permissions_types;
		}
		
		$permissions = $core->blog->settings->notifications->permissions;
		$perms = array_key_exists($component,$permissions) ? $permissions[$component] : $this->permissions_types;
		
		$res = array();
		
		foreach ($perms as $type => $perm) {
			if ($core->auth->check($perm,$core->blog->id)) {
				array_push($type);
			}
		} 
		
		return $res;
	}
	
	/**
	 * Push a notification to the database
	 * 
	 * @param:	$notifications		String
	 * @param:	$component		String 	 
	 *
	 */
	public function pushNotification($msg,$type = 'msg',$component = 'notifications')
	{
		$cur = $this->core->con->openCursor($this->core->prefix.'notification');
		$cur->notification_msg = $msg;
		$cur->notification_component = $component;
		$cur->notification_type = $type;
		
		try {
			$this->addNotification($cur);
		} catch (Exception $e) {
			if ($this->core->auth->isSuperAdmin()) {
				$cur->notification_msg = sprintf(__('Impossible to push notification : "%s" because : "%s"'),$msg,$e->getMessage());
				$cur->notification_component = 'notifications';
				$cur->notification_type = 'err';
				$this->addNotification($cur);
			}
		}
	}
	
	/**
	Creates a new log. Takes a cursor as input and returns the new log
	ID.
	
	@param	cur		<b>cursor</b>		Log cursor
	@return	<b>integer</b>		New log ID
	*/
	private function addNotification($cur)
	{
		//$this->core->con->writeLock($this->core->prefix.'notification');
		
		try
		{
			# Get ID
			$rs = $this->core->con->select(
				'SELECT MAX(notification_id) '.
				'FROM '.$this->core->prefix.'notification ' 
			);
			
			$cur->notification_id = (integer) $rs->f(0) + 1;
			$cur->blog_id = $this->core->blog->id;
			$cur->notification_dt = date('Y-m-d H:i:s');
			$cur->notification_ip = http::realIP();
			
			if (!is_null($this->core->auth->userID())) {
				$cur->user_id = $this->core->auth->userID();
			}
			if ($cur->notification_component === null || !array_key_exists($cur->notification_component,$this->components)) {
				throw new Exception(__('No such component'));
			}
			if ($cur->notification_type === null || !array_key_exists($cur->notification_type,$this->permissions_types)) {
				throw new Exception(__('No notification type'));
			}
			if ($cur->notification_msg === '') {
				throw new Exception(__('No notification message'));
			}
			
			# --BEHAVIOR-- notificationBeforeCreate
			$this->core->callBehavior('notificationBeforeCreate',$this,$cur);
			
			$cur->insert();
			//$this->core->con->unlock();
		}
		catch (Exception $e)
		{
			//$this->core->con->unlock();
			throw $e;
		}
		
		# --BEHAVIOR-- notificationAfterCreate
		$this->core->callBehavior('notificationAfterCreate',$this,$cur);
		
		return $cur->notification_id;
	}
	
	/**
	 * Get notifications according to passed parameters
	 * 
	 * @param:	$params	array
	 * 
	 * @return:	recordSet	 
	 */	 	 	 	 	
	public function getNotifications($params,$count_only = false)
	{
		if ($count_only) {
			$p = 'COUNT(notification_id)';
		}
		else {
			$f =
			'N.notification_id, N.user_id, N.notification_component, '.
			'N.notification_type, N.notification_msg, '.
			'N.notification_dt, N.notification_ip, U.user_name, '.
			'U.user_firstname, U.user_displayname, U.user_url';
		}
		
		$strReq = 
		'SELECT '.$f.' FROM '.$this->core->prefix.'notification N '.
		'LEFT JOIN '.$this->core->prefix.'user U '.
		'ON U.user_id = N.user_id ';
		
		if (!empty($params['blog_id'])) {
			if ($params['blog_id'] === 'all') {
				$strReq .= "WHERE NULL IS NULL ";
			}
			else {
				$strReq .= "WHERE N.blog_id ".$this->core->con->in($params['blog_id'])."' ";
			}
		}
		else {
			$strReq .= "WHERE N.blog_id = '".$this->core->blog->id."' ";
		}
		
		if (!empty($params['user_id'])) {
			$strReq .= "AND L.user_id = '".$this->core->con->escape($params['user_id'])."' ";
		}
		if (!empty($params['notification_ip'])) {
			$strReq .= "AND notification_ip = '".$this->core->con->escape($params['notification_ip'])."' ";
		}
		if (!empty($params['notification_type'])) {
			$strReq .= "AND notification_type ".$this->core->con->in($params['notification_type'])." ";
		}
		if (!empty($params['notification_component'])) {
			$strReq .= "AND notification_component ".$this->core->con->in($params['notification_component'])." ";
		}
		if (!empty($params['sql'])) {
			$strReq .= "AND ".$params['sql']." ";
		}
		
		if (!empty($params['order']) && !$count_only) {
			$strReq .= 'ORDER BY '.$this->core->con->escape($params['order']).' ';
		} else {
			$strReq .= 'ORDER BY notification_dt DESC ';
		}
		
		if (!empty($params['limit'])) {
			$strReq .= 'LIMIT '.$this->core->con->limit($params['limit']);
		}
		
		$rs = $this->core->con->select($strReq);
		$rs->extend('rsExtLog');
		
		return $rs;
	}
	
	public static function isDisabled($component)
	{
		return array_key_exists($component,unserialize($GLOBALS['core']->blog->settings->notifications->disabled_components));
	}
	
	public static function autoClean()
	{
		$strReq = 
		"DELETE FROM ".$core->prefix."notification WHERE blog_id = '".$core->blog->id.
		"' AND notification_dt < (SELECT MIN(log_dt) AS min FROM ".$core->core->prefix.
		"log WHERE blog_id = '".$core->blog->id."')";
		
		if ($GLOBALS['core']->blog->settings->notifications->auto_clean) {
			$core->con->execute($strReq);
		}
	}
}


class notificationsList extends adminGenericList
{
	/**
	 * Display data table for plugins and themes lists
	 *
	 * @param	int		page
	 * @param	int		nb_per_page
	 * @param	string	type
	 * @param	string	url
	 */
	public function display($page,$nb_per_page,$url)
	{		
		if ($this->rs->isEmpty()) {
			echo '<p><strong>'.__('No components registered').'</strong></p>';
		}
		else {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->var_pager = 'page';
			
			$combo_data = array(
				__('Enable component') => 'enable',
				__('disabled component') => 'disable'
			);
			
			$html_block =
				'<form action="'.$url.'" method="post">'.
				'<table summary="components" class="maximal">'.
				'<thead>'.
				'<tr>'.
				'<th>'.__('Id').'</th>'.
				'<th>'.__('Name').'</th>'.
				'<th>'.__('Permissions').'</th>'.
				'<th>'.__('Image').'</th>'.
				'<th>'.__('Status').'</th>'.
				'</tr>'.
				'</thead>'.
				'<tbody>%s</tbody>'.
				'</table>'.
				'<div class="two-cols">'.
				'<p class="col checkboxes-helpers"></p>'.
				'<p class="col right">'.
				$this->core->formNonce().
				form::combo('action',$combo_data).
				'<input type="submit" value="'.__('ok').'" name="savecomponents" /></p>'.
				'</div>'.
				'</form>';
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			$blocks = explode('%s',$html_block);
			echo $blocks[0];
			
			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page) {
				echo $this->componentLine($url);
				if ($this->rs->isEnd()) {
					break;
				}
				else {
					$this->rs->moveNext();
					$iter++;
				}
			}
			echo $blocks[1];
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	/**
	 * Return a generic component row
	 *
	 * @param	string	url
	 *
	 * @return	string
	 */
	private function componentLine($url)
	{
		$class = notifications::isDisabled($this->rs->id) ? ' offline' : '';
		$img_status = notifications::isDisabled($this->rs->id) ? 'check-off' : 'check-on';
		$alt_status = notifications::isDisabled($this->rs->id) ? __('Disabled') : __('Enabled');
		$title_status = notifications::isDisabled($this->rs->id) ? sprintf(__('Component %s disabled'),$this->rs->name) : sprintf(__('Component %s enabled'),$this->rs->name);
		
		return
			'<tr class="line wide'.$class.'" id="component_'.$this->rs->id.'">'."\n".
			# Id
			'<td class="minimal nowrap">'.
				form::checkbox('ids[]',$this->rs->id,false).
				html::escapeHTML($this->rs->id).
			"</td>\n".
			# Name
			'<td class="maximal nowrap">'.
				html::escapeHTML($this->rs->name).
			"</td>\n".
			# Permissions
			'<td>'.
				'<a href="'.$url.'&amp;set='.$this->rs->id.
				'"><img src="images/locker.png" alt="'.
				__('Permissions').'" title="'.
				sprintf(__('Set permissions for component %s'),$this->rs->name).
				'" /></a>'.
			"</td>\n".
			# Image
			'<td>'.
				'<img src="'.$this->rs->icon.'" alt="'.$this->rs->name.'" title="'.sprintf(__('Component %s'),$this->rs->name).'" />'.
			"</td>\n".
			# Status
			'<td>'.
				'<img src="images/'.$img_status.'.png" alt="'.$alt_status.'" title="'.$title_status.'" />'.
			"</td>\n".
			'</tr>'."\n";
	}
}

?>