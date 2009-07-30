<?php
class microBlogException extends Exception{
	public function __construct($msg, $code){
		parent::__construct($msg, $code);
	}
}