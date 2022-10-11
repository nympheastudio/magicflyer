{*
Chronossimo - Gestion automatique de l'affranchissement et du suivi des colis

 NOTICE OF LICENCE

 This source file is subject to a commercial license from SARL VANVAN
 Use, copy, modification or distribution of this source file without written
 license agreement from the SARL VANVAN is strictly forbidden.
 In order to obtain a license, please contact us: contact@chronossimo.fr
 ...........................................................................
 INFORMATION SUR LA LICENCE D'UTILISATION

 L'utilisation de ce fichier source est soumise a une licence commerciale
 concédée par la société VANVAN
 Toute utilisation, reproduction, modification ou distribution du présent
 fichier source sans contrat de licence écrit de la part de la SARL VANVAN est
 expressément interdite.
 Pour obtenir une licence, veuillez contacter la SARL VANVAN a l'adresse:
                  contact@chronossimo.fr
 ...........................................................................
 @package    Chronossimo
 @version    1.0
 @copyright  Copyright(c) 2012-2014 VANVAN SARL
 @author     Wandrille R. <contact@chronossimo.fr>
 @license    Commercial license
 @link http://www.chronossimo.fr
*}<div class="error">
	<span style="float:right"><a id="hideError" href=""><img alt="X" src="/modules/chronossimo/img/close.png" /></a></span>
	<img src="/modules/chronossimo/img/error.png" />{$error_msg}<br /><br />
	
	<p>Détails :</p>
	
	<ul>
		{foreach from=$errors item=error}
		<li>{$error}</li>
		{/foreach}
	</ul>
	


{$error_end}
</div>
{literal}
<style>
	.error {
		color: #383838;
		font-weight: 700;
		margin: 0 0 10px 0;
		line-height: 20px;
		padding: 10px 15px;
		border: 1px solid #EC9B9B;
		background-color: #FAE2E3;
		border-radius: 20px;
	}
</style>
{/literal}
