<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of databasespy, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload;

$__autoload['dbSpy'] = dirname(__FILE__).'/inc/class.dbspy.php';
$__autoload['libDbSpyPage'] = dirname(__FILE__).'/inc/lib.dbspy.page.php';

$__autoload['dbSpyDbMysql'] = dirname(__FILE__).'/inc/db/class.db.mysql.php';
$__autoload['dbSpyDbPgsql'] = dirname(__FILE__).'/inc/db/class.db.pgsql.php';
$__autoload['dbSpyDbVirtual'] = dirname(__FILE__).'/inc/db/class.db.virtual.php';

$__autoload['dbSpyExportCSV'] = dirname(__FILE__).'/inc/export/class.export.csv.php';
$__autoload['dbSpyExportPDF'] = dirname(__FILE__).'/inc/export/class.export.pdf.php';
$__autoload['dbSpyExportHTML'] = dirname(__FILE__).'/inc/export/class.export.html.php';
$__autoload['dbSpyExportSQL'] = dirname(__FILE__).'/inc/export/class.export.sql.php';
$__autoload['dbSpyExportXML'] = dirname(__FILE__).'/inc/export/class.export.xml.php';
?>