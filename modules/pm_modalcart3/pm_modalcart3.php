<?php
/**
 * PM_ModalCart3 Merchandizing Feature
*
* @category merchandizing
* @author Presta-Module.com <support@presta-module.com>
* @copyright Presta-Module 2016
* @version 3.0.10
*
*       _______  ____    ____
*      |_   __ \|_   \  /   _|
*        | |__) | |   \/   |
*        |  ___/  | |\  /| |
*       _| |_    _| |_\/_| |_
*      |_____|  |_____||_____|
*
**************************************
**            ModalCart              *
**   http://www.presta-module.com    *
**             V 3.0.10               *
**************************************
*
* Languages: EN, FR
* PS version: 1.4, 1.5, 1.6
*
*/

if (!defined('_PS_VERSION_')) {
    exit ;
}
require_once(_PS_ROOT_DIR_ . '/modules/pm_modalcart3/modalCart3CoreClass.php');
require_once(_PS_ROOT_DIR_ . '/modules/pm_modalcart3/class/mc3Tpl.php');
require_once(_PS_ROOT_DIR_ . '/modules/pm_modalcart3/class/mc3Tpl_CartSummary.php');
require_once(_PS_ROOT_DIR_ . '/modules/pm_modalcart3/class/mc3Tpl_LastProductAdded.php');
require_once(_PS_ROOT_DIR_ . '/modules/pm_modalcart3/class/mc3Tpl_jGrowl.php');
class pm_modalcart3 extends modalCart3CoreClass
{
    protected $_require_maintenance = true;
    protected $_require_ccc = true;
    public static $_module_prefix = 'MC3';
    protected $_file_to_check = array('views/css', 'tpl');
    protected $_css_js_to_load = array(
                                    'jqueryfancybox',
                                    'jquery',
                                    'jquerytiptip',
                                    'jquerytools',
                                    'selectmenu',
                                    'admincore',
                                    'adminmodule',
                                    'jgrowl',
                                    'colorpicker',
                                    'codemirrorcore',
                                    'codemirrormixed',
                                    'codemirrorcss',
                                    'tiny_mce');
    protected $font_style = array();
    protected $itemTranslated = array();
    protected $templateType = array();
    private $_url_cross_selling;
    public static $_valid_hooks = array('header');
    protected $_copyright_link = array(
        'link'    => '',
        'img'    => '//www.presta-module.com/img/logo-module.JPG'
    );
    protected $_registerOnHooks = array('header');
    public function __construct()
    {
        $this->name = 'pm_modalcart3';
        $this->author = 'Presta-Module';
        $this->tab = 'front_office_features';
        $this->module_key = 'dd1715e0a9f7505e471b77fd17596f33';
        $this->version = '3.0.10';
        $this->need_instance = 0;
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            self::$_valid_hooks[] = 'displayHeader';
        }
        $this->controllers = array('ajax_front');
        parent::__construct();
        if ($this->_onBackOffice()) {
            $this->displayName = $this->l('Modal Cart 3');
            $this->description = $this->l('Display a smart confirmation window when your customers add a product to their cart');
            $url_cross_selling_tab = array();
            $url_cross_selling_tab['fr'] = 'http://addons.prestashop.com/fr/processus-de-commande/2412-cross-selling-on-cart-augmentez-votre-panier-moyen.html';
            $url_cross_selling_tab['en'] = 'http://addons.prestashop.com/en/checkout/2412-cross-selling-on-cart-increase-your-cart-average.html';
            $this->_url_cross_selling = $url_cross_selling_tab['en'];
            if ($this->_iso_lang == 'fr') {
                $this->_url_cross_selling = $url_cross_selling_tab['fr'];
            }
            $forum_url_tab = array();
            $forum_url_tab['fr'] = 'http://www.prestashop.com/forums/topic/254265-module-pm-modal-cart-3-systeme-de-notifications-dajout-au-panier/';
            $forum_url_tab['en'] = 'http://www.prestashop.com/forums/topic/254267-module-pm-modal-cart-3-add-to-cart-notification-system/';
            $forum_url = $forum_url_tab['en'];
            if ($this->_iso_lang == 'fr') {
                $forum_url = $forum_url_tab['fr'];
            }
            $this->_support_link = array(
                array(
                    'link'        => $forum_url,
                    'target'    => '_blank',
                    'label'        => $this->l('Forum topic')
                ),
                array(
                    'link'        => 'http://addons.prestashop.com/contact-community.php?id_product=2438',
                    'target'    => '_blank',
                    'label'        => $this->l('Support contact')
                ),
            );
            $this->_getting_started = array(
                array(
                    'href' => $this->_path . 'views/img/mockups/'.($this->_iso_lang == 'fr' ? 'fr' : 'en').'/step_1.png',
                    'title' => $this->l('Step 1 - Type of window'),
                ),
                array(
                    'href' => $this->_path . 'views/img/mockups/'.($this->_iso_lang == 'fr' ? 'fr' : 'en').'/step_2.png',
                    'title' => $this->l('Step 2 - Configuration'),
                ),
                array(
                    'href' => $this->_path . 'views/img/mockups/'.($this->_iso_lang == 'fr' ? 'fr' : 'en').'/step_3.png',
                    'title' => $this->l('Step 3 - Order and configure fields visibility'),
                ),
                array(
                    'href' => $this->_path . 'views/img/mockups/'.($this->_iso_lang == 'fr' ? 'fr' : 'en').'/step_4.png',
                    'title' => $this->l('Step 4 - Configure field by field (content, background...)'),
                ),
                array(
                    'href' => $this->_path . 'views/img/mockups/'.($this->_iso_lang == 'fr' ? 'fr' : 'en').'/step_5.png',
                    'title' => $this->l('Step 5 - Set the appearence settings (width, borders...)'),
                ),
                array(
                    'href' => $this->_path . 'views/img/mockups/'.($this->_iso_lang == 'fr' ? 'fr' : 'en').'/step_6.png',
                    'title' => $this->l('Step 6 - Enjoy !'),
                ),
            );
            $this->font_style = array('bold' => $this->l('Bold'), 'italic' => $this->l('Italic'), 'underline' => $this->l('Underline') );
            $this->templateType = array(
                0 => $this->l('-- Choose --'),
                'mc3Tpl_CartSummary' => $this->l('Cart Summary'),
                'mc3Tpl_LastProductAdded' => $this->l('Last Product Added'),
                'mc3Tpl_jGrowl' => $this->l('Notification')
            );
        }
    }
    private function createMC3Hooks()
    {
        return Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'hook` (`name`, `title`, `description`, `position`) VALUES (\'MCBelow\', \'Modal Cart 3 hook\', \'Content into modal\', 1)');
    }
    private function _initGlobalVars()
    {
        $this->itemTranslated = array(
            'free_content_1' => $this->l('Free content #1'),
            'free_content_2' => $this->l('Free content #2'),
            'free_shipping' => $this->l('Informations about the amount needed for free shipping'),
            'title' => $this->l('Window title'),
            'product_image' => $this->l('Image'),
            'product_name' => $this->l('Product name'),
            'product_price' => $this->l('Product price'),
            'product_tax' => $this->l('Tax'),
            'product_description' => $this->l('Product description'),
            'product_availability' => $this->l('Availability'),
            'product_quantity' => $this->l('Quantity'),
            'product_total' => $this->l('Total'),
            'order_now' => $this->l('Order Now'),
            'keep_shopping' => $this->l('Keep Shopping'),
            'hook_cross_selling_on_cart' => $this->l('Cross Selling On Cart'),
            'subtotal_label' => $this->l('Subtotal :'),
            'subtotal_value' => $this->l('Subtotal amount'),
            'total_label' => $this->l('Total :'),
            'total_value' => $this->l('Total amount'),
            'total_tax_label' => $this->l('Taxes :'),
            'total_tax_value' => $this->l('Taxes amount'),
            'discounts_label' => $this->l('Total discounts :'),
            'discounts_value' => $this->l('Discounts amount'),
            'shipping_value' => $this->l('Shipping amount'),
            'shipping_label' => $this->l('Shipping :')
        );
    }
    private static $_isCrossSellingInstalledCache;
    public static function isCrossSellingInstalled()
    {
        if (!isset(self::$_isCrossSellingInstalledCache)) {
            self::$_isCrossSellingInstalledCache = (file_exists(dirname(__FILE__) . '/../pm_crosssellingoncart/pm_crosssellingoncart.php') && self::moduleIsInstalled('pm_crosssellingoncart') && Configuration::get('PM_CSOC_LAST_VERSION') >= 1.5);
        }
        return self::$_isCrossSellingInstalledCache;
    }
    public function install()
    {
        if (parent::install() == false || !$this->createMC3Hooks()) {
            return false;
        }
        $this->checkIfModuleIsUpdate(true, false, true);
        return true;
    }
    public function checkIfModuleIsUpdate($updateDb = false, $displayConfirm = true, $firstInstall = false)
    {
        $isUpdate = true;
        if (!$updateDb && $this->version != Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false)) {
            return false;
        }
        if ($updateDb) {
            unset($_GET['makeUpdate']);
            if (!$firstInstall) {
                Configuration::updateValue('PM_' . self::$_module_prefix . '_LAST_VERSION', $this->version);
            }
            if (!$firstInstall) {
                $this->_generateDynamicStyles();
            }
            if ($isUpdate && $displayConfirm) {
                $this->_html .= $this->displayConfirmation($this->l('Module updated successfully'));
            } else {
                $this->_html .= $this->displayError($this->l('Module update failed'));
            }
        }
        return $isUpdate;
    }
    protected function _postProcess()
    {
        $this->_initGlobalVars();
        parent::_postProcess();
        $saveItemsOrder = Tools::getValue('saveItemsOrder');
        $templateKey = Tools::getValue('templateKey');
        $itemName = Tools::getValue('itemName');
        if (Tools::getIsset('saveItemsOrder') && !empty($saveItemsOrder) && Tools::getIsset('templateKey') && !empty($templateKey)) {
            $this->_cleanOutput();
            $tplObj = $this->getCurrentTemplate();
            $tplKey = trim($templateKey);
            if ($tplObj !== false && $tplObj->templateKey == $tplKey) {
                $modalParams = $tplObj->getTplParams();
                $modalParams['fields_order'] = explode('-', rtrim($saveItemsOrder, '-'));
                $tplObj->setTplParams($modalParams);
                $tplObj->generateTpl();
            }
            die();
        } elseif (Tools::getIsset('saveDisplaySettings') && Tools::getIsset('itemName') && !empty($itemName) && Tools::getIsset('displayValue')) {
            $this->_cleanOutput();
            $tplObj = $this->getCurrentTemplate();
            if ($tplObj !== false) {
                $modalParams = $tplObj->getTplParams();
                $itemName = trim(Tools::getValue('itemName'));
                if (isset($modalParams[$itemName])) {
                    $modalParams[$itemName] = (int)Tools::getValue('displayValue');
                    $tplObj->setTplParams($modalParams);
                    $tplObj->generateTpl();
                }
            }
            die();
        } elseif (Tools::getIsset('submit_global_configuration')) {
            $this->postProcessSaveTemplate();
        } elseif (Tools::getIsset('submit_item_form')) {
            $this->postProcessSaveItem();
        } elseif (Tools::getValue('linkCSOCwithMC3')) {
            if (self::isCrossSellingInstalled()) {
                include_once(dirname(__FILE__) . '/../pm_crosssellingoncart/pm_crosssellingoncart.php');
                $objCrossSellingOnCart = new PM_CrossSellingOnCart('PM_MC_CSOC');
                $objCrossSellingOnCart->registerHook('MCBelow');
            }
        } elseif (Tools::getValue('unlinkCSOCwithMC3')) {
            if (self::isCrossSellingInstalled()) {
                include_once(dirname(__FILE__) . '/../pm_crosssellingoncart/pm_crosssellingoncart.php');
                $objCrossSellingOnCart = new PM_CrossSellingOnCart('PM_MC_CSOC');
                if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                    $objCrossSellingOnCart->unregisterHook(Hook::getIdByName('MCBelow'));
                } else {
                    $objCrossSellingOnCart->unregisterHook(Hook::get('MCBelow'));
                }
            }
        } elseif (Tools::getIsset('submitOptions_PM_MC_CSOC') || Tools::getIsset('submitProducts_PM_MC_CSOC')) {
            if (self::isCrossSellingInstalled()) {
                include_once(dirname(__FILE__) . '/../pm_crosssellingoncart/pm_crosssellingoncart.php');
                $objCrossSellingOnCart = new PM_CrossSellingOnCart('PM_MC_CSOC');
                $objCrossSellingOnCart->saveConfig();
            }
            if (!sizeof($this->_errors)) {
                $this->_html .= '<script type="text/javascript">$(document).ready(function() { show_info("' . $this->l('Saved') . '");});</script>';
            } else {
                $this->_html .= '<script type="text/javascript">$(document).ready(function() { show_info("' . implode('<br />', $this->_errors) . '");});</script>';
            }
        }
    }
    public function getContent()
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $this->_html .= '<div id="pm_backoffice_wrapper" class="pm_bo_ps_'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'">';
        }
        $this->_displayTitle($this->displayName);
        if ($this->_checkPermissions()) {
            if (Tools::getValue('makeUpdate')) {
                $this->checkIfModuleIsUpdate(true);
            }
            if (! $this->checkIfModuleIsUpdate(false)) {
                $this->_loadCssJsLibraries();
                $this->_html .= '
					<div class="warning warn clear"><p>' . $this->l('We have detected that you installed a new version of the module on your shop') . '</p>
						<p style="text-align: center"><a href="' . $this->_base_config_url . '&makeUpdate=1" class="button">' . $this->l('Please click here in order to finish the installation process') . '</a></p>
					</div>';
            } elseif (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                $this->_loadCssJsLibraries();
                $this->_html .= '
					<div class="error clear">
						<p style="text-align: center">
							' . $this->l('We have detected that you the "magic_quotes_gpc" option is enabled in your web server configuration.') . '<br />
							' . $this->l('This option is not recommended and must be disabled in order to continue.') . '<br />
							' . $this->l('If you don\'t know how to, please ask your hoster to disable "magic_quotes_gpc" for your shop.') . '<br />
						</p>
					</div>';
            } else {
                if (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && Shop::getContext() != Shop::CONTEXT_SHOP) {
                    $this->_loadCssJsLibraries();
                    $this->_html .= '<div class="module_error alert error">' . $this->l('You must select a specific shop in order to continue, you can\'t create/edit settings from the "all shop" or "group shop" context.'). '</div>';
                } else {
                    $this->_preProcess();
                    $this->_postProcess();
                    $this->_loadCssJsLibraries();
                    $this->_showRating(true);
                    parent::getContent();
                    $tabsPanelOptions = array(
                            'id_panel' => 'MC3_Panel',
                            'tabs' => array(
                                array('url' => $this->_base_config_url . '&pm_load_function=displayGlobalConfiguration',
                                'label' => $this->l('Configuration')),
                                array('url' => $this->_base_config_url . '&pm_load_function=displayCrossSellingConfiguration',
                                'label' => $this->l('Cross Selling')),
                                array('url' => $this->_base_config_url . '&pm_load_function=_displayAdvancedStyles',
                                'label' => $this->l('Advanced Styles'),
                                'id' => 'displayAdvancedStylesPanel'),
                            ));
                    $this->_displayTabsPanel($tabsPanelOptions);
                    $this->_pmClear();
                    $this->_displaySupport();
                }
            }
        }
        $this->_pmClear();
        $this->_html .= '</div>';
        return $this->_html;
    }
    public static function moduleIsInstalled($moduleName)
    {
        Db::getInstance()->ExecuteS('SELECT `id_module` FROM `'._DB_PREFIX_.'module` WHERE `name` = \''.pSQL($moduleName).'\'');
        return (bool)Db::getInstance()->NumRows();
    }
    public function getModuleConfiguration()
    {
        $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
        if (!empty($conf)) {
            return json_decode($conf, true);
        }
        return array();
    }
    public function setModuleConfiguration($newConf)
    {
        Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', json_encode($newConf));
    }
    protected function displayGlobalConfiguration()
    {
        $config = $this->getModuleConfiguration();
        $this->_startForm(array('id' => 'global_configuration', 'iframetarget' => false, 'target' => '_self'));
        if (!isset($config['currentTemplateClass'])) {
            $this->_html .= '<div class="pm_info noSelectedTemplateInfo"><strong>
				'.$this->l('Welcome to Modal Cart 3 configuration').'<br />
				'.$this->l('You now have to select the modal type you want to display for your customers from this list:').'<br /><br />
				<ul>
					<li>'.$this->l('Cart Summary').' : '.$this->l('display the complete cart summary').'</li>
					<li>'.$this->l('Last Product Added').' : '.$this->l('display informations about the latest product added to customer\'s cart').'</li>
					<li>'.$this->l('Notification').' : '.$this->l('display a notice on screen corner, that the product has been add').'</li>
				</ul>
			</strong></div>';
        }
        $this->_displaySelect(array(
                                'obj' => $config,
                                'isarray' => true,
                                'key' => 'currentTemplateClass',
                                'label' => $this->l('Type of window'),
                                'options' => $this->templateType,
                                'defaultvalue' => false,
                                'size' => '200px',
                                'tips' => $this->l('Select your add to cart confirmation window. There is 3 modes available. Each of them can manage several options and look different.'),
                                'onchange' => 'showRelatedConfiguration($jqPm(this))'));
        $this->_html .= '<hr><div id="displayConfiguration" style="margin-left:10px;"></div>';
        if (isset($config['currentTemplateClass']) && $config['currentTemplateClass']) {
            $this->_html .= '<script type="text/javascript">
								$(document).ready(
									$jqPm("#displayConfiguration").load("'.$this->_base_config_url.'&pm_load_function=displayTemplateConfiguration&template_class='.$config['currentTemplateClass'].'",
										function() { $jqPm(this).show("fast"); })
								);
							</script>';
        }
        $this->_endForm(array('iframetarget' => false, 'includehtmlatend' => true));
        if ($this->_require_ccc) {
            $this->_isCccActive();
            $this->_html .= '<hr class="pm_hr" />';
        }
    }
    protected function displayTemplateConfiguration()
    {
        $template_class = trim(Tools::getValue('template_class'));
        if (!class_exists($template_class)) {
            die('Unknown template class : '.$template_class);
        }
        $tplObj = new $template_class();
        $this->_html .= '<script type="text/javascript">
								$(document).ready(
									$jqPm("#pm_mc3_wizard").load("'.$this->_base_config_url.'&pm_load_function=displayTemplateWizard_'.get_class($tplObj).'",
										function() { $jqPm(this).show("fast"); })
								);
						</script>';
        $this->_html .= '<div id="pm_mc3_wizard" style="margin-bottom:25px;">';
        $this->_html .= '</div>';
        $this->_displaySubmit($this->l('Save'), 'submit_global_configuration');
        $this->_includeHTMLAtEnd();
    }
    protected function display_mc3Tpl_CartSummary_Wizard()
    {
        $this->_initGlobalVars();
        $tplObj = new mc3Tpl_CartSummary();
        $params = $tplObj->getTplParams();
        $javascript_init = '';
        foreach ($tplObj->getProductLineFields() as $item_name) {
            $settings_cs = $tplObj->fields[$item_name];
            if (isset($settings_cs['option_active']) && $settings_cs['option_active'] == true) {
                $javascript_init .= '$jqPm("tr.pm_mc3_'.$tplObj->templateKey.'_product_line li.'.$item_name.'").removeClass("pm_mc3_hidden_block");'."\n";
            } else {
                $javascript_init .= '$jqPm("tr.pm_mc3_'.$tplObj->templateKey.'_product_line li.'.$item_name.'").addClass("pm_mc3_hidden_block");'."\n";
            }
        }
        $config_url = $this->_base_config_url . '&pm_load_function=getItemForm&pm_js_callback=closeDialogIframe&template_class='.get_class($tplObj);
        $html = $tplObj->getBackOfficeTemplate();
        $html = preg_replace('/{config_url}/', $config_url, $html);
        preg_match_all('/(?<=\{translate_)([a-zA-Z0-9_-]+)/', $html, $var_to_translate);
        foreach ($this->itemTranslated as $item => $translation) {
            $html = str_replace('{translate_'.$item.'}', $translation, $html);
        }
        $this->_html .= '<div id="pm_mc3_global_content" style="margin-bottom:25px;">'.$html.'</div>';
        $this->_html .= '<input type="hidden" name="fields_order" id="fields_order" value="'.implode('-', $params['fields_order']).'"/>';
        $this->_pmClear();
        $this->_html .= '
		<script type="text/javascript">
			$jqPm(document).ready(function() {
				'.$javascript_init.'
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_free_content_1").'.(isset($params['display_free_content_1']) ? ($params['display_free_content_1'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_free_content_2").'.(isset($params['display_free_content_2']) ? ($params['display_free_content_2'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_free_shipping").'.(isset($params['display_free_shipping']) ? ($params['display_free_shipping'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_title").'.(isset($params['display_title']) ? ($params['display_title'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_hook_cross_selling_on_cart").'.(isset($params['display_hook_cross_selling_on_cart']) ? ($params['display_hook_cross_selling_on_cart'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("tr#pm_mc3_'.$tplObj->templateKey.'_subtotal_row").'.(isset($params['display_subtotal']) ? ($params['display_subtotal'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("tr#pm_mc3_'.$tplObj->templateKey.'_total_tax_row").'.(isset($params['display_taxes']) ? ($params['display_taxes'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("tr#pm_mc3_'.$tplObj->templateKey.'_discounts_row").'.(isset($params['display_discounts']) ? ($params['display_discounts'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("tr#pm_mc3_'.$tplObj->templateKey.'_shipping_row, .shipping_info").'.(isset($params['display_shipping']) ? ($params['display_shipping'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#background_overlay_options").'.(isset($params['background_overlay']) ? ($params['background_overlay'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_global_sort").sortable({
					revert: true,
					update: function() { pm_saveItemsOrder("cs") },
					handle: ".ui-icon-arrow-4"
				});
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_product_line_sort").sortable({
					revert: true,
					update: function() { pm_saveItemsOrder("cs") },
					handle: ".ui-icon-arrow-4",
					start: function( event, ui ) {
						$("td[colspan=5]", $(ui.item).parent().parent()).attr("colspan", 6);
						$("#pm_mc3_'.$tplObj->templateKey.'_product_line_sort .ui-sortable-placeholder").css("width", $(ui.item).width()).css("height", $(ui.item).height());
					},
					stop: function( event, ui ) {
						$("td[colspan=6]", $(ui.item).parent().parent()).attr("colspan", 5);
					}
				});
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_footer_sort").sortable({
					revert: true,
					update: function() { pm_saveItemsOrder("cs") },
					handle: ".ui-icon-arrow-4"
				});
				$jqPm(document).ready(function() {
					pm_saveItemsOrder("cs");
				});
			});
		</script>
		';
    }
    protected function display_mc3Tpl_LastProductAdded_Wizard()
    {
        $this->_initGlobalVars();
        $tplObj = new mc3Tpl_LastProductAdded();
        $params = $tplObj->getTplParams();
        $javascript_init = '';
        foreach ($tplObj->getProductLineFields() as $item_name) {
            $settings_lpa = $tplObj->fields[$item_name];
            if (isset($settings_lpa['option_active']) && $settings_lpa['option_active'] == true) {
                $javascript_init .= '$jqPm("tr.pm_mc3_'.$tplObj->templateKey.'_product_line li.'.$item_name.'").removeClass("pm_mc3_hidden_block");'."\n";
            } else {
                $javascript_init .= '$jqPm("tr.pm_mc3_'.$tplObj->templateKey.'_product_line li.'.$item_name.'").addClass("pm_mc3_hidden_block");'."\n";
            }
        }
        $config_url = $this->_base_config_url . '&pm_load_function=getItemForm&pm_js_callback=closeDialogIframe&template_class='.get_class($tplObj);
        $html = $tplObj->getBackOfficeTemplate();
        $html = preg_replace('/{config_url}/', $config_url, $html);
        preg_match_all('/(?<=\{translate_)([a-zA-Z0-9_-]+)/', $html, $var_to_translate);
        foreach ($this->itemTranslated as $item => $translation) {
            $html = str_replace('{translate_'.$item.'}', $translation, $html);
        }
        $this->_html .= '<div id="pm_mc3_global_content" style="margin-bottom:25px;">'.$html.'</div>';
        $this->_html .= '<input type="hidden" name="fields_order" id="fields_order" value="'.implode('-', $params['fields_order']).'"/>';
        $this->_pmClear();
        $this->_html .= '
		<script type="text/javascript">
			$jqPm(document).ready(function() {
				'.$javascript_init.'
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_free_content_1").'.(isset($params['display_free_content_1']) ? ($params['display_free_content_1'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_free_content_2").'.(isset($params['display_free_content_2']) ? ($params['display_free_content_2'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_free_shipping").'.(isset($params['display_free_shipping']) ? ($params['display_free_shipping'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_title").'.(isset($params['display_title']) ? ($params['display_title'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_hook_cross_selling_on_cart").'.(isset($params['display_hook_cross_selling_on_cart']) ? ($params['display_hook_cross_selling_on_cart'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#background_overlay_options").'.(isset($params['background_overlay']) ? ($params['background_overlay'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_global_content .ui-sortable").sortable({
					revert: true,
					update: function() { pm_saveItemsOrder("lpa") },
					handle: ".ui-icon-arrow-4",
					items: "li:not(.product_description)"
				});
				$jqPm(document).ready(function() {
					pm_saveItemsOrder("lpa");
				});
			});
		</script>
		';
    }
    protected function display_mc3Tpl_jGrowl_Wizard()
    {
        $this->_initGlobalVars();
        $tplObj = new mc3Tpl_jGrowl();
        $params = $tplObj->getTplParams();
        $javascript_init = '';
        foreach ($tplObj->getProductLineFields() as $item_name) {
            $settings_cs = $tplObj->fields[$item_name];
            if (isset($settings_cs['option_active']) && $settings_cs['option_active'] == true) {
                $javascript_init .= '$jqPm("tr.pm_mc3_'.$tplObj->templateKey.'_product_line li.'.$item_name.'").removeClass("pm_mc3_hidden_block");'."\n";
            } else {
                $javascript_init .= '$jqPm("tr.pm_mc3_'.$tplObj->templateKey.'_product_line li.'.$item_name.'").addClass("pm_mc3_hidden_block");'."\n";
            }
        }
        $config_url = $this->_base_config_url . '&pm_load_function=getItemForm&pm_js_callback=closeDialogIframe&template_class='.get_class($tplObj);
        $html = $tplObj->getBackOfficeTemplate();
        $html = preg_replace('/{config_url}/', $config_url, $html);
        preg_match_all('/(?<=\{translate_)([a-zA-Z0-9_-]+)/', $html, $var_to_translate);
        foreach ($this->itemTranslated as $item => $translation) {
            $html = str_replace('{translate_'.$item.'}', $translation, $html);
        }
        $this->_html .= '<div id="pm_mc3_global_content" style="margin-bottom:25px;">'.$html.'</div>';
        $this->_html .= '<input type="hidden" name="fields_order" id="fields_order" value="'.implode('-', $params['fields_order']).'"/>';
        $this->_pmClear();
        $this->_html .= '
		<script type="text/javascript">
			$jqPm(document).ready(function() {
				'.$javascript_init.'
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_free_content_1").'.(isset($params['display_free_content_1']) ? ($params['display_free_content_1'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_free_content_2").'.(isset($params['display_free_content_2']) ? ($params['display_free_content_2'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#background_overlay_options").'.(isset($params['background_overlay']) ? ($params['background_overlay'] ? 'fadeIn' :'fadeOut') : 'fadeIn').'("fast", function() { removeStyle(this); });
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_global_sort").sortable({
					revert: true,
					update: function() { pm_saveItemsOrder("jgrowl") },
					handle: ".ui-icon-arrow-4"
				});
				$jqPm("#pm_mc3_'.$tplObj->templateKey.'_product_line_sort").sortable({
					revert: true,
					update: function() { pm_saveItemsOrder("jgrowl") },
					handle: ".ui-icon-arrow-4"
				});
				$jqPm(document).ready(function() {
					pm_saveItemsOrder("jgrowl");
				});
			});
		</script>
		';
    }
    protected function displayModalGlobalConfiguration($tplObj, $params)
    {
        $this->_initGlobalVars();
        $this->_startFieldset($this->l('Configuration'), $this->_path . '/views/img/configuration-icon.png', false);
        if ($tplObj->templateKey == 'jgrowl') {
            $this->_displaySelect(array(
                    'obj' => $params,
                    'isarray' => true,
                    'key' => 'jgrowl_position',
                    'label' => $this->l('Position of notification center'),
                    'options' => array(0 => $this->l('-- Choose --'), 'top-left' => $this->l('Top Left'), 'top-right' => $this->l('Top Right'), 'bottom-left' => $this->l('Bottom Left'), 'bottom-right' => $this->l('Bottom Right')),
                    'defaultvalue' => false,
                    'tips' => $this->l('Choose the position of your notification messages: they can be on any screen corner.'),
                    'size' => '150px'));
            $this->_displayInputActive(array(
                    'obj' => $params,
                    'isarray' => true,
                    'key_active' => 'jgrowl_sticky',
                    'key_db' => 'jgrowl_sticky',
                    'defaultvalue' => true,
                    'onclick' => 'showRelatedItems($jqPm(this))',
                    'tips' => $this->l('Choose the way you want to manage the display of the notification center. If you activate the persistent mode, notifications will be shown until your customers choose to close them by a click action. In the other case, notifications are automatically closed after a delay.'),
                    'label' => $this->l('Persistent mode')));
            $this->_html .= '<div id="pm_mc3_jgrowl_lifetime" style="display:'.(isset($params['jgrowl_sticky']) ? ($params['jgrowl_sticky'] ? 'none' : 'block') : 'none').'">';
            $this->_displayInputText(array(
                    'obj' => $params,
                    'isarray' => true,
                    'key' => 'jgrowl_lifetime',
                    'label' => $this->l('Close after (ms)'),
                    'size' => '30px',
                    'defaultvalue' => '3000',
                    'tips' => $this->l('Set the duration time of the notification messages. They will closed automatically after a delay (in ms).'),
                    'required' => true));
            $this->_html .= '</div>';
            $this->_displayInputActive(array(
                    'obj' => $params,
                    'isarray' => true,
                    'key_active' => 'jgrowl_display_order_btn',
                    'key_db' => 'jgrowl_display_order_btn',
                    'defaultvalue' => true,
                    'onclick' => 'showRelatedItems($jqPm(this))',
                    'tips' => $this->l('Display a direct access to your checkout page from your notification messages.'),
                    'label' => $this->l('Show checkout button')));
            $this->_html .= '<div>';
        }
        if (isset($tplObj->templateParams['display_title'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_title',
                'key_db' => 'display_title',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Display a title in your modal window. Then customize it\'s content and colors.'),
                'label' => $this->l('Show title')));
        }
        if (isset($tplObj->templateParams['display_free_content_1'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_free_content_1',
                'key_db' => 'display_free_content_1',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Display a rich contents area in your modal window. You can add texts, images, links, html, number of products which are in your customer cart, cart total price... '),
                'label' => $this->l('Show free content #1')));
        }
        if (isset($tplObj->templateParams['display_free_content_2'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_free_content_2',
                'key_db' => 'display_free_content_2',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Display a second rich contents area in your modal window. You can add texts, images, links, html, number of products which are in your customer cart, cart total price...'),
                'label' => $this->l('Show free content #2')));
        }
        if (isset($tplObj->templateParams['display_table_header'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_table_header',
                'key_db' => 'display_table_header',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Display table header for the cart summary list. Also known as columns titles.'),
                'label' => $this->l('Show header labels')));
        }
        if (isset($tplObj->templateParams['display_free_shipping'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_free_shipping',
                'key_db' => 'display_free_shipping',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Invite your customers to increase their cart rate, by showning the amount to reach to get free shipping.'),
                'label' => $this->l('Show the amount needed for free shipping')));
        }
        if (isset($tplObj->templateParams['display_subtotal'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_subtotal',
                'key_db' => 'display_subtotal',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Display subtotal amount into the table footer.'),
                'label' => $this->l('Show subtotal')));
        }
        if (isset($tplObj->templateParams['display_taxes'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_taxes',
                'key_db' => 'display_taxes',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Display tax amount into the table footer.'),
                'label' => $this->l('Show taxes')));
        }
        if (isset($tplObj->templateParams['display_discounts'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_discounts',
                'key_db' => 'display_discounts',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Display discount amount into the table footer.'),
                'label' => $this->l('Show discounts')));
        }
        if (isset($tplObj->templateParams['display_shipping'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_shipping',
                'key_db' => 'display_shipping',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => true,
                'tips' => $this->l('Display shipping amount into the table footer.'),
                'label' => $this->l('Show shipping rate')));
            $this->_html .= '<div class="pm_info shipping_info"><strong>'.$this->l('Enabling this option will display shipping cost of your default carrier if your client has not specified shipping address. Final shipping cost may vary depending on your customer carrier choice.').'</strong></div>';
        }
        if (isset($tplObj->templateParams['display_hook_cross_selling_on_cart']) && self::isCrossSellingInstalled()) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_hook_cross_selling_on_cart',
                'key_db' => 'display_hook_cross_selling_on_cart',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => false,
                'tips' => $this->l('Display cross selling suggestions (you must have our module Cross Selling On Cart installed & activated).'),
                'label' => $this->l('Display Cross Selling On Cart')));
        }
        if (isset($tplObj->templateParams['display_on_mobile'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_on_mobile',
                'key_db' => 'display_on_mobile',
                'defaultvalue' => false,
                'tips' => $this->l('Activate Modal Cart 3 for mobiles devices. Please be careful to its appearance and size. Please check Modal Cart display from your phone and those of your friends to be sure that everything is ok.'),
                'label' => $this->l('Display on mobile')));
        }
        $this->_endFieldset();
        $this->_startFieldset($this->l('Advanced configuration'), $this->_path . '/views/img/configuration-icon.png', true, true);
        if (isset($tplObj->templateParams['add_to_cart_selector'])) {
            $this->_displayInputText(array(
                'obj' => $params,
                'isarray' => true,
                'key' => 'add_to_cart_selector',
                'label' => $this->l('Add to cart button jQuery selector (global)'),
                'size' => '300px',
                'tips' => $this->l('Warning: do not change anything here if you don\'t know what you are doing !'),
                'required' => true));
        }
        if (isset($tplObj->templateParams['product_add_to_cart_selector'])) {
            $this->_displayInputText(array(
                'obj' => $params,
                'isarray' => true,
                'key' => 'product_add_to_cart_selector',
                'label' => $this->l('Add to cart button jQuery selector (product page)'),
                'size' => '300px',
                'tips' => $this->l('Warning: do not change anything here if you don\'t know what you are doing !'),
                'required' => true));
        }
        if (isset($tplObj->templateParams['z_index'])) {
            $this->_displayInputText(array(
                'obj' => $params,
                'isarray' => true,
                'key' => 'z_index',
                'label' => $this->l('CSS z-index value of window modal'),
                'size' => '40px',
                'tips' => $this->l('Warning: do not change anything here if you don\'t know what you are doing !'),
                'required' => true));
        }
        $this->_endFieldset();
    }
    protected function displayModalApparenceConfiguration($tplObj, $params)
    {
        $this->_startFieldset($this->l('Appearance'), $this->_path . '/views/img/color-swatch.png', false);
        if (isset($tplObj->templateParams['modal_width'])) {
            $this->_displayInputText(array(
                'obj' => $params,
                'isarray' => true,
                'key' => 'modal_width',
                'label' => $this->l('Window width'),
                'size' => '60px',
                'defaultvalue' => '600',
                'suffix' => '(px)',
                'required' => true));
        }
        if (isset($tplObj->templateParams['modal_background_color'])) {
            $this->_displayInputGradient(array(
                'obj' => $params,
                'isarray' => true,
                'key' => 'modal_background_color',
                'label' => $this->l('Window background color')));
        }
        if (isset($tplObj->templateParams['modal_border_radius'])) {
            $this->_displayInputSlider(array(
                'obj' => $params,
                'isarray' => true,
                'key' => 'modal_border_radius',
                'label' => $this->l('Window\'s rounded corner'),
                'minvalue' => '0',
                'maxvalue' => '50',
                'defaultvalue' => '25',
                'suffix' => 'px',
                'size' => '250px'));
        }
        if (isset($tplObj->templateParams['border'])) {
            $this->_displayInputBorder(array(
                'obj' => $params,
                'isarray' => true,
                'key' => 'border',
                'label' => $this->l('Window\'s borders')));
        }
        if (isset($tplObj->templateParams['padding'])) {
            $this->_displayInput4size(array(
                'obj'    => $params,
                'isarray'    => true,
                'key'    => 'padding',
                'label'    => $this->l('Window\'s padding')));
        }
        if (isset($tplObj->templateParams['margin'])) {
            $this->_displayInput4size(array(
                'obj'    => $params,
                'isarray'    => true,
                'key'    => 'margin',
                'label'    => $this->l('Window\'s margins')));
        }
        if (isset($tplObj->templateParams['box_shadow'])) {
            $this->_displayInputBoxShadow(array(
                'obj' => $params,
                'isarray' => true,
                'key' => 'box_shadow',
                'label' => $this->l('Window\'s box shadow')));
        }
        if (isset($tplObj->templateParams['background_overlay'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'background_overlay',
                'key_db' => 'background_overlay',
                'label' => $this->l('Soften site content'),
                'defaultvalue' => true,
                'tips' => $this->l('Display an overlay background color behind your opened window.'),
                'onclick' => 'showRelatedItems($jqPm(this))'));
        }
        if (isset($tplObj->templateParams['background_overlay_color']) || isset($tplObj->templateParams['background_overlay_opacity'])) {
            $this->_html .= '<div id="background_overlay_options" class="pm_option_group">';
            if (isset($tplObj->templateParams['background_overlay_color'])) {
                $this->_displayInputGradient(array(
                        'obj' => $params,
                        'isarray' => true,
                        'key' => 'background_overlay_color',
                        'label' => $this->l('Color')));
            }
            if (isset($tplObj->templateParams['background_overlay_opacity'])) {
                $this->_displayInputSlider(array(
                        'obj' => $params,
                        'isarray' => true,
                        'key' => 'background_overlay_opacity',
                        'label' => $this->l('Opacity'),
                        'minvalue' => '0',
                        'maxvalue' => '100',
                        'defaultvalue' => '25',
                        'suffix' => '%',
                        'size' => '250px'));
            }
            $this->_html .= '</div>';
        }
        if ($tplObj->templateKey == 'jgrowl') {
            $this->_displayInputColor(array(
                    'obj' => $params,
                    'isarray' => true,
                    'key' => 'jgrowl_font_color',
                    'label' => $this->l('Font color')));
            $this->_displayInputSlider(array(
                    'obj' => $params,
                    'isarray' => true,
                    'key' => 'jgrowl_font_size',
                    'label' => $this->l('Font size'),
                    'minvalue' => '0',
                    'maxvalue' => '50',
                    'defaultvalue' => '11',
                    'suffix' => 'px',
                    'size' => '250px'));
            $this->_html .= '<div>';
            $this->_displayCheckboxOverflow(array(
                    'obj' => $params,
                    'isarray' => true,
                    'key' => 'jgrowl_font_style',
                    'label' => $this->l('Font style'),
                    'height' => '60px',
                    'options' => $this->font_style));
        }
        if (isset($tplObj->templateParams['display_close_button'])) {
            $this->_displayInputActive(array(
                'obj' => $params,
                'isarray' => true,
                'key_active' => 'display_close_button',
                'key_db' => 'display_close_button',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'defaultvalue' => false,
                'tips' => $this->l('Display a close button at the top right corner of the window.'),
                'label' => $this->l('Show close button')));
        }
        $this->_endFieldset();
    }
    protected function displayTemplateWizard_mc3Tpl_CartSummary()
    {
        $tplObj = new mc3Tpl_CartSummary();
        $params = $tplObj->getTplParams();
        $this->_html .= '<input type="hidden" name="template_class" id="template_class" value="'.get_class($tplObj).'"/>';
        $this->_html .= '<input type="hidden" name="template_key" id="template_key" value="'.$tplObj->templateKey.'"/>';
        $this->displayModalGlobalConfiguration($tplObj, $params);
        $this->_html .= '<div class="clear"></div>';
        $this->_html .= '<style type="text/css">'.$tplObj->getBackOfficeCSS().'</style>';
        $this->display_mc3Tpl_CartSummary_Wizard();
        $this->displayModalApparenceConfiguration($tplObj, $params);
        $this->_includeHTMLAtEnd();
    }
    protected function displayTemplateWizard_mc3Tpl_LastProductAdded()
    {
        $tplObj = new mc3Tpl_LastProductAdded();
        $params = $tplObj->getTplParams();
        $this->_html .= '<input type="hidden" name="template_class" id="template_class" value="'.get_class($tplObj).'"/>';
        $this->_html .= '<input type="hidden" name="template_key" id="template_key" value="'.$tplObj->templateKey.'"/>';
        $this->displayModalGlobalConfiguration($tplObj, $params);
        $this->_html .= '<div class="clear"></div>';
        $this->_html .= '<style type="text/css">'.$tplObj->getBackOfficeCSS().'</style>';
        $this->display_mc3Tpl_LastProductAdded_Wizard();
        $this->displayModalApparenceConfiguration($tplObj, $params);
        $this->_includeHTMLAtEnd();
    }
    protected function displayTemplateWizard_mc3Tpl_jGrowl()
    {
        $tplObj = new mc3Tpl_jGrowl();
        $params = $tplObj->getTplParams();
        $this->_html .= '<input type="hidden" name="template_class" id="template_class" value="'.get_class($tplObj).'"/>';
        $this->_html .= '<input type="hidden" name="template_key" id="template_key" value="'.$tplObj->templateKey.'"/>';
        $this->displayModalGlobalConfiguration($tplObj, $params);
        $this->_html .= '<div class="clear"></div>';
        $this->_html .= '<style type="text/css">'.$tplObj->getBackOfficeCSS().'</style>';
        $this->display_mc3Tpl_jGrowl_Wizard();
        $this->displayModalApparenceConfiguration($tplObj, $params);
        $this->_includeHTMLAtEnd();
    }
    protected function getItemForm($params)
    {
        $this->_initGlobalVars();
        $this->_startForm(array('id' => 'itemForm', 'params' => $params));
        $template_class = trim(Tools::getValue('template_class'));
        if (!class_exists($template_class)) {
            die('Unknown template class : '.$template_class);
        }
        $tplObj = new $template_class();
        $item_name = Tools::getValue('item');
        $settings = $tplObj->getTplFields($item_name);
        if (isset($this->itemTranslated[$item_name])) {
            $this->_html .= '<h2>' . $this->l('Configuration:') . ' ' . $this->itemTranslated[$item_name] . '</h2>';
        }
        $this->_html .= '<input type="hidden" name="item" id="item" value="'.$item_name.'"/>';
        $this->_html .= '<input type="hidden" name="template_class" id="template_class" value="'.$template_class.'"/>';
        if (isset($settings['option_active'])) {
            $this->_html .= '<div class="pm_option_group">';
            $this->_displayInputActive(array(
                'obj' => $settings,
                'isarray' => true,
                'key_active' => 'option_active',
                'key_db' => 'option_active',
                'defaultvalue' => true,
                'onclick' => 'showRelatedItems($jqPm(this))',
                'label' => $this->l('Display')));
            $this->_html .= '</div>';
        }
        $this->_html .= '<div id="pm_options_display" style="display:'.((isset($settings['option_active'])) ? ($settings['option_active'] ? 'block' : 'none') : 'block').'"  >';
        if (isset($settings['background_color']) || isset($settings['width']) || isset($settings['css3_button_line_height']) || isset($settings['margin']) ||
        isset($settings['padding']) || isset($settings['border']) || isset($settings['border_radius']) || isset($settings['text_align']) || isset($settings['vertical_align']) || isset($settings['box_shadow'])) {
            $this->_displaySubTitle($this->l('Container options'));
            $this->_html .= '<div class="pm_option_group">';
            if (isset($settings['background_color'])) {
                $this->_displayInputGradient(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'background_color',
                    'label' => $this->l('Background color')));
            }
            if (isset($settings['width'])) {
                $this->_displayInputText(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'width',
                    'size' => '60px',
                    'label' => $this->l('Width')));
            }
            if (isset($settings['css3_button_line_height'])) {
                $this->_displayInputText(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'css3_button_line_height',
                    'size' => '60px',
                    'label' => $this->l('Line height')));
            }
            if (isset($settings['margin'])) {
                $this->_displayInput4size(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'margin',
                    'label' => $this->l('Margin')));
            }
            if (isset($settings['padding'])) {
                $this->_displayInput4size(array(
                    'obj'    => $settings,
                    'isarray'    => true,
                    'key'    => 'padding',
                    'label'    => $this->l('Padding')));
            }
            if (isset($settings['border'])) {
                $this->_displayInputBorder(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'border',
                    'label' => $this->l('Border')));
            }
            if (isset($settings['border_radius']) && $item_name != 'product_image' && !isset($settings['button'])) {
                $this->_displayInputSlider(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'border_radius',
                    'label' => $this->l('Round corner'),
                    'minvalue' => '0',
                    'maxvalue' => '50',
                    'defaultvalue' => '0',
                    'suffix' => 'px',
                    'size' => '250px'));
            }
            if (isset($settings['box_shadow']) && $item_name != 'product_image') {
                $this->_displayInputBoxShadow(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'box_shadow',
                    'label' => $this->l('Box shadow')));
            }
            if (isset($settings['text_align'])) {
                $this->_displaySelect(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'text_align',
                    'label' => $this->l('Horizontal align'),
                    'options' => array('left' => $this->l('Left'), 'center' => $this->l('Center'), 'right' => $this->l('Right'), 'justify' => $this->l('Justify')),
                    'defaultvalue' => false,
                    'size' => '200px'));
            }
            if (isset($settings['vertical_align'])) {
                $this->_displaySelect(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'vertical_align',
                    'label' => $this->l('Vertical align'),
                    'options' => array('top' => $this->l('Top'), 'middle' => $this->l('Middle'), 'bottom' => $this->l('Bottom')),
                    'defaultvalue' => false,
                    'size' => '200px'));
            }
            $this->_html .= '</div>';
        }
        if (isset($settings['allow_quantity_update']) || isset($settings['image_size']) || isset($settings['text']) || isset($settings['text_lang']) ||
        isset($settings['link']) || isset($settings['truncate_text']) || isset($settings['font_color']) || isset($settings['font_size']) ||
        isset($settings['content_border']) || isset($settings['font_style'])) {
            $this->_displaySubTitle($this->l('Content options'));
            if ($item_name == 'free_shipping') {
                $this->_showInfo($this->l('Available variables :%br%%br%%strong%%free_shipping%%/strong% keyword will output the remaining cart price before having free shipping%br%%strong%%nb_total_products%%/strong% keyword will output the number of products in the cart%br%%strong%%cart_total%%/strong% keyword will output the total cart amount%br%'));
            } elseif ($item_name == 'free_content_1' || $item_name == 'free_content_2' || $item_name == 'title') {
                $this->_showInfo($this->l('Available variables :%br%%br%%strong%%nb_total_products%%/strong% keyword will output the number of products in the cart%br%%strong%%cart_total%%/strong% keyword will output the total cart amount%br%'));
            }
            $this->_html .= '<div class="pm_option_group">';
            if (isset($settings['force_no_tax'])) {
                $this->_displayInputActive(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key_active' => 'force_no_tax',
                    'key_db' => 'force_no_tax',
                    'defaultvalue' => false,
                    'onclick' => 'showRelatedItems($jqPm(this))',
                    'tips' => $this->l('Show the sum of product\'s price without taxes or using your default taxes settings'),
                    'label' => $this->l('Display without tax')));
            }
            if (isset($settings['allow_quantity_update'])) {
                $this->_displayInputActive(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key_active' => 'allow_quantity_update',
                    'key_db' => 'allow_quantity_update',
                    'defaultvalue' => true,
                    'onclick' => 'showRelatedItems($jqPm(this))',
                    'tips' => $this->l('Display quantity action buttons for each product (will show, +, - and delete button).'),
                    'label' => $this->l('Allow quantity update ?')));
            }
            if (isset($settings['image_size'])) {
                $this->_displaySelect(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'image_size',
                    'label' => $this->l('Image size'),
                    'options' => $this->getImageType(),
                    'defaultvalue' => false,
                    'tips' => $this->l('Define the product image size you want to show in the modal window.'),
                    'size' => '250px'));
            }
            if (isset($settings['text'])) {
                $this->_displayInputText(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'text',
                    'label' => $this->l('Text')));
            }
            if (isset($settings['text_lang'])) {
                if (isset($settings['tinymce']) && $settings['tinymce']) {
                    $this->_displayRichTextareaLang(array(
                        'obj' => $settings,
                        'isarray' => true,
                        'key' => 'text_lang',
                        'label' => $this->l('Content to display:')));
                } else {
                    $this->_displayInputTextLang(array(
                        'obj' => $settings,
                        'isarray' => true,
                        'key' => 'text_lang',
                        'label' => $this->l('Text')));
                }
            }
            if (isset($settings['link'])) {
                $this->_displayInputActive(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key_active' => 'link',
                    'key_db' => 'link',
                    'label' => $this->l('Add a link to the product page ')));
            }
            if (isset($settings['content_border'])) {
                $this->_displayInputBorder(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'content_border',
                    'label' => $this->l('Border')));
            }
            if (isset($settings['border_radius']) && $item_name == 'product_image') {
                $this->_displayInputSlider(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'border_radius',
                    'label' => $this->l('Round corner'),
                    'minvalue' => '0',
                    'maxvalue' => '50',
                    'defaultvalue' => '0',
                    'suffix' => 'px',
                    'size' => '250px'));
            }
            if (isset($settings['box_shadow']) && $item_name == 'product_image') {
                $this->_displayInputBoxShadow(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'box_shadow',
                    'label' => $this->l('Box shadow')));
            }
            if (isset($settings['truncate_text'])) {
                $this->_displayInputActive(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key_active' => 'truncate_text',
                    'key_db' => 'truncate_text',
                    'defaultvalue' => true,
                    'onclick' => 'showRelatedItems($jqPm(this))',
                    'tips' => $this->l('Adjust the product description lenght (in number of characters).'),
                    'label' => $this->l('Truncate text ?')));
                $this->_html .= '<div id="pm_options_truncate_display" style="display:'.((isset($settings['truncate_text'])) ? ($settings['truncate_text'] ? 'block' : 'none') : 'block').'">';
                $this->_displayInputSlider(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'truncate_limit',
                    'label' => $this->l('Truncate limit'),
                    'minvalue' => '0',
                    'maxvalue' => (Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') !== false && (int)Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') > 0 ? Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') : 800),
                    'defaultvalue' => '0',
                    'suffix' => $this->l('chars'),
                    'tips' => $this->l('Set the number of chars you want for your product\'s description.'),
                    'size' => '250px'));
                $this->_html .= '</div>';
            }
            if (isset($settings['font_color'])) {
                $this->_displayInputColor(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'font_color',
                    'label' => $this->l('Font color')));
            }
            if (isset($settings['font_size'])) {
                $this->_displayInputSlider(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'font_size',
                    'label' => $this->l('Font size'),
                    'minvalue' => '0',
                    'maxvalue' => '50',
                    'defaultvalue' => '11',
                    'suffix' => 'px',
                    'size' => '250px'));
            }
            if (isset($settings['font_style'])) {
                $this->_displayCheckboxOverflow(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'font_style',
                    'height' => '60px',
                    'label' => $this->l('Font style'),
                    'options' => $this->font_style));
            }
            if (isset($settings['text_shadow'])) {
                $this->_displayInputTextShadow(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'text_shadow',
                    'label' => $this->l('Text shadow')));
            }
            $this->_html .= '</div>';
        }
        if (isset($settings['attributes'])) {
            $this->_displaySubTitle($this->l('Attributes Options'));
            $this->_html .= '<div class="pm_option_group">';
            $this->_displayInputActive(array(
                'obj' => $settings,
                'isarray' => true,
                'key_active' => 'attributes',
                'key_db' => 'attributes',
                'onclick' => 'showRelatedItems($jqPm(this))',
                'tips' => $this->l('Display product\'s attribute details.'),
                'label' => $this->l('Show product attributes')));
            $this->_html .= '<div id="pm_mc3_attributes_options" style="display:'.(isset($settings['attributes']) && $settings['attributes'] ? 'block' : 'none').'">';
            $this->_displayInputColor(array(
                'obj' => $settings,
                'isarray' => true,
                'key' => 'attributes_font_color',
                'label' => $this->l('Font color')));
            $this->_displayInputSlider(array(
                'obj' => $settings,
                'isarray' => true,
                'key' => 'attributes_font_size',
                'label' => $this->l('Font size'),
                'minvalue' => '0',
                'maxvalue' => '50',
                'defaultvalue' => '11',
                'suffix' => 'px',
                'size' => '250px'));
            $this->_displayCheckboxOverflow(array(
                'obj' => $settings,
                'isarray' => true,
                'key' => 'attributes_font_style',
                'label' => $this->l('Font style'),
                'height' => '60px',
                'options' => $this->font_style));
            $this->_html .= '</div>';
            $this->_html .= '</div>';
        }
        if (isset($settings['button']) && $settings['button']) {
            $this->_displaySubTitle($this->l('Button options'));
            $this->_html .= '<div class="global_use_clear_cart_button_option pm_option_group ui-state-highlight" style="margin-bottom:20px">';
            $this->_displayInputActive(array(
                'obj' => $settings,
                'isarray' => true,
                'key_active' => 'css3_background_flat',
                'key_db' => 'css3_background_flat',
                'defaultvalue' => true,
                'tips' => $this->l('Define the look of your buttons. Flat design is a simple gradient to top from bottom. Else, we will automatically create a complex gradient from the chosen color for a candy effect.'),
                'label' => $this->l('Use flat design background (else, a candy button will be generated)')));
            $this->_displayInputColor(array(
                'obj' => $settings,
                'isarray'    => true,
                'key' => 'css3_font_color',
                'label' => $this->l('Text color')));
            $this->_displayInputColor(array(
                'obj' => $settings,
                'isarray'    => true,
                'key' => 'css3_background_color',
                'label' => $this->l('Button background color')));
            $this->_displayInputColor(array(
                'obj' => $settings,
                'isarray'    => true,
                'key' => 'css3_font_color_hover',
                'label' => $this->l('Text color over')));
            $this->_displayInputColor(array(
                'obj' => $settings,
                'isarray'    => true,
                'key' => 'css3_background_color_hover',
                'label' => $this->l('Button background color over')));
            $this->_displayInput4size(array(
                'obj'    => $settings,
                'isarray'    => true,
                'key'    => 'css3_padding',
                'label'    => $this->l('Padding')));
            if (isset($settings['border_radius'])) {
                $this->_displayInputSlider(array(
                    'obj' => $settings,
                    'isarray' => true,
                    'key' => 'border_radius',
                    'label' => $this->l('Round corner'),
                    'minvalue' => '0',
                    'maxvalue' => '50',
                    'defaultvalue' => '0',
                    'suffix' => 'px',
                    'size' => '250px'));
            }
            $this->_html .= '</div>';
        }
        $this->_html .= '</div>';
        $this->_displaySubmit($this->l('Save'), 'submit_item_form');
        $this->_endForm(array('id' => 'itemForm'));
    }
    protected function displayCrossSellingConfiguration()
    {
        $this->_initGlobalVars();
        if (self::isCrossSellingInstalled()) {
            include_once(dirname(__FILE__) . '/../pm_crosssellingoncart/pm_crosssellingoncart.php');
            $objCrossSellingOnCart = new PM_CrossSellingOnCart('PM_MC_CSOC');
            if (!$objCrossSellingOnCart->isRegisteredInHook('MCBelow')) {
                $this->_html .= '<div class="warning warn clear">' . $this->l('In order to make Modal Cart working with Cross Selling on Cart, you must link them together first.') . '<br /><a href="' . $this->_base_config_url . '&linkCSOCwithMC3=1#ui-tabs-2" class="button">' . $this->l('Click here to link Modal Cart & Cross Selling on Cart !') . '</a></div>';
            } else {
                $this->_html .= '<div class="hint clear" style="display:block;margin-bottom:10px;"><a href="' . $this->_base_config_url . '&unlinkCSOCwithMC3=1">' . $this->l('Click here to unlink Cross Selling on Cart from Modal Cart') . '.</a></div>';
                $this->_html .= $objCrossSellingOnCart->displayShareConfig($this->_base_config_url);
            }
        } else {
            $this->_html .= '<div class="warning warn clear">
				' . $this->l('To enable this feature, you must have the module "Cross Selling on Cart", available at:') . '
				<a href="'.$this->_url_cross_selling.'" target="_blank">' . $this->l('Buy Cross Selling on Cart').'</a>
			</div>';
        }
    }
    protected function postProcessSaveTemplate()
    {
        $template_class = trim(Tools::getValue('template_class'));
        if (!class_exists($template_class)) {
            die('Unknown template class : '.$template_class);
        }
        $tplObj = new $template_class();
        $moduleConfiguration = $this->getModuleConfiguration();
        $moduleConfiguration['currentTemplateClass'] = $template_class;
        $this->setModuleConfiguration($moduleConfiguration);
        if (!Tools::getIsset('background_color_gradient') || Tools::getValue('background_color_gradient') != 1) {
            unset($_POST['background_color'][1]);
        }
        $fieldsOrder = Tools::getValue('fields_order');
        if (Tools::getIsset('fields_order') && !empty($fieldsOrder)) {
            $_POST['fields_order'] = explode('-', rtrim($fieldsOrder, '-'));
        } elseif (Tools::getIsset('fields_order') && empty($fieldsOrder)) {
            unset($_POST['fields_order']);
        }
        $settings = $tplObj->getTplParams();
        $settings['class_name'] = $template_class;
        $this->copyFromPost($settings, 'array');
        unset($settings['class_name']);
        $tplObj->setTplParams($settings);
        $tplObj->generateTpl();
        $tplObj->generateCSS();
        //$this->_html .= '<script type="text/javascript">$(document).ready(function() {show_info("' . $this->l('Saved') . '");});</script>';
    }
    protected function postProcessSaveItem()
    {
        $template_class = trim(Tools::getValue('template_class'));
        $item_name = trim(Tools::getValue('item'));
        if (!class_exists($template_class)) {
            die('Unknown template class : '.$template_class);
        }
        $tplObj = new $template_class();
        $settings = $tplObj->getTplFields($item_name);
        $settings['class_name'] = $template_class;
        if (!Tools::getIsset('background_color_gradient') || Tools::getValue('background_color_gradient') != 1) {
            unset($_POST['background_color'][1]);
        }
        if (!Tools::getIsset('font_style') && in_array('font_style', array_keys($settings))) {
            $_POST['font_style'] = array();
        }
        if (!Tools::getIsset('attributes_font_style') && in_array('attributes_font_style', array_keys($settings))) {
            $_POST['attributes_font_style'] = array();
        }
        if (Tools::getIsset('pm_js_callback')) {
            unset($_POST['pm_js_callback']);
        }
        if (Tools::getIsset('submit_item_form')) {
            unset($_POST['submit_item_form']);
        }
        $this->copyFromPost($settings, 'array');
        if (isset($settings['text_lang']) && self::_isFilledArray($settings['text_lang'])) {
            foreach ($settings['text_lang'] as $id_lang => $val) {
                if (Tools::strlen($val) == 0) {
                    unset($settings['text_lang'][$id_lang]);
                }
            }
        }
        unset($settings['class_name']);
        $tplObj->setTplFields($item_name, $settings);
        $tplObj->generateTpl();
        $tplObj->generateCSS();
        $this->_cleanOutput();
        if (!sizeof($this->_errors)) {
            $this->_html .= '<script type="text/javascript">
				parent.parent.reloadBackOfficeTemplate("'.$template_class.'");
				//parent.parent.show_info("' . $this->l('Saved') . '");
				parent.parent.closeDialogIframe();
			</script>';
        }
        $this->_echoOutput(true);
    }
    protected function getImageType()
    {
        $result = Db::getInstance()->ExecuteS('
			SELECT `id_image_type`, `name`, `width`, `height`
			FROM `' . _DB_PREFIX_ . 'image_type`
			WHERE `products` = 1');
        $return = '';
        if ($result) {
            foreach ($result as $img) {
                $return [$img ['name']] = preg_replace('/_default/', '', $img ['name']) . ' ('.$img ['width'].' x '.$img ['height'].') px';
            }
        }
        return $return;
    }
    public function getProductAttributeImage($id_product, $id_product_attribute)
    {
        if (!$id_product_attribute) {
            if (!$result = Db::getInstance()->ExecuteS('
				SELECT i.`cover`, i.`id_image`, i.`id_product`
				FROM `'._DB_PREFIX_.'image` i
				'.(version_compare(_PS_VERSION_, '1.5.0.0', '>=') ? Shop::addSqlAssociation('image', 'i') : '').'
				WHERE i.`id_product` = '.(int)($id_product).'
				ORDER BY `position`
				LIMIT 1')) {
                return false;
            }
        } else {
            if (!$result = Db::getInstance()->ExecuteS('
				SELECT pai.`id_image`, pai.`id_product_attribute`,'.($id_product).' AS `id_product`
				FROM `'._DB_PREFIX_.'product_attribute_image` pai
				'.(version_compare(_PS_VERSION_, '1.5.0.0', '>=') ? Shop::addSqlAssociation('product_attribute', 'pai') : '').'
				WHERE pai.`id_product_attribute` = '.(int)$id_product_attribute.'
				LIMIT 1')) {
                return false;
            }
        }
        return $result[0];
    }
    public static $currentTemplate = null;
    private function getCurrentTemplate()
    {
        if (self::$currentTemplate != null) {
            return self::$currentTemplate;
        }
        $moduleConfiguration = $this->getModuleConfiguration();
        if (self::_isFilledArray($moduleConfiguration) && isset($moduleConfiguration['currentTemplateClass']) && class_exists($moduleConfiguration['currentTemplateClass'])) {
            self::$currentTemplate = new $moduleConfiguration['currentTemplateClass']();
            return self::$currentTemplate;
        }
        return false;
    }
    public function hookHeader()
    {
        if ($this->_isInMaintenance()) {
            return false;
        }
        $tplObj = $this->getCurrentTemplate();
        if ($tplObj === false) {
            return;
        }
        global $link;
        $modalParams = $tplObj->getTplParams();
        if ((!isset($modalParams['display_on_mobile']) || !$modalParams['display_on_mobile']) && $this->_isMobileTheme()) {
            return false;
        }
        if ($tplObj->templateKey == 'jgrowl') {
            $this->_addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/jGrowl/jquery.jgrowl_minimized.js');
            $this->_addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/jGrowl/jquery.jgrowl.css', 'all');
        } else {
            if (version_compare(_PS_VERSION_, "1.4.9.0", '<=')) {
                $this->_addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/psold14fix-jquery.min.js');
                $this->_addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/magnific-popup-14.min.js');
            } else {
                $this->_addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/magnific-popup.min.js');
            }
            $this->_addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/magnific-popup.css', 'all');
        }
        $this->_addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/pm_modalcart3_global.css', 'all');
        $this->_addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/'.str_replace('.css', '-'.(version_compare(_PS_VERSION_, '1.5.0.0', '<') ? 1 : Context::getContext()->shop->id).'.css', self::DYNAMIC_CSS), 'all');
        if (version_compare(_PS_VERSION_, "1.6.0.0", '>=')) {
            $this->_addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/pm_modalcart3-1.6.js');
        } elseif (version_compare(_PS_VERSION_, "1.5.0.0", '>=')) {
            $this->_addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/pm_modalcart3-1.5.js');
        } else {
            $this->_addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/pm_modalcart3-1.4.js');
        }
        $this->_smarty->assign(array(
            'modalCartjQuerySelector' => $modalParams['add_to_cart_selector'],
            'modalCartProductjQuerySelector' => $modalParams['product_add_to_cart_selector'],
            'modalCartWidth'=> (isset($modalParams['modal_width']) ? $modalParams['modal_width'] : ''),
            'modalCartType'=> $tplObj->templateKey,
            'modalCartDisplayCloseButton'=> (isset($modalParams['display_close_button']) && $modalParams['display_close_button'] ? 'true' : 'false'),
            'modalCartFreeProductTranslation' => $this->l('Free !'),
            'modalCartOrderPageLink' => (version_compare(_PS_VERSION_, "1.5.0.0", '>=') ? Context::getContext()->link->getPageLink('order', true) : $link->getPageLink('order.php', true)),
            'modalCartControllerLink' => (version_compare(_PS_VERSION_, "1.5.0.0", '>=') ? Context::getContext()->link->getModuleLink('pm_modalcart3', 'ajax_front', array(), (Tools::usingSecureMode() ? true : false)) : __PS_BASE_URI__.'modules/pm_modalcart3/controllers/front/ajax_front.php'),
        ));
        if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
            return $this->display(__FILE__, 'views/templates/hook/pm_modalcart3_header.tpl');
        } else {
            return $this->display(__FILE__, 'pm_modalcart3_header.tpl');
        }
    }
    public function getModalToDisplay($id_product, $id_product_attribute, $pack = false)
    {
        $tplObj = $this->getCurrentTemplate();
        if ($tplObj === false) {
            return;
        }
        switch ($tplObj->templateKey) {
            case 'lpa':
                return $this->displayModalProduct($id_product, $id_product_attribute, $pack);
            case 'cs':
                return $this->displayCartSummary();
            case 'jgrowl':
                return $this->displayjGrowl($id_product, $id_product_attribute, $pack);
        }
    }
    private function getProductImageLink($id_product, $id_product_attribute)
    {
        $product = new Product($id_product, false, $this->_cookie->id_lang);
        $image = $this->getProductAttributeImage($product->id, $id_product_attribute);
        if (!$image || ($image['id_image'] == 0)) {
            $image = Product::getCover($product->id);
            $image['id_product'] = $product->id;
        }
        $id_image = Product::defineProductImage($image, $this->_cookie->id_lang);
        return $id_image;
    }
    private function displayjGrowl($id_product, $id_product_attribute, $pack)
    {
        global $cart, $link;
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $id_lang = $this->context->language->id;
        } else {
            $id_lang = $this->_cookie->id_lang;
        }
        $tplObj = $this->getCurrentTemplate();
        if ($tplObj === false) {
            return;
        }
        $modalParams = $tplObj->getTplParams();
        $modalItems = $tplObj->getTplFields();
        $summary = $cart->getSummaryDetails();
        if (!self::_isFilledArray($summary['products'])) {
            die();
        }
        $nb_total_products = 0;
        foreach ($summary['products'] as $productKey => $product) {
            $nb_total_products += (int)$product['cart_quantity'];
        }
        $customizedDatas = Product::getAllCustomizedDatas((int)$cart->id);
        Product::addCustomizationPrice($summary['products'], $customizedDatas);
        foreach ($summary['products'] as $productKey => $product) {
            if ($product['id_product'] == $id_product && $product['id_product_attribute'] == $id_product_attribute) {
                if (isset($modalItems['product_image']['image_size'])) {
                    $id_image = $this->getProductImageLink($product['id_product'], $product['id_product_attribute']);
                    $summary['products'][$productKey]['image_src'] =  $link->getImageLink($product['link_rewrite'], $id_image, $modalItems['product_image']['image_size']);
                    if (!empty($product['id_product_attribute'])) {
                        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
                            $summary['products'][$productKey]['image_link'] =  $link->getProductLink($product['id_product'], null, null, null, null, null, $product['id_product_attribute'], Configuration::get('PS_REWRITING_SETTINGS'), false, true);
                        } elseif (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                            $summary['products'][$productKey]['image_link'] =  $link->getProductLink($product['id_product'], null, null, null, null, null, $product['id_product_attribute']);
                        } else {
                            $summary['products'][$productKey]['image_link'] =  $link->getProductLink($product['id_product']);
                        }
                    } else {
                        $summary['products'][$productKey]['image_link'] =  $link->getProductLink($product['id_product']);
                    }
                }
                if (!isset($summary['products'][$productKey]['id_address_delivery'])) {
                    $summary['products'][$productKey]['id_address_delivery'] = 0;
                }
                if ($pack && version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                    $ap5ModuleInstance = Module::getInstanceByName('pm_advancedpack');
                    if (Validate::isLoadedObject($ap5ModuleInstance)) {
                        $summary['products'][$productKey]['attributes'] = $ap5ModuleInstance->displayPackContent($product['id_product'], $product['id_product_attribute'], pm_advancedpack::PACK_CONTENT_SHOPPING_CART);
                        $summary['products'][$productKey]['isPack'] = true;
                    } else {
                        $summary['products'][$productKey]['attributes'] = '';
                    }
                } else {
                    if (!isset($summary['products'][$productKey]['attributes'])) {
                        $summary['products'][$productKey]['attributes'] = '';
                    }
                }
                if (isset($product['ecotax']) && $product['ecotax'] > 0 && !isset($product['ecotax_wt'])) {
                    if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                        $ecotax_rate = (float)Tax::getProductEcotaxRate($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                        $ecotax_tax_amount = Tools::ps_round($product['ecotax'], 2);
                        if ((int)Configuration::get('PS_TAX')) {
                            $ecotax_tax_amount = Tools::ps_round($ecotax_tax_amount * (1 + $ecotax_rate / 100), 2);
                        }
                        $summary['products'][$productKey]['ecotax_wt'] = $ecotax_tax_amount;
                    } else {
                        $summary['products'][$productKey]['ecotax_wt'] = $product['ecotax'];
                    }
                } else {
                    $summary['products'][$productKey]['ecotax_wt'] = 0;
                }
                $this->_smarty->assign('product', $summary['products'][$productKey]);
                break;
            }
        }
        $this->_smarty->assign(array(
            'summary' => $summary,
            'tax_rule' => Configuration::get('PS_TAX'),
            'nb_total_products' => $nb_total_products,
            'free_content_1' => ($modalParams['display_free_content_1'] && isset($modalItems['free_content_1']) && isset($modalItems['free_content_1']['text_lang'][$id_lang])) ? $modalItems['free_content_1']['text_lang'][$id_lang] : '',
            'free_content_2' => ($modalParams['display_free_content_2'] && isset($modalItems['free_content_2']) && isset($modalItems['free_content_2']['text_lang'][$id_lang])) ? $modalItems['free_content_2']['text_lang'][$id_lang] : '',
            'order_now' => (isset($modalItems['order_now']) && isset($modalItems['order_now']['text_lang'][$id_lang])) ? $modalItems['order_now']['text_lang'][$id_lang] : $this->l('Order now'),
            'order_page_link' => $link->getPageLink('order.php'),
            'including_ecotax' => $this->l('including %ecotax% for ecotax'),
        ));
        Tools::safePostVars();
        $jGrowlOptions = array();
        if (isset($modalParams['jgrowl_sticky']) && $modalParams['jgrowl_sticky']) {
            $jGrowlOptions[] = 'sticky:true';
        } else {
            $jGrowlOptions[] = 'sticky:false';
            $jGrowlOptions[] = 'life:"'.(int)$modalParams['jgrowl_lifetime'].'"';
        }
        $jGrowlOptions[] = 'easing:"swing"';
        if ($modalParams['jgrowl_position'] == 'top-right' || $modalParams['jgrowl_position'] == 'bottom-right') {
            $jGrowlOptions[] = 'beforeOpen:function(callerElement) {
				if ($("div.ui-state-jGrowl-theme-mc3:visible").size() == 0) {
					$("div.jGrowl.top-right").css("right", -'.$modalParams['modal_width'].');
					$("div.jGrowl.top-right").animate({right: 0}, 400);
					$(callerElement).animate({opacity: 1}, 600).show(600);
				} else {
					$(callerElement).animate({opacity: 1}, 600).show(600);
				}
			}';
            $jGrowlOptions[] = 'beforeClose:function(callerElement) {
				if ($("div.ui-state-jGrowl-theme-mc3:visible").size() == 1) {
					$("div.jGrowl.top-right").animate({right: -'.$modalParams['modal_width'].'}, 400, function() {
						$(callerElement).remove();
					});
				} else {
					$(callerElement).animate({opacity: 0}, 400, function() { $(this).remove(); }).hide(400);
				}
			}';
        } elseif ($modalParams['jgrowl_position'] == 'top-left' || $modalParams['jgrowl_position'] == 'bottom-left') {
            $jGrowlOptions[] = 'beforeOpen:function(callerElement) {
				if ($("div.ui-state-jGrowl-theme-mc3:visible").size() == 0) {
					$("div.jGrowl.top-left").css("left", -'.$modalParams['modal_width'].');
					$("div.jGrowl.top-left").animate({left: 0}, 400);
					$(callerElement).animate({opacity: 1}, 600).show(600);
				} else {
					$(callerElement).animate({opacity: 1}, 600).show(600);
				}
			}';
            $jGrowlOptions[] = 'beforeClose:function(callerElement) {
				if ($("div.ui-state-jGrowl-theme-mc3:visible").size() == 1) {
					$("div.jGrowl.top-left").animate({left: -'.$modalParams['modal_width'].'}, 400, function() {
						$(callerElement).remove();
					});
				} else {
					$(callerElement).animate({opacity: 0}, 400, function() { $(this).remove(); }).hide(400);
				}
			}';
        }
        $jGrowlOptions[] = 'closeTemplate:""';
        $jGrowlOptions[] = 'animateOpen:{}';
        $jGrowlOptions[] = 'animateClose:{ opacity: "hide" }';
        $message = trim(str_replace("\r", '', str_replace("\n", '', addcslashes($this->display(__FILE__, 'tpl/'.$tplObj->getTplName()), "'"))));
        echo '
				$.jGrowl.defaults.position = "'.$modalParams['jgrowl_position'].'";
				$.jGrowl.defaults.closerTemplate = "<div>'.$this->l('[ close all ]').'</div>";
				$.jGrowl(\''.$message.'\', {themeState:"jGrowl-theme-mc3", '.implode(',', $jGrowlOptions).'});
		';
    }
    private function displayModalProduct($id_product, $id_product_attribute, $pack)
    {
        global $cart, $link;
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $id_lang = $this->context->language->id;
        } else {
            $id_lang = $this->_cookie->id_lang;
        }
        $tplObj = $this->getCurrentTemplate();
        if ($tplObj === false) {
            return;
        }
        $modalParams = $tplObj->getTplParams();
        $modalItems = $tplObj->getTplFields();
        $summary = $cart->getSummaryDetails();
        if (!self::_isFilledArray($summary['products'])) {
            if (Tools::getValue('action') == 'refresh') {
                die('<script type="text/javascript">$(document).ready(function() { mc3_closeModal(); });</script>');
            } else {
                die();
            }
        }
        $customizedDatas = Product::getAllCustomizedDatas((int)$cart->id);
        Product::addCustomizationPrice($summary['products'], $customizedDatas);
        $nb_total_products = 0;
        foreach ($summary['products'] as $productKey => $product) {
            $nb_total_products += (int)$product['cart_quantity'];
        }
        foreach ($summary['products'] as $productKey => $product) {
            if ($product['id_product'] == $id_product && $product['id_product_attribute'] == $id_product_attribute) {
                if (isset($modalItems['product_image']['image_size'])) {
                    $id_image = $this->getProductImageLink($product['id_product'], $product['id_product_attribute']);
                    $summary['products'][$productKey]['image_src'] =  $link->getImageLink($product['link_rewrite'], $id_image, $modalItems['product_image']['image_size']);
                    if (!empty($product['id_product_attribute'])) {
                        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
                            $summary['products'][$productKey]['image_link'] =  $link->getProductLink($product['id_product'], null, null, null, null, null, $product['id_product_attribute'], Configuration::get('PS_REWRITING_SETTINGS'), false, true);
                        } elseif (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                            $summary['products'][$productKey]['image_link'] =  $link->getProductLink($product['id_product'], null, null, null, null, null, $product['id_product_attribute']);
                        } else {
                            $summary['products'][$productKey]['image_link'] =  $link->getProductLink($product['id_product']);
                        }
                    } else {
                        $summary['products'][$productKey]['image_link'] =  $link->getProductLink($product['id_product']);
                    }
                }
                if (!isset($summary['products'][$productKey]['id_address_delivery'])) {
                    $summary['products'][$productKey]['id_address_delivery'] = 0;
                }
                if ($pack && version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                    $ap5ModuleInstance = Module::getInstanceByName('pm_advancedpack');
                    if (Validate::isLoadedObject($ap5ModuleInstance)) {
                        $summary['products'][$productKey]['attributes'] = $ap5ModuleInstance->displayPackContent($product['id_product'], $product['id_product_attribute'], pm_advancedpack::PACK_CONTENT_SHOPPING_CART);
                        $summary['products'][$productKey]['isPack'] = true;
                    } else {
                        $summary['products'][$productKey]['attributes'] = '';
                    }
                } else {
                    if (!isset($summary['products'][$productKey]['attributes'])) {
                        $summary['products'][$productKey]['attributes'] = '';
                    }
                }
                if (isset($product['ecotax']) && $product['ecotax'] > 0 && !isset($product['ecotax_wt'])) {
                    if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                        $ecotax_rate = (float)Tax::getProductEcotaxRate($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                        $ecotax_tax_amount = Tools::ps_round($product['ecotax'], 2);
                        if ((int)Configuration::get('PS_TAX')) {
                            $ecotax_tax_amount = Tools::ps_round($ecotax_tax_amount * (1 + $ecotax_rate / 100), 2);
                        }
                        $summary['products'][$productKey]['ecotax_wt'] = $ecotax_tax_amount;
                    } else {
                        $summary['products'][$productKey]['ecotax_wt'] = $product['ecotax'];
                    }
                } else {
                    $summary['products'][$productKey]['ecotax_wt'] = 0;
                }
                $this->_smarty->assign('product', $summary['products'][$productKey]);
                break;
            }
        }
        if (method_exists($this->_smarty, 'register_function')) {
            $this->_smarty->register_function('mc3_truncateHTML', 'pm_modalcart3::_truncateHTMLSmarty');
        } elseif (method_exists($this->_smarty, 'registerPlugin')) {
            $this->_smarty->registerPlugin('function', 'mc3_truncateHTML', 'pm_modalcart3::_truncateHTMLSmarty');
        } elseif (function_exists('smartyRegisterFunction')) {
            smartyRegisterFunction($this->_smarty, 'function', 'mc3_truncateHTML', 'pm_modalcart3::_truncateHTMLSmarty');
        }
        $total_free_ship = $this->_getAmountToReachForFreeShipping($summary, $cart);
        $summary['free_ship'] = $total_free_ship;
        $this->_smarty->assign('free_ship', $total_free_ship);
        $this->_smarty->assign('quantity_added', (int)abs(Tools::getValue('quantity_added', 1)));
        $this->_smarty->assign(array(
            'summary' => $summary,
            'tax_rule' => Configuration::get('PS_TAX'),
            'nb_total_products' => $nb_total_products,
            'free_content_1' => ($modalParams['display_free_content_1'] && isset($modalItems['free_content_1']) && isset($modalItems['free_content_1']['text_lang'][$id_lang])) ? $modalItems['free_content_1']['text_lang'][$id_lang] : '',
            'free_content_2' => ($modalParams['display_free_content_2'] && isset($modalItems['free_content_2']) && isset($modalItems['free_content_2']['text_lang'][$id_lang])) ? $modalItems['free_content_2']['text_lang'][$id_lang] : '',
            'free_shipping' => ($modalParams['display_free_shipping'] && isset($modalItems['free_shipping']) && isset($modalItems['free_shipping']['text_lang'][$id_lang])) ? $modalItems['free_shipping']['text_lang'][$id_lang] : '',
            'title' => ($modalParams['display_title'] && isset($modalItems['title']) && isset($modalItems['title']['text_lang'][$id_lang])) ? $modalItems['title']['text_lang'][$id_lang] : '',
            'keep_shopping' => (isset($modalItems['keep_shopping']) && isset($modalItems['keep_shopping']['text_lang'][$id_lang])) ? $modalItems['keep_shopping']['text_lang'][$id_lang] : $this->l('Keep shopping'),
            'order_now' => (isset($modalItems['order_now']) && isset($modalItems['order_now']['text_lang'][$id_lang])) ? $modalItems['order_now']['text_lang'][$id_lang] : $this->l('Order now'),
            'order_page_link' => $link->getPageLink('order.php'),
            'customization_text_field_label' => $this->l('Text #'),
            'including_ecotax' => $this->l('including %ecotax% for ecotax'),
            'isVirtualCart' => $cart->isVirtualCart(),
            'productNumber' => $cart->nbProducts(),
            'voucherAllowed' => Configuration::get('PS_VOUCHERS'),
            'shippingCost' => $cart->getOrderTotal(true, 5),
            'shippingCostTaxExc' => $cart->getOrderTotal(false, 5),
            'customizedDatas' => $customizedDatas,
            'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
            'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
            'hook_cross_selling_on_cart' => (version_compare(_PS_VERSION_, '1.5.0.0', '>=') ? Hook::exec('MCBelow') : Module::hookExec('MCBelow'))
            ));
        Tools::safePostVars();
        return $this->display(__FILE__, 'tpl/'.$tplObj->getTplName());
    }
    private function displayCartSummary()
    {
        global $cart, $link;
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $id_lang = $this->_context->language->id;
        } else {
            $id_lang = $this->_cookie->id_lang;
        }
        $tplObj = $this->getCurrentTemplate();
        if ($tplObj === false) {
            return;
        }
        $modalParams = $tplObj->getTplParams();
        $modalItems = $tplObj->getTplFields();
        $summary = $cart->getSummaryDetails();
        if (!self::_isFilledArray($summary['products'])) {
            if (Tools::getValue('action') == 'refresh') {
                die('<script type="text/javascript">$(document).ready(function() { mc3_closeModal(); });</script>');
            } else {
                die();
            }
        }
        $customizedDatas = Product::getAllCustomizedDatas((int)$cart->id);
        Product::addCustomizationPrice($summary['products'], $customizedDatas);
        $total_free_ship = $this->_getAmountToReachForFreeShipping($summary, $cart);
        $summary['free_ship'] = $total_free_ship;
        $this->_smarty->assign('free_ship', $total_free_ship);
        $nb_total_products = 0;
        $ap5ModuleInstance = false;
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $ap5ModuleInstance = Module::getInstanceByName('pm_advancedpack');
        }
        foreach (array('products', 'gift_products') as $cartProductType) {
            if (isset($summary[$cartProductType]) && self::_isFilledArray($summary[$cartProductType])) {
                foreach ($summary[$cartProductType] as $productKey => $product) {
                    if ($cartProductType == 'gift_products') {
                        $cartProductType = 'products';
                        $productKey .= '-gift';
                        $summary['products'][$productKey] = $product;
                    }
                    if (isset($modalItems['product_image']['image_size'])) {
                        $id_image = $this->getProductImageLink($product['id_product'], $product['id_product_attribute']);
                        $summary[$cartProductType][$productKey]['image_src'] =  $link->getImageLink($product['link_rewrite'], $id_image, $modalItems['product_image']['image_size']);
                        if (!empty($product['id_product_attribute'])) {
                            if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
                                $summary[$cartProductType][$productKey]['image_link'] =  $link->getProductLink($product['id_product'], null, null, null, null, null, $product['id_product_attribute'], Configuration::get('PS_REWRITING_SETTINGS'), false, true);
                            } elseif (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                                $summary[$cartProductType][$productKey]['image_link'] =  $link->getProductLink($product['id_product'], null, null, null, null, null, $product['id_product_attribute']);
                            } else {
                                $summary[$cartProductType][$productKey]['image_link'] =  $link->getProductLink($product['id_product']);
                            }
                        } else {
                            $summary[$cartProductType][$productKey]['image_link'] =  $link->getProductLink($product['id_product']);
                        }
                    }
                    if (!isset($summary[$cartProductType][$productKey]['id_address_delivery'])) {
                        $summary[$cartProductType][$productKey]['id_address_delivery'] = 0;
                    }
                    if (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && Validate::isLoadedObject($ap5ModuleInstance) && AdvancedPack::isValidPack($product['id_product'])) {
                        $summary[$cartProductType][$productKey]['attributes'] = $ap5ModuleInstance->displayPackContent($product['id_product'], $product['id_product_attribute'], pm_advancedpack::PACK_CONTENT_SHOPPING_CART);
                        $summary[$cartProductType][$productKey]['isPack'] = true;
                    } else {
                        if (!isset($summary[$cartProductType][$productKey]['attributes'])) {
                            $summary[$cartProductType][$productKey]['attributes'] = '';
                        }
                    }
                    $nb_total_products += (int)$product['cart_quantity'];
                    if (isset($product['ecotax']) && $product['ecotax'] > 0 && !isset($product['ecotax_wt'])) {
                        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                            $ecotax_rate = (float)Tax::getProductEcotaxRate($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                            $ecotax_tax_amount = Tools::ps_round($product['ecotax'], 2);
                            if ((int)Configuration::get('PS_TAX')) {
                                $ecotax_tax_amount = Tools::ps_round($ecotax_tax_amount * (1 + $ecotax_rate / 100), 2);
                            }
                            $summary[$cartProductType][$productKey]['ecotax_wt'] = $ecotax_tax_amount;
                        } else {
                            $summary[$cartProductType][$productKey]['ecotax_wt'] = $product['ecotax'];
                        }
                    } else {
                        $summary[$cartProductType][$productKey]['ecotax_wt'] = 0;
                    }
                }
            }
        }
        $this->_smarty->assign(array(
            'summary' => $summary,
            'tax_rule' => Configuration::get('PS_TAX'),
            'nb_total_products' => $nb_total_products,
            'free_content_1' => ($modalParams['display_free_content_1'] && isset($modalItems['free_content_1']) && isset($modalItems['free_content_1']['text_lang'][$id_lang])) ? $modalItems['free_content_1']['text_lang'][$id_lang] : '',
            'free_content_2' => ($modalParams['display_free_content_2'] && isset($modalItems['free_content_2']) && isset($modalItems['free_content_2']['text_lang'][$id_lang])) ? $modalItems['free_content_2']['text_lang'][$id_lang] : '',
            'free_shipping' => ($modalParams['display_free_shipping'] && isset($modalItems['free_shipping']) && isset($modalItems['free_shipping']['text_lang'][$id_lang])) ? $modalItems['free_shipping']['text_lang'][$id_lang] : '',
            'title' => ($modalParams['display_title'] && isset($modalItems['title']) && isset($modalItems['title']['text_lang'][$id_lang])) ? $modalItems['title']['text_lang'][$id_lang] : '',
            'keep_shopping' => (isset($modalItems['keep_shopping']) && isset($modalItems['keep_shopping']['text_lang'][$id_lang])) ? $modalItems['keep_shopping']['text_lang'][$id_lang] : $this->l('Keep shopping'),
            'order_now' => (isset($modalItems['order_now']) && isset($modalItems['order_now']['text_lang'][$id_lang])) ? $modalItems['order_now']['text_lang'][$id_lang] : $this->l('Order now'),
            'order_page_link' => $link->getPageLink('order.php'),
            'shipping_label' => (isset($modalItems['shipping_label']['text_lang'][$id_lang]) ? $modalItems['shipping_label']['text_lang'][$id_lang] : $this->l('Shipping :')),
            'subtotal_label' => (isset($modalItems['subtotal_label']['text_lang'][$id_lang]) ? $modalItems['subtotal_label']['text_lang'][$id_lang] : $this->l('Subtotal :')),
            'total_label' => (isset($modalItems['total_label']['text_lang'][$id_lang]) ? $modalItems['total_label']['text_lang'][$id_lang] : $this->l('Total :')),
            'total_tax_label' => (isset($modalItems['total_tax_label']['text_lang'][$id_lang]) ? $modalItems['total_tax_label']['text_lang'][$id_lang] : $this->l('Total tax :')),
            'discounts_label' => (isset($modalItems['discounts_label']['text_lang'][$id_lang]) ? $modalItems['discounts_label']['text_lang'][$id_lang] : $this->l('Total discounts :')),
            'product_name_thead' => $this->l('Product'),
            'product_availability_thead' => $this->l('Availability'),
            'product_image_thead' => $this->l('Image'),
            'product_price_thead' => $this->l('Price'),
            'product_tax_thead' => $this->l('Tax'),
            'product_quantity_thead' => $this->l('Qty'),
            'product_total_thead' => $this->l('Total'),
            'product_is_available' => $this->l('Available'),
            'product_is_in_stock' => $this->l('In Stock'),
            'product_is_out_of_stock' => $this->l('Out of stock'),
            'customization_text_field_label' => $this->l('Text #'),
            'free_shipping_on_cart' => $this->l('Free Shipping!'),
            'gift_product' => $this->l('Free!'),
            'including_ecotax' => $this->l('including %ecotax% for ecotax'),
            'isVirtualCart' => $cart->isVirtualCart(),
            'productNumber' => $cart->nbProducts(),
            'voucherAllowed' => Configuration::get('PS_VOUCHERS'),
            'shippingCost' => $cart->getOrderTotal(true, 5),
            'shippingCostTaxExc' => $cart->getOrderTotal(false, 5),
            'customizedDatas' => $customizedDatas,
            'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
            'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
            'hook_cross_selling_on_cart' => (version_compare(_PS_VERSION_, '1.5.0.0', '>=') ? Hook::exec('MCBelow') : Module::hookExec('MCBelow'))
            ));
        Tools::safePostVars();
        return $this->display(__FILE__, 'tpl/'.$tplObj->getTplName());
    }
    protected function _updateAdvancedStylesDb($css_styles = '')
    {
        Configuration::updateValue('PM_'.self::$_module_prefix.'_ADVANCED_STYLES', self::getDataSerialized($css_styles));
        $tplObj = $this->getCurrentTemplate();
        if ($tplObj === false) {
            return true;
        }
        $tplObj->generateCSS();
        return true;
    }
    private function _getAmountToReachForFreeShipping($summary, $cart)
    {
        $total_free_ship = 0;
        if ($free_ship = Tools::convertPrice((float)Configuration::get('PS_SHIPPING_FREE_PRICE'), new Currency((int)$cart->id_currency))) {
            if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                if ($summary['free_ship']) {
                    $total_free_ship = 0;
                } else {
                    $total_free_ship =  (float)Configuration::get('PS_SHIPPING_FREE_PRICE') - ($summary['total_products_wt'] + $summary['total_discounts']);
                }
            } else {
                $discounts = $cart->getDiscounts();
                $total_free_ship =  $free_ship - ($summary['total_products_wt'] + $summary['total_discounts']);
                foreach ($discounts as $discount) {
                    if ($discount['id_discount_type'] == 3) {
                        $total_free_ship = 0;
                        return $total_free_ship;
                    }
                }
            }
        }
        if (!$total_free_ship) {
            if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                $deliveryOption = $cart->getDeliveryOption();
                if (self::_isFilledArray($deliveryOption) && isset($deliveryOption[$cart->id_address_delivery])) {
                    $id_carrier = (int)current(explode(',', $deliveryOption[$cart->id_address_delivery]));
                    $carrier = new Carrier($id_carrier);
                    if (Validate::isLoadedObject($carrier) && $carrier->getShippingMethod() ==  Carrier::SHIPPING_METHOD_PRICE) {
                        $default_country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Configuration::get('PS_LANG_DEFAULT'));
                        $id_zone = (int)$default_country->id_zone;
                        if (!$cart->isMultiAddressDelivery() && isset($cart->id_address_delivery) && $cart->id_address_delivery && Customer::customerHasAddress($cart->id_customer, $cart->id_address_delivery)) {
                            $id_zone = Address::getZoneById((int)$cart->id_address_delivery);
                        }
                        $order_total = $cart->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING);
                        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT r.delimiter1
								FROM `'._DB_PREFIX_.'delivery` d
								LEFT JOIN `'._DB_PREFIX_.'range_price` r ON d.`id_range_price` = r.`id_range_price`
								WHERE d.`id_zone` = '.(int)$id_zone.'
								AND price = 0
								AND '.(float)$order_total.' < r.`delimiter2`
								AND d.`id_carrier` = '.(int)$carrier->id.'
								'.Carrier::sqlDeliveryRangeShop('range_price').'
								ORDER BY r.`delimiter1` ASC');
                        if ($result !== false) {
                            $total_free_ship = (float)$result - ($summary['total_products_wt'] + $summary['total_discounts']);
                        }
                    }
                }
            } else {
                global $defaultCountry;
                $minShippingPrice = null;
                if (Configuration::get('PS_SHIPPING_METHOD') == 0) {
                    if (isset($cart->id_address_delivery)
                        and $cart->id_address_delivery
                        and Customer::customerHasAddress($cart->id_customer, $cart->id_address_delivery)) {
                        $id_zone = Address::getZoneById((int)($cart->id_address_delivery));
                    } else {
                        if (!isset($defaultCountry) || !Validate::isLoadedObject($defaultCountry)) {
                            $defaultCountry = new Country(Configuration::get('_PS_COUNTRY_DEFAULT_'), Configuration::get('_PS_LANG_DEFAULT_'));
                        }
                        $id_zone = (int)$defaultCountry->id_zone;
                    }
                    if (defined('Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING')) {
                        $order_total = $cart->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING);
                    } else {
                        $order_total = $cart->getOrderTotal(true, 7);
                    }
                    $id_carrier = $cart->id_carrier;
                    if (empty($id_carrier) && $cart->isCarrierInRange(Configuration::get('PS_CARRIER_DEFAULT'), $id_zone)) {
                        $id_carrier = (int)(Configuration::get('PS_CARRIER_DEFAULT'));
                    }
                    if (empty($id_carrier)) {
                        if ((int)($cart->id_customer)) {
                            $customer = new Customer((int)($cart->id_customer));
                            $result = Carrier::getCarriers((int)(_PS_LANG_DEFAULT_), true, false, (int)($id_zone), $customer->getGroups());
                            unset($customer);
                        } else {
                            $result = Carrier::getCarriers((int)(_PS_LANG_DEFAULT_), true, false, (int)($id_zone));
                        }
                        $orderTotalWithoutShipping = null;
                        foreach ($result as $k => $row) {
                            if ($row['id_carrier'] == Configuration::get('PS_CARRIER_DEFAULT')) {
                                continue;
                            }
                            $carrier = new Carrier((int)($row['id_carrier']));
                            if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT) or ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE and $carrier->getMaxDeliveryPriceByPrice($id_zone) === false)) {
                                unset($result[$k]);
                                continue;
                            }
                            if ($row['range_behavior']) {
                                if ($orderTotalWithoutShipping == null) {
                                    if (defined('Cart::BOTH_WITHOUT_SHIPPING')) {
                                        $orderTotalWithoutShipping = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
                                    } else {
                                        $orderTotalWithoutShipping = $cart->getOrderTotal(true, 4);
                                    }
                                }
                                if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE and (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $orderTotalWithoutShipping, $id_zone, (int)($cart->id_currency)))) {
                                    unset($result[$k]);
                                    continue;
                                }
                            }
                            $shipping = $carrier->getDeliveryPriceByPrice($order_total, $id_zone, (int)($cart->id_currency));
                            if (!isset($minShippingPrice)) {
                                $minShippingPrice = $shipping;
                            }
                            if ($shipping <= $minShippingPrice) {
                                $id_carrier = (int)($row['id_carrier']);
                                $minShippingPrice = $shipping;
                            }
                        }
                    }
                    if (empty($id_carrier)) {
                        $id_carrier = Configuration::get('PS_CARRIER_DEFAULT');
                    }
                    $carrier = new Carrier($id_carrier);
                    if (Validate::isLoadedObject($carrier)) {
                        if ($carrier->is_free) {
                            return 0;
                        }
                        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
						SELECT r.`delimiter1`
						FROM `'._DB_PREFIX_.'delivery` d
						LEFT JOIN `'._DB_PREFIX_.'range_price` r ON (d.`id_range_price` = r.`id_range_price`)
						WHERE d.`id_zone` = '.(int)$id_zone.'
						AND price = 0
						AND '.(float)$order_total.' < r.`delimiter2`
						AND d.`id_carrier` = '.(int)$carrier->id.'
						ORDER BY r.`delimiter1` ASC');
                        if ($result !== false) {
                            $discounts = $cart->getDiscounts();
                            $total_free_ship = (float)$result - ($summary['total_products_wt'] + $summary['total_discounts']);
                            foreach ($discounts as $discount) {
                                if ($discount['id_discount_type'] == 3) {
                                    return 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $total_free_ship;
    }
}
