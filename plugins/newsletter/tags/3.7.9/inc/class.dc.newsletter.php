<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class dcNewsletter
{
	// Variables
	protected $core;
	protected $blog;
	protected $blogname;
	protected $errors;
	protected $messages;
	
	public $newsletter_settings;
		
	/**
	 * Class constructor. Sets new dcNewsletter object
	 *
	 * @param:	$core	dcCore
	 */
	public function __construct(dcCore $core)
	{
		$this->core = $core;
		$this->blog = $core->blog;
		$this->blogname = $this->blog->name;
		
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$this->blog->settings->addNamespace('newsletter');
			$this->settings =& $core->blog->settings->newsletter;			
		} else {
			$this->blog->settings->setNamespace('newsletter');
			$this->settings =& $core->blog->settings;
		}
		
		$this->newsletter_settings = new newsletterSettings($core);
		$this->errors = $this->settings->newsletter_errors != '' ? unserialize($this->settings->newsletter_errors) : array();
		$this->messages = $this->settings->newsletter_messages != '' ? unserialize($this->settings->newsletter_messages) : array();			
	}

	/**
	 * Saves arrays on blog settings
	*/
	public function save()
	{
		$this->settings->put('newsletter_errors',serialize($this->errors),'string','Newsletter errors list');
		$this->settings->put('newsletter_messages',serialize($this->messages),'string','Newsletter messages list');
		$this->blog->triggerBlog();
	}

	###############################################
	# ERRORS
	###############################################

	// add an error
	public function addError($value)
	{
		if (array_key_exists($value,$this->errors)) {
			$this->delError($value);
		}
		$this->errors[] = $value;
		$this->save();
	}

	// remove an error
	public function delError($value)
	{
		if (array_key_exists($value,$this->errors)) {
			unset($this->errors[$value]);
		}
		$this->save();
	}
	
	// retrieve all errors
	public function getErrors()
	{
		return $this->errors;
	}

	// count all errors
	public function countErrors()
	{
		return sizeof($this->errors);
	}

	
	###############################################
	# MESSAGES
	###############################################

	// add a message
	public function addMessage($value)
	{
		if (array_key_exists($value,$this->messages)) {
			$this->delMessages($value);
		}		
		$this->messages[] = $value;
		$this->save();
	}

	// remove a message
	public function delMessage($value)
	{
		if (array_key_exists($value,$this->messages)) {
			unset($this->messages[$value]);
		}
		$this->save();
	}
	
	// retrieve all messages
	public function getMessages()
	{
		return $this->messages;
	}

	// count all messages
	public function countMessages()
	{
		return sizeof($this->messages);
	}

} // end class dcNewsletter

?>