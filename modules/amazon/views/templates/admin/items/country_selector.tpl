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
<form action="#" id="country-selector" name="country-selector" method="POST">
    {if isset($show_country_selector) && $show_country_selector}
        <fieldset>
            {if $psIs16}
            <h3>{else}
                <legend>{/if}<img src="{$images|escape:'quotes':'UTF-8'}world.gif" alt=""
                                  class="middle"/> {l s='Choose Amazon Platform' mod='amazon'}{if $psIs16}
            </h3>
            {else}</legend>{/if}

            <table class="country-selector">
                <tr>
                    {foreach from=$marketplaces item=marketplace}
                        <td>
                            <img src="{$marketplace.image|escape:'quotes':'UTF-8'}"
                                 alt="{$marketplace.name|escape:'quotes':'UTF-8'}"
                                 onclick="$('input[name=amazon_lang][value={$marketplace.id_lang|intval}]').trigger('click');"/>
                        </td>
                    {/foreach}
                </tr>
                <tr>
                    {foreach from=$marketplaces item=marketplace}
                        <td>
                            <input type="radio" name="amazon_lang" value="{$marketplace.id_lang|intval}" rel="1"/>
                        </td>
                    {/foreach}
                </tr>
                <tr>
                    {foreach from=$marketplaces item=marketplace}
                        <td>
                        <span class="name"
                              onclick="$('input[name=amazon_lang][value={$marketplace.id_lang|intval}]').trigger('click');">{$marketplace.name|escape:'quotes':'UTF-8'}</span>
                        </td>
                    {/foreach}
                </tr>

            </table>

        </fieldset>
    {else}
        {foreach from=$marketplaces item=marketplace}
            <input type="radio" name="amazon_lang" value="{$marketplace.id_lang|intval}" rel="1" style="display:none;"/>
        {/foreach}
    {/if}
</form>
