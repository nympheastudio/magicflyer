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

if (!class_exists('CommonCart')) {
    abstract class CommonCart extends Cart
    {
        public $taxCalculationMethod = PS_TAX_INC;
        public static $debug_mode = false;

        /**
         * @return float|int
         */
        public function marketplaceGetCarrierTaxRate()
        {
            $carrier_tax_rate = 0;
            $pass = true;

            if (!$this->id_carrier) {
                $pass = false;
            }

            $address_type = Configuration::get('PS_TAX_ADDRESS_TYPE');

            if (empty($address_type)) {
                $address_type = 'id_address_delivery';
            }

            $address = new Address($this->{$address_type});

            if (!Validate::isLoadedObject($address)) {
                $pass = false;
            }

            if ($pass && $this->taxCalculationMethod) {
                // Carrier Taxes
                //
                if (method_exists('Carrier', 'getTaxesRate')) {
                    $carrier = new Carrier($this->id_carrier);

                    if (Validate::isLoadedObject($carrier)) {
                        $carrier_tax_rate = (float)$carrier->getTaxesRate($address);
                    }
                } elseif (method_exists('Tax', 'getCarrierTaxRate')) {
                    $carrier_tax_rate = (float)Tax::getCarrierTaxRate($this->id_carrier, (int)$address->id);
                }
            }

            if (self::$debug_mode) {
                CommonTools::p("marketplaceGetCarrierTaxRate");
                CommonTools::p(sprintf('taxCalculationMethod: %s', $this->taxCalculationMethod));
                CommonTools::p(sprintf('id_carrier: %d', $this->id_carrier));
                CommonTools::p(sprintf('address_type: %s', $address_type));
                CommonTools::p(sprintf('id_address: %d', $address->id));
                CommonTools::p(sprintf('carrier_tax_rate: %s', $carrier_tax_rate));
            }

            return ($carrier_tax_rate);
        }


        /**
         * @return int
         */
        public function marketplaceCalculationMethod($force = false)
        {
            if ($force) {
                return (PS_TAX_INC);
            }

            if ($this->id_customer) {
                $customer = new Customer((int)($this->id_customer));
                $this->taxCalculationMethod = !Group::getPriceDisplayMethod((int)($customer->id_default_group));
            } else {
                $this->taxCalculationMethod = !Group::getDefaultPriceDisplayMethod();
            }

            if (self::$debug_mode) {
                CommonTools::p('marketplaceCalculationMethod:');
                CommonTools::p(sprintf('id_customer: %d', $this->id_customer));
                CommonTools::p(sprintf('taxCalculationMethod: %s', $this->taxCalculationMethod));
            }
            return((int)$this->taxCalculationMethod);
        }

        /**
         * @param $product
         *
         * @return float|int
         */
        protected function marketplaceGetTaxRate($product)
        {
            $product_tax_rate = 0;

            if ($product['tax_rate']) {
                if ($this->taxCalculationMethod) {
                    if (method_exists('Tax', 'getProductTaxRate')) {
                        $product_tax_rate = (float)Tax::getProductTaxRate((int)$product['id_product'], (int)$product['id_address_delivery']);
                    } else {
                        $product_tax_rate = (float)Tax::getApplicableTax((int)$product['id_tax'], $product['tax_rate'], (int)$product['id_address_delivery']);
                    }
                }
            }
            if (self::$debug_mode) {
                CommonTools::p('marketplaceGetTaxRate:');
                CommonTools::p(sprintf('taxCalculationMethod: %s', $this->taxCalculationMethod));
                CommonTools::p(sprintf('product/id_product: %d', $product['id_product']));
                CommonTools::p(sprintf('product/id_tax: %d', $product['id_tax']));
                CommonTools::p(sprintf('product/id_address_delivery: %d', $product['id_address_delivery']));
                CommonTools::p(sprintf('product_tax_rate: %s', $product_tax_rate));
            }

            return ($product_tax_rate);
        }
    }
}
