{*
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
* @copyright 2010-2020 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

{if $searchResults != false}
    {foreach $searchResults AS $result}
        <div class="col-lg-12">
            <label>
                {$result.attribute_group}: {$result.attribute_value}
            </label>
            <div class="btn btn-default resultAddAttributeValue" data-id="{$result.id_attribute}">
                {l s='Select' mod='cartcon'}
            </div>
        </div>
    {/foreach}
{else}
    <div class="alert alert-warning">
        {l s='No search results' mod='cartcon'}
    </div>
{/if}