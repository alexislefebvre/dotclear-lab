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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
$core->blog->settings->addNameSpace('robotstxt');

if (is_null($core->blog->settings->robotstxt->robotstxt_active))
{
  try
    {
      $core->blog->settings->addNameSpace('robotstxt');
      $core->blog->settings->robotstxt->put('robotstxt_active', false, 'boolean', 'Active plugin');
      $core->blog->settings->robotstxt->put('robotstxt_allowAllRobots', true, 'boolean', 'Allow all robots');
      $core->blog->settings->robotstxt->put('robotstxt_sitemapUrlActive', false, 'boolean', 'Active sitemap url');
      $core->blog->settings->robotstxt->put('robotstxt_sitemapUrl', $core->blog->url.'sitemap.xml', 'string', 'Sitemap url');
      $core->blog->settings->robotstxt->put('robotstxt_rules', NULL, 'string', 'robotsTxt rules');
      $core->blog->triggerBlog();
      http::redirect(http::getSelfURI());
    }
  catch (Exception $e)
    {
      $core->error->add($e->getMessage());
    }
}

$active = (boolean) $core->blog->settings->robotstxt->robotstxt_active;
$defaultRule = (boolean) $core->blog->settings->robotstxt->robotstxt_allowAllRobots;
$sitemapUrlActive = (boolean) $core->blog->settings->robotstxt->robotstxt_sitemapUrlActive;
$sitemapUrl = (string) $core->blog->settings->robotstxt->robotstxt_sitemapUrl;
$rulesTmp = array();
$rules = array();
if (($rulesTmp = unserialize($core->blog->settings->robotstxt->robotstxt_rules)) !== false)
{
  $rules = $rulesTmp;
}

function displayRobotsTxt($rules, $sitemapUrl, $defaultRule, $sitemapUrlActive)
{
  if ($sitemapUrlActive === true)
    {
      echo 'Sitemap: '.$sitemapUrl.'<br />';
    }
  if (count($rules) == 0)
    {
      return ;
    }
  $ikey = 0;
  foreach ($rules as $key => $value)
    {
      echo '<br />User-Agent: '.$key.'&nbsp;&nbsp;&nbsp;&nbsp;(<a title="Delete user-agent and all connected rules" href="plugin.php?p=robotsTxt&amp;deleteUA='.$ikey.'">delete</a>)<br />';
      $ikey2 = 0;
      foreach ($value as $key1 => $value1)
	{
	  foreach ($value1 as $key2 => $value2)
	    {
	      echo $key1.': '.$key2.'&nbsp;&nbsp;&nbsp;&nbsp;(<a title="Delete rule" href="plugin.php?p=robotsTxt&amp;deleteUA='.$ikey.'&amp;deleteRule='.$ikey2++.'">delete</a>)<br />';
	    }
	}
      if ($defaultRule === true)
	{
	  echo 'Allow: /<br />';
	}
      else
	{
	  echo 'Disallow: /<br />';
	}
      $ikey++;
    }
}

function deleteRule($rules, $iDeleteUA, $iDeleteRule)
{
  global $core;

  $ikey = 0;
  foreach ($rules as $key => $value)
    {
      if ($iDeleteRule != -1 && $ikey == $iDeleteUA)
	{
	  $ikey2 = 0;
	  foreach ($value as $key1 => $value1)
	    {
	      foreach ($value1 as $key2 => $value2)
		{
		  if ($ikey2 == $iDeleteRule)
		    {
		      unset($rules[$key][$key1][$key2]);
		      $core->blog->settings->robotstxt->put('robotstxt_rules', serialize($rules), 'string');
		      return ;
		    }
		  $ikey2++;
		}
	    }
	}
      else if ($ikey == $iDeleteUA)
	{
	  unset($rules[$key]);
	  $core->blog->settings->robotstxt->put('robotstxt_rules', serialize($rules), 'string');
	  return ;
	}
      $ikey++;
    }
}

if (isSet($_REQUEST['deleteUA']) && count($rules) != 0)
{
  if (isSet($_REQUEST['deleteRule']))
    {
      deleteRule($rules, $_REQUEST['deleteUA'], $_REQUEST['deleteRule']);
    }
  else
    {
      deleteRule($rules, $_REQUEST['deleteUA'], -1);
    }
}

if (isSet($_REQUEST['addRule']))
{
  $userAgent = (empty($_REQUEST['userAgent'])) ? '*' : $_REQUEST['userAgent'];
  $ruleAction = ($_REQUEST['ruleAction'] == 'allow') ? 'Allow' : 'Disallow';
  $actionValue = (empty($_REQUEST['actionValue'])) ? '/' : $_REQUEST['actionValue'];
  $rules[$userAgent][$ruleAction][$actionValue] = 1;
  $core->blog->settings->robotstxt->put('robotstxt_rules', serialize($rules), 'string');
  displayRobotsTxt($rules, $sitemapUrl, $defaultRule, $sitemapUrlActive);
  exit;
}

if (isSet($_REQUEST['saveSitemapUrl']))
{
  $sitemapUrl = (empty($_REQUEST['sitemapUrl'])) ? '' : $_REQUEST['sitemapUrl'];
  $core->blog->settings->robotstxt->put('robotstxt_sitemapUrl', $sitemapUrl, 'string');
  displayRobotsTxt($rules, $sitemapUrl, $defaultRule, $sitemapUrlActive);
  exit;
}

if (isSet($_REQUEST['active']))
{
  $active = (empty($_REQUEST['active'])) ? false : true;
  $core->blog->settings->robotstxt->put('robotstxt_active', $active, 'boolean');
  if ($active == true)
    {
      displayRobotsTxt($rules, $sitemapUrl, $defaultRule, $sitemapUrlActive);
    }
  else
    {
      echo __('robotsTxt is now unactive');
    }
  exit;
}

if (isSet($_REQUEST['sitemapUrlActive']))
{
  $sitemapUrlActive = ($_REQUEST['sitemapUrlActive'] == 0) ? false : true;
  $core->blog->settings->robotstxt->put('robotstxt_sitemapUrlActive', $sitemapUrlActive, 'boolean');
  displayRobotsTxt($rules, $sitemapUrl, $defaultRule, $sitemapUrlActive);
  exit;
}

if (isSet($_REQUEST['allow']))
{
  $defaultRule = ($_REQUEST['allow'] == 0) ? false : true;
  $core->blog->settings->robotstxt->put('robotstxt_allowAllRobots', $defaultRule, 'boolean');
  displayRobotsTxt($rules, $sitemapUrl, $defaultRule, $sitemapUrlActive);
  exit;
}

?>
<html>
  <head>
    <title><?php echo __('robotsTxt'); ?></title>
    <script type="text/javascript" charset="utf-8">
    $(function() {
      $('#addRule').submit(function() {
	var inputs = [];
	inputs.push('p' + '=' + escape('robotsTxt'));
	$(':input', this).each(function() {
	  inputs.push(this.name + '=' + escape(this.value));
	})
	jQuery.ajax({
            data: inputs.join('&'),
	    url: this.action,
            timeout: 3000,
            error: function() {
              console.log("Failed to submit");
            },
            success: function(data) {
	      $("div.contentToChange").find("p").html(data);
	    }
	})
	return false;
      })

      $('#saveSitemapUrl').submit(function() {
	var inputs = [];
	inputs.push('p' + '=' + escape('robotsTxt'));
	$(':input', this).each(function() {
	  inputs.push(this.name + '=' + escape(this.value));
	})
	jQuery.ajax({
            data: inputs.join('&'),
	    url: this.action,
            timeout: 3000,
            error: function() {
              console.log("Failed to submit");
            },
            success: function(data) {
	      $("div.contentToChange").find("p").html(data);
	    }
	})
	return false;
      })

      $('#active').click(function() {
	var inputs = [];
	if ($('#active').is(':checked'))
	{
	  inputs.push('active' + '=' + escape('1'));
	}
	else
	{
	  inputs.push('active' + '=' + escape('0'));
	}
	jQuery.ajax({
            data: inputs.join('&'),
	    url: window.location.href,
            timeout: 3000,
            error: function() {
              console.log("Failed to submit");
            },
            success: function(data) {
	      $("div.contentToChange").find("p").html(data);
	    }
	})
	return true;
      })

      $('#allowAll').click(function() {
	var inputs = [];
	if ($('#allowAll').is(':checked'))
	{
	  inputs.push('allow' + '=' + escape('1'));
	}
	jQuery.ajax({
            data: inputs.join('&'),
	    url: window.location.href,
            timeout: 3000,
            error: function() {
              console.log("Failed to submit");
            },
            success: function(data) {
	      $("div.contentToChange").find("p").html(data);
	    }
	})
	return true;
      })

      $('#disallowAll').click(function() {
	var inputs = [];
	if ($('#disallowAll').is(':checked'))
	{
	  inputs.push('allow' + '=' + escape('0'));
	}
	jQuery.ajax({
            data: inputs.join('&'),
	    url: window.location.href,
            timeout: 3000,
            error: function() {
              console.log("Failed to submit");
            },
            success: function(data) {
	      $("div.contentToChange").find("p").html(data);
	    }
	})
	return true;
      })

      $('#sitemapUrlActive').click(function() {
	var inputs = [];
	if ($('#sitemapUrlActive').is(':checked'))
	{
	  inputs.push('sitemapUrlActive' + '=' + escape('1'));
	}
	else
	{
	  inputs.push('sitemapUrlActive' + '=' + escape('0'));
	}
	jQuery.ajax({
            data: inputs.join('&'),
	    url: window.location.href,
            timeout: 3000,
            error: function() {
              console.log("Failed to submit");
            },
            success: function(data) {
	      $("div.contentToChange").find("p").html(data);
	    }
	})
	return true;
      })
    })
    </script>
  </head>
  <body>
    <h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('robotsTxt'); ?></h2>
    <div>
      <fieldset>
        <legend><?php echo __('Plugin activation'); ?></legend>
        <p class="field">
	  <?php echo form::checkbox('active', 1, $active); ?>
	  <label for="active">&nbsp;<?php echo __('Enable robotsTxt');?></label>
        </p>
      </fieldset>
      <fieldset>
        <legend><?php echo(__('General')); ?></legend>
	<table class="maximal">
	  <tbody>
            <tr>
	      <td>
	        <?php echo form::radio(array('defaultRule', 'allowAll'), 'allow', ($defaultRule == true ? true : false)); ?>
                <label for="allowAll" class="classic"><?php echo(__('Allow all robots (recommended)')); ?></label>
	      </td>
	      <td>
	        <?php echo form::radio(array('defaultRule', 'disallowAll'), 'disallow', ($defaultRule == true ? false : true)); ?>
		<label for="disallowAll" class="classic"><?php echo(__('Block all robots')); ?></label>
	      </td>
	    </tr>
	    <tr>
	      <td>
	        <label for="sitemapUrlActive" class="classic">
		  <?php echo form::checkbox('sitemapUrlActive', 1, ${'sitemapUrlActive'});?>
		  &nbsp;<?php echo __('Sitemap url'); ?>
	        </label>
	      </td>
	      <td>
		<form method="get" action="<?php http::getSelfURI(); ?>" id="saveSitemapUrl">
	          <label for="sitemapUrl" class="classic">
		    <?php echo form::field('sitemapUrl', 40, 128, $sitemapUrl); ?>
	          </label>
		  <?php echo $core->formNonce(); ?>
		  <input type="submit" name="saveSitemapUrl" value="<?php echo __('Save sitemap url'); ?>" />
		</form>
	      </td>
	    </tr>
	  </tbody>
        </table>
      </fieldset>
      <form method="get" action="<?php http::getSelfURI(); ?>" id="addRule">
	<fieldset>
          <legend><?php echo(__('Rule')); ?></legend>
          <p class="field">
            <label for="userAgent" class="classic"><?php echo(__('User-agent:')); ?></label>
	    <?php echo form::field('userAgent', 40, 128, '*'); ?>
	  </p>
	  <p class="field">
	    <label for="ruleAction" class="classic"><?php echo(__('Action:')); ?></label>
	    <?php echo form::combo('ruleAction', array(__('Allow') => 'allow', __('Disallow') => 'disallow')); ?>
          </p>
          <p class="field">
            <label for="actionValue" class="classic"><?php echo(__('Action value:')); ?></label>
	    <?php echo form::field('actionValue', 40, 128, ''); ?>
          </p>
          <p>
            <?php echo $core->formNonce(); ?>
            <input type="submit" name="addRule" value="<?php echo __('Add rule'); ?>" />
          </p>
        </fieldset>
      </form>
      <fieldset>
        <legend><?php echo(__('Result')); ?></legend>
	<div class="contentToChange">
        <p><?php displayRobotsTxt($rules, $sitemapUrl, $defaultRule, $sitemapUrlActive); ?></p>
      </div>
      </fieldset>
    </div>
    <div id="help" title="<?php echo __('Help'); ?>">
      <div class="help-content">
        <h2><?php echo(__('Help')); ?></h2>
	<p><?php echo(__('If the plugin is activated, you will be able to see the result at http://yourdomain/robots.txt')); ?></p>
	<p><?php echo(__('You could see differences between your configuration and the result. To resolve it, erase the Dotclear cache.')); ?></p>
	<p><?php printf(__('More about robots.txt on %s.'),'<a title="robotstxt.org" href="http://www.robotstxt.org/">robotstxt.org</a>'); ?></p>
      </div>
    </div>
  </body>
</html>
