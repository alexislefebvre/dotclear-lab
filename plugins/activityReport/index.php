<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of activityReport, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

if (!$core->activityReport instanceof activityReport){return;}

dcPage::check('admin');

require_once dirname(__FILE__).'/inc/lib.activity.report.index.php';

$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'blog_settings';

?>
<html>
 <head>
  <title><?php echo __('Activity report'); ?></title>
<?php 
echo 
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsToolBar().
dcPage::jsPageTabs($tab);
?>
 </head>
<body>
<h2><?php 
 echo html::escapeHTML($core->blog->name).
 ' &rsaquo; '.__('Activity report');
?></h2>

<?php
activityReportLib::settingTab($core,__('Settings'));
activityReportLib::logTab($core,__('Logs'));

if ($core->auth->isSuperAdmin())
{
	activityReportLib::settingTab($core,__('Super settings'),true);
	activityReportLib::logTab($core,__('Super logs'),true);
}

?>

<hr class="clear"/>
<p class="right">
activityReport - 
<?php echo $core->plugins->moduleInfo('activityReport','version'); ?>&nbsp;
<img alt="activityReport" src="index.php?pf=activityReport/icon.png" />
</p>
</body>
</html>