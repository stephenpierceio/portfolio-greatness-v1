/**
 * JEvents Component for Joomla 1.5.x
 *
 * @version     $Id$
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2015 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */

var jevcfcheckbox = {
	setvalue: function(id){
		jQuery('#pdv'+id).val( jQuery('#dv'+id).val());
	},
	changeMultiple:function(id){
		jQuery('#pdv'+id).prop('size',  jQuery('#size'+id).val());
		jQuery('#dv'+id).prop('size',  jQuery('#size'+id).val());
	},
	// Not used
	showNext:function(el, id, op){
		jevcflist.showNext(el, id, op);
	},
	newOption:function(id){
		jevcflist.newOption( id);
	},
	deleteOption: function(el){
		jevcflist.deleteOption( el);
	},
	updatePreview:function(id, op){
		var countel = 0;
		// remove exising options
		jQuery('#pdv'+id + " label").each(function(idx, opt){
			jQuery(opt).remove();
		});
		// create the new options
		jQuery("#options"+id+ " input.inputlabel").each(function(idx, el){
			$el = jQuery(el);
			if ($el.val()!="" ){
				var label = jQuery('<label>');
				label.text($el.val());
				var checkbox = jQuery('<input>', {type:'checkbox'});
				label.append(checkbox);
				jQuery('#pdv'+id).append(label);

				var rid = $el.attr('id').replace("_t_","_r_");
				rid = rid.replace("optionsfield","defaultfield");
				checkbox.prop('checked', jQuery('#'+rid).prop('checked'));
				//var br = new Element('br');
				//jQuery('#pdv'+id).appendChild(br);
			}
		});
	},
	defaultOption:function(el, id, op){
		//alert(jQuery('#'+id+"preview label").length + " " +jQuery("#options"+id + " input.inputlabel").length);
		jQuery('#'+id+"preview label").each(function(lidx, lab){
			lab = jQuery(lab);
			jQuery("#options"+id + " input.inputlabel").each(function(eidx, el){
				el = jQuery(el);
				//alert(lab.text() +":"+ el.val())
				if (lab.text() == el.val()){
					var rid = el.attr('id').replace("_t_","_r_");
					rid = rid.replace("optionsfield","defaultfield");
					rid = jQuery('#'+rid);
					var checkbox = lab.find('input');
					checkbox.prop('checked' , rid.prop('checked'));
					var vid = el.prop('id').replace("_t_","_v_");
					rid.val( jQuery('#'+vid).val());
                                        if (rid.prop('checked')){
                                            jQuery('#defaultvalue'+id).val(jQuery('#'+vid).val());
                                        }
                                        
				}
			});
		})
	}
}