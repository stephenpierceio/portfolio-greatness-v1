<?php
/** 
 * Droppics
 * 
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 * @package Droppics
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;



class DroppicsViewFiles extends JViewLegacy
{
	
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null){
            if(JRequest::getCmd('layout')=='result'){    
                $this->setLayout('result');
                $this->datas = json_encode(JRequest::get('error'));
            }else{
                $model = $this->getModel();
                $this->files = json_encode($model->getFilesAsArray());
            }
            parent::display($tpl);
	}
}
