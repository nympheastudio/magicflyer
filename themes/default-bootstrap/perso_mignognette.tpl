{assign var=cur_id_product value=$smarty.get.id_product}

{if $cur_id_product == ''}
{* back 2 prev*}
<br><br><br><center><h1>Erreur N° produit, redirection vers la page précédente !</h1></center><div style="display:none"><script>history.back()</script>
{/if}

<div class="">
<div class="row header_perso">
	<img class="desktop" src="{$base_dir}img/cms/bandeaux/{$cur_id_product}.jpg" alt="" />

	{if $cur_id_product == '321'}
	<img class="mobile" src="https://www.magicflyer.com/img/p/1/1/2/6/1126.jpg" />
	{elseif $cur_id_product == '322'}
	<img class="mobile" src="https://www.magicflyer.com/img/p/1/1/2/7/1127.jpg" />
	{elseif $cur_id_product == '323'}
	<img class="mobile" src="https://www.magicflyer.com/img/p/1/1/2/8/1128.jpg" />
	{elseif $cur_id_product == '324'}
	<img class="mobile" src="https://www.magicflyer.com/img/p/1/1/2/9/1129.jpg" />
	{elseif $cur_id_product == '325'}
	<img class="mobile" src="https://www.magicflyer.com/img/p/1/1/3/0/1130.jpg" />
	{/if}


<div class="titre_page">
<h1>{$product->name}</h1>

{if $cur_id_product == '321'}
<p id="price" ><span class="big-price">{l s='3,5 € carte'}</span></p>
{elseif $cur_id_product == '322'}
<p id="price" ><span class="big-price">{l s='3,5 € carte + enveloppe'}</span></p>
{elseif $cur_id_product == '323'}
<p id="price" ><span class="big-price">{l s='3,5 € carte pap + enveloppe'}</span></p>
{elseif $cur_id_product == '324'}
<p id="price" ><span class="big-price">{l s='3,5 € Livret'}</span></p>
{elseif $cur_id_product == '325'}
<p id="price" ><span class="big-price">{l s='3,5 € mini-carte'}</span></p>
{/if}

{if $cur_id_product == '321'}
<p  ><b><i> {l s='contenu Mignonnette + Papillon Magique'}</i></b></p>
{elseif $cur_id_product == '322'}
<p ><b><i> {l s='contenu Carte + Papillon Magique + enveloppe'}</i></b></p>
{elseif $cur_id_product == '323'}
<p><b><i> {l s='contenu Carte + Papillon Magique + enveloppe'}</i></b></p>
{elseif $cur_id_product == '324'}
<p ><b><i> {l s='contenu Livret + Papillon Magique'}</i></b></p>
{elseif $cur_id_product == '325'}
<p ><b><i> {l s='contenu Mini Carte double volet + Papillon Magique'}</i></b></p>
{/if}

<details>
	<summary><b>{l s='Détails'}</b></summary>
	


<p>{$product->description_short}</p>


</details>
<!--
<p><a href="#" onclick="history.go(-1)" class="back_button border-btn"><span class="fa fa-angle-left"> </span> {l s='back'}</a> -->

{if $attachments}
{foreach from=$attachments item=attachment}
<a href="{$link->getPageLink('attachment', true, NULL, "id_attachment={$attachment.id}")|escape:'html':'UTF-8'}" class="back_button border-btn"> 	{l s='Télécharger le gabarit'}	</a> 
{/foreach}
{/if}


{if $cur_id_product == '321'}
<a  target="_blank" href="{l s='lien infos mignonette'}" class="back_button border-btn">{l s='+ d\'infos sur la mignonette'}</a></p>
{elseif $cur_id_product == '322'}
<a  target="_blank" href="{l s='lien infos carte personnalisée'}" class="back_button border-btn">{l s='+ d\'infos sur la carte personnalisée'}</a></p>

{elseif $cur_id_product == '323'}
<a  target="_blank" href="{l s='lien infos carte papillon'}" class="back_button border-btn">{l s='+ d\'infos sur la carte papillon'}</a></p>
{elseif $cur_id_product == '324'}
<a  target="_blank" href="{l s='lien infos livret'}" class="back_button border-btn">{l s='+ d\'infos sur le livret'}</a></p>
{elseif $cur_id_product == '325'}
<a  target="_blank" href="{l s='lien infos Mini Carte'}" class="back_button border-btn">{l s='+ d\'infos sur la Mini Carte'}</a></p>
{/if}



</div>



</div>
<button onclick="afficherCacher()" class="mobile">{l s='Récapitulatif'}</button>
<div id="bloc_recap" class="col-md-3 col-xs-12">

<form id="form_perso_mignognette" method="POST" action="index.php?controller=perso_mignognette" enctype="multipart/form-data">

<div id="bloc_ss_bouton">
<h2>{l s='Récapitulatif'}</h2>
<div class="form-group select quantity_perso">
<label>{l s='Quantité'} :</label>

<input  id="qty_livret" name="qty_livret" class="" placeholder="Min. {$product->minimal_quantity} ex" min="{$product->minimal_quantity}" value="{$product->minimal_quantity}" type="number" >
<script type="text/javascript">
var product_minimal_quantity =  '{$product->minimal_quantity}';
</script>

</div>

<div class="form-group select">
<input id="photo_fairepart" name="photo_fairepart" type="file" class="noUniform form-control " >
</div>

<p>{l s='Votre sélection de papillons'}</p>
<ul id="liste_pap"></ul>

<div style="display:none">
<hr>
<p>Option</p>
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
</div>


</div>
</form>

<div class="button_input">
<a href="#"  id="envoyer_form">{l s='Ajouter au panier'}</a>
</div>
</div>
<div id="contenu_bloc" class="col-md-9 col-xs-12">
<div class="titre_page clearfix">
<h2>{l s='Choisissez vos papillons'}</h2>
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
<li class="col-sm-4 col-xs-6 ajax_block_product {if $smarty.foreach.products.first}first_item{elseif $smarty.foreach.products.last}last_item{/if} {if $smarty.foreach.products.index % 2}alternate_item{else}item{/if} clearfix"

data-cat="{foreach from=Product::getProductCategoriesFull($product.id_product) item=cat}{$cat.id_category},{/foreach}"

data-color="{foreach from=$product.features item=feature}{$feature.value|escape:'html':'UTF-8'}{/foreach}"
>
<div class="center_block">
<a href="javascript:void(0);" id="lien_{$product.id_product|intval}" class="lien_pap" data-id-product="{$product.id_product|intval}" title="{$product.name|escape:'htmlall':'UTF-8'|truncate:35:'...'}" >
{*if isset($product.new) && $product.new == 1}<span class="new">{l s='New'}</span>{/if*}
<div class="infos-hover">
<h4>{$product.name|escape:'htmlall':'UTF-8'|truncate:35:'...'}</h4>


{if $product.price >0 }
<!--<span>{l s='Supplément +'}{$product.price * 10}€{l s='/10 papillons'}</span>-->
{/if}
</div>
<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} />
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
var lang_iso = '{$lang_iso}';
var id_product = '{$smarty.get.id_product}';



{if $cur_id_product == '324'}
	var multiplicateur = 10;//produit vendu par lot de 10
{else}
	var multiplicateur = 1;
{/if}

$(document).ready(function(){
	fix_html5_input_number_minmax();
	delete_papillon();
	add_papillon();
	afficher_cacher_btn_achat();
	envoyer_form();	

});

function fix_html5_input_number_minmax(){

/*
	
	$('input[type="number"]').on('keyup keypress blur change', function(e) {
    
	    v = parseInt($(this).val());
        min = parseInt($(this).attr('min'));
        max = parseInt($(this).attr('max'));
	
	
        if (v < min){
            $(this).val(min);
        } else if (v > max){
            $(this).val(max);
        }
});
*/

}


function envoyer_form(){
	$('#envoyer_form').click(function(e) {
		
		e.preventDefault;
		var prenom_pap;
		var date_pap;
		var qty_pap= $("#qty_pap").val();
		creer_panier();
		
	});
}

function afficher_cacher_btn_achat(){
	
	$('#envoyer_form').hide();
	
	var nb_pap_cart_ = $('.item_perso_papillon').size();
	
	if( nb_pap_cart_ === 0){
		$('#envoyer_form').hide();
	}else{
		$('#envoyer_form').show();
	}
		
}	

function creer_panier(){
	
	var prenom_pap,date_pap,qty_pap;
	var color = '';
	var qty_livret= $("#qty_livret").val();
	var qty_env= 0;
	var loader_html = '<div class="perso_loader"><center><img src="{$base_dir}themes/default-bootstrap/img/preloading.svg"><p>{l s="Loading..." }</p></center></div>';

	
	
	
	if(qty_livret < product_minimal_quantity){
	
		if(lang_iso==='fr'){
			swal("Quantité minimale non atteinte ("+product_minimal_quantity+")");
		}else if(lang_iso==='es'){
			swal("bug_qty "+product_minimal_quantity+"");
		}else{
			swal("Error with minimal quantity  ("+product_minimal_quantity+") ");
		}
		return false;
	}
	
	
	var formData = new FormData($("#form_perso_mignognette")[0]);
	
	
	$('#center_column').hide().after(loader_html);

	$('#liste_pap li').each(function(i)
	{
		if (typeof ids_pap != 'undefined'){
			ids_pap = ids_pap + $(this).attr('rel')+',';
		}else{
			ids_pap = $(this).attr('rel')+',';
		}
	});
	
	datas = "ids="+ids_pap+"&qty_livret="+qty_livret+"&color="+color+"&qty_env="+qty_env+"&id_product="+id_product ;
	
	

	$.ajax({
			url: "index.php?controller=perso_mignognette&"+datas,
			type: "POST",
					data : formData,
			processData: false,
			contentType: false,
			beforeSend: function() {
						//console.log('send ajax data..');
					},
			success: function(data){

						location.href = 'index.php?controller=order-opc';


					},
			error: function(xhr, ajaxOptions, thrownError) {
						console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
		});
	
}



function add_papillon(){


	$(".lien_pap").click(function(e) {

		e.preventDefault;
		let nb_livret = $('#qty_livret').val();
		let nb_papillon_max = 10;
		let qty_pap = 10;
		let nb_papillon_selectionne = $('.item_perso_papillon').size()+1;
		let id_pap = $(this).attr('data-id-product');
		let nom_pap = $(this).attr('title');
		let ajout_possible = 1;


		$(".item_perso_papillon").each(function(){
			if($(this).attr('rel')=== id_pap){
				e.preventDefault;
				$('#li_'+ $(this).attr("rel")).remove();
				$('a#lien_'+ $(this).attr("rel")).find('img').css("opacity","1");
				$('a#lien_'+$(this).attr("rel")).find('img').css('background-color',"");

				var qty_pap__ =  multiplicateur *  nb_livret;
				
				var nb_pap_cart__ = $('.item_perso_papillon').size();
				
				$('.quantity_papillon').each(function () {
					$(this).text( ( qty_pap__ / nb_pap_cart__ ) + ' x');
				});
				ajout_possible = 0;
			}
		})

		if(ajout_possible == 1){
			nb_papillon_max = multiplicateur *  nb_livret;
			

			if( !$('.delete_papillon[rel="'+id_pap+'"]').length )
			{
				qty_pap = nb_papillon_max;
				
				if(nb_livret >= 2 && nb_papillon_selectionne >=2 ){
					qty_pap = nb_papillon_max / nb_papillon_selectionne;
					/*alert(
					'nb_papillon_max '+nb_papillon_max+' / nb_papillon_selectionne'+ nb_papillon_selectionne +
					' ==========> qty_pap:'+qty_pap);
					*/
				}
				
				//bloque à deux papillons
				if( nb_papillon_selectionne * 10 > nb_papillon_max  ){
					if(lang_iso==='fr'){
						swal("Vous avez déjà choisi vos papillons. Vous pouvez supprimer votre choix pour en choisir de nouveaux.");
					}else if(lang_iso==='es'){
						swal("Ya has elegido tus mariposas. Puede eliminar su elección para elegir otras nuevas.");
					}else{
						swal("You have already chosen your butterflies. You can delete your choice to choose new ones.");
					}
					return;
				}
				
				$( this ).find('img').css("opacity", "0.1");
				$( this ).find('img').css("background-color", "#000");

				let new_row = '<li class="item_perso_papillon" id="li_'+ id_pap + '" rel="'+ id_pap + '"> <span class="quantity_papillon" rel="'+ id_pap + '">'+qty_pap+' x</span><h3>'+nom_pap+'</h3> <span class="delete_papillon" rel="'+id_pap+'"></span></li>';
				
				
				$('.quantity_papillon').text(qty_pap+' x');
				
				$('#liste_pap').append(new_row);
				
			}
			delete_papillon();
			afficher_cacher_btn_achat();
		}
		
	});

	
}

function delete_papillon(){
	$('.delete_papillon').click(function(e) {
		e.preventDefault;
		let nb_livret = $('#qty_livret').val();
		
		console.log( 'a supp:' + $(this).attr('rel') ) ;
		$('#li_'+ $(this).attr('rel')).remove();
		
		$( 'a#lien_'+ $(this).attr('rel') ).find('img').css('opacity', '1');
		$( 'a#lien_'+ $(this).attr('rel') ).find('img').css('background-color', '');
		
		var qty_pap__= multiplicateur *  nb_livret;
		var nb_pap_cart__ = $('.item_perso_papillon').size();
		
		$('.quantity_papillon').each(function() {
			$(this).text((qty_pap__ / nb_pap_cart__)  +' x');
		});
		
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
