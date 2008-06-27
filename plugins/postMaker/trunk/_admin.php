<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2008 Olivier Azeau and contributors. All rights
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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$__autoload['PostMakerSettings'] = dirname(__FILE__).'/PostMakerSettings.php';

$postMakerSettings = new PostMakerSettings();
foreach( $postMakerSettings->values as $name => $entry )
{
  $_menu['Blog']->addItem(
    __('New entry').' "'.$name.'"', // title
    'post.php?customEntry='.urlencode($name), // url
    'images/menu/edit.png',  // img
    preg_match('/post.php(.*)$/',$_SERVER['REQUEST_URI']), // active
    $core->auth->check('usage,contentadmin',$core->blog->id) // show
  );
}

$_menu['Plugins']->addItem(
  'Post Maker', // title
  'plugin.php?p=postMaker', // url
  'index.php?pf=postMaker/icon.png',  // img
  preg_match('/plugin.php\?p=postMaker/',$_SERVER['REQUEST_URI']), // active
  $core->auth->check('usage,contentadmin',$core->blog->id) // show
);

$core->addBehavior('adminPostFormSidebar',array('PostMaker','FillPostContent'));

define('DC_POST_MAKER_TPL_FOLDER',dirname(__FILE__).'/templates');
define('DC_POST_MAKER_TPL_CACHE',DC_TPL_CACHE.'/cbtpl/postMaker');

class PostMaker
{
  public static function FillPostContent(&$post)
  {
    global $post_content, $post_format, $core;
    if( !empty($post_content ) )
      return;
    if( !isset($_REQUEST['customEntry']) )
      return;
      
    if (!is_dir(DC_POST_MAKER_TPL_CACHE))
			mkdir(DC_POST_MAKER_TPL_CACHE);
    $core->postMakerTpl = new template(DC_POST_MAKER_TPL_CACHE,'$core->postMakerTpl');
    $core->postMakerTpl->setPath( DC_POST_MAKER_TPL_FOLDER );
    $core->postMakerTpl->addBlock('EntryTitle',array('PostMaker','EntryTitle'));
    $core->postMakerTpl->addBlock('EntryExcerpt',array('PostMaker','EntryExcerpt'));
    $core->postMakerTpl->addBlock('EntryContent',array('PostMaker','EntryContent'));
    $core->postMakerTpl->addValue('FeedURL',array('PostMaker','FeedURL'));
    $core->postMakerTpl->addValue('FeedProperty',array('PostMaker','FeedProperty'));
    $core->postMakerTpl->addBlock('FeedItems',array('PostMaker','FeedItems'));
    $core->postMakerTpl->addValue('FeedItemProperty',array('PostMaker','FeedItemProperty'));
    $core->postMakerTpl->addValue('FeedItemDate',array('PostMaker','FeedItemDate'));
    $core->postMakerTpl->addValue('FeedItemTime',array('PostMaker','FeedItemTime'));
    
    $templateTypes = array('xhtml'=>'hentry','wiki'=>'wentry');
    $templateType = $templateTypes[$post_format];
    
    $settings = new PostMakerSettings();
    $entry = $settings->values[$_REQUEST['customEntry']];
    
    global $postMakerHook;
    $postMakerHook = new stdClass();
		$postMakerHook->url = $entry->feed;
		$postMakerHook->feed = feedReader::quickParse($entry->feed,DC_POST_MAKER_TPL_CACHE);
    
    global $post_title, $post_excerpt, $core;
    ob_start();
		include $core->postMakerTpl->getFile($entry->TemplateFile());
		ob_end_clean();
  }

  public static function EntryTitle($attr,$content)
  {
    return '<?php
    ob_start();
    ?>'.
    $content.
    '<?php $post_title = ob_get_contents(); ob_end_clean(); ?>';
  }

  public static function EntryExcerpt($attr,$content)
  {
    return '<?php
    ob_start();
    ?>'.
    $content.
    '<?php $post_excerpt = ob_get_contents(); ob_end_clean(); ?>';
  }

  public static function EntryContent($attr,$content)
  {
    return '<?php
    ob_start();
    ?>'.
    $content.
    '<?php $post_content = ob_get_contents(); ob_end_clean(); ?>';
  }

  public static function FeedURL($attr)
  {
    return '<?php print $postMakerHook->url; ?>';
  }

  public static function FeedProperty($attr)
  {
    if( !isset($attr['name']) )
      return '';
    return '<?php print $postMakerHook->feed->'.$attr['name'].'; ?>';
  }

  public static function FeedItems($attr,$content)
  {
    return '<?php
    foreach ($postMakerHook->feed->items as $postMakerHook->item) :
    ?>'.
    $content.
    '<?php endforeach; unset($postMakerHook->item); ?>';
  }

  public static function FeedItemProperty($attr)
  {
    if( !isset($attr['name']) )
      return '';
    return '<?php print $postMakerHook->item->'.$attr['name'].'; ?>';
  }

  public static function FeedItemDate($attr)
  {
    return '<?php print dt::str($core->blog->settings->date_format,$postMakerHook->item->TS); ?>';
  }

  public static function FeedItemTime($attr)
  {
    return '<?php print dt::str($core->blog->settings->time_format,$postMakerHook->item->TS); ?>';
  }
}
?>