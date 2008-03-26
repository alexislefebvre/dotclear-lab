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

require_once($core->plugins->moduleRoot("simpleWebsite")."/define_urls.php");

class SimpleWebsiteExtMenuItemPost
{
	public static function getURL(&$rs)
	{
		return SimpleWebsiteTools::GetMenuItemURL($rs);
	}
}

class SimpleWebsiteSitemapPost
{
  public $post_title;
  
	function __construct() {
    $this->post_title = __('Sitemap');
  }
  
  public function isEmpty() { return true; }
}

class SimpleWebsiteTools
{
  public static function GetTemplateContent() {
    global $_ctx, $core;
    if($_ctx->exists('swCustomPostContent')) {
      return $core->tpl->getData($_ctx->swCustomPostContent);
    }
    $meta = new dcMeta($core);
    $templateFilename = $meta->getMetaStr($_ctx->posts->post_meta,"swTemplate");
    if (empty($templateFilename))
      return false;
    else
      return $core->tpl->getData($templateFilename);
  }

  public static function CurrentPost() {
    global $_ctx;
    return $_ctx->posts;
  }

  /*
      SELECT M.meta_id, T.post_id
      FROM dc_meta M
      INNER JOIN dc_meta N ON M.post_id=N.post_id
      INNER JOIN dc_meta T ON N.meta_id=T.meta_id
      WHERE M.meta_type='swMenuChain'
      AND N.meta_type='swMenuTag'
      AND T.meta_type='tag'
      AND T.post_id=10
    */
  public static function CurrentMenuHierarchy() {
    if(!self::CurrentPost())
      return false;
    if(self::CurrentPostId() == 'home')
      return false;
    global $core;
    // First check if current post is a menu item. If so, get its hierarchy.
    $meta = new dcMeta($core);
    $menuHierarchy = $meta->getMetaStr(self::CurrentPost()->post_meta,"swMenuChain");
    if($menuHierarchy)
      return explode(".",$menuHierarchy);
    // Else check if current post as a tag associated with a hierarchy. If so, get the associated hierarchy.
    $prefix = $core->blog->prefix;
    $menuHierarchyRs = $core->blog->con->select("SELECT M.meta_id FROM ".$prefix."meta M INNER JOIN ".$prefix."meta N ON M.post_id=N.post_id INNER JOIN ".$prefix."meta T ON N.meta_id=T.meta_id WHERE M.meta_type='swMenuChain' AND N.meta_type='swMenuTag' AND T.meta_type='tag' AND T.post_id=".self::CurrentPostId());
    if($menuHierarchyRs->fetch())
      return explode(".",$menuHierarchyRs->meta_id);
    // Else return false
    return false;
  }

  public static function CurrentEntriesLimit($attr) {
    global $_ctx, $_page_number;
		if (!isset($_page_number))
      $_page_number = 1;
  	$lastn = isset($attr['lastn']) ? abs((integer) $attr['lastn'])+0 : 0;
		$nb = ($lastn > 0) ? $lastn : $_ctx->nb_entry_per_page;
		return array((($_page_number-1)*$nb),$nb);
  }

  public static function CurrentPostId() {
    return (!self::CurrentPost() || self::CurrentPost()->isEmpty()) ? 'home' : self::CurrentPost()->post_id;
  }

  public static function CurrentPostIsMenuItem() {
    global $core, $_ctx;
    if( !$_ctx->posts || !$_ctx->posts->post_meta )
      return false;
    $meta = new dcMeta($core);
    return $meta->getMetaStr($_ctx->posts->post_meta,'swParentMenuItem') ? true : false;
  }

  public static function CurrentPostUrl() {
    return (!self::CurrentPost() || self::CurrentPost()->isEmpty()) ? '' : self::CurrentPost()->post_url;
  }

  public static function CurrentReferenceDate() {
    global $core, $_ctx;
    return $core->blog->con->escape( date('Y-m-d H:i:s',(integer) strtotime($_ctx->posts->post_dt)) );
  }

  public static function GetOrderedMenuItemsInMenuHierarchy($attr) {
    // compute list of items in hierarchy
    $menuHierarchy = self::CurrentMenuHierarchy();
    if( !$menuHierarchy )
      return false;
    
    // retrieve posts corresponding to computed list of hierarchy item id
    global $core;
    $menuHierarchyQuery = $menuHierarchy;
    array_walk($menuHierarchyQuery, create_function('&$value','$value = "post_id=".$value;'));
    $params = array();
    $params['sql'] = "AND (".implode(" OR ",$menuHierarchyQuery).")";
    $params['no_content'] = $attr['no_content'];
    $posts = $core->blog->getPosts( $params )->toStatic();
    
    // sort posts according to hierarchy order
    $h_orders = array_flip($menuHierarchy);
    while($posts->fetch()) {
      $posts->set("h_order",$h_orders[$posts->post_id]);
      $posts->set("core",$core);
    }
    $posts->sort("h_order");
    $posts->moveStart();
    $posts->extend("rsExtPost");
    $posts->extend("SimpleWebsiteExtMenuItemPost");
    
    return $posts;
  }

  public static function GetOrderedMenuItemsInMenuLevel($attr) {
    global $core;
    $prefix = $core->blog->prefix;
    if( isset($attr['parent_url']) ) {
      $parent_post = $core->blog->getPosts( array('post_url'=>$attr['parent_url']) );
      $root_id = $parent_post ? $parent_post->post_id : self::CurrentPostId();
    } else {
      $root_id = isset($attr['parent_id']) ? $attr['parent_id'] : self::CurrentPostId();
    }
    $params = array();
    $params['from'] = 'INNER JOIN '.$prefix.'meta M ON P.post_id=M.post_id';
    $params['sql'] = "AND M.meta_type='swParentMenuItem' AND M.meta_id='".$root_id."'";
    $params['order'] = 'P.post_url ASC';
    $params['no_content'] = $attr['no_content'];
    $posts = $core->blog->getPosts( $params );
    $posts->extend("SimpleWebsiteExtMenuItemPost");
    return $posts;
  }

  public static function AddMenuEntriesSelection(&$params,$attr,$comments)
	{
    global $core, $_ctx;
    $prefix = $core->blog->prefix;
    $root_id = isset($attr['parent_id']) ? $attr['parent_id'] : self::CurrentPostId();
    if( $root_id == 'home' ) {
      $params['sql'] .= " AND P.post_id NOT IN (SELECT post_id FROM ".$prefix."meta WHERE meta_type='swMenuChain')";
    } else {
      $params['from'] .= ' INNER JOIN '.$prefix.'meta M ON P.post_id=M.post_id';
      $params['from'] .= ' INNER JOIN '.$prefix.'meta T ON T.meta_id=M.meta_id';
      $params['from'] .= ' INNER JOIN '.$prefix.'meta H ON T.post_id=H.post_id';
      if($comments) {
        $params['sql'] .= " AND ( ( M.meta_type='tag' AND T.meta_type='swMenuTag'";
        $params['sql'] .= " AND H.meta_type='swMenuChain' AND H.meta_id REGEXP '^(.*\.)?".$root_id."(\..*)?$' )";
        $params['sql'] .= " OR (H.meta_type='swMenuChain' AND P.post_id=".$root_id.") )";
        $_ctx->posts = null;
      } else {
        $params['sql'] .= " AND M.meta_type='tag' AND T.meta_type='swMenuTag'";
        $params['sql'] .= " AND H.meta_type='swMenuChain' AND H.meta_id REGEXP '^(.*\.)?".$root_id."(\..*)?$'";
      }
    }
  }

	public function GetNextPost($attr,$dir)
	{
    global $core, $_ctx;
  
		$dt = date('Y-m-d H:i:s',(integer) strtotime($_ctx->posts->post_dt));
		$post_id = (integer) $_ctx->posts->post_id;
    $prefix = $core->blog->prefix;
		
		if($dir > 0) {
               $sign = '>';
               $order = 'ASC';
          }
          else {
               $sign = '<';
               $order = 'DESC';
          }
		
		
		$params['limit'] = 1;
		$params['order'] = 'post_dt '.$order.', P.post_id '.$order;
		$params['sql'] =
		'AND ( '.
		"	(post_dt = '".$core->con->escape($dt)."' AND P.post_id ".$sign." ".$post_id.") ".
		"	OR post_dt ".$sign." '".$core->con->escape($dt)."' ".
		') ';
    $params['sql'] .= " AND P.post_id NOT IN (SELECT post_id FROM ".$prefix."meta WHERE meta_type='swMenuChain')";

		$rs = $core->blog->getPosts($params);
		
		if ($rs->isEmpty())
      return false;

    $_ctx->posts = $rs;
    return true;
	}
  
  public static function GetMenuItemURL($post)
	{
    global $core;
    return $core->blog->url.$core->url->getBase(SIMPLEWEBSITE_SECTION).'/'.html::sanitizeURL($post->post_url);
  }
  
  public static function GetSitemapURL()
	{
    global $core;
    return $core->blog->url.$core->url->getBase(SIMPLEWEBSITE_SITEMAP);
  }
  
  public static function GetCurrentMenuFeedURL($attr)
	{
    global $core;
    $base = $core->blog->url.$core->url->getBase(SIMPLEWEBSITE_FEED);
    $currentUrl = self::CurrentPostUrl();
    if( empty($currentUrl) || !self::CurrentPostIsMenuItem() )
      return $base;
    
    // return comments feed url if requested
    $feedType = preg_match('#^(rss2|atom)$#',$attr['type']) ? $attr['type'] : 'rss2';
    if( $attr['comments'] )
      return $base.'/'.$currentUrl.'/'.$feedType.'/comments';
    
    // return saved URL value if it is available
    $meta = new dcMeta($core);
    $url = $meta->getMetaStr(self::CurrentPost()->post_meta,'swFeedURL');
    if( !empty($url) )
      return $url;
    
    // return default feed url for item
    return $base.'/'.$currentUrl.'/'.$feedType;
  }

}
?>