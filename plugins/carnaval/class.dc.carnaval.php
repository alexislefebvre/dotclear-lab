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
	private static $blog;
	private static $con;
	private static $table;
	
	public static $found;	// Avoid multiple SQL requests

	public static function init(&$blog)
	{
		self::$blog =& $blog;
		self::$con =& $blog->con;
		self::$table = $blog->prefix.'carnaval';
		
		self::$found  = array(
			'comments'=>array(),
			'pings'=>array()
		);
	}

	public static function getClasses($params=array())
	{
		$strReq =
			'SELECT class_id, comment_author, comment_author_mail, '.
			'comment_author_site, comment_class '.
			'FROM '.self::$table.' '.
			"WHERE blog_id = '".self::$con->escape(self::$blog->id)."' ";

		if (isset($params['class_id'])) {
			$strReq .= 'AND class_id = '.(integer) $params['class_id'].' ';
		}
		if (isset($params['mail'])) {
			$strReq .= 'AND comment_author_mail <> \'\' '.
				'AND comment_author_mail = \''.
				self::$con->escape($params['mail']).'\'';
		}
		if (isset($params['site'])) {
			$strReq .= 'AND comment_author_site <> \'\' '.
				'AND \''.self::$con->escape($params['site']).'\' '.
				'LIKE CONCAT(comment_author_site,\'%\')';
		}
		return self::$con->select($strReq);
	}

	public static function getClass($id)
	{
		return self::getClasses(array('class_id'=>$id));
	}

	public static function addClass($author,$mail,$site='',$class)
	{
		$cur = self::$con->openCursor(self::$table);

		$cur->blog_id = (string) self::$blog->id;
		$cur->comment_author = (string) $author;
		$cur->comment_author_mail = (string) $mail;
		$cur->comment_author_site  = (string) $site;
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

		$strReq = 'SELECT MAX(class_id) FROM '.self::$table;

		$rs = self::$con->select($strReq);
		$cur->class_id = (integer) $rs->f(0) + 1;
		$cur->insert();

		self::$blog->triggerBlog();
	}

	public static function updateClass($id,$author,$mail='',$site='',$class='')
	{
		$cur = self::$con->openCursor(self::$table);
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
			" AND blog_id = '".self::$con->escape(self::$blog->id)."'");

		self::$blog->triggerBlog();
	}


	public static function delClass($id)
	{
		$id = (integer) $id;

		$strReq = 'DELETE FROM '.self::$table.' '.
				"WHERE blog_id = '".self::$con->escape(self::$blog->id)."' ".
				'AND class_id = '.$id.' ';

		self::$con->execute($strReq);
		self::$blog->triggerBlog();
	}

	public static function getCommentClass($mail)
	{
		if (isset(self::$found['comments'][$mail])) {
			return self::$found['comments'][$mail];
		}
		
		$rs = self::getClasses(array('mail'=>$mail));
		self::$found['comments'][$mail] =
			$rs->isEmpty() ? '' : ' '.$rs->comment_class;
		
		return self::$found['comments'][$mail];
	}

	public static function getPingClass($site)
	{
		if (isset(self::$found['pings'][$site])) {
			return self::$found['pings'][$site];
		}
		
		$rs = self::getClasses(array('site'=>$site));
		self::$found['pings'][$site] =
			$rs->isEmpty() ? '' : ' '.$rs->comment_class;
			
		return self::$found['pings'][$site];
	}
}
?>
