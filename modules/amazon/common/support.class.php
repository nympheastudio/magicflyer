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
 * @package   Common-Classes
 * Support by mail:  support@common-services.com
 */

if (!class_exists('CommonSupport')) {
    abstract class CommonSupport extends Module
    {
        public $product_id;
        public $support_url = 'https://support.common-services.com';

        public function getWidget($name, $displayName, $version)
        {
            $widget_infos = array();

            $widget_infos['support_requester']      = Configuration::get('PS_SHOP_EMAIL');
            $widget_infos['support_ps_version']     = _PS_VERSION_;
            $widget_infos['support_module_version'] = $version;
            $widget_infos['support_site']           = $_SERVER['HTTP_HOST'];
            $widget_infos['support_subject']        = sprintf(
                '%s %s v%s %s %s',
                $this->l('Support Request for'),
                $displayName,
                $version,
                $this->l('from'),
                Configuration::get('PS_SHOP_NAME')
            );
            $widget_infos['support_product']        = $this->product_id;
            $widget_infos['support_url']            = $this->support_url;

            return ($widget_infos);
        }
    }
}
