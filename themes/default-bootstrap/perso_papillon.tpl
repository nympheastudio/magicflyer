

{if $smarty.get.color=='or'}{assign var="idp" value="184"}{/if}
{if $smarty.get.color=='argente'}{assign var="idp" value="173"}{/if}
{if $smarty.get.color=='metal'}{assign var="idp" value="312"}{/if}
			
			
<div class="">
	<div class="row header_perso">
		
		<div class="titre_page">
			<div class="block_infos col-sm-6 col-md-4 col-lg-3 desktop_only">
				{if $smarty.get.color=='or'}
					<h1>{l s='Personnaliser ma carte Papillon® OR'}</h1>
				{else if $smarty.get.color=='argente'}
					<h1>{l s='Personnaliser ma carte Papillon® Argent'}</h1>
				{else if $smarty.get.color=='metal'}
					<h1>{l s='Personnaliser ma carte Papillon® Métal'}</h1>				
				{/if}
				<p>{l s='Description carte papillon'}</p>
				<center><img src="/img/certifications-papillon-magique.png"> 
					<!--<a class="scroll_btn" href="#">Voir le livret en vidéo</a>-->				
					<a href="{l s='lien infos carte'}" class="back_button scroll_btn" target="_blank">{l s='+ d\'infos sur la carte Papillon'}</a>
				</center>

				
			</div>
		</div>

		<div id="thumbs_list" class=" desktop_only">
            <div id="slider">
                <a href="#" class="control_next"></a>
                <a href="#" class="control_prev"></a>
                <ul style="width: 100% !important;" id="thumbs_list_frame">
					<li  id="01">
						<img src="/img/cms/slider-lanceurs/carte-papillon-magique-01.jpg">
                    </li>
					<li  id="02">
						<img src="/img/cms/slider-lanceurs/carte-papillon-magique-02.jpg">
                    </li>
					<li  id="03">
						<img src="/img/cms/slider-lanceurs/carte-papillon-magique-03.jpg">
                    </li>
					<li  id="04">
						<img src="/img/cms/slider-lanceurs/carte-papillon-magique-04.jpg">
                    </li>
                    <!--{if isset($images)}
                    {foreach from=$images item=image name=thumbnails}
                    {assign var=imageIds value="`$product->id`-`$image.id_image`"}
                    {if !empty($image.legend)}
                    {assign var=imageTitle value=$image.legend|escape:'html':'UTF-8'}
                    {else}
                    {assign var=imageTitle value=$product->name|escape:'html':'UTF-8'}
                    {/if}
                    <li  id="01">
						<img src="/img/cms/slider-lanceurs/livret-papillon-magique-01.jpg">
                        <img src="{$link->getImageLink($product->link_rewrite, $imageIds, 'thickbox_default')|escape:'html':'UTF-8'}">
                    </li>
					{/foreach}
                    {/if}-->
                </ul>
            </div> 
        </div>
        {literal}
            <script>
				jQuery(document).ready(function ($) {

				setInterval(function () {
				moveRight();
				}, 3000);

				var slideCount = $('#slider ul li').length;
				var slideWidth = $('#slider ul li').width();
				var slideHeight = $('#slider ul li').height();
				var sliderUlWidth = slideCount * slideWidth;

				$('#slider').css({ width: slideWidth, height: slideHeight });

				$('#slider ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });

				$('#slider ul li:last-child').prependTo('#slider ul');

				function moveLeft() {
				$('#slider ul').animate({
				left: + slideWidth
				}, 600, function () {
				$('#slider ul li:last-child').prependTo('#slider ul');
				$('#slider ul').css('left', '');
				});
				};

				function moveRight() {
				$('#slider ul').animate({
				left: - slideWidth
				}, 600, function () {
				$('#slider ul li:first-child').appendTo('#slider ul');
				$('#slider ul').css('left', '');
				});
				};

				$('a.control_prev').click(function () {
				moveLeft();
				});

				$('a.control_next').click(function () {
				moveRight();
				});

				});    

            </script>

        {/literal}


	</div>


	<div id="bloc_recap" class="col-md-3 col-xs-12">

		<div class="Contenthead_perso">
			<p id="price"><span class="big-price">{l s='30 €'}</span></p>
			<h3>{l s='10 cartes papillon + 10 papillons magiques®'}</h3>
		</div>

		<a onclick="afficherCacher()" class="scroll_btn mobile_only"><span class="smalltext">{l s='Cliquez pour choisir'}</span>{l s='Récapitulatif'}</a>
			


		<div id="bloc_ss_bouton">
			<h2 class="mobile_only">{l s='Récapitulatif'}</h2>
			<div class="form-group select quantity_perso">
				<label class="col-xs-6">1/ {l s='Quantité'}</label>
				<select id="qty_livret"   class="col-xs-6">
					<option>01</option>
					<option>02</option>
					<option>03</option>
					<option>04</option>
					<option>05</option>
					<option>06</option>
					<option>07</option>
					<option>08</option>
					<option>09</option>
					<option>10</option>
					<option>11</option>
					<option>12</option>
					<option>13</option>
					<option>14</option>
					<option>15</option>
					<option>16</option>
					<option>17</option>
					<option>18</option>
					<option>19</option>
					<option>20</option>
				</select>
			</div>



<!-- 			<div class="form-group select">

				<select id="choix_couleur">
					<option value="">{l s='Couleur de la carte Papillon'}</option>
					<option value="or"> {l s='Or'}</option>
					<option value="argente"> {l s='Argenté'}</option>

				</select>

			</div> -->
			<input type="hidden" id="choix_couleur" value="{$smarty.get.color}" />

			<div class="form-group checkbox">
				<div class="checker" id="uniform-pas_perso"><span><input type="checkbox" name="enveloppes"
							id="enveloppes"></span></div>
				<label id="enveloppes_label">{l s='je souhaite ajouter des enveloppes à ma commande'}</label>
			</div>


			<label class="col-xs-12">3/ {l s='Votre sélection de papillons'}</label>

			<ul id="liste_pap"></ul>

			<!-- ANCIENNE FONCTION AVEC SELECT - CONNECTER LA PARTIE PLUS HAUT AVEC CHECKBOX ET NOMBRE D'ENVELOPPES EN FONCTION DU NOMBRE DE CARTES
<p>{l s='Enveloppes de haute qualité'}<br>({l s='18.5x18.5cm 120gr'}) :</p>

<select id="options_enveloppes" >
<option>00</option>
<option>01</option>
<option>02</option>
<option>03</option>
<option>04</option>
<option>05</option>
<option>06</option>
<option>07</option>
<option>08</option>
<option>09</option>
<option>10</option>
</select>
-->


		</div>
		<div class="button_input">
			<a href="#" id="envoyer_form">{l s='Ajouter au panier'}</a>
		</div>
	</div>
	<div id="contenu_bloc" class="col-md-9 col-xs-12">
		<div class="titre_page clearfix">
			<h2>{l s='Choisissez vos papillons'}
				<!-- <span>{l s='(10 papillons par couleur)'}</span> -->
			</h2>
			<!--<div class="filtre_listing">

<label>
<select id="choix_categorie">
<option value="">{l s='Filtrer par Categorie'}</option>
<option value="53">{l s='cat 1'}</option>
<option value="54">{l s='cat 2'}</option>
</select>
</label>
</div>-->
			<div id="right_column" class="col-xs-12">
				{$HOOK_RIGHT_COLUMN}
				{include file="./modules/blocklayered/blocklayered.tpl"}
			</div>
		</div>

		<div class="listing">
			{if isset($products) && $products}
			<!-- Products list -->
			<ul id="product_list" class="clear">
				{foreach from=$products item=product name=products}

				{if $product.id_category_default != 56}
				<li class="col-lg-2 col-md-3 col-xs-4  ajax_block_product {if $smarty.foreach.products.first}first_item{elseif $smarty.foreach.products.last}last_item{/if} {if $smarty.foreach.products.index % 2}alternate_item{else}item{/if} clearfix"
					data-cat="{foreach from=Product::getProductCategoriesFull($product.id_product) item=cat}{$cat.id_category},{/foreach}"
					data-color="{foreach from=$product.features item=feature}{$feature.value|escape:'html':'UTF-8'}{/foreach}">
					<div class="center_block">
						<a href="javascript:void(0);" id="lien_{$product.id_product|intval}" class="lien_pap"
							data-id-product="{$product.id_product|intval}"
							title="{$product.name|escape:'htmlall':'UTF-8'|truncate:35:'...'}">
							{*if isset($product.new) && $product.new == 1}<span class="new">{l s='New'}</span>{/if*}
							<div class="infos-hover">
								<!-- <span>{l s='Add 10 butterflies'}</span>
-->
								<h4>{$product.name|escape:'htmlall':'UTF-8'|truncate:35:'...'}</h4>


								{if $product.price >0 }
								<!--<span>{l s='Supplément +'}{$product.price * 10}€{l s='/10 papillons'}</span>-->
								{/if}
							</div>
							<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')}"
								{if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}" {/if} />
						</a>
					</div>

				</li>
				{/if}
				{/foreach}
				<!-- /Products list -->
				{/if}
		</div>
	</div>
</div>
<script type="text/javascript">

	var ids_pap;
	var datas;
	var dynamic_limit = 10;

	$(document).ready(function () {


		init_recherche();

		//init_configurateur();
		delete_papillon();
		add_papillon();
		$('#envoyer_form').hide();
		afficher_cacher_btn_achat()


		$('#envoyer_form').click(function (e) {

			e.preventDefault;
			var prenom_pap;//= $("#prenom_pap").val().replace("&", "ET_HTML");
			var date_pap;//= $("#date_pap").val();
			var qty_pap = $("#qty_pap").val();

			creer_panier();




		});
	});

	function afficher_cacher_btn_achat() {

		var nb_pap_cart_ = $('.item_perso_papillon').size();
		//alert(nb_pap_cart_);
		if (nb_pap_cart_ === 0) {
			$('#envoyer_form').hide();
		} else {
			$('#envoyer_form').show();
		}




	}

	function creer_panier() {

		var prenom_pap, date_pap, qty_pap;//= $("#prenom_pap").val().replace("&", "ET_HTML");
		var color = $("#choix_couleur").val();
		var qty_livret = $("#qty_livret").val();
		var qty_env = 0;//$("#options_enveloppes").val();
		
		 if ($("#enveloppes").is(":checked")) {
			qty_env = qty_livret;
		}

		if (color === '') {
			if(lang_iso==='fr'){			
			swal("Veuillez choisir la couleur de vos cartes");
			}else if(lang_iso==='es'){
				swal("Elija el color de sus tarjetas");
			}else{
				swal("Please choose the color of your cards");
				}





			return;
		}

		$('#center_column').hide();
		$('#center_column').after('<div class="perso_loader"><center><img src="{$base_dir}themes/default-bootstrap/img/preloading.svg"><p>{l s="Loading..." }</p></center></div>');

		$('#liste_pap li').each(function (i) {
			if (typeof ids_pap != 'undefined') {
				ids_pap = ids_pap + $(this).attr('rel') + ',';
			} else {
				ids_pap = $(this).attr('rel') + ',';
			}
		});

		//datas = "ids="+ids_pap+"&prenom="+prenom_pap+"&date="+date_pap+"&qty="+qty_pap;
		datas = "ids=" + ids_pap + "&qty_livret=" + qty_livret + "&color=" + color + "&qty_env=" + qty_env;



		$.ajax({
			url: "index.php?controller=perso_papillon&" + datas,
			type: "GET",
			success: function (data) {
				//alert('succes:'+data);
				location.href = 'index.php?controller=order-opc';
			},
			error: function () {
				alert('error');
			}
		});

	}

	function init_recherche() {
		/*
			$('#choix_couleur').on('change', function() {
				//alert( this.value );
				
				//$('.ajax_block_product').attr('data-color')
				var current_color = this.value;
				$( ".ajax_block_product" ).each(function( index, current_color) {
					
					alert( current_color +'-'+$( this ).attr('data-color') );
					
					
					if( $( this ).attr('data-color') !== current_color){
						$( this ).hide();
					}else{
						$( this ).show();
					}
				});
				
			});
			*/
	}


	function init_configurateur() {

		//delete_papillon();
		//add_papillon();
	}


	function add_papillon() {

		$(".lien_pap").click(function (e) {

			e.preventDefault;
			let nb_livret = $('#qty_livret').val();
			let nb_papillon_max = 10;
			let qty_pap = 10;
			let nb_papillon_selectionne = $('.item_perso_papillon').size() + 1;
			let id_pap = $(this).attr('data-id-product');
			let nom_pap = $(this).attr('title');
			let lang_iso = '{$lang_iso}';
			let ajout_possible = 1;

			$(".item_perso_papillon").each(function(){
			
			
			//console.log('->'+$(this).attr('rel'));
			
				if($(this).attr('rel') === id_pap){
				
					console.log('pap id '+$(this).attr('rel')+' deja present ds le panier');
					e.preventDefault;
					console.log("a supp:" + $(this).attr("rel"));
					$('#li_' + $(this).attr("rel")).remove();


					$('a#lien_' + $(this).attr("rel")).find('img').css("opacity", "1");
					$('a#lien_' + $(this).attr("rel")).find('img').css("background-color", "");


					var qty_pap__ = 30;
					var nb_pap_cart__ = $('.item_perso_papillon').size();

					//alert( nb_pap_cart__);

					if (nb_pap_cart__ == 2) qty_pap__ = 15;
					if (nb_pap_cart__ == 3) qty_pap__ = 10;

					//$('.quantity_papillon').text(qty_pap__+' x');


					$('.quantity_papillon').each(function () {
						$(this).text(qty_pap__ + ' x');
					});
			
			
					ajout_possible = 0;
				}
			});
			


			if(ajout_possible == 1){

				nb_papillon_max = 10 * nb_livret;
				


		
				if (!$('.delete_papillon[rel="' + id_pap + '"]').length) {
				
				
				
				
				
					qty_pap = nb_papillon_max;

					if (nb_livret >= 2 && nb_papillon_selectionne >= 2) {
						qty_pap = nb_papillon_max / nb_papillon_selectionne;
						/*alert(
						'nb_papillon_max '+nb_papillon_max+' / nb_papillon_selectionne'+ nb_papillon_selectionne +
						' ==========> qty_pap:'+qty_pap);
						*/
					}

					//bloque à deux papillons
					//alert ( nb_papillon_selectionne*10 +'>='+ nb_papillon_max) ;
					if (nb_papillon_selectionne * 10 > nb_papillon_max) {
					
					
					
					
					
						if(lang_iso==='fr'){
							swal("Vous avez déjà choisi vos papillons. Vous pouvez supprimer votre choix pour en choisir de nouveaux.");
						}else if(lang_iso==='es'){
							swal("Ya has elegido tus mariposas. Puede eliminar su elección para elegir otras nuevas.");
						}else{
							swal("You have already chosen your butterflies. You can delete your choice to choose new ones.");
							}

						return;
					}

					
					$(this).find('img').css("opacity", "0.1");
					$(this).find('img').css("background-color", "#000");
				
				
				
				
					let new_row = '<li class="item_perso_papillon" id="li_' + id_pap + '" rel="' + id_pap + '"> <span class="quantity_papillon" rel="' + id_pap + '">' + qty_pap + ' x</span><h3>' + nom_pap + '</h3> <span class="delete_papillon" rel="' + id_pap + '"></span></li>';


					$('.quantity_papillon').text(qty_pap + ' x');

					$('#liste_pap').append(new_row);

					

				}
				
				afficher_cacher_btn_achat();
				delete_papillon();
			}

		});

	
	}

	function delete_papillon() {
		$(".delete_papillon").click(function (e) {
			//alert($(this).attr("rel"));
			e.preventDefault;
			console.log("a supp:" + $(this).attr("rel"));
			$('#li_' + $(this).attr("rel")).remove();


			$('a#lien_' + $(this).attr("rel")).find('img').css("opacity", "1");
			$('a#lien_' + $(this).attr("rel")).find('img').css("background-color", "");


			var qty_pap__ = 30;
			var nb_pap_cart__ = $('.item_perso_papillon').size();

			//alert( nb_pap_cart__);

			if (nb_pap_cart__ == 2) qty_pap__ = 15;
			if (nb_pap_cart__ == 3) qty_pap__ = 10;

			//$('.quantity_papillon').text(qty_pap__+' x');


			$('.quantity_papillon').each(function () {
				$(this).text(qty_pap__ + ' x');
			});



			/*add_papillon();*/
			afficher_cacher_btn_achat();
		});
	}


	function afficherCacher() {
		var x = document.getElementById("bloc_ss_bouton");
		if (x.style.display === "none") {
			x.style.display = "block";
		} else {
			x.style.display = "none";
		}
	}
</script>