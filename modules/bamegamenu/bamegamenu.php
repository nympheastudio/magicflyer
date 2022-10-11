<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@buy-addons.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@buy-addons.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 * @since 1.6
 */

class BAMegaMenu extends Module
{

    private $spacer_size = '5';
    private $ps_menu = "";
    private $user_groups;
    private $page_name = '';
    private $pattern = '/^([A-Z_]*)[0-9]+/';
    private $ps_html = null;


    /** @var array cipher tool instance */
    public $ps_cipherTool;
    public $ps_hooks = array(
        'displayHeader',
        'displayBackOfficeHeader',
        'displayTop',
        'leftColumn',
        'rightColumn',
            //'displayNav',
    );

    public function __construct()
    {
        require_once(_PS_ROOT_DIR_ . '/modules/bamegamenu/include/buyaddons.php');
        $this->name = 'bamegamenu';
        $this->tab = 'front_office_features';
        $this->version = '1.0.36';
        $this->author = 'buy-addons';
        $this->module_key = '76eb0784b11288280c406988578f3b3a';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Responsive Mega Menu Pro');
        $this->description = $this->l('Author: buy-addons');
        //$this->ps_cipherTool =new Blowfish(str_pad('', 56, md5('ps'.__FILE__)), str_pad('', 56, md5('iv'.__FILE__)));
        //$this->ps_cipherTool =new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=')) { // for PrestaShop 1.7+
            //echo _NEW_COOKIE_KEY_;die;
            $this->ps_cipherTool = new PhpEncryption(_NEW_COOKIE_KEY_);
        } else {
            $this->ps_cipherTool = new Blowfish('byuzUw7ISVPTywvFYIGadH6FaDSdqGJHFjELWExwyWvVa6iFXEPxqEOr', 'JqABVw1c');
        }
       
        if (MegamenuReturnVersion() == "1.5") {
            $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        } else {
            $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        }
    }

    public function install()
    {
        if (parent::install()) {
            // save Default Configuration
            $this->setDefaultConfig();

            foreach ($this->ps_hooks as $hook) {
                if (!$this->registerHook($hook)) {
                    return false;
                }
            }
            $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'megamenu` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `label` text NOT NULL,
                  `customclass` text NOT NULL,
                  `submenu` tinyint(4) NOT NULL,
                  `typelink` text NOT NULL,
                  `custom_url` text NOT NULL,
                  `typelink_one` text NOT NULL,
                  `custom_url_one` text NOT NULL,
                  `make` tinyint(4) NOT NULL,
                  `fullwidth` tinyint(4) NOT NULL,
                  `sub` text NOT NULL,
                  `position` int(11) NOT NULL,
                  `id_lang` int(11) NOT NULL,
                  `id_shop` int( 11 ) NULL DEFAULT NULL,
                  `action` tinyint(4) NOT NULL,
                  PRIMARY KEY  (`id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
            $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'megamenu_group` (
                    `id_menu` int(11) NOT NULL,
                    `id_group` int(11) NOT NULL,
                    PRIMARY KEY (`id_menu`,`id_group`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }

            return true;
        }
        return false;
    }

    public function hookleftColumn($params)
    {
        return $this->hookDisplayTop($params);
    }

    public function hookrightColumn($params)
    {
        return $this->hookDisplayTop($params);
    }

    public function hookdisplayNav($params)
    {
        return $this->hookDisplayTop($params);
    }

    private function setDefaultConfig()
    {
        $shopArrayList = Shop::getShops(false);
        foreach ($shopArrayList as $shopArray) {
            $id_shop = $shopArray['id_shop'];
            $id_shop_group = $shopArray['id_shop_group'];
            Configuration::updateValue('bamenu_effect', 'default', false, $id_shop_group, $id_shop);
            Configuration::updateValue('bamenu_speed', 100, false, $id_shop_group, $id_shop);
            Configuration::updateValue('bamenu_ltype', 1, false, $id_shop_group, $id_shop);
            Configuration::updateValue('bamenu_style', 'light', false, $id_shop_group, $id_shop);
            Configuration::updateValue('bamenu_vertical', 0, false, $id_shop_group, $id_shop);
            Configuration::updateValue('stay_on_top', 0, false, $id_shop_group, $id_shop);
            Configuration::updateValue('stay_on_top_mobile', 0, false, $id_shop_group, $id_shop);
            Configuration::updateValue('bamenu_ccss', '', false, $id_shop_group, $id_shop);
        }
    }

    public function uninstall()
    {
        return parent::uninstall();
        /*return true;
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'megamenu`';
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'megamenu_group`';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
        return true;*/
    }

    protected function getMegamenu($id = "", $id_shop = null)
    {
        $where = array();
        if (empty($id_shop)) {
            $where[] = "id_shop=" . (int) $this->context->shop->id;
        } else {
            $where[] = "id_shop=" . (int) $id_shop;
        }
        
        if ($id != "") {
            $where[] = "id=" . (int) $id;
        }
        if ($this->context->cookie->finter_lang != '') {
            $where[] = "id_lang=" . (int) $this->context->cookie->finter_lang;
        }

        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'megamenu WHERE ' . ( count($where) ? implode($where, ' AND ') : 1 )
        . ' ORDER BY position ASC ';

        $return = Db::getInstance()->ExecuteS($query);
        if (!empty($return[0])) {
            return $return;
        }
        return false;
    }

    protected function getTopMegamenu()
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'megamenu ORDER BY id DESC LIMIT 0,1';
        $return = Db::getInstance()->ExecuteS($query);
        if (!empty($return)) {
            return $return;
        }
        return false;
    }

    protected function getHook($row = '', $id = '', $selected = array())
    {

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'hook` ORDER BY name ASC ';
        $returns = Db::getInstance()->ExecuteS($query);
        $html = '';
        $html.='<select id="select_category" name="sub[' . (int) $row . '][' . (int) $id . '][]">';
        foreach ($returns as $value) {
            $html.='<option '
            . (in_array(pSQL(htmlspecialchars(strip_tags($value['name']))), $selected) == true ? "selected" : "")
            . ' value="' . pSQL(htmlspecialchars(strip_tags($value['name']))) . '">'
            . pSQL(htmlspecialchars(strip_tags($value['name']))) . '</option>';
        }
        $html.='</select>';
        return $html;
    }

    private function saveConfigMega()
    {
        if (Tools::getValue('action') == 'configmega') {
            //echo '<pre>';print_r($_POST);die;
            Configuration::updateValue('bamenu_effect', Tools::getValue('effect', 'default'));
            Configuration::updateValue('bamenu_speed', Tools::getValue('speed', 500));
            Configuration::updateValue('bamenu_ltype', Tools::getValue('ltype', 1));
            Configuration::updateValue('bamenu_style', Tools::getValue('style', 'light'));
            Configuration::updateValue('bamenu_vertical', Tools::getValue('vertical', 0));
            Configuration::updateValue('stay_on_top', Tools::getValue('stay_on_top', 0));
            Configuration::updateValue('stay_on_top_mobile', Tools::getValue('stay_on_top_mobile', 0));
            Configuration::updateValue('bamenu_ccss', $this->ps_cipherTool->encrypt(Tools::getValue('ccss')));
            $this->ps_html .= $this->displayConfirmation($this->l('Configuration has been updated successfully.'));
        }
    }

    private function renderFormConfigMega()
    {
        $adminModule='index.php?controller=AdminModules';
        $adminModule1='index.php?controller=AdminModules&configmega=1';
        $adminModule2='index.php?controller=AdminModules&customicon=1';
        $tokenModule='&token='.Tools::getValue('token');
        $configureModule='&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $hrefModule = $adminModule.$tokenModule.$configureModule;
        $hrefModule1 = $adminModule1.$tokenModule.$configureModule;
        $hrefModule2 = $adminModule2.$tokenModule.$configureModule;
        $this->ps_html .= '<div class="col-lg-12 menumega ba_wrapper_tab">';
        $this->ps_html .= '<span><a class="'
        . ((Tools::getValue('customicon') == '' && Tools::getValue('configmega') == '') ? "active" : ""). '" href="'
        . Tools::htmlentitiesUTF8($hrefModule)
        . '" title="' . $this->l('Mega Menu Plus') . '">' . $this->l('Mega Menu Plus') . '</a></span>
                        <span><a class="' . (Tools::getValue('configmega') == 1 ? "active" : "") . '" href="'
                        .Tools::htmlentitiesUTF8($hrefModule1)
                        .'" title="' . $this->l('Configuration') . '">' . $this->l('Configuration') . '</a></span>
                        <span><a class="' . (Tools::getValue('customicon') == 1 ? "active" : "")
                        . '" href="' . Tools::htmlentitiesUTF8($hrefModule2)
                        . '" title="' . $this->l('Configuration') . '">' . $this->l('Custom Icon') . '</a></span>';
        $this->ps_html .= '</div>';
        $this->ps_html .= '<form class="form-horizontal clearfix" id="form-megamenu" method="POST" action="'
                        . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '">';
        $this->ps_html .= '<input type="hidden" id="action" name="action" value="">';
        $this->ps_html .= '<input type="hidden" name="id" id="id" value="">';
        $this->ps_html .= '<input type="hidden" name="status" id="status" value="">';
        $this->ps_html .= '<div class="panel col-lg-12">';
        $this->ps_html .= '<div class="panel-heading">
                            ' . $this->l('Configuration') . '
                            <span class="badge "><i class="icon-upload"></i></span>
                            <span class="panel-heading-action">
                                <a title="'.$this->l('Save Configuration')
                                .'" id="desc-product-save" onclick="submitformmenu(0,'
                                . "'configmega'" . ')" class="list-toolbar-btn add" href="#">
                                    <span data-toggle="tooltip" class="label-tooltip" 
                                    data-original-title="Save Configuration" data-html="true">
                                        <i class="process-icon-save "></i>
                                    </span>
                                </a>
                            </span>
                        </div>';
        $this->ps_html .= '<div class="panel">';
        $this->ps_html .= '<div class="form-group">
                                <label class="control-label col-lg-3" for="labelmenu">
                                    <span class="label-tooltip"  title="" data-original-title="'.$this->l('Style.').'">
                                        ' . $this->l('Style:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="style" id="style">
                                        <option ' . (Configuration::get('bamenu_style') == "light" ? "selected" : "")
                                        . ' value="light">' . $this->l('Light') . '</option>
                                        <option ' . (Configuration::get('bamenu_style') == "dark" ? "selected" : "")
                                        . ' value="dark">' . $this->l('Dark') . '</option>
                                    
                                    </select>    
                                </div>
                            </div>';
        $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    <span title="" data-original-title="' . $this->l('Vertical Menu') . '">
                                        ' . $this->l('Vertical Menu:') . '
                                    </span>
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);
                                        showOptions(true);showRedirectProductOptions(false);"
                                        type="radio" name="vertical" id="vertical_on"
                                        value="1" '.(Configuration::get('bamenu_vertical') == 1?'checked="checked"':'')
                                        . '>
                                        <label for="vertical_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            ' . $this->l('Yes') . '
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="vertical"
                                        id="vertical_off" value="0" '
                                        . (Configuration::get('bamenu_vertical') == 0 ? 'checked="checked"' : '') . '>
                                        <label for="vertical_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            ' . $this->l('No') . '
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
        $this->ps_html .='<div class="form-group">                
                                <label class="control-label col-lg-3" for="effect">
                                    <span  title="" data-original-title="' . $this->l('Effect Type.') . '">
                                        ' . $this->l('Effect:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="effect" id="effect">
                                        <option ' . (Configuration::get('bamenu_effect') == "default" ? "selected" : "")
                                        . ' value="default">' . $this->l('Default') . '</option>
                                        <option ' . (Configuration::get('bamenu_effect') == "fade" ? "selected" : "")
                                        . ' value="fade">' . $this->l('Fade In') . '</option>
                                        <option ' . (Configuration::get('bamenu_effect') == "slide" ? "selected" : "")
                                        . ' value="slide">' . $this->l('Slide Down') . '</option>
                                        
                                    </select>    
                                </div>
                            </div>';
        $this->ps_html .= '<div class="form-group">
                                <label class="control-label col-lg-3" for="labelmenu">
                                    <span title="" data-original-title="' . $this->l('Effect Speed.') . '">
                                        ' . $this->l('Effect Speed:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="speed" id="speed">
                                        <option ' . (Configuration::get('bamenu_speed') == "100" ? "selected" : "")
                                        . ' value="100">' . $this->l('Fast') . '</option>
                                        <option ' . (Configuration::get('bamenu_speed') == "500" ? "selected" : "")
                                        . ' value="500">' . $this->l('Medium') . '</option>
                                        <option ' . (Configuration::get('bamenu_speed') == "1000" ? "selected" : "")
                                        . ' value="1000">' . $this->l('Slow') . '</option>
                                    </select>    
                                </div>
                            </div>';
        $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" title="" data-original-title="'
                                    . $this->l('Language Type') . '">
                                        ' . $this->l('Language Type:') . '
                                    </span>
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);
                                        showOptions(true);showRedirectProductOptions(false);"
                                        type="radio" name="ltype" id="status_on" value="1" '
                                        . (Configuration::get('bamenu_ltype') == 1 ? 'checked="checked"' : '') . '>
                                        <label for="status_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            ' . $this->l('Left to Right') . '
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="ltype"
                                        id="status_off" value="0" '
                                        . (Configuration::get('bamenu_ltype') == 0 ? 'checked="checked"' : '') . '>
                                        <label for="status_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            ' . $this->l('Right to Left') . '
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
        $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip"  title="" data-original-title="'
                                    . $this->l('Always stay on top Desktop') . '">
                                        ' . $this->l('Always stay on top Desktop:') . '
                                    </span>
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="stay_on_top"
                                        id="stay_on_top_on" value="1" '
                                        . (Configuration::get('stay_on_top') == 1 ? 'checked="checked"' : '') . '>
                                        <label for="stay_on_top_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            ' . $this->l('Yes') . '
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="stay_on_top"
                                        id="stay_on_top_off" value="0" '
                                        . (Configuration::get('stay_on_top') == 0 ? 'checked="checked"' : '') . '>
                                        <label for="stay_on_top_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            ' . $this->l('No') . '
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
        //var_dump(Configuration::get('stay_on_top_mobile')==0);die;
        $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip"  title="" data-original-title="'
                                    . $this->l('Always stay on top Mobile') . '">
                                        ' . $this->l('Always stay on top Mobile:') . '
                                    </span>
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="stay_on_top_mobile"
                                        id="stay_on_top_mobile_on" value="1" '
                                        .(Configuration::get('stay_on_top_mobile') == 1?'checked="checked"':'') . '>
                                        <label for="stay_on_top_mobile_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            ' . $this->l('Yes') . '
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="stay_on_top_mobile"
                                        id="stay_on_top_mobile_off" value="0" '
                                        .(Configuration::get('stay_on_top_mobile') == 0 ? 'checked="checked"' : '') . '>
                                        <label for="stay_on_top_mobile_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            ' . $this->l('No') . '
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
        $tbs = pSQL(htmlspecialchars(strip_tags($this->ps_cipherTool->decrypt(Configuration::get('bamenu_ccss')))));
        $this->ps_html .= '<div class="form-group">
                            <label class="control-label col-lg-3" for="labelmenu">
                                <span class="label-tooltip"  title="" data-original-title="'.$this->l('Custom Css').'">
                                    ' . $this->l('Custom Css:') . '
                                </span>
                            </label>
                            <div class="col-lg-8">
                                <textarea class="" aria-hidden="true" id="" name="ccss" rows="15" cols="20">'
                                    . str_replace('\\r\\n', '&#13;', $tbs)
                                . '</textarea>
                            </div>
                        </div>';
        $this->ps_html .= '</div>';
        $this->ps_html .= '</div>';
        $this->ps_html .= '</form><br>';
    }

    private function renderFormCustomIcon()
    {
        $adminModule='index.php?controller=AdminModules';
        $adminModule1='index.php?controller=AdminModules&configmega=1';
        $adminModule2='index.php?controller=AdminModules&customicon=1';
        $tokenModule='&token='.Tools::getValue('token');
        $configureModule='&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $hrefModule = $adminModule.$tokenModule.$configureModule;
        $hrefModule1 = $adminModule1.$tokenModule.$configureModule;
        $hrefModule2 = $adminModule2.$tokenModule.$configureModule;
        $this->ps_html .= '<div class="col-lg-12 menumega ba_wrapper_tab">';
        $this->ps_html .= '<span><a class="'
        . ((Tools::getValue('customicon') == '' && Tools::getValue('configmega') == '') ? "active" : "") . '" href="'
        . Tools::htmlentitiesUTF8($hrefModule)
        . '" title="' . $this->l('Mega Menu Plus') . '">' . $this->l('Mega Menu Plus') . '</a></span>
                        <span><a class="' . (Tools::getValue('configmega') == 1 ? "active" : "")
                        . '" href="' . Tools::htmlentitiesUTF8($hrefModule1)
                        . '" title="' . $this->l('Configuration') . '">' . $this->l('Configuration') . '</a></span>
                        <span><a class="' . (Tools::getValue('customicon') == 1 ? "active" : "")
                        . '" href="' . Tools::htmlentitiesUTF8($hrefModule2)
                        . '" title="' . $this->l('Configuration') . '">' . $this->l('Custom Icon') . '</a></span>';
        $this->ps_html .= '</div>';
        $this->ps_html .= '<form class="form-horizontal clearfix" id="form-megamenu" method="POST" action="'
                        . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '">';
        $this->ps_html .= '<input type="hidden" id="action" name="action" value="">';
        $this->ps_html .= '<input type="hidden" name="id" id="id" value="">';
        $this->ps_html .= '<input type="hidden" name="status" id="status" value="">';
        $this->ps_html .= '<div class="panel col-lg-12">';
        $this->ps_html .= '<div class="panel-heading">
                            ' . $this->l('Custom Icon') . '
                            <span class="badge "><i class="icon-info"></i></span>
                        </div>';
        $this->ps_html .= '<div class="panel">';
        $this->ps_html .= '<div class="form-group">';
        $this->ps_html .= "
        <link href='//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' rel='stylesheet' />
                            <h2>Pick up an Icon</h2>
                            <p>Please click to pick up an icon, then add Icon's 
                            CSS Class into the HTML Tag that you want that display.<br />
                                <b>Ex:</b> &lt;i class='fa fa-home'&gt; Home page&lt;/i&gt;<br />
                                <b>The frontend, it display like here: <i class='fa fa-home'>
                                Home page</i></b><br />
                            </p>";
        $this->ps_html .= '</div>';
        $this->ps_html .= '</div>';
        $this->ps_html .= '<div class="panel">';
        $this->ps_html .= '<div class="form-group">';
        $this->ps_html .=
        Tools::file_get_contents(_PS_ROOT_DIR_ . '/modules/bamegamenu/include/html/font_awesome_icons.html');
        $this->ps_html .= '</div>';
        $this->ps_html .= '</div>';
        $this->ps_html .= '</form><br>';
    }

    private function renderFormListLabel()
    {
        $adminModule='index.php?controller=AdminModules';
        $adminModule1='index.php?controller=AdminModules&configmega=1';
        $adminModule2='index.php?controller=AdminModules&customicon=1';
        $tokenModule='&token='.Tools::getValue('token');
        $configureModule='&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $hrefModule = $adminModule.$tokenModule.$configureModule;
        $hrefModule1 = $adminModule1.$tokenModule.$configureModule;
        $hrefModule2 = $adminModule2.$tokenModule.$configureModule;
        if (Tools::getValue('finter_lang') != "") {
            $this->context->cookie->finter_lang = Tools::getValue('finter_lang');
        } else {
            if ($this->context->cookie->finter_lang == '') {
                $this->context->cookie->finter_lang = $this->context->employee->id_lang;
            }
        }
        $menu = $this->getMegamenu();
        $languages = $this->context->controller->getLanguages();
        if ($menu == false) {
            $menu = null;
        }
        // build $languages array
        $languages_arr=array();
        foreach ($languages as $val) {
            $languages_arr[$val['id_lang']]=$val;
        }
        //echo '<pre>';var_dump($languages_arr);die;
        $customicon = Tools::getValue('customicon');
        $languagetype = Tools::getValue('languagetype');
        $this->ps_html .= '<div class="col-lg-12 menumega ba_wrapper_tab">';
        $this->ps_html .= '<span><a class="'
        . (($customicon == '' && $languagetype == '' && Tools::getValue('configmega') == '') ? "active" : "")
        . '" href="' . Tools::htmlentitiesUTF8($hrefModule)
        . '" title="' . $this->l('Mega Menu Plus') . '">' . $this->l('Mega Menu Plus') . '</a></span>
                         <span><a class="' . (Tools::getValue('configmega') == 1 ? "active" : "")
                         . '" href="' . Tools::htmlentitiesUTF8($hrefModule1)
                         . '" title="' . $this->l('Configuration') . '">' . $this->l('Configuration') . '</a></span>
                        <span><a class="' . (Tools::getValue('customicon') == 1 ? "active" : "")
                        . '" href="' . Tools::htmlentitiesUTF8($hrefModule2)
                        . '" title="' . $this->l('Configuration') . '">' . $this->l('Custom Icon') . '</a></span>';
        $this->ps_html .= '</div>';
        $this->ps_html .= '<form class="form-horizontal clearfix" id="form-megamenu" method="POST" action="'
        . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '">';
        $this->ps_html .= '<input type="hidden" id="action" name="action" value="">';
        $this->ps_html .= '<input type="hidden" name="id" id="id" value="">';
        $this->ps_html .= '<input type="hidden" name="status" id="status" value="">';
        $this->ps_html .= '<div class="panel col-lg-12">';
        $this->ps_html .= '<div class="panel-heading">
                            ' . $this->l('Mega Menu') . '
                            <span class="badge">' . count($menu) . '</span>';
        
        $language_html = '';
        $language_html .='<select style="width:120px;" class="filter center"'
                        .'name="finter_lang" onchange="this.form.submit();">';
        $finter_lang_tmp = $this->context->cookie->finter_lang;
        foreach ($languages as $val) {
            $language_html .= '<option '
            . (((int) $finter_lang_tmp != '') && ((int) $finter_lang_tmp == (int) $val['id_lang']) ? "selected" : "")
            . ' value="' . (int) $val['id_lang'] . '">'
            . pSQL(htmlspecialchars(strip_tags($val['name']))) . '</option>';
        }
        $language_html .='</select>';
        
      
        $this->ps_html .='<span class="panel-heading-action">
                                <a title="' . $this->l('Add New Item') . '"  id="desc-product-new"
                                onclick="submitformmenu(0,' . "'addlabel'" . ')" class=" list-toolbar-btn add" href="#">
                                    <span data-toggle="tooltip" class="label-tooltip"
                                    data-original-title="' . $this->l('Add New Item') . '" data-html="true">
                                        <i class="process-icon-new "></i>
                                    </span>
                                </a>
                            </span>
                        </div>';
        $this->ps_html .='<table id="table-product" class="table product">';
        $this->ps_html .='<thead>';
        $this->ps_html .='<tr class="nodrag nodrop">';
        $this->ps_html .='<th class="center fixed-width-xs" width="50px"><span class="title_box">ID</span></th>';
        $this->ps_html .='<th class="fixed-width-xl center"><span class="title_box">Label</span></th>';
        $this->ps_html .='<th class="center" width="100px"><span class="title_box">Language</span></th>';
        $this->ps_html .='<th class="fixed-width-xs center"><span class="title_box">Position</span></th>';
        $this->ps_html .='<th class="fixed-width-xs center"><span class="title_box">Status</span></th>';
        $this->ps_html .='<th class="fixed-width-xs center"><span class="title_box">Action</span></th>';
        $this->ps_html .='</tr>';
        // filter row
        $this->ps_html .='<tr class="nodrag nodrop filter row_hover">';
        $this->ps_html .='<th class="center" width="50px">--</th>';
        $this->ps_html .='<th class="center">--</th>';
        $this->ps_html .='<th class="center text-center">'.$language_html.'</th>';
        $this->ps_html .='<th class="center">--</th>';
        $this->ps_html .='<th class="center">--</th>';
        $this->ps_html .='<th class="center">--</th>';
        $this->ps_html .='</tr>';
        ///////////////
        $this->ps_html .='</thead>';
        $this->ps_html .='<tbody>';

        for ($i = 0; $i < count($menu); $i++) {
            if (($i + 1) % 2 == 0) {
                $odd = "odd";
            } else {
                $odd = "";
            }
            //var_dump($menu);die;
            $this->ps_html .='<tr class="listmenu ' . pSQL(htmlspecialchars(strip_tags($odd))) . '" >';
            $this->ps_html .='<td class="text-center"><input type="hidden" value="'
            . (int) $menu[$i]['id'] . '">' . (int) $menu[$i]['id'] . '</td>';
            $this->ps_html .='<td class="text-left">'
                . pSQL(htmlspecialchars(strip_tags($menu[$i]['label']))) . '</td>';
            $this->ps_html .='<td class="text-center">'
                . pSQL(htmlspecialchars(strip_tags($languages_arr[$menu[$i]['id_lang']]['name']))) . '</td>';
            $this->ps_html .='<td class="text-center pointer dragHandle">
                                <center><div class="dragGroup">
                                    <i style="display:none" class="icon-move"></i>
                                    <div class="positions">
                                        ' . (int) $menu[$i]['position'] . '
                                    </div>
                                </div></center></td>';
            $this->ps_html .='<td class="text-center">';
            $warning = '<a class="update label label-warning" href="#" onclick="submitformmenu('
            . (int) $menu[$i]['id'] . ',' . "'updatelabel'" . ',1)" title="Disabled">
                                    <i class="icon-ban-circle"></i> No
                                 </a>';
            $success = '<a class="update label label-success" href="#" onclick="submitformmenu('
            . (int) $menu[$i]['id'] . ',' . "'updatelabel'" . ',0)" title="Enabled">
                                    <i class="icon-check-sign"></i> Yes
                                </a>';
            $this->ps_html .=((int) $menu[$i]['action'] == 1 ? $success : $warning);
            $this->ps_html .='</td>';
            $this->ps_html .='<td class="text-center">';
            $this->ps_html .='<div class=" ">
                                <a href="'.$hrefModule.'&editlabel=true&action=editlabel&id='
                                .(int) $menu[$i]['id'].'" title="Edit" class="edit btn btn-default">
                                    <i class="icon-pencil"></i> Edit
                                </a>
                                <a href="#" title="Delete" onclick="submitformmenu('
                                .(int) $menu[$i]['id'] . ',' . "'deletelabel'" . ')" class="delete btn btn-default">
                                    <i class="icon-trash"></i> Delete
                                </a>
                                <a href="#" title="Move" class="move btn btn-default">
                                    <i class="icon-move"></i> Move
                                </a>
                            </div>';
            $this->ps_html .='</td>';
            $this->ps_html .='</tr>';
        }
        $this->ps_html .='</tbody>';
        $this->ps_html .='</thead>';
        $this->ps_html .='</table>';
        $this->ps_html .= '</div>';
        $this->ps_html .= '</form><br>';
    }

    private function headerHtml()
    {
        $this->ps_html .= "<script type='text/javascript'>
                                var iso = 'en';
                                var ad = '';
                                $(document).ready(function(){
                                        tinySetup({
                                            editor_selector :'autoload_rte'
                                        });
                                });
                            </script>";
        $this->ps_html .= '<div id="menuoption" style="display:none">' . $this->choicesSelect() . '</div>';
        $this->ps_html .= '<div id="menuoptionone" style="display:none">' . $this->choicesSelect('_one') . '</div>';
        $this->ps_html .= '<script type="text/javascript">';
        $this->ps_html .= 'var menuoption     =      $("#menuoption").clone().html();';
        $this->ps_html .= 'var menuoptionone    =    $("#menuoptionone").clone().html();';
        $this->ps_html .= 'var batoken = "' . $this->cookiekeymodule() . '";';
        $this->ps_html .= 'var base_url = "' . $this->getBaseURL() . '/modules/' . $this->name . '";';
        $this->ps_html .= 'console.log(base_url);';

        $this->ps_html .= '$(function () {
                        var $mySlides = $("tbody");
                        $mySlides.sortable({
                            opacity: 0.6,
                            cursor: "move",
                            update: function() {
                                var position = new Array();
                                $("tbody tr.listmenu input").each(function(i){
                                    position[i]=$(this).val();
                                });
                                var order = $(this).sortable("serialize") + "&action=updatePosition&position="+position;
                                $.post("' . $this->getBaseURL() . '/modules/' . $this->name
                                . '/ajax_' . $this->name . '.php", order);
                                //alert("' . $this->getBaseURL() . '/modules/' . $this->name
                                . '/ajax_' . $this->name . '.php");
                                }
                            });
                        $mySlides.hover(function() {
                            $(this).css("cursor","move");
                            },
                            function() {
                            $(this).css("cursor","auto");
                        });
                        
                    })';
        $this->ps_html .= '</script>';
        $this->ps_html .='<script type="text/javascript" src="'
        . $this->_path . 'views/js/megamenu_backend.js' . '"></script>';
    }

    private function updateStatusMenu()
    {
        if (Tools::getValue('action') == 'updatelabel' && Tools::getValue('id') != '' && Tools::getValue('id') != '') {
            $query = 'UPDATE `' . _DB_PREFIX_ . 'megamenu` SET `action` = ' . (int) Tools::getValue('status') . '
                    WHERE `id` = ' . (int) Tools::getValue('id');

            $res = Db::getInstance()->execute($query);
            if ($res) {
                $this->ps_html .= $this->displayConfirmation($this->l('The status has been updated successfully.'));
            } else {
                $this->ps_html .=$this->displayError($this->l('The status has been updated error.'));
            }
        }
    }

    private function deleteMenu()
    {
        if (Tools::getValue('action') == 'deletelabel' && Tools::getValue('id') != '') {
            $query = 'DELETE FROM `' . _DB_PREFIX_ . 'megamenu` WHERE `id` = ' . (int) Tools::getValue('id');
            $res = Db::getInstance()->execute($query);
            // xóa toàn bộ group
            $sql = 'DELETE FROM '._DB_PREFIX_."megamenu_group WHERE id_menu=".(int) Tools::getValue('id');
            Db::getInstance()->execute($sql);
            if ($res) {
                $this->ps_html .= $this->displayConfirmation($this->l('The menu has been deleted successfully.'));
            } else {
                $this->ps_html .=$this->displayError($this->l('The menu has been deleted error.'));
            }
        }
    }

    private function addMenu()
    {
        if (Tools::getValue('action') == 'addlabel') {
            $languages = $this->context->controller->getLanguages();
            $id_lang_default = $this->context->employee->id_lang;
            $id_shop = $this->context->shop->id;
            $this->ps_html .= '<form class="form-horizontal clearfix" id="form-megamenu" method="POST" action="'
            . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '">';
            $this->ps_html .= '<input type="hidden" id="action" name="action" value="">';
            $this->ps_html .= '<input type="hidden" name="id" id="id" value="'
            . ((int) Tools::getValue('id') != '' ? (int) Tools::getValue('id') : '') . '">';
            $this->ps_html .= '<input type="hidden" name="mega_id_shop" id="mega_id_shop" value="'.$id_shop.'" />';
            $this->ps_html .= '<input type="hidden" name="status" id="status" value="">';
            $this->ps_html .= '<div class="panel col-lg-12">';
            $action_tmp = Tools::getValue('action');
            $actionMGMenuAdmin = ($action_tmp == 'addlabel' ? $this->l('Add Mega Menu') : $this->l('Edit Mega Menu'));
            $this->ps_html .= '<div class="panel-heading">'. pSQL(htmlspecialchars(strip_tags($actionMGMenuAdmin)))
                            . '<span class="badge "><i class="icon-link"></i></span>
                                <span class="panel-heading-action">
                                    <a title="'.$this->l('Save and Stay ')
                                    .'" id="desc-product-save" onclick="submitformmenu('
                . (pSQL(htmlspecialchars(strip_tags(Tools::getValue('id')))) != '' ? (int) Tools::getValue('id') : '')
                                    . ',' . "'savestay'" . ')" class="list-toolbar-btn add" href="#">
                                        <span '
                                        . '"  class="label-tooltip" data-original-title="'
                                        . $this->l('Save and Stay ') . '" data-html="true">
                                            <i class="process-icon-save "></i>
                                        </span>
                                    </a>
                                    <a title="'.$this->l('Save & Close')
                                    .'"  id="desc-product-save" onclick="submitformmenu('
                . (pSQL(htmlspecialchars(strip_tags(Tools::getValue('id')))) != '' ? (int) Tools::getValue('id') : '')
                                    . ',' . "'savelabel'" . ')" class="list-toolbar-btn add" href="#">
                                        <span '
                                        . ' class="label-tooltip" data-original-title="'
                                        . $this->l('Save & Close') . '" data-html="true">
                                            <i class="process-icon-save "></i>
                                        </span>
                                    </a>
                                    <a title="'.$this->l('Cancel')
                                    .'"  id="desc-product-cancel" onclick="submitformmenu(0,'
                                    . "'cancellabel'" . ')" class="list-toolbar-btn add" href="#">
                                        <span '
                                        . ' class="label-tooltip" data-original-title="'
                                        . $this->l('Cancel') . '" data-html="true">
                                            <i class="process-icon-cancel "></i>
                                        </span>
                                    </a>
                                </span>
                            </div>';
            $this->ps_html .= '<div class="panel">';
            $this->ps_html .= '<div class="form-group">
                                <label class="control-label col-lg-3 required" for="labelmenu">
                                    <span class="label-tooltip"  title="" data-original-title="'
                                    . $this->l('Label Menu') . '">
                                        ' . $this->l('Label:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <input type="text" id="labelmenu" class="copy2friendlyUrl updateCurrentText"
                                    name="labelmenu" value="" >
                                </div>
                            </div>';
            $this->ps_html .='<div class="form-group">                
                                <label class="control-label col-lg-3" for="typelink">
                                    <span class="label-tooltip"  title="' . $this->l('Language.')
                                    . '" data-original-title="' . $this->l('Language.') . '">
                                        ' . $this->l('Language:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="language" id="language">';
            foreach ($languages as $val) {
                $this->ps_html .= '<option ' . ($id_lang_default == $val['id_lang'] ? "selected" : "")
                . ' value="' . (int) $val['id_lang'] . '">'
                . pSQL(htmlspecialchars(strip_tags($val['name']))) . '</option>';
            }
            $this->ps_html .= '</select>    
                                </div>
                            </div>';
            $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip"  title="'
                                    . $this->l('Status.') . '" data-original-title="' . $this->l('Status.') . '">
                                        ' . $this->l('Status:') . '
                                    </span>
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="status" 
                                        id="status_on" value="1" checked="checked">
                                        <label for="status_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            Enabled
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="status"
                                        id="status_off" value="0">
                                        <label for="status_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            Disabled
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
            // Group Access
            $this->ps_html .= '<div class="form-group">
                                <label class="control-label col-lg-3" for="labelmenu">
                                    <span class="label-tooltip" title="'
                                    . $this->l('Groups which you would like to have access to this menu item.') . '"
                                       data-original-title="'
                                    . $this->l('Groups which you would like to have access to this menu item.') . '">
                                        ' . $this->l('Group access:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    '.$this->renderGroupsField().'
                                </div>
                            </div>';
            $this->ps_html .= '<div class="form-group">
                                <label class="control-label col-lg-3" for="labelmenu">
                                    <span class="label-tooltip"  title="'
                                    . $this->l('Custom Class ') . '" data-original-title="' . $this->l('Custom Class ')
                                    . '">
                                        ' . $this->l('Custom Class:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <input type="text" id="customclass" class="copy2friendlyUrl updateCurrentText"
                                    name="customclass" value="" >
                                </div>
                            </div>';
            $this->ps_html .='<div class="form-group">                
                                <label class="control-label col-lg-3" for="typelink">
                                    <span class="label-tooltip"  title="' . $this->l('Link.')
                                        . '" data-original-title="' . $this->l('Link.') . '">
                                        ' . $this->l('Link:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="typelink_one" id="typelink_one">
                                        <option  value="externallink">' . $this->l('External Link') . '</option>
                                        <option  value="systemlink">' . $this->l('System Link') . '</option>
                                        <option  value="htmllink">' . $this->l('Html Link') . '</option>
                                    </select>    
                                </div>
                            </div>';
            $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" title="" data-original-title="'
                                    . $this->l('Show/Hide Sub Menu.') . '">
                                        ' . $this->l('Show Submenu:') . '
                                    </span>
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="submenu"
                                        id="submenu_on" value="1" >
                                        <label for="submenu_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            Enabled
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="submenu"
                                        id="submenu_off" value="0" checked="checked">
                                        <label for="submenu_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            Disabled
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
            $this->ps_html .= '<div class="sub_menu">';
            $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    ' . $this->l('Menu Type:') . '
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="make"
                                        id="make_on" value="1" >
                                        <label for="make_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            ' . $this->l('Mega Menu') . '
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="make"
                                        id="make_off" value="0" checked="checked">
                                        <label for="make_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            ' . $this->l('Dropdown') . '
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
            $this->ps_html .= '<div class="after_dropdown">';
            $this->ps_html .='<div class="form-group submenulink">                
                                <label class="control-label col-lg-3" for="typelink">
                                    ' . $this->l('Menu:') . '
                                </label>
                                <div class="col-lg-3">
                                    <select name="typelink" id="typelink">
                                        <option value="existlinks">' . $this->l('One Level') . '</option>
                                        <option value="treelinks">' . $this->l('Multi Level') . '</option>
                                    </select>    
                                </div>
                            </div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '<div class="after_make">';
            $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    ' . $this->l('Full Width:') . '
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="fullwidth"
                                        id="full_on" value="1" >
                                        <label for="full_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            Enabled
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="fullwidth"
                                        id="full_off" value="0" checked="checked">
                                        <label for="full_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            Disabled
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
            $this->ps_html .='<div class="form-group">
                                <label class="button_addrow">'.$this->l('Add Row').'</label>
                                <div class="add_row row_0 end_row">
                                    <div class="row_size">
                                        <label>'.$this->l('Row Size:').' </label>
                                        <select name="size[0][]" id="row_size_0"
                                        onchange="createcol(0,\'row_size_0\',0)">
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                        </select>
                                        <label class="button_removerow">'.$this->l('Remove Row').'</label>
                                    </div>
                                    <div class="list_col list_col_0">
                                        <div class="col col_0">
                                        <div style="display:none" class="cancel_col" onclick="cancel_col(0,0)">
                                        <i class="process-icon-cancel "></i></div>
                                            <div class="form-group">
                                                <label class="control-label col-lg-3">
                                                    ' . $this->l('Label:') . '
                                                </label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="labelmenu_col_0" 
                                                    class="copy2friendlyUrl updateCurrentText" name="sub[0][0][]"
                                                    value="" >
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-lg-3">
                                                    ' . $this->l('Custom Class:') . '
                                                </label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="customclass_col_0"
                                                    class="copy2friendlyUrl updateCurrentText" name="sub[0][0][]"
                                                    value="" >
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-lg-3">
                                                    ' . $this->l('Width:') . '
                                                </label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="width_col_0"
                                                    class="copy2friendlyUrl updateCurrentText" name="sub[0][0][]"
                                                    value="" >
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-lg-3">
                                                    ' . $this->l('Type:') . '
                                                </label>
                                                <div class="col-lg-9">
                                                    <select name="sub[0][0][]" id="typelink_col_0"
                                                    onchange="SelectTypeLink(0,this,0)">
                                                        <option value="">' . $this->l('Hide This') . '</option>
                                                        <option value="link">' . $this->l('System Link') . '</option>
                                                        <option value="customhtml">'.$this->l('Custom Html').'</option>
                                                        <option value="loadhook">' . $this->l('Hook') . '</option>
                                                        <option value="product">' . $this->l('Product') . '</option>
                                                        <option value="productlist">' . $this->l('Product List')
                                                        . '</option>
                                                    </select>
                                                    <br>
                                                    <div class="custom_url_col custom_url_col_0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '</form><br>';
        }
    }

    private function editMenu()
    {
        if (Tools::getValue('action') == 'editlabel' || Tools::getValue('action') == 'savestay') {
            $id = Tools::getValue('id');
            $languages = $this->context->controller->getLanguages();
            $id_shop = $this->context->shop->id;
            $menu = $this->getMegamenu($id);
            $menu = $menu[0];
            if (empty($menu)) {
                //$menu = $this->getTopMegamenu();
                //$menu = $menu[0];
                $admin_link =  Context::getContext()->link->getAdminLink('AdminModules');
                $admin_link .= '&configure='.$this->name.'&module_name=bamegamenu';
                Tools::redirectAdmin($admin_link);
            }
            
            $this->ps_html .= '<form class="form-horizontal clearfix" id="form-megamenu" method="POST" action="'
            . Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']) . '">';
            $this->ps_html .= '<input type="hidden" id="action" name="action" value="" />';
            $this->ps_html .= '<input type="hidden" name="id" id="id" value="'
            . ($menu['id'] != '' ? $menu['id'] : '') . '" />';
            $this->ps_html .= '<input type="hidden" name="status" id="status" value="" />';
            $this->ps_html .= '<input type="hidden" name="mega_id_shop" id="mega_id_shop" value="'.$id_shop.'" />';
            $this->ps_html .= '<div class="panel col-lg-12">';
            $act_tmp = Tools::getValue('action');
            $this->ps_html .= '<div class="panel-heading">
                                ' . ($act_tmp == 'addlabel' ? $this->l('Add Mega Menu') : $this->l('Edit Mega Menu'))
                                .'<span class="badge "><i class="icon-link"></i></span>
                                <span class="panel-heading-action">
                                    <a title="'.$this->l('Save and Stay ')
                                    .'" id="desc-product-save" onclick="submitformmenu('
            . (pSQL(htmlspecialchars(strip_tags($menu['id']))) != '' ? (int) $menu['id'] : '') . ',' . "'savestay'"
                                    . ')" class="list-toolbar-btn add" href="#">
                                        <span '
                                        . ' class="label-tooltip" data-toggle="tooltip" data-original-title="'
                                        . $this->l('Save and Stay ') . '" data-html="true">
                                            <i class="process-icon-save "></i>
                                        </span>
                                    </a>
                                    <a title="'.$this->l('Save & Close')
                                    .'"  id="desc-product-save" onclick="submitformmenu('
            . (pSQL(htmlspecialchars(strip_tags($menu['id']))) != '' ? (int) $menu['id'] : '') . ',' . "'savelabel'"
                                    . ')" class="list-toolbar-btn add" href="#">
                                        <span '
                                        . ' class="label-tooltip" data-toggle="tooltip" data-original-title="'
                                        . $this->l('Save Label'). '" data-html="true">
                                            <i class="process-icon-save "></i>
                                        </span>
                                    </a>
                                    <a title="'.$this->l('Cancel')
                                    .'"  id="desc-product-cancel" onclick="submitformmenu(0,'
                                    . "'cancellabel'" . ')" class="list-toolbar-btn add" href="#">
                                        <span '.
                                        ' class="label-tooltip" data-toggle="tooltip" data-original-title="'
                                        . $this->l('Cancel Label') . '" data-html="true">
                                            <i class="process-icon-cancel "></i>
                                        </span>
                                    </a>
                                </span>
                            </div>';
            $this->ps_html .= '<div class="panel">';
            $this->ps_html .= '<div class="form-group">
                                <label class="control-label col-lg-3 required" for="labelmenu">
                                    <span class="label-tooltip"  title="" data-original-title="'
                                    . $this->l('Label Menu'). '">
                                        ' . $this->l('Label:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <input type="text" id="labelmenu"
                                    class="copy2friendlyUrl updateCurrentText" name="labelmenu" value="'
                                    . htmlentities($menu['label'], ENT_QUOTES, "UTF-8") . '" >
                                </div>
                            </div>';
            $this->ps_html .='<div class="form-group">                
                                <label class="control-label col-lg-3" for="typelink">
                                    <span class="label-tooltip"  title="' . $this->l('Language.')
                                    . '" data-original-title="' . $this->l('Language.') . '">
                                        ' . $this->l('Language:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="language" id="language">';
            foreach ($languages as $key => $val) {
                $this->ps_html .= '<option ' . ($menu['id_lang'] == $val['id_lang'] ? "selected" : "")
                . ' value="' . (int) $val['id_lang'] . '">'
                . pSQL(htmlspecialchars(strip_tags($val['name']))) . '</option>';
            }
            $this->ps_html .= '</select>    
                                </div>
                            </div>';
            $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip"  title="' . $this->l('Status.')
                                    . '" data-original-title="' . $this->l('Status.') . '">
                                        ' . $this->l('Status:') . '
                                    </span>    
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="status" id="status_on"
                                        value="1" ' . ($menu['action'] == 1 ? 'checked="checked"' : "") . '>
                                        <label for="status_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            Enabled
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="status"
                                        id="status_off" value="0" '
                                        . ($menu['action'] == 1 ? "" : 'checked="checked"') . '>
                                        <label for="status_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            Disabled
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
            // Group Access
            $this->ps_html .= '<div class="form-group">
                                <label class="control-label col-lg-3" for="labelmenu">
                                    <span class="label-tooltip" title="'
                                    . $this->l('Groups which you would like to have access to this menu item.') . '"
                                       data-original-title="'
                                    . $this->l('Groups which you would like to have access to this menu item.') . '">
                                        ' . $this->l('Group access:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    '.$this->renderGroupsField($id).'
                                </div>
                            </div>';
            $this->ps_html .= '<div class="form-group">
                                <label class="control-label col-lg-3" for="labelmenu">
                                    <span class="label-tooltip"  title="'
                                    . $this->l('Custom Class ') . '" data-original-title="'
                                    . $this->l('Custom Class ') . '">
                                        ' . $this->l('Custom Class:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <input type="text" id="customclass" class="copy2friendlyUrl updateCurrentText"
                                    name="customclass" value="'
                                    . htmlentities($menu['customclass'], ENT_QUOTES, "UTF-8") . '" >
                                </div>
                            </div>';
            $this->ps_html .='<div class="form-group">                
                                <label class="control-label col-lg-3" for="typelink">
                                    <span class="label-tooltip"  title="'
                                    . $this->l('Link.') . '" data-original-title="' . $this->l('Link.') . '">
                                        ' . $this->l('Link:') . '
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="typelink_one" id="typelink_one">
                                        <option ' . ($menu['typelink_one'] == "externallink" ? "selected" : "")
                                        . ' value="externallink">' . $this->l('External Link') . '</option>
                                        <option ' . ($menu['typelink_one'] == "systemlink" ? "selected" : "")
                                        . ' value="systemlink">' . $this->l('System Link') . '</option>
                                        <option ' . ($menu['typelink_one'] == "htmllink" ? "selected" : "")
                                        . ' value="htmllink">' . $this->l('Html Link') . '</option>
                                    </select>    
                                </div>
                            </div>';
            $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" title="" data-original-title="'
                                    . $this->l('Show/Hide Sub Menu.') . '">
                                        ' . $this->l('Show Submenu:') . '
                                    </span>
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="submenu"
                                        id="submenu_on" value="1" '
                                        . ($menu['submenu'] == 1 ? 'checked="checked"' : "") . '>
                                        <label for="submenu_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            Enabled
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="submenu"
                                        id="submenu_off" value="0" '
                                        . ($menu['submenu'] == 1 ? "" : 'checked="checked"') . '>
                                        <label for="submenu_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            Disabled
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
            $this->ps_html .= '<div class="sub_menu">';
            $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    ' . $this->l('Menu Type:') . '
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="make" id="make_on"
                                        value="1" ' . ($menu['make'] == 1 ? 'checked="checked"' : '') . ' >
                                        <label for="make_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            ' . $this->l('Mega Menu') . '
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="make" id="make_off"
                                        value="0" ' . ($menu['make'] == 1 ? '' : 'checked="checked"') . '>
                                        <label for="make_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            ' . $this->l('Dropdown') . '
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
            $this->ps_html .= '<div class="after_dropdown">';
            $this->ps_html .='<div class="form-group submenulink">                
                                <label class="control-label col-lg-3" for="typelink">
                                    ' . $this->l('Menu:') . '
                                </label>
                                <div class="col-lg-3">
                                    <select name="typelink" id="typelink">
                                        <option ' . ($menu['typelink'] == "existlinks" ? "selected" : "")
                                        . ' value="existlinks">' . $this->l('One Level') . '</option>
                                        <option ' . ($menu['typelink'] == "treelinks" ? "selected" : "")
                                        . ' value="treelinks">' . $this->l('Multi Level') . '</option>
                                    </select>    
                                </div>
                            </div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '<div class="after_make">';

            $this->ps_html .= '<div class="form-group">        
                                <label class="control-label col-lg-3">
                                    ' . $this->l('Full Width:') . '
                                </label>
                                <div class="input-group col-lg-3">
                                    <span class="switch prestashop-switch">
                                        <input onclick="toggleDraftWarning(false);showOptions(true);
                                        showRedirectProductOptions(false);" type="radio" name="fullwidth" id="full_on"
                                        value="1" ' . ($menu['fullwidth'] == 1 ? 'checked="checked"' : '') . '>
                                        <label for="full_on" class="radioCheck">
                                            <i class="icon-check-sign text-success"></i>
                                            Enabled
                                        </label>
                                        <input onclick="toggleDraftWarning(true);showOptions(false);
                                        showRedirectProductOptions(true);" type="radio" name="fullwidth" id="full_off"
                                        value="0" ' . ($menu['fullwidth'] == 1 ? '' : 'checked="checked"') . '>
                                        <label for="full_off" class="radioCheck">
                                            <i class="icon-ban-circle text-danger"></i>
                                            Disabled
                                        </label>
                                        <a class="slide-button btn btn-default"></a>
                                    </span>
                                </div>
                            </div>';
            $this->ps_html .='<div class="form-group">
                                <label class="button_addrow">'.$this->l('Add Row').'</label>';
            $subs = Tools::jsonDecode($this->ps_cipherTool->decrypt(@$menu['sub']));
            // echo"<pre>";
            // var_dump($subs);die;
            $size = count($subs) - 1;
            // nếu chưa có cột nào
            if (empty($subs)) {
                $this->ps_html .='<div class="form-group">            
                    <div class="add_row row_0 end_row">
                        <div class="row_size">
                            <label>'.$this->l('Row Size:').' </label>
                            <select name="size[0][]" id="row_size_0"
                            onchange="createcol(0,\'row_size_0\',0)">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                            </select>
                            <label class="button_removerow">'.$this->l('Remove Row').'</label>
                        </div>
                        <div class="list_col list_col_0">
                            <div class="col col_0">
                            <div style="display:none" class="cancel_col" onclick="cancel_col(0,0)">
                            <i class="process-icon-cancel "></i></div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">
                                        ' . $this->l('Label:') . '
                                    </label>
                                    <div class="col-lg-9">
                                        <input type="text" id="labelmenu_col_0" 
                                        class="copy2friendlyUrl updateCurrentText" name="sub[0][0][]"
                                        value="" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">
                                        ' . $this->l('Custom Class:') . '
                                    </label>
                                    <div class="col-lg-9">
                                        <input type="text" id="customclass_col_0"
                                        class="copy2friendlyUrl updateCurrentText" name="sub[0][0][]"
                                        value="" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">
                                        ' . $this->l('Width:') . '
                                    </label>
                                    <div class="col-lg-9">
                                        <input type="text" id="width_col_0"
                                        class="copy2friendlyUrl updateCurrentText" name="sub[0][0][]"
                                        value="" >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">
                                        ' . $this->l('Type:') . '
                                    </label>
                                    <div class="col-lg-9">
                                        <select name="sub[0][0][]" id="typelink_col_0"
                                        onchange="SelectTypeLink(0,this,0)">
                                            <option value="">' . $this->l('Hide This') . '</option>
                                            <option value="link">' . $this->l('System Link') . '</option>
                                            <option value="customhtml">'.$this->l('Custom Html').'</option>
                                            <option value="loadhook">' . $this->l('Hook') . '</option>
                                            <option value="product">' . $this->l('Product') . '</option>
                                            <option value="productlist">' . $this->l('Product List')
                                            . '</option>
                                        </select>
                                        <br>
                                        <div class="custom_url_col custom_url_col_0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
            } else {
                // đã tồn tại cột
                foreach ($subs as $key => $sub) {
                    $this->ps_html .='<div class="add_row row_' . (int) $key . ' '
                                    . ((int) $key == (int) $size ? "end_row" : "") . '">
                                            <div class="row_size">
                                                <label>'.$this->l('Row Size:').' </label>
                                                <select name="size[' . (int) $key . '][]" id="row_size_'
                                                . (int) $key . '" onchange="createcol('
                                                . (int) $key . ',\'row_size_' . (int) $key . '\','
                                                . (int) $key . ')">
                                                <option ' . (count($sub) == 1 ? "selected" : ""). ' value="1">1</option>
                                                <option ' . (count($sub) == 2 ? "selected" : ""). ' value="2">2</option>
                                                <option ' . (count($sub) == 3 ? "selected" : ""). ' value="3">3</option>
                                                <option ' . (count($sub) == 4 ? "selected" : ""). ' value="4">4</option>
                                                <option ' . (count($sub) == 5 ? "selected" : ""). ' value="5">5</option>
                                                <option ' . (count($sub) == 6 ? "selected" : ""). ' value="6">6</option>
                                                <option ' . (count($sub) == 7 ? "selected" : ""). ' value="7">7</option>
                                                <option ' . (count($sub) == 8 ? "selected" : ""). ' value="8">8</option>
                                                <option ' . (count($sub) == 9 ? "selected" : ""). ' value="9">9</option>
                                                </select>
                                                <label class="button_removerow" '
                                                . ((int) $key > 0 ? 'onclick="removerow(' . (int) $key . ')' : '')
                                                . '" >'.$this->l('Remove Row').'</label>
                                            </div>';
                    $this->ps_html .='<div class="list_col list_col_' . (int) $key . '">';
                    foreach ($sub as $k => $col) {
                        $this->ps_html .='<div class="col col_' . (int) $k . '" '
                        . ((count($sub) > 1) ? "style='float: left; width: 295px;'" : "") . '>
                                            <div class="cancel_col" onclick="cancel_col('
                                            . (int) $key . ','
                                            . (int) $k . ')"><i class="process-icon-cancel "></i></div>
                                            <div class="form-group">
                                                <label class="control-label col-lg-3">
                                                    ' . $this->l('Label:') . '
                                                </label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="labelmenu_col_'
                                                    . (int) $k
                                                    . '" class="copy2friendlyUrl updateCurrentText" name="sub['
                                                    . (int) $key . '][' . (int) $k . '][]" value="'
                                                    . htmlentities($col[0], ENT_QUOTES, "UTF-8") . '" >
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-lg-3">
                                                    ' . $this->l('Custom Class:') . '
                                                </label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="customclass_col_' . (int) $k
                                                    . '" class="copy2friendlyUrl updateCurrentText" name="sub['
                                                    . (int) $key . '][' . (int) $k . '][]" value="'
                                                    . htmlentities($col[1], ENT_QUOTES, "UTF-8") . '" >
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-lg-3">
                                                    ' . $this->l('Width:') . '
                                                </label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="width_col_' . (int) $k
                                                    . '" class="copy2friendlyUrl updateCurrentText" name="sub['
                                                    . (int) $key . '][' . (int) $k . '][]" value="'
                                                    . htmlentities($col[2], ENT_QUOTES, "UTF-8") . '" >
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-lg-3">
                                                    ' . $this->l('Type:') . '
                                                </label>
                                                <div class="col-lg-9">
                                                    <select name="sub[' . (int) $key . ']['
                                                    .(int) $k.'][]" id="typelink_col_'
                                                    . (int) $k . '" onchange="SelectTypeLink('
                                                    .(int) $key. ',this,' . (int) $k . ')">
                                                        <option value="">' . $this->l('Hide This') . '</option>
                                                        <option ' . ($col[3] == "link" ? "selected" : "")
                                                        . ' value="link">' . $this->l('System Link') . '</option>
                                                        <option ' . ($col[3] == "customhtml" ? "selected" : "")
                                                        .' value="customhtml">'.$this->l('Custom Html') . '</option>
                                                        <option ' . ($col[3] == "loadhook" ? "selected" : "")
                                                        . ' value="loadhook">' . $this->l('Hook') . '</option>
                                                        <option ' . ($col[3] == "product" ? "selected" : "")
                                                        . ' value="product">' . $this->l('Product') . '</option>
                                                        <option ' . ($col[3] == "productlist" ? "selected" : "")
                                                        .' value="productlist">'.$this->l('Product List').'</option>
                                                    </select>
                                                    <br>
                                                    <div class="custom_url_col custom_url_col_' . (int) $k . '">
                                                        <script type="text/javascript">
                                                            SelectEditTypeLink(' . (int) $key . ',"'
                                                            . pSQL(htmlspecialchars(strip_tags($col[3]))) . '",'
                                                            . (int) $k . ',' . (int) $menu['id'] . ')
                                                        </script>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
                    }
                    $this->ps_html .='</div>';
                    $this->ps_html .='</div>';
                }
            }
            $this->ps_html .='</div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '</div>';
            $this->ps_html .= '</form><br>';
        }
    }

    public function getContent()
    {
        if (MegamenuReturnVersion() == "1.5") {// load CSS theme if Prestashop 1.5
            $this->context->controller->addCSS($this->_path . 'views/css/admin-theme-1.5.css', 'all');
            $hrefLinkFontIcon = "//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css";
            $this->ps_html.="<link href='".$hrefLinkFontIcon."' rel='stylesheet' />";
        }
        //////// add media file
        $this->context->controller->addJqueryUI('ui.datepicker');
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->addJqueryUI('ui.tooltip');
        $this->context->controller->addJqueryPlugin('colorpicker');
        $this->context->controller->addJS(_PS_JS_DIR_ . 'tiny_mce/tiny_mce.js');
        $this->context->controller->addJS(_PS_JS_DIR_ . 'tinymce.inc.js');
        $this->context->controller->addJS(_PS_JS_DIR_ . 'admin/tinymce.inc.js');
        $this->context->controller->addJS(_PS_JS_DIR_ . 'tiny_mce/tinymce.min.js');
        $this->context->controller->addCSS($this->_path . 'views/css/megamenu.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/back_end.css', 'all');
        ////////////////
        $this->ps_html.=$this->headerHtml();
        if (Tools::getValue('msg') == '1' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->ps_html .= $this->displayConfirmation($this->l('The menu has been updated successfully.'));
        }
        if (Tools::getValue('action') == 'savelabel' || Tools::getValue('action') == 'savestay') {
            $labelmenu = Tools::getValue('labelmenu');
            $customclass = Tools::getValue('customclass');
            $submenu = Tools::getValue('submenu');

            $status = Tools::getValue('status');
            $typelink = Tools::getValue('typelink');
            $custom_url = Tools::getValue('custom_url');
            $typelink_one = Tools::getValue('typelink_one');
            $custom_url_one = Tools::getValue('custom_url_one');
            $mega_id_shop = (int) Tools::getValue('mega_id_shop');
            if ($typelink_one == 'htmllink') {
                $custom_url_one = Tools::stripslashes($custom_url_one);
                $custom_url_one = $this->ps_cipherTool->encrypt(Tools::getValue('custom_url_one'));
            }
            $make = Tools::getValue('make');
            $fullwidth = Tools::getValue('fullwidth');
            // echo "<pre>";
            // var_dump($_POST['sub']);die;
            $_sub = array();
            if (is_array(Tools::getValue('sub'))) {
                foreach (Tools::getValue('sub') as $v1) {
                    $_sub_level2 = array();
                    foreach ($v1 as $v2) {
                        if ($v2[3]=='customhtml') {
                            $v2[4]=Tools::stripslashes($v2[4]);
                        }
                        $_sub_level2[] = $v2;
                    }
                    $_sub[] = $_sub_level2;
                }
            }
            //$sub =$_sub;
            //echo '<pre>';print_r($sub);die;

            $sub = $this->ps_cipherTool->encrypt(Tools::jsonEncode($_sub));

            // if($make==0){
            // $sub='W1tbIiIsIiIsIiIsIiJdXV0=';
            // }
            $position = count($this->getMegamenu()) + 1;
            $id_lang = Tools::getValue('language');
            if (!empty($labelmenu)) {
                $id_menu_new=(int) Tools::getValue('id');
                if (Tools::getValue('id') == 0) {
                    $sql = "INSERT INTO "
                    . _DB_PREFIX_
                    . "megamenu(`label`,`customclass`,`submenu`,`typelink`,`custom_url`,`typelink_one`
                    ,`custom_url_one`,`make`,`fullwidth`,`sub`,`position`,`id_lang`,`id_shop`,`action`)
                    VALUES ('" . pSQL($labelmenu) . "','" . pSQL($customclass) . "'," . (int) $submenu . ",'"
                    . pSQL($typelink) . "','" . pSQL($custom_url) . "','" . pSQL($typelink_one) . "','"
                    . pSQL($custom_url_one) . "'," . (int) $make . ","
                    . (int) $fullwidth . ",'" . pSQL($sub) . "'," . (int) $position
                    . "," . (int) $id_lang . ",".(int) $mega_id_shop. ",". (int)$status . ");";
                    Db::getInstance()->execute($sql);
                    $id_menu_new=Db::getInstance()->Insert_ID();
                } else {
                    $sql = "UPDATE " . _DB_PREFIX_ . "megamenu SET `label`='" . pSQL($labelmenu) . "',`customclass`='"
                    . pSQL($customclass) . "',`submenu`=" . (int) $submenu . ",`typelink`='" . pSQL($typelink)
                    . "',`custom_url`='" . pSQL($custom_url) . "',`typelink_one`='" . pSQL($typelink_one)
                    . "',`custom_url_one`='" . pSQL($custom_url_one) . "',`make`=" . (int) $make . ",`fullwidth`="
                    . (int) $fullwidth . ",`sub`='" . pSQL($sub) . "',`id_lang`="
                    . (int) $id_lang . ",`action`=" . (int) $status
                    . " WHERE `id`=" . (int) Tools::getValue('id');
                    Db::getInstance()->execute($sql);
                }
                // chèn category_group
                // xóa toàn bộ group cũ
                $sql = 'DELETE FROM '._DB_PREFIX_."megamenu_group WHERE id_menu=".(int) $id_menu_new;
                Db::getInstance()->execute($sql);
                $groupBox= Tools::getValue('groupBox');
                if (!empty($groupBox)) {
                    // chèn mới
                    foreach ($groupBox as $group_id) {
                        $sql = 'INSERT INTO '._DB_PREFIX_."megamenu_group(id_menu,id_group) VALUES('"
                        .(int) $id_menu_new."','".(int) $group_id."')";
                        Db::getInstance()->execute($sql);
                    }
                }
                if (Tools::getValue('action') == 'savestay') {
                    $admin_link =  Context::getContext()->link->getAdminLink('AdminModules');
                    $admin_link .= '&configure='.$this->name.'&module_name=bamegamenu&editlabel=true&action=editlabel';
                    $admin_link .= '&msg=1&id='.(int) $id_menu_new;
                    Tools::redirectAdmin($admin_link);
                    die;
                }
                $this->ps_html .= $this->displayConfirmation($this->l('The menu has been updated successfully.'));
            } else {
                $this->ps_html .=$this->displayError($this->l('The menu has been updated failure.'));
            }
        }
        if (Tools::getValue('action') == 'addlabel') {
            $this->ps_html.=$this->addMenu();
        } elseif (Tools::getValue('action') == 'editlabel' || Tools::getValue('action') == 'savestay') {
            $this->ps_html.=$this->editMenu();
        } elseif (Tools::getValue('customicon') == 1) {
            $this->ps_html.=$this->renderFormCustomIcon();
        } elseif (Tools::getValue('configmega') == 1) {
            $this->ps_html.=$this->saveConfigMega();
            $this->ps_html.=$this->renderFormConfigMega();
        } else {
            $this->ps_html.=$this->deleteMenu();
            $this->ps_html.=$this->updateStatusMenu();
            $this->ps_html.=$this->renderFormListLabel();
        }

        return $this->ps_html;
    }

    public function hookDisplayHeader($params)
    {
        // Change  Module belong language type
        $language = $this->context->language;
        //echo '<pre>';var_dump($language);die;
        if ($language->is_rtl == 1) {
            Configuration::updateValue('bamenu_ltype', 0);
        } else {
            Configuration::updateValue('bamenu_ltype', 1);
        }
        $this->context->controller->addCSS($this->_path . 'views/css/font.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/megamenu.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/font_end.css', 'all');
        
        if (Configuration::get('bamenu_style') == "dark") {
            $this->context->controller->addCSS($this->_path . 'views/css/dark.css', 'all');
        } elseif (Configuration::get('bamenu_style') == "light") {
            $this->context->controller->addCSS($this->_path . 'views/css/light.css', 'all');
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/light.css', 'all');
        }
        if (Configuration::get('bamenu_vertical') == 1) {
            $this->context->controller->addCSS($this->_path . 'views/css/vertical_menu.css', 'all');
        }
        $this->context->controller->addCSS($this->_path . 'views/css/style_Mobie.css', 'all');
        if (Configuration::get('stay_on_top') == 1) {
            $this->context->controller->addJS(($this->_path) . 'views/js/ontop.js', 'all');
        }
        $this->context->controller->addCSS($this->_path . 'views/css/3rd_customize.css', 'all');
        ///// 1.7+
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->context->smarty->assign('bamegamenu_path', $this->_path);
            //echo $this->_path;die;
            return $this->display(__FILE__, '/views/templates/front/head.tpl');
        } else {
            $linkCssFontAweSome='https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css';
            $this->context->controller->addCSS($linkCssFontAweSome, 'all');
            $this->context->controller->addJS(($this->_path) . 'views/js/megamenu.js', 'all');
        }
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $out = '';
        $out .= "<script>if (typeof(pathCSS) === 'undefined') {
                    var pathCSS = '" . _THEME_CSS_DIR_ . "' ;
                };</script>";
        // load CSS theme if Prestashop 1.5
        if (MegamenuReturnVersion() == "1.5" && $_REQUEST['configure'] == 'bamegamenu') {
            $this->context->controller->addJS(($this->_path) . 'views/js/jquery-1.11.2.min.js', 'all');
        }
        return $out;
    }

    private function getLinkMenu($item)
    {
        if (empty($item)) {
            return false;
        }
        $id_lang = (int) $this->context->language->id;
        $id_customer=$this->context->customer->id;
        
        preg_match($this->pattern, $item, $value);
        $id = (int) Tools::substr($item, Tools::strlen($value[1]), Tools::strlen($item));
        switch (Tools::substr($item, 0, Tools::strlen($value[1]))) {
            case 'CAT':
                $cat = new Category($id);
                // check Customer can see this category
                $checkAccess= $cat->checkAccess($id_customer);
                if ($checkAccess===false) {
                    return '';
                }
                    
                
                if (MegamenuReturnVersion() == "1.5") {
                    $catlink = new Link;
                    $linkRewrite=is_array($cat->link_rewrite) ? $cat->link_rewrite[$id_lang] : $cat->link_rewrite;
                    $link = $catlink->getCategoryLink($cat, $linkRewrite, $id_lang);
                } else {
                    $link = Tools::HtmlEntitiesUTF8($cat->getLink());
                }
                break;
            case 'CMS':
                $cms = CMS::getLinks((int) $id_lang, array($id));
                $link = Tools::HtmlEntitiesUTF8($cms[0]['link']);
                break;
            case 'CMS_CAT':
                $category = new CMSCategory((int) $id, (int) $id_lang);
                $link = Tools::HtmlEntitiesUTF8($category->getLink());
                break;
            case 'ALLMAN':
                $manlink = new Link;
                $link = $manlink->getPageLink('manufacturer');
                break;
            case 'MAN':
                $manufacturer = new Manufacturer((int) $id, (int) $id_lang);
                if (!is_null($manufacturer->id)) {
                    if ((int) Configuration::get('PS_REWRITING_SETTINGS')) {
                        $manufacturer->link_rewrite = Tools::link_rewrite($manufacturer->name);
                    } else {
                        $manufacturer->link_rewrite = 0;
                    }
                    $manlink = new Link;
                    $link=Tools::HtmlEntitiesUTF8($manlink->getManufacturerLink((int)$id, $manufacturer->link_rewrite));
                }
                break;
            case 'ALLSUP':
                $suplink = new Link;
                $link = $suplink->getPageLink('supplier');
                break;
            case 'SUP':
                $supplier = new Supplier((int) $id, (int) $id_lang);
                if (!is_null($supplier->id)) {
                    $suplink = new Link;
                    $link = Tools::HtmlEntitiesUTF8($suplink->getSupplierLink((int) $id, $supplier->link_rewrite));
                }
                break;
            case 'SHOP':
                $shop = new Shop((int) $id);
                if (Validate::isLoadedObject($shop)) {
                    $link = Tools::HtmlEntitiesUTF8($shop->getBaseURL());
                }
                break;
        }
        return $link;
    }

    private function getLinkTitleMenu($item)
    {
        if (empty($item)) {
            return false;
        }
        $id_lang = (int) $this->context->language->id;

        preg_match($this->pattern, $item, $value);
        $id = (int) Tools::substr($item, Tools::strlen($value[1]), Tools::strlen($item));
        switch (Tools::substr($item, 0, Tools::strlen($value[1]))) {
            case 'CAT':
                $cat = new Category($id);
                $title = $cat->name[$id_lang];
                break;
            case 'CMS':
                $cms = CMS::getLinks((int) $id_lang, array($id));
                $title = Tools::safeOutput($cms[0]['meta_title']);
                break;
            case 'CMS_CAT':
                $category = new CMSCategory((int) $id, (int) $id_lang);
                $title = $category->name;
                break;
            case 'ALLMAN':
                $title = $this->l('All manufacturers');
                break;
            case 'MAN':
                $manufacturer = new Manufacturer((int) $id, (int) $id_lang);
                if (!is_null($manufacturer->id)) {
                    $title = Tools::safeOutput($manufacturer->name);
                }
                break;
            case 'ALLSUP':
                $title = $this->l('All suppliers');
                break;
            case 'SUP':
                $supplier = new Supplier((int) $id, (int) $id_lang);
                if (!is_null($supplier->id)) {
                    $title = $supplier->name;
                }
                break;
            case 'SHOP':
                $shop = new Shop((int) $id);
                if (Validate::isLoadedObject($shop)) {
                    $title = $shop->name;
                }
                break;
        }
        return $title;
    }

    private function getSubMenuProduct($id, $list = 0)
    {
        $id_lang = (int) $this->context->language->id;
        $product = new Product((int) $id, true, (int) $id_lang);
        if ($product->active==0) {
            return '';
        }
        $image = Image::getCover($id);
        $link = new Link();
        // image formatted name
        $ImageFormattedName = '';
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
            $ImageFormattedName = ImageType::getFormattedName('home');
        } else {
            $ImageFormattedName = ImageType::getFormatedName('home');
        }
        
        $imagePath=$link->getImageLink($product->link_rewrite, $image['id_image'], $ImageFormattedName);
        $html = "";
        if ($list == 0) {
            $html .="<ul class='menuproduct clearfix'>";
        }
        $html .="<li>";
        $html .="<a href='" . Tools::HtmlEntitiesUTF8($product->getLink()) . "' title='" . $product->name . "'>
        <span class='menu-item-link-text'>";
        $html .="<img src='//" . $imagePath . "' alt='" . $product->name . "'>";
        $html .="<span class='name'>" . $product->name . "</span>";
        $oldprice=(!isset($product->specificPrice['reduction']) || $product->specificPrice['reduction']<=0)?false:true;
        $html .="<span class='price' style='" . ($oldprice==false? 'width:100%;' : '')
        . "'>" . Tools::displayPrice($product->getPrice(true, null, 2)) . "</span>";
        if (isset($product->specificPrice['reduction']) && $product->specificPrice['reduction'] > 0) {
            $PriceWithoutReduct = Tools::displayPrice(round($product->getPriceWithoutReduct(false, null), 2));
            $html .="<span class='old_price'>". $PriceWithoutReduct
            . "</span>";
        }
        $html .="</span>";
        $html .="</a>";
        $html .="</li>";
        if ($list == 0) {
            $html .="</ul>";
        }
        return $html;
    }

    private function getCategoriesProduct($cat_id)
    {
        $sql_cat = "SELECT * FROM  " . _DB_PREFIX_ . "category WHERE id_category=" . (int) $cat_id;
        $results_cat = Db::getInstance()->executeS($sql_cat);
        $results_cat = $results_cat[0];
        $sql = "SELECT id_category FROM  " . _DB_PREFIX_ . "category WHERE nleft>" . (int) $results_cat['nleft']
        . " AND nright<" . (int) $results_cat['nright'];
        $results = Db::getInstance()->executeS($sql);
        $cat = array();
        foreach ($results as $value) {
            $cat[] = (int) $value['id_category'];
        }
        $cat[] = $cat_id;
        return $cat;
    }

    private function getSubMenuProductList($item, $number)
    {
        preg_match($this->pattern, $item, $value);
        $cat_id = (int) Tools::substr($item, Tools::strlen($value[1]), Tools::strlen($item));
        $cat = $this->getCategoriesProduct($cat_id);
        $cat = array_map("intval", $cat);
        $sql = "SELECT id_product FROM  " . _DB_PREFIX_ . "product WHERE active=1 AND id_category_default IN ("
        . implode($cat, ',') . ") ORDER BY id_product DESC LIMIT 0," . (int) $number;
        $results = Db::getInstance()->executeS($sql);
        $html = "";
        if (!empty($results)) {
            $html .="<div class='menulistproduct clearfix'>";
            $html .="<ul class='menuproduct clearfix'>";
            foreach ($results as $value) {
                $html .=$this->getSubMenuProduct($value['id_product'], 1);
            }
            $html .="</ul>";
            $html .="</div>";
        }
        return $html;
    }

    private function getSubMenuLink($items = array())
    {
        if (empty($items)) {
            return false;
        }
        $html = '';
        $html .="<ul class='menulink clearfix'>";
        foreach ($items as $v) {
            $link=$this->getLinkMenu($v);
            if (empty($link)) {
                continue;
            }
            $html .="<li>";
            $html .="<a href='" . $this->getLinkMenu($v) . "' title='" . $this->getLinkTitleMenu($v)
            . "'><span class='menu-item-link-text'>" . $this->getLinkTitleMenu($v) . "</span></a>";
            $html .="</li>";
        }
        $html .="</ul>";
        return $html;
    }

    private function getSubMenuHtml($content)
    {
        $html = '';
        $html .="<ul class='menuhtml clearfix'>";
        $html .="<li>";
        $html .=html_entity_decode($content);
        $html .="</li>";
        $html .="</ul>";
        return $html;
    }

    private function getLoadHook($hook)
    {
        //echo $hook;die("aaaaa");
        $html = null;
        $html .="<ul class='loadhook clearfix'>";
        $html .="<li>";
        $html .= Hook::exec($hook);
        $html .="</li>";
        $html .="</ul>";

        return $html;
    }

    private function existLinkMenu($item)
    {
        if (empty($item)) {
            return false;
        }
        $id_lang = (int) $this->context->language->id;
        $id_shop = (int) Shop::getContextShopID();

        preg_match($this->pattern, $item, $value);
        $id = (int) Tools::substr($item, Tools::strlen($value[1]), Tools::strlen($item));
    
        switch (Tools::substr($item, 0, Tools::strlen($value[1]))) {
            case 'CAT':
                if (MegamenuReturnVersion() == "1.5") {
                    $categoryGetNestedCategories=Category__getNestedCategories($id, $id_lang, true, $this->user_groups);
                    $this->ps_menu .= $this->generateCategoriesMenu($categoryGetNestedCategories);
                } else {
                    $categoryGetNestedCategories=Category__getNestedCategories($id, $id_lang, true, $this->user_groups);
                    
                    $this->ps_menu .= $this->generateCategoriesMenu($categoryGetNestedCategories);
                }
                break;

            case 'PRD':
                $id_product_tmp = Tools::getValue('id_product');
                $str_tmp = ' class="sfHover endli"';
                $selected = ($this->page_name == 'product' && ($id_product_tmp == $id)) ? $str_tmp : 'class="endli"';
                $product = new Product((int) $id, true, (int) $id_lang);
                if (!is_null($product->id)) {
                    $this->ps_menu .= '<li' . $selected . '><a href="' . Tools::HtmlEntitiesUTF8($product->getLink())
                    . '" title="' . $product->name . '"><span class="menu-item-link-text">' . $product->name
                    . '</span></a></li>' . PHP_EOL;
                }
                break;

            case 'CMS':
                $classHover=' class="sfHover endli"';
                $selected=($this->page_name=='cms'&&(Tools::getValue('id_cms')==$id))?$classHover:'class="endli"';
                $cms = CMS::getLinks((int) $id_lang, array($id));
                if (count($cms)) {
                    $this->ps_menu .= '<li' . $selected . '><a class="endli" href="'
                    . Tools::HtmlEntitiesUTF8($cms[0]['link']) . '" title="' . Tools::safeOutput($cms[0]['meta_title'])
                    . '"><span class="menu-item-link-text">' . Tools::safeOutput($cms[0]['meta_title'])
                    . '</span></a></li>' . PHP_EOL;
                }
                break;

            case 'CMS_CAT':
                $category = new CMSCategory((int) $id, (int) $id_lang);
                if (count($category)) {
                    $this->ps_menu .= '<li><a href="' . Tools::HtmlEntitiesUTF8($category->getLink())
                    . '" title="' . $category->name . '"><span class="menu-item-link-text">' . $category->name
                    . '</span></a>';
                    $this->getCMSMenuItems($category->id);
                    $this->ps_menu .= '</li>' . PHP_EOL;
                }
                break;

            // Case to handle the option to show all Manufacturers
            case 'ALLMAN':
                $link = new Link;
                $this->ps_menu .= '<li><a href="' . $link->getPageLink('manufacturer') . '" title="'
                . $this->l('All manufacturers') . '"><span class="menu-item-link-text">'
                . $this->l('All manufacturers') . '</span></a><ul>' . PHP_EOL;
                $manufacturers = Manufacturer::getManufacturers();
                foreach ($manufacturers as $manufacturer) {
                    $this->ps_menu .= '<li class="endli"><a href="'
                    . $link->getManufacturerLink((int) $manufacturer['id_manufacturer'], $manufacturer['link_rewrite'])
                    . '" title="' . Tools::safeOutput($manufacturer['name']) . '"><span class="menu-item-link-text">'
                    . Tools::safeOutput($manufacturer['name']) . '</span></a></li>' . PHP_EOL;
                }
                $this->ps_menu .= '</ul>';
                break;

            case 'MAN':
                $id_manufacturer_tmp = Tools::getValue('id_manufacturer');
                $class_tmp =' class="sfHover endli"';
                $selected=($this->page_name=='manufacturer'&&($id_manufacturer_tmp==$id))?$class_tmp:'class="endli"';
                $manufacturer = new Manufacturer((int) $id, (int) $id_lang);
                if (!is_null($manufacturer->id)) {
                    if ((int) Configuration::get('PS_REWRITING_SETTINGS')) {
                        $manufacturer->link_rewrite = Tools::link_rewrite($manufacturer->name);
                    } else {
                        $manufacturer->link_rewrite = 0;
                    }
                    $link = new Link;
                    $this->ps_menu .= '<li' . $selected . '><a  href="'
                    . Tools::HtmlEntitiesUTF8($link->getManufacturerLink((int) $id, $manufacturer->link_rewrite))
                    . '" title="' . Tools::safeOutput($manufacturer->name) . '"><span class="menu-item-link-text">'
                    . Tools::safeOutput($manufacturer->name) . '</span></a></li>' . PHP_EOL;
                }
                break;

            // Case to handle the option to show all Suppliers
            case 'ALLSUP':
                $link = new Link;
                $this->ps_menu .= '<li><a href="' . $link->getPageLink('supplier') . '" title="'
                . $this->l('All suppliers') . '"><span class="menu-item-link-text">' . $this->l('All suppliers')
                . '</span></a><ul>' . PHP_EOL;
                $suppliers = Supplier::getSuppliers();
                foreach ($suppliers as $supplier) {
                    $this->ps_menu .= '<li class="endli" ><a  href="'
                    . $link->getSupplierLink((int) $supplier['id_supplier'], $supplier['link_rewrite'])
                    . '" title="' . Tools::safeOutput($supplier['name']) . '"><span class="menu-item-link-text">'
                    . Tools::safeOutput($supplier['name']) . '</span></a></li>' . PHP_EOL;
                }
                $this->ps_menu .= '</ul>';
                break;

            case 'SUP':
                $id_supplier_tmp = Tools::getValue('id_supplier');
                $str_tmp = ' class="sfHover endli"';
                $selected = ($this->page_name == 'supplier' && ($id_supplier_tmp == $id)) ? $str_tmp : 'class="endli"';
                $supplier = new Supplier((int) $id, (int) $id_lang);
                if (!is_null($supplier->id)) {
                    $link = new Link;
                    $this->ps_menu .= '<li' . $selected . '><a  href="'
                    . Tools::HtmlEntitiesUTF8($link->getSupplierLink((int) $id, $supplier->link_rewrite))
                    . '" title="' . $supplier->name . '"><span class="menu-item-link-text">' . $supplier->name
                    . '</span></a></li>' . PHP_EOL;
                }
                break;

            case 'SHOP':
                $str_tmp =  ' class="sfHover endli"';
                $shop_id_tmp = $this->context->shop->id;
                $selected = ($this->page_name == 'index' && ($shop_id_tmp == $id)) ? $str_tmp : 'class="endli"';
                $shop = new Shop((int) $id);
                if (Validate::isLoadedObject($shop)) {
                    $link = new Link;
                    $this->ps_menu .= '<li' . $selected . '><a href="'
                    . Tools::HtmlEntitiesUTF8($shop->getBaseURL()) . '" title="' . $shop->name
                    . '"><span class="menu-item-link-text">' . $shop->name . '</span></a></li>' . PHP_EOL;
                }
                break;
            case 'LNK':
                $link = MenuTopLinks::get((int) $id, (int) $id_lang, (int) $id_shop);
                if (count($link)) {
                    if (!isset($link[0]['label']) || ($link[0]['label'] == '')) {
                        $default_language = Configuration::get('PS_LANG_DEFAULT');
                        $shop_id_tmp =  (int) Shop::getContextShopID();
                        $link = MenuTopLinks::get($link[0]['id_linksmenutop'], $default_language, $shop_id_tmp);
                    }
                    $this->ps_menu .= '<li class="endli" ><a href="' . Tools::HtmlEntitiesUTF8($link[0]['link'])
                    . '"' . (($link[0]['new_window']) ? ' onclick="return !window.open(this.href);"' : '')
                    . ' title="' . Tools::safeOutput($link[0]['label']) . '"><span class="menu-item-link-text">'
                    . Tools::safeOutput($link[0]['label']) . '</span></a></li>' . PHP_EOL;
                }
                break;
        }
    }

    private function generateCategoriesMenu($categories)
    {
        $html = '';
        foreach ($categories as $category) {
            if ($category['level_depth'] > 1) {
                $cat = new Category($category['id_category']);
                $link = Tools::HtmlEntitiesUTF8($cat->getLink());
            } else {
                $link = $this->context->link->getPageLink('index');
            }
            $id_tmp = (int) Tools::getValue('id_category');
            $id_tmp2 = (int) $category['id_category'];
            $html .= '<li class="' . (($this->page_name == 'category' && $id_tmp == $id_tmp2) ? 'sfHoverForce' : '')
            . (empty($category['children']) ? 'endli' : 'children_' . $category['level_depth']) . '" >';
            $html .= '<a  href="' . $link . '" title="' . $category['name'] . '"><span class="menu-item-link-text">'
            . $category['name'] . '</span></a>' . (empty($category['children']) ? '' : '<span class="submore"></span>');

            if (isset($category['children']) && !empty($category['children'])) {
                $html .= '<ul>';
                $html .= $this->generateCategoriesMenu($category['children']);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }

    private function getCMSMenuItems($parent, $depth = 1, $id_lang = false)
    {
        $id_lang = $id_lang ? (int) $id_lang : (int) Context::getContext()->language->id;

        if ($depth > 3) {
            return;
        }
        $categories = $this->getCMSCategories(false, (int) $parent, (int) $id_lang);
        $pages = $this->getCMSPages((int) $parent);

        if (count($categories) || count($pages)) {
            $this->ps_menu .= '<ul>';

            foreach ($categories as $category) {
                $cat = new CMSCategory((int) $category['id_cms_category'], (int) $id_lang);

                $this->ps_menu .= '<li class="endli">';
                $this->ps_menu .= '<a href="' . Tools::HtmlEntitiesUTF8($cat->getLink())
                . '"><span class="menu-item-link-text">' . $category['name'] . '</span></a>';
                $this->getCMSMenuItems($category['id_cms_category'], (int) $depth + 1);
                $this->ps_menu .= '</li>';
            }

            foreach ($pages as $page) {
                $cms = new CMS($page['id_cms'], (int) $id_lang);
                $links = $cms->getLinks((int) $id_lang, array((int) $cms->id));
                $id_tmp = (int) Tools::getValue('id_cms');
                $str_tmp= ' class="sfHoverForce endli"';
                $selected = ($this->page_name == 'cms' && ($id_tmp == $page['id_cms'])) ? $str_tmp : 'class="endli"';
                $this->ps_menu .= '<li ' . $selected . '>';
                $this->ps_menu .= '<a  href="' . $links[0]['link'] . '"><span class="menu-item-link-text">'
                . $cms->meta_title . '</span></a>';
                $this->ps_menu .= '</li>';
            }

            $this->ps_menu .= '</ul>';
        }
    }

    public function getMenu()
    {
        
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "megamenu WHERE action=1 AND id_lang=" . $id_lang
        . " AND id_shop =".(int) $id_shop
        . " ORDER BY position ASC";
        $results = Db::getInstance()->executeS($sql);
        // kiểm tra quyền truy cập từng menu
        if (!empty($results)) {
            foreach ($results as $k => $v) {
                $is_access=$this->isAccessMenu($v['id']);
                if ($is_access==false) {
                    unset($results[$k]);
                }
            }
        }
        $results = array_values($results);
        
        if (count($results) > 0) {
            foreach ($results as $value) {
                $_menu_id = $value["id"];
                if (!empty($value['customclass'])) {
                    $_customclass = preg_replace('/\s+/', ' ', trim($value['customclass']));
                    $_customclass = " bamenuitem-" . str_replace(" ", " bamenuitem-", $_customclass);
                } else {
                    $_customclass = '';
                }
                $this->ps_menu .="<li class='mainmenu-parent menu-level-0_$_menu_id "
                . ($value['submenu'] == 1 ? 'submenu' : '') . htmlentities($_customclass, ENT_QUOTES, "UTF-8") . "'>";
                if ($value['typelink_one'] == 'htmllink') {
                    $this->ps_menu .="<span class='menu-item-link-text'>"
                    . $this->ps_cipherTool->decrypt($value['custom_url_one']) . "</span>";
                } else {
                    $t_o_tmp = $value['typelink_one'];
                    $t_custom_url_one = $value['custom_url_one'];
                    $c_c_tmp = $value['customclass'];
                    $this->ps_menu .="<a href='"
                    . ($t_o_tmp == 'externallink' ? $t_custom_url_one : $this->getLinkMenu($t_custom_url_one))
                    . "' title='" . htmlentities($value['label'], ENT_QUOTES, "UTF-8") . "' >
                                    <span class='menu-item-link-text "
                                    . ($c_c_tmp != '' ? htmlentities($c_c_tmp, ENT_QUOTES, "UTF-8") : '')
                                    . "'>" . htmlentities($value['label'], ENT_QUOTES, "UTF-8") . "</span>
                                </a><span class='submore'></span>";
                }
                if ($value['submenu'] == 1) {
                    $this->ps_menu .="<div style='display:none' class='submenu-level-0_$_menu_id sub "
                    . ((($value['fullwidth'] == 1) && ($value['make'] == 1)) ? "fullwidth" : "autowidth")
                    . " clearfix'> <div class='submenu-container'>";
                    if ($value['make'] == 1) {
                        $subs = Tools::jsonDecode($this->ps_cipherTool->decrypt($value['sub']));

                        foreach ($subs as $key => $sub) {
                            $this->ps_menu .="<div class='rows row_" . $key . "' >";
                            foreach ($sub as $k => $col) {
                                if (!empty($col)) {
                                    $this->ps_menu .="<div style='"
                                    . ($col[2] != '' ? 'width:' . htmlentities($col[2], ENT_QUOTES, "UTF-8") : '')
                                    . "' class='cols col_" . $k . " clearfix"
                                    . (empty($col[1]) ? '' : ' ' . htmlentities($col[1], ENT_QUOTES, "UTF-8")) . "'>";
                                    $this->ps_menu .="<div class='content_submenu clearfix'>";
                                    $this->ps_menu .=($col[0] != '' ? "<div class='titlesub clearfix'>
                                    <span class='menu-item-link-text'>" . $col[0] . "</span></div><span class='submore'>
                                    </span>" : '');

                                    switch ($col[3]) {
                                        case 'link':
                                            unset($col[0]);
                                            unset($col[1]);
                                            unset($col[2]);
                                            unset($col[3]);
                                            $items = $col;
                                            if (!empty($items)) {
                                                $this->ps_menu .=$this->getSubMenuLink($items);
                                            }

                                            break;
                                        case 'product':
                                            $id = $col[4];
                                            if (!empty($col[4])) {
                                                $this->ps_menu .=$this->getSubMenuProduct($id);
                                            }

                                            break;
                                        case 'productlist':
                                            $id = $col[4];
                                            if (!empty($col[4]) && !empty($col[5])) {
                                                $this->ps_menu .=$this->getSubMenuProductList($col[4], $col[5]);
                                            }

                                            break;
                                        case 'customhtml':
                                            if (!empty($col[4])) {
                                                $this->ps_menu .=$this->getSubMenuHtml($col[4]);
                                            }

                                            break;
                                        case 'loadhook':
                                            if (!empty($col[4])) {
                                                $this->ps_menu .=$this->getLoadHook($col[4]);
                                            }

                                            break;
                                        default:
                                            '';
                                    }
                                    $this->ps_menu .="</div>";
                                    $this->ps_menu .="</div>";
                                }
                            }
                            $this->ps_menu .="</div>";
                        }
                    } else {
                        if ($value['typelink'] == 'existlinks') {
                            $this->ps_menu .="<ul class='existlink clearfix'>";
                            $this->ps_menu .= $this->existLinkMenu($value['custom_url']);
                            $this->ps_menu .="</ul>";
                        } elseif ($value['typelink'] == 'treelinks') {
                            $this->ps_menu .="<ul class='treelinks clearfix'>";
                            $this->ps_menu .= $this->existLinkMenu($value['custom_url']);
                            $this->ps_menu .="</ul>";
                        }
                    }
                    $this->ps_menu .="</div></div>";
                }
                $this->ps_menu .="</li>";
            }
        }
    }

    public function hookDisplayTop($params)
    {
        $this->user_groups = ($this->context->customer->isLogged() ?
            $this->context->customer->getGroups() : array(Configuration::get('PS_UNIDENTIFIED_GROUP')));
        
        $this->page_name = Dispatcher::getInstance()->getController();

        if (Tools::isEmpty($this->ps_menu)) {
            $this->getMenu();
        }

        $this->context->smarty->assign(array(
            'MENU' => $this->ps_menu,
            'CSS' => $this->ps_cipherTool->decrypt(Configuration::get('bamenu_ccss')),
            'LANGUAGETYPE' => Configuration::get('bamenu_ltype'),
            'EFFECT' => Configuration::get('bamenu_effect'),
            'SPEED' => Configuration::get('bamenu_speed'),
            'STAY_ON_TOP' => Configuration::get('stay_on_top'),
            'STAY_ON_TOP_MOBILE' => Configuration::get('stay_on_top_mobile'),
        ));

        return $this->display(__FILE__, '/views/templates/front/bamegamenu.tpl');
    }

    public function choicesSelect($name = "", $type = "", $id = "", $row = "", $selected = array())
    {
        $id_shop = (int) Tools::getValue('mega_id_shop', null);
        if (empty($id_shop)) {
            $id_shop = $this->context->shop->id;
        }
        $items = $this->getMenuItems();
        $depth = 1;
        $spacer = str_repeat('&nbsp;', $this->spacer_size * (int) $depth);
        $html = '';
        $html .= '<select id=\'custom_url' . ($name != '' ? $name : '') . '\' name=\''
        . ($id != '' ? 'sub[' . $row . '][' . $id . '][]' : 'custom_url' . ($name != '' ? $name : ''))
        . '\' ' . ($type == 'multiple' ? 'multiple' : '') . '>';
        $html .= '<optgroup label=\'' . $this->l('CMS') . '\'>';
        $html .= $this->getCMSOptions(0, 1, $this->context->language->id, $items, $selected, $id_shop);
        $html .= '</optgroup>';
        // BEGIN SUPPLIER
        $html .= '<optgroup label=\'' . $this->l('Supplier') . '\'>';
        // Option to show all Suppliers
        $html .= '<option ' . (in_array("ALLSUP0", $selected) == true ? "selected" : "")
        . ' value=\'ALLSUP0\'>' . $this->l('All suppliers') . '</option>';
        $suppliers = $this->getSuppliers(false, $this->context->language->id, $id_shop);
        
        foreach ($suppliers as $supplier) {
            if (!in_array('SUP' . $supplier['id_supplier'], $items)) {
                $html .= '<option ' . (in_array("SUP" . $supplier['id_supplier'], $selected) == true ? "selected" : "")
                . ' value=\'SUP' . $supplier['id_supplier'] . '\'>' . $spacer . $supplier['name'] . '</option>';
            }
        }
        $html .= '</optgroup>';
        // BEGIN Manufacturer
        $html .= '<optgroup label=\'' . $this->l('Manufacturer') . '\'>';
        // Option to show all Manufacturers
        $html .= '<option ' . (in_array("ALLMAN0", $selected) == true ? "selected" : "")
        .' value=\'ALLMAN0\'>'.$this->l('All manufacturers') . '</option>';
        $manufacturers = $this->getManufacturers(false, $this->context->language->id, $id_shop);
        foreach ($manufacturers as $manufacturer) {
            if (!in_array('MAN' . $manufacturer['id_manufacturer'], $items)) {
                $html.= '<option '.(in_array("MAN".$manufacturer['id_manufacturer'], $selected) == true ? "selected":"")
                .' value=\'MAN'.$manufacturer['id_manufacturer'].'\'>' . $spacer . $manufacturer['name'] . '</option>';
            }
        }
        $html .= '</optgroup>';
        // BEGIN Categories
        $shop = new Shop($id_shop);
        $html .= '<optgroup label=\'' . $this->l('Categories') . '\'>';
   
        $cat_tmp=$this->customGetNestedCategories($id_shop, null, (int) $this->context->language->id, true);
        $customGetNestedCategories=$cat_tmp;
        $html .= $this->generateCategoriesOption($customGetNestedCategories, $items, $selected);
        
        $html .= '</optgroup>';
        // BEGIN Shops
        if (Shop::isFeatureActive()) {
            $html .= '<optgroup label=\'' . $this->l('Shops') . '\'>';
            $shops = Shop::getShopsCollection();
            foreach ($shops as $shop) {
                if (!$shop->setUrl() && !$shop->getBaseURL()) {
                    continue;
                }

                if (!in_array('SHOP' . (int) $shop->id, $items)) {
                    $html .= '<option ' . (in_array("SHOP" . (int) $shop->id, $selected) == true ? "selected" : "")
                    . ' value=\'SHOP' . (int) $shop->id . '\'>' . $spacer . $shop->name . '</option>';
                }
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';
        return $html;
    }

    public function choicesSelectCategory($type = "", $id = "", $row = "", $selected = array())
    {
        $id_shop = (int) Tools::getValue('mega_id_shop', null);
        if (empty($id_shop)) {
            $id_shop = $this->context->shop->id;
        }
        $items = $this->getMenuItems();
        $html = '';
        $html .= '<select id=\'select_category\' name=\''
        . ($id != '' ? 'sub[' . $row . '][' . $id . '][]' : 'custom_url')
        . '\' ' . ($type == 'multiple' ? 'multiple' : '') . '>';

        $html .= '<optgroup label=\'' . $this->l('Categories') . '\'>';

        
        $cat_tmp=$this->customGetNestedCategories($id_shop, null, (int) $this->context->language->id, true);
        $customGetNestedCategories=$cat_tmp;
        $html .= $this->generateCategoriesOption($customGetNestedCategories, $items, $selected);
        
        $html .= '</optgroup>';
        $html .= '</select>';
        return $html;
    }

    private function getMenuItems()
    {
        $items = Tools::getValue('items');
        if (is_array($items) && count($items)) {
            return $items;
        } else {
            $shops = Shop::getContextListShopID();
            $conf = null;

            if (count($shops) > 1) {
                foreach ($shops as $key => $shop_id) {
                    if ($shop_id < -1) {// only use validate on prestashop.com
                        return;
                    }
                    $conf .= (string) ($key > 1 ? ',' : '');
                }
            }

            if (Tools::strlen($conf)) {
                return explode(',', $conf);
            } else {
                return array();
            }
        }
    }

    public function getCMSOptions($parent = 0, $depth = 1, $id_lang = false, $items_to_skip = null, $selected = array(), $id_shop = null)
    {
        $html = '';
        $id_lang = $id_lang ? (int) $id_lang : (int) Context::getContext()->language->id;
        $categories = $this->getCMSCategories(false, (int) $parent, (int) $id_lang, $id_shop);
        $pages = $this->getCMSPages((int) $parent, $id_shop, (int) $id_lang);
        $depth = (int) $depth;
        $spacer = str_repeat('&nbsp;', $this->spacer_size * (int) $depth);

        foreach ($categories as $category) {
            if (isset($items_to_skip) && !in_array('CMS_CAT' . $category['id_cms_category'], $items_to_skip)) {
                $id_tmp = $category['id_cms_category'];
                $html .= '<option ' . (in_array("CMS_CAT" . $id_tmp, $selected) == true ? "selected" : "")
                . ' value=\'CMS_CAT' . $category['id_cms_category'] . '\' style=\'font-weight: bold;\'>'
                . $spacer . $category['name'] . '</option>';
            }
            $id_tmp = $category['id_cms_category'];
            $html .= $this->getCMSOptions($id_tmp, $depth + 1, (int) $id_lang, $items_to_skip, $selected, $id_shop);
        }
        foreach ($pages as $page) {
            if (isset($items_to_skip) && !in_array('CMS' . $page['id_cms'], $items_to_skip)) {
                $html .= '<option ' . (in_array("CMS" . $page['id_cms'], $selected) == true ? "selected" : "")
                . ' value=\'CMS' . $page['id_cms'] . '\'>' . $spacer . $page['meta_title'] . '</option>';
            }
        }
        return $html;
    }

    private function getCMSCategories($recursive = false, $parent = 1, $id_lang = false, $id_shop = null)
    {
        $id_lang = $id_lang ? (int) $id_lang : (int) Context::getContext()->language->id;

        if ($recursive === false) {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`,
            bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
                FROM `' . _DB_PREFIX_ . 'cms_category` bcp
                INNER JOIN `' . _DB_PREFIX_ . 'cms_category_lang` cl
                ON (bcp.`id_cms_category` = cl.`id_cms_category`)
                WHERE cl.`id_lang` = ' . (int) $id_lang . '
                AND cl.`id_shop` = ' . (int) $id_shop . '
                AND bcp.`id_parent` = ' . (int) $parent;

            return Db::getInstance()->executeS($sql);
        } else {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`,
            bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
                FROM `' . _DB_PREFIX_ . 'cms_category` bcp
                INNER JOIN `' . _DB_PREFIX_ . 'cms_category_lang` cl
                ON (bcp.`id_cms_category` = cl.`id_cms_category`)
                WHERE cl.`id_lang` = ' . (int) $id_lang . '
                AND cl.`id_shop` = ' . (int) $id_shop . '
                AND bcp.`id_parent` = ' . (int) $parent;

            $results = Db::getInstance()->executeS($sql);
            $categories = array();
            foreach ($results as $result) {
                $sub_categories = $this->getCMSCategories(true, $result['id_cms_category'], (int) $id_lang, $id_shop);
                if ($sub_categories && count($sub_categories) > 0) {
                    $result['sub_categories'] = $sub_categories;
                }
                $categories[] = $result;
            }

            return !empty($categories) ? $categories : false;
        }
    }

    private function getCMSPages($id_cms_category, $id_shop = false, $id_lang = false)
    {
        $id_shop = ($id_shop !== false) ? (int) $id_shop : (int) Context::getContext()->shop->id;
        $id_lang = $id_lang ? (int) $id_lang : (int) Context::getContext()->language->id;

        $sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
            FROM `' . _DB_PREFIX_ . 'cms` c
            INNER JOIN `' . _DB_PREFIX_ . 'cms_shop` cs
            ON (c.`id_cms` = cs.`id_cms`)
            INNER JOIN `' . _DB_PREFIX_ . 'cms_lang` cl
            ON (c.`id_cms` = cl.`id_cms`)
            WHERE c.`id_cms_category` = ' . (int) $id_cms_category . '
            AND cs.`id_shop` = ' . (int) $id_shop . '
            AND cl.`id_shop` = ' . (int) $id_shop . '
            AND cl.`id_lang` = ' . (int) $id_lang . '
            AND c.`active` = 1
            ORDER BY `position`';

        return Db::getInstance()->executeS($sql);
    }

    private function generateCategoriesOption($categories, $items_to_skip = null, $selected = array())
    {
        $html = '';

        foreach ($categories as $category) {
            if (isset($items_to_skip)) {
                $shop = (object) Shop::getShop((int) $category['id_shop']);
                $html.='<option '.(in_array("CAT".(int) $category['id_category'], $selected) == true ? "selected" : "")
                . ' value=\'CAT' . (int) $category['id_category'] . '\'>'
                        . str_repeat('&nbsp;', $this->spacer_size * (int) $category['level_depth'])
                        . $category['name'] . ' (' . $shop->name . ')</option>';
            }

            if (isset($category['children']) && !empty($category['children'])) {
                $html .= $this->generateCategoriesOption($category['children'], $items_to_skip, $selected);
            }
        }
        return $html;
    }

    public function customGetNestedCategories($shop_id, $root_category = null, $id_lang = false, $active = true, $groups = null, $use_shop_restriction = true, $sql_filter = '', $sql_sort = '', $sql_limit = '')
    {
        if (isset($root_category) && !Validate::isInt($root_category)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (isset($groups) && Group::isFeatureActive() && !is_array($groups)) {
            $groups = (array) $groups;
        }
        $group_tmp = (isset($groups) && Group::isFeatureActive() ? implode('', $groups) : '');
        $cache_id = 'Category::getNestedCategories_'
        . md5((int) $shop_id . (int) $root_category . (int) $id_lang . (int) $active . (int) $active. $group_tmp);

        if (!Cache::isStored($cache_id)) {
            $sql='
                SELECT c.*, cl.*
                FROM `' . _DB_PREFIX_ . 'category` c
                INNER JOIN `' . _DB_PREFIX_ . 'category_shop` category_shop ON 
                (category_shop.`id_category` = c.`id_category` AND category_shop.`id_shop` = "' . (int) $shop_id . '")
                LEFT JOIN `'
                . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_shop` = "'
                . (int) $shop_id . '")
                WHERE 1 ' . $sql_filter . ' ' . ($id_lang ? 'AND cl.`id_lang` = ' . (int) $id_lang : '') . '
                ' . ($active ? ' AND (c.`active` = 1 OR c.`is_root_category` = 1)' : '') . '
                ' . (isset($groups) && Group::isFeatureActive() ? ' AND cg.`id_group` IN ('
                . implode(',', array_map("intval", $groups)) . ')' : '') . '
                ' . (!$id_lang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '') . '
                ' . ($sql_sort != '' ? $sql_sort : ' ORDER BY c.`level_depth` ASC') . '
                ' . ($sql_sort == '' && $use_shop_restriction ? ', category_shop.`position` ASC' : '') . '
                ' . ($sql_limit != '' ? $sql_limit : '');
            $result = Db::getInstance()->executeS($sql);
            $categories = array();
            $buff = array();
            $shop = new Shop($shop_id);
            if (!isset($root_category)) {
                $root_category = Category::getRootCategory(null, $shop)->id;
            }

            foreach ($result as $row) {
                $current = &$buff[$row['id_category']];
                $current = $row;

                if ($row['id_category'] == $root_category) {
                    $categories[$row['id_category']] = &$current;
                } else {
                    $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
                }
            }

            Cache::store($cache_id, $categories);
        }

        return Cache::retrieve($cache_id);
    }
    // true if customer can view this menu
    public function isAccessMenu($id_menu)
    {
        $group_menu=$this->getGroupforMenu($id_menu);
        // lấy group của customer hiện tại
        if (empty($this->user_groups)) {
            $this->user_groups = ($this->context->customer->isLogged() ?
                $this->context->customer->getGroups() : array(Configuration::get('PS_UNIDENTIFIED_GROUP')));
        }
        // kiểm tra tất cả group của User
        foreach ($this->user_groups as $group_id) {
            if (in_array($group_id, $group_menu)) {
                return true;
            }
        }
        return false;
    }
    // trả về 1 mảng group id gán cho menu
    public function getGroupforMenu($id_menu)
    {
        $group_selected=array();
        if (!empty($id_menu)) {
            // truy vấn các group gán cho menu này
            $sql = "SELECT id_group FROM " . _DB_PREFIX_ . "megamenu_group WHERE id_menu=" . (int) $id_menu;
            $results = Db::getInstance()->executeS($sql);
            if (!empty($results)) {
                foreach ($results as $v) {
                    $group_selected[]=$v['id_group'];
                }
                return $group_selected;
            }
            return array();
        }
        return array();
    }
    // return html for show group
    public function renderGroupsField($id_menu = null)
    {
        $id_menu=(int) $id_menu;
        $group_selected=$this->getGroupforMenu($id_menu);
        // lấy toàn bộ groups
        $groups=Group::getGroups(Context::getContext()->language->id);
        //var_dump($groups);die;
        // kiểm tra xem group nào đã selected
        $fields_value=array();
        foreach ($groups as $v) {
            if (in_array($v['id_group'], $group_selected) || $id_menu==0) {
                $fields_value['groupBox_'.$v['id_group']]=true;
            } else {
                $fields_value['groupBox_'.$v['id_group']]=false;
            }
        }
        
        $this->context->smarty->assign('groups', $groups);
        $this->context->smarty->assign('fields_value', $fields_value);
        $html=$this->display(__FILE__, '/views/templates/admin/groupbox.tpl');
        return $html;
    }
    public function getBaseURL()
    {
        //echo "<pre>";var_dump(rtrim(__PS_BASE_URI__,"/"));die;
        return rtrim(__PS_BASE_URI__, "/");
    }
    public function exePHP($content)
    {
        $license_header ='<?php
        /**
         * 2007-2015 PrestaShop
         *
         * NOTICE OF LICENSE
         *
         * This source file is subject to the Open Software License (OSL 3.0)
         * that is bundled with this package in the file LICENSE.txt.
         * It is also available through the world-wide-web at this URL:
         * http://opensource.org/licenses/osl-3.0.php
         * If you did not receive a copy of the license and are unable to
         * obtain it through the world-wide-web, please send an email
         * to license@buy-addons.com so we can send you a copy immediately.
         *
         * DISCLAIMER
         *
         * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
         * versions in the future. If you wish to customize PrestaShop for your
         * needs please refer to http://www.prestashop.com for more information.
         *
         *  @author    PrestaShop SA <contact@prestashop.com>
         *  @copyright 2007-2015 PrestaShop SA
         *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *  International Registered Trademark & Property of PrestaShop SA
         * @since 1.6
         */
';
        $file= dirname(__FILE__).'/eval.php';
        file_put_contents($file, $license_header.$content.' ?>');
         ob_start();
        require($file);
        $content = ob_get_contents();
        ob_end_clean();
        @file_put_contents($file, $license_header);
        return $content;
    }
     /**
     * Return suppliers
     *
     * @return array Suppliers
     */
    public function getSuppliers($get_nb_products = false, $id_lang = 0, $id_shop = null)
    {
        if (!$id_lang) {
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
        }
        if (empty($id_shop)) {
            $id_shop = (int) $this->context->shop->id;
        }
        
        $active = true;
        $p = false;
        $n = false;
        
        $query = new DbQuery();
        $query->select('s.*, sl.`description`');
        $query->from('supplier', 's');
        $query->leftJoin('supplier_lang', 'sl', 's.`id_supplier` = sl.`id_supplier` AND sl.`id_lang` = '.(int)$id_lang);
        $query->join('INNER JOIN '._DB_PREFIX_.'supplier_shop supplier_shop
                    ON (supplier_shop.id_supplier = s.id_supplier AND supplier_shop.id_shop = '.(int) $id_shop.')');
        if ($active) {
            $query->where('s.`active` = 1');
        }
        $query->orderBy(' s.`name` ASC');
        $query->limit($n, ($p - 1) * $n);
        $query->groupBy('s.id_supplier');

        $suppliers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($suppliers === false) {
            return false;
        }
        if ($get_nb_products == true) {
            return ;
        }
        $nb_suppliers = count($suppliers);
        $rewrite_settings = (int)Configuration::get('PS_REWRITING_SETTINGS');
        for ($i = 0; $i < $nb_suppliers; $i++) {
            $suppliers[$i]['link_rewrite'] = ($rewrite_settings ? Tools::link_rewrite($suppliers[$i]['name']) : 0);
        }
        return $suppliers;
    }
        /**
     * Return manufacturers
     *
     * @param bool $get_nb_products [optional] return products numbers for each
     * @param int $id_lang
     * @param bool $active
     * @param int $p
     * @param int $n
     * @param bool $all_group
     * @return array Manufacturers
     */
    public function getManufacturers($get_nb_products = false, $id_lang = 0, $id_shop = null)
    {
        if (!$id_lang) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }
        if (empty($id_shop)) {
            $id_shop = (int) $this->context->shop->id;
        }
        
        $active = true;
        $p = false;
        $n = false;
        $group_by = false;
        $sql = 'SELECT m.*, ml.`description`, ml.`short_description`
        FROM `'._DB_PREFIX_.'manufacturer` m
        '.Shop::addSqlAssociation('manufacturer', 'm').'
        INNER JOIN `'._DB_PREFIX_.'manufacturer_lang` ml 
        ON (m.`id_manufacturer` = ml.`id_manufacturer` AND ml.`id_lang` = '.(int)$id_lang.')
        INNER JOIN `'._DB_PREFIX_.'manufacturer_shop` ms 
        ON (m.`id_manufacturer` = ms.`id_manufacturer` AND ms.`id_shop` = '.(int)$id_shop.')
        '.($active ? 'WHERE m.`active` = 1' : '')
        .($group_by ? ' GROUP BY m.`id_manufacturer`' : '').'
        ORDER BY m.`name` ASC
        '.($p ? ' LIMIT '.(((int)$p - 1) * (int)$n).','.(int)$n : '');
        $manu = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ($manu === false) {
            return false;
        }
        if ($get_nb_products == true) {
            return ;
        }
        $total_manufacturers = count($manu);
        $rewrite_settings = (int)Configuration::get('PS_REWRITING_SETTINGS');
        for ($i = 0; $i < $total_manufacturers; $i++) {
            $manu[$i]['link_rewrite'] = $rewrite_settings ? Tools::link_rewrite($manu[$i]['name']) : 0;
        }
        return $manu;
    }
    
    public function cookiekeymodule()
    {
        $keygooglecookie = sha1(_COOKIE_KEY_ . 'bamegamenu');
        $md5file = md5($keygooglecookie);
        return $md5file;
    }
}
