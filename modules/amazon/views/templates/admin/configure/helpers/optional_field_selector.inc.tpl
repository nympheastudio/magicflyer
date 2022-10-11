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

<div class="optional-field-selector" rel="{$field|escape:'javascript':'UTF-8'}">
    <img src="{$images_url|escape:'htmlall':'UTF-8'}/plus.png" title="{l s='Add this attribute' mod='amazon'}" class="add-optional" />
    {if (strlen($tip) || strlen($tip2) || strlen($sample))}
        <details closed>
            <summary>
                {if strlen($long_title)}
                    {$long_title|escape:'javascript':'UTF-8'}
                {else}
                    {$title|escape:'javascript':'UTF-8'}
                {/if}

            </summary>
            <ul>
                {if (strlen($tip2))}
                    <li>{$tip2|escape:'javascript':'UTF-8'}</li>
                {/if}

                {if (strlen($sample))}
                    <li>{$sample|escape:'javascript':'UTF-8'}</li>
                {/if}
                {if (strlen($tip))}
                    <li>{$tip|escape:'javascript':'UTF-8'}</li>
                {/if}
            </ul>
        </details>
    {else}
        <div class="no-details">
        <p>
            {if strlen($long_title)}
                {$long_title|escape:'javascript':'UTF-8'}
            {else}
                {$title|escape:'javascript':'UTF-8'}
            {/if}

        </p>
       </div>
    {/if}
    <input type="hidden" class="field-name" value="{$field|escape:'javascript':'UTF-8'}">
</div>