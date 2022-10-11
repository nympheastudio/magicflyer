{assign var=gabarit value=$product->reference}
<div class="">
	<div class="row header_perso"><img
			src="{$base_dir}img/cms/bandeaux/Bandeau-Cartes-personnalisees-{$smarty.get.id_produit}.jpg"
			alt="Cartes de voeux personnalisées avec un Papillon Magique" />
		<div class="titre_page">

			{if $smarty.get.id_produit=='228'}
				<h1>{l s='Carte Papillon personnalisée Métal Or'}</h1>
				<p id="price"><span class="big-price">{l s='prix metal or'}</span></p>
						<p><b><i> {l s='details metal or'}</i></b></p>
						<details>
							<summary><b>{l s='Détails'}</b></summary>
							<p>{l s='Texte description Carte papillon perso Metal or'}</p>
			{else if $smarty.get.id_produit=='309'}
				<h1>{l s='Carte Papillon personnalisée Or'}</h1>
				<p id="price"><span class="big-price">{l s='prix or'}</span></p>
						<p><b><i> {l s='details or'}</i></b></p>
						<details>
							<summary><b>{l s='Détails'}</b></summary>
							<p>{l s='Texte description Carte papillon perso or'}</p>
			{else}
				<h1>{l s='Carte Papillon personnalisée Argent'}</h1>
				<p id="price"><span class="big-price">{l s='prix argent'}</span></p>
						<p><b><i> {l s='details argent'}</i></b></p>
						<details>
							<summary><b>{l s='Détails'}</b></summary>
							<p>{l s='Texte description Carte papillon perso argent'}</p>
			{/if}



							</details>

					 <a
							href="{l s='Lien plus infos carte papillon'}" target="_blank"
							class="back_button scroll_btn">{l s='+ d\'infos sur la carte papillon'}</a>
		</div>
	</div>

	<div class="row faire-part_pap">
		<form method="POST" action="index.php?controller=personnalisation" enctype="multipart/form-data">
			<div id="contenu_bloc" class="col-md-7 col-xs-12 zoomViewport">
				<div class="zoomContainer {$gabarit}">
					<h2 style="text-align:center">{l s='Intérieur du dépliant'}</h2>

					<div id="img_verso" class="zoomTarget" data-closeclick="true"
						style='background-image: url("{$image2}");background-repeat: no-repeat;width:400px;height:200px;white-space: pre-wrap'>
						<div id="preview_general_verso"></div>
					</div>


					<h2 style="text-align:center">{l s='Enveloppe'}</h2>

					<div id="img_recto" class="zoomTarget" data-closeclick="true"
						style='background-image: url("{$image1}");background-repeat: no-repeat;width:200px;height:200px;white-space: pre-wrap'>
						<div style="display: none !important" id="image_upload_preview_div"><img
								id="image_upload_preview" /></div>
						<div id="preview_general_recto"></div>
						<div id="preview_recto_gauche"></div>
					</div>


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
						<select id="police_fairepart" name="police_fairepart">
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
					<div class="col-xs-12">
						<hr />
					</div>
				</div>

				<div class="row">
					<div class="col-md-6 col-sm-12 col-xs-12">
						<p>{l s='Intérieur du dépliant'}</p>
						<textarea id="texte_ext1_fairepart" name="texte_ext1_fairepart"
							placeholder="{l s='Contenu intérieur gauche'}" style="display: none !important"></textarea>
						<textarea id="texte_ext2_fairepart" name="texte_ext2_fairepart"
							placeholder="{l s='Contenu intérieur droit'}"></textarea>
					</div>
					<div class="col-md-6 col-sm-12 col-xs-12">
						<p>{l s='Enveloppe'}</p>
						<textarea id="date_fairepart" name="date_fairepart" type="date"
							placeholder="{l s='Informations de contact'}" style="display: none !important"></textarea>
						<textarea id="prenoms_fairepart" name="prenoms_fairepart"
							placeholder="{l s='Texte enveloppe'}"></textarea>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12">
						<hr />
					</div>
				</div>

				<div class="row">
					<div class="col-md-6 col-sm-12 col-xs-12">
						<input id="photo_fairepart" name="photo_fairepart" type="file" style="display: none !important">
						<a href="" id="visualiser_fairepart">{l s='Visualiser ma carte'}</a>

						<p class=" small_text">{l s='Si vous souhaitez des modifications supplémentaires sur votre texte
							(mettre en gras, en italique, changer de police, ajouter des photos...), n\'hésitez pas à
							nous contacter par <a href="mailto:magicflyer@magicflyer.com">mail</a> ou au
							04.90.94.31.00.'}</p>


					</div>
					<div class="col-md-6 col-sm-12 col-xs-12">

						<input type="hidden" name="formsubmit" value="1">
						<input type="hidden" name="id_produit" value="{$smarty.get.id_produit}">
						<input type="number" name="qty" value="10" min="10" step="10" style="margin-bottom: 10px" />
						<input type="submit" value="{l s='Ajouter au panier et choisir mes papillons'}">
					</div>
				</div>
				<!--
		<div class="row">
			<div class="col-xs-12"><hr/></div>
		</div>
		
		<div class="row">
			<div class="col-md-6 col-sm-12 col-xs-12 small_text">{l s='À partir de 500 cartes, réalisez vous-même le graphisme de votre Papillon Magique'}<br/>
				<a href="https://www.magicflyer.com{$lang_iso}/content/51-cartes-de-voeux#atout" target="_blank">{l s='En savoir plus'}</a>
			</div>
			<div class="col-md-6 col-sm-12 col-xs-12">
				<a href="https://www.magicflyer.com{$lang_iso}/content/51-cartes-de-voeux#tarifs" target="_blank" id="modification_fairepart">{l s='Nos tarifs'}</a>
			</div>
		</div>	-->



			</div><!-- / bloc recap -->


		</form>

	</div>

	<script type="text/javascript">
		var lg = '{$lang_iso}';
		var gabarit = '{$gabarit}';

	</script>
	{literal}
	<script type="text/javascript">


		$(document).ready(function () {


			if (gabarit === 'gabarit1') {
				$('#prenoms_fairepart').hide();

			}

			$("#contenu_bloc").click(function (evt) {
				$(this).zoomTo({
					targetsize: 5,
					duration: 600,


				});
				evt.stopPropagation();
			});

			if (lg === 'fr') $('#photo_fairepart').uniform({ fileButtonHtml: 'Choisir' });

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
				 location.href = 'https://www.magicflyer.comindex.php?controller=category&id_category=51';
				
				});
		*/

		$(document).ready(function () {
			init_configurateur_personnalisation();

		});


		function init_configurateur_personnalisation() {

			visualiser_fairepart();

		}

		function visualiser_fairepart() {


			$("#visualiser_fairepart").click(function (e) {

				e.preventDefault();
				var couleur = $('#couleur_fairepart').val();
				var texte1 = $('#texte_ext1_fairepart').val();
				var texte2 = $('#texte_ext2_fairepart').val();
				var police = $('#police_fairepart').val();
				var prenoms = $('#prenoms_fairepart').val();
				var date_fairepart = $('#date_fairepart').val();

				var police_real;

				if (police == 1) police_real = "Arial";
				if (police == 2) police_real = "Comic Sans MS";
				if (police == 3) police_real = "Trebuchet MS";
				if (police == 4) police_real = "Lucida Sans";
				if (police == 5) police_real = "Tahoma";
				if (police == 6) police_real = "Verdana";
				if (police == 7) police_real = "Impact";
				if (police == 8) police_real = "MV Boli";
				if (police == 9) police_real = "Segoe Print";
				if (police == 10) police_real = "Segoe Script";

				$('#preview_recto_gauche').html('');
				$('#preview_general_recto').html('');
				$('#preview_general_verso').html('');

				$('#preview_general_recto').html(

					'<span style=\'font-family: "' + police_real + '";color:' + couleur + '\'>' +
					prenoms + '</span>'
				);

				$('#preview_recto_gauche').html(

					'<span style=\'font-family: "' + police_real + '";color:' + couleur + '\'>' +
					date_fairepart + '</span>'
				);

				$('#preview_general_verso').html(

					'<div style=\'font-family: "' + police_real + '";color:' + couleur + ';\'><div id="contenu_preview_gauche">' +
					texte1 + '</div><div id="contenu_preview_droit" style="top:47px;"><div class="masque_texte"></div><div class="masque_texteTG"></div><div class="masque_texteTD"></div><div class="masque_texteBG"></div><div class="masque_texteBD"></div><p>' +
					texte2 + '</p></div></div>'
				);


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