<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class postWidgetText
{
	public $core;
	public $con;
	public $table;

	public function __construct($core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
		$this->table = $this->core->prefix.'post_wtext';
	}

	public function get($post_id,$type='post_wtext')
	{
		$rs = $this->core->con->select(
			'SELECT post_id,wtext_type,wtext_content,wtext_content_xhtml,wtext_words '.
			'FROM '.$this->table.' '.
			'WHERE post_id='.(integer) $post_id.' '.
			'AND wtext_type=\''.$this->con->escape($type).'\' '.
			'LIMIT 1'
		);
		return $rs;
	}

	public function set($post_id,$content='',$type='postwidgettext',$post_format='xhtml',$post_lang='en')
	{
		if (!trim($content)) return;

		$this->core->blog->setPostContent(
			$post_id,$post_format,$post_lang,
			$content,$content_xhtml,$empty,$empty_xhtml
		);

		$cur = $this->core->con->openCursor($this->table);
		$cur->post_id = (integer) $post_id;
		$cur->wtext_type = $type;
		$cur->wtext_content =  $content;
		$cur->wtext_content_xhtml =  $content_xhtml;
		$cur->wtext_words = empty($content_xhtml) ? 
			'' : implode(' ',text::splitWords($content_xhtml));
		$cur->insert();
	}

	public function del($post_id,$type='')
	{
		$post_id = (integer) $post_id;
		if (empty($type)) {
			throw new Exception('no type given for post widget text');
			return false;
		}

		$this->con->execute(
			'DELETE FROM '.$this->table.' '.
			'WHERE post_id = '.$post_id.' '.
			'AND wtext_type = \''.$this->con->escape($type).'\' '
		);
	}
}
?>