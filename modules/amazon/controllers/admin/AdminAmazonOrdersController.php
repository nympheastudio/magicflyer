<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
*/

require_once(dirname(__FILE__).'/../../amazon.php');
require_once(dirname(__FILE__).'/../../classes/amazon.tools.class.php');

class AdminAmazonOrdersController extends ModuleAdminController
{
    public $module = 'amazon';
    public $name   = 'amazon';
    public $amazon = null;
    public $url;

    public $ps17x = false;
    public $ps16x = false;

    public function __construct()
    {
        $this->amazon = new Amazon();

        $this->className = $this->amazon->name;
        $this->display = 'edit';

        $this->id_lang = (int)Context::getContext()->language->id;

        $this->lang = true;
        $this->deleted = false;
        $this->colorOnBackground = false;

        $this->url = $this->amazon->url;
        $this->images = $this->amazon->images;
        $this->path = $this->amazon->path;

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->ps17x = true;
            $this->ps16x = true;
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->ps16x = true;
        } else {
            $this->ps16x = false;
        }

        $this->context = Context::getContext();
        $this->bootstrap = true;

        parent::__construct();
    }

    public function renderForm()
    {
        $html = null;
        $html .= $this->tabHeader();
        $html .= $this->languageSelector();

        if ($this->ps17x) {
            $this->addCSS($this->url.'views/css/admin_controller/general16.css', 'screen');
            $this->addCSS($this->url.'views/css/OrdersAmazon16.css', 'screen');
            $this->addCSS($this->url.'views/css/OrdersAmazon17.css', 'screen');
        } elseif ($this->ps16x) {
            $this->addCSS($this->url.'views/css/admin_controller/general16.css', 'screen');
            $this->addCSS($this->url.'views/css/OrdersAmazon16.css', 'screen');
        } else {
            $this->addCSS($this->url.'views/css/admin_controller/general.css', 'screen');
            $this->addCSS($this->url.'views/css/OrdersAmazon.css', 'screen');
        }

        $this->addJS($this->url.'views/js/orders.js');
        $this->addJS($this->url.'views/js/reports.js');

        $this->context->smarty->assign('path', $this->url);
        $this->context->smarty->assign('images', $this->images);
        $this->context->smarty->assign('debug', (bool)Configuration::get('AMAZON_DEBUG_MODE'));
        $this->context->smarty->assign('selected_tab', 'import');
        $this->context->smarty->assign('ps16x', $this->ps16x);
        $this->context->smarty->assign('report_url', $this->url.'functions/products_report.php');

        $alert_class = array();
        $alert_class['danger'] = $this->ps16x ? 'alert alert-danger' : 'error';
        $alert_class['warning'] = $this->ps16x ? 'alert alert-warning' : 'warn';
        $alert_class['success'] = $this->ps16x ? 'alert alert-success' : 'conf';
        $alert_class['info'] = $this->ps16x ? 'alert alert-info' : 'info';

        $this->context->smarty->assign('alert_class', $alert_class);

        $html .= $this->context->smarty->fetch($this->path.'views/templates/admin/AdminOrdersAmazon.tpl');

        return $html.$this->content.parent::renderForm();
    }

    public function tabHeader()
    {
        $this->context->smarty->assign('images', $this->images);

        $amazonTokens = AmazonConfiguration::get('CRON_TOKEN');

        $tokenOrders = Tools::getAdminToken('AdminOrders'.(int)Tab::getIdFromClassName('AdminOrders').(int)$this->context->employee->id);

        $day = 86400;
        $days = 2;
        $startDate = date('Y-m-d', time() - ($day * $days));
        $currentDate = date('Y-m-d');

        $this->addJqueryUI('ui.datepicker');

        if (version_compare(_PS_VERSION_, '1.5', '>=') && Shop::isFeatureActive() && in_array($this->context->shop->getContext(), array(
                    Shop::CONTEXT_GROUP,
                    Shop::CONTEXT_ALL
                ))
        ) {
            $this->context->smarty->assign('shop_warning', $this->l('You are in multishop environment. To use Amazon module, you must select a target shop.'));
        }
        $amazon_features = Amazon::getAmazonFeatures();

        $this->context->smarty->assign('context_key', AmazonContext::getKey($this->context->shop));
        $this->context->smarty->assign('debug', (bool)Configuration::get('AMAZON_DEBUG_MODE'));
        // Aug-23-2018: Remove Carriers/Modules option
        $this->context->smarty->assign('experimental', Amazon::ENABLE_EXPERIMENTAL_FEATURES);
        $this->context->smarty->assign('widget', isset($amazon_features['module']) && ($amazon_features['module'] == 'amazon'));
        $this->context->smarty->assign('tokens', $amazonTokens);
        $this->context->smarty->assign('token_order', $tokenOrders);
        $this->context->smarty->assign('module_path', $this->path);
        $this->context->smarty->assign('tpl_path', $this->path);
        $this->context->smarty->assign('orders_url', $this->url.'functions/orders.php');
        $this->context->smarty->assign('import_url', $this->url.'functions/import.php');
        $this->context->smarty->assign('orders_report_url', $this->url.'functions/orders_reports.php');
        $this->context->smarty->assign('img_loader', $this->images.'loading.gif');
        $this->context->smarty->assign('img_loader_small', $this->images.'small-loader.gif');
        $this->context->smarty->assign('current_date', $currentDate);
        $this->context->smarty->assign('fba', (bool)$amazon_features['fba']);
        $this->context->smarty->assign('start_date', $startDate);
        $this->context->smarty->assign('psIs16', $this->ps16x);
        $this->context->smarty->assign('instant_token', Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0));

        $alert_class = array();
        $alert_class['danger'] = $this->ps16x ? 'alert alert-danger' : 'error';
        $alert_class['warning'] = $this->ps16x ? 'alert alert-warning' : 'warn';
        $alert_class['success'] = $this->ps16x ? 'alert alert-success' : 'conf';
        $alert_class['info'] = $this->ps16x ? 'alert alert-info' : 'info';

        $this->context->smarty->assign('alert_class', $alert_class);
        $this->context->smarty->assign('id_lang', $this->id_lang);

        $amazon_support = new AmazonSupport();

        $documentation = $amazon_support->gethreflink();

        $this->context->smarty->assign('documentation', $documentation);
        $this->context->smarty->assign('support', $amazon_support->gethreflink(AmazonSupport::TUTORIAL_GET_SUPPORT));
        $this->context->smarty->assign('widget', $amazon_support->getWidget($this->amazon->name, $this->amazon->displayName, $this->amazon->version));

        return ($this->context->smarty->fetch($this->path.'views/templates/admin/items/orders_header.tpl'));
    }

    public function languageSelector()
    {
        $html = null;
        $master = AmazonConfiguration::get('MASTER');
        $amazon_features = Amazon::getAmazonFeatures();
        $europe = $amazon_features['amazon_europe'] && !empty($master);

        $actives = AmazonConfiguration::get('ACTIVE');
        $regions = AmazonConfiguration::get('REGION');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        $this->addCSS($this->url.'/views/css/country_selector.css', 'screen');

        $marketplaces = array();

        if ($europe) {
            $marketplacesEuro = array();
            $marketplacesNotEuro = array();

            if (is_array($actives)) {
                foreach (AmazonTools::languages() as $language) {
                    $id_lang = $language['id_lang'];

                    if (!isset($actives[$id_lang]) || !$actives[$id_lang]) {
                        continue;
                    }

                    if (!isset($regions[$id_lang]) || empty($regions[$id_lang])) {
                        continue;
                    }

                    if (!isset($marketPlaceIds[$id_lang])) {
                        continue;
                    }

                    if (AmazonTools::isEuropeMarketplaceId($marketPlaceIds[$id_lang]) && AmazonTools::isEuroMarketplaceId($marketPlaceIds[$id_lang])) {
                        // Euro Zone Area
                        //
                        $marketplacesEuro[$id_lang] = array();
                        $marketplacesEuro[$id_lang]['name'] = sprintf('www.amazon.%s', AmazonTools::idToDomain($id_lang));
                        $marketplacesEuro[$id_lang]['region'] = $regions[$id_lang];
                        $marketplacesEuro[$id_lang]['id_lang'] = $id_lang;
                        $marketplacesEuro[$id_lang]['lang'] = $language['iso_code'];
                        $marketplacesEuro[$id_lang]['image'] = $this->images.'geo_flags_web2/flag_'.$regions[$id_lang].'_64px.png';
                    } else {
                        // Outside Euro Zone Area
                        //
                        $marketplacesNotEuro[$id_lang] = array();
                        $marketplacesNotEuro[$id_lang]['name'] = sprintf('www.amazon.%s', AmazonTools::idToDomain($id_lang));
                        $marketplacesNotEuro[$id_lang]['region'] = $regions[$id_lang];
                        $marketplacesNotEuro[$id_lang]['id_lang'] = $id_lang;
                        $marketplacesNotEuro[$id_lang]['lang'] = $language['iso_code'];
                        $marketplacesNotEuro[$id_lang]['image'] = $this->images.'geo_flags_web2/flag_'.$regions[$id_lang].'_64px.png';
                    }
                }
            }

            $europeEuroArea = is_array($marketplacesEuro) && count($marketplacesEuro);
            $europeNotEuroArea = is_array($marketplacesNotEuro) && count($marketplacesNotEuro);
            $showCountrySelector = is_array($marketplacesEuro) && is_array($marketplacesNotEuro)
                && ((count($marketplacesEuro) + count($marketplacesNotEuro)) > 1);

            $this->context->smarty->assign('images', $this->images);
            $this->context->smarty->assign('europeEuroArea', $europeEuroArea);
            $this->context->smarty->assign('europeNotEuroArea', $europeNotEuroArea);
            $this->context->smarty->assign('europe_flag', $this->images.'geo_flags_web2/flag_eu_64px.png');
            $this->context->smarty->assign('marketplacesEuro', $marketplacesEuro);
            $this->context->smarty->assign('marketplacesNotEuro', $marketplacesNotEuro);
            $this->context->smarty->assign('psIs16', $this->ps16x);
            $this->context->smarty->assign('show_country_selector', $showCountrySelector);

            return ($this->context->smarty->fetch($this->path.'views/templates/admin/items/europe_selector.tpl'));
        } else {
            if (is_array($actives)) {
                foreach (AmazonTools::languages() as $language) {
                    $id_lang = $language['id_lang'];

                    if (!isset($actives[$id_lang]) || !$actives[$id_lang]) {
                        continue;
                    }

                    if (!isset($regions[$id_lang]) || empty($regions[$id_lang])) {
                        continue;
                    }

                    $marketplaces[$id_lang] = array();
                    $marketplaces[$id_lang]['name'] = sprintf('www.amazon.%s', AmazonTools::idToDomain($id_lang));
                    $marketplaces[$id_lang]['region'] = $regions[$id_lang];
                    $marketplaces[$id_lang]['id_lang'] = $id_lang;
                    $marketplaces[$id_lang]['lang'] = $language['iso_code'];
                    $marketplaces[$id_lang]['image'] = $this->images.'geo_flags_web2/flag_'.$regions[$id_lang].'_64px.png';
                }
            }
            $this->context->smarty->assign('images', $this->images);
            $this->context->smarty->assign('marketplaces', $marketplaces);
            $this->context->smarty->assign('psIs16', $this->ps16x);
            $this->context->smarty->assign('show_country_selector', count($marketplaces) > 1 ? true : false);

            return ($this->context->smarty->fetch($this->path.'views/templates/admin/items/country_selector.tpl'));
        }
    }
}
