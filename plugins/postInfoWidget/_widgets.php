<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postInfoWidget, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('postInfoWidget','adminWidget'));

class postInfoWidget
{
	public static function adminWidget($w)
	{
		global $core;

		$w->create('postinfowidget',
			__('Entry information list'),array('postInfoWidget','publicWidget'));
		$w->postinfowidget->setting('title',
			__('Title:'),
			__('About this entry'),'text');
		$w->postinfowidget->setting('dt_str',
			__('Publish date text:'),
			__('Publish on %Y-%m-%d %H:%M'),'text');
		$w->postinfowidget->setting('creadt_str',
			__('Create date text:'),
			__('Create on %Y-%m-%d %H:%M'),'text');
		$w->postinfowidget->setting('upddt_str',
			__('Update date text:'),
			__('Update on %Y-%m-%d %H:%M'),'text');
		$w->postinfowidget->setting('lang_str',
			__('Language: (%T = name, %C = code, %F = flag)'),
			__('Language: %T %F'),'text');
		$w->postinfowidget->setting('author_str',
			__('Author text: (%T = author)'),
			__('Author: %T'),'text');
		$w->postinfowidget->setting('category_str',
			__('Category text: (%T = category)'),
			__('Category: %T'),'text');
		if ($core->plugins->moduleExists('tags')) {
			$w->postinfowidget->setting('tag_str',
			__('Tags text: (%T = tags list)'),
			__('Tags: %T'),'text');
		}
		$w->postinfowidget->setting('attachment_str',
			__('Attachments text: (%T = text, %D = numeric)'),
			__('Attachments: %T'),'text');
		$w->postinfowidget->setting('comment_str',
			__('Comments text: (%T = text, %D = numeric)'),
			__('Comments: %T'),'text');
		$w->postinfowidget->setting('trackback_str',
			__('Trackbacks text: (%T = text, %D = numeric)'),
			__('Trackbacks: %T'),'text');
		$w->postinfowidget->setting('permalink_str',
			__('Permalink text: (%T = text link, %F = full link)'),
			__('%T'),'text');
		$w->postinfowidget->setting('feed',
			__('Show comment feed url'),1,'check');
		$w->postinfowidget->setting('navprevpost',
			__('Link to previous entry: (%T = navigation text, %F = entry title)'),
			__('%T'),'text');
		$w->postinfowidget->setting('navnextpost',
			__('Link to next entry: (%T = navigation text, %F = entry title)'),
			__('%T'),'text');
		$w->postinfowidget->setting('navprevcat',
			__('Link to previous entry of this category: (%T = navigation text, %F = entry title)'),
			__('%T'),'text');
		$w->postinfowidget->setting('navnextcat',
			__('Link to next entry of this category: (%T = navigation text, %F = entry title)'),
			__('%T'),'text');
		$w->postinfowidget->setting('style',
			__('Try to adapt style'),'small','combo',array(
				__('No style') => '-',
				__('Small icon') => 'small',
				__('Normal icon') => 'normal'));
		$w->postinfowidget->setting('rmvinfo',
			__('Try to remove entry information'),1,'check');
		$w->postinfowidget->setting('rmvtags',
			__('Try to remove entry tags'),1,'check');
		$w->postinfowidget->setting('rmvnav',
			__('Try to remove entry navigation'),1,'check');

		# --BEHAVIOR-- postInfoWidgetAdmin
		$core->callBehavior('postInfoWidgetAdmin',$w);
	}

	public static function publicWidget($w)
	{
		global $core, $_ctx;

		if ($core->url->type != 'post' || !$_ctx->posts->post_id){return;}

		$link = '<a href="%s">%s</a>';
		$title = (strlen($w->title) > 0) ? 
			'<h2>'.html::escapeHTML($w->title).'</h2>' : null;
		$content = '';
		
		if ($w->dt_str != '')
		{
			$content .= postInfoWidget::li($w,'date',dt::str(
				$w->dt_str,
				strtotime($_ctx->posts->post_dt),
				$core->blog->settings->system->blog_timezone
			));
		}
		if ($w->creadt_str != '')
		{
			$content .= postInfoWidget::li($w,'create',dt::str(
				$w->creadt_str,
				strtotime($_ctx->posts->post_creadt),
				$core->blog->settings->system->blog_timezone
			));
		}
		if ($w->upddt_str != '')
		{
			$content .= postInfoWidget::li($w,'update',dt::str(
				$w->upddt_str,
				strtotime($_ctx->posts->post_upddt),
				$core->blog->settings->system->blog_timezone
			));
		}
		if ($w->lang_str != '')
		{
			$ln = l10n::getISOcodes();
			$lang_code = $_ctx->posts->post_lang ? $_ctx->posts->post_lang : $core->blog->settings->system->lang;
			$lang_name = isset($ln[$lang_code]) ? $ln[$lang_code] : $lang_code;
			$lang_flag = file_exists(dirname(__FILE__).'/img/flags/'.$lang_code.'.png') ? '<img src="'.$core->blog->getQmarkURL().'pf=postInfoWidget/img/flags/'.$lang_code.'.png" alt="'.$lang_name.'" />' : '';
			
			$content .= postInfoWidget::li($w,'lang',str_replace(
				array('%T','%C','%F'),
				array($lang_name,$lang_code,$lang_flag),
				html::escapeHTML($w->lang_str))
			);
		}
		if ($w->author_str != '')
		{
			$content .= postInfoWidget::li($w,'author',str_replace(
				'%T',
				$_ctx->posts->getAuthorLink(),
				html::escapeHTML($w->author_str))
			);
		}
		if ($w->category_str != '' && $_ctx->posts->cat_id)
		{
			$content .= postInfoWidget::li($w,'category',str_replace(
				'%T',sprintf(
					$link,
					$_ctx->posts->getCategoryURL(),
					html::escapeHTML($_ctx->posts->cat_title)
				),
				html::escapeHTML($w->category_str))
			);
		}
		if ($w->tag_str != '' && $core->plugins->moduleExists('tags'))
		{
			$meta = $core->meta->getMetadata(array('meta_type'=>'tag','post_id'=>$_ctx->posts->post_id));
			$metas = array();
			while ($meta->fetch()) {
				$metas[$meta->meta_id] = sprintf(
					$link,
					$core->blog->url.$core->url->getBase('tag')."/".
					rawurlencode($meta->meta_id),$meta->meta_id
				);
			}
			if (!empty($metas)) {
				$content .= postInfoWidget::li($w,'tag',str_replace(
					'%T',implode(', ',$metas),html::escapeHTML($w->tag_str))
				);
			}
		}
		if ($w->attachment_str != '')
		{
			$nb = $_ctx->posts->countMedia();
			if ($nb == 0) {
				$attachment_numeric = 0;
				$attachment_textual = __('no attachment');
			} elseif ($nb == 1) {
				$attachment_numeric = sprintf($link,'#attachment',1);
				$attachment_textual = sprintf($link,'#attachment',__('one attachment'));
			} else {
				$attachment_numeric = sprintf($link,'#attachment',$nb);
				$attachment_textual = sprintf($link,'#attachment',sprintf(__('%d attachments'),$nb));
			}
			
			$content .= postInfoWidget::li($w,'attachment',str_replace(
				array('%T','%D'),
				array($attachment_textual,$attachment_numeric),
				html::escapeHTML($w->attachment_str))
			);
		}
		if ($w->comment_str != '' && $_ctx->posts->commentsActive())
		{
			$nb = $_ctx->posts->nb_comment;
			if ($nb == 0) {
				$comment_numeric = 0;
				$comment_textual = __('no comment');
			} elseif ($nb == 1) {
				$comment_numeric = sprintf($link,'#comments',1);
				$comment_textual = sprintf($link,'#comments',__('one comment'));
			} else {
				$comment_numeric = sprintf($link,'#comments',$nb);
				$comment_textual = sprintf($link,'#comments',sprintf(__('%d comments'),$nb));
			}
			
			$content .= postInfoWidget::li($w,'comment',str_replace(
				array('%T','%D'),
				array($comment_textual,$comment_numeric),
				html::escapeHTML($w->comment_str))
			);
		}
		if ($w->trackback_str != '' && $_ctx->posts->trackbacksActive())
		{
			$nb = $_ctx->posts->nb_trackback;
			if ($nb == 0) {
				$trackback_numeric = 0;
				$trackback_textual = __('no trackback');
			} elseif ($nb == 1) {
				$trackback_numeric = sprintf($link,'#pings',1);
				$trackback_textual = sprintf($link,'#pings',__('one trackback'));
			} else {
				$trackback_numeric = sprintf($link,'#pings',$nb);
				$trackback_textual = sprintf($link,'#pings',sprintf(__('%d trackbacks'),$nb));
			}
			
			$content .= postInfoWidget::li($w,'trackback',str_replace(
				array('%T','%D'),
				array($trackback_textual,$trackback_numeric),
				html::escapeHTML($w->trackback_str))
			);
		}
		if ($w->permalink_str)
		{
			$content .= postInfoWidget::li($w,'permalink',str_replace(
				array('%T','%F'),
				array(sprintf($link,$_ctx->posts->getURL(),__('Permalink')),$_ctx->posts->getURL()),
				html::escapeHTML($w->permalink_str))
			);
		}
		if ($w->feed && $_ctx->posts->commentsActive())
		{
			$content .= postInfoWidget::li($w,'feed',sprintf(
				$link,
				$core->blog->url.$core->url->getBase('feed').'/atom/comments/'.$_ctx->posts->post_id,
				__("This post's comments feed")
				,html::escapeHTML($w->tag_str))
			);
		}
		if ($w->navprevpost)
		{
			$npp = postInfoWidget::nav($_ctx->posts,-1,false,
				__('Previous entry'),$w->navprevpost
			);
			if ($npp) {
				$content .= postInfoWidget::li($w,'previous',$npp);
			}
		}
		if ($w->navnextpost)
		{
			$nnp = postInfoWidget::nav($_ctx->posts,1,false,
				__('Next entry'),$w->navnextpost
			);
			if ($nnp) {
				$content .= postInfoWidget::li($w,'next',$nnp);
			}
		}
		if ($w->navprevcat)
		{
			$npc = postInfoWidget::nav($_ctx->posts,-1,true,
				__('Previous entry of this category'),$w->navprevcat
			);
			if ($npc) {
				$content .= postInfoWidget::li($w,'previous',$npc);
			}
		}
		if ($w->navnextcat)
		{
			$nnc = postInfoWidget::nav($_ctx->posts,1,true,
				__('Next entry of this category'),$w->navnextcat
			);
			if ($nnc) {
				$content .= postInfoWidget::li($w,'next',$nnc);
			}
		}
		
		# --BEHAVIOR-- postInfoWidgetPublic
		$content .= $core->callBehavior('postInfoWidgetPublic',$w);
		
		if (empty($content)) {return;}
		
		$rmv = '';
		if ($w->rmvinfo || $w->rmvtags || $w->rmvnav) {
			$rmv .= 
			'<script type="text/javascript">'."\n".
			'$(function() {'."\n";
			if ($w->rmvinfo) {
				$rmv .= 
				'var piw_pi=$("#content .post-info");'."\n".
				'if ($(piw_pi).length!=0){$(piw_pi).hide();}'."\n";
			}
			if ($w->rmvtags) {
				$rmv .= 
				'var piw_pt=$("#content .post-tags");'."\n".
				'if ($(piw_pt).length!=0){$(piw_pt).hide();}'."\n";
			}
			if ($w->rmvnav) {
				$rmv .= 
				'var piw_pn=$("#content #navlinks");'."\n".
				'if ($(piw_pn).length!=0){$(piw_pn).hide();}'."\n";
			}
			$rmv .= 
			'});'."\n".
			"</script>\n";
		}
		
		return $rmv.'<div class="postinfowidget">'.$title.'<ul>'.$content.'</ul></div>';
	}

	public static function li($w,$i,$c)
	{
		$s = ' style="padding-left:%spx;background: transparent url(\''.$GLOBALS['core']->blog->getQmarkURL().'pf=postInfoWidget/img/%s%s.png\') no-repeat left center;"';
		if ($w->style == 'small') {
			$s = sprintf($s,16,$i,'-small');
		} elseif($w->style == 'normal') {
			$s = sprintf($s,20,$i,'');
		} else {
			$s = '';
		}
		$l = '<li class="postinfo-%s"%s>%s</li>';
	
		return sprintf($l,$i,$s,$c);
	}
	
	public static function nav($p,$d,$r,$t,$c)
	{
		global $core;
		
		$rs = $core->blog->getNextPost($p,$d,$r);
		if ($rs !== null)
		{
			$l = '<a href="%s" title="%s">%s</a>';
			$u = $rs->getURL();
			$e = html::escapeHTML($rs->post_title);
			
			return str_replace(
				array('%T','%F'),
				array(sprintf($l,$u,$e,$t),sprintf($l,$u,$t,$e)),
				$c
			);
		}
		return '';
	}
}
?>