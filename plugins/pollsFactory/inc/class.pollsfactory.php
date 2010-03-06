<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class pollsFactory
{
	public $core;
	public $con;
	protected $blog;
	protected $table;

	public function __construct($core)
	{
		$this->core = $core;
		$this->con = $core->con;
		$this->blog = $core->con->escape($core->blog->id);
		$this->table = $core->con->escape($core->prefix.'pollsfact');
	}

	public function openCursor($ns='poll')
	{
		$tables = array(
			'poll'=>'_p',
			'query'=>'_q',
			'option'=>'_o',
			'response'=>'_r',
			'user'=>'_u'
		);
		if (!isset($tables[$ns])) {
			throw new Exception(__('Failed to open cursor'));
			return;
		}
		return $this->con->openCursor($this->table.$tables[$ns]);
	}

	public function nextID($ns='poll')
	{
		$tables = array(
			'poll'=>'_p',
			'query'=>'_q',
			'option'=>'_o',
			'response'=>'_r',
			'user'=>'_u'
		);
		$fields = array(
			'poll'=>'poll_id',
			'query'=>'query_id',
			'option'=>'option_id',
			'response'=>'response_id',
			'user'=>'user_id'
		);
		if (!isset($tables[$ns])) {
			throw new Exception(__('Failed to get next ID'));
			return;
		}
		return $this->con->select(
			'SELECT MAX('.$fields[$ns].') FROM '.$this->table.$tables[$ns]
		)->f(0) + 1;
	}

	public function nextPos($ns,$id)
	{
		$tables = array(
			'query' => '_q',
			'option' => '_o'
		);
		$fields = array(
			'query' => 'query_pos',
			'option' => 'option_pos'
		);
		$wheres = array(
			'query' => 'poll_id',
			'option' => 'query_id'
		);
		if (!isset($tables[$ns])) {
			throw new Exception(__('Failed to get next position'));
			return;
		}
		$id = (integer) $id;

		return $this->con->select(
			'SELECT MAX('.$fields[$ns].') '.
			'FROM '.$this->table.$tables[$ns].' '.
			'WHERE '.$wheres[$ns].' = '.$id.' '
		)->f(0) + 1;
	}

	public function trigger()
	{
		$this->core->blog->triggerBlog();
	}

	public function getPolls($params=array(),$count_only=false)
	{
		if (!isset($params['columns'])) $params['columns'] = array();
		if (!isset($params['from'])) $params['from'] = '';
		if (!isset($params['sql'])) $params['sql'] = '';

		$params['columns'][] = 'F.poll_id';
		$params['columns'][] = 'F.poll_type';
		$params['columns'][] = 'F.poll_creadt';
		$params['columns'][] = 'F.poll_upddt';
		$params['columns'][] = 'F.poll_strdt';
		$params['columns'][] = 'F.poll_enddt';
		$params['columns'][] = 'F.poll_status';

		$params['from'] .= 'LEFT JOIN '.$this->table.'_p F ON P.post_id = F.post_id ';

		$params['sql'] .= "AND F.poll_type= 'pollsfactory' ";

		if ($this->core->auth->check('admin',$this->core->blog->id)) {
			if (isset($params['poll_status'])) {
				if ($params['poll_status'] !== '') {
					$params['sql'] .= 'AND F.poll_status = '.(integer) $params['poll_status'].' ';
				}
			}
		}
		else {
			$params['sql'] .= 'AND F.poll_status = 1 ';
		}

		$rs = $this->core->blog->getPosts($params,$count_only);
		$rs->pollsFactory = $this;

		return $rs;
	}

	public function addPoll($cur)
	{
		if ($cur->post_id == '') {
			throw new Exception(__('No such post ID'));
		}
		if ($cur->poll_strdt == '') {
			throw new Exception(__('No poll start date'));
		}
		if ($cur->poll_enddt == '') {
			throw new Exception(__('No poll end date'));
		}

		$cur->poll_id = $this->nextID('poll');
		$cur->poll_creadt = date('Y-m-d H:i:s');
		$cur->poll_upddt = date('Y-m-d H:i:s');
		$cur->insert();

		$this->trigger();
		
		return $cur->poll_id;
	}

	public function updPoll($poll_id,$cur)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to update polls'));
		}

		$poll_id = (integer) $poll_id;

		if (empty($poll_id)) {
			throw new Exception(__('No such poll ID'));
		}
		
		//cursor

		$cur->poll_upddt = date('Y-m-d H:i:s');
		$cur->update('WHERE poll_id = '.$poll_id);

		$this->trigger();
	}

	public function delPoll($poll_id)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to delete polls'));
		}

		$poll_id = (integer) $poll_id;

		if (empty($poll_id)) {
			throw new Exception(__('No such poll ID'));
		}
		
		$queries = $this->getQueries(array('poll_id'=>$poll_id),true)->f(0);
		if (!$queries) {
			throw new Exception(__('Poll is not empty'));
		}

		$q = $this->con->execute('DELETE FROM '.$this->table.'_p WHERE poll_id = '.$poll_id.' ');

		if ($q) $this->trigger();
		
		return $q;
	}

	public function getQueries($params,$count_only=false)
	{
		if ($count_only) {
			$q = 'SELECT count(Q.query_id) ';
		}
		else {
			if (!empty($params['no_content'])) {
				$content_req = '';
			}
			else {
				$content_req =
				'Q.query_title, Q.query_desc, ';
			}
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}
			$q =
			'SELECT Q.query_id, Q.poll_id, '.
			$content_req.
			'Q.query_type, Q.query_status, Q.query_pos ';
		}

		$q .=
		'FROM '.$this->table.'_q Q ';

		if (!empty($params['from'])) {
			$q .= $params['from'].' ';
		}

		$q .= $this->reqQueryStatus($params);

		if (isset($params['query_type'])) {
			if (is_array($params['query_type']) && !empty($params['query_type'])) {
				$q .= 'AND Q.query_type '.$this->con->in($params['query_type']);
			}
			elseif ($params['query_type'] != '') {
				$q .= "AND Q.query_type = '".$this->con->escape($params['query_type'])."' ";
			}
		}
		if (!empty($params['query_id'])) {
			if (is_array($params['query_id'])) {
				array_walk($params['query_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['query_id'] = array((integer) $params['query_id']);
			}
			$q .= 'AND Q.query_id '.$this->con->in($params['query_id']);
		}
		if (!empty($params['poll_id'])) {
			if (is_array($params['poll_id'])) {
				array_walk($params['poll_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['poll_id'] = array((integer) $params['poll_id']);
			}
			$q .= 'AND Q.poll_id '.$this->con->in($params['poll_id']);
		}
		if (!empty($params['sql'])) {
			$q .= $params['sql'].' ';
		}
		if (!$count_only) {
			if (!empty($params['order'])) {
				$q .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			}
			else {
				$q .= 'ORDER BY Q.query_pos ASC, Q.query_id ASC ';
			}
		}
		if (!$count_only && !empty($params['limit'])) {
			$q .= $this->con->limit($params['limit']);
		}
		return $this->con->select($q);
	}

	public function addQuery($cur)
	{
		if ($cur->poll_id == '') {
			throw new Exception(__('No such poll ID'));
		}
		
		$types = array('checkbox','radio','combo','field','textarea');
		if ($cur->query_type == '' || !in_array($cur->query_type,$types)) {
			throw new Exception(__('No such query type'));
		}
		
		if ($cur->query_title == '') {
			throw new Exception(__('Query title is empty'));
		}

		$cur->query_id = $this->nextID('query');
		$cur->query_pos = $this->nextPos('query',$cur->poll_id);
		$cur->insert();

		$this->trigger();
		
		return $cur->query_id;
	}

	public function updQuery($query_id,$cur)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to update polls'));
		}

		$query_id = (integer) $query_id;

		if (empty($query_id)) {
			throw new Exception(__('No such query ID'));
		}
		
		//cursor

		$cur->update('WHERE query_id = '.$query_id);

		$this->trigger();
	}

	public function delQuery($query_id)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to delete polls'));
		}

		$query_id = (integer) $query_id;
		if (empty($query_id)) {
			throw new Exception(__('No such poll ID'));
		}

		$options = $this->getOptions(array('query_id'=>$query_id));
		if (!$options->isEmpty()) {
			throw new Exception(__('Query is not empty'));
		}

		$q = $this->con->execute(
			'DELETE FROM '.$this->table.'_q WHERE query_id = '.$query_id.' '
		);

		if ($q) $this->trigger();
		
		return $q;
	}

	protected function reqQueryStatus($params)
	{
		if ($this->core->auth->check('admin',$this->core->blog->id)) {
			if (isset($params['query_status'])) {
					$sign = '=';
					if (substr($params['query_status'],0,1) == '!') {
						$params['query_status'] = substr($params['query_status'],1);
						$sign = '<>';
					}
					return 'WHERE Q.query_status '.$sign.' '.(integer) $params['query_status'].' ';
			}
			else {
				return'WHERE Q.query_status IS NOT NULL ';
			}
		}
		else {
			return 'WHERE Q.query_status = 1 ';
		}
	}

	public function getOptions($params,$count_only=false)
	{
		if ($count_only) {
			$q = 'SELECT count(O.option_id) ';
		}
		else {
			if (!empty($params['no_content'])) {
				$content_req = '';
			}
			else {
				$content_req =
				'O.option_text, Q.query_title, ';
			}
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}
			$q =
			'SELECT O.option_id, O.query_id, O.option_pos, '.
			$content_req.
			'Q.query_type, Q.query_status ';
		}

		$q .=
		'FROM '.$this->table.'_o O '.
		'INNER JOIN '.$this->table.'_q Q ON Q.query_id = O.query_id ';

		if (!empty($params['from'])) {
			$q .= $params['from'].' ';
		}

		$q .= $this->reqQueryStatus($params);

		if (isset($params['query_type'])) {
			if (is_array($params['query_type']) && !empty($params['query_type'])) {
				$q .= 'AND Q.query_type '.$this->con->in($params['query_type']);
			}
			elseif ($params['query_type'] != '') {
				$q .= "AND Q.query_type = '".$this->con->escape($params['query_type'])."' ";
			}
		}
		if (!empty($params['query_id'])) {
			if (is_array($params['query_id'])) {
				array_walk($params['query_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['query_id'] = array((integer) $params['query_id']);
			}
			$q .= 'AND Q.query_id '.$this->con->in($params['query_id']);
		}
		if (!empty($params['option_id'])) {
			if (is_array($params['option_id'])) {
				array_walk($params['option_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['option_id'] = array((integer) $params['option_id']);
			}
			$q .= 'AND R.option_id '.$this->con->in($params['option_id']);
		}
		if (!empty($params['sql'])) {
			$q .= $params['sql'].' ';
		}
		if (!$count_only) {
			if (!empty($params['order'])) {
				$q .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			}
			else {
				$q .= 'ORDER BY O.option_pos ASC, O.option_id ASC ';
			}
		}
		if (!$count_only && !empty($params['limit'])) {
			$q .= $this->con->limit($params['limit']);
		}
		return $this->con->select($q);
	}

	public function addOption($cur)
	{
		if ($cur->query_id == '') {
			throw new Exception(__('No such query ID'));
		}
		if ($cur->option_text == '') {
			throw new Exception(__('Option_text is empty'));
		}

		$cur->option_id = $this->nextID('option');
		$cur->option_pos = $this->nextPos('option',$cur->query_id);
		$cur->insert();

		$this->trigger();

		return $cur->option_id;
	}

	public function delOption($option_id)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to delete polls'));
		}

		$option_id = (integer) $option_id;

		if (empty($option_id)) {
			throw new Exception(__('No such option ID'));
		}

		$q = $this->con->execute(
			'DELETE FROM '.$this->table.'_o WHERE option_id = '.$option_id.' '
		);

		if ($q) $this->trigger();
		
		return $q;
	}

	public function getResponses($params,$count_only=false)
	{
		if ($count_only) {
			$q = 'SELECT count(R.response_id) ';
		}
		else {
			if (!empty($params['no_content'])) {
				$content_req = '';
			}
			else {
				$content_req =
				'R.response_text, Q.query_title, ';
			}
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}
			$q =
			'SELECT R.response_id, R.query_id, R.user_id, R.response_selected, '.
			$content_req.
			'Q.query_type, Q.query_status ';
		}

		$q .=
		'FROM '.$this->table.'_r R '.
		'INNER JOIN '.$this->table.'_q Q ON Q.query_id = R.query_id ';

		if (!empty($params['from'])) {
			$q .= $params['from'].' ';
		}

		$q .= $this->reqQueryStatus($params);

		if (isset($params['query_type'])) {
			if (is_array($params['query_type']) && !empty($params['query_type'])) {
				$q .= 'AND Q.query_type '.$this->con->in($params['query_type']);
			}
			elseif ($params['query_type'] != '') {
				$q .= "AND Q.query_type = '".$this->con->escape($params['query_type'])."' ";
			}
		}
		if (!empty($params['query_id'])) {
			if (is_array($params['query_id'])) {
				array_walk($params['query_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['query_id'] = array((integer) $params['query_id']);
			}
			$q .= 'AND Q.query_id '.$this->con->in($params['query_id']);
		}
		if (!empty($params['response_id'])) {
			if (is_array($params['response_id'])) {
				array_walk($params['response_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			}
			else {
				$params['response_id'] = array((integer) $params['response_id']);
			}
			$q .= 'AND R.response_id '.$this->con->in($params['response_id']);
		}
		if (!empty($params['user_id'])) {
			$q .= "AND R.user_id = '".$this->con->escape($params['user_id'])."' ";
		}
		if (isset($params['response_selected'])) {
			$q .= 'AND R.response_selected = '.(integer) $params['response_selected'].' ';
		}
		if (!empty($params['sql'])) {
			$q .= $params['sql'].' ';
		}
		if (!$count_only) {
			if (!empty($params['order'])) {
				$q .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			}
			else {
				$q .= 'ORDER BY R.response_id ASC ';
			}
		}
		if (!$count_only && !empty($params['limit'])) {
			$q .= $this->con->limit($params['limit']);
		}
		return $this->con->select($q);
	}

	public function addResponse($cur)
	{
		if ($cur->query_id == '') {
			throw new Exception(__('No such query ID'));
		}
		if ($cur->user_id == '') {
			throw new Exception(__('No such user ID'));
		}
		if (null === $cur->response_text) {
			$cur->response_text = '';
		}

		$cur->response_id = $this->nextId('response');
		$cur->insert();
		
		$this->trigger();

		return $cur->response_id;
	}

	public function updResponse($response_id,$cur)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to update polls'));
		}

		$response_id = (integer) $response_id;

		if (empty($response_id)) {
			throw new Exception(__('No such response ID'));
		}
		
		//cursor

		$cur->update('WHERE response_id = '.$response_id);

		$this->trigger();
	}

	public function delResponses($query_id)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to delete polls'));
		}

		$query_id = (integer) $query_id;

		if (empty($query_id)) {
			throw new Exception(__('No such query ID'));
		}

		$q = $this->con->execute(
			'DELETE FROM '.$this->table.'_r WHERE query_id = '.$query_id.' '
		);

		if ($q) $this->trigger();
		
		return $q;
	}

	public function delUsers($poll_id)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to delete polls'));
		}

		$poll_id = (integer) $poll_id;

		if (empty($poll_id)) {
			throw new Exception(__('No such poll ID'));
		}

		return $this->con->execute(
			'DELETE FROM '.$this->table.'_u WHERE poll_id = '.$poll_id.' '
		);
	}

	public function hasUser($poll_id)
	{
		$chk = false;
		$poll_id = (integer) $poll_id;
		$ident = (integer) $this->core->blog->settings->pollsFactory_user_ident;

		# Cookie
		if($ident < 2)
		{
			$list = isset($_COOKIE['pollsFactoryVotes']) ?
				explode(',',$_COOKIE['pollsFactoryVotes']) : array();

			if(in_array($poll_id,$list)) $chk = true;
		}
		# IP
		if($ident > 0)
		{
			$rs = $this->con->select(
				'SELECT user_id FROM '.$this->table.'_u '.
				'WHERE poll_id = '.$poll_id.' '.
				"AND user_ip = '".$this->con->escape(http::realIP())."' "
			);
			if (!$rs->isEmpty()) $chk = true;
		}
		return $chk;
	}

	public function setUser($poll_id)
	{
		$poll_id = (integer) $poll_id;

		# Cookie
		if($this->core->blog->settings->pollsFactory_user_ident < 2)
		{
			$list = isset($_COOKIE['pollsFactoryVotes']) ?
				explode(',',$_COOKIE['pollsFactoryVotes']) : array();

			$list[] = $poll_id;
			setcookie('pollsFactoryVotes',implode(',',$list),time()+60*60*24*30,'/');
		}
		# IP
		$ip = $this->core->blog->settings->pollsFactory_user_ident > 0 ?
			$this->con->escape(http::realIP()) :
			substr($this->core->getNonce(),0,24);

		# Always populated user table (for counter)
		$cur = $this->openCursor('user');
		$cur->user_id = $this->nextID('user');
		$cur->poll_id = $poll_id;
		$cur->user_upddt = date('Y-m-d H:i:s');
		$cur->user_ip = $ip;
		$cur->insert();

		return $cur->user_id;
	}

	public function countUsers($poll_id)
	{
		$poll_id = (integer) $poll_id;

		return (integer) $this->con->select(
			'SELECT COUNT(user_id) FROM '.$this->table.'_u '.
			'WHERE poll_id = '.$poll_id.' '
		)->f(0);
	}

	public function lastUsersVote($poll_id)
	{
		$poll_id = (integer) $poll_id;

		$rs = $this->con->select(
			'SELECT user_upddt FROM '.$this->table.'_u '.
			'WHERE poll_id = '.$poll_id.' ORDER BY user_upddt DESC '.
			$this->con->limit(1)
		);
		if ($rs->isEmpty()) {
			return false;
		}
		return strtotime($rs->user_upddt);
	}
}
?>