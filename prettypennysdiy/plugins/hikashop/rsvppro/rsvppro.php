<?php

defined ('_JEXEC') or die('Restricted access');

/**
 *
 * a special type of Hikashop plugin - just to update RSVP Pro status':
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 
 */

class plgHikashopRsvppro extends hikashopPlugin {

	public function onHikashopBeforeDisplayView(&$view){
		if (isset($view->extraFields["item"]["rsvptransactionid"])){
			// could change to disable quantity field display ???
			if (isset($view->products[0])){
				$view->products[0]->product_quantity_layout = "";
			}
		}
	}

	function onAfterOrderCreate(&$order, & $send_email){
		return $this->onAfterOrderUpdate($order, $send_email);
	}

	/**
	 * Save updated order data to the method specific table
	*/
	public function onAfterOrderUpdate(&$order, & $send_email) {

		$config=&hikashop_config();
		$confirmed = $config->get('order_confirmed_status');
		list($cancelled,$refunded)  = explode(",",$config->get('cancelled_order_status'));

		if(!isset($order->order_status) || !($order->order_status==$confirmed || $order->order_status==$cancelled  || $order->order_status==$refunded  ))
			return true;

		if(!empty($order->order_type) && $order->order_type != 'sale')
			return true;

		$classOrder = hikashop_get('class.order');
		$fullOrder = $classOrder->loadFullOrder($order->order_id, false, false);

		if (!isset($fullOrder->products) || count($fullOrder->products)==0){
			return true;
		}
		foreach ($fullOrder->products as $product){
			if (!isset($product->rsvptransactionid) || !intval($product->rsvptransactionid)){
				continue;
			}
			$transaction_id =  $product->rsvptransactionid;
				
			JLoader::register('jevFilterProcessing', JPATH_SITE . "/components/com_jevents/libraries/filters.php");
			JLoader::register('JevRsvpParameter', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/jevrsvpparameter.php");
			JLoader::register('JevRsvpAttendance', JPATH_SITE. "/plugins/jevents/jevrsvppro/rsvppro/jevrattendance.php");
			JLoader::register('JevRsvpDisplayAttendance', JPATH_SITE. "/plugins/jevents/jevrsvppro/rsvppro/jevrdisplayattendance.php");
			JLoader::register('RsvpHelper', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/rsvphelper.php");
			JLoader::register('JevTemplateHelper', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/templatehelper.php");
			JLoader::register('JevDate', JPATH_SITE . "/components/com_jevents/libraries/jevdate.php");
			include_once(JPATH_ADMINISTRATOR . "/components/com_rsvppro/rsvppro.defines.php");

			$transaction =new rsvpTransaction();

			$extrainfo = "";
			if ($transaction->load( $transaction_id ) && isset($transaction->attendee_id) &&$transaction->attendee_id) {

				$db = JFactory::getDBO();
				$sql = "SELECT * FROM #__jev_attendees WHERE id=" . $transaction->attendee_id;
				$db->setQuery($sql);
				$attendee = $db->loadObject();
				if ($attendee)
				{
					$sql = "SELECT * FROM #__jev_attendance WHERE id=" . $attendee->at_id;
					$db->setQuery($sql);
					$rsvpdata = $db->loadObject();

					$rpid = $attendee->rp_id;
					$this->dataModel = new JEventsDataModel();
					$this->queryModel = new JEventsDBModel($this->dataModel);

					// Find the first repeat
					$vevent = $this->dataModel->queryModel->getEventById($rsvpdata->ev_id, false, "icaldb", false);
					if ($rpid == 0)
					{
						$repeat = $vevent->getFirstRepeat();
					}
					else
					{
						$repeat = false;
						list($year, $month, $day) = JEVHelper::getYMD();
						$repeatdata = $this->dataModel->getEventData(intval($rpid), "icaldb", $year, $month, $day);
						if ($repeatdata && isset($repeatdata["row"]))
							$repeat = $repeatdata["row"];
					}

					if ($repeat){
						// update the payment status within RSVP Pro
						JPluginHelper::importPlugin("rsvppro");
						$dispatcher =  JDispatcher::getInstance();
						$dispatcher->trigger('updateHikaPaymentStatus', array($rsvpdata, $attendee, $repeat, $transaction,$order));
					}
				}
			}
		
		}
		return true;
	}

	public function onBeforeCalculateProductPriceForQuantity(&$product){

		if (!isset($product->rsvptransactionid) || !intval($product->rsvptransactionid)){

			$jinput     = JFactory::getApplication()->input;

			if (($jinput->getString('ctrl') == 'product' && $jinput->getString('task') == 'updatecart')  ) {
				return;
			}
			// only do this at checkout
			if ($jinput->getString('ctrl') == 'checkout' && $jinput->getString('view') == 'checkout') {
				$sku = $product->product_code;

				$plugin = JPluginHelper::getPlugin("rsvppro", "hikashop");
				$params = new JRegistry($plugin->params);
				$rsvp_sku = $params->get('skuprefix', 'RSVP');

				$parts = explode("_", $sku);

				if (count($parts) !== 3)
					return true;

				if ($parts[0] !== $rsvp_sku){
					return true;
				}

				// We have a product in the cart with no transaction id - we should redirect to the event page??
				list($rsvpprefix, $eventid, $repeatid) = $parts;
				
				$db = JFactory::getDbo();

				if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).'/components/com_hikashop/helpers/helper.php'))
					return true;

				// setup the Joomla autoloader
				include_once(JPATH_SITE . "/components/com_jevents/jevents.defines.php");
				// get the data and query models
				$dataModel = new JEventsDataModel("JEventsAdminDBModel");
				$queryModel = new JEventsDBModel($dataModel);
				//method viewDetailLink is in the following class
				$jEventModel = new jEventCal($dataModel);

				if ($repeatid==0){
					// single event
					$nextrepeat = true;
					// get the event by event id
					$jevent = $queryModel->getEventById(intval($eventid), 1, "icaldb");
				}
				else {
					// repeating event
					$nextrepeat = false;
					// get the event by repeat id
					$jevent = $queryModel->listEventsById(intval($repeatid), 1, "icaldb");

				}

				if (!$jevent)
					return '';
				if ($nextrepeat)
				{
					$jevent = $jevent->getNextRepeat();
				}

				// get the event detail link (already SEFed)
				$Itemid = JEVHelper::getItemid($jevent);
				$detailSefLink = $jevent->viewDetailLink($jevent->yup(), $jevent->mup(), $jevent->dup(), true, $Itemid);
				$link = JRoute::_($detailSefLink);
				JFactory::getApplication()->redirect($link, JText::_('RSVP_HIKA_PLEASE_REGISTER_FOR_EVENT_USING_THIS_FORM'));

			}
			return;
		}

		$db = JFactory::getDbo();

		JLoader::register('jevFilterProcessing', JPATH_SITE . '/components/com_jevents/libraries/filters.php');
		JLoader::register('JevRsvpParameter', JPATH_ADMINISTRATOR . '/components/com_rsvppro/libraries/jevrsvpparameter.php');
		JLoader::register('JevRsvpAttendance', JPATH_SITE. '/plugins/jevents/jevrsvppro/rsvppro/jevrattendance.php');
		JLoader::register('JevRsvpDisplayAttendance', JPATH_SITE. '/plugins/jevents/jevrsvppro/rsvppro/jevrdisplayattendance.php');
		JLoader::register('RsvpHelper', JPATH_ADMINISTRATOR . '/components/com_rsvppro/libraries/rsvphelper.php');
		JLoader::register('JevTemplateHelper', JPATH_ADMINISTRATOR . '/components/com_rsvppro/libraries/templatehelper.php');

		JLoader::register('JevDate', JPATH_SITE . '/components/com_jevents/libraries/jevdate.php');
		include_once(JPATH_ADMINISTRATOR . '/components/com_rsvppro/rsvppro.defines.php');

		$transaction =new rsvpTransaction( );

		if ($transaction->load( $product->rsvptransactionid ) ){
			$currencyClass = hikashop_get('class.currency');

                        if (!isset($product->prices)){
                            $product->prices = array();
                            $product->prices[0] = new stdClass();
                            $product->prices[0]->price_min_quantity = 1;
                        }

                        // Make sure we havre the correct currency and price
                        $db->setQuery("SELECT * from #__hikashop_currency where currency_code = '$transaction->currency'");
                        $currencyData = $db->loadObject();
                        if ($currencyData) {
                                $currency = $currencyData->currency_id; 
                        }
                        else {
                                $currency = hikashop_getCurrency();
                        }
                        $product->prices[0]->price_currency_id = $currency;

                        $product->prices[0]->price_value = $transaction->amount;
                        
			$config=hikashop_config();
			if($config->get('tax_zone_type','shipping')=='billing'){
				$zone_id = hikashop_getZone('billing');
			}else{
				$zone_id = hikashop_getZone('shipping');
			}
			$round = 2;

			$product->price_value_with_tax = $currencyClass->getTaxedPrice($transaction->amount,$zone_id,isset($product->product_tax_id)?$product->product_tax_id:null,$round);
                        $product->prices[0]->price_value_with_tax = $product->price_value_with_tax;
                        if ($product->prices[0]->price_value_with_tax != $product->prices[0]->price_value && count($product->prices[0]->taxes)==1){
                            $product->prices[0]->taxes[0]->tax_amount = ($product->prices[0]->price_value_with_tax - $product->prices[0]->price_value);
                        }

                        if (isset($product->prices)){
                            $currencyClass->quantityPrices($product->prices,1,$product->cart_product_total_quantity);
                        }

		}

	}

	public function  onBeforeCalculateProductPriceForQuantityInOrder(&$product){

		if (!isset($product->rsvptransactionid) || !intval($product->rsvptransactionid)){
			return;
		}

		$db = JFactory::getDbo();

		JLoader::register('jevFilterProcessing', JPATH_SITE . "/components/com_jevents/libraries/filters.php");
		JLoader::register('JevRsvpParameter', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/jevrsvpparameter.php");
		JLoader::register('JevRsvpAttendance', JPATH_SITE. "/plugins/jevents/jevrsvppro/rsvppro/jevrattendance.php");
		JLoader::register('JevRsvpDisplayAttendance', JPATH_SITE. "/plugins/jevents/jevrsvppro/rsvppro/jevrdisplayattendance.php");
		JLoader::register('RsvpHelper', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/rsvphelper.php");
		JLoader::register('JevTemplateHelper', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/templatehelper.php");

		JLoader::register('JevDate', JPATH_SITE . "/components/com_jevents/libraries/jevdate.php");
		include_once(JPATH_ADMINISTRATOR . "/components/com_rsvppro/rsvppro.defines.php");

		$transaction =new rsvpTransaction( );

		if ($transaction->load( $product->rsvptransactionid ) ){
			$currencyClass = hikashop_get('class.currency');

                        if (!isset($product->prices)){
                            $product->prices = array();
                            $product->prices[0] = new stdClass();
                            $product->prices[0]->price_min_quantity = 1;
                        }

                        // Make sure we havre the correct currency and price
                        $db->setQuery("SELECT * from #__hikashop_currency where currency_code = '".$transaction->currency."'");
                        $currencyData = $db->loadObject();
                        if ($currencyData) {
                                $currency = $currencyData->currency_id; 
                        }
                        else {
                                $currency = hikashop_getCurrency();
                        }
                        $product->prices[0]->price_currency_id = $currency;

                        $product->prices[0]->price_value = $transaction->amount;
                        
			$product->order_product_price = $transaction->amount;
			$config=hikashop_config();
			if($config->get('tax_zone_type','shipping')=='billing'){
				$zone_id = hikashop_getZone('billing');
			}else{
				$zone_id = hikashop_getZone('shipping');
			}
			$currency_id = hikashop_getCurrency();
			$round = 2;

			$product->price_value_with_tax = $currencyClass->getTaxedPrice($transaction->amount,$zone_id,isset($product->product_tax_id)?$product->product_tax_id:null,$round);
                        $product->prices[0]->price_value_with_tax = $product->price_value_with_tax;                        

                        if (isset($product->prices)){
                            $currencyClass->quantityPrices($product->prices,1,1);
                        }

		}

	}

	public function onBeforeCartUpdate(&$cartClass, &$cart, &$product_id, &$quantity, &$add, &$type,&$resetCartWhenUpdate,&$force,&$do ) {
                // Not set to zero to skip this or if simply updating coupon
		if ($quantity >0 || $type=="coupon"){
			return;
		}
/*
                JLog::addLogger(array('text_file' => 'rsvppro.php'), JLog::ALL, array('rsvppro'));
                JLog::add("onBeforeCartUpdate ".$quantity." ".$add." ".$type." ".$resetCartWhenUpdate." ".$force." ".$do, JLog::INFO, 'rsvppro');
*/
		$fullcart = $cartClass->loadFullCart(false, true, true);

		$classProduct = hikashop_get('class.product');
		if ($fullcart->products){

			$this->rsvppro_products = array();

			foreach ($fullcart->products as $product){
				if (!isset($product->rsvptransactionid) || !intval($product->rsvptransactionid)){
					continue;
				}
				$this->rsvppro_products[] = $product;
			}
		}

		return true;

	}

	public function onAfterCartUpdate(&$cartClass, &$cart, &$product_id, &$quantity, &$add, &$type,&$resetCartWhenUpdate,&$force,&$updated ) {
		$config=&hikashop_config();

		if (isset($this->rsvppro_products) && count($this->rsvppro_products)){

        		$fullcart = $cartClass->loadFullCart(false, true, true);
                    
			$plugin = JPluginHelper::getPlugin("rsvppro", "hikashop");
			$paypluginparams = new JRegistry($plugin->params);

			foreach ($this->rsvppro_products as $product ) {
                                // Has this product gone from the cart?
                                $processproduct = true;
                                if (count($fullcart->products)){
                                    foreach ($fullcart->products as $postproduct) {
                                        if ($postproduct->cart_product_id == $product->cart_product_id && $postproduct->cart_product_quantity>0){
                                            $processproduct = false;
                                            break;
                                        }
                                    }                                    
                                }
                                if (!$processproduct) {
                                    continue;
                                }
				$transaction_id =  $product->rsvptransactionid;

				JLoader::register('jevFilterProcessing', JPATH_SITE . "/components/com_jevents/libraries/filters.php");
				JLoader::register('JevRsvpParameter', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/jevrsvpparameter.php");
				JLoader::register('JevRsvpAttendance', JPATH_SITE. "/plugins/jevents/jevrsvppro/rsvppro/jevrattendance.php");
				JLoader::register('JevRsvpDisplayAttendance', JPATH_SITE. "/plugins/jevents/jevrsvppro/rsvppro/jevrdisplayattendance.php");
				JLoader::register('RsvpHelper', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/rsvphelper.php");
				JLoader::register('JevTemplateHelper', JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/templatehelper.php");

				JLoader::register('JevDate', JPATH_SITE . "/components/com_jevents/libraries/jevdate.php");
				include_once(JPATH_ADMINISTRATOR . "/components/com_rsvppro/rsvppro.defines.php");

				$transaction =new rsvpTransaction( );

				$extrainfo = "";
				if ($transaction->load( $transaction_id ) && isset($transaction->attendee_id) &&$transaction->attendee_id) {

					$db = JFactory::getDBO();
					$sql = "SELECT * FROM #__jev_attendees WHERE id=" . $transaction->attendee_id;
					$db->setQuery($sql);
					$attendee = $db->loadObject();
					if ($attendee)
					{
						$sql = "SELECT * FROM #__jev_attendance WHERE id=" . $attendee->at_id;
						$db->setQuery($sql);
						$rsvpdata = $db->loadObject();

						$hscancelondelete = false;
						if (isset($rsvpdata->template) && is_numeric($rsvpdata->template))
						{
							$db = JFactory::getDBO();
							$db->setQuery("Select params from #__jev_rsvp_templates where id=" . intval($rsvpdata->template));
							$templateParams = $db->loadObject();
							if ($templateParams)
							{
								$templateParams = json_decode($templateParams->params);
								$hscancelondelete = isset($templateParams->hscancelondelete) ? $templateParams->hscancelondelete : $paypluginparams ->get("hscancelondelete",0);
							}
							else
							{
								$hscancelondelete = $paypluginparams ->get("hscancelondelete",0);
							}
						}
						else
						{
							$hscancelondelete = $paypluginparams ->get("hscancelondelete",0);
						}

						if (!$hscancelondelete) {
							continue;
						}

                                                $db = JFactory::getDBO();
                                                $sql = "SELECT * FROM #__jev_rsvp_transactions WHERE amount>0 AND paymentstate=1 AND  attendee_id=" . $transaction->attendee_id;
                                                $db->setQuery($sql);
                                                $othertransactions = $db->loadObject();
                                                if (count($othertransactions)){
                                                    continue;
                                                }
                                                
						$rpid = $attendee->rp_id;
						$this->dataModel = new JEventsDataModel();
						$this->queryModel = new JEventsDBModel($this->dataModel);

						// Find the first repeat
						$vevent = $this->dataModel->queryModel->getEventById($rsvpdata->ev_id, false, "icaldb");
						if ($rpid == 0)
						{
							$repeat = $vevent->getFirstRepeat();
						}
						else
						{
							$repeat = false;
							list($year, $month, $day) = JEVHelper::getYMD();
							$repeatdata = $this->dataModel->getEventData(intval($rpid), "icaldb", $year, $month, $day);
							if ($repeatdata && isset($repeatdata["row"]))
								$repeat = $repeatdata["row"];
						}

						if ($repeat){

							$attendeeuser = JEVHelper::getUser($attendee->user_id);
							if ($attendeeuser->id == 0 )
							{
								$name = $attendee->email_address;
								$username = $attendee->email_address;
							}
							else
							{
								$name = $attendeeuser->name;
								$username = $attendeeuser->username;
							}
							$attendee->attendstate = 0;

							$sql = "DELETE FROM #__jev_attendees WHERE id=" . $attendee->id;
							$db->setQuery($sql);
							$db->execute();

							// Notify ??
							/*
							$compparams = JComponentHelper::getParams("com_rsvppro");

							include_once(JPATH_ADMINISTRATOR . "/components/com_rsvppro/libraries/attendeehelper.php");
							$helper = new RsvpAttendeeHelper($compparams );

							$helper->notifyUser($rsvpdata, $row, $attendeeuser, $name, $username, $attendee, 'admincancelled', false);
							*/

							$lang = JFactory::getLanguage();
							$lang->load("plg_rsvppro_hikashop", JPATH_ADMINISTRATOR);

							JFactory::getApplication()->enqueueMessage(JText::sprintf("RSVP_HIKASHOP_EVENT_REGISTRATION_CANCELLED", $repeat->title()));
							// update the payment status within RSVP Pro
							//JPluginHelper::importPlugin("rsvppro");
							//$dispatcher =  JDispatcher::getInstance();
							//$dispatcher->trigger('updateHikaPaymentStatus', array($rsvpdata, $attendee, $repeat, $transaction,$order));
						}
					}
				}

			}
			return true;

		}	}

}
