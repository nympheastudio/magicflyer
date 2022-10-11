{*
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2020 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 *}

{if $restriction}
    <div class="cartcon_message">
        {l s='You must order at least: ' mod='cartcon'}{$restriction.quantity} {l s='quantity of this product' mod='cartcon'}
    </div>
{/if}