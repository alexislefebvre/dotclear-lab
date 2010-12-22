<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcDevKit, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom, Dsls and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class zipBuilder extends fileZip
{
	protected function writeFile($name,$file,$size,$mtime)
	{
		global $core;
		
		if (!isset($this->entries[$name])) {
			return;
		}
		
		$size = filesize($file);
		$this->memoryAllocate($size*3);
		
		$content = file_get_contents($file);
		
		$tmp = new ArrayObject;
		$tmp['ext'] = files::getExtension($name);
		$tmp['content'] = $content;
		# --BEHAVIOR-- dcDevKitPackagerContentFile
		$core->callBehavior('dcDevKitPackagerContentFile',$tmp);
		$content = $tmp['content'];
		unset($tmp);
		
		$unc_len	= strlen($content);
		$crc		= crc32($content);
		$zdata	= gzdeflate($content);
		$c_len	= strlen($zdata);
		
		unset($content);
		
		$mdate = $this->makeDate($mtime);
		$mtime = $this->makeTime($mtime);
		
		# Data descriptor
		$data_desc =
		"\x50\x4b\x03\x04".
		"\x14\x00".			# ver needed to extract
		"\x00\x00".			# gen purpose bit flag
		"\x08\x00".			# compression method
		pack('v',$mtime).		# last mod time
		pack('v',$mdate).		# last mod date
		pack('V',$crc).		# crc32
		pack('V',$c_len).		# compressed filesize
		pack('V',$unc_len).		# uncompressed filesize
		pack('v',strlen($name)).	# length of filename
		pack('v',0).			# extra field length
		$name.				# end of "local file header" segment
		$zdata.				# "file data" segment
		pack('V',$crc).		# crc32
		pack('V',$c_len).		# compressed filesize
		pack('V',$unc_len);		# uncompressed filesize
		
		fwrite($this->fp,$data_desc);
		unset($zdata);
		
		$new_offset = $this->old_offset + strlen($data_desc);
		
		# Add to central directory record
		$cdrec =
		"\x50\x4b\x01\x02".
		"\x00\x00".				# version made by
		"\x14\x00".				# version needed to extract
		"\x00\x00".				# gen purpose bit flag
		"\x08\x00".				# compression method
		pack('v',$mtime).			# last mod time
		pack('v',$mdate).			# last mod date
		pack('V',$crc).			# crc32
		pack('V',$c_len).			# compressed filesize
		pack('V',$unc_len).			# uncompressed filesize
		pack('v',strlen($name)).		# length of filename
		pack('v',0).				# extra field length
		pack('v',0).				# file comment length
		pack('v',0).				# disk number start
		pack('v',0).				# internal file attributes
		pack('V',32).				# external file attributes - 'archive' bit set
		pack('V',$this->old_offset).	# relative offset of local header
		$name;
		
		$this->old_offset = $new_offset;
		$this->ctrl_dir[] = $cdrec;
	}

	public static function pack($module,$prefix)
	{
		global $core;
		
		if (!$core->blog->settings->dcDevKit->packager_repository) {
			$public = $core->blog->public_path;
		} else {
			$public = $core->blog->settings->dcDevKit->packager_repository;
		}
		
		try
		{
			if (empty($module['id'])) {
				$module['id'] = basename($module['root']);
			}
			
			$target = $public.'/'.$prefix.$module['id'];
			if (!empty($module['version'])) {
				$target .= '-'.$module['version'];
			}
			$target .= '.zip';
			
			$exclude = '\.svn,CVS,\.DS_Store,Thumbs\.db';
			if ($core->blog->settings->dcDevKit->packager_to_exclude !== '') {
				$exclude .= ','.$core->blog->settings->dcDevKit->packager_to_exclude;
			}
			
			@set_time_limit(300);
			$fp = fopen($target,'wb');
			$zip = new zipBuilder($fp);
			foreach (explode(',',$exclude) as $p) {
				if ($p !== '') {
					$zip->addExclusion('#'.$p.'#');
				}
			}
			$zip->addDirectory($module['root'],$module['id'],true);
			$zip->write();
			unset($zip);
		}
		catch (Exception $e)
		{
			throw new Exception(__('Unable to build package. Error : ').$e->getMessage());
		}
	}
}

?>