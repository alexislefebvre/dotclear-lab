<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Admin behaviors and various admin func
class soCialMeAdmin
{
	#
	# Commons helpers
	#
	
	# Admin URLs
	public static function link($amp,$page='',$part='',$lib='',$more='')
	{
		$url = DC_ADMIN_URL.'plugin.php?p=soCialMe&page=%s&part=%s&lib=%s%s';
		if ($amp) {
			$url = str_replace('&','&amp;',$url);
		}
		return sprintf($url,$page,$part,$lib,$more);
	}
	
	# Top admin menu
	public static function top($page='',$more_head='')
	{
		$title = !empty($page['title']) ? ' '.$page['title'] : '';
		$menu = !empty($page['parts']) ? ' &rsaquo; '.self::menu($page['parts']) : '';
		$section = !empty($_REQUEST['section']) ? $_REQUEST['section'] : '';
		
		return 
		'<html><head><title>'.__('Social').$title.'</title>'.
		'<style type="text/css" media="screen">'."\n".
		"@import url(index.php?pf=soCialMe/style.css);\n".
		"</style>\n".
		dcPage::jsLoad('index.php?pf=soCialMe/js/main.js').
		'<script type="text/javascript">'."\n//<![CDATA[\n".
		dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
		dcPage::jsVar('jcToolsBox.prototype.section',$section).
		"\n//]]>\n</script>\n".
		$more_head.
		'</head>'.
		'<body>'.
		'<div class="two-cols"><div class="col left"><h2>'.
		$GLOBALS['core']->blog->name.' &rsaquo; <a href="'.self::link(1,'').'" title="'.__('Manage social services').'">'.__('Social').'</a>'.
		(!empty($title) ? ' &rsaquo;'.$title : '').
		$menu.'</h2></div></div><div class="clear">';
	}
	
	# Top admin sub menu
	private static function menu($parts)
	{
		$page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : '';
		$part = !empty($_REQUEST['part']) ? $_REQUEST['part'] : key($parts);
		$res = '';
		
		if (isset($parts[$part]))
		{
			$res = $parts[$part].' ';
		}
		$res .= '</h2></div><div class="col right"><h2>';
		
		foreach($parts as $k => $v)
		{
			//if ($k == $current) continue; //remove current page from menu
			
			$res .= '<a class="button" href="'.soCialMeAdmin::link(1,$page,$k).'">'.$v.'</a> ';
		}
		return $res;
	}
	
	# Javacsript for dragsort on multiple tables
	public static function multiDragsortScript($things)
	{
		$i = 0;
		$script = $script_var = $script_drag = $script_sort = '';
		foreach($things as $thing => $plop)
		{
			$k = $i == 0 ? '' : (string) $i; //first dragsort hate another name
			$i++;
			
			$script_var .= "var dragsort".$k." = ToolMan.dragsort();\n";
			$script_drag .= 'dragsort'.$k.'.makeTableSortable($("#priority-list-'.$thing.'").get(0),dotclear.sortable.setHandle,dotclear.sortable.saveOrder'.$k.');'."\n";
			$script_sort .= ',saveOrder'.$k.': function(item) {	var group = item.toolManDragGroup; var order = document.getElementById('."'".'js_orders_'.$thing."'".'); group.register('."'".'dragend'."'".', function() { order.value = '."''".'; items = item.parentNode.getElementsByTagName('."'".'tr'."'".'); for (var i=0; i<items.length; i++) { order.value += items[i].id.substr(3)+'."'".','."'".'; } }); }'."\n";
		}

		return 
		'<script type="text/javascript">'.
		"//<![CDATA[\n".
		$script_var.'$(function() { '.$script_drag.' }); dotclear.sortable = { setHandle: function(item) { var handle = $(item).find('."'".'td.handle'."'".').get(0); while (handle.firstChild) { handle.removeChild(handle.firstChild); } item.toolManDragGroup.setHandle(handle); handle.className = handle.className+'."'".' handler'."'".'; }'.$script_sort.'};'.
		"\n//]]>\n</script>\n";
	}
	
	#
	# Admin behaviors (Writer part)
	#
	
	# Added expandable feature
	# /admin/post.php#L292
	public static function adminPostHeaders($posts_actions=true)
	{
		return dcPage::jsLoad('index.php?pf=soCialMe/js/adminpost.js');
	}
	
	# Added hidden field to check post status change for auto tweet
	# /admin/post.php#L447
	public static function adminPostFormSidebar($post)
	{
		global $core;
		
		if (!$core->blog->settings->soCialMeWriter->active) {
			return;
		}
		
		# Remind old post status, check new post, uncheck update post
		if ($post === null)
		{
			$old_post_status = -1;
			$check = true;
		}
		else {
			$old_post_status = $post->post_status;
			$check = false;
		}
		
		
		# Check user right
		$can_publish = $core->auth->check('publish,contentadmin',$core->blog->id);
		if (!$core->auth->check('contentadmin',$core->blog->id))
		{
			if ($post === null) {
				$can_publish = false;
			}
			else {
				$rs = $this->con->select(
					'SELECT post_id '.
					'FROM '.$this->prefix.'post '.
					'WHERE post_id = '.$id.' '.
					"AND user_id = '".$core->con->escape($core->auth->userID())."' "
				);
				$can_publish = !$rs->isEmpty();
			}
		}
		# Has right, show option
		if ($can_publish) {
			echo 
			'<h3 id="socialwriter-form-title">'.__('Social writer').'</h3>'.
			'<div id="socialwriter-form-content">'.
			'<p class="label"><label class="classic">'.
			form::checkbox('socialwriter_send','1',$check).' '.
			__('Share this').'</label>'.
			form::hidden(array('socialwriter_old_post_status'),(string) $old_post_status).
			'</p></div>';
		}
		else {
			echo '<div>'.
			form::hidden(array('socialwriter_old_post_status'),(string) $old_post_status).
			'</div>';
		}
	}
	
	# On post create
	# /admin/post.php#L230
	public static function adminAfterPostUpdate($cur,$post_id)
	{
		self::adminAfterPostSave($cur,$post_id,true);
	}
	
	# On post update
	# /admin/post.php#L251
	public static function adminAfterPostCreate($cur,$post_id)
	{
		self::adminAfterPostSave($cur,$post_id,false);
	}
	
	# On multiple posts update
	# /admin/posts_actions.php#L62
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'publish' 
		 && $core->auth->check('publish,contentadmin',$core->blog->id) 
		 && $core->blog->settings->soCialMeWriter->active)
		{
			try {
				
				// user status
				$req_user = '';
				if (!$core->auth->check('contentadmin',$core->blog->id)) {
					$req_user = "AND user_id = '".$core->con->escape($core->auth->userID())."' ";
				}
				
				while ($posts->fetch()) {
				
					// get old status to send only post goes pusblished
					$rs_olds = $core->con->select(
						'SELECT post_id FROM '.$core->prefix.'post '.
						"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
						'AND post_status != 1 '.
						$req_user.
						'AND post_id = '.$posts->post_id
					);
					
					// if status goes published
					if (!$rs_olds->isEmpty())
					{
						self::onPostSave($core,$posts,$posts->post_id,true);
					}
				}
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
	}
	
	# Filter from adminAfterPostCreate, adminAfterPostUpdate to onPostSave
	private static function adminAfterPostSave($cur,$post_id,$is_update)
	{
		global $core;
		
		# Active and published
		if (!$core->blog->settings->soCialMeWriter->active
		|| $cur->post_status != 1) return;
		
		# From post form
		if (isset($_POST['socialwriter_old_post_status']))
		{
			if (empty($_POST['socialwriter_send']) || !$core->auth->check('publish,contentadmin',$core->blog->id)) return;
		}
		
		self::onPostSave($core,$cur,$post_id,$is_update);
	}
	
	# send message on post create/update
	private static function onPostSave($core,$cur,$post_id,$is_update)
	{
		$key = $is_update ? 'postupdate' : 'postpublish';
		
		# Active
		if (!$core->blog->settings->soCialMeWriter->active) return;
		
		# Load services
		$soCialMeWriter = new soCialMeWriter($core);
		
		# List of service per action
		$actions = $soCialMeWriter->getMarker('action');
		
		# List of format per type
		$formats = $soCialMeWriter->getMarker('format');
		
		# prepare data
		// shorten url
		$url = $core->blog->url.$core->getPostPublicURL($cur->post_type,html::sanitizeURL($cur->post_url));
		$posturl = soCialMeUtils::reduceURL($url);
		$posturl = $posturl ? $posturl : $url;
		// author
		$user = $core->getUser($cur->user_id);
		$postauthor = dcUtils::getUserCN($user->user_id,$user->user_name,$user->user_firstname,$user->user_displayname);
		// get tags
		$meta = '';
		$meta_array = array();
		$rs_meta = $core->meta->getMetadata(array('meta_type'=>'tag','post_id'=>$post_id));
		if (!$rs_meta->isEmpty()) {
			while($rs_meta->fetch()) {
				$meta .= ' #'.$rs_meta->meta_id;
				$meta_array[] = $rs_meta->meta_id;
			}
		}
		// get category
		$cat = '';
		$cat_array = array();
		if ($cur->cat_id) {
			$rs_cat = $core->blog->getCategory($cur->cat_id);
			if (!$rs_cat->isEmpty()) {
				$cat = '#'.$rs_cat->cat_title;
				$cat_array[] = $rs_cat->cat_title;
			}
		}
		
		# sendMessage
		if (!empty($formats[$key]['Message']) && !empty($actions[$key]['Message']))
		{
			// parse message
			$message_txt = str_replace(
				array('%blog%','%title%','%url%','%author%','%category%','%tags%'),
				array($core->blog->name,$cur->post_title,$posturl,$postauthor,$cat,$meta),
				$formats[$key]['Message']
			);
			
			// send message
			if (!empty($message_txt))
			{
				foreach($actions[$key]['Message'] as $service_id)
				{
					$soCialMeWriter->play($service_id,'Message','Content',$message_txt);
				}
			}
		}
		
		# sendLink
		if (!empty($actions[$key]['Link']))
		{
			foreach($actions[$key]['Link'] as $service_id)
			{
				$soCialMeWriter->play($service_id,'Link','Content',$cur->post_title,$posturl);
			}
		}
		
		# sendData
		// not yet implemented
		
		#sendArticle
		if (!empty($actions[$key]['Article']))
		{
			$record = soCialMeUtils::fillPlayRecord(array(
				'url' => $post_url,
				'shorturl' => soCialMeUtils::reduceURL($post_url),
				'title' => $cur->post_title,
				'excerpt' => $cur->post_excerpt_xhtml,
				'content' => $cur->post_content_xhtml,
				'author' => $postauthor,
				'tags' => implode(',',$meta_array),
				'category' => implode(',',$cat_array)
			));
			
			foreach($actions[$key]['Article'] as $service_id)
			{
				$soCialMeWriter->play($service_id,'Article','Content',$record);
			}
		}
	}
}
?>