/**
 * MODULE PRESTASHOP OFFICIEL CHRONOPOST
 *
 * LICENSE : All rights reserved - COPY AND REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
 * LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
 * SANS ACCORD EXPRES D'OXILEO
 *
 * @author    Oxileo SAS <contact@oxileo.eu>
 * @copyright 2001-2018 Oxileo SAS
 * @license   Proprietary - no redistribution without authorization
 */

$( document ).ready(function() {

    // if(lt_history) {
    // 	// If we have several skybills, add them to the link
    // 	var lt_numbers = lt_history[0];
    // 	for (var i = 1; i < lt_history.length; i++) {
    // 		lt_numbers = lt_numbers + '<br>' + lt_history[i];
    // 	}
    // 	$('span.shipping_number_show a').html(lt_numbers);
    // }

    if (lt_history_link !== false && lt_history_link != '') {


        $('span.shipping_number_show a').remove();
        var lt_numbers = '';
        $.each(lt_history_link, function (index, value) {
            lt_numbers += '<a href="'+value+'">'+index+'</a><br>';
        });

        $('span.shipping_number_show').html(lt_numbers);

        setInactive();
        $("#shipping_table").append("<tr><td></td><td></td><td></td><td></td><td></td>"
            +"<td colspan=\"2\"><a class=\"cancelSkybill\" href=\"\">Annuler cet envoi</a></td></tr>");

    }

    $("#chronoSubmitButton").on('click', function(e) {
        if(lt_history.length > 1) {
            e.preventDefault();
            for (var i = 0; i < lt_history.length; i++) {
                window.open(path+"/skybills/"+lt_history[i]+".pdf");
            }
            return false;
        }
        else if(lt) {
            e.preventDefault();
            document.location.href=path+"/skybills/"+lt+".pdf";
            return false;
        }
        $("#chrono_form").submit();
        $(this).prop('disabled', true);
        //window.location.reload();
    });

    $("#chrono_form").on('submit', function(e) {

        setTimeout(function (){
            window.location.reload();
        }, 3000);

    });


    $(".cancelSkybill").on('click', function(e) {
        e.preventDefault();
        if(confirm("Êtes-vous sûr de vouloir annuler cet envoi ? La lettre de transport associée sera inutilisable.")) {
            $.get(path+"/async/cancelSkybill.php", { skybill: lt, shared_secret: chronopost_secret, id_order: $("input[name=id_order]").val()}).done( function( data ) {
                alert('Lettre de transport bien annulée.');
                location.reload();
            });
        }
    });
});

function setInactive() {
    $("#chronoSubmitButton").val("Ré-imprimer l'étiquette Chronopost");
}
