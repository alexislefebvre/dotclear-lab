<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of autoBackup, a plugin for Dotclear.
# 
# Copyright (c) 2008 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class autoBackupMail
{
	public function __construct()
	{
		$this->to = '';
		$this->from = '';
		$this->replyto = '';
		$this->date = '';
		$this->subject = '';
		$this->message = '';
		$this->html = false;
		$this->utf8 = false;
		$this->parts = array();
		$this->template = '';
		$this->boundary = '----=_Next_Part_'.md5(uniqid(time()));
		$this->tboundary = $this->boundary.'_text';
		$this->eol = "\r\n";
	}

	public function attach($file)
	{
		$name = basename($file);
		$path = dirname($file);
		
		switch(strrchr(basename($name), '.'))
		{
			case '.gz': $ctype = 'application/x-gzip'; break;
			case '.tgz': $ctype = 'application/x-gzip'; break;
			case '.zip': $ctype = 'application/zip'; break;
			case '.pdf': $ctype = 'application/pdf'; break;
			case '.png': $ctype = 'image/png'; break;
			case '.gif': $ctype = 'image/gif'; break;
			case '.jpg': $ctype = 'image/jpeg'; break;
			case '.txt': $ctype = 'text/plain'; break;
			case '.htm': $ctype = 'text/html'; break;
			case '.html': $ctype = 'text/html'; break;
			default: $ctype = 'application/octet-stream'; break;
		}

		$this->parts[] = array (
			'path' => $path,
			'name' => $name,
			'ctype' => $ctype
		);
	}

	public function buildMessage()
	{
		$msg =
			'This is a multi-part message in MIME format.'.$this->eol.$this->eol.
			'--'.$this->boundary.$this->eol.
			'Content-Type: multipart/alternative; boundary="'.$this->tboundary.'"'.$this->eol.
			$this->getMessageText().
			$this->getMessageHtml().
			$this->getParts().
			'--'.$this->boundary.'--';

		return $msg;
	}

	public function getMessageText()
	{
		$res = 
			'--'.$this->tboundary.$this->eol.
			'Content-Type: text/plain; '.
			(($this->utf8) ? 'charset=utf-8'.$this->eol : 'charset=iso-8859-1'.$this->eol ).
			'Content-Transfer-Encoding: 8bit'.$this->eol.$this->eol;

		if ($this->html) {
			$tmp = ereg_replace("<(br[:blank:]?/?)>","\n",$this->message);
			$tmp = ereg_replace("</(p|div)>","\n\n",$tmp);
			$res .= ereg_replace("<[^>]*>","",$tmp);
		}
		else {
		  $res .= $this->message;
		}

		$res .= $this->eol.$this->eol;

		$res = utf8_decode($res);

		return ($this->utf8) ? utf8_encode($res) : $res;
	}

	public function getMessageHtml()
	{
		if ($this->html)
		{
			$res = 
				'--'.$this->tboundary.$this->eol.
				'Content-Type: text/html; '.
				(($this->utf8) ? 'charset=utf-8'.$this->eol : 'charset=iso-8859-1'.$this->eol ).
				'Content-Transfer-Encoding: 8bit'.$this->eol.$this->eol;

			if (!empty($this->template))
			{
				if (ereg('^<.*',$this->template)) {
					$res .= str_replace('%texte%',$this->message,$this->template);
				}
				else {
					$f = fopen($this->template,'r');
					$buffer = fread($f,filesize($this->template));
					fclose($f);
					$res .= str_replace('%texte%',$this->message,$buffer);
				}
			}
			else {
				$res .= $this->message;
			}

			$res .= $this->eol.$this->eol;

			$res = utf8_decode($res);

			return ($this->utf8) ? utf8_encode($res) : $res;
		}
	}

	public function getParts()
	{
		foreach ($this->parts as $part)
		{
			if (is_file($part['path'].'/'.$part['name']))
			{
				$file = fread(fopen($part['path'].'/'.$part['name'], 'r'), filesize($part['path'].'/'.$part['name']));
				$file = chunk_split(base64_encode($file));

				$res = 
					'--'.$this->boundary.$this->eol.
					'Content-Type: '.$part['ctype'].'; name="'.$part['name'].'"'.$this->eol.
					'Content-Transfer-Encoding: base64'.$this->eol.
					'Content-Description: '.$part['name'].$this->eol.
					'Content-Disposition: attachment; filename="'.$part['name'].'"'.$this->eol.$this->eol.
					$file.$this->eol.$this->eol;
			}
		}

		return $res;
	}

	public function getHeaders()
	{
		$exp = (!$this->utf8) ? utf8_decode($this->from) : $this->from;

		$res = 
			'From: '.$exp.$this->eol.
			((!empty($this->replyto)) ? 'Reply-To: <'.$this->replyto.'>'.$this->eol : '').
			((!empty($this->date)) ? 'Date: '.$this->date.$this->eol : 'Date: '.date("r").$this->eol).
			'Return-Path: '.$exp.'>'.$this->eol.
			'MIME-Version: 1.0'.$this->eol.
			'X-Sender: <'.$_SERVER['HTTP_HOST'].'>'.$this->eol.
			'X-Mailer: PHP v'.phpversion().$this->eol.
			'X-auth-smtp-user: '.$exp.' '.$this->eol.
			'X-abuse-contact: '.$exp.' '.$this->eol.
			'Content-Type: multipart/mixed; boundary="'.$this->boundary.'"'.$this->eol;

		return $res;
	}

	public function send()
	{
		$headers = $this->getHeaders();
		$msg = $this->buildMessage();

		if (mail($this->to, $this->subject, $msg, $headers)) {
			return true;
		}
		else {
			return false;
		}
	}

}

?>