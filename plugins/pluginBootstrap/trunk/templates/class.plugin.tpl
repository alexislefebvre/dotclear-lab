<?php
##licence_block##

/**
 * This class does nothing for the moment...
 *
 * @package    ##plugin_name##
 * @author     ##plugin_author##
 * @version    SVN: $Id: $
 */
class ##class_name##
{
	/**
	 * dcCore reference
	 *
	 * @var dcCore
	 * @access private
	 */
	private $core;

	/**
	 * Constructor
	 *
	 * @param dcCore $core
	 */
	public function __construct(dcCore $core)
	{
		$this->core = $core;
	}

	/**
	 * Example method 1: Compare given name with the one stored in session
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function compareName($name)
	{
		if ($name != $this->core->auth->getInfo('user_name')) {
			return false;
		}

		return true;
	}

	/**
	 * Example method 2: Compare given name with the one stored in session
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function compareFirstname($firstname)
	{
		if ($firstname != $this->core->auth->getInfo('user_firstname')) {
			return false;
		}

		return true;
	}
}
