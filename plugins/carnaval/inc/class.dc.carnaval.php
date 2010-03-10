<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Carnaval a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Me and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class dcCarnaval
{
	private $blog;
	private $con;
	private $table;
	
	public $found;	// Avoid multiple SQL requests
	
	public function __construct($blog)
	{
		$this->blog =& $blog;
	
		$this->con =$this->blog->con;
		$this->table = $this->blog->prefix.'carnaval';
		
		$this->found  = array(
			'comments'=>array(),
			'pings'=>array()
		);
	}

	public function getClasses($params=array())
	{
		$strReq =
			'SELECT class_id, comment_author, comment_author_mail, '.
			'comment_author_site, comment_class, '.
			'comment_text_color, comment_background_color '.
			'FROM '.$this->table.' '.
			"WHERE blog_id = '".$this->con->escape($this->blog->id)."' ";

		if (isset($params['class_id'])) {
			$strReq .= 'AND class_id = '.(integer) $params['class_id'].' ';
		}
		if (isset($params['mail'])) {
			$strReq .= 'AND comment_author_mail <> \'\' '.
				'AND comment_author_mail = \''.
				$this->con->escape($params['mail']).'\'';
		}
		if (isset($params['site'])) {
			$strReq .= 'AND comment_author_site <> \'\' '.
				'AND \''.$this->con->escape($params['site']).'\' '.
				//'LIKE CONCAT(comment_author_site,\'%\')';
				//'LIKE \'%\'comment_author_site\'%\'';
				'LIKE comment_author_site';
		}
		return $this->con->select($strReq);
	}

	public function getClass($id)
	{
		return $this->getClasses(array('class_id'=>$id));
	}

	public function addClass($author,$mail,$site='',$text,$backg,$class)
	{
		$cur = $this->con->openCursor($this->table);

		$cur->blog_id = (string) $this->blog->id;
		$cur->comment_author = (string) $author;
		$cur->comment_author_mail = (string) $mail;
		$cur->comment_author_site  = (string) $site;
		$cur->comment_class = (string) $class;
		$cur->comment_text_color  = (string) $text;
		$cur->comment_background_color = (string) $backg;

		if ($cur->comment_author == '') {
			throw new Exception(__('You must provide a name'));
		}
		if ($cur->comment_class == '') {
			throw new Exception(__('You must provide a CSS Class'));
		}
		if ($cur->comment_author_mail == '' && $cur->comment_author_site == '') {
			throw new Exception(__('You must provide an e-mail or a web site adress'));
		}

		$strReq = 'SELECT MAX(class_id) FROM '.$this->table;

		$rs = $this->con->select($strReq);
		$cur->class_id = (integer) $rs->f(0) + 1;
		$cur->insert();

		$this->blog->triggerBlog();
	}

	public function updateClass($id,$author,$mail='',$site='',$text,$backg,$class='')
	{
		$cur = $this->con->openCursor($this->table);

		$cur->comment_author_mail = $mail;
		$cur->comment_author_site  = $site;
		$cur->comment_class = $class;
		$cur->comment_text_color  = $text;
		$cur->comment_background_color = $backg;

		if ($author != '') {
			$cur->comment_author = $author;
		}
		if ($cur->comment_class == '') {
			throw new Exception(__('You must provide a CSS Class'));
		}
		if ($cur->comment_author_mail == '' && $cur->comment_author_site == '') {
			throw new Exception(__('You must provide an e-mail or a web site adress'));
		}

		$cur->update('WHERE class_id = '.(integer) $id.
			" AND blog_id = '".$this->con->escape($this->blog->id)."'");

		$this->blog->triggerBlog();
	}


	public function delClass($id)
	{
		$id = (integer) $id;

		$strReq = 'DELETE FROM '.$this->table.' '.
				"WHERE blog_id = '".$this->con->escape($this->blog->id)."' ".
				'AND class_id = '.$id.' ';

		$this->con->execute($strReq);
		$this->blog->triggerBlog();
	}

	public function getCommentClass($mail)
	{
		if (isset($this->found['comments'][$mail])) {
			return $this->found['comments'][$mail];
		}
		
		$rs = $this->getClasses(array('mail'=>$mail));
		$this->found['comments'][$mail] =
			$rs->isEmpty() ? '' : ' '.$rs->comment_class;
		
		return $this->found['comments'][$mail];
	}

	public function getPingClass($site)
	{
		if (isset($this->found['pings'][$site])) {
			return $this->found['pings'][$site];
		}
		
		$rs = $this->getClasses(array('site'=>$site));
		$this->found['pings'][$site] =
			$rs->isEmpty() ? '' : ' '.$rs->comment_class;
			
		return $this->found['pings'][$site];
	}
}
?>
