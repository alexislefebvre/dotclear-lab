<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

/**
 * @ingroup DC_PLUGIN_CINECTURLINK2
 * @brief Share media you like - main methods.
 * @since 2.6
 */
class cinecturlink2
{
	/** @var dcCore dcCore instance */
	public $core;
	/** @var dbLayer dbLayer instance */
	public $con;
	/** @var string Cinecturlink table name */
	public $table;
	/** @var string Blog ID */
	public $blog;

	/**
	 * Contructor
	 * 
	 * @param dcCore $core dcCore instance
	 */
	public function __construct(dcCore $core)
	{
		$core->blog->settings->addNamespace('cinecturlink2');

		$this->core = $core;
		$this->con = $core->con;
		$this->table = $core->prefix.'cinecturlink2';
		$this->blog = $core->con->escape($core->blog->id);
	}

	/**
	 * Get links
	 * 
	 * @param  array   $params     Query params
	 * @param  boolean $count_only Count only result
	 * @return record              record instance
	 */
	public function getLinks($params=array(), $count_only=false)
	{
		if ($count_only) {
			$strReq = 'SELECT count(L.link_id) ';
		}
		else {
			$content_req = '';
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ', $params['columns']).', ';
			}

			$strReq =
			'SELECT L.link_id, L.blog_id, L.cat_id, L.user_id, L.link_type, '.
			$content_req.
			'L.link_creadt, L.link_upddt, L.link_note, L.link_count, '.
			'L.link_title, L.link_desc, L.link_author, '.
			'L.link_lang, L.link_url, L.link_img, '.
			'U.user_name, U.user_firstname, U.user_displayname, U.user_email, '.
			'U.user_url, '.
			'C.cat_title, C.cat_desc ';
		}

		$strReq .= 
		'FROM '.$this->table.' L '.
		'INNER JOIN '.$this->core->prefix.'user U ON U.user_id = L.user_id '.
		'LEFT OUTER JOIN '.$this->table.'_cat C ON L.cat_id = C.cat_id ';

		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}

		$strReq .= "WHERE L.blog_id = '".$this->blog."' ";

		if (isset($params['link_type'])) {
			if (is_array($params['link_type']) && !empty($params['link_type'])) {
				$strReq .= 'AND L.link_type '.$this->con->in($params['link_type']);
			}
			elseif ($params['link_type'] != '') {
				$strReq .= "AND L.link_type = '".$this->con->escape($params['link_type'])."' ";
			}
		}
		else {
			$strReq .= "AND L.link_type = 'cinecturlink' ";
		}

		if (!empty($params['link_id'])) {
			if (is_array($params['link_id'])) {
				array_walk($params['link_id'], create_function('&$v,$k', 'if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['link_id'] = array((integer) $params['link_id']);
			}
			$strReq .= 'AND L.link_id '.$this->con->in($params['link_id']);
		}
		
		if (!empty($params['cat_id'])) {
			if (is_array($params['cat_id'])) {
				array_walk($params['cat_id'], create_function('&$v,$k', 'if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['cat_id'] = array((integer) $params['cat_id']);
			}
			$strReq .= 'AND L.cat_id '.$this->con->in($params['cat_id']);
		}
		if (!empty($params['cat_title'])) {
			$strReq .= "AND C.cat_title = '".$this->con->escape($params['cat_title'])."' ";
		}

		if (!empty($params['link_title'])) {
			$strReq .= "AND L.link_title = '".$this->con->escape($params['link_title'])."' ";
		}

		if (!empty($params['link_lang'])) {
			$strReq .= "AND L.link_lang = '".$this->con->escape($params['link_lang'])."' ";
		}

		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}

		if (!$count_only) {
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			}
			else {
				$strReq .= 'ORDER BY L.link_upddt DESC ';
			}
		}

		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}

		return $this->con->select($strReq);
	}

	/**
	 * Add link
	 * 
	 * @param cursor $cur cursor instance
	 */
	public function addLink(cursor $cur)
	{
		$this->con->writeLock($this->table);
		
		try {
			if ($cur->link_title == '') {
				throw new Exception(__('No link title'));
			}
			if ($cur->link_desc == '') {
				throw new Exception(__('No link description'));
			}
			if ('' == $cur->link_note) {
				$cur->link_note = 10;
			}
			if (0 > $cur->link_note || $cur->link_note > 20) {
				$cur->link_note = 10;
			}

			$cur->link_id = $this->getNextLinkId();
			$cur->blog_id = $this->blog;
			$cur->user_id = $this->core->auth->userID();
			$cur->link_creadt = date('Y-m-d H:i:s');
			$cur->link_upddt = date('Y-m-d H:i:s');
			$cur->link_pos = 0;
			$cur->link_count = 0;
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e) {
			$this->con->unlock();
			throw $e;
		}
		$this->trigger();

		# --BEHAVIOR-- cinecturlink2AfterAddLink
		$this->core->callBehavior('cinecturlink2AfterAddLink', $cur);

		return $cur->link_id;
	}

	/**
	 * Update link
	 * 
	 * @param  integer $id       Link ID
	 * @param  cursor  $cur      cursor instance
	 * @param  boolean $behavior Call related behaviors
	 */
	public function updLink($id, cursor $cur, $behavior=true)
	{
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such link ID'));
		}

		$cur->link_upddt = date('Y-m-d H:i:s');

		$cur->update("WHERE link_id = ".$id." AND blog_id = '".$this->blog."' ");
		$this->trigger();

		if ($behavior) {
			# --BEHAVIOR-- cinecturlink2AfterUpdLink
			$this->core->callBehavior('cinecturlink2AfterUpdLink', $cur, $id);
		}
	}

	/**
	 * Delete link
	 * 
	 * @param  integer $id Link ID
	 */
	public function delLink($id)
	{
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such link ID'));
		}

		# --BEHAVIOR-- cinecturlink2BeforeDelLink
		$this->core->callBehavior('cinecturlink2BeforeDelLink', $id);

		$this->con->execute(
			'DELETE FROM '.$this->table.' '.
			'WHERE link_id = '.$id.' '.
			"AND blog_id = '".$this->blog."' "
		);

		$this->trigger();
	}

	/**
	 * Get next link ID
	 * 
	 * @return integer Next link ID
	 */
	private function getNextLinkId()
	{
		return $this->con->select(
			'SELECT MAX(link_id) FROM '.$this->table.' '
		)->f(0) + 1;
	}

	/**
	 * Get categories
	 * 
	 * @param  array   $params     Query params
	 * @param  boolean $count_only Count only result
	 * @return record              record instance
	 */
	public function getCategories($params=array(), $count_only=false)
	{
		if ($count_only) {
			$strReq = 'SELECT count(C.cat_id) ';
		}
		else {
			$content_req = '';
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ', $params['columns']).', ';
			}

			$strReq =
			'SELECT C.cat_id, C.blog_id, C.cat_title, C.cat_desc, '.
			$content_req.
			'C.cat_pos, C.cat_creadt, C.cat_upddt ';
		}

		$strReq .= 'FROM '.$this->table.'_cat C  ';

		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}

		$strReq .= "WHERE C.blog_id = '".$this->blog."' ";

		if (!empty($params['cat_id'])) {
			if (is_array($params['cat_id'])) {
				array_walk($params['cat_id'], create_function('&$v,$k', 'if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['cat_id'] = array((integer) $params['cat_id']);
			}
			$strReq .= 'AND C.cat_id '.$this->con->in($params['cat_id']);
		}

		if (!empty($params['cat_title'])) {
			$strReq .= "AND C.cat_title = '".$this->con->escape($params['cat_title'])."' ";
		}

		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}

		if (!$count_only) {
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			}
			else {
				$strReq .= 'ORDER BY cat_pos ASC ';
			}
		}

		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}

		return $this->con->select($strReq);
	}

	/**
	 * Add category
	 * 
	 * @param  cursor  $cur cursor instance
	 * @return integer      New category ID
	 */
	public function addCategory(cursor $cur)
	{
		$this->con->writeLock($this->table.'_cat');
		
		try {
			if ($cur->cat_title == '') {
				throw new Exception(__('No category title'));
			}
			if ($cur->cat_desc == '') {
				throw new Exception(__('You must provide a description.'));
			}

			$cur->cat_id = $this->getNextCatId();
			$cur->cat_pos = $this->getNextCatPos();
			$cur->blog_id = $this->blog;
			$cur->cat_creadt = date('Y-m-d H:i:s');
			$cur->cat_upddt = date('Y-m-d H:i:s');
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e) {
			$this->con->unlock();
			throw $e;
		}
		$this->trigger();

		return $cur->cat_id;
	}

	/**
	 * Update category
	 * 
	 * @param  integer $id  Category ID
	 * @param  cursor  $cur cursor instance
	 */
	public function updCategory($id, cursor $cur)
	{
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such category ID'));
		}

		$cur->cat_upddt = date('Y-m-d H:i:s');

		$cur->update("WHERE cat_id = ".$id." AND blog_id = '".$this->blog."' ");
		$this->trigger();
	}

	/**
	 * Delete category
	 * 
	 * @param  integer $id Category ID
	 */
	public function delCategory($id)
	{
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such category ID'));
		}

		$this->con->execute(
			'DELETE FROM '.$this->table.'_cat '.
			'WHERE cat_id = '.$id.' '.
			"AND blog_id = '".$this->blog."' "
		);

		# Update link cat to NULL
		$cur = $this->con->openCursor($this->table);
		$cur->cat_id = NULL;
		$cur->link_upddt = date('Y-m-d H:i:s');
		$cur->update("WHERE cat_id = ".$id." AND blog_id = '".$this->blog."' ");

		$this->trigger();
	}

	/**
	 * Get next category ID
	 * 
	 * @return integer Next category ID
	 */
	private function getNextCatId()
	{
		return $this->con->select(
			'SELECT MAX(cat_id) FROM '.$this->table.'_cat '
		)->f(0) + 1;
	}

	/**
	 * Get next category position
	 * 
	 * @return integer Next category position
	 */
	private function getNextCatPos()
	{
		return $this->con->select(
			'SELECT MAX(cat_pos) FROM '.$this->table.'_cat '.
			"WHERE blog_id = '".$this->blog."' "
		)->f(0) + 1;
	}

	/**
	 * Trigger event
	 */
	private function trigger()
	{
		$this->core->blog->triggerBlog();
	}

	/**
	 * Check if a directory exists and is writable
	 * 
	 * @param  string  $root   Root
	 * @param  string  $folder Folder to create into root folder
	 * @param  boolean $throw  Throw exception or not
	 * @return boolean         True if exists and writable
	 */
	public static function test_folder($root, $folder, $throw=false)
	{
		if (!is_dir($root.'/'.$folder)) {
			if (!is_dir($root) || !is_writable($root) || !mkdir($root.'/'.$folder)) {
				if ($throw) {
					throw new Exception(__('Failed to create public folder for images.'));
				}

				return false;
			}
		}

		return true;
	}
}
