<div>
    {if $debug_status > 0}
        {if $prestashop_version == 16}<p class="alert alert-warning">{/if}
        {if $prestashop_version == 15}<p class="warning">{/if}
            {l s='DEBUG FREE SHIPPING' mod='lgfreeshippingzones'}<BR>
            ---<br>
            {l s='Carrier ID' mod='lgfreeshippingzones'}: {if $id_carrier < 1}{l s='undefined' mod='lgfreeshippingzones'}{/if}{if $id_carrier >= 1}{$id_carrier|escape:'html':'UTF-8'}{/if}<br>
            {l s='Carrier ref' mod='lgfreeshippingzones'}: {if $ref_carrier < 1}{l s='undefined' mod='lgfreeshippingzones'}{/if}{if $ref_carrier >= 1}{$ref_carrier|escape:'html':'UTF-8'}{/if}<br>
            {l s='Carrier name' mod='lgfreeshippingzones'}: {if $ref_carrier < 1}{l s='undefined' mod='lgfreeshippingzones'}{/if}{if $ref_carrier >= 1}{$name_carrier|escape:'html':'UTF-8'}{/if}<br>
            {l s='Zone ID' mod='lgfreeshippingzones'}: {if $id_zone < 1}{l s='undefined' mod='lgfreeshippingzones'}{/if}{if $id_zone >= 1}{$id_zone|escape:'html':'UTF-8'}{/if}<br>
            {l s='Zone name' mod='lgfreeshippingzones'}: {if $id_zone < 1}{l s='undefined' mod='lgfreeshippingzones'}{/if}{if $id_zone >= 1}{$name_zone|escape:'html':'UTF-8'}{/if}<br>
            {l s='Shop ID' mod='lgfreeshippingzones'}: {if $id_shop < 1}{l s='undefined' mod='lgfreeshippingzones'}{/if}{if $id_shop >= 1}{$id_shop|escape:'html':'UTF-8'}{/if}<br>
            ---<br>
            {l s='Total cart price' mod='lgfreeshippingzones'} : {displayPrice price=$cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING)}<br>
            {if $price_tax == 1}{l s='TAX included' mod='lgfreeshippingzones'}{/if}{if $price_tax === 0}{l s='TAX excluded' mod='lgfreeshippingzones'}{/if}<br>
            {if $price1 != false}{l s='Minimum price' mod='lgfreeshippingzones'}: {displayPrice price=$price1}<br>{/if}
            {if $price2 != false}{l s='Maximum price' mod='lgfreeshippingzones'}: {displayPrice price=$price2}<br>{/if}
            ---<br>
            {l s='Total cart weight' mod='lgfreeshippingzones'}: {$cart->getTotalWeight()|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'}<br>
            {if $weight2 != false}{l s='Minimum weight' mod='lgfreeshippingzones'}: {$weight1|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'}<br>{/if}
            {if $weight2 != false}{l s='Maximum weight' mod='lgfreeshippingzones'}: {$weight2|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'}{/if}<br>
            ---<br>
            {if $shipping_cost > 0}{l s='SHIPPING NOT FREE' mod='lgfreeshippingzones'}:<br>
                {if $price1 === 0 and $price2 === 0 and $weight1 === 0 and $weight2 === 0}{l s='Free shipping is not configured for the current zone and carrier (check the free shipping module configuration)' mod='lgfreeshippingzones'}<br>{/if}
                {if $price1 > 0 and $cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING) < $price1}{l s='Minimum price not reached' mod='lgfreeshippingzones'}: {displayPrice price=$cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING)} < {displayPrice price=$price1}<br>{/if}
                {if $weight1 > 0 and $cart->getTotalWeight() < $weight1}{l s='Minimum weight not reached' mod='lgfreeshippingzones'}: {$cart->getTotalWeight()|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'} < {$weight1|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'}<br>{/if}
                {if $price2 > 0 and $cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING) > $price2}{l s='Maximum price exceeded' mod='lgfreeshippingzones'}: {displayPrice price=$cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING)} > {displayPrice price=$price2}<br>{/if}
                {if $weight2 > 0 and $cart->getTotalWeight() > $weight2}{l s='Maximum weight exceeded' mod='lgfreeshippingzones'}: {$cart->getTotalWeight()|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'} > {$weight2|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'}<br>{/if}
            {/if}
            {if $shipping_cost === 0}{l s='FREE SHIPPING' mod='lgfreeshippingzones'}:<br>
                {if $is_free > 0}{l s='Free shipping is permanently enabled for this carrier (check your carrier configuration)' mod='lgfreeshippingzones'}<br>
                {else}
                    {if $price1 > 0 and $cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING) > $price1}{l s='Minimum price reached' mod='lgfreeshippingzones'}: {displayPrice price=$cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING)} > {displayPrice price=$price1}<br>{/if}
                    {if $price2 > 0 and $cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING) < $price2}{l s='Maximum price not exceeded' mod='lgfreeshippingzones'}: {displayPrice price=$cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING)} < {displayPrice price=$price2}<br>{/if}
                    {if $weight1 > 0 and $cart->getTotalWeight() > $weight1}{l s='Minimum weight reached' mod='lgfreeshippingzones'}: {$cart->getTotalWeight()|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'} > {$weight1|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'}<br>{/if}
                    {if $weight2 > 0 and $cart->getTotalWeight() < $weight2}{l s='Maximum weight not exceeded' mod='lgfreeshippingzones'}: {$cart->getTotalWeight()|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'} < {$weight2|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'}<br>{/if}
                {/if}
            {/if}
        </p>
    {/if}
    {if $left_message > 0}
        {if $shipping_cost > 0 and $price1 > 0 and $cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING) <= $price1}
            {if $prestashop_version == 16}<!-- <p class="alert alert-warning"> -->{/if}
            {if $prestashop_version == 15}<p class="warning">{/if}
                {l s='Spend' mod='lgfreeshippingzones'} {displayPrice price=$price1-$cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING)} {l s='more to get free shipping' mod='lgfreeshippingzones'} <!-- <a class="button btn btn-default" onClick="window.location.reload()"><i class="icon-refresh"></i> {l s='Recalculate' mod='lgfreeshippingzones'}</a> -->
            <!-- </p> -->
        {/if}
        {if $shipping_cost > 0 and $weight1 > 0 and $cart->getTotalWeight() <= $weight1}
            {if $prestashop_version == 16}<p class="alert alert-warning">{/if}
            {if $prestashop_version == 15}<p class="warning">{/if}
                {l s='Get' mod='lgfreeshippingzones'} {$weight1-$cart->getTotalWeight()|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'} {l s='more to get free shipping' mod='lgfreeshippingzones'} <a class="button btn btn-default" onClick="window.location.reload()"><i class="icon-refresh"></i> {l s='Recalculate' mod='lgfreeshippingzones'}</a>
            </p>
        {/if}
        {if $shipping_cost > 0 and $price2 > 0 and $cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING) >= $price2}
            {if $prestashop_version == 16}<p class="alert alert-warning">{/if}
            {if $prestashop_version == 15}<p class="warning">{/if}
                {l s='You have an extra' mod='lgfreeshippingzones'} {displayPrice price=$cart->getordertotal($tax_status, Cart::BOTH_WITHOUT_SHIPPING)-$price2} {l s='to qualify for free shipping' mod='lgfreeshippingzones'} <a class="button btn btn-default" onClick="window.location.reload()"><i class="icon-refresh"></i> {l s='Recalculate' mod='lgfreeshippingzones'}</a>
            </p>
        {/if}
        {if $shipping_cost > 0 and $weight2 > 0 and $cart->getTotalWeight() >= $weight2}
            {if $prestashop_version == 16}<p class="alert alert-warning">{/if}
            {if $prestashop_version == 15}<p class="warning">{/if}
                {l s='You have an extra' mod='lgfreeshippingzones'} {$cart->getTotalWeight()-$weight2|escape:'html':'UTF-8'} {$weight_unit|escape:'html':'UTF-8'} {l s='to qualify for free shipping' mod='lgfreeshippingzones'} <a class="button btn btn-default" onClick="window.location.reload()"><i class="icon-refresh"></i> {l s='Recalculate' mod='lgfreeshippingzones'}</a>
            </p>
        {/if}
        {if $shipping_cost === 0}
            {if $prestashop_version == 16}<p class="alert alert-success">{/if}
            {if $prestashop_version == 15}<p class="success">{/if}
                {l s='Your purchase qualifies for free shipping' mod='lgfreeshippingzones'} <a class="button btn btn-default" onClick="window.location.reload()"><i class="icon-refresh"></i> {l s='Recalculate' mod='lgfreeshippingzones'}</a>
            </p>
        {/if}
    {/if}
</div>
