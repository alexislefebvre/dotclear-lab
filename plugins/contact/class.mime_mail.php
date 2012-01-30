<?php
/* BEGIN LICENSE BLOCK
This file is part of Contact, a plugin for Dotclear.

K-net
Pierre Van Glabeke

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */
class mime_mail {
	
	var $recipients = array();
	var $sender_email = '';
	var $sender_name = '';
	var $subject = '';
	var $senddate = '';
	var $body = '';
	var $parts = array();
	var $mimemail = true;
	
	function addRecipient($email, $name = '') {
		if (empty($name)) {
			$name = $email;
		}
		$this->recipients[] = array($email, $name);
	}
	
	function attach($message, $name, $ctype='', $encoding='') {
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
	
	function send() {
		if (empty($this->recipients)) {
			return false;
		}
		
		if (empty($this->senddate)) {
			$this->senddate = date('r');
		}
			
		$mime_basic = 'MIME-Version: 1.0'."\n".
			'Date: '.$this->senddate."\n";
			
		if (!empty($this->sender_email)) {
			if (empty($this->sender_name)) {
				$mime_basic .= 'From: '.$this->sender_email."\n";
			} else {
				$mime_basic .= 'From: '.$this->sender_name.' <'.$this->sender_email.'>'."\n";
			}
		}
			
		if ($this->mimemail) {
			if (!empty($this->body)) {
				$this->attach($this->body, '', 'text/plain', 'utf-8');
			}
			
			$boundary = 'b'.md5(uniqid(time()));
			
			$message = $mime_basic;
			//*
			if ($this->mimemail) {
				$message .= 'To: ';
				$i = 0;
				foreach ($this->recipients as $rec) {
					$message .= ($i > 0 ? ', ' : '').$rec[1].' <'.$rec[0].'>';
				}
				$message .= "\n";
			}
			//*/
			
			$message .= 'Subject: '.$this->subject."\n".
				'Content-Type: multipart/mixed; boundary='.$boundary."\n".
				'This is a MIME encoded message.'."\n".
				'--'.$boundary;
			$this->parts = array_reverse($this->parts, TRUE);
			foreach ($this->parts as $part) {
				$message .= "\n".
					'Content-Type: '.$part['ctype'].(!empty($part['name']) ? '; name="'.$part['name'].'"' : '').(!empty($part['encoding']) ? '; charset='.$part['encoding'] : '')."\n".
					'Content-Transfer-Encoding: base64'."\n".
					"\n".
					chunk_split(base64_encode($part['message']))."\n".
					'--'.$boundary;
			}
			$message .= '--'."\n";
			
			//return mail($this->recipients[0][0], '', '', $message);
			//*
			$ok = false;
			foreach ($this->recipients as $rec) {
				if (mail($rec[0], $this->subject, '', $message)) {
					$ok = true;
				}
			}
			return $ok;
			//*/
		} else {
			$ok = false;
			foreach ($this->recipients as $rec) {
				if (mail($rec[0], $this->subject, $this->body, $mime_basic)) {
					$ok = true;
				}
			}
			return $ok;
		}
	}
}
?>
