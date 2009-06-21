<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of odt, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

# This is a wrapper class to handle ODT XML specific cases

class odtTemplate extends dcTemplate
{
	protected $odf;

	function __construct($cache_dir,$self_name,&$core, $odf)
	{
		parent::__construct($cache_dir,$self_name,$core);
		$this->tpl_path = $core->tpl->tpl_path;
		$this->blocks = $core->tpl->blocks;
		$this->values = $core->tpl->values;
		$this->odf = $odf;
	}

	protected function compileFile($file)
	{
		$fc = $this->odf->getContentXml();
		
		$this->compile_stack[] = $file;
		
		# Remove every PHP tags
		if ($this->remove_php)
		{
			$fc = preg_replace('/<\?(?=php|=|\s).*?\?>/ms','',$fc);
		}
		
		# Replace escaped tags for blocks
		$fc = preg_replace('#&lt;(/?)tpl:(.+?)&gt;#ms','<\1tpl:\2>',$fc);
		$fc = preg_replace('#(tpl:\w+ \w+=)&quot;(.+?)&quot;#ms','\1"\2"',$fc);
		$fc = preg_replace('#xlink:href="[^"]*%7B%7Btpl:(\w+)%7D%7D"#ms','xlink:href="{{tpl:\1}}"',$fc);
		
		# Transform what could be considered as PHP short tags
		$fc = preg_replace('/(<\?(?!php|=|\s))(.*?)(\?>)/ms',
		'<?php echo "$1"; ?>$2<?php echo "$3"; ?>',$fc);
		
		# Remove template comments <!-- #... -->
		$fc = preg_replace('/(^\s*)?<!-- #(.*?)-->/ms','',$fc);
		
		# Compile blocks
		foreach ($this->blocks as $b => $f) {
			$pattern = sprintf($this->tag_block,preg_quote($b,'#'));
			
			$fc = preg_replace_callback('#'.$pattern.'#ms',
			array($this,'compileBlock'),$fc);
		}
		
		# Compile values
		foreach ($this->values as $v => $f) {
			$pattern = sprintf($this->tag_value,preg_quote($v,'#'));
			
			$fc = preg_replace_callback('#'.$pattern.'#ms',
			array($this,'compileValue'),$fc);
		}
		
		return $fc;
	}
	
}
?>
