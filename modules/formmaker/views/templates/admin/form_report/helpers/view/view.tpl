{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2016 silbersaiten
* @version   1.1.1
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}
{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
<h2>{l s='Report for form "%s"' sprintf=$report->name|escape:'quotes':'UTF-8' mod='formmaker'}</h2>

<div class="row">
	<div class="col-lg-6">
		<div class="panel">
			<div class="panel-heading">
				<i class="icon-user"></i>
				{l s='Customer' mod='formmaker'}
				{if $customer}
				<span class="badge">
					<a href="?tab=AdminCustomers&amp;id_customer={$customer->id|escape:'html':'UTF-8'}&amp;viewcustomer&amp;token={getAdminToken tab='AdminCustomers'}">
						{if Configuration::get('PS_B2B_ENABLE')}{$customer->company|escape:'html':'UTF-8'} - {/if}
						{if $gender}{$gender->name|escape:'html':'UTF-8'}{/if}
						{$customer->firstname|escape:'html':'UTF-8'}
						{$customer->lastname|escape:'html':'UTF-8'}
					</a>
				</span>
				<span class="badge">
					{l s='#' mod='formmaker'}{$customer->id|escape:'html':'UTF-8'}
				</span>
				{/if}
			</div>
			<div class="panel-body">
				{if $customer}
					{if ($customer->isGuest())}
						{l s='This form has been submitted by a guest.' mod='formmaker'}
					{else}
						<dl class="well list-detail">
							<dt>{l s='Email' mod='formmaker'}</dt>
								<dd><a href="mailto:{$customer->email|escape:'htmlall':'UTF-8'}"><i class="icon-envelope-o"></i> {$customer->email|escape:'htmlall':'UTF-8'}</a></dd>
							<dt>{l s='Account registered' mod='formmaker'}</dt>
								<dd class="text-muted"><i class="icon-calendar-o"></i> {dateFormat date=$customer->date_add full=true}</dd>
							<dt>{l s='Valid orders placed' mod='formmaker'}</dt>
								<dd><span class="badge">{$customerStats['nb_orders']|intval}</span></dd>
							<dt>{l s='Total spent since registration' mod='formmaker'}</dt>
								<dd><span class="badge badge-success">{displayPrice price=Tools::ps_round(Tools::convertPrice($customerStats['total_orders'], $currency), 2) currency=$currency->id}</span></dd>
							{if Configuration::get('PS_B2B_ENABLE')}
								<dt>{l s='Siret' mod='formmaker'}</dt>
									<dd>{$customer->siret|escape:'html':'UTF-8'}</dd>
								<dt>{l s='APE' mod='formmaker'}</dt>
									<dd>{$customer->ape|escape:'html':'UTF-8'}</dd>
							{/if}
						</dl>
					{/if}
				{else}
				<div class="alert alert-warning">{l s='Submitted by an unregistered customer' mod='formmaker'}</div>
				{/if}
			</div>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="panel">
			<div class="panel-heading">
				<i class="icon-user"></i>
				{l s='Form' mod='formmaker'}
				{if $form}
				<span class="badge">
					<a href="?tab=AdminFormSettings&amp;id_fm_form={$form->id|escape:'html':'UTF-8'}&amp;updatefm_form&amp;token={getAdminToken tab='AdminFormSettings'}">
						{$form->name|escape:'html':'UTF-8'}
					</a>
				</span>
				<span class="badge">
					{l s='#' mod='formmaker'}{$form->id|escape:'html':'UTF-8'}
				</span>
				{/if}
			</div>
			<div class="panel-body">
				<dl class="well list-detail">
					<dt>{l s='Name' mod='formmaker'}</dt>
						<dd>{$form->name|escape:'html':'UTF-8'}</dd>
					<dt>{l s='Form created' mod='formmaker'}</dt>
						<dd class="text-muted"><i class="icon-calendar-o"></i> {dateFormat date=$form->date_add full=true}</dd>
					<dt>{l s='Currently associated to products' mod='formmaker'}</dt>
						<dd>
							{if $form_products|@count}
							<ul>
								{foreach from=$form_products item=form_product}
								<li><a href="?tab=AdminProducts&amp;id_product={$form_product.id_product|escape:'html':'UTF-8'}&amp;updateproduct&amp;token={getAdminToken tab='AdminProducts'}">{$form_product.name|escape:'html':'UTF-8'}</a></li>
								{/foreach}
							</ul>
							{else}
							{l s='No associations' mod='formmaker'}
							{/if}
						</dd>
				</dl>
			</div>
		</div>
	</div>
</div>
{if $form_fields}
<div class="panel">
	<div class="panel-heading">{l s='Form Values:' mod='formmaker'}</div>
	<div class="panel-body">
		<dl class="well list-detail">
			<dt>{l s='Form submitted on' mod='formmaker'}</dt>
				<dd>{dateFormat date=$report->date_add full=true}</dd>
			{if $product}
			<dt>{l s='Product in question' mod='formmaker'}</dt>
				<dd class="text-muted">
					{if $product_image}{$product_image.tag|escape:'UTF-8'}{/if}
					<div>
						<a href="?tab=AdminProducts&amp;id_product={$product->id|escape:'html':'UTF-8'}&amp;updateproduct&amp;token={getAdminToken tab='AdminProducts'}">{$product->name|escape:'html':'UTF-8'}</a>
					</div>
				</dd>
			{/if}
		</dl>
		<div class="table-responsive-row clearfix">
			<table class="table">
				<thead>
					<tr>
						<th>{l s='Field' mod='formmaker'}</th>
						<th>{l s='Value' mod='formmaker'}</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$form_fields item=form_element}
					<tr>
						<td>{$form_element.field|escape:'UTF-8'}</td>
						<td>
							{if $form_element['reference'] == 'fileInput' && !empty($form_element['value'])}
							<a href="{$form_element['value']|escape:'quotes':'UTF-8'}">{l s='Click to download' mod='formmaker'}</a>
							{else if $form_element['reference'] == 'selectInput' || $form_element['reference'] == 'radioInput' || $form_element['reference'] == 'checkboxInput'}
							{foreach from=$form_element['value'] item=value}
							<font size="2" face="Open-sans, sans-serif" color="#555454">
								<strong>{$value|escape:'htmlall':'UTF-8'}</strong>
							</font><br />
							{/foreach}
							{else}
							<font size="2" face="Open-sans, sans-serif" color="#555454">
								<strong>{$form_element['value']|escape:'htmlall':'UTF-8'}</strong>
							</font>
							{/if}
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
{else}
<div class="col-lg-12">
	<div class="alert alert-warning">{l s='No fields were submitted' mod='formmaker'}</div>
</div>
{/if}
{/block}