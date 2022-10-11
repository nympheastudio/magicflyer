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

class AmazonBulletPoint extends Amazon
{
    public function __construct()
    {
        parent::__construct();

        parent::loadFeatures();
        parent::loadAttributes();
    }

    public function bulletPointEncode()
    {
        $json_raw = Tools::getValue('data');

        if (!Tools::strlen($json_raw)) {
            return (false);
        }

        $json_object = Tools::jsonDecode($json_raw);

        if (!$json_object instanceof stdClass) {
            return (false);
        }

        return AmazonTools::encode(serialize($json_object));
    }

    public function bulletPointDecode($encoded_bullet_point)
    {
        if (!Tools::strlen($encoded_bullet_point)) {
            return (false);
        }

        $json_object = unserialize(AmazonTools::decode($encoded_bullet_point));

        if (!$json_object instanceof stdClass) {
            return (false);
        }

        return Tools::jsonEncode($json_object);
    }

    public function bulletPointEditorUi($id, $existing_bullet_point = null)
    {
        $html = null;
        $view_params = array();

        $view_params['attributes'] = null;
        $view_params['path'] = _PS_MODULE_DIR_.'/amazon/';
        $view_params['url'] = $this->url;
        $view_params['version'] = $this->version;
        $view_params['id'] = $id;

        $view_params['fields'] = array();
        $view_params['fields']['product_name'] = $this->l('Product Name');
        $view_params['fields']['manufacturer'] = $this->l('Manufacturer');
        $view_params['fields']['supplier'] = $this->l('Supplier');
        $view_params['fields']['category'] = $this->l('Category');

        if (Tools::strlen($existing_bullet_point)) {
            $view_params['existing_bullet_point'] = $bullet_point_object = $this->bulletPointDecode($existing_bullet_point);
        } else {
            $view_params['existing_bullet_point'] = null;
        }

        if (isset(self::$attributes_groups[$this->id_lang]) && isset(self::$attributes[$this->id_lang])) {
            // Bullet Point Generator

            $attributes_groups = &self::$attributes_groups[$this->id_lang];
            $attributes = &self::$attributes[$this->id_lang];

            $view_params['attributes'] = array();

            if (is_array($attributes_groups) && count($attributes_groups)) {
                foreach ($attributes_groups as $id_attribute_group => $attribute_group) {
                    if (!isset($attributes[$id_attribute_group])) {
                        continue;
                    }

                    $count_attributes = is_array($attributes[$id_attribute_group]) ? count($attributes[$id_attribute_group]) : 0;

                    $attribute_value = null;
                    $pass = false;

                    for ($i = 0; $i < $count_attributes; $i++) {
                        $any_index = array_rand($attributes[$id_attribute_group]);
                        $attribute_value = $attributes[$id_attribute_group][$any_index]['name'];

                        if (!Tools::strlen($attribute_value)) {
                            continue;
                        }

                        $pass = true;
                    }
                    if ($pass) {
                        $view_params['attributes'][$id_attribute_group] = $attribute_group;
                        $view_params['attributes'][$id_attribute_group]['any_value'] = $attribute_value;
                    }
                }
            }
        }
        if (isset(self::$features[$this->id_lang]) && isset(self::$features_values[$this->id_lang])) {
            $features = &self::$features[$this->id_lang];
            $features_values = &self::$features_values[$this->id_lang];

            $view_params['features'] = array();

            if (is_array($features) && count($features)) {
                foreach ($features as $id_feature => $feature) {
                    if (!isset($features_values[$id_feature])) {
                        continue;
                    }

                    $count_features = is_array($features_values[$id_feature]) ? count($features_values[$id_feature]) : 0;

                    $feature_value = null;
                    $pass = false;

                    for ($i = 0; $i < $count_features; $i++) {
                        $any_index = array_rand($features_values[$id_feature]);
                        $feature_value = $features_values[$id_feature][$any_index]['value'];

                        if (!Tools::strlen($feature_value)) {
                            continue;
                        }

                        $pass = true;
                    }
                    if ($pass) {
                        $view_params['features'][$id_feature] = $feature;
                        $view_params['features'][$id_feature]['any_value'] = $feature_value;
                    }
                }
            }
        }

        $this->context->smarty->assign('bulletpoint', $view_params);

        $html = $this->context->smarty->fetch(_PS_MODULE_DIR_.'/amazon/views/templates/admin/configure/bulletpoint/editor.tpl');

        return $html;
    }
}
