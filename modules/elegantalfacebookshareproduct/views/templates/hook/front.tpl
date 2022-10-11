{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2018, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
{if $display_type == 'popup'}
    <div id="elegantal_share_popup" class="elegantal-popup">
        <div class="elegantal-popup-inner">
            <div class="elegantal-fb-header">
                {if $fb_event == 'like'}
                    {l s='Like us on Facebook to receive a bonus gift...' mod='elegantalfacebookshareproduct'}
                {else}
                    {l s='Share us on Facebook to receive a bonus gift...' mod='elegantalfacebookshareproduct'}
                {/if}
            </div>
            <div style="text-align: center; padding: 20px 0;">
                {if $fb_event == 'like'}
                    <div class="fb-like" 
                         data-href="{$url_to_share|escape:'html':'UTF-8'}" 
                         data-width="200" 
                         data-layout="standard" 
                         data-action="like" 
                         data-size="large"
                         data-show-faces="false" 
                         data-share="false" 
                         style="display: inline-block"></div>
                {else}
                    <button class="btn btn-default btn-lg elegantal-share-btn elegantal-fbshare4gift-btn">
                        <i class="icon-gift"></i> {l s='Access the bonus gift now' mod='elegantalfacebookshareproduct'}
                    </button>
                {/if}
            </div>
            <a class="elegantal-popup-close elegantal-popup-close-x" href="#">x</a>
        </div>
    </div>
{else}
    {if $fb_event == 'like'}
        <div class="fb-like" 
             data-href="{$url_to_share|escape:'html':'UTF-8'}" 
             data-width="200" 
             data-layout="standard" 
             data-action="like" 
             data-size="large"
             data-show-faces="false" 
             data-share="false" 
             style="display: inline-block"></div>
    {else}
        <button class="btn btn-info elegantal-fbshare4gift-btn">
            <i class="icon-gift"></i> {l s='Share us on Facebook and get a gift' mod='elegantalfacebookshareproduct'}
        </button>
    {/if}

{/if}

{if $cart_rules|@count gt 1}
    <div id="elegantal_select_gift_popup" class="elegantal-popup" style="display: none">
        <div class="elegantal-popup-inner">
            <p class="elegantal_select_gift_header">
                {l s='Choose your gift' mod='elegantalfacebookshareproduct'}
            </p>
            <table>
                <tbody>
                    {foreach from=$cart_rules item=cart_rule}
                        <tr>
                            <td>
                                {if $cart_rule.product_name && $cart_rule.product_image}
                                    <img src="{$cart_rule.product_image|escape:'html':'UTF-8'}" alt=""> 
                                    {$cart_rule.product_name|escape:'html':'UTF-8'}
                                {/if}
                                {if $cart_rule.free_shipping}
                                    - {l s='FREE SHIPPING' mod='elegantalfacebookshareproduct'}
                                {/if}
                                {if $cart_rule.reduction_percent > 0}
                                    - {$cart_rule.reduction_percent|escape:'html':'UTF-8'} % 
                                    {l s='OFF' mod='elegantalfacebookshareproduct'}
                                {/if}
                                {if $cart_rule.reduction_amount > 0}
                                    - {$cart_rule.reduction_amount|escape:'html':'UTF-8'} 
                                    {$cart_rule.reduction_currency|escape:'html':'UTF-8'} 
                                    {l s='OFF' mod='elegantalfacebookshareproduct'}
                                {/if}
                            </td>
                            <td style="text-align:center">
                                <div class="radio">
                                    <span>
                                        <input type="radio" name="cart_rule" value="{$cart_rule.cart_rule_id|intval}">
                                    </span>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
            <p style="text-align: center; padding-top: 20px;">
                <button class="btn btn-default btn-lg elegantal-share-btn elegantal-fbchoosegift-btn">
                    <i class="icon-gift"></i> {l s='Get the bonus gift now' mod='elegantalfacebookshareproduct'}
                </button>
            </p>
            <a class="elegantal-popup-close elegantal-popup-close-x" href="#">x</a>
        </div>
    </div>
{/if}

<div id="elegantalfacebookshareproductJsDef" class="elegantal_hidden" data-fbappid="{$fb_app_id|escape:'html':'UTF-8'}" data-fbevent="{$fb_event|escape:'html':'UTF-8'}" data-shareurl="{$url_to_share|escape:'html':'UTF-8'}" data-action="{$process_action_url|escape:'html':'UTF-8'}" data-locale="{$locale|escape:'html':'UTF-8'}" data-debug="{$debug_mode|intval}"></div>


