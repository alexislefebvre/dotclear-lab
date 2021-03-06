<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class newsletterMailing implements IteratorAggregate
{
	private $items = array();
	private $count = 0;
	
	protected $blog;

	protected $x_mailer;
	protected $x_blog_id;
	protected $x_blog_name;
	protected $x_blog_url;
	protected $x_originating_ip;

	protected $email_from;
	protected $name_from;

	protected $errors;
	protected $success;
	protected $states;
	protected $nothing;

	protected $limit;
	protected $offset;
	
	/**
	 * Class constructor
	 *
	 * @param:	$core	dcCore
	 */
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->blog =& $core->blog;
		
		if(newsletterPlugin::getEditorEmail() == "")
			throw new Exception (__('Editor email is empty'));
		else
			$this->email_from = mail::B64Header('<'.newsletterPlugin::getEditorEmail().'>');
		
		if(newsletterPlugin::getEditorName() == "")
			throw new Exception (__('Editor name is empty'));
		else
			$this->name_from = mail::B64Header(newsletterPlugin::getEditorName());
		
		$this->x_mailer = mail::B64Header(newsletterPlugin::dbVersion());
		$this->x_blog_id = mail::B64Header($this->blog->id);
		$this->x_blog_name = mail::B64Header($this->blog->name);
		$this->x_blog_url = mail::B64Header($this->blog->url);		
		$this->x_originating_ip = http::realIP();
		
		$this->success = array();
		$this->errors = array();
		$this->states = array();
		$this->nothing = array();
		
		$this->limit = 10;
		
	}

	public function getIterator() {
		return new MyIterator($this->items);
	}

	/**
	 * Ajoute un message à la liste des envois
	 *
	 * @param:	$id_subscriber		int
	 * @param:	$email_to			string
	 * @param:	$subject			string
	 * @param:	$body			string
	 * @param:	$mode			string
	 * 
	 * @return:	
	 */	
	public function addMessage($id_subscriber,$email_to,$subject,$body,$mode='html')
	{
		$this->items[$this->count++] = array(
			'id' => $id_subscriber,
			'email_to' => $email_to,
			'subject' => $subject,
			'body' => $body,
			'mode' => $mode
		);
	}

	/**
	 * Retourne le nombre de messages dans la liste
	 *
	 * @return:	int
	 */	
	public function getCount()
	{
		return $this->count;
	}
	
	// supprime un message de la liste
	/*
	public function del()
	{
	}

	function __toString()
	{
		return 'to do ...';
	}
	//*/ 
	
	/**
	 * Gère le traitement d'envoi des messages de la liste
	 *
	 * @return:
	 */	
	public function batchSend()
	{
		$this->offset = 0;
		do {
			$portion = array_slice($this->items,$this->offset,$this->limit,true);
			$this->offset += $this->limit;
			$this->send($portion);
		} while (count($portion) > 0);
	}
	
	/**
	 * Envoi des messages de la liste
	 *
	 * @return:
	 */	
	protected function send($portion)
	{
		foreach ($portion as $k => $v) {
			
			if($this->sendMail($v['email_to'], $v['subject'], $v['body'], $v['mode'], $_lang = 'fr')) {
				$this->success[$k] = $v['email_to'];
				$this->states[$k] = $v['id'];
			} else {
				$this->errors[$k] = $v['email_to'];
			}
		}
	}

	/**
	 * Retourne le tableau des emails dont les envois sont passés correctement
	 *
	 * @return:	array
	 */	
	public function getSuccess()
	{
		return $this->success;
	}

	/**
	 * Retourne le tableau des emails dont les envois ne sont pas passés correctement
	 *
	 * @return:	array
	 */	
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Retourne le tableau des id pour une modification de l'état suite au succès de l'envoi
	 *
	 * @return:	array
	 */	
	public function getStates()
	{
		return $this->states;
	}

	/**
	 * Ajoute un message à la liste des messages avec rien à envoyer
	 *
	 * @return:
	 */	
	public function addNothingToSend($id=-1,$email=null)
	{
		if ($email) {
			$this->nothing[$id] = $email;
		}
	}

	/**
	 * Retourne le tableau des emails dont le contenu des messages étaient vides
	 *
	 * @return:	array
	 */	
	public function getNothingToSend()
	{
		return $this->nothing;
	}

	/**
	 * Formate et envoi un message
	 * Utilise la fonction mail() de Dotclear
	 *
	 * @return:	boolean
	 */	
	protected function sendMail($_email, $_subject, $_body, $_type = 'html', $_lang = 'fr')
	{
		try {
			if (empty($_email) || empty($_subject) || empty($_body)) {
				return false;
			} else {
	
		          $f_check_notification = newsletterPlugin::getCheckNotification();
	
				$email_to = mail::B64Header($_email.' <'.$_email.'>');
	
				$headers = array(
					'From: "'.$this->name_from.'" '.$this->email_from,
					'Reply-To: '.$this->email_from,
					'Delivered-to: '.$email_to,
					'X-Sender:'.$this->email_from,
					'MIME-Version: 1.0',
					(($_type == 'html') ? 'Content-Type: text/html; charset=UTF-8;' : 'Content-Type: text/plain; charset=UTF-8;'),
					'X-Mailer: Dotclear '.$this->x_mailer,
					'X-Blog-Id: '.$this->x_blog_id,
					'X-Blog-Name: '.$this->x_blog_name,
					'X-Blog-Url: '.$this->x_blog_url,
					'X-Originating-IP: '.$this->x_originating_ip,
					(($f_check_notification) ? 'Disposition-Notification-To: '.$this->email_from : '')
				);
			          
		          $subject = mail::B64Header($_subject);
		          
				return (mail::sendMail($_email, $subject, $_body, $headers));
			}
		} catch (Exception $e) { 
			return false;
		}
	}

}

?>
