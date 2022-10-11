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
    <fieldset>
        {if isset($show_country_selector) && $show_country_selector}
            {if $psIs16}
            <h3>{else}
                <legend>{/if}<img src="{$images|escape:'htmlall':'UTF-8'}world.gif" alt=""
                                  class="middle"/> {l s='Choose Amazon Platform' mod='amazon'}{if $psIs16}
            </h3>{else}</legend>{/if}
            {if $europeEuroArea}
                <fieldset class="country-fieldset">
                    {if $psIs16}
                    <h3>{else}
                        <legend style="font-size:11px">{/if}{l s='Europe, Euro Area' mod='amazon'}{if $psIs16}
                    </h3>{else}</legend>{/if}


                    <table class="country-selector">
                        <tr>
                            <td>
                                <img src="{$europe_flag|escape:'htmlall':'UTF-8'}" alt="{l s='Euro Area' mod='amazon'}"
                                     onclick="$('input[name=amazon_lang][value=europe]').trigger('click');"/>
                            </td>

                            {foreach from=$marketplacesEuro item=marketplace}
                                <td>
                                    <img src="{$marketplace.image|escape:'htmlall':'UTF-8'}" alt="{$marketplace.name|escape:'htmlall':'UTF-8'}"
                                         onclick="$('input[name=amazon_lang][value=europe]').trigger('click');"/>
                                </td>
                            {/foreach}
                        <tr>
                            <td>
                                <input type="radio" name="amazon_lang" value="europe" rel="1"/>
                            </td>

                            {foreach from=$marketplacesEuro item=marketplace}
                                <td>
                                    &nbsp;
                                </td>
                            {/foreach}
                        </tr>
                        <tr>
                            <td>
                                <span class="name">{l s='Euro Area' mod='amazon'}</span>
                            </td>

                            {foreach from=$marketplacesEuro item=marketplace}
                                <td>
                                    <span class="name"
                                          onclick="$('input[name=amazon_lang][value=europe]').trigger('click');">{$marketplace.name|escape:'htmlall':'UTF-8'}</span>
                                </td>
                            {/foreach}
                        </tr>
                    </table>
                </fieldset>
            {/if}

            {if $europeNotEuroArea}
            <fieldset class="country-fieldset">
                    {if $psIs16}<h3>{else}
                        <legend style="font-size:11px">
                    {/if}{l s='Any Platforms' mod='amazon'}{if $psIs16}</h3>{else}</legend>{/if}
                    <table class="country-selector">
                        <tr>
                            {foreach from=$marketplacesNotEuro item=marketplace}
                                <td>
                                    <img src="{$marketplace.image|escape:'htmlall':'UTF-8'}" alt="{$marketplace.name|escape:'htmlall':'UTF-8'}"
                                         onclick="$('input[name=amazon_lang][value={$marketplace.id_lang|intval}]').trigger('click');"/>
                                </td>
                            {/foreach}
                        </tr>
                        <tr>
                            {foreach from=$marketplacesNotEuro item=marketplace}
                                <td>
                                    <input type="radio" name="amazon_lang" value="{$marketplace.id_lang|intval}" rel="1"/>
                                </td>
                            {/foreach}
                        </tr>
                        <tr>
                            {foreach from=$marketplacesNotEuro item=marketplace}
                                <td>
                                    <span class="name"
                                          onclick="$('input[name=amazon_lang][value={$marketplace.id_lang|intval}]').trigger('click');">{$marketplace.name|escape:'htmlall':'UTF-8'}</span>
                                </td>
                            {/foreach}
                        </tr>
                    </table>
            </fieldset>
            {/if}
        {else}
            {if $europeEuroArea}
                {foreach from=$marketplacesEuro item=marketplace}
                    <input type="radio" name="amazon_lang" value="{$marketplace.id_lang|intval}" rel="1" checked style="display:none" />
                {/foreach}
            {elseif $europeNotEuroArea}
                {foreach from=$marketplacesNotEuro item=marketplace}
                    <input type="radio" name="amazon_lang" value="{$marketplace.id_lang|intval}" rel="1" checked style="display:none" />
                {/foreach}
            {/if}
        {/if}
    </fieldset>
</form>  
    