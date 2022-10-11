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

<span class="submission-result-title">{$feedSubmissionResultTitle|escape:'html':'UTF-8'}</span><br />

{if isset($feedSubmissionResultSummary)}
    <table class="submission-results-table">
        <thead>
        <tr>
            <th>{$feedSubmissionResultText1|escape:'html':'UTF-8'}</th>
            <th>{$feedSubmissionResultText2|escape:'html':'UTF-8'}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="col1">{$feedSubmissionResultText3|escape:'html':'UTF-8'}</td>
            <td style="font-weight:bold;">{$feedSubmissionResultSummary->MessagesProcessed|escape:'html'}</td>
        <tr>
        </tr>
        <tr>
            <td class="col1">{$feedSubmissionResultText4|escape:'html':'UTF-8'}</td>
            <td style="color:#1D910F;font-weight:bold;">{$feedSubmissionResultSummary->MessagesSuccessful|escape:'html'}</td>
        </tr>
        <tr>
            <td class="col1">{$feedSubmissionResultText5|escape:'html':'UTF-8'}</td>
            <td style="color:#FD0000;font-weight:bold;">{$feedSubmissionResultSummary->MessagesWithError|escape:'html'}</td>
        </tr>
        <tr>
            <td class="col1">{$feedSubmissionResultText6|escape:'html':'UTF-8'}</td>
            <td style="color:#FFA300;font-weight:bold;">{$feedSubmissionResultSummary->MessagesWithWarning|escape:'html'}</td>
        </tr>
        <tr>
            <td class="col1">{$feedSubmissionResultText7|escape:'html':'UTF-8'}</td>
            <td>{$feedSubmissionResultTextError}{* Validation: can't escape *}</td>
        </tr>
        </tbody>
    </table>
{elseif isset($feedSubmissionResultIsError)}
    <table class="submission-results-table">
        <thead>
        <tr>
            <th>{$feedSubmissionResultText1|escape:'html':'UTF-8'}</th>
            <th>{$feedSubmissionResultText2|escape:'html':'UTF-8'}</th>
        </tr>
        </thead>
        <tr>
            <td class="col1">{$feedSubmissionResultText8|escape:'html':'UTF-8'}</td>
            <td style="color:#FFA300;font-weight:bold;">{$feedSubmissionResultObject->Error->Code|escape:'html'}</td>
        </tr>
        <tbody>
        <tr>
            <td class="col1">{$feedSubmissionResultText7|escape:'html':'UTF-8'}</td>
            <td>{$feedSubmissionResultTextError}{* Validation: can't escape *}</td>
        </tr>
        {if isset($feedSubmissionResultIsNotReady)}
            <tr>
                <td>&nbsp;</td>
                <td class="interpretation"><b>{$feedSubmissionResultText9|escape:'html':'UTF-8'}</b><br/>{$feedSubmissionResultText10|escape:'html':'UTF-8'}</td>
            </tr>
        {/if}
        </tbody>
    </table>
{elseif isset($feedSubmissionResultObjectText)}
    {$feedSubmissionResultObjectText|escape:'html'}
{/if}

<br /><hr /><br />