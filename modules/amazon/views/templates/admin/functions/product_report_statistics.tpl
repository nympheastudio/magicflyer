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

<p>
    <b>{$productReportText1|escape:'html':'UTF-8'}</b>: {$productReportText2|escape:'html':'UTF-8'}
    <br/><br/>
    <b>{$productReportText3|escape:'html':'UTF-8'}</b>:
</p>

{if $productReportStatistics|@count gt 0}
    <table style="width:100%">
        <tr style="font-style:italic">
            <td width="40%">{$productReportText4|escape:'html':'UTF-8'}</td>
            <td>{$productReportText5|escape:'html':'UTF-8'}</td>
            <td>{$productReportText6|escape:'html':'UTF-8'}</td>
            <td>{$productReportText7|escape:'html':'UTF-8'}</td>
            <td>{$productReportText8|escape:'html':'UTF-8'}</td>
            <td>{$productReportText9|escape:'html':'UTF-8'}</td>
        </tr>
        {foreach from=$productReportStatistics key=k item=statistic}
            <tr>
                <td>Amazon {$statistic.region|escape:'htmlall':'UTF-8'}</td>
                <td>{$statistic.type|escape:'htmlall':'UTF-8'}</td>
                <td>{$statistic.mode|escape:'htmlall':'UTF-8'}</td>
                <td>{$statistic.timestart|escape:'htmlall':'UTF-8'}</td>
                <td>{$statistic.records|escape:'htmlall':'UTF-8'}</td>
                <td>{$statistic.duration|escape:'htmlall':'UTF-8'}</td>
            </tr>
        {/foreach}
    </table>
{else}
    <span style="color:brown;font-weight:bold;">{$productReportText10|escape:'html':'UTF-8'}</span>
{/if}
