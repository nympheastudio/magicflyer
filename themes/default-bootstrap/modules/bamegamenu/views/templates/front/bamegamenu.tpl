{*

* 2007-2018 PrestaShop

*

* NOTICE OF LICENSE

*

* This source file is subject to the Open Software License (OSL 3.0)

* that is bundled with this package in the file LICENSE.txt.

* It is also available through the world-wide-web at this URL:

* http://opensource.org/licenses/osl-3.0.php

* If you did not receive a copy of the license and are unable to

* obtain it through the world-wide-web, please send an email

* to license@buy-addons.com so we can send you a copy immediately.

*

* DISCLAIMER

*

* Do not edit or add to this file if you wish to upgrade PrestaShop to newer

* versions in the future. If you wish to customize PrestaShop for your

* needs please refer to http://www.prestashop.com for more information.

*

*  @author PrestaShop SA <contact@buy-addons.com>

*  @copyright  2007-2018 PrestaShop SA

*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

*  International Registered Trademark & Property of PrestaShop SA

*}

{if $MENU != ''}

	{if $CSS != ''}

		<!-- Css -->

		<style type="text/css">

			{$CSS|escape:'htmlall':'UTF-8'}

		</style>

		<!--/ Css -->

	{/if}

	{if $STAY_ON_TOP_MOBILE==0}

	<style type="text/css">

	@media only screen and (max-width : 768px){

		#mega_menu_plus.ontop{

			position: static !important;

		}

	}

	</style>

	{/if}

	{if empty($SPEED)}

		{assign var='SPEED' value='100'}

	{/if}

	{if $EFFECT == 'default'}

		<script type="text/javascript">

			var anchor='default';

			var speed={$SPEED|escape:'htmlall':'UTF-8'};

		</script>

	{elseif $EFFECT == 'fade'}

		<script type="text/javascript">

			var anchor='fade';

			var speed={$SPEED|escape:'htmlall':'UTF-8'};

		</script>

	{elseif $EFFECT == 'slide'}

		<script type="text/javascript">

			var anchor='slide';

			var speed={$SPEED|escape:'htmlall':'UTF-8'};

		</script>

	{elseif $EFFECT == 'none'}

		<script type="text/javascript">

			var anchor='none';

			var speed={$SPEED|escape:'htmlall':'UTF-8'};

		</script>

	{else}

		<script type="text/javascript">

			var anchor='default';

			var speed={$SPEED|escape:'htmlall':'UTF-8'};

		</script>

	{/if}

	<!-- Menu -->

	{if $STAY_ON_TOP==1}<div class="stayontop"></div>{/if}

	<div id="mega_menu_plus" class="mg-contener {if $LANGUAGETYPE==1}language_ltr{else}language_rtl{/if} clearfix col-lg-12">

		<div class="mobi"><a title="menu"><div class="navTrigger">
			<i></i><i></i><i></i>
		  </div></a></div>
			<!--<span class='menu-item-link-text'><img src="https://www.magicflyer.com/themes/default-bootstrap/img/icons_mobile.svg" /></span>-->
		

		<ul class="mg-menu hidden-ul clearfix menu-content">
			<hr  class="mobile_menu" />

			{$MENU nofilter} {* escape is unnecessary *}




			<hr  class="mobile_menu" />
			<li class="mobile_menu"><a href="" id="mySearch_mobile">{l s='Recherche' mod='bamegamenu'}</a></li>

			{if $is_logged}
				<li class="mobile_menu">
				<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" title="{l s='View my customer account' mod='blockuserinfo'}" class="account" rel="nofollow">{l s='Mon compte'}</a>
				</li>
			{else}
				<li class="mobile_menu">
				<a data-action="login"  class="login" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Log in to your customer account' mod='blockuserinfo'}" id="show_modal_login">
				{l s='Connexion'}
				</a></li>
			{/if}
			
			<hr  class="mobile_menu"/>
			{if count($languages) > 1}
			{foreach from=$languages key=k item=language name="languages"}
			<li class="mobile_menu lang_menu"><a href="https://www.magicflyer.com/{$language.iso_code|regex_replace:"/\s\(.*\)$/":""}">{$language.iso_code|regex_replace:"/\s\(.*\)$/":""}</a></li>
			{/foreach}
			{/if}


			<li class="mobile_menu" ><a href="{$link->getCMSLink('3')}">{l s='CGV' mod='bamegamenu'}</a></li>
			<li class="mobile_menu"><a href="{$link->getCMSLink('2')}">{l s='Mentions légales' mod='bamegamenu'}</a></li>

		</ul>

	</div>

	<!--/ Menu -->

{/if}

	<script type="text/javascript">

		var width_window=$(window).width();

		width_window=$(window).width();

		if(width_window>768){

			showPanel(anchor,speed);

		}else{ 

			$('#mega_menu_plus ul li').unbind('hover');

		} 

	</script>