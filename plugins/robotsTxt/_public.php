<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of robotsTxt, a plugin for DotClear2.
# Copyright (c) 2008 William Dauchy and contributors. All rights
# reserved.
#
# This plugin is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This plugin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this plugin; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->addBehavior('publicBeforeDocument', array('tplRobotsTxt', 'addTemplatePath'));

$core->tpl->addBlock('robotsTxtSitemapUrlIf', array('tplRobotsTxt', 'robotsTxtSitemapUrlIf'));
$core->tpl->addValue('robotsTxtSitemapUrl', array('tplRobotsTxt', 'robotsTxtSitemapUrl'));
$core->tpl->addValue('robotsTxtRules', array('tplRobotsTxt', 'robotsTxtRules'));

class urlRobotsTxt extends dcUrlHandlers
{
  public static function robotsTxt($args)
  {
    global $core;

    if (!$core->blog->settings->robotstxt->robotstxt_active)
      {
	self::p404();
      }
   self::serveDocument('robots.txt', 'text/plain');
   exit;
  }
}

function displayRules($rules, $defaultRule)
{
  if (count($rules) == 0)
    {
      return ;
    }
  foreach ($rules as $key => $value)
    {
      echo "\n".'User-Agent: '.$key."\n";
      foreach ($value as $key1 => $value1)
      {
	foreach ($value1 as $key2 => $value2)
          {
	    echo $key1.': '.$key2."\n";
	  }
      }
      if ($defaultRule === true)
	{
	  echo 'Allow: /'."\n";
	}
      else
	{
	  echo 'Disallow: /'."\n";
	}
    }
}

class tplRobotsTxt
{
  public static function addTemplatePath($core)
  {
    $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
  }

  public static function robotsTxtSitemapUrlIf($attr, $content)
  {
    global $core;

    if ($core->blog->settings->robotstxt->robotstxt_sitemapUrlActive === true)
      {
	return $content;
      }
  }

  public static function robotsTxtSitemapUrl($attr)
  {
    return '<?php echo $core->blog->settings->robotstxt->robotstxt_sitemapUrl; ?>';
  }

  public static function robotsTxtRules($attr)
  {
    return
      '<?php $defaultRule = (boolean) $core->blog->settings->robotstxt->robotstxt_allowAllRobots;
             $rulesTmp = array();
             $rules = array();
             if (($rulesTmp = unserialize($core->blog->settings->robotstxt->robotstxt_rules)) !== false)
             {
               $rules = $rulesTmp;
             }
             displayRules($rules, $defaultRule);
       ?>';
  }
}
?>
