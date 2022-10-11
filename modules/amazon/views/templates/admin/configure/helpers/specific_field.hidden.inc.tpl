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
{if isset($data.comment)}
    <!-- {$data.comment|escape:'quotes':'UTF-8'} -->
{/if}
<input type="hidden" name="{$data.name|escape:'quotes':'UTF-8'}"
       {if isset($data.class)}class="{$data.class|escape:'quotes':'UTF-8'}"{/if}
        {if isset($data.disabled)}{$data.disabled|escape:'quotes':'UTF-8'}{/if}
        {if isset($data.rel)}rel="{$data.rel|escape:'quotes':'UTF-8'}"{/if}
       value="{$data.value|escape:'quotes':'UTF-8'}"/>