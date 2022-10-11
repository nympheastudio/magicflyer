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

if (!class_exists('CommonOrder')) {
    abstract class CommonOrder extends Order
    {
        public static $debug_mode = false;

        /**
         * @param $order
         *
         * @return int|null
         */
        public static function getShippingNumber($order = null)
        {
            if (!Validate::isLoadedObject($order)) {
                return (null);
            }

            if (!empty($order->shipping_number)) {
                return ($order->shipping_number);
            } else {
                if (version_compare(_PS_VERSION_, '1.5', '>')) {
                    $id_order_carrier = Db::getInstance()->getValue(
                        '
                            SELECT `id_order_carrier`
                            FROM `'._DB_PREFIX_.'order_carrier`
                            WHERE `id_order` = '.(int)$order->id
                    );

                    if ($id_order_carrier) {
                        $order_carrier = new OrderCarrier($id_order_carrier);

                        if (Validate::isLoadedObject($order_carrier)) {
                            if (!empty($order_carrier->tracking_number)) {
                                return ($order_carrier->tracking_number);
                            }
                        }
                    }
                }
            }

            return (null);
        }


        /**
         * @param $date_add
         * @param $amount
         * @param $payment_title
         * @param $module
         *
         * @return bool
         */
        public static function isExistingOrder($date_add, $amount, $payment_title, $module)
        {
            $sql = 'SELECT `id_order` FROM `'._DB_PREFIX_.'orders`
			WHERE `payment` = "'.pSQL($payment_title).'" AND  `module`="'.pSQL($module).'" AND `date_add`="'.pSQL($date_add).'" AND `total_paid`='.(float)$amount;

            $result = Db::getInstance()->executeS($sql, true, false);

            if (self::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('SQL: %s'.Amazon::LF, $sql));
                CommonTools::p(sprintf('Result: %s'.Amazon::LF, print_r($result, true)));
            }

            if (is_array($result) && count($result)) {
                return ($result[0]['id_order']);
            }

            return (false);
        }
    }
}
