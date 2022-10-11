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
{foreach from=$details item=detail}
    {if isset($detail.marketplace_detail.customization) && $detail.marketplace_detail.customization}
        <p class="marketplace_detail hide" data-value="{$detail.id_order_detail|escape:'htmlall':'UTF-8'}">
            <b>Customizations:</b><br>
            {foreach from=$detail.marketplace_detail.customization item=customization}
                <span>{$customization.label|escape:'htmlall':'UTF-8'}: {$customization.optionValue|escape:'htmlall':'UTF-8'}</span><br>
            {/foreach}
        </p>
    {/if}
{/foreach}