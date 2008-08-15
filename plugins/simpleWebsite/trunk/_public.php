<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2007 Olivier Azeau and contributors. All rights
# reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");

$core->tpl->addBlock('swCustomPostContent',array('SimpleWebsiteTemplates','customPostContent'));
$core->tpl->addBlock('swDefineBlock',array('SimpleWebsiteTemplates','defineBlock'));
$core->tpl->addValue('swReuseBlock',array('SimpleWebsiteTemplates','reuseBlock'));

$core->tpl->addBlock('swMenuHierarchyEntries',array('SimpleWebsiteTemplates','menuHierarchyEntries'));
$core->tpl->addBlock('swMenuLevelEntries',array('SimpleWebsiteTemplates','menuLevelEntries'));
$core->tpl->addBlock('EntryNext',array('SimpleWebsiteTemplates','entryNext'));
$core->tpl->addBlock('EntryPrevious',array('SimpleWebsiteTemplates','entryPrevious'));
$core->tpl->addBlock('swSetHierarchyRef',array('SimpleWebsiteTemplates','setHierarchyRef'));
$core->tpl->addBlock('swEntryIfHierarchy',array('SimpleWebsiteTemplates','entryIfHierarchy'));
$core->tpl->addBlock('swEntryIfHierarchyRef',array('SimpleWebsiteTemplates','entryIfHierarchyRef'));

$core->tpl->addValue('swMenuFeedURL',array('SimpleWebsiteTemplates','menuFeedURL'));
$core->tpl->addValue('swSitemapURL',array('SimpleWebsiteTemplates','sitemapURL'));

$core->tpl->addValue('swShowContext',array('SimpleWebsiteTemplates','showContext')); // for debugging purpose

$core->addBehavior('templateBeforeBlock',array('SimpleWebsiteTemplates','beforeBlock'));
$core->addBehavior('templateAfterBlock',array('SimpleWebsiteTemplates','afterBlock'));

$core->url->register(SIMPLEWEBSITE_SECTION,SIMPLEWEBSITE_SECTION,'^'.SIMPLEWEBSITE_SECTION.'/(.+)$',array('SimpleWebsitePages','section'));
$core->url->register(SIMPLEWEBSITE_FEED,SIMPLEWEBSITE_FEED,'^'.SIMPLEWEBSITE_FEED.'/(.+)$',array('SimpleWebsitePages','feed'));
$core->url->register(SIMPLEWEBSITE_SITEMAP,SIMPLEWEBSITE_SITEMAP,'^'.SIMPLEWEBSITE_SITEMAP.'$',array('SimpleWebsitePages','sitemap'));

define('SIMPLEWEBSITE_PHPOPEN', '<?php require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");'."\n");

class SimpleWebsiteTemplates
{
  // Allows to replace the default template, that is everything between <tpl:swCustomPostContent> and </tpl:swCustomPostContent>,
  // by the content of a specific template file referenced in the post definition
  public static function customPostContent($attr,$content)
  {
    return SIMPLEWEBSITE_PHPOPEN.
    '$swCurrentTemplateContent = SimpleWebsiteTools::GetTemplateContent();
    if ($swCurrentTemplateContent) :
      echo $swCurrentTemplateContent;
    else : ?>'.
    $content.
    '<?php endif; ?>';
  }

  // Defines a block to be reused
  public static function defineBlock($attr,$content)
  {
    return
    "\r\n<?php function swBlock".$attr['name']."() {\r\n".
    "global \$core, \$_ctx;\r\n".
    "?>\r\n".
    $content.
    "\r\n<?php } ?>\r\n";
  }

  // Reuses a block 
  public static function reuseBlock($attr)
  {
    return "\r\n<?php swBlock".$attr['name']."(); ?>\r\n";
  }

  public static function makeArray($attr)
  {
    $attrWithKey = array();
    foreach($attr as $key => $value)
      $attrWithKey[] = '"'.$key.'" => "'.$value.'"';
    return 'array('.implode(',',$attrWithKey).')';
  }

  // All hierarchy entries to current level
  // <tpl:swMenuHierarchyEntries></tpl:swMenuHierarchyEntries>
  public static function menuHierarchyEntries($attr,$content)
  {
    return SIMPLEWEBSITE_PHPOPEN.
    '$_ctx->posts = SimpleWebsiteTools::GetOrderedMenuItemsInMenuHierarchy('.self::makeArray($attr).');
    if ($_ctx->posts) :
    while($_ctx->posts->fetch()) :
    ?>'.
    $content.
    '<?php endwhile; endif; $_ctx->posts = null; ?>';
  }

  // All child entries of current level
  // <tpl:swMenuLevelEntries></tpl:swMenuLevelEntries>
  public static function menuLevelEntries($attr,$content)
  {
    return SIMPLEWEBSITE_PHPOPEN.
    '$_ctx->posts = SimpleWebsiteTools::GetOrderedMenuItemsInMenuLevel('.self::makeArray($attr).');
    while($_ctx->posts->fetch()) :
    ?>'.
    $content.
    '<?php endwhile; $_ctx->posts = null; ?>';
  }
	
  // Used in menu widget to define the reference post, that is the one currently displayed on the page
  // <tpl:swSetHierarchyRef></tpl:swSetHierarchyRef>
  public static function setHierarchyRef($attr,$content)
  {
    return SIMPLEWEBSITE_PHPOPEN.
    "\$_ctx->swHierarchyRef = SimpleWebsiteTools::CurrentMenuHierarchy(); ?>\n".
    $content.
    "<?php \$_ctx->swHierarchyRef = null; ?>\n";
  }
	
  // Used in menu widget together with <tpl:swSetHierarchyRef> to test if the current iterated entry is equal to the reference post or is one or its ascendants
  // <tpl:swEntryIfHierarchy></tpl:swEntryIfHierarchy>
  public static function entryIfHierarchy($attr,$content)
  {
    return SIMPLEWEBSITE_PHPOPEN.
    'if( SimpleWebsiteTools::TestHierarchy('.self::makeArray($attr).') ) : ?>'."\n".
    $content.
    "<?php endif; ?>\n";
  }
	
  // Used in menu widget together with <tpl:swSetHierarchyRef> to test if the current iterated entry is equal to the reference post
  // <tpl:swEntryIfHierarchyRef></tpl:swEntryIfHierarchyRef>
  public static function entryIfHierarchyRef($attr,$content)
  {
    return SIMPLEWEBSITE_PHPOPEN.
    'if( SimpleWebsiteTools::TestHierarchyRef('.self::makeArray($attr).') ) : ?>'."\n".
    $content.
    "<?php endif; ?>\n";
  }
	
  // Overrides the standard <tpl:EntryNext> block to use additional sql parameters
	public static function entryNext($attr,$content)
	{
    return SIMPLEWEBSITE_PHPOPEN.
    'if( SimpleWebsiteTools::GetNextPost('.self::makeArray($attr).',1) ) : ?>'."\n".
    '<?php while($_ctx->posts->fetch()) :
    ?>'.
    $content.
    '<?php endwhile; $_ctx->posts = null; ?>'.
    "<?php endif; ?>\n";
	}
	
  // Overrides the standard <tpl:EntryPrevious> block to use additional sql parameters
	public static function entryPrevious($attr,$content)
	{
    return SIMPLEWEBSITE_PHPOPEN.
    'if( SimpleWebsiteTools::GetNextPost('.self::makeArray($attr).',-1) ) : ?>'."\n".
    '<?php while($_ctx->posts->fetch()) :
    ?>'.
    $content.
    '<?php endwhile; $_ctx->posts = null; ?>'.
    "<?php endif; ?>\n";
	}
  
  public static function beforeBlock(&$core,$b,$attr)
  {
    if ($b == 'Entries') {
			return
			 SIMPLEWEBSITE_PHPOPEN.
       "SimpleWebsiteTools::AddMenuEntriesSelection(\$params,".self::makeArray($attr).",false);\n".
			 "?>\n";
		} elseif ($b == 'Comments') {
			return
			 SIMPLEWEBSITE_PHPOPEN.
       "if(SimpleWebsiteTools::CurrentPostIsMenuItem()) {\n".
          "SimpleWebsiteTools::AddMenuEntriesSelection(\$params,".self::makeArray($attr).",true);\n".
          "\$_ctx->swPosts = \$_ctx->posts; \$_ctx->posts = null;\n".
        "}\n".
			"?>\n";
		}
  }
  
  public static function afterBlock(&$core,$b,$attr)
  {
    if ($b == 'Comments') {
			return
			"<?php\n".
        "if(\$_ctx->exists('swPosts')) {\n".
          "\$_ctx->posts = \$_ctx->swPosts; \$_ctx->swPosts = null;\n".
        "}\n".
			"?>\n";
		}
  }

  // Feed URL for current post
  // Use {{tpl:swMenuFeedURL}} as a replacement for {{tpl:BlogFeedURL}} in post.html
  public static function menuFeedURL($attr)
  {
    return SIMPLEWEBSITE_PHPOPEN.
    'echo SimpleWebsiteTools::GetCurrentMenuFeedURL('.self::makeArray($attr).');
    ?>';
  }

  // Sitemap URL
  public static function sitemapURL($attr)
  {
    return SIMPLEWEBSITE_PHPOPEN.
    'echo SimpleWebsiteTools::GetSitemapURL();
    ?>';
  }

  // for debugging purpose : show the current context stack
  public static function showContext($attr)
  {
    return
    '<?php
      print_r($_ctx->stack);
    ?>';
  }

  // menu widget display mainly consists in evaluating the selected ".menu.html" template file
  public static function menuWidget(&$widget)
  {
    global $core;
    $result = '<div>'.($widget->title ? '<h2>'.html::escapeHTML($widget->title).'</h2>' : '');
    $core->tpl->setPath(array_merge($core->tpl->getPath(),array(dirname(__FILE__))));
    $result .= $core->tpl->getData($widget->content.'.menu.html');
    $result .= '</div>';
    return $result;
  }
}

class SimpleWebsitePages extends dcUrlHandlers
{
	public static function section($args)
	{
		if (!preg_match('#^(.+?)(/page/([0-9]+))?$#',$args,$m))
      self::p404();
      
    if( count($m) == 4 )
			$GLOBALS['_page_number'] = $m[3];
      
    self::post($m[1]);
  }
  
	public static function feed($args)
	{
    // Match URL swFeed/<postid>/(atom|rss2)(/comments)
		if (!preg_match('#^(.+)/(atom|rss2)(/comments)?$#',$args,$m))
			parent::feed($args); // fallback to default feed handler when does not match
    
    // Initialize context with given post id for all posts under given post as menu item
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
    $params['post_url'] = $m[1];
    $_ctx->posts = $core->blog->getPosts($params);
    if ($_ctx->posts->isEmpty())
      self::p404();
    
    // Configure feed template
		$comments = !empty($m[3]);
    $tpl = $m[2];
		if ($comments) {
			$tpl .= '-comments';
			$_ctx->nb_comment_per_page = $core->blog->settings->nb_comment_per_feed;
		} else {
			$_ctx->nb_entry_per_page = $core->blog->settings->nb_post_per_feed;
			$_ctx->short_feed_items = $core->blog->settings->short_feed_items;
		}    
		$tpl .= '.xml';
    
    # Publish Feed
    $_ctx->feed_subtitle = ' - '.$_ctx->posts->post_title;
    $mime = ($m[2]=='atom') ? 'application/atom+xml' : 'text/xml';
		header('Content-Type: '.$mime.'; charset=UTF-8');
		$out = $core->tpl->getData($tpl);
		http::etag($out,http::getSelfURI());
		echo $out;
  }
  
	public static function sitemap($args)
	{
    global $core, $_ctx;
    $core->tpl->setPath(array_merge($core->tpl->getPath(),array(dirname(__FILE__))));
    $_ctx->swCustomPostContent = SIMPLEWEBSITE_SITEMAP.'.html';
    $_ctx->posts = new SimpleWebsiteSitemapPost();
    self::serveDocument('post.html');
    exit;
  }

}
?>