{*
  * MODULE PRESTASHOP OFFICIEL CHRONOPOST
  * 
  * LICENSE : All rights reserved - COPY AND REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
  * LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
* SANS ACCORD EXPRES D'OXILEO
  *
  * @author    Oxileo SAS <contact@oxileo.eu>
  * @copyright 2001-2018 Oxileo SAS
  * @license   Proprietary - no redistribution without authorization
  *}
<div class="form-group">
	<label class="control-label col-lg-3">{l s='Carrier for' mod='chronopost'} {$code_label|escape:'htmlall':'UTF-8'}</label>
	<div class="col-lg-9">
		<select name="chronoparams[{$code|escape:'htmlall':'UTF-8'}][id]">
			<option value="-1">{l s='Do not activate' mod='chronopost'}</option>

			{foreach from=$carriers item=carrier}
			
				<option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}"{if $selected==$carrier.id_reference} selected{/if}>
					{$carrier.name|escape:'htmlall':'UTF-8'}
				</option>
			{/foreach}
		</select>
	</div>
</div>
<div class="form-group">
	<div class="col-lg-3">
	</div>
	<div class="col-lg-9 text-right">
		<button class="createCarrier btn btn-default" value="{$code|escape:'htmlall':'UTF-8'}"><i class="icon-plus"></i> {l s='Create new carrier' mod='chronopost'}</button>
	</div>
</div>