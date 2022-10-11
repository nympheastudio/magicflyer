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

<div id="lecabflash_order_detail" class="info-order box">
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

        .lecablfash_blue_btn {
            color: #00aff0;
            text-decoration: none;
        }

        .lecablfash_blue_btn:hover, .lecablfash_blue_btn:focus {
            color: #0077a4;
            text-decoration: underline;
        }
    </style>

    <div class="row">
        <div class="col-md-6">
            <div class="lecab__logo" >
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-lecab.png" alt="{l s='LeCab' mod='lecabflash'}">
            </div>

            <p><strong class="dark">{l s='Tracking number' mod='lecabflash'}</strong><a class="lecablfash_blue_btn" href="{$lecabflash.confirm_url|escape:'htmlall':'UTF-8'}" target="_blank">{$lecabflash.confirm_number|escape:'htmlall':'UTF-8'}</a><p>
            {if (!$lecabflash.confirm_id)}
                <div class="alert">
                    <p><strong class="dark">{l s='Your order could not be completed, please contact LeCabFlash' mod='lecabflash'}</strong></p>
                </div>
            {/if}
            <p><strong class="dark">{l s='Date and hour of delivery' mod='lecabflash'}</strong> : {$drop_date_translated|escape:'htmlall':'UTF-8'}<p>
        </div>

        <div class="col-md-6">
            <div class="embed-responsive embed-responsive-4by3">
                <iframe class="embed-responsive-item" src="{$lecabflash.confirm_url|escape:'htmlall':'UTF-8'}"> </iframe>
            </div>
        </div>
    </div>
</div>
