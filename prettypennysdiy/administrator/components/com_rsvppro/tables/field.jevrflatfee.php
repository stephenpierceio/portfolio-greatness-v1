<?php
/**
 * copyright (C) 2008-2017 GWE Systems Ltd - All rights reserved
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

include_once(dirname(__FILE__)."/field.php");

/**
* Template Field class
*
*/
class TableJevrflatfee extends TableField
{

	/**
	* Overloaded bind function
	*
	*/
	public function bind($array, $ignore=array(), $fieldid="")
	{
		$success = parent::bind($array, $ignore, $fieldid);
		return $success;
	}

}
