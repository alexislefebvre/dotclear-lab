<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class agoraTemplate
{
	public static function agoraAnnounce($attr)
	{
		return '<?php echo $core->blog->settings->agora_announce; ?>';
	}

	public static function forumURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("agora")').'; ?>';
	}

	public static function registerURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("register")').'; ?>';
	}

	public static function loginURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("login")').'; ?>';
	}

	public static function profileURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("profile")').'; ?>';
	}

	public static function logoutURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("logout")').'; ?>';
	}

	public static function AgoraFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("agofeed")."/'.$type.'"').'; ?>';
	}

	
	public static function IfRegisterPreview($attr,$content)
	{
		global $_ctx;
		
		return
		'<?php if ($_ctx->agora_register !== null && $_ctx->agora_register["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	public static function RegisterPreviewLogin($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->agora_register["login"]').'; ?>';
	}
	
	public static function RegisterPreviewEmail($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->agora_register["email"]').'; ?>';
	}

	/*dtd
	<!ELEMENT tpl:Subforums - - -- Subforums loop -->
	*/
	public static function Subforums($attr,$content)
	{
		global $core, $_ctx;
		
		$p = "\$params = array();\n";
		
		if (isset($attr['url'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['url'])."';\n";
		}
		
		//if (isset($attr['without_empty'])) {  
		//	$p .= "\$params['without_empty'] = '".(bool) $attr['without_empty']."';\n"; 
		//} 
		
		if (!empty($attr['post_type'])) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}
		else {
			$p .= "\$params['post_type'] = 'threadpost';\n";
		}
		
		if (!empty($attr['level'])) {
			$p .= "\$params['level'] = ".(integer) $attr['level'].";\n";
		}

		//if (isset($_ctx->subforumurl)) {
		//	$p .= "\$params['cat_url'] = '".addslashes($_ctx->subforumurl)."';\n";
		//}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->categories = $_ctx->agora->getCategoriesPlus($params);'."\n";
		$res .= "?>\n";
		$res .= '<?php while ($_ctx->categories->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->categories = null; unset($params); ?>';
		
		return $res;
	}

	public static function SubforumFirstChildren($attr,$content)
	{
		return
		"<?php\n".
		'$_ctx->categories = $_ctx->agora->getCategoryFirstChildren($_ctx->categories->cat_id);'."\n".
		'while ($_ctx->categories->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->categories = null; ?>';
	}

	public static function SubforumURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("subforum")."/".$_ctx->categories->cat_url').'; ?>';
	}

	public static function SubforumFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("agofeed")."/subforum/".'.
		'$_ctx->categories->cat_url."/'.$type.'"').'; ?>';
	}

	public static function ThreadAnswersCount($attr)
	{
		global $core, $_ctx;
		
		$none = 'no answer';
		$one = 'one answer';
		$more = '%d answers';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		$operation = '$_ctx->posts->nb_comment';
		
		return
		"<?php if (".$operation." == 0) {\n".
		"  printf(__('".$none."'),".$operation.");\n".
		"} elseif (".$operation." == 1) {\n".
		"  printf(__('".$one."'),".$operation.");\n".
		"} else {\n".
		"  printf(__('".$more."'),".$operation.");\n".
		"} ?>";
	}

	public static function authForm($attr,$content)
	{
		global $core;
		
		return
		'<?php if ($core->auth->userID() != false && isset($_SESSION[\'sess_user_id\'])) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function notauthForm($attr,$content)
	{
		global $core;
		
		return
		'<?php if ($core->auth->userID() == false) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function SubForumNewThreadLink($attr)
	{
		global $core;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("newthread")."/".$_ctx->categories->cat_url').'; ?>';
	}

	public static function SubforumThreadsNumber($attr)
	{
		global $core, $_ctx;
		
		$none = __('no thread');
		$one = __('one thread');
		$more = __('%d threads');
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		if (!empty($attr['full'])) {
			$operation = '$_ctx->categories->nb_total';
		} else {
			$operation = '$_ctx->categories->nb_post';
		}
		
		return
		"<?php if (".$operation." == 0) {\n".
		"  printf('".$none."',".$operation.");\n".
		"} elseif (".$operation." == 1) {\n".
		"  printf('".$one."',".$operation.");\n".
		"} else {\n".
		"  printf('".$more."',".$operation.");\n".
		"} ?>";
	}

	public static function SubforumAnswersNumber($attr)
	{
		global $core, $_ctx;
		
		$none = __('no answer');
		$one = __('one answer');
		$more = __('%d answers');
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}

		if (!empty($attr['full'])) {
			$operation = '$_ctx->categories->nb_total2';
		} else {
			$operation = '$_ctx->categories->nb_answer';
		}
		
		return
		"<?php if (".$operation." == 0) {\n".
		"  printf('".$none."',".$operation.");\n".
		"} elseif (".$operation." == 1) {\n".
		"  printf('".$one."',".$operation.");\n".
		"} else {\n".
		"  printf('".$more."',".$operation.");\n".
		"} ?>";
	}

	public static function PublicUserID($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->auth->userID()').'; ?>';
	}
	
	public static function PublicUserDisplayName($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->auth->getInfo(\'user_displayname\')').'; ?>';
	}

	public static function IfThreadPreview($attr,$content)
	{
		global $_ctx;
		
		return
		'<?php if ($_ctx->thread_preview !== null && $_ctx->thread_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function ThreadPreviewTitle($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->thread_preview["title"]').'; ?>';
	}
	

	public static function ThreadPreviewContent($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$co = '$_ctx->thread_preview["rawcontent"]';
		} else {
			$co = '$_ctx->thread_preview["content"]';
		}
		
		return '<?php echo '.sprintf($f,$co).'; ?>';
	}

	public static function IfAnswerPreview($attr,$content)
	{
		global $_ctx;
		
		return
		'<?php if ($_ctx->post_preview !== null && $_ctx->post_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function AnswerPreviewContent($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$co = '$_ctx->post_preview["rawcontent"]';
		} else {
			$co = '$_ctx->post_preview["content"]';
		}
		
		return '<?php echo '.sprintf($f,$co).'; ?>';
	}

	public static function ThreadProfileUserID($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("profile")."/".$_ctx->posts->user_id').'; ?>';
	}

	public static function ThreadURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("thread")."/".$_ctx->posts->post_url').'; ?>';
	}

	public static function ThreadCategoryURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("subforum")."/".$_ctx->posts->cat_url').'; ?>';
	}

	public static function AnswerThreadURL($attr)
	{
		global $core, $_ctx;

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("thread")."/".$_ctx->agora->getThreadURL($_ctx->posts)').'; ?>';
	}

	public static function EntryIfClosed($attr)
	{
		global $core, $_ctx;
		
		$ret = isset($attr['return']) ? $attr['return'] : 'closed';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (!$_ctx->posts->post_open_comment) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function PaginationPlus($attr,$content)
	{
		global $core, $_ctx;
		
		$p = "<?php\n";
		$p .= '$params = $_ctx->post_params;'."\n";
		$p .= '$_ctx->pagination = $_ctx->agora->getPostsPlus($params,true); unset($params);'."\n";
		$p .= "?>\n";
		
		if (isset($attr['no_context'])) {
			return $p.$content;
		}
		
		return
		$p.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function IfEditPreview($attr,$content)
	{
		global $_ctx;
		
		return
		'<?php if ($_ctx->post_preview !== null && $_ctx->post_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function IfIsThread($attr,$content)
	{
		global $_ctx;
		
		return
		'<?php if ($_ctx->post_preview !== null && $_ctx->post_preview["isThread"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function PostEditTitle($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		//$f = $attr;
		
		$res = "<?php\n";
		$res .= '$v = isset($_POST["ed_title"]) ? $_POST["ed_title"] : $_ctx->posts->post_title; '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";
		
		return $res;
	}

	public static function PostEditContent($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$res = "<?php\n";
			$res .= '$v = isset($_POST["ed_content"]) ? $_POST["ed_content"] : $_ctx->post_preview["rawcontent"]; '."\n";
			$res .= 'echo '.sprintf($f,'$v').';'."\n";
			$res .= "?>";
		} else {
			$res = "<?php\n";
			$res .= '$v = $_ctx->post_preview["content"]; '."\n";
			$res .= 'echo '.sprintf($f,'$v').';'."\n";
			$res .= "?>";
		}
		
		return $res;
	}

	public static function ModerationDelete($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("removepost")."/".$_ctx->posts->post_id').'; ?>';
	}

	public static function ModerationEdit($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("editpost")."/".$_ctx->posts->post_id').'; ?>';
	}

	public static function ModerationPin($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = "<?php\n";
		$res .= '$li = strpos($core->blog->url,\'?\') !== false ? \'&\' : \'?\'; '."\n";
		$res .= '$li = $li."action=pin" ; '."\n";
		$res .= 'echo '.sprintf($f,'$li').';'."\n";
		$res .= "?>";
		
		return $res;
	}

	public static function ModerationUnpin($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = "<?php\n";
		$res .= '$li = strpos($core->blog->url,\'?\') !== false ? \'&\' : \'?\'; '."\n";
		$res .= '$li = $li."action=unpin" ; '."\n";
		$res .= 'echo '.sprintf($f,'$li').';'."\n";
		$res .= "?>";
		
		return $res;
	}

	public static function ModerationClose($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = "<?php\n";
		$res .= '$li = strpos($core->blog->url,\'?\') !== false ? \'&\' : \'?\'; '."\n";
		$res .= '$li = $li."action=close" ; '."\n";
		$res .= 'echo '.sprintf($f,'$li').';'."\n";
		$res .= "?>";
		
		return $res;
	}

	public static function ModerationOpen($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = "<?php\n";
		$res .= '$li = strpos($core->blog->url,\'?\') !== false ? \'&\' : \'?\'; '."\n";
		$res .= '$li = $li."action=open" ; '."\n";
		$res .= 'echo '.sprintf($f,'$li').';'."\n";
		$res .= "?>";
		
		return $res;
	}

	public static function AnswerOrderNumber($attr)
	{
		return '<?php echo $_ctx->posts->index()+1; ?>';
	}

	public static function SysIfThreadUpdated($attr,$content)
	{
		return
		'<?php if (!empty($_GET[\'pin\'])) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function EntryCreaDate($attr)
	{
		global $core;
	 	
		$format = (!empty($attr['format'])) ? $attr['format'] : 
			$core->blog->settings->date_format.', '.$core->blog->settings->time_format; 
		$f = $GLOBALS['core']->tpl->getFilters($attr);
	 	
		return('<?php echo '.'dt::dt2str(\''.$format.'\','.sprintf($f,'$_ctx->posts->post_creadt').
			',\''.$core->blog->settings->blog_timezone.'\'); ?>');
	}

	public static function userIsModo($attr,$content)
	{
		global $core, $_ctx;
		
		return
		'<?php if (($core->auth->userID() != false) && $_ctx->agora->isModerator($core->auth->userID()) === true) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function ProfileUserID($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->profile->user_id').'; ?>';
	}

	public static function ProfileUserDisplayName($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->profile->user_displayname').'; ?>';
	}

	public static function ProfileUserURL($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->profile->user_url').'; ?>';
	}

	public static function ProfileUserEmail($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->profile->user_email').'; ?>';
	}

	public static function ProfileUserCreaDate($attr)
	{
		global $core, $_ctx;
		
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		} else {
			$format = '%Y-%m-%d %H:%M:%S';
		}
		
		//$_ctx->profile->user_creadt = strtotime($_ctx->profile->user_creadt);
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"dt::rfc822(\$_ctx->profile->user_creadt,\$core->blog->settings->blog_timezone)").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"dt::iso8601(\$_ctx->profile->user_creadt,\$core->blog->settings->blog_timezone)").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"dt::str('".$format."',\$_ctx->profile->user_creadt)").'; ?>';
		}
	}
	
	public static function ProfileUserUpdDate($attr)
	{
		global $core, $_ctx;
		
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		} else {
			$format = '%Y-%m-%d %H:%M:%S';
		}
		
		//$_ctx->profile->user_upddt = strtotime($_ctx->profile->user_upddt);
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"dt::rfc822(\$_ctx->profile->user_upddt,\$core->blog->settings->blog_timezone)").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"dt::iso8601(\$_ctx->profile->user_upddt,\$core->blog->settings->blog_timezone)").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"dt::str('".$format."',\$_ctx->profile->user_upddt)").'; ?>';
		}
	}
	
	public static function Messages($attr,$content)
	{
		global $core, $_ctx;

		$lastn = -1;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn != 0) {
			if ($lastn > 0) {
				$p .= "\$params['limit'] = ".$lastn.";\n";
			} else {
				$p .= "\$params['limit'] = \$_ctx->nb_message_per_page;\n";
			}
			
			if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
				$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
			} else {
				$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
			}
		}
		
		if (isset($attr['author'])) {
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("users")) { '.
				"\$params['user_id'] = \$_ctx->users->user_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("archives")) { '.
				"\$params['post_year'] = \$_ctx->archives->year(); ".
				"\$params['post_month'] = \$_ctx->archives->month(); ".
				"unset(\$params['limit']); ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['post_lang'] = \$_ctx->langs->post_lang; ".
			"}\n";
			
			$p .=
			'if (isset($_search)) { '.
				"\$params['search'] = \$_search; ".
			"}\n";
		}

		$sortby = 'post_dt';
		$order = 'desc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'title': $sortby = 'post_title'; break;
				case 'selected' : $sortby = 'post_selected'; break;
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'post_dt'; break;
				case 'id' : $sortby = 'post_id'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->message_params = $params;'."\n";
		$res .= '$_ctx->messages = $_ctx->agora->getMessages($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->messages->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->messages = null; $_ctx->message_params = null; ?>';
		
		return $res;
	}

	public static function MessageIfFirst($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->messages->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function MessageIfOdd($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->messages->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function MessageContent($attr)
	{
		global $core, $_ctx;

		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,'$_ctx->messages->getContent('.$urls.')').'; ?>';
	}

	public static function MessageAuthorID($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,'$_ctx->messages->user_id').'; ?>';
	}

	public static function MessageAuthor($attr)
	{//A revoir
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,'$_ctx->messages->user_id').'; ?>';
	}

	public static function MessageID($attr)
	{
		global $core, $_ctx;

		return '<?php echo $_ctx->messages->message_id; ?>';
	}

	public static function MessageOrderNumber($attr)
	{
		global $core, $_ctx;

		return '<?php echo $_ctx->messages->index()+1; ?>';
	}

	public static function MessagesHeader($attr,$content)
	{
		global $core, $_ctx;

		return
		"<?php if (\$_ctx->messages->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function MessagesFooter($attr,$content)
	{
		global $core, $_ctx;

		return
		"<?php if (\$_ctx->messages->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function MessageDate($attr)
	{
		global $core, $_ctx;
		
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->messages->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->messages->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->messages->getDate('".$format."')").'; ?>';
		}
	}

	public static function MessageTime($attr)
	{
		global $core, $_ctx;

		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,"\$_ctx->messages->getTime('".$format."')").'; ?>';
	}

	public static function IfMessagePreview($attr,$content)
	{
		global $core, $_ctx;

		return
		'<?php if ($_ctx->message_preview !== null && $_ctx->message_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function MessagePreviewContent($attr)
	{
		global $core, $_ctx;

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$co = '$_ctx->message_preview["rawcontent"]';
		} else {
			$co = '$_ctx->message_preview["content"]';
		}
		
		return '<?php echo '.sprintf($f,$co).'; ?>';
	}

	public static function MessageProfileUserID($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("profile")."/".$_ctx->messages->user_id').'; ?>';
	}

	public static function agoPagination($attr,$content)
	{
		global $core, $_ctx;

		$p = "<?php\n";
		$p .= '$params = $_ctx->message_params;'."\n";
		$p .= '$_ctx->pagination = $_ctx->agora->getMessages($params,true); unset($params);'."\n";
		$p .= "?>\n";
		
		if (isset($attr['no_context'])) {
			return $p.$content;
		}

		return
		$p.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->messages->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	public static function agoPaginationURL($attr)
	{
		global $core, $_ctx;

		$offset = 0;
		if (isset($attr['offset'])) {
			$offset = (integer) $attr['offset'];
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,"agoraTools::PaginationURL(".$offset.")").'; ?>';
	}

	public static function agoPaginationCounter($attr)
	{
		global $core, $_ctx;

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,"agoraTools::PaginationNbPages()").'; ?>';
	}
	
	public static function agoPaginationCurrent($attr)
	{
		global $core, $_ctx;
		$offset = 0;
		if (isset($attr['offset'])) {
			$offset = (integer) $attr['offset'];
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,"agoraTools::PaginationPosition(".$offset.")").'; ?>';
	}
	
	public static function agoPaginationIf($attr,$content)
	{
		$if = array();
		
		if (isset($attr['start'])) {
			$sign = (boolean) $attr['start'] ? '' : '!';
			$if[] = $sign.'agoraTools::PaginationStart()';
		}
		
		if (isset($attr['end'])) {
			$sign = (boolean) $attr['end'] ? '' : '!';
			$if[] = $sign.'agoraTools::PaginationEnd()';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' && ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}


}
?>