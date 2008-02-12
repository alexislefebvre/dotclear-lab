<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Carnaval', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Carnaval' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class dcCarnaval
{
	private $blog;
	private $con;
	private $table;
	
	public function __construct(&$blog)
	{
		$this->blog =& $blog;
		$this->con =& $blog->con;
		$this->table = $this->blog->prefix.'carnaval';
	}
	
	public function getClasses($params=array())
	{
		$strReq =
			'SELECT class_id, comment_author, comment_author_mail, '.
			'comment_author_site, comment_class '.
			'FROM '.$this->table.' '.
			"WHERE blog_id = '".$this->con->escape($this->blog->id)."' ";
		
		if (isset($params['class_id'])) {
			$strReq .= 'AND class_id = '.(integer) $params['class_id'].' ';
		}
		if (isset($params['mail'])) {
			$strReq .= 'AND comment_author_mail = \''.
				$this->con->escape($params['mail']).'\'';
		}
		if (isset($params['site'])) {
			$strReq
				.= 'AND \''.$this->con->escape($params['site']).'\' '.
				'LIKE CONCAT(comment_author_site,\'%\')';
		}
		return $this->con->select($strReq);
	}
	
	public function getClass($id)
	{
		return $this->getClasses(array('class_id'=>$id));
	}
	
	public function addClass($author,$mail,$site='',$class)
	{
		$cur = $this->con->openCursor($this->table);
		
		$cur->blog_id = (string) $this->blog->id;
		$cur->comment_author = (string) $author;
		$cur->comment_author_mail = (string) $mail;		$cur->comment_author_site  = (string) $site;
		$cur->comment_class = (string) $class;
		
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
	
	public function updateClass($id,$author,$mail='',$site='',$class='')
	{
		$cur = $this->con->openCursor($this->table);
		$cur->comment_author = $author;
		$cur->comment_author_mail = $mail;
		$cur->comment_author_site  = $site;
		$cur->comment_class = $class;
		
		if ($cur->comment_author == '') {
			throw new Exception(__('You must provide a name'));
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
		$rs = $this->getClasses(array('mail'=>$mail));
		return $rs->isEmpty() ? '' : ' '.$rs->comment_class;
	}
	
	public function getPingClass($site)
	{
		$rs = $this->getClasses(array('site'=>$site));
		return $rs->isEmpty() ? '' : ' '.$rs->comment_class;
	}
}
?>
