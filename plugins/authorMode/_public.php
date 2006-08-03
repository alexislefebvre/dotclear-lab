<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->tpl->addValue('UserPostsURL',array('tplUser','UserPostsURL'));

$core->tpl->addValue('UserCommonName',array('tplUser','UserCommonName'));
$core->tpl->addValue('UserDisplayName',array('tplUser','UserDisplayName'));
$core->tpl->addValue('UserEmail',array('tplUser','UserEmail'));
$core->tpl->addValue('UserID',array('tplUser','UserID'));
$core->tpl->addValue('UserLink',array('tplUser','UserLink'));
$core->tpl->addValue('UserName',array('tplUser','UserName'));
$core->tpl->addValue('UserFirstName',array('tplUser','UserFirstName'));

$core->tpl->addValue('UserURL',array('tplUser','UserURL'));
$core->tpl->addValue('UserDesc',array('tplUser','UserDesc'));

$core->tpl->addBlock('Users',array('tplUser','Users'));
$core->tpl->addBlock('UserEntries',array('tplUser','UserEntries'));

class tplUser
{

	public static function UserEntries($attr,$content)
	{
		if ($GLOBALS['_ctx']->exists("user")) {$user = $GLOBALS['_ctx']->user;}
		$user = isset($attr['user']) ? addslashes($attr['user']) : $user;

		$p =
		"\$params['sql'] = \"AND P.user_id = '".$GLOBALS['core']->con->escape($user)."' \";\n";

		return
		'<?php '.$p.' ?>'.
		$GLOBALS['core']->tpl->Entries($attr,$content);
	}

	public static function Users($attr,$content)
	{
		$res = "<?php\n";
		$res .= '$_ctx->users = $core->blog->getPostsUsers();'."\n";
		$res .= '$_ctx->users->core = $core;'."\n";
		$res .= '$_ctx->users->extend(\'rsExtPost\');'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->users->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->users = null; ?>';
		
		return $res;
	}

	public static function UserInfo($attr)
	{
		$res = "<?php\n";
		$res .= 'print_r($_ctx->users);'."\n";
		$res .= "?>\n";

		return $res;
	}

	public static function UserDesc($attr)
	{
		$res = "<?php\n";
		$res .= 'print_r($_ctx->users->user_desc);'."\n";
		$res .= "?>\n";

		return $res;
	}

	public static function UserPostsURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.
			sprintf($f,'$core->blog->url.$core->url->getBase("user").
			"/".$_ctx->users->user_id').'; ?>';
	}

	public static function getAuthorCN(&$rs)
	{
		return dcUtils::getUserCN($rs->user_id, $rs->user_name,
		$rs->user_firstname, $rs->user_displayname);
	}

	public static function UserCommonName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->getAuthorCN()').'; ?>';
	}
	
	public static function UserDisplayName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_displayname').'; ?>';
	}
	
	public static function UserFirstName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_firstname').'; ?>';
	}
	
	public static function UserName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_name').'; ?>';
	}
	
	public static function UserID($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_id').'; ?>';
	}

	public static function UserEmail($attr)
	{
		$p = 'true';
		if (isset($attr['spam_protected']) && !$attr['spam_protected']) {
			$p = 'false';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->users->getAuthorEmail(".$p.")").'; ?>';
	}
	
	public static function UserLink($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->getAuthorLink()').'; ?>';
	}
	
	public static function UserURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_url').'; ?>';
	}

}
	

class urlUser extends dcUrlHandlers
{
	public static function user($args)
	{
		$n = self::getPageNumber($args);
		
		if ($args == '' && !$n) {
			self::p404();
		} else {
			if ($n) {
				$GLOBALS['_page_number'] = $n;
			}
			$GLOBALS['_ctx']->user = $args;
			$GLOBALS['_ctx']->users =
				$GLOBALS['core']->getUser($GLOBALS['core']->con->escape($args));
			$GLOBALS['_ctx']->users->core = $GLOBALS['core'];
			$GLOBALS['_ctx']->users->extend('rsExtPost');
		
			self::serveDocument('user.html');
		}
		exit;
	}
	
	public static function users($args)
	{
		self::serveDocument('users.html');
		exit;
	}
}
?>