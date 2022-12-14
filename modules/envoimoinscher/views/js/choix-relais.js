/**
 * 2007-2018 PrestaShop
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
 * @author    EnvoiMoinsCher <api@boxtal.com>
 * @copyright 2007-2018 PrestaShop SA / 2011-2016 EnvoiMoinsCher
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 */
/* change event associated to next step button to prevent carrier without relay point  */
var mapOptions;

var map;
var markers = new Array();
var openedWindow;
var bounds;

var daysLetter = translations.daysLetter;
var list;

var inputCallBackName = "";
var host = "";

/**
 * Initialise les textes d'affichage des points relais contenus dans la
 * liste passée en parametre.
 */
function initTextDisplays(l, type) {
    inputCallBackName =  $('#ptrel-inputCallBack').val();
    host =  $('#host').val();
    for(var i=0; i<l.length; i++) {
        var point = l[i];
        point['rightcoltext'] = getRightColText(point, i);
        point['markertext'] = getMarkerText(point, i);
    }
}

/**
 * Fonction d'affichage des informations d'un relais dans le div de la page choix.
 * Les parametres sont 
 * - index : l'index du relais dans le tableau des relais
 * - selected : un booleen qui indique si le relais est affiche en temps que relais
 *                          le plus proche ou le relais sélectionné.
 */
function displayRelayInfo(index, selected) {
    var point = list[index];

    // affichage des horaires.
    var horaires ="<div>";
    for(var k in point.schedule){
        if(!daysLetter[k]) continue;
        if(point.schedule[k].open_am != "" && point.schedule[k].open_pm != ""){
            if(point.schedule[k].close_am != point.schedule[k].open_pm)
                horaires += daysLetter[k]+" : &nbsp;<span class='db fr'>"+ formatHours(point.schedule[k].open_am)+"-"+ formatHours(point.schedule[k].close_am)+", "+ formatHours(point.schedule[k].open_pm)+"-"+ formatHours(point.schedule[k].close_pm)+"</span><br />";
            else 
                horaires += daysLetter[k]+" : &nbsp;<span class='db fr'>"+ formatHours(point.schedule[k].open_am)+"-"+ formatHours(point.schedule[k].close_pm)+"</span><br />";
        } else if(point.schedule[k].open_am != ""){
            horaires +=daysLetter[k]+": <span class='db fr'>"+formatHours(point.schedule[k].open_am)+"-"+formatHours(point.schedule[k].close_am)+"</span><br />";
        }
    }
    horaires += "</div>";

    var infos = "<p><strong>"+point.name+"</strong><br />"
                                +point.address+"<br />"
                                +point.zipcode+" "+point.city+"<br />"
                                +translations.openingHours
                                +'<a class="popover-hour pointer" data-placement="top" data-toggle="popover" type="button" data-original-title="'+horaires+'" >'
                                +"<img class='pl5 pb2' src='/img/ico-question.png'>"
                                +"</a><br/>"
                                +(selected ? "": "<a class='choice pointer button' rel='"+point.id+"' onClick='choicePtrel("+index+")'>"+translations.selecting+"</a>")
                                +"</p>";

    $("#show").html(infos);
    // activation tooltip horaires
    $('.popover-hour').popover({ trigger :"hover", html : true });
}

/**
 * Fonction de creation de l'affichage des informations du point relais dans la colonne de droite
 */
function getRightColText(point, index) {
    var nbImg = index+1;
    var parcelPointSelect = "";
    if( inputCallBackName != '' && inputCallBackName != 'undefined' ){
         parcelPointSelect = "<a class='btn btn-info btn-xs' style='width:190px;' onClick='chooseParcelPoint(\""+point.code.split("-")[1]+"\")' ><b>"+translations.choose+"</b></a>";
    }
    var txt = "<tr class='mt20' id='infos-pr-"+nbImg+"' class='point-relais-right-col'>"
                    +"<td><img src='"+host+"modules/envoimoinscher/views/img/marker-number-"+nbImg+".png' class='emcMarker' />"
                    +"<div class='emcPointTitle'><a class='showInfo"+point.code+" pointer'>"+point.name+"</a></div><br />"
                    +point.address+"<br />"+point.zipcode+" "+point.city+"<br />"
                    +translations.code+" <strong>"+point.code.split("-")[1]+"</strong><br/>"
                    + parcelPointSelect
                    +"</td></tr>";
    return txt;
}

/**
 * Fonction de creation du texte d'un marker google maps
 * Prend en parametre index car on en a besoin pour le lien de choix.
 */
function getMarkerText(point, index) {
    var horaires ="";
     var parcelPointSelect = "";
    if( inputCallBackName != '' && inputCallBackName != 'undefined' ){
         parcelPointSelect = "<a class='btn btn-info btn-xs' onClick='chooseParcelPoint(\""+point.code.split("-")[1]+"\")' ><b>"+translations.choose+"</b></a><br/>";
    }
    for(var k in point.schedule){
        if(!daysLetter[k]) continue;
        if(point.schedule[k].open_am != "" && point.schedule[k].open_pm != ""){
            if(point.schedule[k].close_am != point.schedule[k].open_pm)
                horaires += daysLetter[k]+" : &nbsp;<span class='fr' style='font-size:11px;'>"+ formatHours(point.schedule[k].open_am)+"-"+ formatHours(point.schedule[k].close_am)+", "+ formatHours(point.schedule[k].open_pm)+"-"+ formatHours(point.schedule[k].close_pm)+"</span><br />";
            else 
                horaires += daysLetter[k]+" : &nbsp;<span class='fr' style='font-size:11px;'>"+ formatHours(point.schedule[k].open_am)+"-"+ formatHours(point.schedule[k].close_pm)+"</span><br />";
        } else if(point.schedule[k].open_am != ""){
            horaires +=daysLetter[k]+": <span class='fr' style='font-size:11px;'>"+formatHours(point.schedule[k].open_am)+"-"+formatHours(point.schedule[k].close_am)+"</span><br />";
        }
    }
    var txt = "<div style='width:250px'><b>"+point.name+"</b><br/>"
                        +parcelPointSelect
                        +point.address+"<br/>"
                        +point.zipcode+" "+point.city+"<br/>"
                        +"<b>"+translations.openingHours+"</b><br/>"
                        +"<div style='font-size:11px;'>"+horaires+"</div>"
                        +"</div>";
    return txt;
}

/**
 * Fonction qui permet de selectionner un point relais.
 * Les paramètres sont : 
 * - type : exp ou dst
 * - index : index du point dans le tableau.
 */
var choicePtrel = function(index) {
    displayRelayInfo(index, true);
    $("#map").addClass("hidden");
    $("#relais_id").val(list[index].id);
    $("#choice").removeClass("hidden");
}

/**
 * Mise à jour de la liste des relais en ajax.
 */
function updateListPoints(urlBase, cp, ville, pays, poids, ope_code, srv_code) {
    var listByCarrier;
    var list;
    $.ajax({
        type: "GET",
        url: urlBase+"type=json&ville="+ville+"&cp="+cp+"&country="+pays+"&poids="+poids+"&ope="+ope_code+"&srv="+srv_code,
        dataType: "json",
        async: false,
        success: function(data) {
            listByCarrier = eval(data);
            list = listByCarrier[0]["points"];
        }
    });
    return list;
}

/**
 * Permet de reinitialiser ce qui doit l'etre 
 * quand on raffraichit la carte
 */
function resetGlobals() {
    if(markers.length > 0) {
        for(var i=0; i<markers.length; i++) {
            if(markers[i]) markers[i].setVisible(false);
        }
    }
    markers = new Array();
}

/**
 * Affichage d'un marker sur la carte.
 */
function makeMarker(latlng, point, index) {

    var nbImg = index + 1;
    
    var emcMarker = {
        url: host + "modules/envoimoinscher/views/img/marker-number-"+(index+1)+".png",
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(13, 37),
        scaledSize: new google.maps.Size(26, 37)
    }
    
    var marker = new google.maps.Marker({
            map: map,
            position: latlng,
            icon: emcMarker
        });
    markers[index] = marker;
    bounds.extend(marker.getPosition());

    var infowindows = new google.maps.InfoWindow({
            'content'  : point.markertext
        });

    var added = false;
    for(var j = nbImg-1 ; j > 0 ; j--) {
        if($("#infos-pr-"+j).length > 0 && !added) {
            $("#infos-pr-"+j).after(point.rightcoltext);
            added = true;
        }
    }
    if(!added) {
        $("#rightcol-ptrel").prepend(point.rightcoltext);
    }

    google.maps.event.addListener(marker,"click",function() {
        if(typeof openedWindow != 'undefined' && openedWindow != null) {
            openedWindow.close();
        }
        openedWindow = infowindows;
        infowindows.open(map, this);
    });

    //Trigger a click event to marker when the button is clicked.
    $(".showInfo"+point.code).click(function(){
        if(typeof openedWindow != 'undefined' && openedWindow != null) {
            openedWindow.close();
        }
        openedWindow = infowindows;
        infowindows.open(map,marker);
    });

    //chois du point relais
    $("a.choice").click(function(){
        $("#modal-parcel-point").modal("hide");
    })

}

$(document).ready(function (){
    // initialisation des variables Google
    mapOptions = { zoom: 8, 
                 zoomControlOptions : { style : google.maps.ZoomControlStyle.LARGE },
                 mapTypeId: google.maps.MapTypeId.ROADMAP,
                 streetViewControl: false };

    // premier init de la map
    list = updateListPoints($('#urlBase').val(), $('#ptrel-cp').val(), $('#ptrel-ville').val(), $('#ptrel-pays').val(), $('#ptrel-poids').val(), $('#ptrel-ope').val(), $('#ptrel-srv').val());

    initTextDisplays(list);

    if(!map && $('#map-canvas').length > 0) {
        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
    }
    bounds = new google.maps.LatLngBounds();

    $("#rightcol-ptrel").html("");
    resetGlobals();
    
    for(var i=0;i<list.length; i++) {
        var latlng = new google.maps.LatLng(parseFloat(list[i]['latitude']),parseFloat(list[i]['longitude']));
        makeMarker(latlng, list[i], i);
    }
    
    map.fitBounds(bounds);

    /**
     * Soumission du pseudo formulaire de la popup.
     */
    $('#submitNewMap').click(function() {
        list = updateListPoints($('#urlBase').val(), $('#ptrel-cp').val(), $('#ptrel-ville').val(), $('#ptrel-pays').val(), $('#ptrel-poids').val(), $('#ptrel-ope').val(), $('#ptrel-srv').val());
        initTextDisplays(list);
        
        bounds = new google.maps.LatLngBounds();
        $("#rightcol-ptrel").html("");
        resetGlobals();
        
        for(var i=0;i<list.length; i++) {
            var latlng = new google.maps.LatLng(parseFloat(list[i]['latitude']),parseFloat(list[i]['longitude']));
            makeMarker(latlng, list[i], i);
        }
        map.fitBounds(bounds);
        
        return false;
    });
});


/**
 * Choose Parcel Point and close modal
 */
function chooseParcelPoint(parcelPoint) {
    $("input[name='"+inputCallBackName+"']", window.parent.document).val(parcelPoint);
    /* close fancybox in prestashop */
    if(window.parent.document.getElementsByClassName("fancybox-iframe")){
        parent.jQuery.fancybox.close();
    }

    if(window.parent.document.getElementById("my-modal-close-iframe")){
        $("#my-modal-close-iframe", window.parent.document).click();
    }
}

/* from 12:00:00 to 12:00 */
function formatHours(time)
{
    var explode = time.split(':');
    if (explode.length == 3) {
        time = explode[0]+':'+explode[1];
    }  
    return time;
}