<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postExpired, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
if (!$core->plugins->moduleExists('metadata')){return;}

__('Expired on');
__('This entry has no expirion date');

if (in_array($core->url->type,array('default','feed'))) {
	$core->addBehavior('publicBeforeDocument',array('publicBehaviorPostExpired','unpublishExpiredEntries'));
}
$core->addBehavior('coreBlogGetPosts',array('publicBehaviorPostExpired','coreBlogGetPosts'));

$core->tpl->addBlock('EntryExpiredIf',array('tplPostExpired','EntryExpiredIf'));
$core->tpl->addValue('EntryExpiredDate',array('tplPostExpired','EntryExpiredDate'));
$core->tpl->addValue('EntryExpiredTime',array('tplPostExpired','EntryExpiredTime'));

class publicBehaviorPostExpired
{
	public static function unpublishExpiredEntries($core)
	{
		$meta = new dcMeta($core);

		# Get expired dates and post_id
		$posts = $core->con->select(
			'SELECT P.post_id, P.post_tz, META.meta_id '.
			'FROM '.$core->prefix.'post P '.
			'INNER JOIN '.$core->prefix.'meta META '.
			'ON META.post_id = P.post_id '.
			"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
			"AND P.post_type = 'post' ".
			"AND META.meta_type = 'postexpired' "
		);
		# No expired date
		if ($posts->isEmpty()) {
			return;
		}
		# Get curent timestamp
		$now = dt::toUTC(time());
		# Prepared post cursor
		$post_cur = $core->con->openCursor($core->prefix.'post');
		# Loop through marked posts
		while($posts->fetch())
		{
			# Check if post is outdated
			$now_tz = $now + dt::getTimeOffset($posts->post_tz,$now);
			$meta_tz = strtotime($posts->meta_id);
			if ($now_tz > $meta_tz)
			{
				# Delete meta for expired date
				$core->auth->sudo(array($meta,'delPostMeta'),$posts->post_id,'postexpired');
				# Retrieve action on 'post_status'
				$rs_status = $core->con->select(
					'SELECT meta_id '.
					'FROM '.$core->prefix.'meta '.
					'WHERE post_id = '.$posts->post_id.' '.
					"AND meta_type = 'postexpiredstatus' ".
					$core->con->limit(1)
				);
				# Retrieve action on 'cat_id'
				$rs_cat = $core->con->select(
					'SELECT meta_id '.
					'FROM '.$core->prefix.'meta '.
					'WHERE post_id = '.$posts->post_id.' '.
					"AND meta_type = 'postexpiredcat' ".
					$core->con->limit(1)
				);
				# Retrieve action on 'post_selected'
				$rs_selected = $core->con->select(
					'SELECT meta_id '.
					'FROM '.$core->prefix.'meta '.
					'WHERE post_id = '.$posts->post_id.' '.
					"AND meta_type = 'postexpiredselected' ".
					$core->con->limit(1)
				);

				# --BEHAVIOR-- publicBeforePostExpiredUpdate
				$core->callbehavior('publicBeforePostExpiredUpdate',$posts->post_id,$posts->meta_id,$posts->post_tz);

				# If there are actions to do
				if (!$rs_status->isEmpty() 
				 || !$rs_cat->isEmpty() 
				 || !$rs_selected->isEmpty())
				{
					# Prepare post cursor
					$post_cur->clean();
					$post_cur->post_upddt = date('Y-m-d H:i:s',$now_tz);
					# Action on 'post_status'
					if (!$rs_status->isEmpty())
					{
						# Set status
						$post_status = (integer) substr($rs_status->meta_id,1);
						$post_cur->post_status = $post_status;

						# Delete meta record for status
						$core->auth->sudo(array($meta,'delPostMeta'),$posts->post_id,'postexpiredstatus');
					}
					# Action on 'cat_id'
					if (!$rs_cat->isEmpty())
					{
						# Set category
						$post_cat = (integer) substr($rs_cat->meta_id,1);
						$post_cur->cat_id = $post_cat ? $post_cat : null;

						# Delete meta record for category
						$core->auth->sudo(array($meta,'delPostMeta'),$posts->post_id,'postexpiredcat');
					}
					# Action on 'post_selected'
					if (!$rs_selected->isEmpty())
					{
						# Set selected
						$post_selected = (integer) substr($rs_selected->meta_id,1);
						$post_cur->post_selected = $post_selected ? 1 : 0;

						# Delete meta record for selected
						$core->auth->sudo(array($meta,'delPostMeta'),$posts->post_id,'postexpiredselected');
					}
					# Update post
					$post_cur->update(
						'WHERE post_id = '.$posts->post_id.' '.
						"AND blog_id = '".$core->con->escape($core->blog->id)."' "
					);
					# Say blog is updated
					$core->blog->triggerBlog();
				}

				# --BEHAVIOR-- publicAfterPostExpiredUpdate
				$core->callbehavior('publicAfterPostExpiredUpdate',$posts->post_id,$posts->meta_id,$posts->post_tz);
			}
		}
	}

	public static function coreBlogGetPosts(&$rs)
	{
		$rs->extend('rsExtPostExpiredPublic');
	}
}

class rsExtPostExpiredPublic extends rsExtPost
{
	public static function postExpiredDate(&$rs,$absolute_urls=false)
	{
		if (!$rs->postexpired[$rs->post_id]) {
			$meta = new dcMeta($rs->core);
			$rs_date = $meta->getMeta('postexpired',1,null,$rs->post_id);
			return $rs_date->isEmpty() ? null : (string) $rs_date->meta_id;
		}
		return $rs->postexpired[$rs->post_id];
	}
}

class tplPostExpired
{
	public static function EntryExpiredIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';

		if (isset($attr['has_date']))
		{
			$sign = (boolean) $attr['has_date'] ? '!' : '=';
			$if[] = '(null '.$sign.'== $_ctx->posts->postExpiredDate())';
		}
		else {
			$if[] = '(null !== $_ctx->posts->postExpiredDate())';
		}

		return 
		"<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".
		$content.
		"<?php endif; ?>\n";
	}

	public static function EntryExpiredDate($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['rfc822']))
			$res = sprintf($f,"dt::rfc822(strtotime(\$_ctx->posts->postExpiredDate()),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$res = sprintf($f,"dt::iso8601(strtotime(\$_ctx->posts->postExpiredDate(),\$_ctx->posts->post_tz)");
		elseif ($format)
			$res = sprintf($f,"dt::dt2str('".$format."',\$_ctx->posts->postExpiredDate())");
		else 
			$res = sprintf($f,"dt::dt2str((version_compare(DC_VERSION,'2.2-alpha','>=') ? \$core->blog->settings->system->date_format : \$core->blog->settings->date_format),\$_ctx->posts->postExpiredDate())");

		return '<?php if (null !== $_ctx->posts->postExpiredDate()) { echo '.$res.'; } ?>';
	}

	public static function EntryExpiredTime($attr)
	{
		return '<?php if (null !== $_ctx->posts->postExpiredDate()) { echo '.sprintf($GLOBALS['core']->tpl->getFilters($attr),"dt::dt2str(".(!empty($attr['format']) ? "'".addslashes($attr['format'])."'" : "(version_compare(DC_VERSION,'2.2-alpha','>=') ? \$core->blog->settings->system->time_format : \$core->blog->settings->time_format)").",\$_ctx->posts->postExpiredDate())").'; } ?>';
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
?>