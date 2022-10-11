{**
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
 * @package   Amazon Market Place
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * Support by mail:  support.amazon@common-services.com
*}
<input type="hidden" id="amazon-automaton-matching-killme" value="0"/>
<a id="amazon-automaton-matching-window-open" href="#amazon-automaton-matching-window"
   title="{l s='Product Matching - Automatic Discovery Wizard' mod='amazon'}"></a>
<div id="amazon-overlay" style="display: none;"></div>

<div id="amazon-automaton-matching-window" style="display:none">
    <div id="amazon-automaton-matching-title">
        <span>{l s='Product Matching Wizard - Create new offers on Amazon' mod='amazon'}</span></div>
    <div id="amazon-automaton-matching-header">
        <a href="javascript:;" id="amazon-automaton-matching-close" style="float:right">[ {l s='Close' mod='amazon'}
            ]</a>
        <br/>
        <table width="760">
            <tr>
                <td>
                    <fieldset id="amazon-automaton-matching-options">
                        <legend>{l s='Options' mod='amazon'}</legend>
                        <div>
                            <input type="checkbox" id="matching-load-image"
                                   checked="checked">&nbsp;{l s='Load Images' mod='amazon'}&nbsp;&nbsp;&nbsp;
                            <input type="checkbox" id="matching-display-unmatched"
                                   checked="checked">&nbsp;{l s='Display Unmatched' mod='amazon'}&nbsp;&nbsp;&nbsp;
                            <input type="checkbox" id="matching-display-selectall">&nbsp;{l s='Select All' mod='amazon'}
                            &nbsp;&nbsp;&nbsp;
                        </div>
                    </fieldset>
                </td>

                <td>
                    <fieldset id="amazon-automaton-matching-actions">
                        <legend>{l s='Actions' mod='amazon'}</legend>

                        <a id="amazon-automaton-matching-action-confirm"
                           class="amazon-automaton-matching-action-button inactive" href="#"
                           title="{l s='Confirm' mod='amazon'}">
                            <span class="amazon-automaton-matching-action-icon"></span>

                            <div>{l s='Confirm' mod='amazon'}</div>
                        </a>

                        <a id="amazon-automaton-matching-action-reject"
                           class="amazon-automaton-matching-action-button inactive" href="#"
                           title="{l s='Reject' mod='amazon'}">
                            <span class="amazon-automaton-matching-action-icon amazon-automaton-matching-action-reject-icon"></span>

                            <div>{l s='Reject' mod='amazon'}</div>
                        </a>

                    </fieldset>
                </td>

            </tr>
        </table>
        <hr/>
    </div>

    <div id="amazon-automaton-matching-product-model" class="amazon-automaton-matching-product" style="display:none">
        <div class="selection selectable">
            <table class="matching-product-left">
                <tr>
                    <td rel="image" class="image">
                        <img src="{$images|escape:'htmlall':'UTF-8'}loader.gif" alt="{l s='Loading' mod='amazon'}"
                             rel="loader"
                             style="height:16px"/>
                        <img src="{$images|escape:'htmlall':'UTF-8'}no-image.png" alt="{l s='Loading' mod='amazon'}"
                             rel="nope"
                             style="height:64px;display:none"/>
                    </td>
                    <td class="content">
                        <span rel="name" class="name"></span><br/>
                        <span rel="manufacturer" class="manufacturer"></span> | <span rel="reference"
                                                                                      class="reference"></span> | <span
                                rel="code" class="code"></span>

                    </td>
                    <td>
                        <img src="{$images|escape:'htmlall':'UTF-8'}warning.png" alt="{l s='Mismatch' mod='amazon'}"
                             class="mismatch"
                             title="{l s='Brand Mismatch !' mod='amazon'}"/>
                    </td>
                </tr>
            </table>

            <table class="matching-product-right">
                <tr>
                    <td class="content">
                        <span rel="amazon_name" class="name"></span><br/>
                        <span rel="amazon_brand" class="brand"></span> | <span rel="amazon_asin" class="asin"></span> |
                        <span rel="code" class="code"></span>
                    </td>
                    <td rel="amazon_image" class="image">
                        <img src="{$images|escape:'htmlall':'UTF-8'}loader.gif" alt="{l s='Loading' mod='amazon'}"
                             rel="loader"
                             style="height:16px"/>
                        <img src="{$images|escape:'htmlall':'UTF-8'}no-image.png" alt="{l s='Loading' mod='amazon'}"
                             rel="nope"
                             style="height:64px;display:none"/>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div id="amazon-automaton-matching-products-loader" class="amazon-automaton-matching-products-loader">
        <img src="{$images|escape:'htmlall':'UTF-8'}loader-connection.gif" alt="{l s='Loading' mod='amazon'}"/>
    </div>

    <div id="amazon-automaton-matching-body">
        <table width="50%" class="amazon-automaton-matching-body-header">
            <tr>
                <td><img src="{$images|escape:'htmlall':'UTF-8'}house.png" alt="{l s='Shop' mod='amazon'}"/>
                <td>{l s='Offers on store side, but not in your inventory on Amazon' mod='amazon'}:</td>
            </tr>
        </table>
        <table width="50%" class="amazon-automaton-matching-body-header">
            <tr>
                <td><img src="{$images|escape:'htmlall':'UTF-8'}a32.png" alt="{l s='Amazon' mod='amazon'}"/>
                <td>{l s='Matching products on Amazon which allows you to create an offer' mod='amazon'}:</td>
            </tr>
        </table>

        <!-- Product Matching Container -->
        <div id="amazon-automaton-matching-products"></div>
    </div>
</div>                