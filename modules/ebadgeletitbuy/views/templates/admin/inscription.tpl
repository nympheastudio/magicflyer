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

<form id="inscription_form" method="post">
  <fieldset id="fieldset_main_conf"  style="margin-top: 10px;  width: 80%" >
    <legend>
      <img alt="" src="">Inscription
    </legend>
    <label>{l s='Adresse e-mail' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="identification" type="text" class="" size="25" value="{$email|escape:'htmlall':'UTF-8'}" id="login" name="EMAIL">
      <sup>*</sup>
    </div>
    <label>{l s=' Mot de passe ' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="identification" type="password" class="" size="25" value="" id="password" name="PASSWORD">
      <sup>*</sup>
    </div>
    <label>{l s=' URL ' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="url" type="url" class="" size="40" value="{$url|escape:'htmlall':'UTF-8'}" id="url" name="URL">
      <sup>*</sup>
      <span id="attention" style="margin-left: 10px" > Attention http:// ou https://</span>
    </div>
    <label>{l s=' SIREN ' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="siren" type="number" class="" size="25" value="{$siren|escape:'htmlall':'UTF-8'}" id="siren" name="SIREN" max="999999999">
      <sup>*</sup>
    </div>
    <div class="margin-form">
      <input type="submit" class="button btn btn-default pull-right" name="Inscription" value="{l s='Créer un compte' mod='ebadgeletitbuy'}" id="_form_submit_btn" style="margin-left: 30px;">
      <span id="connexion" style="margin-left: 10px" > Vous avez déjà un compte ? <a href="#admin.tpl" id="lien_connexion" name="connexion"  onclick="document.getElementById('inscription_form').style.display='none';document.getElementById('connextion_form').style.display=''">Connectez-vous</a>
      </span>
    </div>
  </fieldset>
</form>





<form id="connextion_form" method="post" style="display:none">
  <fieldset id="fieldset_main_conf"  style="margin-top: 10px;  width: 80%" >
    <legend>
      <img alt="" src="">Identification
    </legend>
    <label>{l s='Adresse e-mail' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="identification" type="text" class="" size="25" value="" id="login" name="EMAIL">
      <sup>*</sup>
    </div>
    <label>{l s=' Mot de passe ' mod='ebadgeletitbuy'}</label>
    <div class="margin-form">
      <input id="identification" type="password" class="" size="25" value="" id="password" name="PASSWORD">
      <sup>*</sup>
    </div>
    <div class="margin-form">
      <input type="submit" class="button btn btn-default pull-right" name="Connexion" value="{l s='Connexion' mod='ebadgeletitbuy'}" id="_form_submit_btn">

      <span id="inscription" style="margin-left: 10px" > Vous n'avez pas de compte ? <a href="#admin.tpl" id="lien_inscription" name="inscription"  onclick="document.getElementById('connextion_form').style.display='none';document.getElementById('inscription_form').style.display=''" >Créez votre compte</a>

      </span>

    </div>

  </fieldset>
</form>
