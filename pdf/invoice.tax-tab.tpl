

{if $tax_exempt || ((isset($product_tax_breakdown) && $product_tax_breakdown|@count > 0) || (isset($ecotax_tax_breakdown) && $ecotax_tax_breakdown|@count > 0) || (isset($shipping_tax_breakdown) && $shipping_tax_breakdown|@count > 0))}
<!--  TAX DETAILS -->

			{if $tax_exempt}
				{l s='Exempt of VAT according section 259B of the General Tax Code.' pdf='true'}
			{else}
	<table id="tax-tab" width="100%">
		<thead>
			<tr>
				<th class="header small">{l s='Tax Detail' pdf='true'}</th>
				<th class="header small">{l s='Tax Rate' pdf='true'}</th>
				{if $display_tax_bases_in_breakdowns}
					<th class="header small">{l s='Base price' pdf='true'}</th>
				{/if}
				<th class="header-right small">{l s='Total Tax' pdf='true'}</th>
			</tr>
		</thead>
		<tbody>

				{if isset($product_tax_breakdown)}
					{foreach $product_tax_breakdown as $rate => $product_tax_infos}
					<tr >
					 <td class="white center">
						{if !isset($pdf_product_tax_written)}
							{l s='Products' pdf='true'}
							{assign var=pdf_product_tax_written value=1}
						{/if}
					</td>
					 <td class="right white">{$rate} %</td>
					{if !$use_one_after_another_method}
					 <td class="right white">
						 {if isset($is_order_slip) && $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=$product_tax_infos.total_price_tax_excl}
					 </td>
					{/if}
					 <td class="right white">{if isset($is_order_slip) && $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=$product_tax_infos.total_amount}</td>
					</tr>
					{/foreach}
				{/if}

				{if isset($shipping_tax_breakdown)}
					{foreach $shipping_tax_breakdown as $shipping_tax_infos}
					<tr >
					 <td class="white center">
						{if !isset($pdf_shipping_tax_written)}
							{l s='Shipping' pdf='true'}
							{assign var=pdf_shipping_tax_written value=1}
						{/if}
					 </td>
					 <td class="white right">{$shipping_tax_infos.rate} %</td>
					{if !$use_one_after_another_method}
						 <td class="white right">{if isset($is_order_slip) && $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=$shipping_tax_infos.total_tax_excl}</td>
					{/if}
					 <td class="white right">{if isset($is_order_slip) && $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=$shipping_tax_infos.total_amount}</td>
					</tr>
					{/foreach}
				{/if}

				{if isset($ecotax_tax_breakdown)}
					{foreach $ecotax_tax_breakdown as $ecotax_tax_infos}
						{if $ecotax_tax_infos.ecotax_tax_excl > 0}
						<tr >
							<td >{l s='Ecotax' pdf='true'}</td>
							<td >{$ecotax_tax_infos.rate  } %</td>
							{if !$use_one_after_another_method}
								<td >{if isset($is_order_slip) && $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=$ecotax_tax_infos.ecotax_tax_excl}</td>
							{/if}
							<td >{if isset($is_order_slip) && $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=($ecotax_tax_infos.ecotax_tax_incl - $ecotax_tax_infos.ecotax_tax_excl)}</td>
						</tr>
						{/if}
					{/foreach}
				{/if}
			</tbody>
	</table>
			{/if}

<!--  / TAX DETAILS -->
{/if}
