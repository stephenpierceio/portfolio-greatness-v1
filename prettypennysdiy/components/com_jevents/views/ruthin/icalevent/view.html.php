<?php
/**
 * JEvents Component for Joomla 1.5.x
 *
 * @version     $Id: view.html.php 1406 2009-04-04 09:54:18Z geraint $
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2016 GWE Systems Ltd
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
class RuthinViewICalevent extends JEventsRuthinView 
{
	
	function detail($tpl = null)
	{
		JEVHelper::componentStylesheet($this);

		$document = JFactory::getDocument();
		// TODO do this properly
		//$document->setTitle(JText::_( 'BROWSER_TITLE' ));
						
		$params = JComponentHelper::getParams(JEV_COM_COMPONENT);
		//$this->assign("introduction", $params->get("intro",""));
		
		$this->data = $this->datamodel->getEventData( $this->evid, $this->jevtype, $this->year, $this->month, $this->day, $this->uid  );

		// Dynamic pathway
		if (isset($this->data['row'])){
			$pathway = JFactory::getApplication()->getPathway();

			$pathway->addItem($this->data['row']->title() ,"");

			// Set date in view for use in navigation icons
			$this->year = $this->data['row']->yup();
			$this->month = $this->data['row']->mup();
			$this->day = $this->data['row']->dup();

			// seth month and year to be used by mini-calendar if needed
			if (!JRequest::getVar("month",0)) JRequest::setVar("month",$this->month);
			if (!JRequest::getVar("year",0)) JRequest::setVar("year",$this->year);
		}
		
	}	
}
