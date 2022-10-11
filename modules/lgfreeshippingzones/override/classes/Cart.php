<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Cart extends CartCore
{
    public function getPackageShippingCost(
        $id_carrier = null,
        $use_tax = true,
        Country $default_country = null,
        $product_list = null,
        $id_zone = null,
        $id_shop = null
    ) {
        if (Module::isInstalled('lgfreeshippingzones')) {
            if (!$id_zone) {
                $addr = new Address($this->id_address_delivery);
                if (Validate::isLoadedObject($addr)) {
                    $id_zone = State::getIdZone($addr->id_state);
                    if (!$id_zone) {
                        $id_zone = CountryCore::getIdZone($addr->id_country);
                    }
                }
            } if (!$id_zone && $default_country) {
                $id_zone = $default_country->id_zone;
            } if ($id_zone == false) {
                $id_country = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
                $id_zone = Db::getInstance()->getValue(
                    'SELECT id_zone FROM '._DB_PREFIX_.'country '.
                    'WHERE id_country = '.(int)$id_country
                );
            }
            if ($id_carrier == false) {
                $id_carrier = (int)(Configuration::get('PS_CARRIER_DEFAULT'));
            }
            $cache_id = 'Cart::getPackageShippingCost'.md5($id_carrier.$use_tax.$id_zone);
            include_once(_PS_MODULE_DIR_.'lgfreeshippingzones/lgfreeshippingzones.php');
            $lgfsz = new LGFreeshippingzones();
            if ((int)Configuration::get('PS_LGFREESHIPPINGZONES_TAX') == 1) {
                if (
                    $lgfsz->FSCheck(
                        $id_zone,
                        $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false),
                        $this->getTotalWeight(),
                        $id_carrier,
                        $id_shop
                    )
                 ) {
                    $shipping_cost = 0;
                    Cache::store($cache_id, $shipping_cost);
                    return Cache::retrieve($cache_id);
                }
            } if ((int)Configuration::get('PS_LGFREESHIPPINGZONES_TAX') == 0) {
                if (
                    $lgfsz->FSCheck(
                        $id_zone,
                        $this->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING, null, null, false),
                        $this->getTotalWeight(),
                        $id_carrier,
                        $id_shop
                    )
                ) {
                    $shipping_cost = 0;
                    Cache::store($cache_id, $shipping_cost);
                    return Cache::retrieve($cache_id);
                }
            }
        }
        return parent::getPackageShippingCost($id_carrier, $use_tax, $default_country, $product_list, $id_shop);
    }
}
