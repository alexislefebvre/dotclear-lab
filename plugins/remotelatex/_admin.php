<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Remote Latex', a plugin for Dotclear              *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Remote Latex' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('coreInitWikiPost',array('remoteLatex','coreInitWikiPost'));

class remoteLatex
{
	public static function coreInitWikiPost(&$wiki2xhtml)
	{
		$wiki2xhtml->registerFunction('macro:math',
			array('remoteLatex','render'));
	}
	
	public static function render($tex,$args)
	{
		global $core;
		
		$hash = md5($tex = trim($tex));
		
		try {
			self::getSettings($latex_server,$root_path,$root_url);
			
			$file_name = sprintf('%s/%s/%s.png',
				$root_path,
				substr($hash,0,2),
				$hash
			);
			
			$file_url = sprintf('%s/%s/%s.png',
				$root_url,
				substr($hash,0,2),
				$hash
			);
			
			if (file_exists($file_name)) {
				return self::getHtml($tex,$file_url);
			}
			
			# File doesn't exist : we need create one
			
			if (!is_dir(dirname($file_name))) {
				files::makeDir(dirname($file_name),true);
			}
			
			$dist_url = $latex_server.rawurlencode($tex);
			
			# Get rendered PNG picture
			if (netHttp::quickGet($dist_url,$file_name) === false) {
				throw new Exception(sprintf(
					__('Unable to get Latex image from the server %s.'),
					html::escapeHTML($latex_server)
				));
			}
			
			# Verify that we got a correct PNG file
			$signature = file_get_contents($file_name,false,null,0,8);
			
			if ($signature != "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a") {
				@unlink($file_name);
				throw new Exception(__('File is not a valid PNG image'));
			}
		}
		catch (Exception $e) {
			# If something is wrong, Laex code is returned in plain text
			#$core->error->add($e->getMessage());
			return self::getHtml($tex);
		}
		
		return self::getHtml($tex,$file_url);
	}
	
	public static function getHtml($tex,$file_url=null)
	{
		$tex = html::escapeHTML($tex);
		
		if ($file_url) {
			return '<img src="'.$file_url.'" alt="'.$tex.'" />';
		}
		else {
			return '<pre>'.$tex.'</pre>';
		}
	}
	
	public static function getSettings(&$latex_server,&$root_path,&$root_url)
	{
		global $core;
		
		$root_path = path::real($core->blog->public_path).'/latex-images';
		$root_url = $core->blog->host.
			path::clean($core->blog->settings->public_url).'/latex-images';
		
		$latex_server = $core->blog->settings->latex_server;
		if ($latex_server === null) {
			$latex_server = 'http://math.spip.org/tex.php?';
			$core->blog->settings->setNamespace('latex');
			$core->blog->settings->put(
				'latex_server',
				$latex_server,
				'string',
				'Latex server URL'
			);
		}
	}
}
?>
