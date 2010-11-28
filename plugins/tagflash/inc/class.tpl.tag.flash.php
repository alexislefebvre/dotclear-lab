<?php
// +-----------------------------------------------------------------------+
// | tagFlash  - a plugin for Dotclear                                     |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2010 Nicolas Roudaire             http://www.nikrou.net  |
// | Copyright(C) 2010 Guenaël						   |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,            |
// | MA 02110-1301 USA.                                                    |
// +-----------------------------------------------------------------------+

class tplTagFlash
{
  private static $size_translator = array(0 => 0, 10 => 8, 20 => 10, 30 => 12, 40 => 14, 50 => 16,
					  60 => 18, 70 => 20, 80 => 22, 90 => 24, 100 => 26);

  public static function widget($w) {
    global $core, $_ctx;
    
    if (!$core->blog->settings->tagflash->active) {
      return;
    }

    $flash_url = html::stripHostURL($GLOBALS['core']->blog->getQmarkURL().'pf=tagflash/tagcloud.swf');
    $settings = $core->blog->settings->tagflash;

    $res = '';

    if ($w->title) {
      $res .= '<h2>'.$w->title.'</h2>';
    }

    $res .= sprintf('<object data="%s" width="%s" height="%s" type="application/x-shockwave-flash">',
		    $flash_url,
		    $settings->width,
		    $settings->height
		    );
		    
    $res .= sprintf('<param name="movie" value="%s"/>', $flash_url);
    $res .= '<param name="allowScriptAccess" value="sameDomain"/>';
    $res .= sprintf('<param name="flashvars" value="%s"/>',
		    self::tagFlashParams($w)
		    );
    $res .= '<param name="quality" value="high"/>'."\n";
    if ($w->transparent_mode) {
      $res .= '<param name="wmode" value="transparent"/>'."\n";
    } else {
      $res .= '<param name="bgcolor" value="#'.$settings->bgcolor.'"/>'."\n";
    }
    if ($w->with_seo_content) {
      $res .= '<div id="tagFlashContent">'.self::getTags($w, false).'</div>'."\n";    
    } else {
      $res .= '<p>'. __('This will be shown to users with no Flash plugin.').'</p>';
    }
    $res .= '</object>'."\n";
    $res .= '<p><a href="'.$core->blog->url.$core->url->getBase('tags').'">'.__('All tags').'</a></p>';

    return $res;
  }

  public static function tagFlashParams($w) {
    global $core;

    $res = '';
    $res .= 'mode=tags';
    $res .= '&tcolor=0x'.$core->blog->settings->tagflash->color1;
    $res .= '&tcolor2=0x'.$core->blog->settings->tagflash->color2;
    $res .= '&hicolor=0x'.$core->blog->settings->tagflash->hicolor;
    $res .= '&tspeed='.$core->blog->settings->tagflash->speed;
    $res .= '&distr=true';
    $res .= '&tagcloud='.self::getTags($w);

    return $res;
  }

  public static function getTags($w, $for_flash=true) {
    global $core;
      
    $limit = null;
    if ($w->limit && is_numeric($w->limit)) {
      $limit = $w->limit;
    }
    
    $rs = $core->meta->computeMetaStats(
      $core->meta->getMetadata(array('meta_type'=> 'tag',
				     'limit'=> $limit)
			       )
					); 
    $res = '';
    if ($for_flash) {
      $fmt_tag = "<a href='%s' rel='tag' style='font-size:%spt'>%s</a>";
    } else {
      $fmt_tag = '<li><a href="%s" class="tag%s" rel="tag">%s</a></li>';
    }
    
    if (!$rs->isEmpty()) {
      while ($rs->fetch()) {
	$res .= sprintf($fmt_tag, 
			$core->blog->url.$core->url->getBase('tag').'/'.urlencode(utf8_encode($rs->meta_id)),
			self::$size_translator[$rs->roundpercent],
			$rs->meta_id
			);
      }
    }

    if ($for_flash) {
      $res = '<tags>'.$res.'</tags>';
    } else {
      $res = '<ul>'.$res.'</ul>';
    }
    
    return $res;
  }
}
?>