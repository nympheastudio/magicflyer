{*
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2017 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}

<div style="display: none">
    <div id="pickup_panel" class="panel">

        <div class="panel-heading pickup">
            <i class="icon-truck"></i>
            {l s='Delivery with la Navette Pickup' mod='lanavettepickup'}
        </div>

        {if $error_messages}
            <div>
                {foreach $error_messages as $error_message}
                    <p class="alert alert-danger">{$error_message|escape:'htmlall':'UTF-8'}</p>
                {/foreach}
            </div>
        {/if}
        {if $confirmation_messages}
            <div>
                {foreach $confirmation_messages as $confirmation_message}
                    <p class="alert alert-success">{$confirmation_message|escape:'htmlall':'UTF-8'}</p>
                {/foreach}
            </div>
        {/if}

        <table>
            <tr>
                {if $lnp_load == 'cart'}
                    <td>
                        <form method="post"
                              action="index.php?controller=Lnp2PickupOrderPage&token={getAdminToken tab='Lnp2PickupOrderPage'}&generate_tracking=true&id_order={$id_order|escape:'html':'UTF-8'}&order_page=1">
                            <input type="submit" value="{l s='Generate a new tracking number' mod='lanavettepickup'}"></form>
                    </td>
                {/if}
                {if $lnp_load == 'all'}
                {*<td>*}
                    {*<form method="post"*}
                          {*action="index.php?controller=Lnp2PickupOrderPage&token={getAdminToken tab='Lnp2PickupOrderPage'}&generate_tracking=true&id_order={$id_order|escape:'html':'UTF-8'}&order_page=1">*}
                        {*<input type="submit" value="{l s='Generate a new tracking number' mod='lanavettepickup'}"></form>*}
                {*</td>*}
                {*<td>&nbsp;</td>*}
                <td>
                    <form method="post"
                          action="index.php?controller=Lnp2PickupOrderPage&token={getAdminToken tab='Lnp2PickupOrderPage'}&print_pdf=true&id_order={$id_order|escape:'html':'UTF-8'}&order_page=1">
                        <input type="submit" value="{l s='Print the shipping label' mod='lanavettepickup'}"></form>
                </td>
                <td>&nbsp;</td>
                <td>
                    <form method="post" action="https://lanavette.pickup.fr/Suivi/{$navette_code|escape:'html':'UTF-8'}" target="_blank"><input
                                type="submit" value="{l s='Track your parcel' mod='lanavettepickup'}">
                    </form>
                </td>
                {/if}
            </tr>
        </table>
        <br/>

        <dl class="well list-detail">
            {if $lnp_load == 'all'}
                <dt>{l s='Temporary tracking number' mod='lanavettepickup'}</dt>
                <dd>{$navette_code|escape:'html':'UTF-8'}</dd>
                <dt>{l s='Pickup parcelshop' mod='lanavettepickup'}</dt>
                <dd>{$delivery_name|escape:'html':'UTF-8'}<br/>
                    {$delivery_address|escape:'html':'UTF-8'}<br/>
                    {$delivery_zip_code|escape:'html':'UTF-8'} {$delivery_city|escape:'html':'UTF-8'} {$delivery_country_code|escape:'html':'UTF-8'}
                </dd>
                <dt>{l s='Insurance' mod='lanavettepickup'}</dt>
            {/if}
            <dd>{if $insurance}{l s='Yes' mod='lanavettepickup'}{else}{l s='No' mod='lanavettepickup'}{/if}</dd>
        </dl>
    </div>
</div>

<script>
    $(document).ready(function () {

        var is_PS16 = '{$is_PS16|escape:'html':'UTF-8'}';

        {literal}

        console.log('is_PS16 = ' + is_PS16);

        if (is_PS16) {
            $('#pickup_panel').insertAfter($('#shipping').parent('.panel')); // PS 1.6
            console.log('PS 1.6');
        } else {
            $('#pickup_panel').insertAfter($('#shipping_table')); // PS 1.5
            $('#pickup_panel').css("border", "1px solid #ccc").css("background-color", "#fff").css("padding", "1em").css("margin-top", "1em");
            console.log('PS 1.5');
        }

        {/literal}
    })
</script>



