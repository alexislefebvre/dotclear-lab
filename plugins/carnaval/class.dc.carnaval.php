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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

class dcCarnaval
{
	private static $blog;
	private static $con;
	private static $table;
	
	public static $found;	// Avoid multiple SQL requests

	public static function cssPath()
	{
		global $core;
		return path::real($core->blog->public_path).'/carnaval-css';
	}
	
	public static function cssURL()
	{
		global $core;
		return $core->blog->settings->public_url.'/carnaval-css';
	}

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
			'comment_author_site, comment_class, '.
			'comment_text_color, comment_background_color '.
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

	public static function addClass($author,$mail,$site='',$text,$backg,$class)
	{
		$cur = self::$con->openCursor(self::$table);

		$cur->blog_id = (string) self::$blog->id;
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

		$strReq = 'SELECT MAX(class_id) FROM '.self::$table;

		$rs = self::$con->select($strReq);
		$cur->class_id = (integer) $rs->f(0) + 1;
		$cur->insert();

		self::$blog->triggerBlog();
	}

	public static function updateClass($id,$author,$mail='',$site='',$text,$backg,$class='')
	{
		$cur = self::$con->openCursor(self::$table);
		$cur->comment_author = $author;
		$cur->comment_author_mail = $mail;
		$cur->comment_author_site  = $site;
		$cur->comment_class = $class;
		$cur->comment_text_color  = $text;
		$cur->comment_background_color = $backg;

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
class carnavalConfig
{
	public static function adjustColor($c)
	{
		if ($c === '') {
			return '';
		}
		
		$c = strtoupper($c);
		
		if (preg_match('/^[A-F0-9]{3,6}$/',$c)) {
			$c = '#'.$c;
		}
		
		if (preg_match('/^#[A-F0-9]{6}$/',$c)) {
			return $c;
		}
		
		if (preg_match('/^#[A-F0-9]{3,}$/',$c)) {
			return '#'.substr($c,1,1).substr($c,1,1).substr($c,2,1).substr($c,2,1).substr($c,3,1).substr($c,3,1);
		}
		
		return '';
	}
	
	public static function imagesPath()
	{
		global $core;
		return path::real($core->blog->public_path).'/carnaval-images';
	}
	
	public static function imagesURL()
	{
		global $core;
		return $core->blog->settings->public_url.'/carnaval-images';
	}
	
	public static function canWriteImages($create=false)
	{
		global $core;
		
		$public = path::real($core->blog->public_path);
		$imgs = self::imagesPath();
		
		if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng') || !function_exists('imagecreatefrompng')) {
			return false;
		}
		
		if (!is_dir($public)) {
			return false;
		}
		
		if (!is_dir($imgs)) {
			if (!is_writable($public)) {
				return false;
			}
			if ($create) {
				files::makeDir($imgs);
			}
			return true;
		}
		
		if (!is_writable($imgs)) {
			return false;
		}
		
		return true;
	}
	
	
	public static function createImages($color,$name)
	{
		if (!self::canWriteImages(true)) {
			throw new Exception(__('Unable to create images.'));
		}
				
		$comment_t = dirname(__FILE__).'/../../plugins/blowupConfig/alpha-img/comment-t.png';
		$comment_b = dirname(__FILE__).'/../../plugins/blowupConfig/alpha-img/comment-b.png';

		$cval_comment_t = $name.'-comment-t.png';
		$cval_comment_b = $name.'-comment-b.png';
		
		self::dropImage($cval_comment_t);
		self::dropImage($cval_comment_b);
		
		$color = self::adjustColor($color);
		
		self::commentImages($color,$comment_t,$comment_b,$cval_comment_t,$cval_comment_b);

	}
	
	protected static function commentImages($comment_color,$comment_t,$comment_b,$dest_t,$dest_b)
	{
		$comment_color = sscanf($comment_color,'#%2X%2X%2X');
			
		$d_comment_t = imagecreatetruecolor(500,25);
		$fill = imagecolorallocate($d_comment_t,$comment_color[0],$comment_color[1],$comment_color[2]);
		imagefill($d_comment_t,0,0,$fill);
		
		$s_comment_t = imagecreatefrompng($comment_t);
		imagealphablending($s_comment_t,true);
		imagecopy($d_comment_t,$s_comment_t,0,0,0,0,500,25);
		
		imagepng($d_comment_t,self::imagesPath().'/'.$dest_t);
		imagedestroy($d_comment_t);
		imagedestroy($s_comment_t);
		
		$d_comment_b = imagecreatetruecolor(500,7);
		$fill = imagecolorallocate($d_comment_b,$comment_color[0],$comment_color[1],$comment_color[2]);
		imagefill($d_comment_b,0,0,$fill);
		
		$s_comment_b = imagecreatefrompng($comment_b);
		imagealphablending($s_comment_b,true);
		imagecopy($d_comment_b,$s_comment_b,0,0,0,0,500,7);
		
		imagepng($d_comment_b,self::imagesPath().'/'.$dest_b);
		imagedestroy($d_comment_b);
		imagedestroy($s_comment_b);
	}
	
	public static function dropImage($img)
	{
		$img = path::real(self::imagesPath().'/'.$img);
		if (is_writable(dirname($img))) {
			@unlink($img);
			@unlink(dirname($img).'/.'.basename($img,'.png').'_sq.jpg');
			@unlink(dirname($img).'/.'.basename($img,'.png').'_m.jpg');
			@unlink(dirname($img).'/.'.basename($img,'.png').'_s.jpg');
			@unlink(dirname($img).'/.'.basename($img,'.png').'_sq.jpg');
			@unlink(dirname($img).'/.'.basename($img,'.png').'_t.jpg');
		}
	}
}
?>
