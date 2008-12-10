<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of acronyms, a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcAcronyms
{
	private $core;

	public $dir;
	public $file;

	public function __construct(dcCore $core)
	{
		$this->core = $core;

		$this->dir = $core->blog->public_path.'/acronyms/';
		$this->file = $core->blog->public_path.'/acronyms/'.$this->core->blog->id.'.txt';

		$this->handleFile();
	}

	/**
	 * Check if acronyms file exists and create it if not
	 *
	 */
	public function handleFile()
	{
		if (!file_exists($this->file))
		{
			if (!is_dir($this->dir)) {
				files::makeDir($this->dir,true);
			}

			files::putContent($this->file, file_get_contents(dirname(__FILE__).'/wiki-acronyms.txt'));
		}
	}

	/**
	 * Write the acronyms file
	 *
	 * @param array $acronyms_list
	 */
	public function writeFile($acronyms_list)
	{
		$neocontent = '';
		foreach ($acronyms_list as $nk=>$nv) {
			$neocontent .= (string)$nk."\t\t : ".(string)$nv."\n";
		}

		files::putContent($this->file, $neocontent);
	}

	/**
	 * Return the acronyms list
	 *
	 * @return array
	 */
	public function getList()
	{
		$acronyms_list = array();
		if (($fc = @file($this->file)) !== false)
		{
			foreach ($fc as $v)
			{
				$v = trim($v);
				if ($v != '')
				{
					$p = strpos($v,':');
					$K = (string) trim(substr($v,0,$p));
					$V = (string) trim(substr($v,($p+1)));
		
					if ($K) {
						$acronyms_list[$K] = $V;
					}
				}
			}
		}
		else {
			$this->core->error->add(sprintf(__('Unable to read the %s file'), $this->file));
			return false;
		}
		
		return $acronyms_list;
	}
	
} # class
