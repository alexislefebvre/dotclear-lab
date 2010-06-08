<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Corpoweeeb, a theme for Dotclear.
#
# Copyright (c) 2010
# Weeeb
# Pierre Van Glabeke pvg@weeeb.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

# Redefines 'home' urlHandler
$core->url->registerDefault(array('urlVogueHomePage','home'));

class urlVogueHomePage extends dcUrlHandlers
{
	public static function home($args)
	{
		$core =& $GLOBALS['core'];
		
		$n = self::getPageNumber($args);
		
		if ($args && !$n)
		{
			self::p404();
		}
		if (!$n && empty($_GET['q']))
		{
			# The entry
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			//header('Pragma: no-cache');
			//header('Cache-Control: no-cache');
			self::serveDocument('homepage.html');
			$core->blog->publishScheduledEntries();
			exit;
		}		
		else
		{
			if ($n) {
				$GLOBALS['_page_number'] = $n;
				$core->url->type = $n > 0 ? 'defaut-page' : 'default';
			}
			
			if (empty($_GET['q'])) {
				self::serveDocument('home.html');
				$core->blog->publishScheduledEntries();
				exit;
			} else {
				self::search();
			}
		}
	}
}

$core->addBehavior('templateBeforeBlock',array('behaviorsExcludeCurrentPost','templateBeforeBlock'));

class behaviorsExcludeCurrentPost 
{
	public static function templateBeforeBlock($core,$b,$attr)
	{
	       if ($b == 'Entries' && isset($attr['exclude_current']) && $attr['exclude_current'] == 1)
	       {
		       return
		       "<?php\n".
		       '$params["sql"] .= "AND P.post_url != \'".$_ctx->posts->post_url."\' ";'."\n".
		       "?>\n";
	       }
	}
}

$core->tpl->addValue('gravatar', array('gravatar', 'tplGravatar'));

class gravatar {

  const
    URLBASE = 'http://www.gravatar.com/avatar.php?gravatar_id=%s&amp;default=%s&amp;size=%d',
    HTMLTAG = '<img src="%s" class="%s" alt="%s" />',
    DEFAULT_SIZE = '40',
    DEFAULT_CLASS = 'gravatar_img',
    DEFAULT_ALT = 'Gravatar de %s';

  public static function tplGravatar($attr)
  {
    $md5mail = '\'.md5(strtolower($_ctx->comments->getEmail(false))).\'';
    $size    = array_key_exists('size',   $attr) ? $attr['size']   : self::DEFAULT_SIZE;
    $class   = array_key_exists('class',  $attr) ? $attr['class']  : self::DEFAULT_CLASS;
    $alttxt  = array_key_exists('alt',    $attr) ? $attr['alt']    : self::DEFAULT_ALT;
    $altimg  = array_key_exists('altimg', $attr) ? $attr['altimg'] : '';
    $gurl    = sprintf(self::URLBASE,
                       $md5mail, urlencode($altimg), $size);
    $gtag    = sprintf(self::HTMLTAG,
                       $gurl, $class, preg_match("/%s/i", $alttxt) ?
                                      sprintf($alttxt, '\'.$_ctx->comments->comment_author.\'') : $alttxt);
    return '<?php echo \'' . $gtag . '\'; ?>';
  }

}

?>