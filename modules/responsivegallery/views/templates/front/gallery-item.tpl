{*
 *@license
*}
<li data-title="{$item.legend|strip_tags|escape:'html':'UTF-8'}" data-legend-hover="{$item.legend_on_hover|escape:'intval'}" class="page-{$page|intval}">
    <a href="{$item.link|escape:'html':'UTF-8'}"
    {if $item.link == ''}
        class="fancy-group" rel="group"
    {/if}
        title="{$item.legend|escape:'html':'UTF-8'}">
        <img src="{$ps_base_uri|escape:'html':'UTF-8'}{$item.image|escape:'html':'UTF-8'}" alt="{$item.legend|strip_tags|escape:'html':'UTF-8'}"/>
    </a>
</li>