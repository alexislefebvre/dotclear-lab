<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Tweak URLs', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  xave and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('adminBlogPreferencesForm',array('tweakurlsAdminBehaviours','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('tweakurlsAdminBehaviours','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('adminBeforePostCreate',array('tweakurlsAdminBehaviours','adminBeforePostUpdate'));
$core->addBehavior('adminBeforePostUpdate',array('tweakurlsAdminBehaviours','adminBeforePostUpdate'));

class tweakurlsAdminBehaviours
{
	public static function adminBlogPreferencesForm(&$core,&$settings)
	{
		
		# URL modes
		$tweakurls_combo = array(
			'default mode' => 'default',
			'clean all diacritics' => 'nodiacritic',
			'Lowercase' => 'lowercase'
		);
		echo
		'<fieldset><legend>Tweak URLs</legend>'.
		'<div>'.
		'<p><label>'.
		__('Posts URL type:')." ".
		form::combo('tweakurls_posturltransform',$tweakurls_combo,$settings->tweakurls_posturltransform).
		'</label></p>'.
		'</div>'.
		'</fieldset>';
	}
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		$settings->setNameSpace('tweakurls');
		$settings->put('tweakurls_posturltransform',$_POST['tweakurls_posturltransform']);
		$settings->setNameSpace('system');
	}
	
	public static function getPostURL($url,$post_dt,$post_title,$post_id)
	{
		global $core;
		$blog = $core->blog;
		
		$url = trim($url);
		switch ($core->blog->settings->tweakurls_posturltransform) 
		{
			case 'nodiacritic':
				$formated_title = text::str2URL($post_title);
				break;
			case 'lowercase':
				$formated_title = strtolower(text::str2URL($post_title));
				break;
			default:
				$formated_title = text::tidyURL($post_title);
		}
		
		$url_patterns = array(
		'{y}' => date('Y',strtotime($post_dt)),
		'{m}' => date('m',strtotime($post_dt)),
		'{d}' => date('d',strtotime($post_dt)),
		'{t}' => $formated_title,
		'{id}' => (integer) $post_id
		);
		
		# If URL is empty, we create a new one
		if ($url == '')
		{
			# Transform with format
			$url = str_replace(
				array_keys($url_patterns),
				array_values($url_patterns),
				$blog->settings->post_url_format
			);
		}
		else
		{
			$url = text::tidyURL($url);
		}
		
		# Let's check if URL is taken...
		$strReq = 'SELECT post_url FROM '.$blog->prefix.'post '.
				"WHERE post_url = '".$blog->con->escape($url)."' ".
				'AND post_id <> '.(integer) $post_id. ' '.
				"AND blog_id = '".$blog->con->escape($blog->id)."' ".
				'ORDER BY post_url DESC';
		
		$rs = $blog->con->select($strReq);
		
		if (!$rs->isEmpty())
		{
			$strReq = 'SELECT post_url FROM '.$blog->prefix.'post '.
					"WHERE post_url LIKE '".$blog->con->escape($url)."%' ".
					'AND post_id <> '.(integer) $post_id. ' '.
					"AND blog_id = '".$blog->con->escape($blog->id)."' ".
					'ORDER BY post_url DESC ';
			
			$rs = $blog->con->select($strReq);
			$a = array();
			while ($rs->fetch()) {
				$a[] = $rs->post_url;
			}
			
			natsort($a);
			$t_url = end($a);
			
			if (preg_match('/(.*?)([0-9]+)$/',$t_url,$m)) {
				$i = (integer) $m[2];
				$url = $m[1];
			} else {
				$i = 1;
			}
			
			return $url.($i+1);
		}
		
		# URL is empty?
		if ($url == '') {
			throw new Exception(__('Empty entry URL'));
		}
		
		return $url;
	}
	
	public static function adminBeforePostUpdate ($cur,$id=null)
	{
		$cur->post_url = tweakurlsAdminBehaviours::getPostURL($cur->post_url,$cur->post_dt,$cur->post_title,$id);
	}
}
?>