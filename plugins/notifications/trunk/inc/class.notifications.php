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
	Public constructor
	
	@param	core		<b>dcCore</b>		dcCore object
	*/	 	
	public function __construct($core)
	{
		# Init
		$this->core			= $core;
		$this->components		= array();
		$this->permissions_types	= array();
		$this->initComponents();
		$this->initPermissionsTypes();
	}
	
	/**
	Initializes components for notifications
	*/	 
	public function initComponents()
	{
		$components = new ArrayObject(array());
		
		# --BEHAVIOR-- notificationsRegister
		$this->core->callBehavior('notificationsRegister',$components);
		
		foreach ($components as $component) {
			$id = isset($component[0]) && $component[0] !== '' ? $component[0] : null;
			$name = isset($component[1]) && $component[1] !== '' ? $component[1] : null;
			$icon = isset($component[2]) && $component[2] !== '' ? $component[2] : sprintf('index.php?pf=%s/icon.png',$id);
			$disabled = array_key_exists($id,unserialize($this->core->blog->settings->notifications->disabled_components));
			if (!is_null($id) && !is_null($name)) {
				$this->components[$id] = array(
					'id' => $id,
					'name' => $name,
					'icon' => $icon,
					'disabled' => $disabled
				);
			}
		}
	}
	
	/**
	Initializes permissions types for notifications 
	*/	 
	public function initPermissionsTypes()
	{
		$this->permissions_types = array(
			'new' => 'publish',
			'upd' => 'publish',
			'del' => 'contentadmin',
			'msg' => 'usage',
			'err' => 'admin',
			'spm' => 'admin'
		);
	}
	
	/**
	Creates a new log. Takes a cursor as input and returns the new log
	ID.
	
	@param	cur		<b>cursor</b>		Log cursor
	@return	<b>integer</b>		New log ID
	*/
	public function addNotification($cur)
	{
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
			if (is_null($cur->notification_component) || !array_key_exists($cur->notification_component,$this->components)) {
				throw new Exception(__('No such component'));
			}
			if (is_null($cur->notification_type) || !array_key_exists($cur->notification_type,$this->permissions_types)) {
				throw new Exception(__('No notification type'));
			}
			if (is_null($cur->notification_msg) || $cur->notification_msg === '') {
				throw new Exception(__('No notification message'));
			}
			
			# --BEHAVIOR-- notificationBeforeCreate
			$this->core->callBehavior('notificationBeforeSend',$this,$cur);
			
			$cur->insert();
		}
		catch (Exception $e)
		{
			throw $e;
		}
		
		# --BEHAVIOR-- notificationAfterCreate
		$this->core->callBehavior('notificationAfterSend',$this,$cur);
		
		return $cur->notification_id;
	}
	
	/**
	Returns registered components
	
	@return	<b>array</b>		Array of registered components
	*/
	public function getComponents()
	{
		return $this->components;
	}
	
	/**
	Returns permissions types associated to a type.
	If no component specified, returns default permissions types.	 	 
	
	@param	component		<b>string</b>		Component name
	@param	with_auth		<b>boolean</b>		Taking to account user permissions
	@return	<b>array</b>		Array of permissions types
	 */
	public function getPermissionsTypes($component = null,$with_auth = false)
	{
		$permissions_types = $this->permissions_types;
		$custom_permissions_types = unserialize($this->core->blog->settings->notifications->permissions_types);
		
		$perms = $permissions_types;
		
		if (!is_null($component)) {
			$perms = array_key_exists($component,$custom_permissions_types) ? $custom_permissions_types[$component] : $perms;
		}
		
		if ($with_auth) {
			foreach ($perms as $type => $perm) {
				if (!$this->core->auth->check($perm,$this->core->blog->id)) {
					unset($perms[$type]);
				}
			}
		}
		
		return $perms;
	}
	
	/**
	Get notifications according to passed parameters
	
	@param	params		<b>array</b>		Parameters
	@param	count_only		<b>boolean</b>		Count only
	@return	<b>curson</b>		Cursor of notifications
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
				$strReq .= "WHERE N.blog_id ".$this->core->con->in($params['blog_id'])." ";
			}
		}
		else {
			$strReq .= "WHERE N.blog_id = '".$this->core->blog->id."' ";
		}
		
		$strReq .=
		"AND N.notification_dt > (SELECT MAX(L.log_dt) FROM ".$this->core->prefix."log L ".
		"WHERE L.blog_id = '".$this->core->con->escape($this->core->blog->id)."' ".
		"AND L.user_id = '".$this->core->con->escape($this->core->auth->userID())."') ";
		
		if (!empty($params['user_id'])) {
			$strReq .= "AND U.user_id = '".$this->core->con->escape($params['user_id'])."' ";
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
			$strReq .= $params['sql']." ";
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
		$class = $this->rs->disabled ? ' offline' : '';
		$img_status = $this->rs->disabled ? 'check-off' : 'check-on';
		$alt_status = $this->rs->disabled ? __('Disabled') : __('Enabled');
		$title_status = $this->rs->disabled ? sprintf(__('Component %s disabled'),strtolower($this->rs->name)) : sprintf(__('Component %s enabled'),strtolower($this->rs->name));
		
		return
			'<tr class="line wide'.$class.'" id="component_'.$this->rs->id.'">'."\n".
			# Name
			'<td class="maximal nowrap">'.
				form::checkbox('ids[]',$this->rs->id,false).
				html::escapeHTML($this->rs->name).
			"</td>\n".
			# Permissions
			'<td>'.
				'<a href="'.$url.'&amp;set='.$this->rs->id.
				'">'.__('Define').'</a>'.
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