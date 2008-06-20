<?php
# -- BEGIN LICENSE BLOCK ---------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2003-2008 Bruno Hondelatte,  and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ---------------------------------- */


// Cannot extend properly meta class because of private attributes ...
class MetaPlus
{
	protected $core;
	protected $table;
	protected $con;
	protected $meta;

	public function __construct (&$core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
		$this->table = $this->core->prefix.'meta';
		$this->meta = new dcMeta($core);
	}

	public function massSetPostMeta ($meta_array = array()) {
		$meta_values = array();
		/* SQL hack here :
		   * PostgresQL < 8.2 does not support multi-row inserts
		   * Mysql does not support multi-lines queries
		*/
		if (DC_DBDRIVER == 'pgsql') {
			$strReq="";
			foreach ($meta_array as $meta) {
				$post_ids[$meta['post_id']]="1";
			$strReq .= "INSERT INTO ".$this->table.
				'(post_id,meta_id,meta_type) VALUES '.
				"('".$meta['post_id']."','".
					$this->con->escape($meta['meta_id'])."','".
					$this->con->escape($meta['meta_type'])."');";
			}
		} else {
			$meta_values = array();
			foreach ($meta_array as $meta) {
				$post_ids[$meta['post_id']]="1";
				$meta_values[] = "(".$meta['post_id'].",'".
					$this->con->escape($meta['meta_id'])."','".
					$this->con->escape($meta['meta_type'])."')";
			}
			$strReq = "INSERT INTO ".$this->table.
				'(post_id,meta_id,meta_type) VALUES '.join(',',$meta_values);
		}
		$this->con->execute($strReq);
		foreach ($post_ids as $post_id => $val)
			$this->updatePostMeta($post_id);

	}

	public function massDelPostMeta ($post_id=null, $type=null, $meta_id_list = array()) {
		$strReq = "DELETE FROM ".$this->core->prefix.'meta '.
			"WHERE meta_id ".$this->con->in($meta_id_list).' ';
		if ($post_id != null)
			$strReq .= 'AND post_id = '.(integer)$post_id.' ';
		if ($type != null)
			$strReq .= "AND meta_type = '".$this->con->escape($type)."' ";
		$this->con->execute($strReq);
		$this->updatePostMeta($post_id);

	}

	protected function updatePostMeta($post_id)
	{
		$post_id = (integer) $post_id;
		
		$strReq = 'SELECT meta_id, meta_type '.
				'FROM '.$this->table.' '.
				'WHERE post_id = '.$post_id.' ';
		
		$rs = $this->con->select($strReq);
		
		$meta = array();
		while ($rs->fetch()) {
			$meta[$rs->meta_type][] = $rs->meta_id;
		}
		
		$post_meta = serialize($meta);
		
		$cur = $this->con->openCursor($this->core->prefix.'post');
		$cur->post_meta = $post_meta;
		
		$cur->update('WHERE post_id = '.$post_id);
		$this->core->blog->triggerBlog();
	}
	

}
?>
