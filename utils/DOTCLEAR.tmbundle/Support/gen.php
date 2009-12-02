#!/usr/bin php
<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of TextMate Bundle for Dotclear 2.
#
# Copyright (c) 2009 Thomas Bouron
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

print "[START]\n";

if (isset($argv[1]) && preg_match('/~?(\/[^\/]+)\/?/',$argv[1])) {
	$path = $argv[1];
	gen_behaviors($path);
	gen_constants($path);
	gen_value_tags($path.'/themes/default/tpl');
	gen_block_tags($path.'/themes/default/tpl');
	gen_functions($path);
}
else {
	print "You have to provide a valid Dotclear root path ... [ERROR]\n";
}

print "[END]\n";

function gen_behaviors($path)
{
	print "Gen behaviors ... ";
	
	$p_plist = "(\n%s\n)";
	$p_plist_line = "\t{title = '%s';}";

	$cmd = 'find '.$path;
	exec($cmd,$eres,$ret);

	$b_res = array();
	foreach ($eres as $f)
	{
		$cmd = 'grep -h -i -A1 "# \-\-BEHAVIOR\-\-" '.$f;
		$list = `$cmd`;

		if ($list)
		{
			$list = preg_split('/^--$/m',$list);

			foreach ($list as $b)
			{
				$b = preg_replace('/\<\!\-\-(.*)\-\-\>/','$1',$b);
				$b = trim(preg_replace('/^\s+/m','',$b));
				

				$b = explode("\n",$b);
				$b[0] = trim(preg_replace('/^# --BEHAVIOR--\s+/','',$b[0]));

				$b_res[$b[0]] = sprintf($p_plist_line,$b[0],$b[0]);
			}
		}
	}

	ksort($b_res);
	
	if (!$f = (fopen(dirname(__FILE__).'/behaviors.plist','w'))) {
		print "[ERROR]\n";
	}
	elseif (fwrite($f,sprintf($p_plist,implode(",\n",$b_res)))) {
		print "[OK]\n";
	}
}

function gen_constants($path)
{
	print "Gen constants ... ";
	
	$p_plist = "(\n%s\n)";
	$p_plist_line = "\t{display = '%s';}";

	$cmd = 'find '.$path.' -name \'*.php\'';
	exec($cmd,$eres,$ret);

	$c_res = array();
	foreach ($eres as $f)
	{
		$cmd = 'grep -h -i "define([^)]*);" '.$f;
		$list = `$cmd`;

		if ($list)
		{
			$list = preg_split('/\n/m',$list);

			foreach ($list as $c)
			{
				$c = trim(preg_replace('/^\s+/m','',$c));

				$c = preg_replace('/define\(([^\)]*)\);\s*/','$1',$c);
				$c = preg_replace("/'/",'',$c);
				$c = explode(',',$c);

				if (!empty($c[0])) {
					$c_res[$c[0]] = sprintf($p_plist_line,$c[0],$c[0]);
				}
			}
		}
	}

	ksort($c_res);
	
	if (!$f = (fopen(dirname(__FILE__).'/constants.plist','w'))) {
		print "[ERROR]\n";
	}
	elseif (fwrite($f,sprintf($p_plist,implode(",\n",$c_res)))) {
		print "[OK]\n";
	}
}

function gen_value_tags($path)
{
	print "Gen value tags ... ";
	
	$p_plist = "(\n%s\n)";
	$p_plist_line = "\t{display = '%s';}";

	$cmd = 'find '.$path.' -name \'*.html\'';
	exec($cmd,$eres,$ret);

	$v_res = array();
	foreach ($eres as $f)
	{
		$cmd = 'grep -h -i "{{[^}]*}}" '.$f;
		$list = `$cmd`;

		if ($list)
		{
			preg_match_all('/\{\{tpl:([^\}]*)\}\}/',$list,$matches);

			foreach ($matches[1] as $v)
			{
				$v = explode(" ",$v);

				$v_res[$v[0]] = sprintf($p_plist_line,$v[0],$v[0]);
			}
		}
	}

	ksort($v_res);
	
	if (!$f = (fopen(dirname(__FILE__).'/value_tags.plist','w'))) {
		print "[ERROR]\n";
	}
	elseif (fwrite($f,sprintf($p_plist,implode(",\n",$v_res)))) {
		print "[OK]\n";
	}
}

function gen_block_tags($path)
{
	print "Gen block tags ... ";
	
	$p_plist = "(\n%s\n)";
	$p_plist_line = "\t{display = '%s';}";

	$cmd = 'find '.$path.' -name \'*.html\'';
	exec($cmd,$eres,$ret);

	$b_res = array();
	foreach ($eres as $f)
	{
		$cmd = 'grep -h -i "<[^>]*>" '.$f;
		$list = `$cmd`;

		if ($list)
		{
			preg_match_all('/\<tpl:([^>}]*)\>/',$list,$matches);

			foreach ($matches[1] as $b)
			{
				$b = explode(" ",$b);

				$b_res[$b[0]] = sprintf($p_plist_line,$b[0],$b[0]);
			}
		}
	}

	ksort($b_res);
	
	if (!$f = (fopen(dirname(__FILE__).'/block_tags.plist','w'))) {
		print "[ERROR]\n";
	}
	elseif (fwrite($f,sprintf($p_plist,implode(",\n",$b_res)))) {
		print "[OK]\n";
	}
}

function gen_functions($path)
{
	print "Gen functions definitions ... ";
	
	$p_plist = "(\n%s\n)";
	$p_plist_line = "\t{name = '%s'; definition = '%s'; file = '%s';}";

	$cmd = 'find '.$path.' -name \'*.php\'';
	exec($cmd,$eres,$ret);

	$f_res = array();
	foreach ($eres as $f)
	{
		$cmd = 'awk \'/function ([^(]+)(([^)]*))/ { print $0";"NR }\' '.$f;
		$functions = `$cmd`;

		if ($functions)
		{
			$functions = preg_split('/\n/m',$functions);

			foreach ($functions as $func)
			{
				$func = trim(preg_replace('/^\s+/m','',$func));
				
				preg_match('/^((public|protected|private)\s*(static)?\s*function\s+([a-zA-Z0-9\_\-]+).*);([0-9]+)/',$func,$matches);
				
				if (!preg_match('/^\_\_/',$matches[4])) {
					$file = str_replace($path,'',$f);

					$f_res[$matches[4]] = sprintf($p_plist_line,$matches[4],addslashes($matches[1]),$file.', line '.$matches[5]);
				}
			}
		}
	}
	
	ksort($f_res);

	if (!$f = (fopen(dirname(__FILE__).'/functions.plist','w'))) {
		print "[ERROR]\n";
	}
	elseif (fwrite($f,sprintf($p_plist,implode(",\n",$f_res)))) {
		print "[OK]\n";
	}
}

?>