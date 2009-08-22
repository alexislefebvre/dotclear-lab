<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicHeadContent',array('urlMiniUrl','publicHeadContent'));

$core->tpl->addBlock('miniUrlPageIf',array('tplMiniUrl','pageIf'));
$core->tpl->addBlock('miniUrlMsgIf',array('tplMiniUrl','pageMsgIf'));

$core->tpl->addValue('miniUrlPageURL',array('tplMiniUrl','pageURL'));
$core->tpl->addValue('miniUrlMsg',array('tplMiniUrl','pageMsg'));
$core->tpl->addValue('miniUrlHumanField',array('tplMiniUrl','humanField'));
$core->tpl->addValue('miniUrlHumanFieldProtect',array('tplMiniUrl','humanFieldProtect'));

$core->tpl->addBlock('AttachmentMiniIf',array('tplMiniUrl','AttachmentMiniIf'));
$core->tpl->addValue('AttachmentMiniURL',array('tplMiniUrl','AttachmentMiniURL'));
$core->tpl->addBlock('MediaMiniIf',array('tplMiniUrl','MediaMiniIf'));
$core->tpl->addValue('MediaMiniURL',array('tplMiniUrl','MediaMiniURL'));
$core->tpl->addBlock('EntryAuthorMiniIf',array('tplMiniUrl','EntryAuthorMiniIf'));
$core->tpl->addValue('EntryAuthorMiniURL',array('tplMiniUrl','EntryAuthorMiniURL'));
$core->tpl->addBlock('EntryMiniIf',array('tplMiniUrl','EntryMiniIf'));
$core->tpl->addValue('EntryMiniURL',array('tplMiniUrl','EntryMiniURL'));
$core->tpl->addBlock('CommentAuthorMiniIf',array('tplMiniUrl','CommentAuthorMiniIf'));
$core->tpl->addValue('CommentAuthorMiniURL',array('tplMiniUrl','CommentAuthorMiniURL'));
$core->tpl->addBlock('CommentPostMiniIf',array('tplMiniUrl','CommentPostMiniIf'));
$core->tpl->addValue('CommentPostMiniURL',array('tplMiniUrl','CommentPostMiniURL'));

class urlMiniUrl extends dcUrlHandlers
{
	public static function redirectMiniUrl($args)
	{
		global $core, $_ctx;

		if (!$core->blog->settings->miniurl_active) {
			self::p404();
			exit;
		}

		if (!preg_match('#^(/|)(.*?)$#',$args,$m)) {
			self::p404();
			exit;
		}

		$args = $m[2];
		$_ctx->miniurl_msg = '';
		$_ctx->miniurl_hmf = hmfMiniUrl::create();
		$_ctx->miniurl_hmfp = hmfMiniUrl::protect($_ctx->miniurl_hmf);

		$autoshorturl = (boolean) $core->blog->settings->miniurl_public_autoshorturl;
		$protocols = $core->blog->settings->miniurl_protocols;
		$protocols = !$protocols ? '' : explode(',',$protocols);

		$O = new dcMiniUrl($core,$autoshorturl,$protocols);

		if ($m[1] == '/' && $args == '')
			$_ctx->miniurl_msg = 'No link given';

		if ($args == '') {
			self::pageMiniUrl($O);
			exit;
		}

		$type = 'miniurl';
		if (-1 == ($str = $O->str($type,$args))) {

			$type = 'customurl';
			if (-1 == ($str = $O->str($type,$args))) {
				$_ctx->miniurl_msg = 'Failed to find short link';
				self::pageMiniUrl($O);
				exit;
			}
		}

		$O->counter($type,$args,'up');

		$core->blog->triggerBlog();
		http::redirect($str);
		exit;
	}

	private static function pageMiniUrl($O)
	{
		global $core, $_ctx;

		if (!$core->blog->settings->miniurl_public_active) {
			self::p404();
			exit;
		}

		# Valid form
		if (!empty($_POST['longurl'])) {
			$str = $_POST['longurl'];
			$hmf = !empty($_POST['hmf']) ? $_POST['hmf'] : '!';
			$hmfu = !empty($_POST['hmfp']) ? hmfMiniUrl::unprotect($_POST['hmfp']) : '?';

			$err = false;
			if (!$err) {
				if ($hmf != $hmfu) {
					$err = true;
					$_ctx->miniurl_msg = __('Failed to verify protected field');
				}
			}
			if (!$err) {
				if (!$O->isLonger($str)) {
					$err = true;
					$_ctx->miniurl_msg = __('This link is too short');
				}
			}
			if (!$err) {
				if ($O->isMini($str)) {
					$err = true;
					$_ctx->miniurl_msg = __('This link is already a short link');
				}
			}
			if (!$err) {
				if (!$O->isAllowed($str)) {
					$err = true;
					$_ctx->miniurl_msg = __('This type of link is not allowed');
				}
			}
			if (!$err) {
				$id = $O->auto($str,array('miniurl','customurl'));
				if ($id == -1) {
					$err = true;
					$_ctx->miniurl_msg = __('Failed to create short link');
				}
			}
			if (!$err) {
				$url = $core->blog->url.$core->url->getBase('miniUrl').'/'.$id;
				$_ctx->miniurl_msg = sprintf(__('Short link for %s is %s'),
					$str,'<a href="'.$url.'" title="miniurl">'.$url.'</a>');

				$core->blog->triggerBlog();
			}
		}

		$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');
		self::serveDocument('miniurl.html');
		exit;
	}

	public static function publicHeadContent($core)
	{
		global $core, $_ctx;

		$autoshorturl = (boolean) $core->blog->settings->miniurl_public_autoshorturl;
		$protocols = $core->blog->settings->miniurl_protocols;
		$protocols = !$protocols ? array('http:') : explode(',',$protocols);

		$_ctx->miniurl = new dcMiniUrl($core,$autoshorturl,$protocols);
	
	}
}

class tplMiniUrl
{
	public static function pageURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("miniUrl")').'; ?>';
	}

	public static function pageIf($attr,$content)
	{
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';

		if (isset($attr['is_active'])) {

			$sign = (boolean) $attr['is_active'] ? '' : '!';
			$if[] = $sign.'$core->blog->settings->miniurl_public_active';
		}

		if (empty($if))
			return $content;

		return 
		"<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".
		$content.
		"<?php endif; ?>\n";
	}

	public static function pageMsgIf($attr,$content)
	{
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';

		if (isset($attr['has_message'])) {

			$sign = (boolean) $attr['has_message'] ? '!' : '=';
			$if[] = '"" '.$sign.'= $_ctx->miniurl_msg';
		}

		if (empty($if))
			return $content;

		return 
		"<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".
		$content.
		"<?php endif; ?>\n";
	}

	public static function pageMsg($attr)
	{
		return '<?php echo $_ctx->miniurl_msg; ?>';
	}

	public static function humanField($attr)
	{
		return "<?php echo sprintf(__('Write \"%s\" in next field:'),\$_ctx->miniurl_hmf); ?>";
	}

	public static function humanFieldProtect($attr)
	{
		return 
		"<input type=\"hidden\" name=\"hmfp\" id=\"hmfp\" value=\"<?php echo \$_ctx->miniurl_hmfp; ?>\" />".
		"<?php echo \$core->formNonce(); ?>";
	}

	public static function AttachmentMiniIf($attr,$content)
	{
		return self::genericMiniIf('$attach_f->file_url',$attr,$content);
	}

	public static function AttachmentMiniURL($attr)
	{
		return self::genericMiniURL('$attach_f->file_url',$attr);
	}

	public static function MediaMiniIf($attr,$content)
	{
		return self::genericMiniIf('$_ctx->file_url',$attr,$content);
	}

	public static function MediaMiniURL($attr)
	{
		return self::genericMiniURL('$_ctx->file_url',$attr);
	}

	public static function EntryAuthorMiniIf($attr,$content)
	{
		return self::genericMiniIf('$_ctx->posts->user_url',$attr,$content);
	}

	public static function EntryAuthorMiniURL($attr)
	{
		return self::genericMiniURL('$_ctx->posts->user_url',$attr);
	}

	public static function EntryMiniIf($attr,$content)
	{
		return self::genericMiniIf('$_ctx->posts->getURL()',$attr,$content);
	}

	public static function EntryMiniURL($attr)
	{
		return self::genericMiniURL('$_ctx->posts->getURL()',$attr);
	}

	public static function CommentAuthorMiniIf($attr,$content)
	{
		return self::genericMiniIf('$_ctx->comments->getAuthorURL()',$attr,$content);
	}

	public static function CommentAuthorMiniURL($attr)
	{
		return self::genericMiniURL('$_ctx->comments->getAuthorURL()',$attr);
	}

	public static function CommentPostMiniIf($attr,$content)
	{
		return self::genericMiniIf('$_ctx->comments->getPostURL()',$attr,$content);
	}

	public static function CommentPostMiniURL($attr)
	{
		return self::genericMiniURL('$_ctx->comments->getPostURL()',$attr);
	}

	protected static function genericMiniIf($str,$attr,$content)
	{
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';

		if (isset($attr['has_miniurl'])) {

			$sign = (boolean) $attr['has_miniurl'] ? '!' : '=';
			$if[] = '-1 '.$sign.'= $_ctx->miniurl->id("miniurl",'.$str.')';
		}

		if (isset($attr['has_customurl'])) {

			$sign = (boolean) $attr['has_customurl'] ? '!' : '=';
			$if[] = '-1 '.$sign.'= $_ctx->miniurl->id("customurl",'.$str.')';
		}

		if (empty($if))
			return $content;

		return 
		"<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".
		$content.
		"<?php endif; ?>\n";
	}

	protected static function genericMiniURL($str,$attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return 
		"<?php \n".
		"\$miniurl_id = \$_ctx->miniurl->auto(".$str.",array('miniurl','customurl')); \n".
		"if (-1 != \$miniurl_id) echo ".sprintf($f,'$core->blog->url.$core->url->getBase("miniUrl")."/".$miniurl_id')."; \n".
		"unset(\$miniurl); \n".
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

class widgetPublicMiniUrl
{
	public static function dominiurl($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') return;

		if (!$core->blog->settings->miniurl_active 
		 || !$core->blog->settings->miniurl_public_active) return;

		$hmf = hmfMiniUrl::create();
		$hmfp = hmfMiniUrl::protect($hmf);

		return 
		'<div class="dominiurl">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<form name="dominiurl" method="post" action="'.
		 $core->blog->url.$core->url->getBase('miniUrl').'">'.
		'<p><label>'.
		 __('Long link:').'<br />'.
		 form::field('longurl',20,255,'').
		'</label></p>'.
		'<p><label>'.
		 sprintf(__('Write "%s" in next field:'),$hmf).'<br />'.
		 form::field('hmf',20,255,'').
		'</label></p>'.
		'<p><input class="submit" type="submit" name="submiturl" value="'.__('Create').'" />'.
		form::hidden('hmfp',$hmfp).
		$core->formNonce().
		'</p>'.
		'</form>'.
		'</div>';
	}

	public static function rankminiurl($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') return;

		if (!$core->blog->settings->miniurl_active) return;

		$rs = $core->con->select(
		'SELECT miniurl_counter, miniurl_id '.
		"FROM ".$core->prefix."miniurl ".
		"WHERE blog_id='".$core->con->escape($core->blog->id)."' ".
		"AND miniurl_type='miniurl' ".
		'ORDER BY miniurl_counter '.($w->sort == 'asc' ? 'ASC' : 'DESC').',miniurl_id ASC '.
		$core->con->limit(abs((integer) $w->limit)));

		if ($rs->isEmpty()) return;

		$content = '';
		$i = 0;

		while($rs->fetch()) {
			$i++;
			$rank = '<span class="rankminiurl-rank">'.$i.'</span>';

			$url = $core->blog->url.$core->url->getBase('miniUrl').'/'.$rs->miniurl_id;
			$cut_len = - abs((integer) $w->urllen);
			if (strlen($url) > $cut_len)
				$url = '...'.substr($url,$cut_len);

			if ($rs->miniurl_counter == 0)
				$counttext = __('never followed');
			elseif ($rs->miniurl_counter == 1)
				$counttext = __('followed one time');
			else
				$counttext = sprintf(__('followed %s times'),$rs->miniurl_counter);

			$content .= 
				'<li><a href="'.
				$core->blog->url.$core->url->getBase('miniUrl').'/'.$rs->miniurl_id.
				'">'.
				str_replace(
					array('%rank%','%id%','%url%','%count%','%counttext%'),
					array($rank,$rs->miniurl_id,$url,$rs->miniurl_counter,$counttext),
					$w->text
				).
				'</a></li>';

		}

		return 
		'<div class="rankminiurl">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$content.'</ul>'.
		'</div>';
	}
}

class hmfMiniUrl
{
	public static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

	public static function create($len=6)
	{
		$res = '';
		$chars = self::$chars;

		for($i = 0;$i < $len; $i++) {
			$res .= $chars[rand(0,strlen($chars)-1)];
		}

		return $res;
	}

	public static function protect($str)
	{
		$res = '';
		$chars = self::$chars;

		for($i = 0; $i < strlen($str);$i++) {
			$res .= $chars[rand(0,strlen($chars)-1)].$str[$i];
		}

		return $res;
	}

	public static function unprotect($str)
	{
		$res = '';

		for($i = 0; $i < strlen($str);$i++) {
			$i++;
			$res .= $str[$i];
		}

		return $res;
	}
}
?>