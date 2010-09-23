<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ExpAt,
# a plugin for DotClear2.
#
# Copyright (c) 2010 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class expatDict {
	private $obj;
	private $functions;
	
	public static $php_functions = array("substr", "strlen",
		array("cut_string","text::cutString"),
		array("remove_html","context::remove_html"),
		array("encode_html","html::escapeHTML"),);

	public function __construct($core) {
		$this->obj = array();
		$this->functions = array();
		foreach (self::$php_functions as $f) {
			if (is_array($f))
				$this->registerFunction (new expatBasicFunction($f[0],$f[1]));
			else
				$this->registerFunction (new expatBasicFunction($f));
		}
		$this->registerFunction (new expatStrCatFunction());
		$this->registerObject(new expatDynObject());
		$this->registerObject(
			new expatContextObject(
				"entry", "posts",
				array (
					"date" => "post_date",
					"content" => "getContent(0)",
					"excerpt" => "getExcerpt()",
					"title" => "post_title",
					"id" => "post_id",
					"type" => "post_type",
					"url" => "post_url",
					"cat_id" => "cat_id",
					"cat_url" => "cat_url",
					"selected" => "post_selected",
					"author" => "getAuthorCN()",
					"extended" => "isExtended()"
				)
			)
		);

		$this->registerObject(
			new expatContextObject(
				"category", "categories",
				array (
					"id" => "cat_id",
					"url" => "cat_url",
					"title" => "cat_title",
					"nb_post" => "nb_post",
					"desc" => "cat_desc"
				)
			)
		);

		$this->registerObject(
			new expatGlobalObject(
				"system",
				array (
					"blog_lang" => "\$core->blog->settings->system->lang",
					"current_tpl" => "\$_ctx->current_tpl",
					"url_type" => "\$core->url->type",
					"comments_active" => "\$core->blog->settings->system->allow_comments",
					"pings_active" => "\$core->blog->settings->system->allow_trackbacks",
					"wiki_comments" => "\$core->blog->settings->system->wiki_comments",
					"search_count" => "(isset(\$_search_count)?\$_search_count:0)"
				)
			)
		);
	}
	
	public function getObject($object) {
		if (!isset($this->obj[$object])) {
			return false;
		} else {
			return $this->obj[$object];
		}
	}
	
	public function registerObject($obj) {
		$this->obj[$obj->getName()]=$obj;
	}
	
	public function registerFunction($func) {
		$this->functions[$func->getName()]=$func;
	}
	
	public function getFunctionCode($f,$args) {
		if (!isset($this->functions[$f]))
			return false;
		return $this->functions[$f]->call($args);
	}
	
}

class expatObject {
	protected $name;
	
	public function __construct($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}
	
	public function getAttribute($att) {
		return false;
	}

	public function getMethod($method) {
		return false;
	}
	
}

class expatContextObject extends expatObject {
	protected $code_name;
	protected $attributes;
	protected $methods;

	private static $common_attr = array(
		"index" => "index()",
		"count" => "count()",
		"isFirst" => "isStart()",
		"isLast" => "isEnd()"
	);
	
	public function __construct($name,$context_name,$attributes=array()) {
		$this->name = $name;
		$this->attributes = array_merge($attributes,self::$common_attr);
		$this->code_name = '$_ctx->'.$context_name;
	}

	public function getAttribute($att) {
		if (!isset($this->attributes[$att])) {
			return false;
		}
		return $this->code_name.'->'.$this->attributes[$att];
	}
	
	
}

class expatGlobalObject extends expatObject {
	protected $attributes;
	
	public function __construct($name,$attributes=array()) {
		$this->name = $name;
		$this->attributes = $attributes;
	}

	public function getAttribute($att) {
		if (!isset($this->attributes[$att])) {
			return false;
		}
		return $this->attributes[$att];
	}
}
class expatDynObject extends expatObject {
	protected $code_name;

	public function __construct() {
		$this->name = "my";
		$this->code_name = '$my';
	}

	public function getAttribute($att) {
		return $this->code_name."['".$att."']";
	}
	
}
abstract class expatFunction {
	protected $name;
	public function __construct($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	function call($args){}
}

class expatBasicFunction extends expatFunction {
	protected $php_name;
	public function __construct($name,$php_name=null) {
		parent::__construct($name);
		if ($php_name == null)
			$this->php_name = $this->name;
		else
			$this->php_name = $php_name;
	}
	public function call($args) {
		return $this->php_name.'('.join(',',$args).')';
	}
}

class expatStrcatFunction extends expatFunction {
	public function __construct() {
		parent::__construct("strcat");
	}
	public function call($args) {
		return join('.',$args);
	}
}
?>

