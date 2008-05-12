<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Remote LaTeX', a plugin for Dotclear              *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Remote LaTeX' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class remoteLatex
{
	public static function parseContent($str)
	{
		return preg_replace_callback('#&lt;math&gt;(.+)&lt;/math&gt;#Umis',
			array('remoteLatex','render'),$str);
	}
	
	public static function render($tex,$args=null)
	{
		global $core;
		
		if (is_array($tex)) {
			$tex = array_pop($tex);
		}
		
		$hash = md5($tex = trim($tex));
		
		try {
			$file_name = sprintf('%s/%s',substr($hash,0,2),$hash);
			$url = self::getImage($tex,$file_name);
			return self::getHtml($tex,$url);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			return self::getHtml($tex);
		}
	}
	
	public static function test($tex,$server)
	{
		global $core;
		
		$core->blog->settings->latex_server = $server;
		
		$url = self::getImage($tex,'_test',false);
		return self::getHtml($tex,$url);
	}
	
	/**
	Downloads Tex image from the server and returns its URL.
	
	@param	tex		<b>string</b>		Tex code to transform
	@param	dest		<b>string</b>		Destination filename
	@param	cache	<b>boolean</b>		Use cache ?
	@return			<b>string</b>
	*/
	public static function getImage($tex,$dest,$cache=true)
	{
		self::getSettings($server_uri,$root_path,$root_url);
		
		$dest_path = $root_path.'/'.$dest;
		$dest_url = $root_url.'/'.$dest;
		
		if ($cache && file_exists($dest_path)) {
			return $dest_url;
		}
		
		if (!is_dir(dirname($dest_path))) {
			files::makeDir(dirname($dest_path),true);
		}
		
		$server_uri = sprintf($server_uri,rawurlencode($tex));
		if (netHttp::quickGet($server_uri,$dest_path) === false) {
			throw new Exception(__('Unable to get LaTeX image from the server.'));
		}
		
		$valid_image = false;
		$signatures = array(
			"\x89\x50\x4e\x47\x0d\x0a\x1a\x0a",	# PNG
			"\x47\x49\x46\x38\x39\x61",			# GIF87a
			"\x47\x49\x46\x38\x37\x61"			# GIF89a
		);
		$signature = file_get_contents($dest_path,false,null,0,8);
		foreach ($signatures as $sig)
		{
			if (strncmp($signature,$sig,strlen($sig)) === 0) {
				$valid_image = true;
				break;
			}
		}
		
		if (!$valid_image) {
			@unlink($dest_path);
			throw new Exception(__('File is not a valid GIF or PNG image.'));
		}
		
		return $dest_url;
	}
	
	/**
	Returns HTML code corresponding to Tex code to insert.
	If no <var>file_url</var> given, returns plain text.
	
	@param	tex		<b>string</b>		Tex code
	@param	file_url	<b>string</b>		Corresponding Tex image URL
	@return			<b>string</b>
	*/
	public static function getHtml($tex,$file_url=null)
	{
		$tex = html::escapeHTML($tex);
		
		if ($file_url) {
			return '<img src="'.$file_url.'" alt="'.$tex.'" style="vertical-align:middle;"/>';
		}
		else {
			return '<tt>'.$tex.'</tt>';
		}
	}
	
	public static function getSettings(&$latex_server='',&$root_path='',&$root_url='')
	{
		global $core;
		
		$root_path = path::real($core->blog->public_path).'/latex-images';
		$root_url = path::clean($core->blog->settings->public_url).'/latex-images';
		
		$latex_server = $core->blog->settings->latex_server;
		if ($latex_server === null) {
			$latex_server = 'http://math.spip.org/tex.php?%s';
			$core->blog->settings->setNamespace('latex');
			$core->blog->settings->put(
				'latex_server',
				$latex_server,
				'string',
				'LaTeX server URL'
			);
		}
	}
}
?>