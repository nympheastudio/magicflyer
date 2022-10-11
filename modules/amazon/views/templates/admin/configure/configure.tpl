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
<fieldset>
    <form action="{$configure.form_action|escape:'htmlall':'UTF-8'}" method="post" autocomplete="off" id="amazon_form">
        <input type="hidden" id="id_lang" value="{$configure.id_lang|escape:'quotes':'UTF-8'}"/>
        <input type="hidden" id="check_url" value="{$configure.check_url|escape:'quotes':'UTF-8'}"/>
        <input type="hidden" id="check_msg_region" value="{$configure.check_msg_region|escape:'quotes':'UTF-8'}"/>
        <input type="hidden" id="check_msg_currency" value="{$configure.check_msg_currency|escape:'quotes':'UTF-8'}"/>
        <input type="hidden" name="selected_tab" value="{$configure.selected_tab|escape:'quotes':'UTF-8'}"/>

        <div class="cleaner"></div>
        <!-- div tabList -->
        <div id="tabList">
            {foreach from=$configure.tabs key=index item=tab}
                {include file="$tab"}
            {/foreach}
        </div>
        <!-- div tabList end -->
</fieldset>
<div class="form-group" style="clear:both;">&nbsp;</div>