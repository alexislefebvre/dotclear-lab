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

$core->addBehavior('adminPostFormSidebar',array('SimpleWebsite','displayForm'));
$core->addBehavior('adminAfterPostCreate',array('SimpleWebsite','saveAllPostMeta'));
$core->addBehavior('adminAfterPostUpdate',array('SimpleWebsite','saveAllPostMeta'));
$core->addBehavior('initWidgets',array('SimpleWebsite','initWidgets'));

class SimpleWebsite
{
  // returns the parent id in menu hierarchy for a given post ; returns 'none' when post is not in menu hierarchy ; returns 'home' when post is at the top level of the menu hierarchy
  public static function getParentMenuItem(&$meta,&$post)
  {
    $pmi = ($post) ? $meta->getMetaStr($post->post_meta,'swParentMenuItem') : '';
    return ($pmi) ? $pmi : 'none';
  }

  // returns the menu tag for a given post ; returns blank when post has no associated tag
  public static function getMenuTag(&$meta,&$post)
  {
    return ($post) ? $meta->getMetaStr($post->post_meta,'swMenuTag') : '';
  }

  // returns the template file name for a given post ; returns blank when post using the default template
  public static function getTemplateFilename(&$meta,&$post)
  {
    return ($post) ? $meta->getMetaStr($post->post_meta,'swTemplate') : '';
  }

  // returns the custom feed URL for a given post ; returns blank when post using the default feed
  public static function getFeedURL(&$meta,&$post)
  {
    return ($post) ? $meta->getMetaStr($post->post_meta,'swFeedURL') : '';
  }
  
  // creates a menu hierarchy <li> tag for post sidebar corresponding to a given post and its subitems
  public static function displayMenuItemInHierarchy($id,$title,$currentParent,$isSelectable,$inside)
  {
    $checked = '';
    if( $currentParent == $id ) {
      $checked = ' checked="1"';
      $title = '<b>'.$title.'</b>';
    }
    if( $isSelectable )
      return '<li style="list-style: none;"><input type="radio" name="swParentMenuItem" value="'.$id.'" '.$checked.'/> '.$title.$inside.'</li>';
    else
      return '<li style="list-style: inside square;">'.$title.$inside.'</li>';
  }
  
  // creates a menu hierarchy <ul> tag for post sidebar corresponding to a given menu level (identified by its parent) and its content
  public static function displayMenuHierarchy(&$items,$parent_id,$currentParent,$currentPost,$isSelectable)
  {
    $html = '<ul style="padding-left: 20px;">';
    foreach($items as $item) {
      if($item['parent_id'] == $parent_id) {
        $itemIsSelectable = $isSelectable && ($item['id'] != $currentPost);
        $html .= self::displayMenuItemInHierarchy( $item['id'], $item['title'], $currentParent, $itemIsSelectable, self::displayMenuHierarchy(&$items,$item['id'],$currentParent,$currentPost,$itemIsSelectable) );
      }
    }
    $html .= '</ul>';
    return $html;
  }
  
  // behaviour for displaying 'simple website' form in post sidebar
  public static function displayForm(&$post)
  {
    $core = $GLOBALS['core'];
    $blog = &$core->blog;
    $meta = new dcMeta($core);
    $currentParent = self::getParentMenuItem($meta,$post);
    $imgSrcs = array('none'=>'images/plus.png','block'=>'images/minus.png');
    $blockDisplay = ( $currentParent == 'none' ) ? 'none' : 'block';
    
    // init menu view with parent selection
    $allMenuItems = "SELECT P.post_title title, P.post_id id, P.post_url url, M.meta_id parent_id FROM ".$blog->prefix."post P, ".$blog->prefix."meta M WHERE P.post_id = M.post_id AND M.meta_type='swParentMenuItem' ORDER BY P.post_url";
    $menuView = '<ul style="padding-left: 5px;">';
    $menuView .= self::displayMenuItemInHierarchy( 'none', __('None'), $currentParent, true, '' );
    $menuView .= self::displayMenuItemInHierarchy( 'home', __('Home'), $currentParent, true, self::displayMenuHierarchy($blog->con->select($allMenuItems)->rows(),'home',$currentParent,$post->post_id,true) );
    $menuView .= '</ul>';
?>
    <script>
      function swShowHide(swForm) {
        swFormImg = swForm.childNodes[1];
        swFormBlock = swForm.childNodes[4];
        if( swFormBlock.style.display == 'none' ) {
          swFormBlock.style.display = 'block';
          swFormImg.src = '<?php echo $imgSrcs['block']; ?>';
        } else {
          swFormBlock.style.display = 'none';
          swFormImg.src = '<?php echo $imgSrcs['none']; ?>';
        }
      }
    </script>
    <div id="SimpleWebsiteForm">
      <img style="cursor: pointer;" alt="" src="<?php echo $imgSrcs[$blockDisplay]; ?>" onclick="javascript:swShowHide(parentNode);"><b> <?php echo __('Simple Website')?></b>
      <div style="border: 1px solid rgb(204, 204, 204); padding: 3px; display: <?php echo $blockDisplay; ?>;">
        <p/>
        <p><?php echo __('Parent Menu Item:').$menuView; ?></p>
        <p><?php echo __('Menu Tag:').form::field('swMenuTag', 30, 100, self::getMenuTag($meta,$post)); ?></p>
        <p><?php echo __('Template file name:').form::field('swTemplate', 30, 100, self::getTemplateFilename($meta,$post)); ?></p>
        <p class="form-note warn"><?php echo __('Leave blank for default template')?></p>
        <p><?php echo __('Feed URL:').form::field('swFeedURL', 30, 100, self::getFeedURL($meta,$post)); ?></p>
        <p class="form-note warn"><?php echo __('Leave blank for default feed URL')?></p>
      </div>
    </div>
<?php
  }
  
  // save a single meta information for the given post
  public static function savePostMeta(&$meta,&$post_id,$meta_type,&$oldValue,$newValueKey,$defaultValue)
  {
    $meta->delPostMeta($post_id,$meta_type);
    $newValue = isset($_REQUEST[$newValueKey]) ? $_REQUEST[$newValueKey] : $oldValue;
    if( $newValue != $defaultValue )
      $meta->setPostMeta($post_id, $meta_type, $newValue);
    return $newValue;
  }
  
  // compute the menu chain (= the list of all ancestor including self in menu hierarchy separated by dot characters) for a given post
  public static function computeMenuChain(&$blog,&$parent_post_id,&$post_id)
  {
    if($parent_post_id == 'none')
      return '';
    if($parent_post_id == 'home')
      return $post_id;
    $parent_post = $blog->con->select("SELECT M.meta_id FROM ".$blog->prefix."meta M WHERE M.post_id = ".$parent_post_id." AND M.meta_type='swMenuChain'");
    $parent_menu_chain = $parent_post->meta_id;
    return $parent_menu_chain.'.'.$post_id;
  }
  
  // saves the menu chain for a given post and all its descendants in the menu hierarchy
  public static function saveMenuChain(&$meta,&$blog,&$post_id,&$menu_chain)
  {
    $meta->delPostMeta($post_id, 'swMenuChain');
    if( !empty($menu_chain) )
      $meta->setPostMeta($post_id, 'swMenuChain', $menu_chain);
    $children_posts = $blog->con->select("SELECT M.post_id FROM ".$blog->prefix."meta M WHERE M.meta_id = ".$post_id." AND M.meta_type='swParentMenuItem'");
    foreach( $children_posts->rows() as $child_post ) {
      $child_post_id = $child_post['post_id'];
      $child_menu_chain = empty($menu_chain) ? $menu_chain : $menu_chain.'.'.$child_post_id;
      self::saveMenuChain($meta,$blog,$child_post_id,$child_menu_chain);
    }
  }
  
  // behaviour for creating/updating all information after post submit
  public static function saveAllPostMeta(&$cur,&$post_id)
  {
    $post_id = (integer) $post_id;
    $core = $GLOBALS['core'];
    $blog = &$core->blog;
    $meta = new dcMeta($core);
    $post = $blog->getPosts(array('post_id' => $post_id));
    
    // save parent item
    $parent_post_id = self::savePostMeta($meta, $post_id, 'swParentMenuItem', self::getParentMenuItem($meta,$post), 'swParentMenuItem', 'none');
    
    // save  menu chain
    self::saveMenuChain($meta,$blog,$post_id,self::computeMenuChain(&$blog,&$parent_post_id,&$post_id));

    // save menu tag
    self::savePostMeta($meta, $post_id, 'swMenuTag', self::getMenuTag($meta,$post), 'swMenuTag', '');

    // save template file name
    self::savePostMeta($meta, $post_id, 'swTemplate', self::getTemplateFilename($meta,$post), 'swTemplate', '');

    // save feed URL
    self::savePostMeta($meta, $post_id, 'swFeedURL', self::getFeedURL($meta,$post), 'swFeedURL', '');
  }
  
  // behaviour for widget initialization
  public static function initWidgets(&$widgets)
  {
    $widgets->create('swMenu',__('Simple Website Menu'),array('SimpleWebsiteTemplates','menuWidget'));
    $widgets->swMenu->setting('title',__('Title:'),__('Menu'));
    $widgets->swMenu->setting('sitemapOn',__('Display Sitemap'),1,'check');				
    $widgets->swMenu->setting('sitemapText',__('Sitemap Text:'),__('Sitemap'));
  }
}

?>