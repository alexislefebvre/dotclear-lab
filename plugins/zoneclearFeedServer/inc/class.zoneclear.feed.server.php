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
	public static $nethttp_timeout = 2;
	public static $nethttp_agent = 'zoneclearFeedServer - http://zoneclear.org';
	public static $nethttp_maxredirect = 2;

	public $core;
	public $con;
	private $blog;
	private $table;
	private $lock = null;

	public function __construct($core)
	{
		$this->core =& $core;
		$this->con = $core->con;
		$this->blog = $core->con->escape($core->blog->id);
		$this->table = $core->prefix.'zc_feed';
	}

	public static function settings($core,$namespace='zoneclearFeedServer') {
		if (!version_compare(DC_VERSION,'2.1.6','<=')) { 
			$core->blog->settings->addNamespace($namespace); 
			$s =& $core->blog->settings->{$namespace}; 
		} else { 
			$core->blog->settings->setNamespace($namespace); 
			$s =& $core->blog->settings; 
		}
		return $s;
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
			$cur->feed_upddt = date('Y-m-d H:i:s');

			$cur->update("WHERE feed_id = ".$id." AND blog_id = '".$this->blog."' ");
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
			$cur->feed_id = $this->getNextId();
			$cur->blog_id = $this->blog;
			$cur->feed_creadt = date('Y-m-d H:i:s');
			$cur->feed_upddt = date('Y-m-d H:i:s');

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

		return $cur->feed_id;
	}

	# Quick enable / disable feed
	public function enableFeed($id,$enable=true,$time=null)
	{
		try
		{
			$id = (integer) $id;

			if ($id < 1) {
				throw new Exception(__('No such ID'));
			}
			$cur = $this->openCursor();
			$this->con->writeLock($this->table);

			$cur->feed_upddt = date('Y-m-d H:i:s');
			
			$cur->feed_status = (integer) $enable;
			if (null !== $time) {
				$cur->feed_upd_last = (integer) $time;
			}

			$cur->update("WHERE feed_id = ".$id." AND blog_id = '".$this->blog."' ");
			$this->con->unlock();
			$this->trigger();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}

		# --BEHAVIOR-- zoneclearFeedServerAfterEnableFeed
		$this->core->callBehavior('zoneclearFeedServerAfterEnableFeed',$id,$enable,$time);

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
			'WHERE feed_id = '.$id.' '.
			"AND blog_id = '".$this->blog."' "
		);
		$this->trigger();
	}

	# Get related posts
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
			$strReq = 'SELECT count(Z.feed_id) ';
		}
		else
		{
			$content_req = '';
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}

			$strReq =
			'SELECT Z.feed_id, Z.feed_creadt, Z.feed_upddt, Z.feed_type, '.
			'Z.blog_id, Z.cat_id, '.
			'Z.feed_upd_int, Z.feed_upd_last, Z.feed_status, '.
			$content_req.
			'LOWER(Z.feed_name) as lowername, Z.feed_name, Z.feed_desc, '.
			'Z.feed_url, Z.feed_feed, '.
			'Z.feed_tags, Z.feed_owner, Z.feed_lang, '.
			'Z.feed_nb_out, Z.feed_nb_in, '.
			'C.cat_title, C.cat_url, C.cat_desc ';
		}

		$strReq .= 
		'FROM '.$this->table.' Z '.
		'LEFT OUTER JOIN '.$this->core->prefix.'category C ON Z.cat_id = C.cat_id ';

		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}

		$strReq .= "WHERE Z.blog_id = '".$this->blog."' ";

		if (isset($params['feed_type'])) {
			$strReq .= "AND Z.feed_type = '".$this->con->escape($params['type'])."' ";
		} else {
			$strReq .= "AND Z.feed_type = 'feed' ";
		}

		if (!empty($params['feed_id'])) {
			if (is_array($params['feed_id'])) {
				array_walk($params['feed_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			} else {
				$params['feed_id'] = array((integer) $params['feed_id']);
			}
			$strReq .= 'AND Z.feed_id '.$this->con->in($params['feed_id']);
		}

		if (isset($params['feed_feed'])) {
			$strReq .= "AND Z.feed_feed = '".$this->con->escape($params['feed_feed'])."' ";
		}
		if (isset($params['feed_url'])) {
			$strReq .= "AND Z.feed_url = '".$this->con->escape($params['feed_url'])."' ";
		}
		if (isset($params['feed_status'])) {
			$strReq .= "AND Z.feed_status = ".((integer) $params['feed_status'])." ";
		}

		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}

		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY Z.feed_upddt DESC ';
			}
		}

		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}

		$rs = $this->con->select($strReq);
		$rs->zc = $this;
		
		return $rs;
	}

	# Get next table id
	private function getNextId()
	{
		return $this->con->select('SELECT MAX(feed_id) FROM '.$this->table)->f(0) + 1;
	}

	# Lock a file to see if an update is ongoing
	public function lockUpdate()
	{
		# Cache writable ?
		if (!is_writable(DC_TPL_CACHE)) {
			return false;
		}
		# Set file path
		$f_md5 = md5($this->blog);
		$cached_file = sprintf('%s/%s/%s/%s/%s.txt',
			DC_TPL_CACHE,
			'zcfs',
			substr($f_md5,0,2),
			substr($f_md5,2,2),
			$f_md5
		);
		# Make dir
		if (!is_dir(dirname($cached_file))) {
			try {
				files::makeDir(dirname($cached_file),true);
			} catch (Exception $e) {
				return false;
			}
		}
		# Make file
		if (!file_exists($cached_file)) {
			if (!@file_put_contents($cached_file,'')) {
				return false;
			}
		}
		# Open file
		if (!($fp = @fopen($cached_file, 'wb'))) {
			return false;
		}
		# Lock file
		if (flock($fp,LOCK_EX)) {
			$this->lock = $fp;
			return true;
		}
		return false;
	}

	public function unlockUpdate()
	{
		@fclose($this->lock);
		$this->lock = null;
	}

	# Check and add/update post related to record if needed
	public function checkFeedsUpdate($id=null)
	{
		# Limit to one update at a time
		if (!$this->lockUpdate()) {
			return false;
		}

		$s = self::settings($this->core,'system');
		dt::setTZ($s->blog_timezone);
		$time = time();
		$s = self::settings($this->core);

		# All feeds or only one (from admin)
		$f = !$id ?
			$this->getFeeds(array('feed_status'=>1)) : 
			$this->getFeeds(array('feed_id'=>$id));

		# No feed
		if ($f->isEmpty()) return false;

		# Set feeds user
		$this->enableUser($s->zoneclearFeedServer_user);

		$meta = new dcMeta($this->core);
		$updates = false;
		$loop_mem = array();

		$limit = abs((integer) $s->zoneclearFeedServer_update_limit);
		if ($limit < 1) $limit = 10;
		$i = 0;

		$cur_post = $this->con->openCursor($this->core->prefix.'post');
		$cur_meta = $this->con->openCursor($this->core->prefix.'meta');

		while($f->fetch())
		{
			# Check if feed need update
			if ($id || $i < $limit && $f->feed_status == 1 
			 && $time > $f->feed_upd_last + $f->feed_upd_int)
			{		
				$i++;
				$feed = self::readFeed($f->feed_feed);

				# Nothing to parse
				if (!$feed) {
					# Disable feed
					$this->enableFeed($f->feed_id,false);
					$i++;
				}
				# Not updated since last visit
				elseif (!$id && '' != $feed->pubdate && strtotime($feed->pubdate) < $f->feed_upd_last) {
					# Set update time of this feed
					$this->enableFeed($f->feed_id,true,$time);
					$i++;
				}
				else
				{
					# Set update time of this feed
					$this->enableFeed($f->feed_id,$f->feed_status,$time);

					$this->con->begin();

					foreach ($feed->items as $item)
					{
						$item_TS = $item->TS ? $item->TS : $time;
						$item_link = $this->con->escape($item->link);

						# Not updated since last visit
						if (!$id && $item_TS < $f->feed_upd_last) continue;

						# Fix loop twin
						if (in_array($item_link,$loop_mem)) continue;
						$loop_mem[] = $item_link;

						# Check if entry exists
						$old_post = $this->con->select(
							'SELECT P.post_id '.
							'FROM '.$this->core->prefix.'post P '.
							'INNER JOIN '.$this->core->prefix.'meta M '.
							'ON P.post_id = M.post_id '.
							"WHERE blog_id='".$this->blog."' ".
							"AND meta_type = 'zoneclearfeed_url' ".
							"AND meta_id = '".$item_link."' "
						);

						# Prepare entry cursor
						$cur_post->clean();
						$cur_post->post_dt = date('Y-m-d H:i:s',$item_TS);
						if ($f->cat_id) $cur_post->cat_id = $f->cat_id;
						$post_content = $item->content ? $item->content : $item->description;
						$cur_post->post_content = html::absoluteURLs($post_content,$feed->link);
						$cur_post->post_title = $item->title ? $item->title : text::cutString(html::clean($cur_post->post_content),60);
						$creator = $item->creator ? $item->creator : $f->feed_owner;

						try
						{
							# Create entry
							if ($old_post->isEmpty())
							{
								# Post
								$cur_post->user_id = $this->core->auth->userID();
								$cur_post->post_format = 'xhtml';
								$cur_post->post_status = (integer) $s->zoneclearFeedServer_post_status_new;
								$cur_post->post_open_comment = 0;
								$cur_post->post_open_tb = 0;

								$post_id = $this->core->auth->sudo(array($this->core->blog,'addPost'),$cur_post);
							}
							# Update entry
							else
							{
								$post_id = $old_post->post_id;
								$this->core->auth->sudo(array($this->core->blog,'updPost'),$post_id,$cur_post);

								# Quick delete old meta
								$this->con->execute(
									'DELETE FROM '.$this->core->prefix.'meta '.
									'WHERE post_id = '.$post_id.' '.
									"AND meta_type LIKE 'zoneclearfeed_%' "
								);
								# Delete old tags
								$this->core->auth->sudo(array($meta,'delPostMeta'),$post_id,'tag');
							}

							# Quick add new meta
							$cur_meta->clean();
							$cur_meta->post_id = $post_id;
							$cur_meta->meta_type = 'zoneclearfeed_url';
							$cur_meta->meta_id = $item->link;
							$cur_meta->insert();

							$cur_meta->clean();
							$cur_meta->post_id = $post_id;
							$cur_meta->meta_type = 'zoneclearfeed_author';
							$cur_meta->meta_id = $creator;
							$cur_meta->insert();

							$cur_meta->clean();
							$cur_meta->post_id = $post_id;
							$cur_meta->meta_type = 'zoneclearfeed_site';
							$cur_meta->meta_id = $f->feed_url;
							$cur_meta->insert();

							$cur_meta->clean();
							$cur_meta->post_id = $post_id;
							$cur_meta->meta_type = 'zoneclearfeed_sitename';
							$cur_meta->meta_id = $f->feed_name;
							$cur_meta->insert();

							$cur_meta->clean();
							$cur_meta->post_id = $post_id;
							$cur_meta->meta_type = 'zoneclearfeed_id';
							$cur_meta->meta_id = $f->feed_id;
							$cur_meta->insert();

							# Add new tags
							$tags = $meta->splitMetaValues($f->feed_tags);
							$tags = array_merge($tags,$item->subject);
							$tags = array_unique($tags);
							foreach ($tags as $tag)
							{
								$this->core->auth->sudo(array($meta,'setPostMeta'),$post_id,'tag',dcMeta::sanitizeMetaID($tag));
							}
						}
						catch (Exception $e)
						{
							$this->con->rollback();
							$this->enableUser(false);
							$this->unlockUpdate();
							throw $e;
						}
						$updates = true;
					}
					$this->con->commit();
				}
			}
		}
		$this->enableUser(false);
		$this->unlockUpdate();
		return true;
	}

	# Set permission to update post table
	public function enableUser($enable=false)
	{
		# Enable
		if ($enable) {
			if (!$this->core->auth->checkUser($enable)) {
				throw new Exception('Unable to set user');
			}
		# Disable
		} else {
			$this->core->auth = null;
			$this->core->auth = new dcAuth($this->core);
		}
	}

	# Read and parse external feeds
	public static function readFeed($f)
	{
		try {
			$feed_reader = new feedReader;
			$feed_reader->setCacheDir(DC_TPL_CACHE);
			$feed_reader->setTimeout(self::$nethttp_timeout);
			$feed_reader->setMaxRedirects(self::$nethttp_maxredirect);
			$feed_reader->setUserAgent(self::$nethttp_agent);
			return $feed_reader->parse($f);
		}
		catch (Exception $e) {}

		return null;
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
			__('disabled') => '0',
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