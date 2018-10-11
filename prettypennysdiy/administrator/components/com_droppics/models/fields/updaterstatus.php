<?php
/** 
 * Droppics
 * 
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 * @package Droppics
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barr?re (http://www.crac-design.com). All rights reserved.
 * @license GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 */
JLoader::register('JuupdaterHelper', JPATH_SITE.'/plugins/installer/juupdater/helper.php');
class JFormFieldUpdaterstatus extends JFormField
{
	
	protected $type = 'updaterstatus';

	/**
	 */
	protected function getInput()
	{
             // Load modal behavior
            JHtml::_('behavior.modal', 'a.modal');
 
            // Build the script
            $script = array();
            $script[] = "var ju_url = {};";
            $script[] = "if(document.location.toString().indexOf('?') !== -1) {
                            var query = document.location
                                           .toString()
                                           // get the query string
                                           .replace(/^.*?\?/, '')
                                           // and remove any existing hash string (thanks, @vrijdenker)
                                           .replace(/#.*$/, '')
                                           .split('&');

                            for(var i=0, l=query.length; i<l; i++) {
                               var aux = decodeURIComponent(query[i]).split('=');
                               ju_url[aux[0]] = aux[1];
                            }
                        }";
            $script[] = "var option = ju_url.component";
            
            $script[] = "function ju_disconnect() {;
                                jQuery.ajax({
                                    url     :   'index.php?option='+ option +'&task=jutoken.ju_remove_token',
                                    method    : 'GET',
                                    dataType : 'json',
                                }).done(function(response){
                                    window.location.assign(document.URL);
                                });
                            }";
            $script[] = "jQuery(document).ready(function($){";
            
            $script[] = "if(typeof ju_url != 'undefined'){
                            var eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';
                            var eventer = window[eventMethod];
                            var messageEvent = eventMethod == 'attachEvent' ? 'onmessage' : 'message';

                            // Listen to message from child window
                            eventer(messageEvent, function (e) {
                            
                                var res = e.data;
                                if(typeof res != 'undefined' && typeof res.type != 'undefined' && res.type == 'joomunited_login'){
                                    $.ajax({
                                        url     :   'index.php?option='+ option +'&task=jutoken.ju_add_token',
                                        method    : 'GET',
                                        dataType : 'json',
                                        data    :   {
                                            'token': res.token,
                                        }
                                    }).done(function(response){
                                        window.location.assign(document.URL);
                                        //var domain = window.location;
                                        //window.location.assign(domain.origin + domain.pathname + '?option=com_installer&view=update' );
                                    });
                                }
                            }, false);
                        }";
            $script[] = '});';
            
            $style = array();
            $style[] = '.ju-btn {
                            text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
                            display: inline-block;
                            padding: 6px 12px;
                            margin-bottom: 0;
                            font-size: 13px;
                            font-weight: 400;
                            line-height: 18px;
                            text-align: center;
                            white-space: nowrap;
                            vertical-align: middle;
                            touch-action: manipulation;
                            cursor: pointer;
                            -webkit-user-select: none;
                            background-image: none;
                            border: 1px solid transparent;
                            border-radius: 4px;
                            text-transform: none;
                            text-decoration: none;
                            font: inherit;
                            margin: 0;
                            overflow: visible;
                        }

                        .ju-btn:hover{
                            color: #fff;
                        }.ju-btn-connect {
                            color: #fff;
                            background-color: #5cb85c;
                            border-color: #4cae4c;
                        }

                        .ju-btn-connect:hover{
                            background-color: #4cae4c;
                        }

                        .ju-btn-disconnect {
                            color: #fff;
                            background-color: #f0ad4e;
                            border-color: #eea236;
                        }
                        .ju-btn-disconnect:hover{
                            background-color: #eea236;
                        }';
            
           
              //Add to document head
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
            JFactory::getDocument()->addStyleDeclaration(implode("\n", $style));
          
            $html = array();
            $checklogin = JuupdaterHelper::check_config_token();
            $ju_base = 'https://www.joomunited.com';
            $link = $ju_base.'/index.php?option=com_juupdater&amp;view=login&amp;tmpl=component&amp;extension=droppics.zip&amp;site='.JUri::base().'&amp;layout=modal';

            // The book select button
            $html[] = '<div class="button2-left">';
            $html[] = '  <div class="blank">';
            if($checklogin == 0){
                $html[] = '<p>To enable live update please link your joomunited account</p><a style="text-decoration:none;" class="btn button modal ju-btn ju-btn-connect" title="Link my joomunited account" href="'.$link.
                               '" rel="{handler: \'iframe\', size: {x:800, y:450}}">Link my joomunited account</a>';
            }else{
                $html[] = '<p>Live update are enabled click here if you want to disable it</p><a style="text-decoration:none;" class="btn button ju-btn ju-btn-disconnect" title="Disconnect my joomunited account" onclick="ju_disconnect();">Disconnect my joomunited account</a>';
            }
            $html[] = '  </div>';
            $html[] = '</div>';

            return implode("\n", $html);  
	}
        
}
