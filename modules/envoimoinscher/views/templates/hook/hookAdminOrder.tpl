{**
 * 2007-2018 PrestaShop
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
 * @author    EnvoiMoinsCher <api@boxtal.com>
 * @copyright 2007-2018 PrestaShop SA / 2011-2016 EnvoiMoinsCher
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 *}

{if $multiSize > 1}
    {if ($panel)}
        <div id="emcMultiparcelTab">
            <li>
                <a href="#emcMultiparcel">
                    <i class="icon-truck"></i>
                    {l s='Multiparcel' mod='envoimoinscher'}
                </a>
            </li>
        </div>
        <div id="emcMultiparcelTabContent">
            <div class="tab-pane" id="emcMultiparcel">
                <!-- Parcel point info -->
                <h4 class="visible-print">{l s='Multiparcel' mod='envoimoinscher'}</h4>
                <div>
                    {l s='Multiparcel' mod='envoimoinscher'} : <b>{$multiSize|escape:'htmlall':'UTF-8'} {l s='parcel' mod='envoimoinscher'}</b>
                    ({foreach from=$multiParcels key=p item=parcel name=parcels}{$parcel.weight_eop} kg{if !$smarty.foreach.parcels.last}, {/if}{/foreach}).
                </div>
            </div>
        </div>
    {else}
        <div class="emcM0 emcMt15 emcMb15">
            <fieldset class="emcW400">
                <legend class="emcS13"><img src="../img/admin/delivery.gif" alt="Point relais">{l s='Multiparcel' mod='envoimoinscher'}</legend>
                <div class="emcFl emcS13">{l s='Multiparcel' mod='envoimoinscher'} : <b>{$multiSize|escape:'htmlall':'UTF-8'} {l s='parcel' mod='envoimoinscher'}</b> (
                    {foreach from=$multiParcels key=p item=parcel name=parcels}
                        {$parcel.weight_eop} kg {if !$smarty.foreach.parcels.last},{/if}
                    {/foreach} ).
                </div>
            </fieldset>
        </div>
    {/if}
{/if}
{if isset($point)}
    {if ($panel)}
        <div id="emcRelayPointTab">
            <li>
                <a href="#emcRelayPoint">
                    <i class="icon-truck"></i>
                    {l s='Arrival parcel point' mod='envoimoinscher'}
                </a>
            </li>
        </div>
        <div id="emcRelayPointTabContent">
            <div class="tab-pane" id="emcRelayPoint">
                <!-- Parcel point info -->
                <h4 class="visible-print">{l s='Arrival parcel point' mod='envoimoinscher'}</h4>
                <div>
                    <b>{$point.name|escape:'htmlall':'UTF-8'}</b><br />
                    {$point.address|escape:'htmlall':'UTF-8'}<br />
                    {$point.zipcode|escape:'htmlall':'UTF-8'} {$point.city|escape:'htmlall':'UTF-8'} 
                </div>
                <div>
                    {foreach from=$schedule key=d item=day}
                    {$day}<br />
                    {/foreach}
                </div>
            </div>
        </div>
    {else}
        <div class="emcM0 emcMb15 emcMt15">
            <fieldset class="emcW400">
                <legend class="emcS13"><img src="../img/admin/delivery.gif" alt="Point relais">{l s='Arrival parcel point' mod='envoimoinscher'}</legend>
                <div class="emcFl emcW200 emcS13">
                    {$point.name|escape:'htmlall':'UTF-8'}<br />
                    {$point.address|escape:'htmlall':'UTF-8'}<br />
                    {$point.zipcode|escape:'htmlall':'UTF-8'} {$point.city|escape:'htmlall':'UTF-8'} 
                </div>
                <div class="emcFl emcS13">
                    {foreach from=$schedule key=d item=day}
                    {$day}<br />
                    {/foreach}
                </div>
            </fieldset>
        </div>
    {/if}
{/if}

{if ($panel)}
    <script>
        $(document).ready(function() {
            var tabRelayPoint = $('#emcRelayPointTab').html();
            var tabRelayPointContent = $('#emcRelayPointTabContent').html();
            var tabMultiparcel = $('#emcMultiparcelTab').html();
            var tabMultiparcelContent = $('#emcMultiparcelTabContent').html();
            $('a[href="#addressShipping"]').parent('li').after(tabRelayPoint);
            $('a[href="#addressInvoice"]').parent('li').after(tabMultiparcel);
            $('#addressShipping').after(tabRelayPointContent);
            $('#addressInvoice').after(tabMultiparcelContent);
            $('#emcRelayPointTab').remove();
            $('#emcRelayPointTabContent').remove();
            $('#emcMultiparcelTab').remove();
            $('#emcMultiparcelTabContent').remove();
            
            $(document).delegate('#tabAddresses a', 'click', function (e) {
                e.preventDefault()
                $(this).tab('show')
            })
        });
    </script>
{/if}