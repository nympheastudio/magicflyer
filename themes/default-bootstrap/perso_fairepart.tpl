{***assign var=gabarit value=$product->reference}
<div class="">
<div class="row header_perso"><img src="{$base_dir}img/cms/bandeaux/cartes_de_voeux_entreprise.jpg" alt="Cartes de voeux entreprise avec un Papillon Magique" />
<div class="titre_page">
<h1>{l s='Cartes de voeux 2019'}</h1>
<p>{l s='Personnalisez votre carte de voeux ci-dessous.'}</p>
<p id="price" ><span class="big-price">{l s='24,5 €'}</span><b><i> {l s='Le Lot de 10 cartes + Enveloppes personnalisées (TTC)'}</i></b></p>
</div>
</div>

<div class="row">
<form method="POST" action="index.php?controller=personnalisation" enctype="multipart/form-data">
	<div  id="contenu_bloc" class="col-md-7 col-xs-12 zoomViewport">
		<div class="zoomContainer {$gabarit}">
			<h2 style="text-align:center">{l s='Extérieur du dépliant'}</h2>
			
			<div id="img_recto" class="zoomTarget" data-closeclick="true" style='background-image: url("{$image1}");width:400px;height:200px;white-space: pre-wrap' >
			<div   id="image_upload_preview_div"><img   id="image_upload_preview"/></div>

			<div id="preview_recto_gauche"></div>
			<div id="preview_general_recto"></div>
			</div>

			<h2 style="text-align:center">{l s='Intérieur du dépliant'}</h2>
			
			<div id="img_verso" class="zoomTarget" data-closeclick="true" style='background-image: url("{$image2}");width:400px;height:200px;white-space: pre-wrap' ><div id="preview_general_verso"></div></div>

		</div>
	</div>

	<div id="bloc_recap_fairepart" class="col-md-5 col-xs-12">
		<h2>{l s='Personnalisation'}</h2>
		<div class="row">
			<div class="col-md-6 col-sm-12 col-xs-12">
				<select id="couleur_fairepart" name="couleur_fairepart">
				<option disabled>{l s='Couleur d\'écriture'}</option>
				<option value="#000">{l s='Noir'}</option>
				<option value="#FFF">{l s='Blanc'}</option>
				<option value="#808080">{l s='Gris'}</option>	
				<option value="#C0C0C0">{l s='Argenté'}</option>
				<option value="#FF0000">{l s='Rouge'}</option>	
				<option value="#800000">{l s='Marron'}</option>	
				<option value="#FFFF00">{l s='Jaune'}</option>	
				<option value="#808000">{l s='Olive'}</option>	
				<option value="#00FF00">{l s='Vert citron'}</option>	
				<option value="#008000">{l s='Vert'}</option>	
				<option value="#00FFFF">{l s='Turquoise'}</option>	
				<option value="#0000FF">{l s='Bleu'}</option>	
				<option value="#000080">{l s='Bleu marine'}</option>	
				<option value="#FF00FF">{l s='Fuchsia'}</option>	
				<option value="#800080">{l s='Violet'}</option>
				</select>
			</div>
			<div class="col-md-6 col-sm-12 col-xs-12">
				<select  id="police_fairepart" name="police_fairepart">
				<option disabled>{l s='Choix de la police'}</option>
				<option value="1">Arial</option>
				<option value="2">Comics</option>
				<option value="3">Trebuchet</option>
				<option value="4">Lucida Sans</option>
				<option value="5">Tahoma</option>
				<option value="6">Verdana</option>
				<option value="7">Impact</option>
				<option value="8">MV Boli</option>
				<option value="9">Segoe Print</option>
				<option value="10">Segoe Script</option>
				</select>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-12"><hr/></div>
		</div>
		
		<div class="row">
			<div class="col-md-6 col-sm-12 col-xs-12">
				<p>{l s='Intérieur du dépliant'}</p>
				<textarea id="texte_ext1_fairepart" name="texte_ext1_fairepart" placeholder="{l s='Contenu intérieur gauche'}"></textarea>
				<textarea id="texte_ext2_fairepart" name="texte_ext2_fairepart" placeholder="{l s='Contenu intérieur droit'}"></textarea>
			</div>
			<div  class="col-md-6 col-sm-12 col-xs-12">
				<p>{l s='Extérieur du dépliant'}</p>
				<textarea id="prenoms_fairepart" name="prenoms_fairepart" placeholder="{l s='Texte face'}"></textarea>
				<textarea id="date_fairepart" name="date_fairepart" type="date" placeholder="{l s='Informations de contact'}"></textarea>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-12"><hr/></div>
		</div>

		<div class="row">
			<div class="col-md-6 col-sm-12 col-xs-12">		
				<input id="photo_fairepart" name="photo_fairepart" type="file"  >
				<a href="" id="visualiser_fairepart">{l s='Visualiser ma carte'}</a>
			</div>
			<div class="col-md-6 col-sm-12 col-xs-12">
				<input type="hidden" name="formsubmit" value="1" >
				<input type="hidden" name="id_produit" value="{$smarty.get.id_produit}" >
				<input type="number" name="qty" value="50" step="10" />
				<input type="submit" value="{l s='Ajouter au panier et choisir mes papillons'}" >
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-12"><hr/></div>
		</div>
		
		<div class="row">
			<div class="col-md-6 col-sm-12 col-xs-12 small_text">{l s='À partir de 500 cartes, réalisez vous-même le graphisme de votre Papillon Magique'}<br/>
				<a href="https://www.magicflyer.com/{$lang_iso}/content/51-cartes-de-voeux#atout" target="_blank">{l s='En savoir plus'}</a>
			</div>
			<div class="col-md-6 col-sm-12 col-xs-12">
				<a href="https://www.magicflyer.com/{$lang_iso}/content/51-cartes-de-voeux#tarifs" target="_blank" id="modification_fairepart">{l s='Nos tarifs'}</a>
			</div>
		</div>	
				

	
	</div><!-- / bloc recap -->


</form>

</div>

<script type="text/javascript">
var lg = '{$lang_iso}';
var gabarit = '{$gabarit}';

</script>
{literal}
<script type="text/javascript">


$(document).ready(function(){
	

	if(gabarit==='gabarit1'){
		$('#prenoms_fairepart').hide();
		
	}
	
	$("#contenu_bloc").click(function(evt) {
        $(this).zoomTo({
		targetsize:5,
		duration:600,
		
		
		});
        evt.stopPropagation();
    });
	
	if(lg==='fr')$('#photo_fairepart').uniform({fileButtonHtml: 'Choisir'});

});
</script>

{/literal}
<script type="text/javascript">


/*
$.ajax({
		  url: 'index.php?controller=cart&ajax=true&add=1&id_product='+id_p+'&qty='+qty,
		  cache: true,
		  async: true,
		}).done(function() {
		 location.href = 'https://www.magicflyer.com/index.php?controller=category&id_category=51';
		
		});
*/

$(document).ready(function(){
	init_configurateur_personnalisation();

});


function init_configurateur_personnalisation(){
	
	visualiser_fairepart();

}

function visualiser_fairepart(){
	
	
	$("#visualiser_fairepart").click(function(e) {
		
		e.preventDefault();
		var couleur 		= $('#couleur_fairepart').val();
		var texte1 			= $('#texte_ext1_fairepart').val();
		var texte2 			= $('#texte_ext2_fairepart').val();
		var police 			= $('#police_fairepart').val();
		var prenoms 		= $('#prenoms_fairepart').val();
		var date_fairepart 	= $('#date_fairepart').val();
		
		var police_real;
		
		if(police==1) police_real ="Arial";
		if(police==2) police_real ="Comic Sans MS";
		if(police==3) police_real ="Trebuchet MS";
		if(police==4) police_real ="Lucida Sans" ;
		if(police==5) police_real ="Tahoma";
		if(police==6) police_real ="Verdana";
		if(police==7) police_real ="Impact";
		if(police==8) police_real ="MV Boli";
		if(police==9) police_real ="Segoe Print";
		if(police==10) police_real ="Segoe Script";
		
		$('#preview_recto_gauche').html('');
		$('#preview_general_recto').html('');
		$('#preview_general_verso').html('');
		
		$('#preview_general_recto').html(

		'<span style=\'font-family: "'+ police_real +'";color:'+ couleur +'\'>'+
		prenoms 	+ '</span>' 
		);		
		
		$('#preview_recto_gauche').html(

		'<span style=\'font-family: "'+ police_real +'";color:'+ couleur +'\'>'+
		date_fairepart + '</span>' 
		);
		
		$('#preview_general_verso').html(

		'<div style=\'font-family: "'+ police_real +'";color:'+ couleur +';top:47px;\'><div id="contenu_preview_gauche">'+
		texte1 		+ '</div><div id="contenu_preview_droit" >' +	
		texte2 		+ '</div></div>' 
		);

		//$('#contenu_preview_droit').css('top','47px');
	});
	
	$("#photo_fairepart").change(function () {
        readURL(this);
    });
	
}


function readURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();

		reader.onload = function (e) {
			$('#image_upload_preview').attr('src', e.target.result);
			
		}

		reader.readAsDataURL(input.files[0]);
	}
}

</script>
