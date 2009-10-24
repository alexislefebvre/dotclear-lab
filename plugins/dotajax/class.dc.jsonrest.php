<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Clearbricks.
# Copyright (c) 2006 Olivier Meunier and contributors. All rights
# reserved.
#
# Clearbricks is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Clearbricks is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Clearbricks; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

if (!function_exists('myjson_encode'))
{
  function myjson_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = myjson_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = myjson_encode($k).':'.myjson_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

class jsonRestServer
{
	public $rsp;
	private $services;
	private $core;

	public function __construct($core)
	{
		$this->core = $core;
		$this->services=array();
		$this->rsp = array();
	}

	public function register($name,$restClass) {
		$this->services[$name]=$restClass;
	}

	
	public function serve($service,$encoding='UTF-8')
	{
		$get = array();
		if (isset($_GET)) {
			$get = $_GET;
		}
		
		$post = array();
		if (isset($_POST)) {
			$post = $_POST;
		}
		
		
		if (!isset($this->services[$service])) {
			$this->rsp['status'] = 'failed';
			$this->rsp['message']='Service does not exist';
			$this->getJSON($encoding);
			return false;
		}
		$restClass=$this->services[$service];
		if (!isset($_REQUEST['f'])) {
			$this->rsp['status'] = 'failed';
			$this->rsp['message']='No function given';
			$this->getJSON($encoding);
			return false;
		}
		$call=array($restClass,$_REQUEST['f']);	
		if (!is_callable($call)) {
			$this->rsp['status'] = 'failed';
			$this->rsp['message']='Function does not exist';
			$this->getJSON($encoding);
			return false;
		}
		try {
			$res = call_user_func($call,$this->core,$get,$post);
			$this->rsp['data'] = $res;
		} catch (Exception $e) {
			$this->rsp['status'] = 'failed';
			$this->rsp['message']=$e->getMessage();
			$this->getJSON($encoding);
			return false;
		}
		
		$this->rsp['status'] = 'ok';
		
		$this->getJSON($encoding);
		return true;
	}
	
	private function getJSON($encoding='UTF-8')
	{
		header('Content-Type: text/plain; charset='.$encoding);
		echo myjson_encode($this->rsp);

	}

	public static function getFilteredParams($allowed_params=array(),$get) {
		$params=array();

		foreach ($allowed_params as $key => $value) {
			if (is_int($key))
				$key = $value;
			if (!empty($get[$key]))
				$params[$value]=$get[$key];
		}
		return $params;
	}

}