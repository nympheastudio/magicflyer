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

require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.tools.class.php');

class AmazonProductTab extends Amazon
{
    public $regions = null;
    public $shipping_parameters = null;
    protected $alternative_content = false;

    public function marketplaceProductTabContent($params)
    {
        $combinations = array();
        $active = true;

        $id_product = Tools::getValue('id_product', isset($params['id_product']) ? $params['id_product'] : null) ;
        $id_lang = $this->context->language->id;
        $lang_admin = Language::getIsoById($id_lang);

        if (!is_numeric($id_product)) {
            return (false);
        }

        $product = new Product($id_product);

        if (!Validate::isLoadedObject($product)) {
            return (false);
        }

        $this->shipping_parameters = AmazonConfiguration::get('SHIPPING');
        $this->regions = AmazonConfiguration::get('REGION');
        
        $view_params = array();
        $view_params['module_url'] = $this->url;
        $view_params['module_path'] = $this->path;
        $view_params['alternative_content'] = $this->alternative_content = Configuration::get('AMAZON_ALTERNATIVE_CONTENT');
        $view_params['images'] = $this->images;
        $view_params['version'] = $this->version;
        $view_params['ps17x'] = version_compare(_PS_VERSION_, '1.7', '>=');
        $view_params['ps16x'] = version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<');
        $view_params['ps15'] = version_compare(_PS_VERSION_, '1.6', '<');
        $view_params['id_product'] = (int)$id_product;

        $marketplaces = $this->countrySelector();

        $view_params['class_warning'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
        $view_params['class_error'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
        $view_params['class_success'] = 'confirm '.($this->ps16x ? 'alert alert-success' : 'conf');
        $view_params['class_info'] = 'hint '.($this->ps16x ? 'alert alert-info' : 'conf');

        if (version_compare(_PS_VERSION_, '1.5', '>=') && Shop::isFeatureActive() && in_array($this->context->shop->getContext(), array(Shop::CONTEXT_GROUP, Shop::CONTEXT_ALL))) {
            $view_params['shop_warning'] = $this->l('You are in multishop environment. To use Amazon module, you must select a target shop.');
            $active = false;
        }

        $actives = AmazonConfiguration::get('ACTIVE');

        if ($active && is_array($actives) && count($actives) && max($actives) && $this->active && is_array($marketplaces) && count($marketplaces)) {
            $view_params['active'] = $active = true;
        } else {
            $view_params['active'] = $active = false;
        }

        if ($active) {
            $this->context = Context::getContext();
            $context_key = AmazonContext::getKey($this->context->shop);

            $view_params['json_url'] = $this->url.'functions/product_options_action.php?context_key='.$context_key;

            $product_name = $product->name[$id_lang];

            if (Combination::isFeatureActive()) {
                $combinations = array();

                $attributes_groups = $product->getAttributesGroups($id_lang);
                $attributes = $product->getProductAttributesIds($id_product, true);

                if (is_array($attributes_groups) && is_array($attributes)) {
                    foreach ($attributes as $attribute) {
                        $id_product_attribute = $attribute['id_product_attribute'];
                        $complex_id = sprintf('%d_%d', $id_product, $attribute['id_product_attribute']);

                        $combinations[$complex_id] = array();

                        $combination = new Combination((int)$id_product_attribute);
                        $attributes = $combination->getAttributesName($id_lang);

                        foreach ($attributes as $attribute2) {
                            $attribute_group_name = null;

                            foreach ($attributes_groups as $attribute_group) {
                                if ($attribute_group['id_attribute'] != $attribute2['id_attribute']) {
                                    continue;
                                }
                                $attribute_group_name = $attribute_group['group_name'];
                            }
                            if (Tools::strlen($attribute_group_name)) {
                                $combination_pair = sprintf('%s - %s', $attribute_group_name, $attribute2['name']);
                            } else {
                                $combination_pair = $attribute2['name'];
                            }

                            $combinations[$complex_id]['complex_id'] = sprintf('%d_%d', $product->id, $id_product_attribute);
                            $combinations[$complex_id]['id_product_attribute'] = (int)$id_product_attribute;
                            $combinations[$complex_id]['reference'] = $combination->reference;
                            $combinations[$complex_id]['ean13'] = $combination->ean13;
                            $combinations[$complex_id]['upc'] = $combination->upc;

                            if (isset($combinations[$complex_id]['name']) && Tools::strlen($combinations[$complex_id]['name'])) {
                                $combinations[$complex_id]['name'] .= sprintf(', %s', $combination_pair);
                            } else {
                                $combinations[$complex_id]['name'] = $combination_pair;
                            }
                        }
                    }
                }
            }

            $view_params['expert_mode'] = $this->amazon_features['expert_mode'];
            $view_params['amazon_tokens'] = AmazonConfiguration::get('CRON_TOKEN');
            $view_params['repricing'] = (bool)$this->amazon_features['repricing'];

            $view_params['product'] = array();
            $view_params['product']['name'] = $product_name;
            $view_params['product']['complex_id'] = sprintf('%d_0', $product->id);
            $view_params['product']['reference'] = $product->reference;
            $view_params['product']['ean13'] = $product->ean13;
            $view_params['product']['upc'] = $product->upc;

            $view_params['glossary'] = AmazonSettings::getGlossary($lang_admin, 'product_tab');
            $view_params['marketplaces'] = $marketplaces;
            $view_params['show_countries'] = is_array($marketplaces) && count($marketplaces) > 1;
            $view_params['combinations'] = $combinations;

            $this->context->smarty->assign(array(
                'id_lang' => $id_lang,
                'hasAttributes' => $product->hasAttributes()
            ));

            $view_params['product_options'] = $this->productOptions($product, $combinations);
        }

        $this->context->smarty->assign('product_tab', $view_params);
        $html = $this->context->smarty->fetch($this->path.'views/templates/admin/product_tab/product_tab.tpl');

        return ($html);
    }

    public function productOptions($product, $combinations)
    {
        $actives = AmazonConfiguration::get('ACTIVE');
        $regions = $this->regions;
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        $view_params = array();

        if (is_array($actives)) {
            foreach (AmazonTools::languages() as $language) {
                $id_lang = $language['id_lang'];

                if (!isset($actives[$id_lang]) || !$actives[$id_lang]) {
                    continue;
                }

                if (!isset($regions[$id_lang]) || empty($regions[$id_lang])) {
                    continue;
                }

                $nextAction = AmazonProduct::marketplaceActionGet($product->id, $id_lang);

                $product_options = AmazonProduct::getProductOptionsV4($product->id, 0, $id_lang);

                if (is_array($product_options) && count($product_options) && array_key_exists('asin1', reset($product_options))) {
                    $exists = true;
                    $product_option = reset($product_options);
                } else {
                    $exists = false;
                    $product_option = array();
                }

                if ($this->amazon_features['amazon_europe'] && isset($marketPlaceIds[$id_lang]) && AmazonTools::isEuropeMarketplaceId($marketPlaceIds[$id_lang])) {
                    $language['europe'] = true;
                } else {
                    $language['europe'] = false;
                }


                $view_params['action'][$id_lang] = $this->ptAction($product, $id_lang, $nextAction);
                $view_params['options'][$id_lang] = $this->productOptionsDetails($product, $id_lang, $product_option);
                $view_params['options'][$id_lang]['name'] = sprintf('%s (%s)', $product->name[$id_lang], $product->reference);
                $view_params['options'][$id_lang]['create'] = !$exists;

                if (is_array($combinations) && count($combinations)) {
                    $view_params['combinations_options'][$id_lang] = array();

                    foreach ($combinations as $complex_id => $combination) {
                        $combination_options = AmazonProduct::getProductOptionsV4($product->id, (int)$combination['id_product_attribute'], $id_lang);

                        if (is_array($combination_options) && count($combination_options) && array_key_exists('asin1', reset($combination_options))) {
                            $combination_option = reset($combination_options);

                            $view_params['combinations_options'][$id_lang][$complex_id] = $this->productOptionsDetails($product, $id_lang, $combination_option);
                            $view_params['combinations_options'][$id_lang][$complex_id]['name'] = sprintf('%s - %s (%s)', $product->name[$id_lang], $combination['name'], $combination['reference']);
                            $view_params['combinations_options'][$id_lang][$complex_id]['create'] = false;
                        }
                    }
                }
            }
        }
        return ($view_params);
    }

    public function productOptionsDetails($product, $id_lang, $defaults)
    {
        $view_params = array();

        $view_params['asin'] = $this->ptAsin($product, $id_lang, $defaults);
        $view_params['bullet_points'] = $this->ptBulletPoints($product, $id_lang, $defaults);
        $view_params['extra_text'] = $this->ptExtraText($product, $id_lang, $defaults);
        if ($this->alternative_content) {
            $view_params['alternative_title'] = $this->ptAlternativeTitle($product, $id_lang, $defaults);
            $view_params['alternative_description'] = $this->ptAlternativeDescription($product, $id_lang, $defaults);
        }
        $view_params['extra_price'] = $this->ptExtraPrice($product, $id_lang, $defaults);
        $view_params['unavailable'] = $this->ptUnavailable($product, $id_lang, $defaults);
        $view_params['force_in_stock'] = $this->ptForceInStock($product, $id_lang, $defaults);

        if ($this->amazon_features['expert_mode']) {
            $view_params['nopexport'] = $this->ptNopexport($product, $id_lang, $defaults);
            $view_params['noqexport'] = $this->ptNoqexport($product, $id_lang, $defaults);
        }

        if ($this->amazon_features['fba']) {
            $view_params['fba_option'] = $this->ptFbaOption($product, $id_lang, $defaults);
            $view_params['fba_value'] = $this->ptFbaValue($product, $id_lang, $defaults);
        }

        $view_params['latency'] = $this->ptLatency($product, $id_lang, $defaults);
        $view_params['gift'] = $this->ptGift($product, $id_lang, $defaults);

        if ($this->amazon_features['expert_mode'] && is_array($this->shipping_parameters) && isset($this->shipping_parameters['allow_overrides']) && (bool)$this->shipping_parameters['allow_overrides']) {
            $view_params['shipping_overrides'] = $this->ptShippingOverrides($product, $id_lang, $defaults);
        }

        $view_params['go_amazon'] = $this->ptGoAmazon($product, $id_lang, $defaults);

        $additionnal_fields = AmazonProduct::getProductOptionFields();

        if (in_array('browsenode', $additionnal_fields)) {
            $view_params['browsenode'] = $this->ptBrowsenode($product, $id_lang, $defaults);
        }
        if ($this->amazon_features['repricing'] && in_array('repricing_min', $additionnal_fields) && in_array('repricing_max', $additionnal_fields)) {
            $view_params['repricing'] = $this->ptRepricing($product, $id_lang, $defaults);
        }

        if (in_array('shipping_group', $additionnal_fields) && $this->amazon_features['shipping'] && is_array($this->shipping_parameters) && isset($this->shipping_parameters['shipping_templates']) && (bool)$this->shipping_parameters['shipping_templates']) {
            $view_params['shipping_group'] = $this->ptShippingGroup($product, $id_lang, $defaults);
        }
        return ($view_params);
    }

    public function countrySelector()
    {
        $actives = AmazonConfiguration::get('ACTIVE');
        $regions = $this->regions;

        $marketplaces = array();

        if (is_array($actives)) {
            $default = true;

            foreach (AmazonTools::languages() as $language) {
                $id_lang = $language['id_lang'];

                if (!isset($actives[$id_lang]) || !$actives[$id_lang]) {
                    continue;
                }

                if (!isset($regions[$id_lang]) || empty($regions[$id_lang])) {
                    continue;
                }

                $marketplaces[$id_lang] = array();
                $marketplaces[$id_lang]['default'] = $default;
                $marketplaces[$id_lang]['name'] = sprintf('www.amazon.%s', AmazonTools::idToDomain($id_lang));
                $marketplaces[$id_lang]['region'] = $regions[$id_lang];
                $marketplaces[$id_lang]['id_lang'] = $id_lang;
                $marketplaces[$id_lang]['iso_code'] = $language['iso_code'];
                $marketplaces[$id_lang]['active'] = $language['active'];
                $marketplaces[$id_lang]['image'] = $this->images.'geo_flags_web2/flag_'.$regions[$id_lang].'_64px.png';
                $marketplaces[$id_lang]['name_short'] = preg_replace('/ .*/', '', $language['name']);
                $marketplaces[$id_lang]['name_long'] = $language['name'];
                $default = false;
            }
        }

        return ($marketplaces);
    }

    private function ptAsin(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'id_lang' => $id_lang,
            'default' => isset($defaults['asin1']) ? $defaults['asin1'] : null
        );

        return ($view_params);
    }

    private function ptRepricing(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'id_lang' => $id_lang,
            'repricing_min' => isset($defaults['repricing_min']) && (float)$defaults['repricing_min'] ? sprintf('%.02f', $defaults['repricing_min']) : null,
            'repricing_max' => isset($defaults['repricing_max']) && (float)$defaults['repricing_max'] ? sprintf('%.02f', $defaults['repricing_max']) : null
        );

        return ($view_params);
    }

    private function ptShippingOverrides(&$product, $id_lang, &$defaults = null)
    {
        $default1 = isset($defaults['shipping']) && $defaults['shipping'] ? sprintf('%.02f', $defaults['shipping']) : null;
        $default2 = isset($defaults['shipping_type']) ? (int)$defaults['shipping_type'] : null;

        $checked1 = $default2 == 1 ? ' checked="checked"' : '';
        $checked2 = $default2 == 2 ? ' checked="checked"' : '';

        $view_params = array(
            'default' => $default1,
            'checked1' => $checked1,
            'checked2' => $checked2,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptGoAmazon(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array();
        $asin = isset($defaults['asin1']) && $defaults['asin1'] ? $defaults['asin1'] : null;

        if ($asin) {
            $view_params = array(
                'default' => AmazonTools::goToProductPage($id_lang, $asin),
                'id_lang' => $id_lang
            );
        }

        return ($view_params);
    }

    private function ptGift(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'gift_wrap_checked' => isset($defaults['gift_wrap']) && (bool)$defaults['gift_wrap'] ? 'checked="checked"' : '',
            'gift_message_checked' => isset($defaults['gift_message']) && (bool)$defaults['gift_message'] ? 'checked="checked"' : '',
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptLatency(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'default' => isset($defaults['latency']) ? $defaults['latency'] : null,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptBrowsenode(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'default' => isset($defaults['browsenode']) ? $defaults['browsenode'] : null,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptFbaValue(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'default' => isset($defaults['fba_value']) ? $defaults['fba_value'] : null,
            'id_lang' => $id_lang,
            'isFBA' => isset($defaults['fba']) && (bool)$defaults['fba'] ? 'europe' : null
        );

        return ($view_params);
    }

    private function ptFbaOption(&$product, $id_lang, &$defaults = null)
    {
        if ($this->amazon_features['amazon_europe']) {
            $europe = 'rel="europe"';
        } else {
            $europe = '';
        }

        $checked = isset($defaults['fba']) && (bool)$defaults['fba'] ? 'checked="checked"' : '';

        $view_params = array(
            'checked' => $checked,
            'id_lang' => $id_lang,
            'europe' => $europe
        );

        return ($view_params);
    }

    private function ptNoqexport(&$product, $id_lang, &$defaults = null)
    {
        $checked = isset($defaults['noqexport']) && (bool)$defaults['noqexport'] ? 'checked="checked"' : '';

        $view_params = array(
            'checked' => $checked,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptNopexport(&$product, $id_lang, &$defaults = null)
    {
        $checked = isset($defaults['nopexport']) && (bool)$defaults['nopexport'] ? 'checked="checked"' : '';

        $view_params = array(
            'checked' => $checked,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptForceInStock(&$product, $id_lang, &$defaults = null)
    {
        $checked = isset($defaults['force']) && (bool)$defaults['force'] ? 'checked="checked"' : '';

        $view_params = array(
            'checked' => $checked,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptUnavailable(&$product, $id_lang, &$defaults = null)
    {
        $checked = isset($defaults['disable']) && (bool)$defaults['disable'] ? 'checked="checked"' : '';

        $view_params = array(
            'checked' => $checked,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptExtraPrice(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'default' => isset($defaults['price']) ? $defaults['price'] : null,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptExtraText(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'default' => isset($defaults['text']) ? $defaults['text'] : null,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptAlternativeTitle(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'default' => isset($defaults['alternative_title']) ? $defaults['alternative_title'] : null,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptAlternativeDescription(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'default' => isset($defaults['alternative_description']) ? $defaults['alternative_description'] : null,
            'id_lang' => $id_lang
        );

        return ($view_params);
    }

    private function ptBulletPoints(&$product, $id_lang, &$defaults = null)
    {
        $view_params = array(
            'id_lang' => $id_lang,
            'default' => $defaults
        );

        return ($view_params);
    }

    private function ptShippingGroup(&$product, $id_lang, &$defaults = null)
    {
        static $configured_group_names = null;

        $view_params = array();

        if (!isset($this->regions[$id_lang]) || empty($this->regions[$id_lang])) {
            return($view_params);
        } else {
            $region = $this->regions[$id_lang];
        }

        if ($configured_group_names === null) {
            $configured_group_names = unserialize(AmazonConfiguration::get('shipping_groups'));
        }

        $group_names = array();
        $display_group_names = array();

        if (is_array($configured_group_names) && count($configured_group_names)) {
            foreach ($configured_group_names as $group_region => $group_names) {
                if ($group_region != $region) {
                    continue;
                }
                if (is_array($group_names) && count($group_names)) {
                    foreach ($group_names as $group_key => $group_name) {
                        $display_group_names[$id_lang][$group_key] = $group_name;
                    }
                }
            }
        }

        if (is_array($group_names) && count($group_names)) {
            $view_params = array(
                'groups' => $display_group_names,
                'default' => isset($defaults['shipping_group']) ? $defaults['shipping_group'] : null,
                'id_lang' => $id_lang
            );
        }
        return ($view_params);
    }

    private function ptAction(&$product, $id_lang, $default)
    {
        if (AmazonProduct::marketplaceInCategories($product->id)) {
            $view_params = array(
                'id_lang' => $id_lang,
                'id_product' => $product->id,
                'default' => $default,
                'images' => $this->images,
                'amazon_update' => Amazon::UPDATE,
                'amazon_add' => Amazon::ADD,
                'amazon_remove' => Amazon::REMOVE,
                'expert_mode' => $this->amazon_features['expert_mode'],
                'in_category' => true,
                'deletion' => AmazonConfiguration::get('DELETE_PRODUCTS')
            );
        } else {
            $view_params = array(
                'in_category' => false
            );
        }

        return ($view_params);
    }
}
