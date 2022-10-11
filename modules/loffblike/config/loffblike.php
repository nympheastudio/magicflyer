<?php
if (!defined('_CAN_LOAD_FILES_'))
	exit;
?>
<link rel="stylesheet" href="<?php echo __PS_BASE_URI__."modules/".$this->name."/assets/admin/form.css";?>" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="<?php echo __PS_BASE_URI__."modules/".$this->name."/assets/admin/farbtastic/farbtastic.css";?>" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="<?php echo __PS_BASE_URI__."modules/".$this->name."/assets/admin/farbtastic/farbtastic.js";?>"></script>
<script type="text/javascript" src="<?php echo __PS_BASE_URI__."modules/".$this->name."/assets/admin/form.js";?>"></script>
<script type="text/javascript">
  $(document).ready(function() {    
   	$('.it-addrow-block .add').each( function( idx , item ){    	  
        $(item).bind('click',function(e){
			var name        = $(item).attr('id').replace('btna-','');            
            var div         = $('<div class="row"></div>');
            var spantext    = $('<span class="spantext"></span>');
            var span        = $('<span class="remove"></span>');
            var input       = $('<input type="text" name="'+name+'[]" value=""/>');
                     			           
			var parent = $(item).parent().parent();
                        
            div.append(spantext);
            div.append(input);
            div.append(span);
                        
            parent.append(div);
            number = parent.find('input').length;                  
            spantext.html(parent.find('input').length);			
            
			span.bind('click',function(){ 
				if( span.parent().find('input').value ) {
					if( confirm('Are you sure to remove this') ) {
						span.parent().remove(); 
					}
				} else {
					span.parent().remove(); 
				}				
			} );				 			
        });
	});
    
	$('.it-addrow-block .remove').bind('click',function(events){	    
	    parent = $(this).parent();        
		if( parent.find('input').value ) {
			if( confirm('Are you sure to remove this') ) {
				parent.remove();
			}
		}else {
			parent.remove();
		}		
	});
    
    $('#demo').hide();
    var f = $.farbtastic('#picker');  
    var selected;
    $('.colorwell')
      .each(function () { f.linkTo(this); $(this).css('opacity', 0.75); })
      .focus(function() {
        //$('#picker').show();
        if (selected) {
          $(selected).css('opacity', 0.75).removeClass('colorwell-selected');
        }
        f.linkTo(this);
        //p.css('opacity', 1);
        $(selected = this).css('opacity', 1).addClass('colorwell-selected');
      });
             
  });
</script>
<h3><?php echo $this->l('Lof FaceBook Like Box Configuration');?></h3>
<?php 
//register Yes - No Lang
$yesNoLang = array("0"=>$this->l('No'),"1"=>$this->l('Yes'));
$postColor  = array("light"=>$this->l('Light'),"dark"=>$this->l('Dark'));
$postTrueFalse  = array("1"=>$this->l('Show'),"0"=>$this->l('Hide'));

$targetArr = array("_blank"=>$this->l('Blank'),"_parent"=>$this->l('Parent'),"_self"=>$this->l('Self'),"_top"=>$this->l('Top'));
$imagePos  = array("left"=>$this->l('Left'),"center"=>$this->l('Center'),"right"=>$this->l('Right'));

?>
<form action="<?php echo $_SERVER['REQUEST_URI'].'&rand='.rand();?>" method="post" id="lofform">
 <input type="submit" name="submit" value="<?php echo $this->l('Update');?>" class="button" />
  <fieldset>
    <legend><img src="../img/admin/contact.gif" /><?php echo $this->l('General Setting'); ?></legend>
    <div class="lof_config_wrrapper clearfix">
      <ul>
        <?php 
            echo $this->_params->selectTag("module_theme",$themes,$this->getParamValue("module_theme",'default'),$this->l('Theme - Layout'),'class="inputbox"', 'class="row" title="'.$this->l('Select a theme').'"');                                                
			echo $this->_params->inputTag("loflink",$this->getParamValue("loflink","http://www.facebook.com/LandOfCoder"),$this->l('Page Link'),'class="text_area"','class="row"','');                                                
			echo $this->_params->inputTag("module_width",$this->getParamValue("module_width",190),$this->l('Box Width'),'class="text_area"','class="row"','');                                                
            echo $this->_params->inputTag("module_height",$this->getParamValue("module_height",600),$this->l('Box Height'),'class="text_area"','class="row"','');
            echo $this->_params->inputTag("numfans",$this->getParamValue("numfans",10),$this->l('Number Fans Box'),'class="text_area"','class="row"','');
            $color = $this->getParamValue("bdcolor","#000000")?$this->getParamValue("bdcolor","#000000"):"#000000";
            echo $this->_params->inputTag("bdcolor",$color,$this->l('Caption Link Color'),'class="text_area colorwell"','class="row"','');
           
            echo $this->_params->radioBooleanTag("lofcolor", $postColor,$this->getParamValue("lofcolor","light"),$this->l('Color Scheme'),'','class="row"','','');
            echo $this->_params->radioBooleanTag("loffaces", $postTrueFalse,$this->getParamValue("loffaces",1),$this->l('Show Faces'),'','class="row"','','');
            echo $this->_params->radioBooleanTag("lofstream", $postTrueFalse,$this->getParamValue("lofstream",1),$this->l('Show Page Stream'),'','class="row"','','');
            echo $this->_params->radioBooleanTag("lofheader", $postTrueFalse,$this->getParamValue("lofheader",1),$this->l('Show Like Box Header'),'','class="row"','','');
        ?>                 
      </ul>
      <div id="picker"></div> 
    </div>
  </fieldset>
  
<br />
  <input type="submit" name="submit" value="<?php echo $this->l('Update');?>" class="button" />
  	<fieldset><legend><img src="../img/admin/comment.gif" alt="" title="" /><?php echo $this->l('Information');?></legend>    	
    	<ul>
    	     <li>+ <a target="_blank" href="http://landofcoder.com/our-porfolios/prestashop/item/53-prestashop-lof-slider-module.html"><?php echo $this->l('Detail Information');?></li>
             <li>+ <a target="_blank" href="http://landofcoder.com/forum/forum.html?id=40"><?php echo $this->l('Forum support');?></a></li>
             <li>+ <a target="_blank" href="http://landofcoder.com/submit-request.html"><?php echo $this->l('Customization/Technical Support Via Email');?>.</a></li>
             <li>+ <a target="_blank" href="http://landofcoder.com/download/guides-docs/docs-guide-prestashop/121-prestashop13x-lofcoinslider-module.html"><?php echo $this->l('UserGuide ');?></a></li>
        </ul>
        <br />
        @copyright: <a href="http://landofcoder.com">LandOfCoder.com</a>
    </fieldset>
</form>
