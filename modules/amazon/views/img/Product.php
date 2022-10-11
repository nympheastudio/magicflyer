<?php
/**
 * GIFT CARD
 *
 *    @author    EIRL Timactive De Véra
 *    @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
 *    @license   Commercial license
 *    @category pricing_promotion
 *    @version 1.1.0
 *
 *************************************
 **         GIFT CARD                *
 **          V 1.0.0                 *
 *************************************
 * +
 * + Languages: EN, FR, ES
 * + PS version: 1.5,1.6,1.7
 */
class Product extends ProductCore
{
    /**
     *
     * @author
     */
    /*
    * module: giftcard
    * date: 2017-12-20 15:00:07
    * version: 1.1.15
    */
    public static function getPriceStatic(
        $id_product,
        $usetax = true,
        $id_product_attribute = null,
        $decimals = 6,
        $divisor = null,
        $only_reduc = false,
        $usereduc = true,
        $quantity = 1,
        $force_associated_tax = false,
        $id_customer = null,
        $id_cart = null,
        $id_address = null,
        &$specific_price_output = null,
        $with_ecotax = true,
        $use_group_reduction = true,
        Context $context = null,
        $use_customer_price = true,
        $id_customization = null
    ) {
        $giftcard = Module::getInstanceByName('giftcard');
        if ($giftcard && $giftcard->active && (int) $id_product > 0 && $giftcard->isGiftCard($id_product)) {
            return ((float) GiftCardProduct::getAmount($id_product));
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<') === true) {
            return (parent::getPriceStatic(
                $id_product,
                $usetax,
                $id_product_attribute,
                $decimals,
                $divisor,
                $only_reduc,
                $usereduc,
                $quantity,
                $force_associated_tax,
                $id_customer,
                $id_cart,
                $id_address,
                $specific_price_output,
                $with_ecotax,
                $use_group_reduction,
                $context,
                $use_customer_price
            ));
        }
        return (parent::getPriceStatic(
            $id_product,
            $usetax,
            $id_product_attribute,
            $decimals,
            $divisor,
            $only_reduc,
            $usereduc,
            $quantity,
            $force_associated_tax,
            $id_customer,
            $id_cart,
            $id_address,
            $specific_price_output,
            $with_ecotax,
            $use_group_reduction,
            $context,
            $use_customer_price,
            $id_customization
        ));
    }
}
