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
  
function toggleRDVpane(cust_address, codePostal, city, e)
{
    if($("input.delivery_option_radio:checked").val()==rdv_carrierID+"," || $("input[name=id_carrier]:checked").val()==rdv_carrierIntID) 
    {
        if(typeof e != "undefined") {
            e.stopPropagation();
        }

        var tellMeWhere=$("input.delivery_option_radio:checked").parent().parent().parent().parent().parent();
        if(!tellMeWhere.parent().hasClass('delivery_option')) {
            tellMeWhere=tellMeWhere.parent().parent();
        }

        $('#chronordv_container').insertAfter(tellMeWhere);
        
        $('input[name="chronoRDVSlot"]').change(function() {
            var rank = $('input[name="chronoRDVSlot"]:checked').val();
            var fee = $('input[name="chronoRDVSlot"]:checked').attr('data-fee');
            var deliveryDate = $('input[name="chronoRDVSlot"]:checked').attr('data-delivery-date');
            var deliveryDateEnd = $('input[name="chronoRDVSlot"]:checked').attr('data-delivery-date-end');
            var slotCode = $('input[name="chronoRDVSlot"]:checked').attr('data-slot-code');
            var tariffLevel = $('input[name="chronoRDVSlot"]:checked').attr('data-tariff-level');
            associateCreneau(rank, deliveryDate, deliveryDateEnd, slotCode, tariffLevel, transactionID, fee);
        });

        $('input[name="chronoRDVSlot"]:first').click();
        $('#chronordv_container').show();
        return false;
    }

    // Hide controls
    $('#chronordv_container').hide();

};


function associateCreneau(rank, deliveryDate, deliveryDateEnd, slotCode, tariffLevel, transactionID, fee) {
    $.ajax({
        url: path + '/async/storeCreneau.php?rank=' + rank + '&deliveryDate=' + encodeURIComponent(deliveryDate) + '&deliveryDateEnd=' + encodeURIComponent(deliveryDateEnd) + '&slotCode=' + encodeURIComponent(slotCode) + '&tariffLevel=' + tariffLevel + '&transactionID=' + encodeURIComponent(transactionID) + '&fee=' + fee + '&cartID=' + cartID
    });
}

