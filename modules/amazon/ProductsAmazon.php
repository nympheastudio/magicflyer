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

if (version_compare(_PS_VERSION_, '1.5', '<') && defined('PS_ADMIN_DIR') && file_exists(PS_ADMIN_DIR.'/../classes/AdminTab.php')) {
    include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
}

require_once(dirname(__FILE__).'/amazon.php');
require_once(dirname(__FILE__).'/classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/classes/amazon.support.class.php');

class ProductsAmazon extends AdminTab
{
    public $name = 'amazon';

    public $id_lang;

    public function __construct()
    {
        $this->context = Context::getContext();

        $this->id_lang = (int)$this->context->language->id;

        $this->url = __PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/'.$this->name.'/';
        $this->images = $this->url.'views/img/';

        $this->path = str_replace('\\', '/', dirname(__FILE__)).'/';
        $this->ps16x = false;

        parent::__construct();
    }

    public function l($string, $class = false, $addslashes = false, $htmlentities = true)
    {
        return (parent::l($string, __CLASS__, $addslashes, $htmlentities));
    }

    public function display()
    {
        $smarty = &$this->context->smarty;

        $amazon_features = Amazon::getAmazonFeatures();

        $smarty->assign('ps16x', false);
        $smarty->assign('support', null);
        $smarty->assign('path', $this->url);
        $smarty->assign('experimental', Amazon::ENABLE_EXPERIMENTAL_FEATURES);
        $smarty->assign('instant_token', Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0));
        $smarty->assign('images', $this->images);
        $smarty->assign('ps17x', false);
        $smarty->assign('import_url', null);
        $smarty->assign('repricing', null);
        $smarty->assign('debug', (bool)Configuration::get('AMAZON_DEBUG_MODE'));
        $smarty->assign('selected_tab', 'synchronize');
        $smarty->assign('creation', (bool)$amazon_features['creation']);
        $smarty->assign('expert_mode', (bool)$amazon_features['expert_mode']);
        $smarty->assign('psIs16', false);
        $smarty->assign('matching_box', $this->path.'views/templates/admin/items/matching_box.tpl');
        $smarty->assign('wizard_enabled', (bool)$amazon_features['wizard']);
        $smarty->assign('tpl_path', $this->path);

        $alert_class = array();
        $alert_class['danger'] = $this->ps16x ? 'alert alert-danger' : '';
        $alert_class['warning'] = $this->ps16x ? 'alert alert-warning' : '';
        $alert_class['success'] = $this->ps16x ? 'alert alert-success' : '';
        $alert_class['info'] = $this->ps16x ? 'alert alert-info' : '';
        $smarty->assign('alert_class', $alert_class);

        $this->addCSS($this->url.'views/css/admin_controller/general.css', 'screen');
        $this->addCSS($this->url.'views/css/ProductsAmazon.css', 'screen');
        $this->addCSS($this->url.'views/css/ProductsAmazon.compat.css', 'screen');

        $this->addJS($this->url.'views/js/products.js');
        $this->addJS($this->url.'views/js/reports.js');
        $this->addJS($this->url.'views/js/automaton.js');

        $html = null;
        $html .= $this->tabHeader();
        $html .= $this->languageSelector();
        $html .= $smarty->fetch($this->path.'views/templates/admin/AdminCatalogAmazon.tpl');

        echo $html;

        return;
    }

    public function addCSS($css)
    {
        echo html_entity_decode('&lt;link type="text/css" rel="stylesheet" href="' . $css . '" /&gt;');

        return;
    }

    public function addJS($js)
    {
        echo html_entity_decode('&lt;script type="text/javascript" src="' . $js . '"&gt;&lt;/script&gt;');

        return;
    }

    public function tabHeader()
    {
        $smarty = &$this->context->smarty;

        $smarty->assign('images', $this->images);

        $amazonTokens = AmazonConfiguration::get('CRON_TOKEN');

        $smarty->assign('context_key', null);
        $smarty->assign('tokens', $amazonTokens);
        $smarty->assign('path', $this->url);
        $smarty->assign('debug', (bool)Configuration::get('AMAZON_DEBUG_MODE'));
        $smarty->assign('update_url', $this->url.'functions/products.php');
        $smarty->assign('automaton_url', $this->url.'functions/automaton.php');
        $smarty->assign('report_url', $this->url.'functions/products_report.php');
        $smarty->assign('img_loader', $this->images.'loading.gif');
        $smarty->assign('current_date', date('Y-m-d H:i:s'));
        $smarty->assign('id_lang', $this->id_lang);

        $documentation = AmazonSupport::gethreflink();

        $smarty->assign('documentation', $documentation);


        return ($smarty->fetch($this->path.'views/templates/admin/items/catalog_header.tpl'));
    }

    public function languageSelector()
    {
        $smarty = &$this->context->smarty;

        $this->addCSS($this->url.'views/css/country_selector.css', 'screen');
        $this->addCSS($this->url.'views/css/matching_box.css', 'screen');

        $actives = AmazonConfiguration::get('ACTIVE', false, false);
        $regions = AmazonConfiguration::get('REGION');

        $marketplaces = array();

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

        $smarty->assign('images', $this->images);
        $smarty->assign('marketplaces', $marketplaces);
        $smarty->assign('psIs16', $this->ps16x);
        $smarty->assign('show_country_selector', is_array($marketplaces) && count($marketplaces) > 1);

        return ($smarty->fetch($this->path.'views/templates/admin/items/country_selector.tpl'));
    }
}
