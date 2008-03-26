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

$core->tpl->addValue('swMenuFeedURL',array('SimpleWebsiteTemplates','menuFeedURL'));
$core->tpl->addValue('swSitemapURL',array('SimpleWebsiteTemplates','sitemapURL'));

$core->addBehavior('templateBeforeBlock',array('SimpleWebsiteTemplates','beforeBlock'));

$core->url->register(SIMPLEWEBSITE_SECTION,SIMPLEWEBSITE_SECTION,'^'.SIMPLEWEBSITE_SECTION.'/(.+)$',array('SimpleWebsitePages','section'));
$core->url->register(SIMPLEWEBSITE_FEED,SIMPLEWEBSITE_FEED,'^'.SIMPLEWEBSITE_FEED.'/(.+)$',array('SimpleWebsitePages','feed'));
$core->url->register(SIMPLEWEBSITE_SITEMAP,SIMPLEWEBSITE_SITEMAP,'^'.SIMPLEWEBSITE_SITEMAP.'$',array('SimpleWebsitePages','sitemap'));

class SimpleWebsiteTemplates
{
  // Allows to replace the default template, that is everything between <tpl:swCustomPostContent> and </tpl:swCustomPostContent>,
  // by the content of a specific template file referenced in the post definition
  public static function customPostContent($attr,$content)
  {
    return
    '<?php
    require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");
    $swCurrentTemplateContent = SimpleWebsiteTools::GetTemplateContent();
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

  public static function makeArrayCallback(&$value,$key)
  {
    $value = '"'.$key.'" => "'.$value.'"';
  }

  public static function makeArray($attr)
  {
    array_walk($attr, 'SimpleWebsiteTemplates::makeArrayCallback');
    return 'array('.implode(',',$attr).')';
  }

  // All hierarchy entries to current level
  // <tpl:swMenuHierarchyEntries></tpl:swMenuHierarchyEntries>
  public static function menuHierarchyEntries($attr,$content)
  {
    return
    '<?php
    require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");
    $_ctx->posts = SimpleWebsiteTools::GetOrderedMenuItemsInMenuHierarchy('.self::makeArray($attr).');
    while($_ctx->posts->fetch()) :
    ?>'.
    $content.
    '<?php endwhile; $_ctx->posts = null; ?>';
  }

  // All child entries of current level
  // <tpl:swMenuLevelEntries></tpl:swMenuLevelEntries>
  public static function menuLevelEntries($attr,$content)
  {
    return
    '<?php
    require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");
    $_ctx->posts = SimpleWebsiteTools::GetOrderedMenuItemsInMenuLevel('.self::makeArray($attr).');
    while($_ctx->posts->fetch()) :
    ?>'.
    $content.
    '<?php endwhile; $_ctx->posts = null; ?>';
  }
	
	public function entryNext($attr,$content)
	{
    return
    '<?php
    require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");
    if( SimpleWebsiteTools::GetNextPost('.self::makeArray($attr).',1) ) : ?>'."\n".
    '<?php while($_ctx->posts->fetch()) :
    ?>'.
    $content.
    '<?php endwhile; $_ctx->posts = null; ?>'.
    "<?php endif; ?>\n";
	}
	
	public function entryPrevious($attr,$content)
	{
    return
    '<?php
    require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");
    if( SimpleWebsiteTools::GetNextPost('.self::makeArray($attr).',-1) ) : ?>'."\n".
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
			"<?php\n".
        "require_once(\$core->plugins->moduleRoot('simpleWebsite').'/tools.php');\n".
        //"if(SimpleWebsiteTools::CurrentPostIsMenuItem())\n".
				"SimpleWebsiteTools::AddMenuEntriesSelection(\$params,".self::makeArray($attr).",false);\n".
			"?>\n";
		} elseif ($b == 'Comments') {
			return
			"<?php\n".
        "require_once(\$core->plugins->moduleRoot('simpleWebsite').'/tools.php');\n".
        "if(SimpleWebsiteTools::CurrentPostIsMenuItem())\n".
				"SimpleWebsiteTools::AddMenuEntriesSelection(\$params,".self::makeArray($attr).",true);\n".
			"?>\n";
		}
  }

  // Feed URL for current post
  // Use {{tpl:swMenuFeedURL}} as a replacement for {{tpl:BlogFeedURL}} in post.html
  public static function menuFeedURL($attr)
  {
    return
    '<?php
    require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");
    echo SimpleWebsiteTools::GetCurrentMenuFeedURL('.self::makeArray($attr).');
    ?>';
  }

  // Sitemap URL
  public static function sitemapURL($attr)
  {
    return
    '<?php
    require_once($core->plugins->moduleRoot("simpleWebsite")."/tools.php");
    echo SimpleWebsiteTools::GetSitemapURL();
    ?>';
  }
  
  public static function menuWidgetItem(&$record,&$content,$emphasize=false)
  {
    $name = html::escapeHTML($record->post_title);
    if($emphasize)
      $name = '<span class="swCurrentItem">'.$name.'</span>';
    return '<li><a href="'.$record->getURL().'">'.$name.'</a>'.$content.'</li>';
  }
  
  public static function menuWidgetHierarchy(&$levels,&$posts)
  {
    global $_ctx;
    $result = '<ul>';
    if($levels)
      $levels->fetch();
    while( $posts->fetch() ) {
      if( $posts->post_id == $levels->post_id ) {
        $childrenPosts = SimpleWebsiteTools::GetOrderedMenuItemsInMenuLevel(array('no_content' => '1','parent_id' => $levels->post_id));
        $content = self::menuWidgetHierarchy($levels,$childrenPosts);
        $result .= self::menuWidgetItem($posts,$content,true);
      } else {
        $content = '';
        $result .= self::menuWidgetItem($posts,$content);
      }
    }
    $result .= '</ul>';
    return $result;
  }

  public static function menuWidget(&$widget)
  {
    $toplevelposts = SimpleWebsiteTools::GetOrderedMenuItemsInMenuLevel(array('no_content' => '1','parent_id' => 'home'));
    if( $toplevelposts->isEmpty() )
      return;
    $levels = SimpleWebsiteTools::GetOrderedMenuItemsInMenuHierarchy(array('no_content' => '1'));
    $result = '<div>'.($widget->title ? '<h2>'.html::escapeHTML($widget->title).'</h2>' : '');
    $result .= self::menuWidgetHierarchy($levels,$toplevelposts);
    if($widget->sitemapOn)
      $result .= '<div class="swSitemap"><a href="'.SimpleWebsiteTools::GetSitemapURL().'">'.$widget->sitemapText.'</a></div>';
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