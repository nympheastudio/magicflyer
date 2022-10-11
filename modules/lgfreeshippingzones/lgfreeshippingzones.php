<?php
/**
 *  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class LGFreeshippingzones extends Module
{
    public $bootstrap;
    public function __construct()
    {
        $this->name          = 'lgfreeshippingzones';
        $this->tab           = 'shipping_logistics';
        $this->version       = '1.2.5';
        $this->author        = 'Línea Gráfica';
        $this->module_key    = '89ac4fd54e6cdb93595643fb986be768';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Free Shipping / Delivery by Zone, Carrier, Price and Weight');
        $this->description = $this->l('Set free shipping for different zones, carriers, price and weight interval.');
    }

    private function getCarriers()
    {
        $carriers = CarrierCore::getCarriers($this->context->language->id, true, false, false, null, ALL_CARRIERS);
        return $carriers;
    }

    private function getZones()
    {
        $zones = ZoneCore::getZones();
        return $zones;
    }

    private function getZoCaValue($id_zone, $id_carrier, $value, $id_shop = 0, $id_shop_group = 0)
    {
        if (!$id_shop) {
            $id_shop = $this->context->shop->id;
        } if ($id_shop_group) {
            $val = Db::getInstance()->getValue(
                'SELECT lg.'.pSQL($value).' '.
                'FROM '._DB_PREFIX_.'lgfreeshippingzones lg '.
                'INNER JOIN '._DB_PREFIX_.'carrier c '.
                'WHERE lg.id_carrier = c.id_reference '.
                'AND lg.id_zone = '.(int)$id_zone.' '.
                'AND c.id_carrier = '.(int)$id_carrier.' '.
                'AND lg.id_shop_group = '.(int)$id_shop_group
            );
        } else {
            $val = Db::getInstance()->getValue(
                'SELECT lg.'.pSQL($value).' '.
                'FROM '._DB_PREFIX_.'lgfreeshippingzones lg '.
                'INNER JOIN '._DB_PREFIX_.'carrier c '.
                'WHERE lg.id_carrier = c.id_reference '.
                'AND lg.id_zone = '.(int)$id_zone.' '.
                'AND c.id_carrier = '.(int)$id_carrier.' '.
                'AND lg.id_shop = '.(int)$id_shop
            );
        }
        return $val;
    }

    private function getZoneName($id_zone)
    {
        $name = Db::getInstance()->getValue(
            'SELECT name '.
            'FROM '._DB_PREFIX_.'zone '.
            'WHERE id_zone = '.(int)$id_zone
        );
        return $name;
    }

    public function install()
    {
        if (
            !parent::install()
            || !$this->registerHook('displayBeforeShoppingCartBlock')
        ) {
            return false;
        }
        $queries = array(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lgfreeshippingzones` (
              `id_zone` int(11) NOT NULL,
              `id_shop` int(11) NOT NULL,
              `id_shop_group` int(11) NOT NULL,
              `id_carrier` int(11) NOT NULL,
              `price` decimal(10,2) NOT NULL,
              `weight` decimal(10,3) NOT NULL,
              `price2` decimal(10,2) NOT NULL,
              `weight2` decimal(10,3) NOT NULL,
              KEY `id_zone` (`id_zone`),
              KEY `id_carrier` (`id_carrier`)
              ) ENGINE='.(defined('ENGINE_TYPE') ? ENGINE_TYPE : 'Innodb')
        );
        foreach ($queries as $query) {
            if (! Db::getInstance()->Execute($query)) {
                parent::uninstall();
                return false;
            }
        }
        $shops = Shop::getShops();
        foreach ($shops as $shop) {
            // init values
            $zonas = ZoneCore::getZones();
            foreach ($zonas as $zona) {
                $carriers = $this->getCarriers();
                foreach ($carriers as $carrier) {
                    Db::getInstance()->Execute(
                        'INSERT INTO '._DB_PREFIX_.'lgfreeshippingzones '.
                        'VALUES (
                            \''.$zona['id_zone'].'\',
                            \''.(int)$shop['id_shop'].'\',
                            \'0\',
                            \''.(int)$carrier['id_reference'].'\',
                            0,
                            0,
                            0,
                            0
                        )'
                    );
                }
            }
        }
        $shop_groups = ShopGroup::getShopGroups();
        foreach ($shop_groups as $shop_group) {
            $shop_g = (array)$shop_group;
            // init values
            $zonas = ZoneCore::getZones();
            foreach ($zonas as $zona) {
                $carriers = $this->getCarriers();
                foreach ($carriers as $carrier) {
                    Db::getInstance()->Execute(
                        'INSERT INTO '._DB_PREFIX_.'lgfreeshippingzones '.
                        'VALUES (
                            \''.$zona['id_zone'].'\',
                            \'0\',
                            \''.(int)$shop_g['id'].'\',
                            \''.$carrier['id_reference'].'\',
                            0,
                            0,
                            0,
                            0
                        )'
                    );
                }
            }
        }
        if (!Configuration::updateValue('PS_LGFREESHIPPINGZONES_TAX', '1')
            || !Configuration::updateValue('PS_LGFREESHIPPINGZONES_MESSAGE', '1')
            || !Configuration::updateValue('PS_LGFREESHIPPINGZONES_DEBUG', '0')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'lgfreeshippingzones`');
        return parent::uninstall();
    }

    private function formatBootstrap($text)
    {
        $text = str_replace('<fieldset>', '<div class="panel">', $text);
        $text = str_replace('</fieldset>', '</div>', $text);
        $text = str_replace('<legend>', '<h3>', $text);
        $text = str_replace('</legend>', '</h3>', $text);
        return $text;
    }

    private function getP()
    {
        $default_lang = $this->context->language->id;
        $lang         = Language::getIsoById($default_lang);
        $pl           = array('es','fr');
        if (!in_array($lang, $pl)) {
            $lang = 'en';
        }
        $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/publi/style.css');
        $base = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')  ?
            'https://'.$this->context->shop->domain_ssl :
            'http://'.$this->context->shop->domain);
        if (version_compare(_PS_VERSION_, '1.5.0', '>')) {
            $uri = $base.$this->context->shop->getBaseURI();
        } else {
            $uri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')  ?
                    'https://'._PS_SHOP_DOMAIN_SSL_DOMAIN_:
                    'http://'._PS_SHOP_DOMAIN_).__PS_BASE_URI__;
        }
        $path = _PS_MODULE_DIR_.$this->name
            .DIRECTORY_SEPARATOR.'views'
            .DIRECTORY_SEPARATOR.'publi'
            .DIRECTORY_SEPARATOR.$lang
            .DIRECTORY_SEPARATOR.'index.php';
        $object = Tools::file_get_contents($path);
        $object = str_replace('src="/modules/', 'src="'.$uri.'modules/', $object);

        return $object;
    }
    public function getContent()
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/bootstrap.js');
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/admin15.js');
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/admin15.css');
        }
        $shops = array();
        $id_group = false;
        $shop_context = $this->context->cookie->shopContext;
        $default_id_carrier = (int)(Configuration::get('PS_CARRIER_DEFAULT'));
        $default_carrier = Db::getInstance()->getValue(
            'SELECT name '.
            'FROM '._DB_PREFIX_.'carrier '.
            'WHERE id_carrier = '.(int)$default_id_carrier
        );
        if ($default_id_carrier < 1) {
            $default_carrier = $this->l('undefined');
        }
        $default_id_country = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
        $default_zone = Db::getInstance()->getValue(
            'SELECT z.name '.
            'FROM '._DB_PREFIX_.'zone z '.
            'INNER JOIN '._DB_PREFIX_.'country c '.
            'WHERE z.id_zone = c.id_zone '.
            'AND c.id_country = '.(int)$default_id_country
        );
        if ($default_id_country < 1) {
            $default_zone = $this->l('undefined');
        }
        if (strpos($shop_context, 's-') !== false) {
            $shops[] = $id_shop = (int)Tools::str_replace_once('s-', '', $shop_context);
        } if (strpos($shop_context, 'g-') !== false) {
            $id_group = (int)Tools::str_replace_once('g-', '', $shop_context);
            $id_shop = false;
            $groups_shops = ShopGroup::getShopsFromGroup((int)$id_group);
            foreach ($groups_shops as $shop) {
                $shops[] = $shop['id_shop'];
            }
        } if (empty($shops)) {
            $id_shop = false;
            $shops_groups = Shop::getShops();
            foreach ($shops_groups as $shop) {
                $shops[] = $shop['id_shop'];
            }
        }
        $zonas = $this->getZones();
        $this->_html = $this->getP().
        '<h2>'.$this->displayName.'</h2><br>';
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $this->_html .= '
            <div class="info">
            '.$this->l('PS 1.5: To display the message and debug in the cart, please go to your FTP,').'&nbsp;'.
            $this->l('edit the file /themes/your_theme/shopping-cart.tpl, find the line "table id="cart_summary""').
            '&nbsp;'.$this->l('and add this before it: {hook h="displayBeforeShoppingCartBlock"}').'
            </div>';
        } if (!file_exists(_PS_ROOT_DIR_.'/override/classes/Cart.php')) {
            $this->_html .= $this->displayError(
                $this->l('The Cart.php override is missing.').'&nbsp;'.
                $this->l('Please reset the module or copy the override manually on your FTP.')
            );
        } if ((int)Configuration::get('PS_DISABLE_OVERRIDES') > 0) {
            $this->_html .= $this->displayError(
                $this->l('The overrides are currently disabled on your store. Please change the configuration')
                .' <a href="index.php?tab=AdminPerformance&token='.Tools::getAdminTokenLite('AdminPerformance')
                .'" target="_blank">'.$this->l('here').'</a>'
            );
        } if ((int)Configuration::get('PS_DISABLE_NON_NATIVE_MODULE') > 0) {
            $this->_html .= $this->displayError(
                $this->l('Non PrestaShop modules are currently disabled on your store. Please change the configuration')
                .' <a href="index.php?tab=AdminPerformance&token='.Tools::getAdminTokenLite('AdminPerformance')
                .'" target="_blank">'.$this->l('here').'</a>'
            );
        } if ((int)Configuration::get('PS_SHIPPING_HANDLING') > 0) {
            $this->_html .= $this->displayError(
                $this->l('Your handling charges are not set at 0. Please change the configuration')
                .' <a href="index.php?tab=AdminShipping&token='.Tools::getAdminTokenLite('AdminShipping')
                .'" target="_blank">'.$this->l('here').'</a>'
            );
        }  if ((int)Configuration::get('PS_LGFREESHIPPINGZONES_DEBUG') > 0) {
            $this->_html .= $this->displayError(
                $this->l('The debug mode is enabled in the shopping cart').'&nbsp;'.
                $this->l('do not forget to disable it when you have finished using it.')
            );
        } if ((int)$default_id_carrier < 1) {
            $this->_html .= $this->displayError(
                $this->l('You have not selected a default carrier').'&nbsp;'.
                $this->l('(free shipping will not be visible if users are not logged in).').'&nbsp;'.
                $this->l('Please change the configuration')
                .' <a href="index.php?tab=AdminShipping&token='.Tools::getAdminTokenLite('AdminShipping')
                .'" target="_blank">'.$this->l('here').'</a>'
            );
        } if ((int)$default_id_country < 1) {
            $this->_html .= $this->displayError(
                $this->l('You have not selected a default country').'&nbsp;'.
                $this->l('(free shipping will not be visible if users are not logged in).').'&nbsp;'.
                $this->l('Please change the configuration')
                .' <a href="index.php?tab=AdminLocalization&token='.Tools::getAdminTokenLite('AdminLocalization')
                .'" target="_blank">'.$this->l('here').'</a>'
            );
        } if (Tools::isSubmit('updateConf')) {
            foreach ($shops as $shop) {
                foreach ($zonas as $zona) {
                    $id_zone = $zona['id_zone'];
                    $carriers = $this->getCarriers();
                    foreach ($carriers as $carrier) {
                        $id_carrier = $carrier['id_reference'];
                        Db::getInstance()->Execute(
                            'UPDATE '._DB_PREFIX_.'lgfreeshippingzones '.
                            'SET '.
                            'price = \''.(float)Tools::getValue('price_'.$id_zone.'_'.$id_carrier).'\', '.
                            'weight = \''.(float)Tools::getValue('weight_'.$id_zone.'_'.$id_carrier).'\', '.
                            'price2 = \''.(float)Tools::getValue('price2_'.$id_zone.'_'.$id_carrier).'\', '.
                            'weight2 = \''.(float)Tools::getValue('weight2_'.$id_zone.'_'.$id_carrier).'\' '.
                            'WHERE id_zone = '.$zona['id_zone'].' '.
                            'AND id_shop = '.(int)$shop.' '.
                            'AND id_carrier = '.(int)$id_carrier
                        );
                    }
                }
            }
            if ($id_group) {
                foreach ($zonas as $zona) {
                    $id_zone = $zona['id_zone'];
                    $carriers = $this->getCarriers();
                    foreach ($carriers as $carrier) {
                        $id_carrier = $carrier['id_reference'];
                        Db::getInstance()->Execute(
                            'UPDATE '._DB_PREFIX_.'lgfreeshippingzones '.
                            'SET '.
                            'price = \''.(float)Tools::getValue('price_'.$id_zone.'_'.$id_carrier).'\', '.
                            'weight = \''.(float)Tools::getValue('weight_'.$id_zone.'_'.$id_carrier).'\', '.
                            'price2 = \''.(float)Tools::getValue('price2_'.$id_zone.'_'.$id_carrier).'\', '.
                            'weight2 = \''.(float)Tools::getValue('weight2_'.$id_zone.'_'.$id_carrier).'\' '.
                            'WHERE id_zone = '.$zona['id_zone'].' '.
                            'AND id_shop_group = '.(int)$id_group.' '.
                            'AND id_carrier = '.(int)$id_carrier
                        );
                    }
                }
            }
            Configuration::updateValue('PS_LGFREESHIPPINGZONES_TAX', Tools::getValue('price_tax'));
            Configuration::updateValue('PS_LGFREESHIPPINGZONES_MESSAGE', Tools::getValue('shipping_message'));
            Configuration::updateValue('PS_LGFREESHIPPINGZONES_DEBUG', Tools::getValue('debug_mode'));
            $this->_html .= Module::DisplayConfirmation($this->l('Configuration updated'));
        }
        if (substr_count(_PS_VERSION_, '1.6') > 0) {
                    $class_alert = "alert alert-info";
                    $shipping_tab = "AdminShipping";
        } elseif (substr_count(_PS_VERSION_, '1.5') > 0) {
                    $class_alert = "info";
                    $shipping_tab = "AdminCarriers";
        }
        $this->_html .= '
        <fieldset>
            <form name="zonesform" method="post" action="'.$_SERVER['REQUEST_URI'].'">
            <legend>
                '.$this->l('Configuration').
                '&nbsp;
                <a href="../modules/'.$this->name.'/readme/readme_'.$this->l('en').'.pdf#page=4" target="_blank">
                    <img src="../modules/'.$this->name.'/views/img/info.png">
                </a>
            </legend>
            <br>
            <div style="overflow-x: auto;">
            <table class="table">
                <tr>
                    <td>
                        <h3 style="margin:0 0 10px 0; line-height:1em;">
                            <label>'.$this->l('When users are not logged in:').'</label>
                        </h3>
                    </td>
                    <td>
                        <h3 style="margin:0 0 10px 0; line-height:1em;">
                            <label>'.$this->l('Price with tax included:').'</label>
                        </h3>
                    </td>
                    <td>
                        <h3 style="margin:0 0 10px 0; line-height:1em;">
                            <label>'.$this->l('Display message in cart:').'</label>
                        </h3>
                    </td>
                    <td>
                        <h3 style="margin:0 0 10px 0; line-height:1em;">
                            <label>'.$this->l('Display debug in cart:').'</label>
                        </h3>
                    </td>
                    <td>
                        <h3 style="margin:0 0 10px 0; line-height:1em;">
                            <label>'.$this->l('A problem with the module?').'</label>
                        </h3>
                    </td>
                    <td rowspan="3">
                        <button class="button btn btn-default" type="submit" name="updateConf" >
                            <i class="process-icon-save"></i>'.$this->l('Save').'
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>
                            <i class="icon-map-marker"></i>
                            &nbsp;'.$this->l('Default zone:').'&nbsp;'.$default_zone.'&nbsp;
                            (<a href="index.php?controller=AdminLocalization&token='.
                            Tools::getAdminTokenLite('AdminLocalization').
                            '" target="_blank">
                                '.$this->l('modify').'
                            </a>)
                        </span>
                    </td>
                    <td rowspan="2">
                        <span class="switch prestashop-switch fixed-width-lg" style="float:left; margin-left:10px;">
                        <input type="radio" name="price_tax" id="price_tax_on" value="1"
                        '.(Configuration::get('PS_LGFREESHIPPINGZONES_TAX') == 1 ? 'checked="checked"' : '').' />
                        <label for="price_tax_on" style="width:75px;">'.$this->l('Yes').'</label>
                        <input type="radio" name="price_tax" id="price_tax_off" value="0"
                        '.(Configuration::get('PS_LGFREESHIPPINGZONES_TAX') == 0 ? 'checked="checked"' : '').' />
                        <label for="price_tax_off" style="width:75px;">'.$this->l('No').'</label>
                        <a class="slide-button btn"></a>
                        </span>
                    </td>
                    <td rowspan="2">
                        <span class="switch prestashop-switch fixed-width-lg" style="float:left; margin-left:10px;">
                        <input type="radio" name="shipping_message" id="shipping_message_on" value="1"
                        '.(Configuration::get('PS_LGFREESHIPPINGZONES_MESSAGE') == 1 ? 'checked="checked"' : '').' />
                        <label for="shipping_message_on" style="width:75px;">'.$this->l('Yes').'</label>
                        <input type="radio" name="shipping_message" id="shipping_message_off" value="0"
                        '.(Configuration::get('PS_LGFREESHIPPINGZONES_MESSAGE') == 0 ? 'checked="checked"' : '').' />
                        <label for="shipping_message_off" style="width:75px;">'.$this->l('No').'</label>
                        <a class="slide-button btn"></a>
                        </span>
                    </td>
                    <td rowspan="2">
                        <span class="switch prestashop-switch fixed-width-lg" style="float:left; margin-left:10px;">
                        <input type="radio" name="debug_mode" id="debug_mode_on" value="1"
                        '.(Configuration::get('PS_LGFREESHIPPINGZONES_DEBUG') == 1 ? 'checked="checked"' : '').' />
                        <label for="debug_mode_on" style="width:75px;">'.$this->l('Yes').'</label>
                        <input type="radio" name="debug_mode" id="debug_mode_off" value="0"
                        '.(Configuration::get('PS_LGFREESHIPPINGZONES_DEBUG') == 0 ? 'checked="checked"' : '').' />
                        <label for="debug_mode_off" style="width:75px;">'.$this->l('No').'</label>
                        <a class="slide-button btn"></a>
                        </span>
                    </td>
                    <td rowspan="2">
                        <i class="icon-info-circle"></i>&nbsp;
                        <a href="../modules/'.$this->name.'/readme/readme_'.$this->l('en').'.pdf#page=7"
                        target="_blank">
                        '.$this->l('Read the module FAQ').'
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>
                            <i class="icon-truck"></i>
                            &nbsp;'.$this->l('Default carrier:').'&nbsp;'.$default_carrier.'&nbsp;
                            (<a href="index.php?controller='.$shipping_tab.'&token='.
                            Tools::getAdminTokenLite(''.$shipping_tab.'').
                            '" target="_blank">
                                '.$this->l('modify').'
                            </a>)
                        </span>
                    </td>
                </tr>
            </table>
            </div>
            <br>
            <div class="'.$class_alert.'">
                '.$this->l('Free shipping is disabled for the zones and carriers').'&nbsp;'.
                $this->l('that have 0 set for the 4 values (0.00 | 0.00 | 0.000 | 0.000)').'
                <br>
                '.$this->l('To set free shipping, you need to configure at least one value').'&nbsp;'.
                $this->l('for the zone and carrier (not necessary to configure the 4 values)').'
                <br>
                '.$this->l('Ex: if you set "Minimum price : 50.00" and "Maximum price : 0.00",').'&nbsp;'.
                $this->l('shipping will be free from 50 to infinity.').'
                <br>
                '.$this->l('Ex: if you set "Minimum price : 0.00" and "Maximum price : 300.00",').'&nbsp;'.
                $this->l('shipping will be free from 0 to 300.').'
            </div>
            <br>
            <div style="overflow-x:auto;">
            <table class="table" width="100%">
            <tr>
                <th style="text-transform: uppercase;">
                    <i class="icon-map-marker"></i>&nbsp;'.$this->l('Zone').'
                </th>
                <th style="text-transform: uppercase;">
                    <i class="icon-truck"></i>&nbsp;'.$this->l('Carrier').'
                </th>
                <th style="text-transform: uppercase;">
                    <i class="icon-chevron-right"></i>&nbsp;'.$this->l('Minimum price').'
                </th>
                <th style="text-transform: uppercase;">
                    <i class="icon-chevron-left"></i>&nbsp;'.$this->l('Maximum price').'
                </th>
                <th style="text-transform: uppercase;">
                    <i class="icon-chevron-right"></i>&nbsp;'.$this->l('Minimum weight').'
                </th>
                <th style="text-transform: uppercase;">
                    <i class="icon-chevron-left"></i>&nbsp;'.$this->l('Maximun weight').'
                </th>
            </tr>';
        foreach ($zonas as $zona) {
            $id_zone = $zona['id_zone'];
            $carriers = $this->getCarriers();
            foreach ($carriers as $carrier) {
                $id_carrier = $carrier['id_carrier'];
                $this->_html .= '
            <tr>
                <td>
                    '.$this->getZoneName($zona['id_zone']).'
                </td>
                <td>
                    '.$carrier['name'].'
                </td>
                <td>
                    <input type="text" name="price_'.$zona['id_zone'].'_'.$carrier['id_reference'].'"
                    value="'.$this->getZoCaValue($id_zone, $id_carrier, 'price', $id_shop, $id_group).'">
                </td>
                <td>
                    <input type="text" name="price2_'.$zona['id_zone'].'_'.$carrier['id_reference'].'"
                    value="'.$this->getZoCaValue($id_zone, $id_carrier, 'price2', $id_shop, $id_group).'">
                </td>
                <td>
                    <input type="text" name="weight_'.$zona['id_zone'].'_'.$carrier['id_reference'].'"
                    value="'.$this->getZoCaValue($id_zone, $id_carrier, 'weight', $id_shop, $id_group).'">
                </td>
                <td>
                    <input type="text" name="weight2_'.$zona['id_zone'].'_'.$carrier['id_reference'].'"
                    value="'.$this->getZoCaValue($id_zone, $id_carrier, 'weight2', $id_shop, $id_group).'">
                </td>
            </tr>';
            }
        }
        $this->_html .= '
            <tr>
                <td colspan="4">
                    <button class="button btn btn-default" type="submit" name="updateConf" >
                        <i class="process-icon-save"></i>'.$this->l('Save').'
                    </button>
                </td>
            </tr>
        </table>
        </div>
        </form>
        </fieldset>';
        if ($this->bootstrap == true) {
            $this->_html = $this->formatBootstrap($this->_html);
        }
        return $this->_html;
    }

    public function FSCheck($id_zone, $price, $weight, $id_carrier, $id_shop)
    {
        foreach ($this->getZones() as $zona) {
            if ($zona['id_zone'] == $id_zone) {
                foreach ($this->getCarriers() as $carrier) {
                    if ($id_carrier == $carrier['id_carrier']) {
                        $price1 = $this->getZoCaValue($zona['id_zone'], $carrier['id_carrier'], 'price', $id_shop);
                        $price2 = $this->getZoCaValue($zona['id_zone'], $carrier['id_carrier'], 'price2', $id_shop);
                        $weight1 = $this->getZoCaValue($zona['id_zone'], $carrier['id_carrier'], 'weight', $id_shop);
                        $weight2 = $this->getZoCaValue($zona['id_zone'], $carrier['id_carrier'], 'weight2', $id_shop);
                        // 15 different possibilities
                        if ($price1 > 0 and $price2 > 0 and $weight1 > 0 and $weight2 > 0) {
                            if (
                                ($price >= $price1 or $weight >= $weight1)
                                and ($price <= $price2 and $weight <= $weight2)
                            ) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 > 0 and $weight1 > 0 and $weight2 == 0) {
                            if (($price >= $price1 or $weight >= $weight1) and $price <= $price2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 > 0 and $weight1 == 0 and $weight2 > 0) {
                            if ($price >= $price1 and ($price <= $price2 and $weight <= $weight2)) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 > 0 and $weight1 == 0 and $weight2 == 0) {
                            if ($price >= $price1 and $price <= $price2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 == 0 and $weight1 > 0 and $weight2 > 0) {
                            if (($price >= $price1 or $weight >= $weight1) and $weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 == 0 and $weight1 > 0 and $weight2 == 0) {
                            if ($price >= $price1 or $weight >= $weight1) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 == 0 and $weight1 == 0 and $weight2 > 0) {
                            if ($price >= $price1 and $weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 == 0 and $weight1 == 0 and $weight2 == 0) {
                            if ($price >= $price1) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 > 0 and $weight1 > 0 and $weight2 > 0) {
                            if ($weight >= $weight1 and ($price <= $price2 and $weight <= $weight2)) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 > 0 and $weight1 > 0 and $weight2 == 0) {
                            if ($weight >= $weight1 and $price <= $price2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 > 0 and $weight1 == 0 and $weight2 > 0) {
                            if ($price <= $price2 and $weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 > 0 and $weight1 == 0 and $weight2 == 0) {
                            if ($price <= $price2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 == 0 and $weight1 > 0 and $weight2 > 0) {
                            if ($weight >= $weight1 and $weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 == 0 and $weight1 > 0 and $weight2 == 0) {
                            if ($weight >= $weight1) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 == 0 and $weight1 == 0 and $weight2 > 0) {
                            if ($weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    public function hookDisplayBeforeShoppingCartBlock($params)
    {
        $taxCalculationMethod = Group::getPriceDisplayMethod((int)Group::getCurrent()->id);
        $useTax = !($taxCalculationMethod == PS_TAX_EXC);
        $products = $params['cart']->getProducts(true);
        $nbTotalProducts = 0;
        foreach ($products as $product) {
            $nbTotalProducts += (int)$product['cart_quantity'];
        }
        $base_shipping = $params['cart']->getOrderTotal($useTax, Cart::ONLY_SHIPPING);
        $id_carrier = (int)$this->context->cart->id_carrier;
        if ($id_carrier == 0) {
            $id_carrier = (int)(Configuration::get('PS_CARRIER_DEFAULT'));
        }
        $id_address = (int)$this->context->cart->id_address_delivery;
        if ($id_address) {
            $id_zone = Address::getZoneById((int)$id_address);
        } else {
            $id_country = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
            $id_zone = CountryCore::getIdZone((int)$id_country);
        }
        $name_zone = Db::getInstance()->getValue(
            'SELECT name FROM '._DB_PREFIX_.'zone WHERE id_zone = '.(int)$id_zone
        );
        $id_shop = (int)$this->context->shop->id;
        $carrier = Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'carrier WHERE id_carrier = '.(int)$id_carrier
        );
        $value = Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'lgfreeshippingzones '.
            'WHERE id_zone = '.(int)$id_zone.' '.
            'AND id_carrier = '.(int)$carrier['id_reference'].' '.
            'AND id_shop = '.(int)$id_shop
        );
        $weight_unit = Db::getInstance()->getValue(
            'SELECT value FROM '._DB_PREFIX_.'configuration WHERE name = "PS_WEIGHT_UNIT"'
        );
        $price_tax = Configuration::get('PS_LGFREESHIPPINGZONES_TAX');
        if ($price_tax == 1) {
            $tax_status = true;
        } if ($price_tax == 0) {
            $tax_status = false;
        }
        $debug_status = Configuration::get('PS_LGFREESHIPPINGZONES_DEBUG');
        $left_message = Configuration::get('PS_LGFREESHIPPINGZONES_MESSAGE');
        if (substr_count(_PS_VERSION_, '1.6') > 0) {
            $prestashop_version = 16;
        } else {
            $prestashop_version = 15;
        }
        $this->context->smarty->assign(array(
            'shipping_cost' => $base_shipping,
            'price1' => $value['price'],
            'price2' => $value['price2'],
            'weight1' => $value['weight'],
            'weight2' => $value['weight2'],
            'id_carrier' => $id_carrier,
            'ref_carrier' => $carrier['id_reference'],
            'name_carrier' => $carrier['name'],
            'is_free' => $carrier['is_free'],
            'id_zone' => $id_zone,
            'name_zone' => $name_zone,
            'id_shop' => $id_shop,
            'weight_unit' => $weight_unit,
            'price_tax' => $price_tax,
            'tax_status' => $tax_status,
            'debug_status' => $debug_status,
            'left_message' => $left_message,
            'prestashop_version' => $prestashop_version,
        ));
        return ($this->display(__FILE__, '/views/templates/front/shoppingCart.tpl'));
    }
}
