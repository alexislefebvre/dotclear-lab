<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Grumph,
# a plugin for DotClear2.
#
# Copyright (c) 2010 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------

class dcGrumph
{
	protected $core;
	
	// Supported resource types
	public static $resource_types = array(
		'a' => 'link',
		'img' => 'image',
		'audio' => 'audio',
		'video' => 'video',
		'object' => 'object',
		'embed' => 'embed'); 
	
	public static $scopes = array(
		'excerpt' => 1,
		'content' => 2);
	
	public function __construct(&$core)
	{
		$this->core =& $core;
	}
		
	/**
	 * grabResources
	 * 
	 * Grabs resources inside a post
	 * @param cursor post to inspect
	 * @access public
	 * @return array the list of resources
	 */
	public function grabResources($cur) {
		$content = $cur->post_excerpt_xhtml.'<sep src="content"/>'.$cur->post_content_xhtml;
		$regex = '#<('.join('|',array_keys(self::$resource_types)).'|sep) [^>]*?(src|href)="(.*?)"[^>]*?>#i';
		$count = preg_match_all($regex,$content,$matches,PREG_SET_ORDER);
		$data=array();
		$scope='excerpt';
		$public_url = rawurldecode(
			$this->core->blog->host.
			path::clean($this->core->blog->settings->system->public_url));
		
		foreach ($matches as $k => $match) {
			if ($match[1] == 'sep') {
				$scope=$match[3];
			} else {
				$type = self::$resource_types[strtolower($match[1])];
				$url = $match[3];
				if (preg_match('#(alt|title)="([^"]+)"#',$match[0],$descmatch)) {
					$desc = $descmatch[2];
				} else {
					$desc = null;
				}
				$internal = !(preg_match ('#^(http|https|ftp):#',$url) 
					|| (strpos($url,$public_url) !== false));
			
				$value = array(
					"u" => html::clean($url),
					"t" => $type,
					"d" => $desc,
					"i" => ($internal?1:0),
					"s" => self::$scopes[$scope]
				);
				if ($desc != null) {
					$value['d']=$desc;
				}
				$data[]=$value;
				
			}
		}
		return $data;
	}
	
	public function getPostResources($cur) {
		if ($cur->post_res == null)
			return array();
		return unserialize($cur->post_res);
	}
	
	public function updatePostResources($rs) {
		$res = $this->grabResources($rs);
		$strReq = 'UPDATE '.$this->core->prefix.'post '.
			"SET post_res='".$this->core->con->escape(serialize($res))."' ".
			"WHERE post_id=".$rs->post_id." ";
		return $this->core->con->execute($strReq);	
	}
	
	public function getResources($post,$params) {
		if (isset($post->post_res)) {
			$res = unserialize($post->post_res);
		} else {
			$strReq = 'SELECT post_res FROM '.$this->core->prefix.'post '.
				'WHERE post_id='.((integer)$post->post_id);
			$res = @unserialize($this->core->con->select($strReq)->f(0));		
		}
		if (!is_array($res))
			return array();
		$filters = array();
		if (isset($params['types'])) {
			$filters[] = new GrumphTypeFilter($params['types']);
		}
		
		if (isset($params['internal'])) {
			$filters[] = new GrumphInternalFilter($params['internal']);
		}
		
		if (isset($params['scope'])) {
			$filters[] = new GrumphScopeFilter($params['scope']);
		}
		switch (sizeof($filters)) {
			case 0 :
				return $res;
			case 1 :
				return array_filter($res,array($filters[0],"filter"));
			default :
				return array_filter($res,array(new GrumphAndFilter($filters),"filter"));
		}
	}
}

interface GrumphFilter {
	public function filter($entry);
}


class GrumphTypeFilter implements GrumphFilter {
	private $types = array();
	
	public function __construct($types) {
		$this->types = $types;
	}
	public function filter($entry) {
		return in_array($entry['t'],$this->types);
	}
}

class GrumphAndFilter implements GrumphFilter {
	private $filters = array();
	
	public function __construct($filters) {
		$this->filters = $filters;
	}
	public function filter($entry) {
		foreach ($this->filters as $filter) {
			if (!$filter->filter($entry))
				return false;
		}
		return true;
	}
}

class GrumphOrFilter implements GrumphFilter {
	private $filters = array();
	
	public function __construct($filters) {
		$this->types = $types;
	}
	public function filter($entry) {
		foreach ($this->filters as $filter) {
			if ($filter->filter($entry))
				return true;
		}
		return false;
	}
}

class GrumphInternalFilter implements GrumphFilter {
	private $internal;

	public function __construct($internal) {
		$this->internal = $internal;
	}
	public function filter($entry) {
		return $entry['i']==$this->internal;
	}
}

class GrumphScopeFilter implements GrumphFilter {
	private $scope;

	public function __construct($scope) {
		$this->scope = $scope;
	}
	public function filter($entry) {
		return ($this->scope == 'all') || ($entry['s']==dcGrumph::$scopes[$this->scope]);
	}
}
?>
