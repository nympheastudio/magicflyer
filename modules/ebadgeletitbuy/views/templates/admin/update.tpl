{*
*
* NOTICE OF LICENSE
*
*  @author YuhaoZHANG
*  @copyright Altares
*  @license
*
*}

{$output|strval}
<fieldset style="margin-top: 10px;  width: 80%" > <!-- TODO responsive -->
	<legend>
		{l s='E-Bagde LetitBuy - Preuve de qualité pour vos clients' mod='ebadgeletitbuy'}
	</legend>
	<div class="description">
		<p><strong>Letitbuy</strong> répond au besoin d'identifier immédiatement les commerces fiables.</p>
		<p>Avec la hausse de la fraude, les acheteurs veulent être rassurés.</p>
		<p>Letitbuy améliore l'indice de confiance des sites commerces auprès des acheteurs. <strong>Il garantit que l'entreprise existe.</strong> </p>
		<p>Nous utilisons pour cela notre expertise : une vaste connaissance des entreprises françaises, avec d'importantes bases de données mises à jour quotidiennement.</p>
	</div>
	<p></p>
	<p>
		Pour vous connectez, consultez <u><a href="https://www.adminiz.fr/letitbuy/" target="_blank">notre site en ligne.</a></u>
	</p>
</fieldset>

<form id="update_form" method="post">
  <fieldset id="fieldset_main_conf"  style="margin-top: 10px;  width: 80%" >
    <legend>
      <img alt="" src="">Mettre à jour
    </legend>
    <label>{l s='Votre Adresse e-mail' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="identification" type="text" class="" size="25" value="{$email|escape:'htmlall':'UTF-8'}" id="login" name="EMAIL" readonly>
      <sup>*</sup>
    </div>
    <div class="margin-form">
      <input id="tmp" type="text" class="" size="25" value="{$tmp|escape:'htmlall':'UTF-8'}" id="tmp" name="TMP" style="display: none" readonly>
    </div>
    <label>{l s=' URL ' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="url" type="url" class="" size="40" value="{$url|escape:'htmlall':'UTF-8'}" id="url" name="URL">
      <sup>*</sup>
      <span id="attention" style="margin-left: 10px" > Attention http:// ou https://</span>
    </div>
    <label>{l s=' SIREN ' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="siren" type="number" class="" size="25" value="{$siren|escape:'htmlall':'UTF-8'}" id="siren" name="SIREN"  max="999999999">
      <sup>*</sup>
    </div>
    <div class="margin-form">
      <input type="submit" class="button btn btn-default pull-right" name="Update" value="{l s='Metter à jour votre url et siren' mod='ebadgeletitbuy'}" id="_form_submit_btn" style="margin-left: 30px;">


    </div>

  </fieldset>
</form>
