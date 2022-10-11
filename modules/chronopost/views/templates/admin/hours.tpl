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
 <select name="chronoparams[{$group_name|escape:'htmlall':'UTF-8'}][{$field_name|escape:'htmlall':'UTF-8'}]">
	{for $i=0 to 23}
		<option value="{$i|escape:'htmlall':'UTF-8'}"{if $i==$selected} selected{/if}>{$i|string_format:'%02d'}</option>
	{/for}
</select>