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

if (!defined('DC_RC_PATH')){return;}

class zoneclearFeedServer
{
	public $core;
	public $con;
	public $timer = 30;
	private $blog;
	private $table;

	public function __construct($core)
	{
		$this->core =& $core;
		$this->con = $core->con;
		$this->blog = $core->con->escape($core->blog->id);
		$this->table = $core->prefix.'zc_feed';
	}

	# Short openCursor
	public function openCursor()
	{
		return $this->con->openCursor($this->table);
	}

	# Update record
	public function updFeed($id,$cur)
	{
		$this->con->writeLock($this->table);
		
		try
		{
			$id = (integer) $id;

			if ($id < 1) {
				throw new Exception(__('No such ID'));
			}
			$cur->upddt = date('Y-m-d H:i:s');

			$cur->update("WHERE id = ".$id." AND blog_id = '".$this->blog."' ");
			$this->con->unlock();
			$this->trigger();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		# --BEHAVIOR-- zoneclearFeedServerAfterUpdFeed
		$this->core->callBehavior('zoneclearFeedServerAfterUpdFeed',$cur,$id);

	}

	# Add record
	public function addFeed($cur)
	{
		$this->con->writeLock($this->table);
		
		try
		{
			$cur->id = $this->getNextId();
			$cur->blog_id = $this->blog;
			$cur->creadt = date('Y-m-d H:i:s');
			$cur->upddt = date('Y-m-d H:i:s');

			//add getFeedCursor here
			
			$cur->insert();
			$this->con->unlock();
			$this->trigger();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}

		# --BEHAVIOR-- zoneclearFeedServerAfterAddFeed
		$this->core->callBehavior('zoneclearFeedServerAfterAddFeed',$cur);

		return $cur->id;
	}

	# Quick enable / disable feed
	public function enableFeed($id,$enable=true)
	{
		try
		{
			$id = (integer) $id;

			if ($id < 1) {
				throw new Exception(__('No such ID'));
			}
			$cur = $this->openCursor();
			$this->con->writeLock($this->table);

			$cur->upddt = date('Y-m-d H:i:s');
			$cur->status = (integer) $enable;

			$cur->update("WHERE id = ".$id." AND blog_id = '".$this->blog."' ");
			$this->con->unlock();
			$this->trigger();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}

		# --BEHAVIOR-- zoneclearFeedServerAfterEnableFeed
		$this->core->callBehavior('zoneclearFeedServerAfterEnableFeed',$id,$enable);

	}

	# Delete record (this not deletes post)
	public function delFeed($id)
	{
		$id = (integer) $id;
		
		if ($id < 1) {
			throw new Exception(__('No such ID'));
		}

		# --BEHAVIOR-- zoneclearFeedServerBeforeDelFeed
		$this->core->callBehavior('zoneclearFeedServerBeforeDelFeed',$id);

		$this->con->execute(
			'DELETE FROM '.$this->table.' '.
			'WHERE id = '.$id.' '.
			"AND blog_id = '".$this->blog."' "
		);
		$this->trigger();
	}

	# get related posts
	public function getPostsByFeed($params=array(),$count_only=false)
	{
		if (!isset($params['feed_id'])) {
			return null;
		}

		$params['from'] = 
		'LEFT JOIN '.$this->core->prefix.'meta F '.
		'ON P.post_id = F.post_id ';
		$params['sql'] = 
		"AND P.blog_id = '".$this->blog."' ".
		"AND F.meta_type = 'zoneclearfeed_id' ".
		"AND F.meta_id = '".$this->con->escape($params['feed_id'])."' ";

		unset($params['feed_id']);
		
		return $this->core->blog->getPosts($params,$count_only);
	}

	# get record
	public function getFeeds($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(Z.id) ';
		}
		else
		{
			$content_req = '';
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}

			$strReq =
			'SELECT Z.id, Z.creadt, Z.upddt, Z.type, Z.blog_id, Z.cat_id, '.
			'Z.upd_int, Z.upd_last, Z.status, '.
			$content_req.
			'LOWER(Z.name) as lowername, Z.name, Z.desc, Z.url, Z.feed, '.
			'Z.tags, Z.owner, Z.lang, '.
			'Z.nb_out, Z.nb_in, '.
			'C.cat_title, C.cat_url, C.cat_desc ';
		}

		$strReq .= 
		'FROM '.$this->table.' Z '.
		'LEFT OUTER JOIN '.$this->core->prefix.'category C ON Z.cat_id = C.cat_id ';

		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}

		$strReq .= "WHERE Z.blog_id = '".$this->blog."' ";

		if (isset($params['type'])) {
			$strReq .= "AND Z.type = '".$this->con->escape($params['type'])."' ";
		} else {
			$strReq .= "AND Z.type = 'feed' ";
		}

		if (!empty($params['id'])) {
			if (is_array($params['id'])) {
				array_walk($params['id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			} else {
				$params['id'] = array((integer) $params['id']);
			}
			$strReq .= 'AND Z.id '.$this->con->in($params['id']);
		}

		if (isset($params['feed'])) {
			$strReq .= "AND Z.feed = '".$this->con->escape($params['feed'])."' ";
		}
		if (isset($params['url'])) {
			$strReq .= "AND Z.url = '".$this->con->escape($params['url'])."' ";
		}
		if (isset($params['status'])) {
			$strReq .= "AND Z.status = ".((integer) $params['status'])." ";
		}

		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}

		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY Z.upddt DESC ';
			}
		}

		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}

		$rs = $this->con->select($strReq);
		$rs->zc = $this;
		
		return $rs;
	}

	# Check and add/update post related to record if needed
	public function checkFeedsUpdate($id=null)
	{

		dt::setTZ($this->core->blog->settings->blog_timezone);
		$time = time();

		# All feeds
		if ($id === null)
		{
			# Limit update to every "timer" second
			if (!$this->checkTimer(true)) return null;

			$f = $this->getFeeds();
		}
		# One feed (from admin)
		else {
			$f = $this->getFeeds(array('id'=>$id));
		}

		# No feed
		if ($f->isEmpty()) {
			return null;
		}

		$this->enableUser();

		$meta = new dcMeta($this->core);
		$updates = false;
		$loop_mem = array();

		$limit = abs((integer) $this->core->blog->settings->zoneclearFeedServer_update_limit);
		if ($limit < 2) $limit = 10;
		$i = 1;

		while($f->fetch())
		{
			# Check if feed need update
			if ($id || $i < $limit && $f->status == 1 && $time > $f->upd_last + $f->upd_int)
			{
				$i++;

				# Claim first that update is done
				$upd = $this->openCursor();
				$upd->upd_last = $time;
				$this->updFeed($f->id,$upd);

				$feed = feedReader::quickParse($f->feed,null);//,DC_TPL_CACHE);

				if (!$feed) {
					$this->enableFeed($f->id,false);
					$i++;
					continue;
				}

				$cur = $this->con->openCursor($this->core->prefix.'post');

				$this->con->begin();

				foreach ($feed->items as $item)
				{
					# Fix loop bug
					if (in_array($item->link.$item->TS,$loop_mem)) continue;
					$loop_mem[] = $item->link.$item->TS;

					# Check if entry exists
					$rs = $this->con->select(
						'SELECT P.post_id '.
						'FROM '.$this->core->prefix.'post P '.
						'INNER JOIN '.$this->core->prefix.'meta M '.
						'ON P.post_id = M.post_id '.
						"WHERE blog_id='".$this->blog."' ".
						"AND meta_type = 'zoneclearfeed_url' ".
						"AND meta_id = '".$item->link."' "
					);
					if (!$rs->isEmpty()) continue;

					# Insert entry
					$cur->clean();
					$cur->user_id = $this->core->auth->userID();
					if ($f->cat_id) {
						$cur->cat_id = $f->cat_id;
					}
					$cur->post_title = $item->title ? $item->title : text::cutString(html::clean($cur->post_content),60);
					$cur->post_format = 'xhtml';
					$cur->post_dt = date('Y-m-d H:i:s',$item->TS);
					$cur->post_status = (integer) $this->core->blog->settings->zoneclearFeedServer_post_status_new;
					$cur->post_open_comment = 0;
					$cur->post_open_tb = 0;

					$post_content = $item->content ? $item->content : $item->description;
					$cur->post_content = html::absoluteURLs($post_content,$feed->link);

					$creator = $item->creator ? $item->creator : $f->owner;

					try
					{
						$post_id = $this->core->auth->sudo(array($this->core->blog,'addPost'),$cur);

						$meta->setPostMeta($post_id,'zoneclearfeed_url',$item->link);
						$meta->setPostMeta($post_id,'zoneclearfeed_author',$creator);
						$meta->setPostMeta($post_id,'zoneclearfeed_site',$f->url);
						$meta->setPostMeta($post_id,'zoneclearfeed_sitename',$f->name);
						$meta->setPostMeta($post_id,'zoneclearfeed_id',$f->id);

						$tags = $meta->splitMetaValues($f->tags);
						$tags = array_merge($tags,$item->subject);
						$tags = array_unique($tags);

						foreach ($tags as $tag)
						{
							$meta->setPostMeta($post_id,'tag',dcMeta::sanitizeMetaID($tag));
						}
					}
					catch (Exception $e)
					{
						$this->con->rollback();
						$this->enableUser(false);
						throw $e;
					}
					$updates = true;
				}
				$this->con->commit();
			}
		}
		$this->enableUser(false);
		return true;
	}

	# Get next table id
	private function getNextId()
	{
		return $this->con->select('SELECT MAX(id) FROM '.$this->table)->f(0) + 1;
	}
	
	# Set a timer between two updates
	private function checkTimer($update=false)
	{
		$now = time();
		$last = (integer) $this->core->blog->settings->zoneclearFeedServer_timer;
		$enable = ($last + $this->timer < $now);

		if ($update && $enable) {
			$this->core->blog->settings->setNamespace('zoneclearFeedServer');
			$this->core->blog->settings->set('zoneclearFeedServer_timer',$now);
			$this->core->blog->settings->setNamespace('system');
		}
		return $enable;
	}

	# Set permission to update post table
	public function enableUser($enable=true)
	{
		# Enable
		if ($enable) {
			if (!$this->core->auth->checkUser($this->core->blog->settings->zoneclearFeedServer_user)) {
				throw new Exception('Unable to set user');
			}
		# Disable
		} else {
			$this->core->auth = null;
			$this->core->auth = new dcAuth($this->core);
		}
	}

	# Trigger blog
	private function trigger()
	{
		$this->core->blog->triggerBlog();
	}

	# Check if an URL is well formed
	public static function validateURL($url)
	{
		if (
		 (function_exists('filter_var') && !filter_var($url, FILTER_VALIDATE_URL))
		 || false !== strpos($url,'localhost')
		 || false === strpos($url,'http://')
		 || false !== strpos($url,'http://192.')
		)
		{
			return false;
		}
		return true;
	}
	
	public static function absoluteURL($root,$url)
	{
		$host = preg_replace('|^([a-z]{3,}://)(.*?)/(.*)$|','$1$2',$root);

		$parse = parse_url($url);
		if (empty($parse['scheme']))
		{
			if (strpos($url,'/') === 0) {
				$url = $host.$url;
			} elseif (strpos($url,'#') === 0) {
				$url = $root.$url;
			} elseif (preg_match('|/$|',$root)) {
				$url = $root.$url;
			} else {
				$url = dirname($root).'/'.$url;
			}
		}
		return $url;
	}

	public static function getAllStatus()
	{
		return array(
			__('disbaled') => '0',
			__('enabled') => '1'
		);
	}

	public static function getAllUpdateInterval()
	{
		return array(
			__('every hour') => 3600,
			__('every two hours') => 7200,
			__('two times per day') => 43200,
			__('every day') => 86400,
			__('every two days') => 172800,
			__('every week') => 604800
		);
	}

	public function getAllBlogAdmins()
	{
		$admins = array();

		# Get super admins
		$rs = $this->con->select(
			'SELECT user_id, user_super, user_name, user_firstname, user_displayname '.
			'FROM '.$this->con->escapeSystem($this->core->prefix.'user').' '.
			'WHERE user_super = 1 AND user_status = 1 '
		);
		
		if (!$rs->isEmpty())
		{
			while ($rs->fetch())
			{
				$user_cn = dcUtils::getUserCN($rs->user_id, $rs->user_name, $rs->user_firstname, $rs->user_displayname);
				$admins[$user_cn.' (super admin)'] = $rs->user_id;
			}
		}
		
		# Get admins
		$rs = $this->con->select(
			'SELECT U.user_id, U.user_super, U.user_name, U.user_firstname, U.user_displayname '.
			'FROM '.$this->con->escapeSystem($this->core->prefix.'user').' U '.
			'LEFT JOIN '.$this->con->escapeSystem($this->core->prefix.'permissions').' P '.
			'ON U.user_id=P.user_id '.
			'WHERE U.user_status = 1 '.
			"AND P.blog_id = '".$this->blog."' ".
			"AND P.permissions LIKE '%|admin|%' "
		);
		
		if (!$rs->isEmpty())
		{
			while ($rs->fetch())
			{
				$user_cn = dcUtils::getUserCN($rs->user_id, $rs->user_name, $rs->user_firstname, $rs->user_displayname);
				$admins[$user_cn.' (admin)'] = $rs->user_id;
			}
		}
		
		return $admins;
	}

	# Get list of urls where entries could be hacked
	public static function getPublicUrlTypes($core)
	{
		$types = array();

		# --BEHAVIOR-- zoneclearFeedServerPublicUrlTypes
		$core->callBehavior('zoneclearFeedServerPublicUrlTypes',$types);

		$types[__('home page')] = 'default';
		$types[__('post pages')] = 'post';
		$types[__('tags pages')] = 'tag';
		$types[__('archives pages')] = 'archive';
		$types[__('category pages')] = 'category';
		$types[__('entries feed')] = 'feed';

		return $types;
	}
}
?>