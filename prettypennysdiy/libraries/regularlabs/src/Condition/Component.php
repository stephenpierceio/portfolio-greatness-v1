<?php
/**
 * @package         Regular Labs Library
 * @version         17.6.13439
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

/**
 * Class Component
 * @package RegularLabs\Library\Condition
 */
class Component
	extends \RegularLabs\Library\Condition
{
	public function pass()
	{
		return $this->passSimple(strtolower($this->request->option));
	}
}
