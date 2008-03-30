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

$_menu['Plugins']->addItem('LaTeX','plugin.php?p=remotelatex',
	null,
	preg_match('/plugin.php\?p=remotelatex(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('contentadmin',$core->blog->id));

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
			
			$file_name = sprintf('%s/%s/%s',
				$root_path,
				substr($hash,0,2),
				$hash
			);
			
			$file_url = sprintf('%s/%s/%s',
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
			
			# Get rendered PNG image
			if (netHttp::quickGet($dist_url,$file_name) === false) {
				throw new Exception(sprintf(
					__('Unable to get LaTeX image from the server %s.'),
					html::escapeHTML($latex_server)
				));
			}
			
			# Verify that we got a valid PNG / GIF file
			$accept_sig = array(
				"\x89\x50\x4e\x47\x0d\x0a\x1a\x0a",	# PNG
				"\x47\x49\x46\x38\x39\x61",			# GIF87a
				"\x47\x49\x46\x38\x37\x61"			# GIF89a
			);
			$signature = file_get_contents($file_name,false,null,0,8);
			
			foreach ($accept_sig as $sig)
			{
				if (strncmp($signature,$sig,strlen($sig)) === 0) {
					return self::getHtml($tex,$file_url);
				}
			}
			
			@unlink($file_name);
			throw new Exception(__('File is not a valid PNG / GIF image'));
		}
		catch (Exception $e) {
			# If something is wrong, LaTeX code is returned in plain text
			#$core->error->add($e->getMessage());
			return self::getHtml($tex);
		}
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
	
	public static function getSettings(&$latex_server='',&$root_path='',&$root_url='')
	{
		global $core;
		
		$root_path = path::real($core->blog->public_path).'/latex-images';
		$root_url = path::clean($core->blog->settings->public_url).'/latex-images';
		
		$latex_server = $core->blog->settings->latex_server;
		if ($latex_server === null) {
			$latex_server = 'http://math.spip.org/tex.php?';
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
