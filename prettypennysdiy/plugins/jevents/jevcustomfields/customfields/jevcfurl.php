<?php

/**
 * JEvents Component for Joomla 1.5.x
 *
 * @version     $Id: jevtext.php 1569 2009-09-16 06:22:03Z geraint $
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2009 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('text');
include_once(JPATH_SITE."/plugins/jevents/jevcustomfields/customfields/JevcfFieldText.php");

class JFormFieldJevcfurl extends JevcfFieldText
{

	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Jevcfurl';
	const name = 'jevcfurl';

	public static function loadScript($field=false){

		JHtml::script( 'plugins/jevents/jevcustomfields/customfields/js/jevcfurl.js' );

		if ($field){
			$id = 'field'.$field->fieldname;
		}
		else {
			$id = '###';
		}
		ob_start();
?>
<div class='jevcffieldinput'>

	<?php
        CustomFieldsHelper::fieldtype($id, $field, self::name );                
	CustomFieldsHelper::hidden($id,  $field, self::name);
	CustomFieldsHelper::label($id,  $field, self::name);
        CustomFieldsHelper::name($id, $field, self::name);
	CustomFieldsHelper::tooltip($id,  $field);
	?>

	<div class="jevcflabel"><?php echo JText::_("JEVCF_DEFAULT_VALUE");?></div>
	<div class="jevcfinputs">
		<input type="text" name="dv[<?php echo $id;?>]" id="dv<?php echo $id;?>" size="<?php echo $field?$field->attribute('size'):50;?>"  value="<?php echo $field?$field->attribute('default'):"http://www.jevents.net";?>"  onchange="jevcfurl.setvalue('<?php echo $id;?>');"  onkeyup="jevcfurl.setvalue('<?php echo $id;?>');"/>
	</div>
	<div class="jevcfclear"></div>

	<?php
        CustomFieldsHelper::textField($id, $field, JText::_("JEVCF_PLACEHOLDER"), JText::_('JEVCF_PLACEHOLDER_DESC'), "placeholder", "placeholder","", ' onchange="jevcftext.setplaceholder(\''. $id .'\');"  onkeyup="jevcftext.setplaceholder(\''. $id .'\');"');
	CustomFieldsHelper::textField($id, $field, JText::_("JEVCF_LINKTEXT"), JText::_('JEVCF_LINKTEXT_DESC'), "linktext", "linktext");
	CustomFieldsHelper::textField($id, $field, JText::_("JEVCF_URL_TARGET"), JText::_('JEVCF_URL_TARGET_DESC'), "target", "target","_blank");
        CustomFieldsHelper::booleanField($field,"redirect[$id]", "redirect0$id", "redirect1$id", JText::_("JEVCF_URL_REDIRECT"), "redirect", JText::_("JEVCF_URL_REDIRECT_DESC"));
	CustomFieldsHelper::size($id,  $field, self::name);
	CustomFieldsHelper::maxlength($id,  $field, self::name);
	CustomFieldsHelper::required($id,  $field);
	CustomFieldsHelper::requiredMessage($id,  $field);
	CustomFieldsHelper::searchable($id,  $field);
	CustomFieldsHelper::filterOptions($id, $field);
	CustomFieldsHelper::filtermenuOptions($id, $field);
      	CustomFieldsHelper::filterDefault($id, $field);
	CustomFieldsHelper::allowoverride($id,  $field);
	CustomFieldsHelper::accessOptions($id,  $field);
        CustomFieldsHelper::readaccessOptions($id,  $field);
	CustomFieldsHelper::applicableCategories($id, $field) ;
        CustomFieldsHelper::fieldclass($id, $field);
	CustomFieldsHelper::universal($id, $field);
	?>

	<div class="jevcfclear"></div>

</div>
<div class='jevcffieldpreview'  id='<?php echo $id;?>preview'>
	<div class="previewlabel"><?php echo JText::_("JEVCF_PREVIEW");?></div>
	<div class="jevcflabel jevcfpl" id='pl<?php echo $id;?>'><?php echo $field?$field->attribute('label'):JText::_("JEVCF_FIELD_LABEL");?></div>
	<input type="text"  id="pdv<?php echo $id;?>" value="<?php echo $field?$field->attribute('default'):"http://www.jevents.net";?>" size="<?php echo $field?$field->attribute('size'):50;?>"  />
</div>
<div class="jevcfclear"></div>
		<?php
		$html = ob_get_clean();

		return CustomFieldsHelper::setField($id,  $field, $html, self::name	);

	}

	function getInput() {

		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		$placeholder = ( $this->attribute('placeholder') ? ' placeholder="'.JText::_($this->attribute('placeholder')).'"' : '' );

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $placeholder. '/>';

	}

	function fetchRequiredScript($name, &$node, $control_name)
	{
		return "JevStdRequiredFields.fields.push({'name':'" . $control_name . $name . "', 'default' :'" . $this->attribute('default') . "' ,'reqmsg':'" . trim(JText::_($this->attribute('requiredmessage'), true)) . "'}); ";

	}

	public function constructFilter($node)
	{
		$this->node = $node;
		$specialCharacters = array(" ", "-" , ",", ".");
		$this->filterType = str_replace($specialCharacters,"",$this->attribute("name"));
		$this->filterLabel = JevcfField::varempty($this->attribute("filterlabel")) ? $this->attribute("label") : $this->attribute("filterlabel");
		//$this->filterNullValue = JevcfField::varempty($this->attribute("filterdefault"))?(JevcfField::varempty($this->attribute("default"))?"":$this->attribute("default")):$this->attribute("filterdefault");
		$this->filterNullValue = $this->attribute("filterdefault");
		$this->filter_value = $this->filterNullValue;
		$this->map = "csf" . $this->filterType;

		$registry = JRegistry::getInstance("jevents");
		$this->indexedvisiblefilters = $registry->get("indexedvisiblefilters", false);
		if ($this->indexedvisiblefilters === false)
			return;

		// This is our best guess as to whether this filter is visible on this page.
		$this->visible = in_array("customfield", $this->indexedvisiblefilters);

		// If using caching should disable session filtering if not logged in
		$cfg = JEVConfig::getInstance();
		$useCache = (int) $cfg->get('com_cache', 0);
		$user = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		if ((int) JRequest::getVar('filter_reset', 0))
		{
			JFactory::getApplication()->setUserState( $this->filterType.'_fv_ses', $this->filterNullValue ); 			
			$this->filter_value = $this->filterNullValue;
		}
		// ALSO if this filter is not visible on the page then should not use filter value - does this supersede the previous condition ???
		else if (!$this->visible)
		{
			$this->filter_value = JRequest::getVar($this->filterType . '_fv', $this->filterNullValue, "request", "string");
		}
		else
		{
			$this->filter_value = JFactory::getApplication()->getUserStateFromRequest($this->filterType . '_fv_ses', $this->filterType . '_fv', $this->filterNullValue, "string");
		}

	}

	public function createJoinFilter()
	{
		if (is_string($this->filter_value) && trim($this->filter_value) == $this->filterNullValue)
			return "";
		$join = " #__jev_customfields AS $this->map ON det.evdet_id=$this->map.evdet_id";
		$db = JFactory::getDBO();
		$filter =  "$this->map.name=" . $db->Quote($this->attribute('name')) . " AND $this->map.value LIKE (" . $db->Quote($this->filter_value . "%") . ")";
		return $join . " AND ". $filter;
	}

	public function createFilter()
	{
		if (is_string($this->filter_value) && trim($this->filter_value) == $this->filterNullValue)
			return "";
		return "$this->map.id IS NOT NULL";
	}

	public function createFilterHTML()
	{
		$filterList = array();
		$filterList["title"] = "<label class='evdate_label' for='" . $this->filterType . "_fv'>" . $this->filterLabel . "</label>";
		$filterList["html"] = $this->getFilterInput($this->filterType . "_fv", $this->filter_value, $this->node, "");

		$script = "try {JeventsFilters.filters.push({id:'" . $this->filterType . "_fv',value:'" . addslashes($this->filterNullValue) . "'});} catch (e) {}";
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script);

		return $filterList;

	}

	public function convertValue($value, &$node)
	{
		$target = $this->attribute('target') ? " target='" . $this->attribute('target') . "' " : "";
		$linktext = $this->attribute('linktext') ? trim(JText::_($this->attribute('linktext'))) : $value;

		if ($value != "")
		{
			$hv = $this->attribute("hiddenvalue");
			if (strpos($hv, "<a href=") !== 0 && strpos($hv, "http") === 0)
			{
				$node->element->attributes()->hiddenvalue = "<a href='$hv' $target >$hv</a>";
			}
			if (!is_string($value)){
				$x = 1;
			}
			if (strpos($value, "http://") === false && strpos($value, "https://") === false && strpos($value, "ftp://") === false && strpos($value, "mailto:") === false)
			{
				// convert email addresses to mailto links
				if (strpos($value, "@")>0 && strpos($value, "/")===false){
					$value = "mailto:" . $value;
				}
				else if (strpos($value, "/")!==0) {
					$value = "http://" . $value;
				}
			}

			// redirect ONLY if viewing event detail 
			if ($this->attribute("redirect")==1)
			{
				if (JRequest::getString("jevtask") == "icalrepeat.detail" || JRequest::getString("jevtask") == "icalevent.detail")
				{
					JFactory::getApplication()->redirect($value);
				}
			}

			return "<a href='$value' $target>$linktext</a>";
		}

	}

	/**
	 * Magic setter; allows us to set protected values
	 * @param string $name
	 * @return nothing
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	public function bindField($fieldid)	{
		include_once("JevcfField.php");
		return JevcfField::bindFieldWithVarkeys($fieldid, get_object_vars($this));
	}

}