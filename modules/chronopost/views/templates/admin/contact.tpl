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
	<label class="control-label col-lg-3">
		{l s='Title' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<select name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][civility]">
			<option value="M"{if $civility=='M'} selected{/if}>{l s='Mr.' mod='chronopost'}</option>
			<option value="E"{if $civility=='E'} selected{/if}>{l s='Mrs.' mod='chronopost'}</option>
			<option value="L"{if $civility=='L'} selected{/if}>{l s='Ms.' mod='chronopost'}</option>
		</select>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Company name' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "35" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][name]" 
		value="{$name|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Company name 2' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "35" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][name2]" 
		value="{$name2|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Address' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "35" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][address]" 
		value="{$address|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Address 2' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "35" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][address2]" 
		value="{$address2|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>


<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Zipcode' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "5" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][zipcode]" 
		value="{$zipcode|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='City' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "35" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][city]" 
		value="{$city|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Country' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<select name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][country]">
			<option value="FR"{if $country=='FR'} selected{/if}>{l s='Metropolitan France' mod='chronopost'}</option>
			<option value="GP"{if $country=='GP'} selected{/if}>{l s='Guadeloupe' mod='chronopost'}</option>
			<option value="GF"{if $country=='GF'} selected{/if}>{l s='French Guyana' mod='chronopost'}</option>
			<option value="MQ"{if $country=='MQ'} selected{/if}>{l s='Martinique' mod='chronopost'}</option>
			<option value="YT"{if $country=='YT'} selected{/if}>{l s='Mayotte' mod='chronopost'}</option>
			<option value="RE"{if $country=='RE'} selected{/if}>{l s='Réunion' mod='chronopost'}</option>
			<option value="MF"{if $country=='MF'} selected{/if}>{l s='Saint-Martin' mod='chronopost'}</option>
		</select>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Contact name' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "35" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][contactname]" 
		value="{$contactname|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Email' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "35" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][email]" 
		value="{$email|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Phone number' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "10" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][phone]" 
		value="{$phone|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div class="form-group">													
	<label class="control-label col-lg-3">
		{l s='Mobile phone number' mod='chronopost'}
	</label>				
	<div class="col-lg-9 ">
		<input type="text" maxlength = "10" name="chronoparams[{$prefix|escape:'htmlall':'UTF-8'}][mobile]" 
		value="{$mobile|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>
