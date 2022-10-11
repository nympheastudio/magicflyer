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

<script type="text/javascript">
    var requester="{$data.support_requester|escape:'html':'UTF-8'}";
    var support_subject="{$data.support_subject|escape:'html':'UTF-8'}";
    var support_ps_version="{$data.support_ps_version|escape:'html':'UTF-8'}";
    var support_module_version="{$data.support_module_version|escape:'html':'UTF-8'}";
    var support_site="{$data.support_site|escape:'html':'UTF-8'}";
    var support_product=parseInt({$data.support_product|escape:'html':'UTF-8'});
    var support_url="{$data.support_url|escape:'html':'UTF-8'}";

    {literal}
    FreshWidget.init("", {"queryString":
    "&widgetType=popup&searchArea=no&helpdesk_ticket[requester]="+requester
    +"&helpdesk_ticket[subject]="+support_subject
    +"&helpdesk_ticket[product]="+support_product
    +"&helpdesk_ticket[custom_field][version_prestashop_191912]="+support_ps_version
    +"&helpdesk_ticket[custom_field][version_module_191912]="+support_module_version
    +"&helpdesk_ticket[custom_field][site_191912]="+support_site,
        "utf8": "✓", "widgetType": "popup", "buttonType": "text", "buttonText": "Common-Services / Support", "buttonColor": "white", "buttonBg": "#484848", "alignment": "3", "offset": "80%", "formHeight": "650px", "url": support_url} );
    {/literal}
</script>