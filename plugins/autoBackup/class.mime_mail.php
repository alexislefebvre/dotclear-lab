<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of autoBackup, a plugin for Dotclear.
# 
# Copyright (c) 2008 k-net
# http://www.k-netweb.net/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class mime_mail {
	
	var $to_email = '';
	var $to_name = '';
	var $from_email = '';
	var $from_name = '';
	var $subject = '';
	var $senddate = '';
	var $headers = '';
	var $body = '';
	var $parts = array();
	
	public function __construct($to='', $subject='', $body='', $from='') {
		$this->to_email = $to;
		$this->subject = $subject;
		$this->body = $body;
		$this->from_email = $from;
	}
	
	public function attach($message, $name, $ctype='', $encoding='') {
		if (!empty($message)) {
			if (empty($ctype)) {
				$ctype = files::getMimeType(strtolower(strrchr(basename($name), '.')));
			}
			$this->parts[] = array('ctype' => $ctype, 'message' => $message, 'encoding' => $encoding, 'name' => $name);
			return true;
		} else {
			return false;
		}
	}
	
	public function send() {
		if (empty($this->to_email)) {
			return false;
		}
		
		if (empty($this->senddate)) {
			$this->senddate = date('r');
		}
		
		$mime = 'MIME-Version: 1.0'."\n".
			'Date: '.$this->senddate."\n";
			
		if (!empty($this->from_email)) {
			if (empty($this->from_name)) {
				$mime .= 'From: '.$this->from_email."\n";
			} else {
				$mime .= 'From: '.$this->from_name.' <'.$this->from_email.'>'."\n";
			}
		}
			
		if (!empty($this->body)) {
			$this->attach($this->body, '', 'text/plain', 'utf-8');
		}
		
		$boundary = 'b'.md5(uniqid(time()));
		
		$mime .= 'Subject: '.$this->subject."\n".
			'Content-Type: multipart/mixed; boundary='.$boundary."\n".
			'This is a MIME encoded message.'."\n".
			'--'.$boundary;
		$this->parts = array_reverse($this->parts, TRUE);
		foreach ($this->parts as $part) {
			$mime .= "\n".
				'Content-Type: '.$part['ctype'].(!empty($part['name']) ? '; name="'.$part['name'].'"' : '').(!empty($part['encoding']) ? '; charset='.$part['encoding'] : '')."\n".
				'Content-Transfer-Encoding: base64'."\n".
				"\n".
				chunk_split(base64_encode($part['message']))."\n".
				'--'.$boundary;
		}
		$mime .= '--'."\n";
		
		if (mail($this->to_email, $this->subject, '', $mime)) {
			return true;
		}
		return false;
	}
}
?>