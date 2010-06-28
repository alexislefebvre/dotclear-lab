<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');

$core->addBehavior('publicBeforeDocument',array('pubKutrl','publicBeforeDocument'));
$core->addBehavior('publicHeadContent',array('pubKutrl','publicHeadContent'));
$core->addBehavior('publicBeforeContentFilter',array('pubKutrl','publicBeforeContentFilter'));

$core->tpl->addBlock('kutrlPageIf',array('tplKutrl','pageIf'));
$core->tpl->addBlock('kutrlMsgIf',array('tplKutrl','pageMsgIf'));

$core->tpl->addValue('kutrlPageURL',array('tplKutrl','pageURL'));
$core->tpl->addValue('kutrlMsg',array('tplKutrl','pageMsg'));
$core->tpl->addValue('kutrlHumanField',array('tplKutrl','humanField'));
$core->tpl->addValue('kutrlHumanFieldProtect',array('tplKutrl','humanFieldProtect'));

$core->tpl->addBlock('AttachmentKutrlIf',array('tplKutrl','AttachmentKutrlIf'));
$core->tpl->addValue('AttachmentKutrl',array('tplKutrl','AttachmentKutrl'));
$core->tpl->addBlock('MediaKutrlIf',array('tplKutrl','MediaKutrlIf'));
$core->tpl->addValue('MediaKutrl',array('tplKutrl','MediaKutrl'));
$core->tpl->addBlock('EntryAuthorKutrlIf',array('tplKutrl','EntryAuthorKutrlIf'));
$core->tpl->addValue('EntryAuthorKutrl',array('tplKutrl','EntryAuthorKutrl'));
$core->tpl->addBlock('EntryKutrlIf',array('tplKutrl','EntryKutrlIf'));
$core->tpl->addValue('EntryKutrl',array('tplKutrl','EntryKutrl'));
$core->tpl->addBlock('CommentAuthorKutrlIf',array('tplKutrl','CommentAuthorKutrlIf'));
$core->tpl->addValue('CommentAuthorKutrl',array('tplKutrl','CommentAuthorKutrl'));
$core->tpl->addBlock('CommentPostKutrlIf',array('tplKutrl','CommentPostKutrlIf'));
$core->tpl->addValue('CommentPostKutrl',array('tplKutrl','CommentPostKutrl'));

class urlKutrl extends dcUrlHandlers
{
	# Redirect !!! local !!! service only
	public static function redirectUrl($args)
	{
		global $core, $_ctx;
		$s = $core->blog->settings->kUtRL;
		
		# Not active, go to default 404
		if (!$s->kutrl_active)
		{
			self::p404();
			return;
		}
		# Not a valid url, go to kutrl 404
		if (!preg_match('#^(|(/(.*?)))$#',$args,$m))
		{
			self::kutrl404();
			return;
		}
		
		$args = isset($m[3]) ? $m[3] : '';
		$_ctx->kutrl_msg = '';
		$_ctx->kutrl_hmf = hmfKutrl::create();
		$_ctx->kutrl_hmfp = hmfKutrl::protect($_ctx->kutrl_hmf);
		
		$kut = new $core->kutrlServices['local']($core,$s->kutrl_limit_to_blog);
		
		# Nothing on url
		if ($m[1] == '/')
		{
			$_ctx->kutrl_msg = 'No link given.';
		}
		# find suffix on redirect url
		$suffix = '';
		if (preg_match('@^([^?/#]+)(.*?)$@',$args,$more))
		{
			$args = $more[1];
			$suffix = $more[2];
		}
		# No arg, go to kurtl page
		if ($args == '')
		{
			self::pageKutrl($kut);
			return;
		}
		# Not find, go to kutrl 404
		if (false === ($url = $kut->getUrl($args)))
		{
			//$_ctx->kutrl_msg = 'Failed to find short link.';
			//self::pageKutrl($kut);
			
			self::kutrl404();
			return;
		}
		# Removed (empty url), go to kutrl 404
		if (!$url)
		{
			self::kutrl404();
			return;
		}
		
		$core->blog->triggerBlog();
		http::redirect($url.$suffix);
		return;
	}
	
	private static function pageKutrl($kut)
	{
		global $core, $_ctx;
		$s = $core->blog->settings->kUtRL;
		
		# Not active, go to default 404
		if (!$s->kutrl_active)
		{
			self::p404();
			return;
		}
		# Public page not active, go to kutrl 404
		if (!$s->kutrl_srv_local_public)
		{
			self::kutrl404();
			return;
		}
		# Validation form
		$url = !empty($_POST['longurl']) ? trim($core->con->escape($_POST['longurl'])) : '';
		if (!empty($url))
		{
			$hmf = !empty($_POST['hmf']) ? $_POST['hmf'] : '!';
			$hmfu = !empty($_POST['hmfp']) ? hmfKutrl::unprotect($_POST['hmfp']) : '?';
			
			$err = false;
			if (!$err)
			{
				if ($hmf != $hmfu)
				{
					$err = true;
					$_ctx->kutrl_msg = __('Failed to verify protected field.');
				}
			}
			if (!$err)
			{
				if (!$kut->testService())
				{
					$err = true;
					$_ctx->kutrl_msg = __('Service is not well configured.');
				}
			}
			if (!$err)
			{
				if (!$kut->isValidUrl($url))
				{
					$err = true;
					$_ctx->kutrl_msg = __('This string is not a valid URL.');
				}
			}
			if (!$err)
			{
				if (!$kut->isLongerUrl($url))
				{
					$err = true;
					$_ctx->kutrl_msg = __('This link is too short.');
				}
			}
			if (!$err)
			{
				if (!$kut->isProtocolUrl($url))
				{
					$err = true;
					$_ctx->kutrl_msg = __('This type of link is not allowed.');
				}
			}

			if (!$err)
			{
				if ($s->kutrl_limit_to_blog && !$kut->isBlogUrl($url))
				{
					$err = true;
					$_ctx->kutrl_msg = __('Short links are limited to this blog URL.');
				}
			}
			if (!$err)
			{
				if ($kut->isServiceUrl($url))
				{
					$err = true;
					$_ctx->kutrl_msg = __('This link is already a short link.');
				}
			}
			if (!$err)
			{
				if (false !== ($rs = $kut->isKnowUrl($url)))
				{
					$err = true;

					$url = $rs->url;
					$new_url = $kut->url_base.$rs->hash;

					$_ctx->kutrl_msg = sprintf(
						__('Short link for %s is %s'),
						html::escapeHTML($url),
						'<a href="'.$new_url.'">'.$new_url.'</a>'
					);
				}
			}
			if (!$err)
			{
				if (false === ($rs = $kut->hash($url)))
				{
					$err = true;
					$_ctx->kutrl_msg = __('Failed to create short link.');
				}
				else
				{
					$url = $rs->url;
					$new_url = $kut->url_base.$rs->hash;

					$_ctx->kutrl_msg = sprintf(
						__('Short link for %s is %s'),
						html::escapeHTML($url),
						'<a href="'.$new_url.'">'.$new_url.'</a>'
					);
					$core->blog->triggerBlog();
					
					if ($s->kutrl_twit_onpublic) {
						# Send new url by libDcTwitter
						$twit = kutrlLibDcTwitter::getMessage('kUtRL');
						$twit = str_replace(array('%L','%B','%U'),array($new_url,$core->blog->name,__('public')),$twit);
						kutrlLibDcTwitter::sendMessage('kUtRL',$twit);
					}
				}
			}
		}
		
		$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');
		self::serveDocument('kutrl.html');
		return;
	}
	
	protected static function kutrl404()
	{
		global $core;
		
		if (!$core->blog->settings->kUtRL->kutrl_srv_local_404_active)
		{
			self::p404();
			return;
		}
		
		$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');
		$_ctx =& $GLOBALS['_ctx'];
		$core = $GLOBALS['core'];
		
		header('Content-Type: text/html; charset=UTF-8');
		http::head(404,'Not Found');
		$core->url->type = '404';
		$_ctx->current_tpl = 'kutrl404.html';
		$_ctx->content_type = 'text/html';
		
		echo $core->tpl->getData($_ctx->current_tpl);
		
		# --BEHAVIOR-- publicAfterDocument
		$core->callBehavior('publicAfterDocument',$core);
		exit;
	}
}

class pubKutrl
{
	# Replace long urls on the fly for default tags
	public static function publicBeforeContentFilter($core,$tag,$args)
	{
		if (!$core->blog->settings->kUtRL->kutrl_active 
		 || !$core->blog->settings->kUtRL->kutrl_tpl_active) return;
		
		$know_tags = array(
			'AttachmentURL',
			'MediaURL',
			'EntryAuthorURL',
			'EntryURL',
			'CommentAuthorURL',
			'CommentPostURL'
		);
		
		# Unknow tag
		if (!in_array($tag,$know_tags)) return;
		
		global $_ctx;
		
		# Oups
		if (!$_ctx->exists('kutrl')) return;
		
		# Existing
		if (false !== ($kutrl_rs = $_ctx->kutrl->isKnowUrl($args[0])))
		{
			$args[0] = $_ctx->kutrl->url_base.$kutrl_rs->hash;
		}
		# New
		elseif (false !== ($kutrl_rs = $_ctx->kutrl->hash($args[0])))
		{
			$args[0] = $_ctx->kutrl->url_base.$kutrl_rs->hash;
		
			# Send new url by libDcTwitter
			if ($_ctx->kutrl_twit_ontpl)
			{
				$twit = kutrlLibDcTwitter::getMessage('kUtRL');
				$twit = str_replace(array('%L','%B','%U'),array($_ctx->kutrl->url_base.$kutrl_rs->hash,$core->blog->name,__('public')),$twit);
				kutrlLibDcTwitter::sendMessage('kUtRL',$twit);
			}
		}
	}
	
	public static function publicBeforeDocument($core)
	{
		global $_ctx;
		$s = $core->blog->settings->kUtRL;

		# Passive : all kutrl tag return long url
		$_ctx->kutrl_passive = (boolean) $s->kutrl_tpl_passive;
		# Twitter feature when auto create short link on template
		$_ctx->kutrl_twit_ontpl = (boolean) $s->kutrl_twit_ontpl;
		
		if (!$s->kutrl_active || !$s->kutrl_tpl_service 
		 || !isset($core->kutrlServices[$s->kutrl_tpl_service])) return;
		
		$_ctx->kutrl = new $core->kutrlServices[$s->kutrl_tpl_service]($core,$s->kutrl_limit_to_blog);
	}
	
	public static function publicHeadContent($core)
	{
		$css = $core->blog->settings->kUtRL->kutrl_srv_local_css;
		if ($css)
		{
			echo 
			"\n<!-- CSS for kUtRL --> \n".
			"<style type=\"text/css\"> \n".
			html::escapeHTML($css)."\n".
			"</style>\n";
		}
	}
}

class tplKutrl
{
	public static function pageURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("kutrl")').'; ?>';
	}
	
	public static function pageIf($attr,$content)
	{
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['is_active']))
		{
			$sign = (boolean) $attr['is_active'] ? '' : '!';
			$if[] = $sign.'$core->blog->settings->kUtRL->kutrl_srv_local_public';
		}
		
		if (empty($if))
		{
			return $content;
		}
		
		return 
		"<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".
		$content.
		"<?php endif; unset(\$s);?>\n";
	}
	
	public static function pageMsgIf($attr,$content)
	{
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['has_message']))
		{
			$sign = (boolean) $attr['has_message'] ? '!' : '=';
			$if[] = '"" '.$sign.'= $_ctx->kutrl_msg';
		}
		
		if (empty($if))
		{
			return $content;
		}
		
		return 
		"<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".
		$content.
		"<?php endif; ?>\n";
	}
	
	public static function pageMsg($attr)
	{
		return '<?php echo $_ctx->kutrl_msg; ?>';
	}
	
	public static function humanField($attr)
	{
		return "<?php echo sprintf(__('Rewrite \"%s\" in next field to show that you are not a robot:'),\$_ctx->kutrl_hmf); ?>";
	}
	
	public static function humanFieldProtect($attr)
	{
		return 
		"<input type=\"hidden\" name=\"hmfp\" id=\"hmfp\" value=\"<?php echo \$_ctx->kutrl_hmfp; ?>\" />".
		"<?php echo \$core->formNonce(); ?>";
	}
	
	public static function AttachmentKutrlIf($attr,$content)
	{
		return self::genericKutrlIf('$attach_f->file_url',$attr,$content);
	}
	
	public static function AttachmentKutrl($attr)
	{
		return self::genericKutrl('$attach_f->file_url',$attr);
	}
	
	public static function MediaKutrlIf($attr,$content)
	{
		return self::genericKutrlIf('$_ctx->file_url',$attr,$content);
	}
	
	public static function MediaKutrl($attr)
	{
		return self::genericKutrl('$_ctx->file_url',$attr);
	}
	
	public static function EntryAuthorKutrlIf($attr,$content)
	{
		return self::genericKutrlIf('$_ctx->posts->user_url',$attr,$content);
	}
	
	public static function EntryAuthorKutrl($attr)
	{
		return self::genericKutrl('$_ctx->posts->user_url',$attr);
	}
	
	public static function EntryKutrlIf($attr,$content)
	{
		return self::genericKutrlIf('$_ctx->posts->getURL()',$attr,$content);
	}
	
	public static function EntryKutrl($attr)
	{
		return self::genericKutrl('$_ctx->posts->getURL()',$attr);
	}
	
	public static function CommentAuthorKutrlIf($attr,$content)
	{
		return self::genericKutrlIf('$_ctx->comments->getAuthorURL()',$attr,$content);
	}
	
	public static function CommentAuthorKutrl($attr)
	{
		return self::genericKutrl('$_ctx->comments->getAuthorURL()',$attr);
	}
	
	public static function CommentPostKutrlIf($attr,$content)
	{
		return self::genericKutrlIf('$_ctx->comments->getPostURL()',$attr,$content);
	}
	
	public static function CommentPostKutrl($attr)
	{
		return self::genericKutrl('$_ctx->comments->getPostURL()',$attr);
	}
	
	protected static function genericKutrlIf($str,$attr,$content)
	{
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['is_active']))
		{
			$sign = (boolean) $attr['is_active'] ? '' : '!';
			$if[] = $sign.'$_ctx->exists("kutrl")';
		}
		
		if (isset($attr['passive_mode']))
		{
			$sign = (boolean) $attr['passive_mode'] ? '' : '!';
			$if[] = $sign.'$_ctx->kutrl_passive';
		}
		
		if (isset($attr['has_kutrl']))
		{
			$sign = (boolean) $attr['has_kutrl'] ? '!' : '=';
			$if[] = '($_ctx->exists("kutrl") && false '.$sign.'== $_ctx->kutrl->select('.$str.',null,null,"kutrl"))';
		}
		
		if (empty($if))
		{
			return $content;
		}
		return 
		"<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".
		$content.
		"<?php endif; ?>\n";
	}
	
	protected static function genericKutrl($str,$attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return 
		"<?php \n".
		# Preview
		"if (\$_ctx->preview) { \n".
		" echo ".sprintf($f,$str)."; ".
		"} else { \n".
		# Disable
		"if (!\$_ctx->exists('kutrl')) { \n".
		# Passive mode
		" if (\$_ctx->kutrl_passive) { ".
		"  echo ".sprintf($f,$str)."; ".
		" } \n".
		"} else { \n".
		# Existing
		" if (false !== (\$kutrl_rs = \$_ctx->kutrl->isKnowUrl(".$str."))) { ".
		"  echo ".sprintf($f,'$_ctx->kutrl->url_base.$kutrl_rs->hash')."; ".
		" } \n".
		# New
		" elseif (false !== (\$kutrl_rs = \$_ctx->kutrl->hash(".$str."))) { ".
		"  echo ".sprintf($f,'$_ctx->kutrl->url_base.$kutrl_rs->hash')."; ".
		
		# Send new url by libDcTwitter
		"if (\$_ctx->kutrl_twit_ontpl) { ".
		 "\$twit = kutrlLibDcTwitter::getMessage('kUtRL'); ".
		 "\$twit = str_replace(array('%L','%B','%U'),array(\$_ctx->kutrl->url_base.\$kutrl_rs->hash,\$core->blog->name,__('public')),\$twit); ".
		 "kutrlLibDcTwitter::sendMessage('kUtRL',\$twit); ".
		 " unset(\$twit); ".
		"} \n".
		
		" } \n".
		" unset(\$kutrl_rs); \n".
		"} \n".
		"?>\n";
	}
	
	protected static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
}

class hmfKutrl
{
	public static $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	
	public static function create($len=6)
	{
		$res = '';
		$chars = self::$chars;

		for($i = 0;$i < $len; $i++)
		{
			$res .= $chars[rand(0,strlen($chars)-1)];
		}
		
		return $res;
	}
	
	public static function protect($str)
	{
		$res = '';
		$chars = self::$chars;

		for($i = 0; $i < strlen($str);$i++)
		{
			$res .= $chars[rand(0,strlen($chars)-1)].$str[$i];
		}
		
		return $res;
	}
	
	public static function unprotect($str)
	{
		$res = '';

		for($i = 0; $i < strlen($str);$i++)
		{
			$i++;
			$res .= $str[$i];
		}
		
		return $res;
	}
}
?>