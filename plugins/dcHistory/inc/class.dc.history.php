<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dcHistory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcHistory
{
	public function __construct($core)
	{
		$this->con =& $core->con;
		$this->prefix =& $core->prefix;
		$this->core =& $core;
	}

	public function addRevision($post_id,$data,$src)
	{
		if (!$this->core->auth->check('usage,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to create a revision'));
		}
		
		$cur =  $this->con->openCursor($this->prefix.'revision');
		//$this->con->writeLock($this->prefix.'revision');
		try
		{
			# Get ID
			$rs = $this->con->select(
				'SELECT MAX(revision_id) '.
				'FROM '.$this->prefix.'revision '.
				'WHERE post_id = '.$post_id
				);
			
			$cur->revision_id = (integer) $rs->f(0) + 1;

			$offset = dt::getTimeOffset($this->core->blog->settings->blog_timezone);
			$cur->revision_dt = date('Y-m-d H:i:s',time() + $offset);
			$cur->revision_tz = $this->core->blog->settings->blog_timezone;
			$cur->post_id = $post_id;
			$cur->user_id = $this->core->auth->userID();
			
			# Create diff
			$this->getRevisionContent($cur,$src,$data);
			
			if ($cur->revision_content_diff != '' || $cur->revision_excerpt_diff != '' ) 
			{
				$cur->insert();
			}
			//$this->con->unlock();
			
		}
		catch (Exception $e)
		{
			//$this->con->unlock();
			throw $e;
		}
		$this->core->blog->triggerBlog();
		
		return $cur->revision_id;

	}

	public function getAllRevisions($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(revision_id) ';
		}
		else
		{
			if (!empty($params['no_content'])) {
				$content_req ='';
			} else {
				$content_req = 
				'revision_content_diff, revision_excerpt_diff, ';
			}
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}
			
			$strReq = 
			'SELECT R.post_id, R.user_id, revision_id, revision_dt, revision_tz, '.
			//'count(revision_id) AS nb_revisions, '.
			$content_req.
			'P.post_title, P.post_url, P.post_type, P.post_dt, '.
			'U.user_name, U.user_firstname, U.user_displayname, U.user_email, U.user_url ';
		
		}

		$strReq .=
		'FROM '.$this->prefix.'revision R '.
		'INNER JOIN '.$this->prefix.'post P ON P.post_id = R.post_id '.
		'LEFT OUTER JOIN '.$this->prefix.'user U ON R.user_id = U.user_id ';

		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";

		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND (( P.post_status = 1 ';
			
			$strReq .= ') ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}

		if (!empty($params['post_type']))
		{
			if (is_array($params['post_type']) && !empty($params['post_type'])) {
				$strReq .= 'AND post_type '.$this->con->in($params['post_type']);
			} else {
				$strReq .= "AND post_type = '".$this->con->escape($params['post_type'])."' ";
			}
		}
		
		if (!empty($params['post_id'])) {
			$strReq .= 'AND P.post_id = '.(integer) $params['post_id'].' ';
		}

		if (!empty($params['revision_id'])) {
			$strReq .= 'AND revision_id = '.(integer) $params['revision_id'].' ';
		}

		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}
		
		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY revision_dt DESC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}

		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->extend('rsExtRevision');
		
		# --BEHAVIOR-- revisionPostsGetRevisions
		$this->core->callBehavior('revisionPostsGetRevisions',$rs);
		
		return $rs;

	}

	public function getRevisions($id)
	{
		$revisions = $this->getAllRevisions(array('post_id'=>$id));
  
		if ($revisions->isEmpty()) {
			return false;
		}
      
		return $revisions;
	}

	public function removeRevisions($id,$last=false)
	{
		if (!$this->core->auth->check('delete,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to delete revisions'));
		}
		
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such revision ID'));
		}
		
		#If user can only delete, we need to check the post's owner
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id))
		{
			$strReq = 'SELECT R.revision_id '.
					'FROM '.$this->prefix.'revision H, '.
					'WHERE '.
					'post_id = '.$id.' '.
					"AND user_id = '".$this->con->escape($this->core->auth->userID())."' ";
			
			$rs = $this->con->select($strReq);
			
			if ($rs->isEmpty()) {
				throw new Exception(__('You are not allowed to delete this revision'));
			}
		}
		
		$strReq = 'DELETE FROM '.$this->prefix.'revision '.
				'WHERE post_id = '.$id.' ';
				//'ORDER BY revision_id DESC LIMIT 1';
				
		if ($last)
		{
			$strReq .= 'ORDER BY revision_id DESC LIMIT 1';
		}
		
		$this->con->execute($strReq);
	}

	public function getRevisionContent($cur,$src,$data)
	{
		$new_content = $data['post_content'];
		$new_excerpt = $data['post_excerpt'];

		$prev_content = $src->post_content;
		$prev_excerpt = $src->post_excerpt;
		
		//echo $new_content ;
		
		//echo 'plop';

		$cur->revision_content_diff = uDiff::diff($prev_content,$new_content);
		$cur->revision_excerpt_diff = uDiff::diff($prev_excerpt,$new_excerpt);
		
		//echo $cur->revision_content_diff ;
	}

	public function patchWithLastDiff($post_id)
	{
		if (!$this->core->auth->check('restore',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to restore post'));
		}
		
		$params['post_id'] = $post_id;
		//$params['limit'] = 1;
		
		$post = $this->core->blog->getPosts($params);
		$params['limit'] = 1;
		$diff = $this->getAllRevisions($params);
		
			if ($diff->isEmpty()) {
				throw new Exception(__('DIFF IS EMPTY !'));
			}
			
		
		//die($post->post_excerpt.'---'.$diff->revision_excerpt_diff);
		try {
			uDiff::check($diff->revision_excerpt_diff);
			uDiff::check($diff->revision_content_diff);
		}
		catch (Exception $e)
		{
			//$this->con->unlock();
			throw $e;
		}
		
		$cur = $this->con->openCursor($this->prefix.'post');
		
		$cur->post_title = $post->post_title;
		$cur->post_excerpt = uDiff::patch($post->post_excerpt,$diff->revision_excerpt_diff);
		$cur->post_content = uDiff::patch($post->post_content,$diff->revision_content_diff);
		
		try {
			$this->core->blog->updPost($post_id,$cur);
		}
		catch (Exception $e)
		{
			//$this->con->unlock();
			throw $e;
		}
		
		return true;
		
	}
}

?>