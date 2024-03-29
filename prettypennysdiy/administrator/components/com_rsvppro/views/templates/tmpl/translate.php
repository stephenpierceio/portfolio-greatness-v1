<?php

defined('_JEXEC') or die;

echo JToolbar::getInstance('toolbar')->render('toolbar');
//jimport('joomla.html.html.bootstrap');
JHtml::_('formbehavior.chosen', 'select');

$ticketeditor =  JFactory::getEditor();
$editor =  JFactory::getEditor('none');
if ($this->item->id === 0)
	exit();
?>
<div class='jevrsvppro rsvptranslatemplate'>
	<div id="jevents">
		<form action="index.php" method="post" name="adminForm"  id="adminForm" >
			<div class="row-fluid">
				<input type="hidden" name="customise" value="<?php echo JRequest::getInt("customise"); ?>" />
				<input type="hidden" name="cid[]" value="<?php echo (JRequest::getInt("customise") && $this->item->istemplate) ? 0 : $this->item->id; ?>" />
				<input type="hidden" name="template_id" id="id" value="<?php echo (JRequest::getInt("customise") && $this->item->istemplate) ? 0 : $this->item->id; ?>" />
				<input type="hidden" name="istemplate" id="istemplate" value="<?php echo JRequest::getInt("customise") ? 0 : 1; ?>" />
				<input type="hidden" name="evid" id="evid" value="<?php echo JRequest::getInt("evid"); ?>" />
				<input type="hidden" name="lang" id="lang" value="<?php echo $this->lang; ?>" />
				<input type="hidden" name="translation_id" id="translation_id" value="<?php echo $this->item->translation_id; ?>" />

				<script type="text/javascript" >
					function submitbutton(pressbutton) {
						if (pressbutton == 'cancel' || pressbutton == 'templates.cancel') {
							Joomla.submitform( pressbutton , document.adminForm);
							return;
						}
						var form = document.adminForm;
<?php
if (!JRequest::getInt("customise"))
{
	echo $editor->save('description');
}
echo $ticketeditor->save('ticket');
?>
		// do field validation
		if (form.title.value == "") {
			alert ( "<?php echo html_entity_decode(JText::_('Missing Title')); ?>" );
		}
		else {
			Joomla.submitform( pressbutton , document.adminForm);
		}
	}

				</script>
				<div class="form-horizontal span12">
					<div class="control-group">
						<label class="control-label"><?php echo JText::_('RSVP_TEMPLATE_TITLE'); ?></label>
						<div class="controls" >
							<input class="inputbox" type="text" name="title" size="50" maxlength="100" value="<?php echo htmlspecialchars($this->item->title, ENT_QUOTES, 'UTF-8'); ?>" />
						</div>
					</div>

					<div class="control-group">
						<label class="control-label"><?php echo JText::_('RSVP_TEMPLATE_DESCRIPTION'); ?></label>

						<div class="controls" >
							<?php
							// parameters : areaname, content, hidden field, width, height, rows, cols
							echo $editor->display('description', htmlspecialchars($this->item->description, ENT_QUOTES, 'UTF-8'), "80%", 100, '70', '10', false);
							?>
						</div>
					</div>

				<fieldset class='jevconfig <?php echo $this->item->withfees ? "" : "jevconfighidden"; ?>'>
					<legend class="toggleEnlarge">
						<?php echo JText::_('RSVP_CONFIG_PAYMENTS'); ?><span class="spanclosed"> [+]</span><span class="spanopen"> [-]</span>
					</legend>
					<div class="largeDetail">
						<div class="largeDetailContent">
							<?php
							$groups = $this->params->getFieldsets();
							if (count($groups) > 0)
							{
								?>
								<ul class="nav nav-tabs" id="configTabs">
									<?php
									foreach ($groups as $group => $element)
									{
										$count = $this->params->getFieldCountByFieldSet($group);
										if ($group != "xmlfile" && $group != "RSVP_ATTENDANCE_MESSAGES" && $group != "RSVP_SESSION_OPTIONS" && $count > 0)
										{
											$active = (strtolower($group) == "rsvp_currency_formatting") ? ' class="active"' : '';
											?>
											<li  <?php echo $active; ?> ><a data-toggle="tab" href="#<?php echo strtolower(str_replace(array(" ","."), "_", $group)); ?>"><?php echo JText::_($group); ?></a></li>
											<?php
										}
									}
									?>
								</ul>
								<?php
								// Now the tab content
								echo JHtml::_('bootstrap.startPane', 'configTabs', array('active' => 'rsvp_currency_formatting'));
								foreach ($groups as $group => $element)
								{
									$count = $this->params->getFieldCountByFieldSet($group);
									if ($group != "RSVP_ATTENDANCE_MESSAGES" && $group != "RSVP_SESSION_OPTIONS" && $count > 0)
									{
										echo JHtml::_('bootstrap.addPanel', "configTabs", strtolower(str_replace(array(" ","."), "_", $group)));
										$customfields = array();
										$this->params->render('params', $group, $customfields);
										?>
										<div  class="span11">
											<?php
											foreach ($customfields as $cf)
											{
												echo "<div class='controlgroup'>" . $cf["label"] . "<div class='controls'>" . $cf["input"] . "</div></div>";
												if ($cf["type"] == "Editor")
												{
													$editorfields[] = $cf["name"];
												}
											}
											?>
										</div>
										<?php
										echo JHtml::_('bootstrap.endPanel');
									}
								}
								echo JHtml::_('bootstrap.endPane', 'configTabs');
							}
							else
							{
								echo $this->params->render();
							}
							?>
						</div>
					</div>
				</fieldset>

				<fieldset class='jevticket <?php echo $this->item->withticket ? "" : "jevtickethidden"; ?>'>
					<legend class="toggleEnlarge">
						<?php echo JText::_('RSVP_TICKET'); ?><span class="spanclosed"> [+]</span><span class="spanopen"> [-]</span>
					</legend>
					<div class="largeDetail">
						<div class="largeDetailContent">
							<?php
							echo $ticketeditor->display('ticket', htmlspecialchars($this->item->ticket, ENT_QUOTES, 'UTF-8'), 500, 150, '70', '10', false);
							?>
							<h4><?php echo JText::_("RSVP_SELECT_FIELD_TO_INSERT"); ?> : </h4>
							<select onchange="ticketsEditorPlugin.insert('ticket','ticketfields' )" id="ticketfields">
								<option value="Select ...:">Select ...</option>
								<optgroup label="<?php echo JText::_("RSVP_EVENT_FIELDS", true); ?>" >
									<option value="EVENT"><?php echo JText::_("RSVP_EVENT_TITLE"); ?></option>
									<option value="DATE}%Y %m %d{/DATE"><?php echo JText::_("RSVP_EVENT_DATE"); ?></option>
									<option value="LOCATION"><?php echo JText::_("RSVP_EVENT_LOCATION"); ?></option>
									<option value="CATEGORY"><?php echo JText::_("RSVP_EVENT_CATEGORY");?></option>
									<option value="LINK"><?php echo JText::_("RSVP_EVENT_LINK"); ?></option>
									<option value="TOTALFEES"><?php echo JText::_("RSVP_TOTALFEES"); ?></option>
									<option value="FEESPAID"><?php echo JText::_("RSVP_FEESPAID"); ?></option>
									<option value="BALANCE"><?php echo JText::_("RSVP_BALANCE"); ?></option>
									<option value="CREATOR"><?php echo JText::_("RSVP_EVENT_CREATOR"); ?></option>
									<option value="REGDATE}%Y-%m-%d{/REGDATE"><?php echo JText::_("RSVP_EVENT_REGISTRATION_DATE"); ?></option>
									<option value="REGID}Ticket Number : %s{/REGID"><?php echo JText::_("RSVP_EVENT_REGISTRATION_ID"); ?></option>
									<option value="GUESTNUM}Guest Number : %s{/GUESTNUM"><?php echo JText::_("RSVP_EVENT_GUEST_NUMBER"); ?></option>
									<!--<option value="BARCODE"><?php echo JText::_("RSVP_EVENT_BARCODE"); ?></option>/-->
									<option value="QR"><?php echo JText::_("RSVP_EVENT_QRCODE"); ?></option>
									<option value="CUSTOM"><?php echo JText::_("RSVP_EVENT_CUSTOMFIELD_SUMMARY"); ?></option>
									<option value="REPEATSUMMARY"><?php echo JText::_("RSVP_EVENT_REPEATSUMMARY"); ?></option>
								</optgroup>
								<optgroup label="<?php echo JText::_("RSVP_TEMPLATE_FIELDS", true); ?>" class="templatefields">
								</optgroup>
							</select>
							<table cellpadding="5" cellspacing="0" border="0" >
							<?php
							$ticketparams = $this->templateparams->get("whentickets");
							if ($ticketparams == "")
							{
								echo "<tr><td><h3 class='error'>" . JText::_('RSVP_TICKETS_ONLY_FOR_FULLY_PAID_NO_MODIFY_NO_CANCEL_ATTENDEES') ."<h3></td></tr>";
								$ticketparams = array("paidnochangecancel");
							}
							?>
								<tr>
									<td align="left"><h4><?php echo JText::_('RSVP_WHEN_OFFER_TICKETS'); ?></h4></td>
								</tr>
								<tr>
									<td >
										<label><input class="inputbox" type="checkbox" name="params[whentickets][]"  value="cancancel"  <?php echo in_array("cancancel", $ticketparams) ? "checked='checked'" : ""; ?> /><?php echo JText::_("RSVP_WHEN_CAN_STILL_BE_CANCELLED"); ?></label><br/>
										<label><input class="inputbox" type="checkbox" name="params[whentickets][]"  value="canchange"  <?php echo in_array("canchange", $ticketparams) ? "checked='checked'" : ""; ?> /> <?php echo JText::_("RSVP_WHEN_CAN_STILL_BE_CHANGED"); ?></label><br/>
										<label><input class="inputbox" type="checkbox" name="params[whentickets][]"  value="outstandingbalance"   <?php echo in_array("outstandingbalance", $ticketparams) ? "checked='checked'" : ""; ?> /> <?php echo JText::_("RSVP_WITH_OUTSTANDING_BALANCE"); ?></label>
									</td>
								</tr>
							</table>

						</div>
					</div>
				</fieldset>

				<fieldset class="jevtemplatefields">
					<legend class="toggleEnlarge">
						<?php echo JText::_('RSVP_TEMPLATE_FIELDS'); ?><span class="spanclosed"> [+]</span><span class="spanopen"> [-]</span>
					</legend>
					<div class="largeDetail">
						<div class="largeDetailContent">
							<div id="jevtemplate_fields">
								<?php echo RsvpHelper::getFieldScript();?>
							</div>
							<div id="rsvpfields">
								<?php
								foreach ($this->item->fields as $field)
								{
									$fieldhtml = $field->html;
									?>
									<div class='rsvpfield' id='field<?php echo $field->field_id; ?>'>
										<!--input id="deleteFieldButtonfield<?php echo $field->field_id; ?>" type="button" value="<?php echo JText::_("RSVP_DELETE_FIELD"); ?>" class="deleteFieldButton"/>//-->
										<input id="closeFieldButtonfield<?php echo $field->field_id; ?>" type="button" value="<?php echo JText::_("RSVP_CLOSE_FIELD"); ?>" class="closeFieldButton"/>
										<?php
										echo $fieldhtml;
										?>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>
				</fieldset>

				<fieldset class='jevmessages'>
					<legend class="toggleEnlarge">
						<?php echo JText::_('RSVP_CUSTOM_MESSAGES'); ?><span class="spanclosed"> [+]</span><span class="spanopen"> [-]</span>
					</legend>
					<div class="largeDetail">
						<div class="largeDetailContent">
							<?php
							$groups = $this->params->getFieldsets();
							foreach ($groups as $group => $element)
							{
								$count = $this->params->getFieldCountByFieldSet($group);
								if ($group == "RSVP_ATTENDANCE_MESSAGES" && $count > 0)
								{
									$customfields = array();
									$this->params->render('params', $group, $customfields);
									?>
									<div  class="span11">
										<?php
										foreach ($customfields as $cf)
										{
											echo "<div class='controlgroup'>" . $cf["label"] . "<div class='controls'>" . $cf["input"] . "</div></div>";
											if ($cf["type"] == "Editor")
											{
												$editorfields[] = $cf["name"];
											}
										}
										?>
									</div>
									<?php
								}
							}
							?>
						</div>
					</div>
				</fieldset>

			</div>
			</div>
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="task" value="defaults.edit" />
			<input type="hidden" name="act" value="" />
			<input type="hidden" name="option" value="<?php echo RSVP_COM_COMPONENT; ?>" />
			<input type="hidden" name="Itemid" value="<?php echo JRequest::getInt("Itemid", 0); ?>" />
			<?php
			if (JRequest::getString("tmpl") == "component")
			{
				?>
				<input type="hidden" name="tmpl" value="component" />
			<?php } ?>
		</form>
	</div>
</div>

<?php

// make sure the form isn't too big!'
$max_input_vars = intval(@ini_get("max_input_vars"));
if ($max_input_vars == 0)
{
	$max_input_vars = 999999;
}
?>
<script type="text/javascript">
	var inputvars = $('adminForm').getElements('input');
	var selectvars = $('adminForm').getElements('select');
	var textareavars = $('adminForm').getElements('textarea');
	if(inputvars.length+ selectvars.length+textareavars.length > <?php echo $max_input_vars; ?> * 0.90 ){
		alert("<?php echo JText::_("RSVP_FORM_GETTING_CLOSE_TO_MAXIMUM_SIZE_CHECK_HTACESS_SETTINGS", true) ?>\n"+(inputvars.length+ selectvars.length+textareavars.length)+"  vs " + <?php echo $max_input_vars; ?>);
	}
</script>

<script type="text/javascript" >
	window.setTimeout("setupRSVPTemplateBootstrap()", 500);

	function setupRSVPTemplateBootstrap(){
		(function($){
			// Turn radios into btn-group
			$('.radio.btn-group label').addClass('btn');
			var el = $(".radio.btn-group label:not(.active)");

			// Isis template and others may already have done this so remove these!
			$(".radio.btn-group label:not(.active)").unbind('click');

			$(".radio.btn-group label:not(.active)").click(function() {
				var label = $(this);
				var input = $('#' + label.attr('for'));
				if (!input.prop('checked') && !input.prop('disabled')) {
					label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
					if (input.prop('value')!=0){
						label.addClass('active btn-success');
					}
					else {
						label.addClass('active btn-danger');
					}
					input.prop('checked', true);
				}
			});

			// Turn checkboxes into btn-group
			$('.checkbox.btn-group label').addClass('btn');

			// Isis template and others may already have done this so remove these!
			$(".checkbox.btn-group label").unbind('click');

			$(".checkbox.btn-group label").click(function(event) {
				event || (event = window.event);
				var label = $(this);
				var input = $('#' + label.attr('for'));
				//alert(label.val()+ " checked? "+input.prop('checked')+ " disabled? "+input.prop('disabled')+ " label disabled? "+label.hasClass('disabled'));
				if (input.prop('disabled')) {
					label.removeClass('active btn-success btn-danger btn-primary');
					input.prop('checked', false);
					event.stopImmediatePropagation();
					return false;
				}
				if (!input.prop('checked')) {
					if (input.prop('value')!=0){
						label.addClass('active btn-success');
					}
					else {
						label.addClass('active btn-danger');
					}
				}
				else {
					label.removeClass('active btn-success btn-danger btn-primary');
				}
				// bootstrap takes care of the checkboxes themselves!
			});

			$(".btn-group input[type='checkbox']").each(function() {
				var input = $(this);
				input.css('display','none');
			});
		})(jQuery);

		initialiseRSVPTemplateBootstrapButtons();
	}

	function initialiseRSVPTemplateBootstrapButtons(){
		(function($){
			// this doesn't seem to find just the checked ones!'
			//$(".btn-group input[checked=checked]").each(function() {
			$(".btn-group input").each(function() {
				var label = $("label[for=" + $(this).attr('id') + "]");
				var elem = $(this);
				if (elem.prop('disabled')) {
					label.addClass('disabled');
					label.removeClass('active btn-success btn-danger btn-primary');
					return;
				}
				label.removeClass('disabled');
				if (!elem.prop('checked')) {
					label.removeClass('active btn-success btn-danger btn-primary');
					return;
				}
				if (elem.prop('value')!=0){
					label.addClass('active btn-success');
				}
				else {
					label.addClass('active btn-danger');
				}
			});

		})(jQuery);
	}

</script>
