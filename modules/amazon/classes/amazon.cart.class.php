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

require_once(_PS_MODULE_DIR_.'/amazon/common/cart.class.php');

class AmazonCart extends CommonCart
{
    /**
     * @var bool
     */
    public $marketplace = true;

    /**
     * @var null
     */
    public $amazonProducts = null;
    /**
     * @var int
     */
    public $amazonShipping = 0;
    /**
     * @var null
     */
    public $amazonChannel  = null;

    /**
     * @var null
     */
    public $amazon_order_info = null;

    /**
     * @var null
     */
    public $id_country  = null;

    /**
     * In FBA order, seller country and buyer country can be different. We should apply tax of buyer country
     * This tax will be apply for both carrier + product. So we should calculate 1 time only and store it to $_tax_rate_for_fba
     * @var object('active', 'id_tax_rule', 'id_country')
     */
    public $tax_for_fba;

    // Store tax rate for special FBA order
    private $_tax_rate_for_fba = null;

    /**
     * This function returns the total cart amount
     *
     * type = 1 : only products
     * type = 2 : only discounts
     * type = 3 : both
     * type = 4 : both but without shipping
     * type = 5 : only shipping
     * type = 6 : only wrapping
     * type = 7 : only products without shipping
     */

    public function getOrderTotal($withTaxes = true, $type = 3, $products = null, $id_carrier = null, $use_cache = true)
    {
        if (!is_array($this->amazonProducts) || (is_array($this->amazonProducts) && !count($this->amazonProducts))) {
            return (false);
        }

        $type = (int)$type;
        if (!in_array($type, array(1, 2, 3, 4, 5, 6, 7, 8))) {
            die(Tools::displayError('no type specified'));
        }

        $this->marketplaceCalculationMethod(Configuration::get('AMAZON_FORCE_TAXES'));

        $total_price_tax_incl = 0;
        $total_price_tax_excl = 0;

        $total_wrapping_tax_incl = 0;
        $total_wrapping_tax_excl = 0;

        $total_shipping_tax_incl = 0;
        $total_shipping_tax_excl = 0;

        $amazon_has_tax = null;

        $carrier_tax_rate = $this->getTaxRate('carrier', null);
        $is_business = null;

        foreach ($this->amazonProducts as $product) {
            $product_tax_rate = 0;
            $quantity = (int)$product['qty'];
            $is_business = $product['is_business'];

            if ($product['amazon_has_tax']) {
                if ($product['europe']) {
                    $unit_price_tax_excl = Tools::ps_round($product['price'] - ((float)$product['amazon_item_tax'] / $quantity), 2);
                    $unit_price_tax_incl = Tools::ps_round($product['price'], 2);
                } else {
                    // why this case: https://support.common-services.com/helpdesk/tickets/36978
                    // seems in USA, the product price in the feed is tax excluded
                    $unit_price_tax_excl = Tools::ps_round($product['price'], 2);
                    $unit_price_tax_incl = Tools::ps_round($product['price'] + ((float)$product['amazon_item_tax'] / $quantity), 2);
                }

                $total_shipping_tax_excl += Tools::ps_round($product['shipping'] - $product['amazon_shipping_tax'], 2);
                $total_shipping_tax_incl += Tools::ps_round($product['shipping'], 2);

                if ($amazon_has_tax === null) {
                    $amazon_has_tax = true;
                }
            } else {
                if ($is_business) {
                    $carrier_tax_rate = 0;
                }
                $product_tax_rate = $this->getTaxRate('product', $product);

                $unit_price_tax_excl = $product_tax_rate ? Tools::ps_round($product['price'] / ((100 + $product_tax_rate) / 100), 2) : Tools::ps_round($product['price'], 2);
                $unit_price_tax_incl = Tools::ps_round((float)$product['price'], 2);
            }

            $total_price_tax_incl += ($unit_price_tax_incl * (int)$product['qty']);
            $total_price_tax_excl += ($unit_price_tax_excl * (int)$product['qty']);

            if (isset($product['giftwrap'])) {
                if (!$product['amazon_has_tax']) {
                    $unit_wrapping_tax_excl = $product_tax_rate ? Tools::ps_round(($product['giftwrap'] / $quantity) / ((100 + $product_tax_rate) / 100), 2) : Tools::ps_round(($product['giftwrap'] / $quantity), 2);
                    $unit_wrapping_tax_incl = $product['giftwrap'] / $quantity;

                    $total_wrapping_tax_incl += $unit_wrapping_tax_incl;
                    $total_wrapping_tax_excl += Tools::ps_round($unit_wrapping_tax_excl, 2);
                } else {
                    $unit_wrapping_tax_excl = Tools::ps_round($product['giftwrap'] - ($product['amazon_giftwrap_tax'] / $quantity), 2);
                    $unit_wrapping_tax_incl = Tools::ps_round($product['giftwrap'], 2);

                    $total_wrapping_tax_incl += $unit_wrapping_tax_incl;
                    $total_wrapping_tax_excl += $unit_wrapping_tax_excl;
                }
            }
        }

        if (!$amazon_has_tax) {
            $total_shipping_tax_excl = $carrier_tax_rate ? Tools::ps_round($this->amazonShipping / ((100 + $carrier_tax_rate) / 100), 2) : Tools::ps_round($this->amazonShipping, 2);
            $total_shipping_tax_incl = (float)Tools::ps_round($this->amazonShipping, 2);
        }

        $wrapping_fees = ($withTaxes ? $total_wrapping_tax_incl : $total_wrapping_tax_excl);

        switch ($type) {
            case 1:
            case 8:
                $amount = ($withTaxes ? $total_price_tax_incl : $total_price_tax_excl);
                break;
            case 3:
                $amount = ($withTaxes ? $total_price_tax_incl + $total_shipping_tax_incl : $total_price_tax_excl + $total_shipping_tax_excl) + $wrapping_fees;
                break;
            case 4:
                $amount = ($withTaxes ? $total_price_tax_incl : $total_price_tax_excl) + $wrapping_fees;
                break;
            case 2:
                return (0);
            case 5:
                $amount = $withTaxes ? $total_shipping_tax_incl : $total_shipping_tax_excl;
                break;
            case 6:
                $amount = $wrapping_fees;
                break;
            case 7:
                $amount = $withTaxes ? $total_price_tax_incl : $total_price_tax_excl;
                break;
            default:
                $amount = 0;
        }

        return Tools::ps_round(max(0, $amount), 2);
    }

    /**
     * @param string $type carrier|product
     * @param array|null $product
     * @return float|int
     */
    public function getTaxRate($type, $product)
    {
        // Not apply for normal order
        if (!$this->tax_for_fba->active) {
            return $this->getTaxRateNormal($type, $product);
        }

        // Tax rate has been calculated return it
        if (!is_null($this->_tax_rate_for_fba)) {
            return $this->_tax_rate_for_fba;
        }

        if (!class_exists('TaxManagerFactory') || !method_exists('TaxManagerFactory', 'getManager')) {
            return $this->getTaxRateNormal($type, $product);
        }

        // Calculate tax rate for special FBA order, store it to use later
        $address             = new Address();
        $address->id_country = $this->tax_for_fba->id_country;
        $address->id_state   = 0;
        $address->postcode   = 0;

        $tax_manager = TaxManagerFactory::getManager($address, $this->tax_for_fba->id_tax_rule);
        $tax_calculator = $tax_manager->getTaxCalculator();
        $tax_rate = $tax_calculator->getTotalRate();
        $this->_tax_rate_for_fba = $tax_rate;

        return $tax_rate;
    }

    /**
     * @param string $type carrier|product
     * @param array $product
     * @return float|int
     */
    public function getTaxRateNormal($type, $product)
    {
        if ('carrier' == $type) {
            return parent::marketplaceGetCarrierTaxRate();
        } elseif ('product' == $type) {
            return parent::marketplaceGetTaxRate($product);
        }

        return 0;
    }
}
