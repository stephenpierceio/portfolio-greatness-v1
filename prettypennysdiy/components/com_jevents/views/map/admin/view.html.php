<?php

/**
 * JEvents Component for Joomla 1.5.x
 *
 * @version     $Id$
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2015 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class for the component frontend
 *
 * @static
 */
class MapViewAdmin extends JEventsMapView
{

	private static $called = 0;

	function listevents($tpl = null)
	{
		// silly check to avoid reccursion
		if (self::$called == 1)
		{
			return "";
		}
		self::$called = 1;

		$jevparams = JComponentHelper::getParams(JEV_COM_COMPONENT);

		$fallbackview = $jevparams->get("fallbackview", "geraint");
		// use this contorted method to handle soft linked files !
		$dir = basename(dirname(__FILE__));
		$fallbackabstractfile = JPATH_SITE . "/components/com_jevents/views/$fallbackview/abstract/abstract.php";
		$fallbackfile = JPATH_SITE . "/components/com_jevents/views/$fallbackview/$dir/" . basename(__FILE__);
		//$fallbackfile = str_replace('/map/','/'.$fallbackview.'/',__FILE__);
		if (!JFile::exists($fallbackfile))
		{
			$fallbackview = "geraint";
			$fallbackfile = JPATH_SITE . "/components/com_jevents/views/$fallbackview/$dir/" . basename(__FILE__);
		}
		$newViewClass = str_replace("Map", ucfirst($fallbackview), get_class($this));
		include_once($fallbackabstractfile);
		include_once($fallbackfile);
		// TODO Joomla 3.0
		// Use DIRECTORY_SEPARATOR for windows servers !!!
		$name = str_replace(array("map".DIRECTORY_SEPARATOR, "map/"),array($fallbackview.DIRECTORY_SEPARATOR, "$fallbackview/"), $this->_name);
		$theme = $fallbackview;
		$view = str_replace(array("map".DIRECTORY_SEPARATOR, "map/"),"", $this->_name);
		$newView = new $newViewClass(array('base_path' => $this->_basePath,
					"template_path" => $this->_basePath . '/' . "views" . '/' . $theme . '/' . $view . '/' . 'tmpl',
					"name" => $name));
		// propogate public fields
		foreach (get_object_vars($this) as $key => $var)
		{
			if (in_array($key, array("_path", "_name", "path", "name", "jevlayout", "layout", "_layout")))
			{
				continue;
			}
			$newView->$key = $var;
		}
		$newView->jevlayout = $fallbackview;
		$newView->setLayout($this->getLayout());
		ob_start();
		$newView->display();
		$this->jevoutput = ob_get_clean();
		ob_start();
		echo $this->jevoutput;
		return;

	}

}
