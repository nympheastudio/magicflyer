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

{foreach from=$header.scripts item=script_url}
    <script type="text/javascript"
            src="{$script_url|escape:'htmlall':'UTF-8'}?version={$header.version|escape:'htmlall':'UTF-8'}"></script>
{/foreach}

<script type="text/javascript" src="{$header.module_url|escape:'htmlall':'UTF-8'}views/js/jquery.qtip.js"></script>

{if !$psIs14}
    <script type="text/javascript" src="{$header.module_url|escape:'htmlall':'UTF-8'}views/js/jquery.tagify.js"></script>
{/if}

{if $header.widget}
    {* Support Widget*}
    <script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
    {include file="{$header.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/support/widget.tpl" data=$header['widget_data']}
    {* End of Support Widget*}
{/if}

<link href="{$header.module_url|escape:'htmlall':'UTF-8'}views/css/profiles.css?version={$header.version|escape:'htmlall':'UTF-8'}"
      rel="stylesheet" type="text/css"/>
<link href="{$header.module_url|escape:'htmlall':'UTF-8'}views/css/jquery.qtip.css" rel="stylesheet" type="text/css"/>

{if !$psIs16}
    <link href="{$header.module_url|escape:'htmlall':'UTF-8'}views/css/tagify.css" rel="stylesheet" type="text/css"/>
{/if}

{if $psIs16}
    {*<link href="{$header.module_url|escape:'htmlall':'UTF-8'}views/css/amazon16.css?version={$header.version|escape:'htmlall':'UTF-8'}"
          rel="stylesheet" type="text/css"/>*}
{else}
    <link rel="stylesheet" type="text/css" href="{$header.module_url|escape:'htmlall':'UTF-8'}views/css/chosen.min.css"/>
    <script type="text/javascript" src="{$header.module_url|escape:'htmlall':'UTF-8'}views/js/chosen.jquery.min.js"></script>
    <link href="{$header.module_url|escape:'quotes':'UTF-8'}views/css/amazon.css?version={$header.version|escape:'htmlall':'UTF-8'}"
          rel="stylesheet" type="text/css"/>
{/if}


<!-- error -->
{if $header.error}
    <div class="error alert alert-danger">{$header.error_content|escape:'javascript':'UTF-8'}</div>
{/if}

<!-- warnings -->
{if $header.warning}
    <div class="warn alert alert-warning">{$header.warning_content|escape:'javascript':'UTF-8'}</div>
{/if}

<!-- debug inforations -->
{if $header.debug}
    <div class="conf warn alert alert-warning">DEBUG:<br/>{$header.debug_content|escape:'javascript':'UTF-8'}</div>
{/if}


<!-- header -->
<div style="padding:10px 0 {if $psIs16}40px{else}10px{/if} 0">
    <div class="col-lg-6" style="float:left;position:relative;">
        <img src="{$header.images_url|escape:'htmlall':'UTF-8'}common-services_48px.png"
             alt="{l s='Common-Services' mod='amazon'}"/>
    </div>
    <div class="col-lg-6" style="float:right">
        <img src="{$header.images_url|escape:'htmlall':'UTF-8'}amazon.png" alt="{l s='Amazon Marketplace' mod='amazon'}"
             style="float: right;"/>
    </div>

    <div class="cleaner">&nbsp;</div>
</div>

<br/>
<!-- end of header -->