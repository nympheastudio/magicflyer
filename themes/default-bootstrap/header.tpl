{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!DOCTYPE HTML>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="{$language_code|escape:'html':'UTF-8'}"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8 ie7" lang="{$language_code|escape:'html':'UTF-8'}"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9 ie8" lang="{$language_code|escape:'html':'UTF-8'}"><![endif]-->
<!--[if gt IE 8]> <html class="no-js ie9" lang="{$language_code|escape:'html':'UTF-8'}"><![endif]-->
<html lang="{$language_code|escape:'html':'UTF-8'}">
	<head>

		
		<meta charset="utf-8" />
		<title>{$meta_title|escape:'html':'UTF-8'}</title>
{if isset($meta_description) AND $meta_description}
		<meta name="description" content="{$meta_description|escape:'html':'UTF-8'}" />
{/if}
{if isset($meta_keywords) AND $meta_keywords}
		<meta name="keywords" content="{$meta_keywords|escape:'html':'UTF-8'}" />
{/if}
		<meta name="generator" content="PrestaShop" />
		<meta name="robots" content="{if isset($nobots)}no{/if}index,{if isset($nofollow) && $nofollow}no{/if}follow" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="icon" type="image/vnd.microsoft.icon" href="{$favicon_url}?{$img_update_time}" />
		<link rel="shortcut icon" type="image/x-icon" href="{$favicon_url}?{$img_update_time}" />
{if isset($css_files)}
	{foreach from=$css_files key=css_uri item=media}
		<link rel="stylesheet" href="{$css_uri|escape:'html':'UTF-8'}" type="text/css" media="{$media|escape:'html':'UTF-8'}" />
	{/foreach}
{/if}
{if isset($js_defer) && !$js_defer && isset($js_files) && isset($js_def)}
	{$js_def}
	{foreach from=$js_files item=js_uri}
	<script type="text/javascript" src="{$js_uri|escape:'html':'UTF-8'}"></script>
	{/foreach}
{/if}
		{$HOOK_HEADER}
		<link href="https://fonts.googleapis.com/css?family=Roboto+Slab:100,300,400,700|Roboto:300,400,700" rel="stylesheet">
		
		
		<!--[if IE 8]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
		{literal}
		<!-- Facebook Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '503746626730208');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=503746626730208&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-73536343-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-73536343-1');
</script>


<!-- Global site tag (gtag.js) - Google Ads: 734388051 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-734388051"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-734388051');
</script>


<!-- Insight Tag Linkedin -->
<script type="text/javascript">
_linkedin_partner_id = "506753";
window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
window._linkedin_data_partner_ids.push(_linkedin_partner_id);
</script><script type="text/javascript">
(function(){var s = document.getElementsByTagName("script")[0];
var b = document.createElement("script");
b.type = "text/javascript";b.async = true;
b.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js";
s.parentNode.insertBefore(b, s);})();
</script>
<noscript>
<img height="1" width="1" style="display:none;" alt="" src="https://dc.ads.linkedin.com/collect/?pid=506753&fmt=gif" />
</noscript>
<!-- End Insight Tag Linkedin -->


		{/literal}
		
	</head>
	<body{if isset($page_name)} id="{$page_name|escape:'html':'UTF-8'}"{/if} class="{if isset($page_name)}{$page_name|escape:'html':'UTF-8'}{/if}{if isset($body_classes) && $body_classes|@count} {implode value=$body_classes separator=' '}{/if}{if $hide_left_column} hide-left-column{/if}{if $hide_right_column} hide-right-column{/if}{if isset($content_only) && $content_only} content_only{/if} lang_{$lang_iso}">
	{if !isset($content_only) || !$content_only}
		{if isset($restricted_country_mode) && $restricted_country_mode}
			<div id="restricted-country">
				<p>{l s='You cannot place a new order from your country.'} <span class="bold">{$geolocation_country|escape:'html':'UTF-8'}</span></p>
			</div>
		{/if}
		<div id="page">
			<div class="banner">
				<div class="container-fluid">
					<div class="row">
						{hook h="displayBanner"}
					</div>
				</div>
			</div>
			<div class="topbar">
				<div class="container">
					<div class="row">
						<nav>{hook h="displayNav"}
						
						
						
						<p class="cart_navigation clearfix">
							{if !$opc}
								<a  href="{if $back}{$link->getPageLink('order', true, NULL, 'step=1&amp;back={$back}')|escape:'html':'UTF-8'}{else}{$link->getPageLink('order', true, NULL, 'step=1')|escape:'html':'UTF-8'}{/if}" class="button btn btn-default standard-checkout button-medium" title="{l s='Proceed to checkout'}">
									<span>{l s='Proceed to checkout'}<i class="icon-chevron-right right"></i></span>
								</a>
							{/if}
							<a href="{if (isset($smarty.server.HTTP_REFERER) && ($smarty.server.HTTP_REFERER == $link->getPageLink('order', true) || $smarty.server.HTTP_REFERER == $link->getPageLink('order-opc', true) || strstr($smarty.server.HTTP_REFERER, 'step='))) || !isset($smarty.server.HTTP_REFERER)}{$link->getPageLink('index')}{else}{$smarty.server.HTTP_REFERER|escape:'html':'UTF-8'|secureReferrer}{/if}" class="button-exclusive btn btn-default" title="{l s='Continue shopping'}">
								<i class="icon-chevron-left"></i>{l s='Continue shopping'}
							</a>
						</p>
						
						
						
						</nav>
					</div>
				</div>
			</div>
			
			<div class="header-container col-lg-2 col-md-3 col-sm-12 col-xs-12">
				<header id="header">
					
					<div class="header-left">
						<div class="container-fluid">
							<div class="row">
								<p class="animate-text">{l s='Site officiel de l\'inventeur Fran??ais'}</p>
								<div id="header_logo">
									<a href="{if $force_ssl}{$base_dir_ssl}{else}{$base_dir}{/if}" title="{$shop_name|escape:'html':'UTF-8'}">
										<img class="logo img-responsive" src="{$logo_url}" alt="{$shop_name|escape:'html':'UTF-8'}"{if isset($logo_image_width) && $logo_image_width} width="{$logo_image_width}"{/if}{if isset($logo_image_height) && $logo_image_height} height="{$logo_image_height}"{/if}/>
									</a>
									
								</div>
								
								{if isset($HOOK_TOP)}{$HOOK_TOP}{/if}
							</div>
						</div>
						{if isset($HOOK_FOOTER)}
							<!-- Footer -->
							<div class="footer-container">
								<footer id="footer"  class="container">
									<div class="row">{$HOOK_FOOTER}</div>
									<div class="copy">{l s='?? 2018 Magic Flyer'}</div>
									<div class="copy">{l s='Tous droits r??serv??s'}</div>
									
									<div class="copy"><a href="{$link->getCMSLink('3')}">{l s='CGV'}</a> | <a href="{$link->getCMSLink('2')}">{l s='Mentions l??gales'}</a></div>
									<div class="copy "><a href="{$link->getCMSLink('74')}">{l s='annuaire local'}</a></div>
									<hr/>
									<div class="copy"><a href="http://www.nymphea-studio.fr" rel="nofollow" title="conception du site internet par l'agence Nymphea Studio">Nymphea Studio</a></div>
								</footer>
							</div><!-- #footer -->
						{/if}
						
						
					</div>
				</header>
				
				
			</div>
			<div class="columns-container col-lg-10 col-md-9 col-sm-12 col-xs-12  global-content">
				<div id="columns" class="container-fluid">

						
						
						
						{if $category->id == '51'}
							<!-- Tous papillons -->
						{elseif $page_name == 'index'}	

						<div id="slider_row" class="row">
						<div id="top_column" class="center_column">
							<!-- SLIDER -->
							{hook h="displayTopColumn"}
							<!-- OU VIDEO 
							<video width="100%" height="100%" loop="loop" autoplay="autoplay" poster="image avant lancement.jpg">
							<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Story-Video-magic-flyer.webm" type="video/webm"> 
							<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Story-Video-magic-flyer.mp4" type="video/mp4"> 
							<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Story-Video-magic-flyer.ogv" type="video/ogg"> 
							Votre navigateur ne permet pas de lire les vid??os HTML5. </video>
							-->
							
							</div>

							<div class="video_hp desktop_only ">
								<video  loop muted autoplay poster="https://www.magicflyer.com/themes/default-bootstrap/img/video/Magic-AC.jpg">
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/magic-AC.webm" type="video/webm"> 
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Magic-AC.mp4" type="video/mp4"> 
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Magic-AC.ogv" type="video/ogg"> 
									Votre navigateur ne permet pas de lire les vid??os HTML5. 
								</video>
							</div>
							<div class="mobile_only content_ac">
								<img src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Magic-AC-mobile.jpg"/>
								<div class="text-intro">
									<h1><span class="smalltext">{l s='D??couvrez'}</span>{l s='Le messager de l\'??motion'}</h1>
									<a class="btn btn-primary" href="https://www.magicflyer.com/fr/content/65-boutique" title="{l s='Voir la boutique du papillon magique'}" >{l s='Aller ?? la boutique'}</a>
								</div>

							</div>

							

		</div>
		<div class="row">
			<div id="center_column" class="center_column col-xs-12">
				<div class="rte">
					<div class="pagecent">

						<div id="leading-1" class="row page_scroll">
							<div class="image_gauche"><img class="image_small"
									src="https://www.magicflyer.com/img/cms/2021/Mariano Vilaplana-720-x-1200.jpg"
									alt="" width="720" height="1200"></div>

							<div class="content_page">
								<h3 class="sous_titre">Le Papillon Magique</h3>
								<h2 class="titre">L'amour en h??ritage</h2>
								<p></p>
								<p>A la crois??e de la po??sie et du romantisme
									d???une histoire d???amour pour cr??er une invention unique !
								</p>
								<p>
									Il ??tait une fois un inventeur de g??nie qui avait imagin?? une id??e folle : s??duire
									sa belle avec un Papillon Magique. Mettant au point un ing??nieux assemblage de fil
									de m??tal, d'??lastique, de papier aux couleurs chatoyantes, il ??crivit son message
									sur les ailes du papillon et le glissa dans une lettre avant de l'envoyer par la
									Poste...
								</p>
								<p>
									A l'ouverture de la carte, le Papillon Magique dans son envol extraordinaire vint
									d??livrer ?? la jeune fille, le t??moignage de ses sentiments.</p>
								<a title="Achetez vos cartes papillons et ??merveillez vos invit??s"
									href="https://www.magicflyer.com/fr/content/43-le-papillon-magique-une-invention-de-genie"
									class="scroll_btn">Conna??tre toute l'histoire</a>
							</div>
						</div>
						<div class="row page_scroll" id="leading-2">
							<div class="image_gauche"><img class="image_small"
									src="https://www.magicflyer.com/img/cms/2021/Cartes-Livrets-Personnalises-Evenementiel-720x-1200.jpg"
									alt="" width="720" height="1200"></div>

							<div class="content_page">
								<h3 class="sous_titre">Le papillon Magique??</h3>
								<h2 class="titre">D??couvrez l'incroyable performer <span>pour les entreprises</span>
								</h2>
								<p>Cette invention fran??aise dynamise votre communication d'entreprise. Compos?? d'un
									Papillon Magique?? ultra l??ger muni de fines ailes en papier subtilement color??es et
									d'un ??lastique brevet??, il s'envole d??s l'ouverture de votre carte pour surprendre
									et ??merveiller tous vos clients, vos prospects, vos partenaires.</p>
								<p>Avec un <strong>Taux de retour moyen de 20% en campagne de Marketing Direct</strong>,
									une <strong>Prise en main de plus de 10 personnes</strong> et une <strong>R??manence
										de 5 ans minimum</strong>, Le Papillon Magique?? g??n??rateur d'??motions, d??tient
									des records in??gal??s constat??s par des centaines de r??f??rences dans le monde</p>
								<p>Alors, faites comme Thomas Pesquet, les ??missions Mask Singer ou mari??s au premier
									regard, C??line Dion et bien d'autres qui rendent sa noblesse ?? cette incroyable
									invention !</p>



								<a href="https://www.magicflyer.com/fr/content/75-professionnels-2022"
									class="scroll_btn">D??couvrir nos produits pour les pros</a>
							</div>
						</div>
						<div class="row page_scroll" id="leading-3">


							<div class="boutiqueAC ">
								<h3 class="sous_titre">Le Papillon Magique??</h3>
								<h2 class="titre">La boutique</h2>




								<div class="clearfix row">
									<div class="col-xs-6 col-sm-3 block_boutiqueAC">
										<div><a href="https://www.magicflyer.com/fr/51-nos-papillons-magiques">
											<div class="img_boutiqueAC"><img src="/img/papillons-magiques.jpg"
													alt="Papillons magiques toutes cat??gories"></div>
											<div class="content_boutiqueAC">
												<h3>Papillons Magiques??</h3>
												<p>?? partir de 19,50 ???<br>/ 10 unit??s</p>
											</div>
										</a></div>
									</div>
									<div class="col-xs-6 col-sm-3 block_boutiqueAC">
										<div><a href="https://www.magicflyer.com/fr/content/66-cartes-papillon">
											<div class="img_boutiqueAC"><img src="https://www.magicflyer.com/img/cms/2021/Carte-Papillon-Perle-Nacree-3.jpg"
													alt="Papillons magiques toutes cat??gories"></div>
											<div class="content_boutiqueAC">
												<h3>Cartes Papillon</h3>
												<p>?? partir de 30 ???<br> / 10 cartes + 10 Papillons Magiques??</p>
											</div>
										</a></div>
									</div>
									<div class="col-xs-6 col-sm-3 block_boutiqueAC">
										<div>
										<a href="https://www.magicflyer.com/fr/index.php?controller=perso_livret">
											<div class="img_boutiqueAC"><img src="https://www.magicflyer.com/img/cms/2021/Boutique-65-Livret-Papillon.jpg"
													alt="Papillons magiques toutes cat??gories"></div>
											<div class="content_boutiqueAC">
												<h3>Livret Papillon</h3>
												<p>?? partir de 38 ???<br>/ livret + 10 Papillons Magiques??</p>
											</div>
										</a></div>
									</div>
									<div class="col-xs-6 col-sm-3 block_boutiqueAC">
										<div><a href="https://www.magicflyer.com/fr/index.php?controller=perso_coeur">
											<div class="img_boutiqueAC"><img src="https://www.magicflyer.com/img/cms/2021/livret-coeur.jpg"
													alt="Papillons magiques toutes cat??gories"></div>
											<div class="content_boutiqueAC">
												<h3>Livret Coeur</h3>
												<p>?? partir de 86 ???<br>/ livret + 30 Papillons Magiques??</p>
											</div>
										</a></div>
									</div>
								</div>







							</div>
						</div>



						<div id="leading-4" class="row page_scroll">
							<div class="image_gauche"><img class="image_small"
									src="https://www.magicflyer.com/img/cms/2021/Tutoriels-magic-flyer-accueil.jpg"
									alt="" width="720" height="1200"></div>
							<div class="titre_page"></div>
							<div class="content_page">
								<h3 class="sous_titre">Les tutos du Papillon</h3>
								<h2 class="titre">Vous voulez savoir <span> comment il fonctionne ?</span></h2>



								<p>Suivez tr??s attentivement nos tutoriels afin de pr??parer de fa??on optimale vos
									Livrets et Cartes Papillons.<br>
									Cr??ez des sorties officielles extraordinaires ou des ??v??nements f????riques. Effet
									garanti ?? l'int??rieur ou ?? l'ext??rieur. De plus, les Papillons Magiques sont
									r??utilisables ?? volont?? ! G??n??rez des ??motions positives qui resteront grav??es dans
									votre m??moire et celle de vos invit??s durant de longues ann??es... Un instant
									inoubliable qui marquera ?? jamais l'un des plus beaux jours de votre vie !</p>
								<a href="https://www.magicflyer.com/fr/content/6-tutoriel"
									class="scroll_btn">Nos tutos vid??o</a>
							</div>
						</div>

					</div>
				</div>
			</div><!-- #center_column -->


				
						{elseif $category->id == '72' && $page_name != 'cartepapillon'}
						
					<div id="slider_row" class="row">


						<div id="top_column" class="center_column">
							<!-- SLIDER -->
							{hook h="displayTopColumn"}
							<!-- OU VIDEO 
							<video width="100%" height="100%" loop="loop" autoplay="autoplay" poster="image avant lancement.jpg">
							<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Story-Video-magic-flyer.webm" type="video/webm"> 
							<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Story-Video-magic-flyer.mp4" type="video/mp4"> 
							<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Story-Video-magic-flyer.ogv" type="video/ogg"> 
							Votre navigateur ne permet pas de lire les vid??os HTML5. </video>
							-->
							
							</div>
							<!-- Carte perso -->
						<div class="header_perso"><img src="http://www.magicflyer.com/img/cms/2020/Bandeau-Cartes-personnalisees-1600x290.jpg" alt="Personnalisez votre carte papillon !" /></div>
							
							
							
						{/if}
						
					</div>
					<div class="row">
						<div id="center_column" class="center_column col-xs-12">
{/if}
						{*if isset($left_column_size) && !empty($left_column_size)}
						<div id="left_column" class="column col-xs-12 col-sm-{$left_column_size|intval}">{$HOOK_LEFT_COLUMN}</div>
						{/if*}