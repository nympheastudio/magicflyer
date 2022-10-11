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
*}<link type="text/css" rel="stylesheet" href="../modules/chronossimo/chronossimo.css" />


<div style="text-align: center;">
    <div id="chronossimo_logo">
        <img src="../modules/chronossimo/logo_chronossimo.png" alt="Logo Chronossimo" title="Logo Chronossimo">
    </div>

    <h3>{l s='Intégration des numéros de suivi'}</h3>
    <br><br>
    <div class="orderDiv" style="width: 600px; margin: auto">
        <fieldset>
            <legend>{l s='Entrez un numéro de commande'}</legend>

            <div class="notice">
                <img src="../modules/chronossimo/img/scanner_icon.png" alt="">
                <br>
                {l s='Compatible avec un lecteur de code barre'}
            </div>
            <input type="text" name="chronossimo_order_id" id="chronossimo_order_id" class="inputLinkOrder" value="" />

        </fieldset>
    </div>

<div class="trackingContainer">
    <div class="trackingDiv" style="width: 400px;">
        <fieldset>
            <legend>{l s='Entrez le numéro de suivi du colis'}</legend>

            <label>{l s='Client'}: </label>
            <div class="margin-form">
                <span class="trackingCustomer"></span>
            </div>
            <label>{l s='Montant'}: </label>
            <div class="margin-form">
                <span class="trackingPrice"></span>
            </div>
            <label>{l s='Date'}: </label>
            <div class="margin-form">
                <span class="trackingDate"></span>
            </div>

            <input type="text" name="chronossimo_order_tracking" id="chronossimo_order_tracking" class="inputLinkTracking" value="" />

        </fieldset>
    </div>
</div>

    <br><br>
    <div class="clear"></div>

    <p><a href="index.php?tab=AdminModules&configure=chronossimo&token={$tokenConfigAdminChronossimo}&tab_module=shipping_logistics&module_name=chronossimo">{l s='Editer la configuration'}</a> | <a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=100">{l s='Historique des expéditions'}</a></p>
</div>


<script type="text/javascript">
    var editingTracking = false;
    var securityKey = "{$securityKey}";
    $( document ).ready(function() {
        function closeTracking() {
            editingTracking = false;
            $("#chronossimo_order_id").val('');
            $("#chronossimo_order_id").focus();
            $(".trackingDiv").fadeOut();
        }

        function getInfosError() {
            alert('Le numéro de commande est invalide');
            closeTracking();
        }
        function setTrackingError() {
            closeTracking();
            alert('Erreur: Numéro de suivi non pris en compte');
        }
        function setTrackingFormatError(trackingNumber) {
            closeTracking();
            alert('Erreur: le format du numéro de suivi ('+trackingNumber+') est incorrect.');
        }


        function openTracking() {
            editingTracking = true;
            $("#chronossimo_order_tracking").val('');
            $(".trackingDiv").fadeIn();
            $("#chronossimo_order_tracking").focus();
        }

        function showTracking(order_id) {
            $.ajax({
                type: "POST",
                url: '../modules/chronossimo/ajax_order.php',
                data: {
                    security_key: securityKey,
                    id_order: order_id
                },
                success: function(data) {
                    if (data && data.customer) {
                        $('.trackingCustomer').html(data.customer);
                        $('.trackingPrice').html(data.price+' €');
                        $('.trackingDate').html(data.date);
                        openTracking();
                    }
                    else {
                        getInfosError();
                    }
                },
                error: function() {
                    getInfosError();
                },
                dataType: 'json'
            });
        }

        function setTracking(order_id, tracking) {
            if (order_id && tracking) {
                if (tracking.match(/^[a-z0-9 ]+$/i)) {
                    $.ajax({
                        type: "POST",
                        url: '../modules/chronossimo/ajax_order.php',
                        data: {
                            security_key: securityKey,
                            id_order: order_id,
                            tracking: tracking
                        },
                        success: function (data) {
                            if (!data.success)
                                setTrackingError();
                        },
                        error: function () {
                            setTrackingError();
                        },
                        dataType: 'json'
                    });
                }
                else
                    setTrackingFormatError(tracking)
            }
            else
                setTrackingError();
        }

        setTimeout(function() {
            $('.notice').slideUp();
        }, 5000);



        $("#chronossimo_order_id").focus();
        var barcode="";
        $(document).keydown(function(e) {

            var code = (e.keyCode ? e.keyCode : e.which);
            if(code==13 || code==9) { // Enter key hit or tab
                if (editingTracking) {
                    setTracking($("#chronossimo_order_id").val(), $("#chronossimo_order_tracking").val());
                    closeTracking();
                }
                else
                    showTracking($("#chronossimo_order_id").val());
            }
            else if (code == 27) // Eschape
                closeTracking();
            else
            {
                barcode=barcode+String.fromCharCode(code);
            }
        });

    });
</script>