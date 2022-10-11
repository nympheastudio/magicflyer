<?php 
class likeboxfree extends Module {
	function __construct(){
		$this->name = 'likeboxfree';
		$this->tab = 'social_networks';
		$this->version = '2.1';
        $this->author = 'MyPresta.eu';
        $this->dir = '/modules/likeboxfree/';
		parent::__construct();
        $this->trusted();
		$this->displayName = $this->l('Fanpage Likebox Free');
		$this->description = $this->l('This module add special likebox block with your fanpage on Facebook. Now anybody can like your facebook fanpage!');
        $this->mkey="freelicense";
        if (@file_exists('../modules/'.$this->name.'/key.php'))
            @require_once ('../modules/'.$this->name.'/key.php');
        else if (@file_exists(dirname(__FILE__) . $this->name.'/key.php'))
            @require_once (dirname(__FILE__) . $this->name.'/key.php');
        else if (@file_exists('modules/'.$this->name.'/key.php'))
            @require_once ('modules/'.$this->name.'/key.php');                        
        $this->checkforupdates();
	}

    function checkforupdates(){
        if (isset($_GET['controller']) OR isset($_GET['tab'])){
            if (Configuration::get('update_'.$this->name) < (date("U")>86400)){
                $actual_version = likeboxfreeUpdate::verify($this->name,$this->mkey,$this->version);
            }
            if (likeboxfreeUpdate::version($this->version)<likeboxfreeUpdate::version(Configuration::get('updatev_'.$this->name))){
                $this->warning=$this->l('New version available, check www.MyPresta.eu for more informations');
            }
        }
    }
    function trusted(){
            if (_PS_VERSION_ >= "1.6.0.8"){
                if (isset($_GET['controller'])){
                    if ($_GET['controller']=="AdminModules"){
                        if (defined('self::CACHE_FILE_TRUSTED_MODULES_LIST')==true){
                            $context = Context::getContext();
                    		$theme = new Theme($context->shop->id_theme);
                            $xml= simplexml_load_string(file_get_contents(_PS_ROOT_DIR_.self::CACHE_FILE_TRUSTED_MODULES_LIST));
                            if ($xml){
                                $css=$xml->modules->addChild('module');
                                $css->addAttribute('name',$this->name);
                                $xmlcode=$xml->asXML();
                                if (!strpos(file_get_contents(_PS_ROOT_DIR_.self::CACHE_FILE_TRUSTED_MODULES_LIST),$this->name))
                                    file_put_contents(_PS_ROOT_DIR_.self::CACHE_FILE_TRUSTED_MODULES_LIST,$xmlcode);
                            }
                            if (defined('self::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST')==true){
                                $xml= simplexml_load_string(file_get_contents(_PS_ROOT_DIR_.self::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST));
                                //$xml=new SimpleXMLElement('<modules/>');
                                //$cs=$xml->addChild('modules');
                                if ($xml){
                                $css=$xml->addChild('module');
                                $css->addChild('id',0);
                                $css->addChild('name',"<![CDATA[".$this->name."]]>");
                                $xmlcode=$xml->asXML();
                                $xmlcode=str_replace('&lt;',"<",$xmlcode);
                                $xmlcode=str_replace('&gt;',">",$xmlcode);
                                if (!strpos(file_get_contents(_PS_ROOT_DIR_.self::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST),$this->name))
                                    file_put_contents(_PS_ROOT_DIR_.self::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST,$xmlcode);                                    
                                }
                            }
                        }
                    }
                }
            }
        }    
    
	function install(){
        if (parent::install() == false 
        OR !Configuration::updateValue('update_'.$this->name,'0')
	    OR $this->registerHook('rightColumn') == false
	    OR $this->registerHook('leftColumn') == false
	    OR $this->registerHook('home') == false
	    OR $this->registerHook('footer') == false
	    OR Configuration::updateValue('likeboxfree_position', '2') == false
        OR Configuration::updateValue('lbf_width', '191') == false
        OR Configuration::updateValue('lbf_height', '300') == false
        OR Configuration::updateValue('lbf_hide_cover', '0') == false
        OR Configuration::updateValue('lbf_show_facepile', '1') == false
        OR Configuration::updateValue('lbf_show_posts', '0') == false
        OR Configuration::updateValue('lbf_hide_cta', '0') == false
        OR Configuration::updateValue('lbf_small_header', '0') == false
        OR Configuration::updateValue('lbf_url', 'https://www.facebook.com/mypresta') == false
        ){
            return false;
        }
        return true;
	}
    
	public function getContent(){
	   $output="";
		if (Tools::isSubmit('submit_settings')){
            
            Configuration::updateValue('likeboxfree_position', Tools::getValue('new_likebox_position'), true);
			Configuration::updateValue('lbf_width', Tools::getValue('lbf_width'), true);
            Configuration::updateValue('lbf_height', Tools::getValue('lbf_height'), true);
            Configuration::updateValue('lbf_hide_cover', Tools::getValue('lbf_hide_cover'), true);
            Configuration::updateValue('lbf_show_facepile', Tools::getValue('lbf_show_facepile'), true);
            Configuration::updateValue('lbf_show_posts', Tools::getValue('lbf_show_posts'), true);
            Configuration::updateValue('lbf_hide_cta', Tools::getValue('lbf_hide_cta'), true);
            Configuration::updateValue('lbf_small_header', Tools::getValue('lbf_small_header'), true);
            Configuration::updateValue('lbf_url', Tools::getValue('lbf_url'), true);
            Configuration::updateValue('likeboxfree_fanpageurl', Tools::getValue('new_likebox_fanpageurl'), true);
            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';                                    
        }	   
        $output.="";
        return $output.$this->displayForm();
	}

	public function displayForm(){
	   $likebox_showstream_checked="0";
       $likebox_showheader_checked="0";
       $likebox_showfaces_checked="0";
       
	   
	   $likebox_position = Configuration::get('likeboxfree_position');	
		$new_likebox1=""; $new_likebox2=""; $new_likebox3=""; $new_likebox4="";
		if ($likebox_position=="4"){$new_likebox4="checked=\"yes\"";}
	   	if ($likebox_position=="3"){$new_likebox3="checked=\"yes\"";}
		if ($likebox_position=="2"){$new_likebox2="checked=\"yes\"";}
		if ($likebox_position=="1"){$new_likebox1="checked=\"yes\"";}

       
        $likebox_fanpageurl = Configuration::get('likeboxfree_fanpageurl');
        $likebox_width = Configuration::get('likeboxfree_width');
        $likebox_height = Configuration::get('likeboxfree_height');
        $likebox_colorscheme = Configuration::get('likeboxfree_colorscheme');
            if ($likebox_colorscheme=="light"){$selected_light="SELECTED"; $selected_dark=""; $likebox_colorscheme_bg="white";}
            if ($likebox_colorscheme=="dark"){$selected_dark="SELECTED"; $selected_light=""; $likebox_colorscheme_bg="black";}
        $likebox_showfaces = Configuration::get('likeboxfree_showfaces');
            if ($likebox_showfaces=="1"){$likebox_showfaces_checked="checked='YES'";}
        $likebox_bordercolor = Configuration::get('likeboxfree_bordercolor');  
        $likebox_bgcolor = Configuration::get('likeboxfree_bgcolor');  
        $likebox_showstream = Configuration::get('likeboxfree_showstream');
            if ($likebox_showstream=="1"){$likebox_showstream_checked="checked='YES'";}
        $likebox_showheader = Configuration::get('likeboxfree_showheader');
            if ($likebox_showheader=="1"){$likebox_showheader_checked="checked='YES'";}
        
        $likebox_bgon=Configuration::get('likeboxfree_bgon');
            if ($likebox_bgon=="1"){$likebox_bgon_checked="checked='YES'";$likebox_colorscheme_bg="#".$likebox_bgcolor;}else{$likebox_bgon_checked="";}   
            
        
		return'
        <script type="text/javascript" src="../modules/likeboxfree/jscolor/jscolor.js"></script>
        <img src="'.$this->_path.'logo.gif" alt="" title="" style="margin-bottom:10px;"/>
        <iframe src="//apps.facepages.eu/somestuff/onlyexample.html" width="100%" height="150" border="0" style="border:none; dispaly:block; margin:auto;"></iframe>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <div style="display:block; margin:auto; overflow:hidden; ">
                    <div style="clear:both; display:block; ">
                        <fieldset>
            				<legend>'.$this->l('Likebox configuration').'</legend>
                            <h3 style="margin-bottom:0px; padding-bottom:0px;">'.$this->l('LikeBox Visual Settings').'</h3>
                            <hr style="margin-top:5px;">
                            
                            
			                <div style="clear:both;display:block;">
							    <label>'.$this->l('Left column').':</label>
								<div class="margin-form" valign="middle">
			                        <div style="margin-top:7px;">
									<input type="radio" name="new_likebox_position" value="1" '.$new_likebox1.'> '.$this->l('yes').'
			                        </div>
								</div>
			                </div>
			                <div style="clear:both;display:block;">
							    <label>'.$this->l('Right column').':</label>
								<div class="margin-form" valign="middle">
			                        <div style="margin-top:7px;">
									<input type="radio" name="new_likebox_position" value="2" '.$new_likebox2.'> '.$this->l('yes').'
			                        </div>
								</div>
			                </div>
			                <div style="clear:both;display:block;">
							    <label>'.$this->l('Homepage').':</label>
								<div class="margin-form" valign="middle">
			                        <div style="margin-top:7px;">
									<input type="radio" name="new_likebox_position" value="3" '.$new_likebox3.'> '.$this->l('yes').'
			                        </div>
								</div>
			                </div>
							
			                <div style="clear:both;display:block;">
							    <label>'.$this->l('Footer').':</label>
								<div class="margin-form" valign="middle">
			                        <div style="margin-top:7px;">
									<input type="radio" name="new_likebox_position" value="4" '.$new_likebox4.'> '.$this->l('yes').'
			                        </div>
								</div>
			                </div>					
                            
            				<label>'.$this->l('Facebook Page URL').'</label>
            					<div class="margin-form">
            						<input type="text" style="width:400px;" value="'.Configuration::get('lbf_url').'"  name="lbf_url">
                                    <p class="clear">'.$this->l('The URL of the Facebook Page for LikeBox').'</p>
                                </div>
                                 
            				<label>'.$this->l('Width').'</label>
            					<div class="margin-form">
            						<input type="text" style="width:100px;" value="'.Configuration::get('lbf_width').'" name="lbf_width">
                                    <p class="clear">'.$this->l('The width of the LikeBox plugin').'</p>
                                </div> 
                   
            				<label>'.$this->l('Height').'</label>
            					<div class="margin-form">
            						<input type="text" style="width:100px;" value="'.Configuration::get('lbf_height').'"  name="lbf_height">
                                    <p class="clear">'.$this->l('The height of the LikeBox plugin').'</p>
                                </div>
                                                  
            				<label>'.$this->l('Hide cover').'</label>
            					<div class="margin-form">
            						<input type="checkbox" name="lbf_hide_cover" value="1" '.(Configuration::get('lbf_hide_cover')==1 ? 'checked="yes"':'').'>
                                    <p class="clear">'.$this->l('Hide cover photo in the header').'</p>
                                </div>
                            
                            <label>'.$this->l('Profile pictures').'</label>
            					<div class="margin-form">
            						<input type="checkbox" name="lbf_show_facepile" value="1" '.(Configuration::get('lbf_show_facepile')==1 ? 'checked="yes"':'').'>
                                    <p class="clear">'.$this->l('Show profile photos when friends like this').'</p>
                                </div>
                            
                            <label>'.$this->l('Show posts').'</label>
            					<div class="margin-form">
            						<input type="checkbox" name="lbf_show_posts" value="1" '.(Configuration::get('lbf_show_posts')==1 ? 'checked="yes"':'').'>
                                    <p class="clear">'.$this->l('Show posts from the timeline.').'</p>
                                </div>
                                
                            <label>'.$this->l('Call to action button').'</label>
            					<div class="margin-form">
            						<input type="checkbox" name="lbf_hide_cta" value="1" '.(Configuration::get('lbf_hide_cta')==1 ? 'checked="yes"':'').'>
                                    <p class="clear">'.$this->l('Hide the custom call to action button (if available)').'</p>
                                </div>   
                                                             
                             <label>'.$this->l('Small header').'</label>
            					<div class="margin-form">
            						<input type="checkbox" name="lbf_small_header"  value="1" '.(Configuration::get('lbf_small_header')==1 ? 'checked="yes"':'').'>
                                    <p class="clear">'.$this->l('Use the small header instead').'</p>
                                </div>     
                                
                                                                                                                                                         
                                <div align="center">
            				        <input type="submit" name="submit_settings" value="'.$this->l('Save Settings').'" class="button" />
                                </div>
                        </fieldset>                    
                    </div>
                    
                   <div style="hright:auto; clear:both; display:block; margin-top:20px; text-align:center;">
                        <fieldset>
                            <legend>'.$this->l('Likebox Preview').'</legend>
                            <div id="likeboxpreview">
                            <div id="fb-root"></div>
                            <script>(function(d, s, id) {
                              var js, fjs = d.getElementsByTagName(s)[0];
                              if (d.getElementById(id)) return;
                              js = d.createElement(s); js.id = id;
                              js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";
                              fjs.parentNode.insertBefore(js, fjs);
                            }(document, \'script\', \'facebook-jssdk\'));</script>
                            <div class="fb-page" data-width="'.Configuration::get('lbf_width').'" data-height="'.Configuration::get('lbf_height').'" data-href="'.Configuration::get('lbf_url').'" data-small-header="'.(Configuration::get('lbf_small_header')==1 ? 'true':'false').'" data-hide-cta="'.(Configuration::get('lbf_hide_cta')==1 ? 'true':'false').'" data-hide-cover="'.(Configuration::get('lbf_hide_cover')==1 ? 'true':'false').'" data-show-facepile="'.(Configuration::get('lbf_show_facepile')==1 ? 'true':'false').'" data-show-posts="'.(Configuration::get('lbf_show_posts')==1 ? 'true':'false').'"><div class="fb-xfbml-parse-ignore"><blockquote cite="'.Configuration::get('lbf_url').'"><a href="'.Configuration::get('lbf_url').'"></a></blockquote></div></div>
                            </div>
                        </fieldset>
                    </div>
                    
                    <div style="diplay:block; clear:both; margin-bottom:10px;">
		</div>'.$this->l('like us on Facebook').'</br><iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Ffacebook.com%2Fmypresta&amp;send=false&amp;layout=button_count&amp;width=120&amp;show_faces=true&amp;font=verdana&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=276212249177933" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:21px; margin-top:10px;" allowtransparency="true"></iframe>
        '.'<div style="float:right; text-align:right; display:inline-block; margin-top:10px; font-size:10px;">
        '.$this->l('Proudly developed by').' <a href="http://mypresta.eu" style="font-weight:bold; color:#B73737">MyPresta<font style="color:black;">.eu</font></a>
        </div>

        </div>
		</form>
        ';
	}   
   
    public function setlikeboxoptions(){
        $likebox_showstream_checked="0";
        $likebox_showheader_checked="0";
        $likebox_showfaces_checked="0";
        $likebox_fanpageurl = Configuration::get('likeboxfree_fanpageurl');
        $likebox_width = Configuration::get('likeboxfree_width');
        $likebox_height = Configuration::get('likeboxfree_height');
        $likebox_colorscheme = Configuration::get('likeboxfree_colorscheme');
            if ($likebox_colorscheme=="light"){$selected_light="SELECTED"; $selected_dark=""; $likebox_colorscheme_bg="white";}
            if ($likebox_colorscheme=="dark"){$selected_dark="SELECTED"; $selected_light=""; $likebox_colorscheme_bg="black";}
        $likebox_showfaces = Configuration::get('likeboxfree_showfaces');
            if ($likebox_showfaces=="1"){$likebox_showfaces_checked="checked='YES'";}
        $likebox_bordercolor = Configuration::get('likeboxfree_bordercolor');    
        $likebox_showstream = Configuration::get('likeboxfree_showstream');
            if ($likebox_showstream=="1"){$likebox_showstream_checked="checked='YES'";}
        $likebox_showheader = Configuration::get('likeboxfree_showheader');
            if ($likebox_showheader=="1"){$likebox_showheader_checked="checked='YES'";}
        
        $likebox_bgon = Configuration::get('likeboxfree_bgon');
        $likebox_bgcolor = Configuration::get('likeboxfree_bgcolor');
         
        $array['likeboxfree_fanpageurl']=$likebox_fanpageurl;
        $array['likeboxfree_width']=$likebox_width;
        $array['likeboxfree_height']=$likebox_height;
        $array['likeboxfree_colorscheme']=$likebox_colorscheme;
        $array['likeboxfree_showfaces']=$likebox_showfaces;
        $array['likeboxfree_showheader']=$likebox_showheader;
        $array['likeboxfree_showstream']=$likebox_showstream;
        $array['likeboxfree_bordercolor']=$likebox_bordercolor;
        $array['likeboxfree_bgcolor']=$likebox_bgcolor;
        $array['likeboxfree_bgon']=$likebox_bgon;
        return $array;    
    }
   
	function hookrightColumn($params){
		if (Configuration::get('likeboxfree_position')==2){
		    $likeboxarray=$this->setlikeboxoptions();
	        global $smarty;
	        $smarty->assign(array('likebox' => $likeboxarray));
			return $this->display(__FILE__, 'rightcolumn.tpl');
		}	
	}
	function hookleftColumn($params){
		if (Configuration::get('likeboxfree_position')==1){
		    $likeboxarray=$this->setlikeboxoptions();
	        global $smarty;
	        $smarty->assign(array('likebox' => $likeboxarray));
			return $this->display(__FILE__, 'rightcolumn.tpl');
		}	
	}
	function hookHome($params){
		if (Configuration::get('likeboxfree_position')==3){
		    $likeboxarray=$this->setlikeboxoptions();
	        global $smarty;
	        $smarty->assign(array('likebox' => $likeboxarray));
			return $this->display(__FILE__, 'rightcolumn.tpl');
		}	
	}
	function hookFooter($params){
		if (Configuration::get('likeboxfree_position')==4){
		    $likeboxarray=$this->setlikeboxoptions();
	        global $smarty;
	        $smarty->assign(array('likebox' => $likeboxarray));
			return $this->display(__FILE__, 'rightcolumn.tpl');
		}	
	}		   
}


class likeboxfreeUpdate extends likeboxfree {  
    public static function version($version){
        $version=(int)str_replace(".","",$version);
        if (strlen($version)==3){$version=(int)$version."0";}
        if (strlen($version)==2){$version=(int)$version."00";}
        if (strlen($version)==1){$version=(int)$version."000";}
        if (strlen($version)==0){$version=(int)$version."0000";}
        return (int)$version;
    }
    
    public static function encrypt($string){
        return base64_encode($string);
    }
    
    public static function verify($module,$key,$version){
        if (ini_get("allow_url_fopen")) {
             if (function_exists("file_get_contents")){
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module='.$module."&version=".self::encrypt($version)."&lic=$key&u=".self::encrypt(_PS_BASE_URL_.__PS_BASE_URI__));
             }
        }
        Configuration::updateValue("update_".$module,date("U"));
        Configuration::updateValue("updatev_".$module,$actual_version); 
        return $actual_version;
    }
}
?>