{*
  * MODULE PRESTASHOP OFFICIEL CHRONOPOST
  * 
  * LICENSE : All rights reserved - COPY AND REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
  * LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
* SANS ACCORD EXPRES D'OXILEO
  *
  * @author    Oxileo SAS <contact@oxileo.eu>
  * @copyright 2001-2018 Oxileo SAS
  * @license   Proprietary - no redistribution without authorization
  *}

<script type="text/javascript">
    /* VAR INIT */
    // Define ChronoRelais' radio ID
    var cust_address="{$cust_address|escape:'javascript':'UTF-8'}";
    var cust_address_clean="{$cust_address_clean|escape:'javascript':'UTF-8'}";
    var cust_city="{$cust_city|escape:'javascript':'UTF-8'}"; 
    var cust_country="{$cust_country|escape:'javascript':'UTF-8'}"; 
    var cust_codePostal="{$cust_codePostal|escape:'javascript':'UTF-8'}";
    var cust_lastname="{$cust_lastname|escape:'javascript':'UTF-8'}";
    var cust_firstname="{$cust_firstname|escape:'javascript':'UTF-8'}";
    var cartID="{$cartID|escape:'javascript':'UTF-8'}";
    var CHRONORELAIS_ID="{$CHRONORELAIS_ID|escape:'javascript':'UTF-8'}";
    var CHRONORELAIS_ID_INT="{$CHRONORELAIS_ID_INT|escape:'javascript':'UTF-8'}";
    var RELAISEUROPE_ID="{$RELAISEUROPE_ID|escape:'javascript':'UTF-8'}";
    var RELAISEUROPE_ID_INT="{$RELAISEUROPE_ID_INT|escape:'javascript':'UTF-8'}";
    var RELAISDOM_ID="{$RELAISDOM_ID|escape:'javascript':'UTF-8'}";
    var RELAISDOM_ID_INT="{$RELAISDOM_ID_INT|escape:'javascript':'UTF-8'}";
    var path="{$module_uri|escape:'javascript':'UTF-8'}";
    var oldCodePostal=null;
    var errormessage="{l s='No pickup point has been selected !\nPlease select a pickup point to continue.' mod='chronopost'}";
    var map_enabled="{$map_enabled|escape:'javascript':'UTF-8'}";

    var chronodata=new Array();
    var relais_map=null; // our map
    var latlngbounds= new google.maps.LatLngBounds();
    var infowindow=null; // currently displayed infowindow
    var map_markers=new Array();


    {literal}
        $(function() {
        
            // Listener for selection of the ChronoRelais carrier radio button
            $('input.delivery_option_radio, input[name=id_carrier]').click(function(e) {
                toggleRelaisMap(cust_address_clean, cust_codePostal, cust_city, e);
            });


            // move in DOM to prevent compatibility issues with Common Services' modules
            if($("#chronorelais_container").length>0)
            {
                $('#chronorelais_dummy_container').remove();
            } else {
                $('#chronorelais_dummy_container').insertAfter($('#extra_carrier'));
                $('#chronorelais_dummy_container').attr('id', 'chronorelais_container');
            }

            // toggle on load
            toggleRelaisMap(cust_address_clean, cust_codePostal, cust_city);
        
            // Listener for CP change
            $('#changeCustCP').on('click', postcodeChangeEvent);
            $("#relais_codePostal").on('keypress keydown keyup', function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    e.stopPropagation();
                    postcodeChangeEvent();
                    return false;
                }
            });


            // Listener for BT select in InfoWindow
            $('#chronorelais_map').click(function(e) {
                if( $(e.target).is('.btselect') )
                   btSelect.call(e.target,e);
            });

            // Listener for cart navigation to next step
            $('input[name=processCarrier]').click(function() {
                if ($('input[name=id_carrier]:checked').val()==carrierID && !$("input[name=chronorelaisSelect]:checked").val()) {
                    alert(errormessage);
                    $.scrollTo($('#relais_txt_cont'), 800);
                    return false;
                }
            });

            // 
        });
    {/literal}
</script>


<div id="chronorelais_dummy_container" style="{if $opc!=true}display:none;{/if}" class="container-fluid chronopost">
    <h3>{l s='Select a pickup point for delivery' mod='chronopost'}</h3>
    <div class="row">
        <p class="alert col-lg-9">{l s='Select a pickup point here below then confirm by choosing \'Select\'' mod='chronopost'}</p>

        <div class="col-lg-3" id="chrono_postcode_controls">
            <div class="input-group">
                <input type="text" name="relais_codePostal" class="form-control" value="{$cust_codePostal|escape:'htmlall':'UTF-8'}" id="relais_codePostal"/>
                  <span class="input-group-btn">
                    <button class="btn btn-info" id="changeCustCP" type="button">{l s='Change my postcode' mod='chronopost'}</button>
                  </span>
            </div>  
        </div>
    </div>
    <div class="row">
        <div id="chronorelais_map" class="col-xs-12" {if $map_enabled==0}style="display:none"{/if}></div>
    </div>
    <div id="relais_txt_cont">
            <h4>{l s='Closest pickup points' mod='chronopost'}</h4>
            <div id="relais_txt"></div>
    </div>
</div>
