{*
 *
 * 2009-2017 202 ecommerce
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
 *  @author    202 ecommerce <support@202-ecommerce.com>
 *  @copyright 2009-2017 202 ecommerce SARL
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 *}

<div id="lecabflash_order_detail" class="panel">
    <style>
        #lecabflash_order_detail iframe {
            width: 100%;
            height: 330px;
            border: solid 1px #ccc;
        }
        #lecabflash_order_detail .lecab__logo {
            width: 200px;
            margin-left: 1em;
            margin-bottom: 1em;
        }
    </style>

    <div class="row">
        <div class="col-md-6">
            <div class="lecab__logo" >
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-lecab.png" alt="{l s='LeCab' mod='lecabflash'}">
            </div>
            <p><strong class="dark">{l s='Tracking number' mod='lecabflash'}</strong> <a href="{$lecabflash.confirm_url|escape:'htmlall':'UTF-8'}" target="_blank">{$lecabflash.confirm_number|escape:'htmlall':'UTF-8'}</a><p>

            {if (!$lecabflash.confirm_id)}
                <div class="alert">
                    <p><strong class="dark">{l s='Your order could not be completed, please contact LeCabFlash' mod='lecabflash'}</strong> </p>
                </div>
            {/if}

            <p><strong class="dark">{l s='Date and hour of pick-up' mod='lecabflash'}</strong> {$pickup_date_translated|escape:'htmlall':'UTF-8'}<p>
            <p><strong class="dark">{l s='Date and hour of drop' mod='lecabflash'}</strong> {$drop_date_translated|escape:'htmlall':'UTF-8'}<p>
            
            <p><a href="#showdebuglacab" data-toggle="collapse">Informations Techniques</a></p>
            <div id="showdebuglacab" class="collapse">
                <h6>{l s='estimate_context' mod='lecabflash'}estimate_context</h6>
                <code>{$lecabflash.estimate_context|escape:'htmlall':'UTF-8'}</code>
                <h6>{l s='estimate_response' mod='lecabflash'}</h6>
                <code>{$lecabflash.estimate_response|escape:'htmlall':'UTF-8'}</code>
                <h6>{l s='confirm_request' mod='lecabflash'}</h6>
                <code>{$lecabflash.confirm_request|escape:'htmlall':'UTF-8'}</code>
                <h6>{l s='confirm_response' mod='lecabflash'}</h6>
                <code>{$lecabflash.confirm_response|escape:'htmlall':'UTF-8'}</code>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="embed-responsive embed-responsive-4by3">
                <iframe class="embed-responsive-item" src="{$lecabflash.confirm_url|escape:'htmlall':'UTF-8'}"> </iframe>
            </div>
        </div>
    </div>
</div>
