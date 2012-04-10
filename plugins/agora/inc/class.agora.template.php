<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class agoraTemplate
{
	public static function registerURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("register")').'; ?>';
	}

	public static function recoverURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("recover")').'; ?>';
	}

	public static function loginURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("login")').'; ?>';
	}

	public static function profileURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("profile")').'; ?>';
	}
	
	public static function preferencesURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("preferences")').'; ?>';
	}	

	public static function logoutURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("logout")').'; ?>';
	}

	public static function IfRegisterPreview($attr,$content)
	{
		global $_ctx;
		
		return
		'<?php if ($core->agora_register !== null && $core->agora_register["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	public static function RegisterPreviewLogin($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->agora_register["login"]').'; ?>';
	}
	
	public static function RegisterPreviewEmail($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->agora_register["email"]').'; ?>';
	}

	public static function RecoverLogin($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->agora_recover["login"]').'; ?>';
	}
	
	public static function RecoverEmail($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->agora_recover["email"]').'; ?>';
	}

	public static function SysUserSearchString($attr)
	{
		$s = isset($attr['string']) ? $attr['string'] : '%1$s';
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php if (isset($_user_search)) { echo sprintf(__(\''.$s.'\'),'.sprintf($f,'$_user_search').',$_user_search_count);} ?>';
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

	public static function SysIfAgoraMessage($attr,$content)
	{
		return
		'<?php if ($_ctx->agora_message !== null) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function SysIfNoAgoraMessage($attr,$content)
	{
		return
		'<?php if ($_ctx->agora_message == null) : ?>'.
		$content.
		'<?php endif; ?>';
	}


	/*dtd
	<!ELEMENT tpl:SysAgoraMessage - O -- Form error -->
	*/
	public static function SysAgoraMessage($attr)
	{
		return
		'<?php if ($_ctx->agora_message !== null) { echo $_ctx->agora_message; } ?>';
	}

	public static function categoryNewEntryLink($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return
		'<?php if ($_ctx->exists("categories")) {'.
		' echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("new")."/".$_ctx->categories->cat_url').';'.
		'} else {'.
		'  echo '.sprintf($f,'$core->blog->url.$core->url->getURLFor("new")').';'.
		'} ?>';
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
		return '<?php echo '.sprintf($f,'$core->auth->getInfo(\'user_cn\')').'; ?>';
	}

	public static function PublicUserAvatar($attr)
	{
		global $_ctx;
		$f = $GLOBALS['core']->tpl->getFilters($attr); // IN PROGRESS
		$size = !empty($attr['size']) ? $attr['size'] : 'sq';
		$class = !empty($attr['class']) ? $attr['class'] : '';
		
		return "<?php echo mediaAgora::myAvatar('".addslashes($size)."','".addslashes($class)."'); ?>";
	}

	public static function EntryIfAuthMe($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : 'authme';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->posts->authMe()) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function IfEntryPreview($attr,$content)
	{
		global $_ctx;
		
		return
		'<?php if ($_ctx->post_preview !== null && $_ctx->post_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function EntryPreviewTitle($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->post_preview["title"]').'; ?>';
	}
	

	public static function EntryPreviewContent($attr)
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


	public static function EntryUserProfileURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getMemberProfile()').'; ?>';
	}

	public static function EntryStatus($attr)
	{
		global $core, $_ctx;
		
		//return $ret;getStatus
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getStatus()').'; ?>';
	}

	public static function EntryEditURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getEditURL()').'; ?>';
	}

	public static function EntryPublishURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getPublishURL()').'; ?>';
	}

	public static function EntryUnpublishURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getUnpublishURL()').'; ?>';
	}

/*
		$this->message_status['-3'] =  __('junk');
		$this->message_status['-2'] = __('pending');
		$this->message_status['-1'] = __('scheduled');
		$this->message_status['0'] = __('unpublished');
		$this->message_status['1'] = __('published');

*/

	public static function MessageStatus($attr)
	{
		global $core, $_ctx;
		
		//return $ret;getStatus
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->messages->getStatus()').'; ?>';
	}

	public static function MessageEditURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->messages->getEditURL()').'; ?>';
	}

	public static function MessagePublishURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->messages->getPublishURL()').'; ?>';
	}

	public static function MessageUnpublishURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->messages->getUnpublishURL()').'; ?>';
	}

	public static function MessageEntryURL($attr)
	{
		global $core, $_ctx;

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->messages->getPostURL()').'; ?>';
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


	public static function EntryIfModerator($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : '*';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->posts->isModerator()) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function EntryMessageCount($attr)
	{
		global $core, $_ctx;
		
		$none = 'no message';
		$one = 'one message';
		$more = '%d messages';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		return
		"<?php if ((integer) \$_ctx->posts->getMessagesCount() == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->posts->getMessagesCount() -1);\n".
		"} elseif ((integer) \$_ctx->posts->getMessagesCount() == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->posts->getMessagesCount());\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->posts->getMessagesCount());\n".
		"} ?>";
	}

	public static function EntryEditTitle($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		//$f = $attr;
		
		$res = "<?php\n";
		$res .= '$v = isset($_POST["c_title"]) ? $_POST["c_title"] : $_ctx->posts->post_title; '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";
		
		return $res;
	}

	public static function EntryEditExcerpt($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$res = "<?php\n";
			$res .= '$v = isset($_POST["c_excerpt"]) ? $_POST["c_excerpt"] : $_ctx->post_preview["rawexcerpt"]; '."\n";
			$res .= 'echo '.sprintf($f,'$v').';'."\n";
			$res .= "?>";
		} else {
			$res = "<?php\n";
			$res .= '$v = $_ctx->post_preview["excerpt"]; '."\n";
			$res .= 'echo '.sprintf($f,'$v').';'."\n";
			$res .= "?>";
		}
		
		return $res;
	}


	public static function EntryEditContent($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$res = "<?php\n";
			$res .= '$v = isset($_POST["c_content"]) ? $_POST["c_content"] : $_ctx->post_preview["rawcontent"]; '."\n";
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

	
	/*dtd
	<!ELEMENT tpl:SysIfEntryPublished - - -- Container displayed if entry has been published -->
	*/
	public static function SysIfEntryPublished($attr,$content)
	{
		return
		'<?php if (isset($_GET[\'post\']) && $_GET[\'post\'] == 1) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysIfEntryPending - - -- Container displayed if entry is pending after submission -->
	*/
	public static function SysIfEntryPending($attr,$content)
	{
		return
		'<?php if (isset($_GET[\'post\']) && $_GET[\'post\'] == 0) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysIfEntryUpdated - - -- Container displayed if entry has been updated -->
	*/
	public static function SysIfEntryUpdated($attr,$content)
	{
		return
		'<?php if (isset($_GET[\'post\']) && $_GET[\'post\'] == 2) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	/*dtd
	<!ELEMENT tpl:SysIfMessagePublished - - -- Container displayed if message has been published -->
	*/
	public static function SysIfMessagePublished($attr,$content)
	{
		return
		'<?php if (isset($_GET[\'msg\']) && $_GET[\'msg\'] == 1) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysIfMessagePending - - -- Container displayed if message is pending after submission -->
	*/
	public static function SysIfMessagePending($attr,$content)
	{
		return
		'<?php if (isset($_GET[\'msg\']) && $_GET[\'msg\'] == 0) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysIfMessageUpdated - - -- Container displayed if message has been updated -->
	*/
	public static function SysIfMessageUpdated($attr,$content)
	{
		return
		'<?php if (isset($_GET[\'msg\']) && $_GET[\'msg\'] == 2) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	/*dtd
	<!ELEMENT tpl:SysIfUserUpdated - - -- Container displayed if user has been updated -->
	*/
	public static function SysIfUserUpdated($attr,$content)
	{
		return
		'<?php if (isset($_GET[\'upd\']) && $_GET[\'upd\'] == 2) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function userIsModo($attr,$content)
	{
		global $core, $_ctx;
		
		return
		'<?php if ($core->agora->isModerator($core->auth->userID())) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function Users($attr,$content)
	{
		global $core, $_ctx;
		/*$lastn = -1;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn != 0) {
			if ($lastn > 0) {
				$p .= "\$params['limit'] = ".$lastn.";\n";
			} else {
				$p .= "\$params['limit'] = \$_ctx->nb_entry_per_page;\n";
			}
			
			if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
				$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
			} else {
				$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
			}
		}

		if (empty($attr['no_context']))
		{
			$p .=
			'if (isset($_user_search)) { '.
				"\$params['q'] = \$_user_search; ".
			"}\n";
		}*/

		$p .= "\$params['public'] = 1;\n";
		
		//$p .= "\$params['order'] = '".$core->tpl->getSortByStr($attr,'user')."';\n";
		
		$sortby = 'user_creadt';
		$order = 'asc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'user_creadt'; break;
				//case 'post' : $sortby = 'nb_post'; break;
				//case 'message' : $sortby = 'nb_message'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->user_params = $params;'."\n";
		$res .= '$_ctx->users = $core->agora->getUsers($params); '."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->users->fetch()) : ?>'.$content.'<?php endwhile; ?>';
		'$_ctx->users = null; $_ctx->users_params = null; ?>';
		
		return $res;
	}

	public static function UsersHeader($attr,$content)
	{
		global $core, $_ctx;

		return
		"<?php if (\$_ctx->users->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function UsersFooter($attr,$content)
	{
		global $core, $_ctx;

		return
		"<?php if (\$_ctx->users->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function UserIfOdd($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->users->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function UserID($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_id').'; ?>';
	}

	public static function UserDisplayName($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_displayname').'; ?>';
	}
	
	public static function UserCommonName($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,'$_ctx->users->getAuthorCN()').'; ?>';
	}

	public static function UserDesc($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['raw'])) {
			$co = '$_ctx->users->user_desc';
		} else {
			$co = '$_ctx->users->wikiDesc()';
		}
		
		return '<?php echo '.sprintf($f,$co).'; ?>';
	}
	
	public static function UserProfileURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return '<?php echo '.sprintf($f,'$_ctx->users->getMemberProfile()').'; ?>';
	}

	public static function UserURL($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['raw'])) {
			$url = '$_ctx->users->user_url';
		} else {
			$url = '$_ctx->users->getMemberLink()';
		}
		return '<?php echo '.sprintf($f,$url).'; ?>';
	}

	public static function UserIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';
		
		if (isset($attr['allow_contact'])) {
			$sign = (boolean) $attr['allow_contact'] ? '!=' : '==';
			$if[] = '$_ctx->users->option(\'allow_contact\') '.$sign.' ""';
		}
		
		if (isset($attr['has_url'])) {
			$sign = (boolean) $attr['has_url'] ? '!=' : '==';
			$if[] = '$_ctx->users->user_url '.$sign.' ""';
		}
		
		if (isset($attr['has_email'])) { 
			$sign = (boolean) $attr['has_email'] ? '!=' : '=='; 
			$if[] = '$_ctx->users->user_email '.$sign.' ""'; 
		} 
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}


	public static function UserIfModerator($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : '*';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->users->isModerator()) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function UserStats($attr)
	{
		global $_ctx;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if (!empty($attr['messages'])) {
			return '<?php echo '.sprintf($f,'$_ctx->users->getCountMessages()').'; ?>';
		} else {
			return '<?php echo '.sprintf($f,'$_ctx->users->getCountPosts()').'; ?>';
		}
	}

	public static function UserAvatar($attr)
	{
		global $_ctx;
		$f = $GLOBALS['core']->tpl->getFilters($attr); // IN PROGRESS
		$size = !empty($attr['size']) ? $attr['size'] : 'sq';
		$class = !empty($attr['class']) ? $attr['class'] : '';
		
		return "<?php echo mediaAgora::avatarHelper('".addslashes($size)."','".addslashes($class)."'); ?>";
	}
	
	public static function UserEntriesCount($attr)
	{
		global $core, $_ctx;
		
		$none = 'no entry';
		$one = 'one entry';
		$more = '%d entries';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		return
		"<?php if ((integer) \$_ctx->users->getCountPosts() == 0) {\n".
		"  printf(__('".$none."'),\$_ctx->users->getCountPosts());\n".
		"} elseif ((integer) \$_ctx->users->getCountPosts() == 1) {\n".
		"  printf(__('".$one."'),\$_ctx->users->getCountPosts());\n".
		"} else {\n".
		"  printf(__('".$more."'),\$_ctx->users->getCountPosts());\n".
		"} ?>";
	}

	public static function UserAllEntriesCount($attr)
	{
		global $core, $_ctx;
		
		$none = 'no entry';
		$one = 'one entry';
		$more = '%d entries';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		return
		"<?php if ((integer) \$_ctx->users->getActivity() == 0) {\n".
		"  printf(__('".$none."'),\$_ctx->users->getActivity());\n".
		"} elseif ((integer) \$_ctx->users->getActivity() == 1) {\n".
		"  printf(__('".$one."'),\$_ctx->users->getActivity());\n".
		"} else {\n".
		"  printf(__('".$more."'),\$_ctx->users->getActivity());\n".
		"} ?>";
	}
	
	public static function UserMessagesCount($attr)
	{
		global $core, $_ctx;
		
		$none = 'no message';
		$one = 'one message';
		$more = '%d messages';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		return
		"<?php if ((integer) \$_ctx->users->getCountMessages() == 0) {\n".
		"  printf(__('".$none."'),\$_ctx->users->getCountMessages());\n".
		"} elseif ((integer) \$_ctx->users->getCountMessages() == 1) {\n".
		"  printf(__('".$one."'),\$_ctx->users->getCountMessages());\n".
		"} else {\n".
		"  printf(__('".$more."'),\$_ctx->users->getCountMessages());\n".
		"} ?>";
	}

	public static function UserEmail($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_email').'; ?>';
	}

	public static function UserDate($attr)
	{
		global $core, $_ctx;
		
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$type = (!empty($attr['upddt']) ? 'upddt' : '');
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return '<?php echo '.sprintf($f,"\$_ctx->users->getDate('".$format."','".$type."')").'; ?>';
	}

	public static function UserTime($attr)
	{
		global $core, $_ctx;
		
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$type = (!empty($attr['upddt']) ? 'upddt' : '');
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return '<?php echo '.sprintf($f,"\$_ctx->users->getTime('".$format."','".$type."')").'; ?>';
	}

	public static function Messages($attr,$content)
	{
		global $core, $_ctx;
		
		$p =
		"if (\$_ctx->posts !== null) { ".
			"\$params['post_id'] = \$_ctx->posts->post_id; ".
		"}\n";

		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_message_per_page !== null) { \$params['limit'] = \$_ctx->nb_message_per_page; }\n";
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

		$sortby = 'message_id';
		$order = 'asc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'message_dt'; break;
				case 'id' : $sortby = 'message_id'; break;
				case 'post_id' : $sortby = 'post_id'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";
		
		//$p .= "\$params['order'] = '".$core->tpl->getSortByStr($attr,'message')."';\n";

		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->message_params = $params;'."\n";
		$res .= '$_ctx->messages = $core->agora->getMessages($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->messages->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->messages = null; $_ctx->message_params = null; ?>';
		
		return $res;
	}

	public static function MessageIf($attr,$content)
	{
		global $core;
		$if = new ArrayObject();
		$extended = null;
		$hascategory = null;
		
		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';
		
		if (isset($attr['type'])) {
			$type = trim($attr['type']);
			$type = !empty($type)?$type:'post';
			$if[] = '$_ctx->messages->post_type == "'.addslashes($type).'"';
		}

		
		if (isset($attr['first'])) {
			$sign = (boolean) $attr['first'] ? '=' : '!';
			$if[] = '$_ctx->messages->index() '.$sign.'= 0';
		}
		
		if (isset($attr['odd'])) {
			$sign = (boolean) $attr['odd'] ? '=' : '!';
			$if[] = '($_ctx->messages->index()+1)%2 '.$sign.'= 1';
		}
		
		if (isset($attr['has_category'])) {
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->messages->cat_id';
		}
		
		if (isset($attr['is_published'])) {
			$sign = (boolean) $attr['is_published'] ? '' : '!';
			$if[] = $sign.'$_ctx->messages->isPublished()';
		}
		
		if (isset($attr['is_editable'])) {
			$sign = (boolean) $attr['is_editable'] ? '' : '!';
			$if[] = $sign.'$_ctx->messages->isEditable()';
		}
		
		if (isset($attr['is_me'])) {
			$sign = (boolean) $attr['is_me'] ? '' : '!';
			$if[] = $sign.'$_ctx->messages->isMe()';
		}

		if (isset($attr['auth_me'])) {
			$sign = (boolean) $attr['is_me'] ? '' : '!';
			$if[] = $sign.'$_ctx->messages->authMe()';
		}
		
		$core->callBehavior('tplIfConditions','MessageIf',$attr,$content,$if);
		
		if (count($if) != 0) {
			return '<?php if('.implode(' '.$operator.' ', (array) $if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
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
	
	public static function MessageIfMe($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->messages->isMe()) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function MessageIfAuthMe($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : 'authme';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->messages->authMe()) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function MessageIfModerator($attr)
	{
		global $core, $_ctx;

		$ret = isset($attr['return']) ? $attr['return'] : '*';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->messages->isModerator()) { '.
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

	public static function MessageAuthorCommonName($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,'$_ctx->messages->getAuthorCN()').'; ?>';
	}
	
	public static function MessageAuthorLink($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return '<?php echo '.sprintf($f,'$_ctx->messages->getAuthorLink()').'; ?>';
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
		if (!empty($attr['creadt'])) {
			$type = 'creadt';
		} elseif (!empty($attr['upddt'])) {
			$type = 'upddt';
		} else {
			$type = '';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->messages->getRFC822Date('".$type."')").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->messages->getISO8601Date('".$type."')").'; ?>';
/*return '<?php echo "plop" ; ?>';*/
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->messages->getDate('".$format."','".$type."')").'; ?>';
		}
	}

	public static function MessageTime($attr)
	{
		global $core, $_ctx;

		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		if (!empty($attr['creadt'])) {
			$type = 'creadt';
		} elseif (!empty($attr['upddt'])) {
			$type = 'upddt';
		} else {
			$type = '';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return '<?php echo '.sprintf($f,"\$_ctx->messages->getTime('".$format."','".$type."')").'; ?>';
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
	
	public static function MessageEditContent($attr)
	{
		global $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$res = "<?php\n";
			$res .= '$v = isset($_POST["c_content"]) ? $_POST["c_content"] : $_ctx->message_preview["rawcontent"]; '."\n";
			$res .= 'echo '.sprintf($f,'$v').';'."\n";
			$res .= "?>";
		} else {
			$res = "<?php\n";
			$res .= '$v = $_ctx->message_preview["content"]; '."\n";
			$res .= 'echo '.sprintf($f,'$v').';'."\n";
			$res .= "?>";
		}
		
		return $res;
	}

	public static function MessageUserProfileURL($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->messages->getMemberProfile()').'; ?>';
	}

	public static function MessageEntryTitle($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->messages->post_title').'; ?>';
	}

	public static function MessageFeedID($attr)
	{
		global $core, $_ctx;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->messages->getFeedID()').'; ?>';
	}
}
?>
