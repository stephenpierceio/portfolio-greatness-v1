// jQuery File Tree Plugin
//
// Version 1.0
//
// Base on the work of Cory S.N. LaViska  A Beautiful Site (http://abeautifulsite.net/)
// Dual-licensed under the GNU General Public License and the MIT License
// Icons from famfamfam silk icon set thanks to http://www.famfamfam.com/lab/icons/silk/
//
// Usage : $('#jao').jaofiletree(options);
//
// Author: Damien Barrère
// Website: http://www.crac-design.com

(function( $ ) {
  
    var options =  {
      'root'            : '/',
      'script'         : 'connectors/jaoconnector.php',
      'showroot'        : 'root',
      'onclick'         : function(elem,type,file){},
      'oncheck'         : function(elem,checked,type,file){},
      'usecheckboxes'   : true, //can be true files dirs or false
      'expandSpeed'     : 500,
      'collapseSpeed'   : 500,
      'expandEasing'    : null,
      'collapseEasing'  : null,
      'canselect'       : true
    };

    var methods = {
        init : function( o ) {
            if($(this).length==0){
                return;
            }
            $thisTree = $(this);
            $.extend(options,o);

            if(options.showroot!=''){
                checkboxes = '';
                if(options.usecheckboxes===true || options.usecheckboxes==='dirs'){
                    checkboxes = '<input type="checkbox" />';
                }
                $thisTree.html('<ul class="jaofiletree"><li class="drive directory collapsed selected">'+checkboxes+'<a href="#" data-file="'+options.root+'" data-type="dir">'+options.showroot+'</a></li></ul>');
            }
            openfolder(options.root);
        },
        open : function(dir){
            openfolder(dir);
        },
        close : function(dir){
            closedir(dir);
        },
        getchecked : function(){
            var list = new Array();            
            var ik = 0;
            $thisTree.find('input:checked + a').each(function(){
                list[ik] = {
                    type : $(this).attr('data-type'),
                    file : $(this).attr('data-file')
                }                
                ik++;
            });
	    return list;
        },
        getselected : function(){
            var list = new Array();            
            var ik = 0;
            $thisTree.find('li.selected > a').each(function(){
                list[ik] = {
                    type : $(this).attr('data-type'),
                    file : $(this).attr('data-file')
                }                
                ik++;
            });
	    return list;
        }
    };

    openfolder = function(dir) {
	    if($thisTree.find('a[data-file="'+dir+'"]').parent().hasClass('expanded')){
		return;
	    }
            var ret;
            ret = $.ajax({
                url : options.script,
                data : {dir : dir},
                context : $thisTree,
		dataType: 'json',
                beforeSend : function(){this.find('a[data-file="'+dir+'"]').parent().addClass('wait');}
            }).done(function(datas) {
                ret = '<ul class="jaofiletree" style="display: none">';
                for(ij=0; ij<datas.length; ij++){
                    if(datas[ij].type=='dir'){
                        classe = 'directory collapsed';
                        isdir = '/';
                    }else{
                        classe = 'file ext_'+datas[ij].ext;
                        isdir = '';
                    }
                    ret += '<li class="'+classe+'">'                    
                    if(options.usecheckboxes===true || (options.usecheckboxes==='dirs' && datas[ij].type=='dir') || (options.usecheckboxes==='files' && datas[ij].type=='file')){
                        ret += '<input type="checkbox" data-file="'+dir+datas[ij].file+'" data-type="'+datas[ij].type+'"/>';
                    }
                    else{
//                        ret += '<input disabled="disabled" type="checkbox" data-file="'+dir+datas[ij].file+'" data-type="'+datas[ij].type+'"/>';
                    }
                    if(datas[ij].type=='dir'){                    
                        ret += '<i class="zmdi zmdi-folder"></i>'; 
                    }
//                    if(dir+datas[ij].file === dir) {
//                            ret += '<i class="zmdi zmdi-folder-outline"></i>';        
//                    }else {
//                            ret += '<i class="zmdi zmdi-folder"></i>';        
//                    }
                        
                    ret += '<a href="#" data-file="'+dir+datas[ij].file+isdir+'" data-type="'+datas[ij].type+'">'+datas[ij].file+'</a>';
                    ret += '</li>';
                }
                ret += '</ul>';
                
                this.find('a[data-file="'+dir+'"]').parent().find(".zmdi").removeClass('zmdi-folder').addClass('zmdi-folder-outline');
                this.find('a[data-file="'+dir+'"]').parent().removeClass('wait').removeClass('collapsed').addClass('expanded');
                this.find('a[data-file="'+dir+'"]').after(ret);
                this.find('a[data-file="'+dir+'"]').next().slideDown(options.expandSpeed,options.expandEasing);

                if(options.usecheckboxes){
                    this.find('li input[type="checkbox"]').attr('checked',null);
                    this.find('a[data-file="'+dir+'"]').prev(':not(:disabled)').attr('checked','checked');
                    this.find('a[data-file="'+dir+'"] + ul li input[type="checkbox"]:not(:disabled)').attr('checked','checked');
                }

                setevents();
            }).done(function(){
                //Trigger custom event
                $thisTree.trigger('afteropen');
                $thisTree.trigger('afterupdate');
            });
    }

    closedir = function(dir) {
            $thisTree.find('a[data-file="'+dir+'"]').next().slideUp(options.collapseSpeed,options.collapseEasing,function(){$(this).remove();});
            $thisTree.find('a[data-file="'+dir+'"]').parent().removeClass('expanded').addClass('collapsed');
            setevents();
            
            //Trigger custom event
            $thisTree.trigger('afterclose');
            $thisTree.trigger('afterupdate');
            
    }

    setevents = function(){
        $thisTree.find('li a').unbind('click');
        //Bind userdefined function on click an element
        $thisTree.find('li a').bind('click', function() {
            options.onclick(this, $(this).attr('data-type'),$(this).attr('data-file'));
            if(options.usecheckboxes && $(this).attr('data-type')=='file'){
                    $thisTree.find('li input[type="checkbox"]').attr('checked',null);
                    $(this).prev(':not(:disabled)').attr('checked','checked');
                    $(this).prev(':not(:disabled)').trigger('check');
            }
            if(options.canselect){
                $thisTree.find('li').removeClass('selected');
                $(this).parent().addClass('selected');
            }
            return false;
        });
        //Bind checkbox check/uncheck
        $thisTree.find('li input[type="checkbox"]').bind('change', function() {
            options.oncheck(this,$(this).is(':checked'), $(this).next().attr('data-type'),$(this).next().attr('data-file'));
            if($(this).is(':checked')){
                $thisTree.trigger('check');
            }else{
                $thisTree.trigger('uncheck');
            }
        });
        //Bind for collapse or expand elements
        $thisTree.find('li.directory.collapsed a').bind('click', function() {methods.open($(this).attr('data-file'));return false;});
        $thisTree.find('li.directory.expanded a').bind('click', function() {methods.close($(this).attr('data-file'));return false;});        
    }

    $.fn.jaofiletree = function( method ) {
        // Method calling logic
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            //error
        }    
  };
})( jQuery );
