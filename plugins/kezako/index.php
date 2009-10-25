<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kezako, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jean-Christophe Dubacq
# jcdubacq1@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
$realnames=array(
                 'metadata-tag' => __('Tags'),
                 'category-cat' => __('Categories'),
                 'somethingelse' => __('Something else')
                 );

if (isset($_GET['edit'])) {
    include dirname(__FILE__)."/edit.php";
 } else {
    include dirname(__FILE__)."/list.php";
 }
?>