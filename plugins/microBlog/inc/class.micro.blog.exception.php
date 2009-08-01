<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

/**
 * Class that set specific Exception for the microBlog plugin
 *  
 * @author jeremie
 * @package microBlog
 */
class microBlogException extends Exception
{
	public function __construct($msg,$code)
	{
		parent::__construct($msg,$code);
	}
}