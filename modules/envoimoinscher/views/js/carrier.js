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

/**
 * Handle all google map actions
 */
var EmcMap = {
    id : "emcMap",
    googleMap : null,
    googleBounds : null,
    markers : [],
    weekdays : {
        1: 'monday',
        2: 'tuesday',
        3: 'wednesday',
        4: 'thursday',
        5: 'friday',
        6: 'saturday',
        7: 'sunday',
    },

    /**
     * Inject the google map into the actual page
     */
    injectPopup : function ()
    {
        var mapHtml = '<div id="'+this.id+'"><div id="emcMapInner">';
        mapHtml += '<div class="emcClose" title="'+Emc.messages.close_map_translation+'"></div>';
        mapHtml += '<div id="mapHeader"><div id="mapHeaderContainer"><div id="emcGeolocate" title="'+Emc.messages.geolocate_map_translation+'"></div>';
        mapHtml += '<input type="text" name="emcMapPostcode" class="emcTextInput" value="'+(Emc.zip?Emc.zip:"")+'" placeholder="'+Emc.messages.postcode_map_translation+'" />';
        mapHtml += '<input type="text" name="emcMapCity" class="emcTextInput" value="'+(Emc.city?Emc.city:"")+'" placeholder="'+Emc.messages.city_map_translation+'" />';
        mapHtml += '<button id="emcSearch"><span class="desktopTitle">'+Emc.messages.search_map_translation+'</span>';
        mapHtml += '<span class="mobileTitle">'+Emc.messages.search_mobile_map_translation+'</span></button>';
        mapHtml += '<div id="emcLoadingPoints" style="display:none"><img src="'+Emc.host+'modules/envoimoinscher/views/img/loading.gif" alt="'+Emc.messages.pp_loading+'" title="'+Emc.messages.pp_loading+'" /></div>';
        mapHtml += '</div></div>';
        mapHtml += '<div id="mapContainer"><div id="mapCanvas"></div></div>';
        mapHtml += '<div id="prContainer"><table></table></div>';
        mapHtml += '</div></div>';
        $('body').append(mapHtml);
    },

    /**
     * Initialize the map for a carrier
     */
    initMap : function(carrier)
    {
        var mapOptions = {
            zoom: 8,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        if(!EmcMap.googleMap && $('#mapCanvas').length > 0) {
            EmcMap.googleMap = new google.maps.Map(document.getElementById("mapCanvas"), mapOptions);
        }

        EmcMap.removePoints();

        if (typeof carrier !== "undefined") {
            EmcMap.loadPoints(carrier);
        }

        EmcMap.refresh();
    },

    /**
     *  Load all carrier's points on the map
     */
    loadPoints : function(carrier)
    {
        var points = Emc.points[carrier];

        // delete previous points
        EmcMap.removePoints();

        // add new parkers
        for(var p in points) {
            var point = points[p];
            EmcMap.addParcelPoint(point, carrier);
        }
    },

    /**
     * Add a single parcel point to the map
     */
    addParcelPoint : function(point, carrier)
    {
        var carrier_infos = Emc.getCarrierInfos(carrier);
        title = point.name;
        id = point.code;
        address = point.address;
        schedule = EmcMap.setSchedule(point.schedule).join('<br />');
        index = EmcMap.markers.length;
        operator = point.code.substr(0,4);

        // add parcel point on map
        infos = "<div class='emcMakerPopup'><b>" + point.name + "</b><br />";
        infos += '<a class="parcelButton emcPointer emcPoint" data-carrier="'+carrier_infos.carrier+'" data-address="'+carrier_infos.address+'" data-point="'+point.code+'">' + Emc.messages.choose[operator] + '</a> <br />';
        infos += address + '<br />' + "<b>" + Emc.messages.opening_hours + "</b><br/>";
        infos += "<div class='emcSchedule'>" + schedule + "</div>";

        if (typeof infos == "undefined") {
            infos = infoParcel[index];
        }
        var emcMarker = {
            url: Emc.host + "modules/envoimoinscher/views/img/marker-number-"+(parseInt(index)+1)+".png",
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(13, 37),
            scaledSize: new google.maps.Size(26, 37)
        };
        var marker = new google.maps.Marker({
            map: EmcMap.googleMap,
            position: new google.maps.LatLng(parseFloat(point.latitude),parseFloat(point.longitude)),
            title: point.name,
            icon: emcMarker
        });
        EmcMap.markers.push(marker);

        var infowindow = new google.maps.InfoWindow({
            'content'  : infos
        });
        google.maps.event.addListener(marker, "click", function () {
            if(typeof openedWindow != 'undefined' && openedWindow !== null) {
                openedWindow.close();
            }
            openedWindow = infowindow;
            infowindow.open(EmcMap.googleMap, this);
        });
        $(".showInfo"+id).click(function(){
            if(typeof openedWindow != 'undefined' && openedWindow !== null) {
                openedWindow.close();
            }
            openedWindow = infowindow;
            infowindow.open(EmcMap.googleMap, marker);
        });

        //extend the bounds to include each marker's position
        EmcMap.googleBounds.extend(marker.position);

        // add parcel point on right list


        // add parcel point in popup list
        var ope = point.code.substring(0, 4);
        choose = "";
        if (Emc.messages.choose[ope]) {
            choose = Emc.messages.choose[ope];
        } else {
            choose = Emc.messages.choose["default"];
        }
        html = '';
        html += '<tr>';
        html += '<td><img src="' + Emc.host + '/modules/envoimoinscher/views/img/marker-number-'+(parseInt(index)+1)+'.png" class="emcMarker" />';
        html += '<div class="emcPointTitle"><a class="showInfo' + point.code + ' emcPointer">' + point.name + '</a></div><br/>';
        html += point.address + '<br/>';
        html += point.zipcode + ' ' + point.city + '<br/>';
        html += '<a class="parcelButton emcPointer emcPoint"  data-carrier="'+carrier_infos.carrier+'" data-address="'+carrier_infos.address+'" data-point="'+point.code+'" style="width:190px;"><b>'+choose+'</b></a>';
        html += '</td>';
        html += '</tr>';
        $('#emcMap #prContainer table').append(html);

        /*if (typeof $(parcelPointId) != "undefined") {
            $(parcelPointId).prop("checked", true);
        }*/
    },

    /**
     * Remove all parcel points from the map
     */
    removePoints : function()
    {
        EmcMap.googleBounds = new google.maps.LatLngBounds();
        for (var i = 0; i < EmcMap.markers.length; i++) {
            EmcMap.markers[i].setMap(null);
        }
        EmcMap.markers = [];
        $('#emcMap #prContainer table *').remove();
    },

    /**
     * Center the map to show all parcel points
     */
    center : function()
    {
        EmcMap.googleMap.fitBounds(EmcMap.googleBounds);
    },

    /**
     * Refresh te map's content
     */
    refresh : function()
    {
        google.maps.event.trigger(EmcMap.googleMap, 'resize');
    },

    /**
     * Show the map
     */
    show : function()
    {
        var map = $('#'+this.id);
        // set offset on the middle of the page (or top of the page for small screens)
        var offset = $(window).scrollTop() + ($(window).height() - $('#emcMap').height())/2;
        if (offset < $(window).scrollTop()) {
            offset = $(window).scrollTop();
        }
        map.css({'top':offset + 'px', 'display': 'block'});

        EmcMap.initMap(Emc.getCarrierInfos().carrier);

        EmcMap.refresh();
        EmcMap.center();
    },

    /**
     * Hide the map
     */
    hide : function()
    {
        $('#'+this.id).css('display', 'none');
    },

    /**
     * Change he map's loading status, if set to true, all actions on the map are disabled
     */
    setLoading : function(loading)
    {
        if (loading) {
            $('#mapHeader #emcSearch').hide();
            $('#emcLoadingPoints').show();
            $('#mapHeader .emcTextInput').prop('disabled', true);
            $('#emcGeolocate').data('inactive', 1);
        } else {
            $('#emcLoadingPoints').hide();
            $('#mapHeader #emcSearch').show();
            $('#mapHeader .emcTextInput').prop('disabled', false);
            $('#emcGeolocate').data('inactive', 0);
        }
    },

    /**
     * Format days value for a parcel point schedule
     */
    setDay : function(schedule)
    {
        var dispo = [];
        if (schedule.open_am !== '' && schedule.close_am !== '') {
            dispo.push({from: EmcMap.formatHours(schedule.open_am), to: EmcMap.formatHours(schedule.close_am)});
        }
        if (schedule.open_pm !== '' && schedule.close_pm !== '') {
            dispo.push({from: EmcMap.formatHours(schedule.open_pm), to: EmcMap.formatHours(schedule.close_pm)});
        }
        if (dispo.length > 0) {
            return {day: EmcMap.weekdays[schedule.weekday], hours: dispo};
        }
        return {day: [], hours: []};
    },

    /**
     * Format hours for a parcel point
     */
    formatHours : function(time)
    {
        var explode = time.split(':');
        if (explode.length == 3) {
            time = explode[0]+':'+explode[1];
        }
        return time;
    },

    /**
     * Format schedule for a parcel point
     */
    setSchedule : function(schedule)
    {
        var days = [];
        $.each(schedule, function (index, sched) {
            var day_arr = EmcMap.setDay(sched);
            if (day_arr.hours.length > 0) {
                var from_to = [];
                $.each(day_arr.hours, function (index, hour) {
                    from_to.push(hour.from+'-'+hour.to);
                });
                days.push(""+Emc.messages[day_arr.day]+' : '+from_to.join(', '));
            }
        });
        return days;
    }

};

/**
 * Handle all front actions
 */
var Emc = {
    points : [],
    country : null,
    city : null,
    zip : null,
    messages : [],
    host : null,
    remove : [],

    debug : function()
    {
        console.log("Country : " + this.country);
        console.log("City : " + this.city);
        console.log("Zip : " + this.zip);
        console.log("Host : " + this.host);
        console.log("Points : ");
        console.log(this.points);
        console.log("Localization : ");
        console.log(this.messages);
        console.log("Disabled carrier's id : ");
        console.log(this.remove);
        console.log("Selected parcel point : " + Emc.getSelectedParcelPointValue());
    },

    /**
     * Return the selected parcel point
     */
    getSelectedParcelPointValue : function()
    {
        return $("input[name='emc-chosen-parcel-point']").val();
    },

    /**
     * Return all carrier's forms
     */
    getCarrierForm : function(carrier)
    {
        // for 1.7
        var form = $("#js-delivery");

        // for 1.6, 1.5
        if (form.length === 0) {
            form = $("#carrier_area #form");
        }

        // for 1.6, 1.5 one page checkout
        if (form.length === 0) {
            form = $("body");
        }

        return form;
    },

    /**
     * Return the parcel point data from it's value
     */
    getParcelPointByValue : function(carrier, value)
    {
        for (var i in Emc.points[carrier]) {
            if (Emc.points[carrier][i].code == value) {
                return Emc.points[carrier][i];
            }
        }
        return null;
    },

    /**
     * Display all carrier's parcel point to the front (no carrier = selected carrier)
     */
    displayParcelPointList : function(carrier)
    {
        infos = Emc.getCarrierInfos(carrier);

        // remove actual containers content
        Emc.getAllCarrierExtraContentElements().html("");

        // get parcel point value if it exists
        if (Emc.points[infos.carrier]) {

            var container = Emc.getCarrierExtraContentElement(infos.carrier);
            var selectedParcelPoint = Emc.getSelectedParcelPointValue();

            // add list of points html (below carrier selection)
            var html = '';
            $.each(Emc.points[infos.carrier], function (key, point) {
                html += '<div class="emcPoint emcPointer" data-carrier="'+infos.carrier+'" data-point="' + point.code + '" data-name="' + point.name + '"'+
                        'data-address="' + infos.address + '" id="'+infos.carrier+'_'+point.code+'_'+infos.address+'"><b>'+point.name+'</b> ';
                html += '<small>'+point.address+', '+point.zipcode+' '+point.city+'</small></div>';
                if (selectedParcelPoint == point.code) {
                    html += '<script type="text/javascript">$(document).ready(function () {Emc.selectParcelPoint(\''+point.code+'\', \''+infos.carrier+'\', false);});</script>';
                }
            });

            // add front parcel points lists
            // container.append(
                // '<p class="emcDesktop">' + Emc.messages.select_pickup_point1 +
                // '<a id="openMap" class="emcLink emc-open-gmap"  data-address="'+infos.address+'" data-carrier="'+infos.carrier+'">' +
                // Emc.messages.select_pickup_point2 + '</a>.</p>' +
                // '<p class="emcMobile introMobile">' + Emc.messages.select_pickup_point3 +
                // '<a href="#" class="emcLink emc-open-gmap">' +
                // Emc.messages.select_pickup_point2 + '</a>.</p>' + '<div class="emcPt5 emcListPointsContainer">' + html + '</div>'
            // );
			/*container.append(
			   
                '<p class="emcMobile introMobile">' + 
                '<a id="openMap" class="emcLink emc-open-gmap"  data-address="'+infos.address+'" data-carrier="'+infos.carrier+'">' +
                Emc.messages.select_pickup_point2 + '</a></p>'
				
				
            );*/
			// $('.emcMobile').clone().appendTo('.carrier_list_col2:eq(0)');
			$('.carrier_list_col2:eq(0)').html( '<strong>Mondial Relay</strong><br><p class="emcMobile introMobile">' + 
                '<a id="openMap" class="emcLink emc-open-gmap"  data-address="'+infos.address+'" data-carrier="'+infos.carrier+'">' +
                Emc.messages.select_pickup_point2 + '</a></p>');
			// $('.delivery_option_radio:eq(0)').closest('.carrier_list_col2').append(
			 
			 
        }
		//var copy = $('.emcMobile').html();
		
		
    },

    /**
     * Refresh datas for the new selected carrier (no carrier = selected carrier)
     * Display parcel points in front and on the map
     */
    refreshCarrierInfo : function(carrier)
    {
      Emc.displayParcelPointList(carrier);
      EmcMap.initMap(carrier);
    },

    /**
     * Select a parcel point
     */
    selectParcelPoint : function(point, carrier, save)
    {
        carrier_infos = Emc.getCarrierInfos(carrier);
        save = (typeof save == "undefined") ? true : save;

        if (save) {
            $.ajax({
                url: 'index.php?fc=module&module=envoimoinscher&controller=ajax&option=set_point&point=' +
                    point,
                type: 'GET'
            });
        }

        $('input[name="emc-chosen-parcel-point"]').val(point);
        Emc.getCarrierBlock(carrier_infos.carrier).find(".emcPoint").removeClass('selected')
        .filter("[data-point='"+point+"']").addClass("selected");

        // show parcel point for mobile
        name = Emc.getParcelPointByValue(carrier, point).name;
        var html = Emc.messages.selected_point + '<br/><span class="emcPointTitle">' + name + '</span><br/>';
        html += '<a class="emcLink emc-open-gmap" data-carrier="'+ carrier_infos.carrier +'" data-address="'+ carrier_infos.address +'">' +
                Emc.messages.change_point + '</a>';
        $('.introMobile').html(html);
    },

    /**
     * Return the carrier input associated with the carrier id
     * If no id is given, return the selected carrier input
     */
    getCarrierInput : function(id)
    {
        var inputSelector = (typeof id == "undefined") ? "input[type='radio']:checked" : "input[value^='" + id + ",']";

        // for 1.7
        var input = $(".delivery-option " + inputSelector);

        // for 1.6
        if (input.length === 0) {
            input = $(".delivery_option_radio " + inputSelector);
        }

        // for 1.5
        if (input.length === 0) {
            input = $(".delivery_option " + inputSelector);
        }
        return input;
    },

    /**
     * Return all carrier inputs
     */
    getAllCarrierBlocks : function()
    {
        return $(".delivery_option, .delivery-option");
    },

    /**
     * Rebuild prestashop classes style for carriers
     */
    rebuildCarrierClasses : function()
    {
        var blocks = Emc.getAllCarrierBlocks();

        // for 1.5 and 1.6
        if (blocks.hasClass("item") || blocks.hasClass("alternate_item")) {
            blocks.removeClass("item").removeClass("alternate_item");
            blocks.filter(":even").addClass("item");
            blocks.filter(":odd").addClass("alternate_item");
        }
    },

    /**
     * Return all carrier inputs
     */
    getAllCarrierInputsSelector : function()
    {
        // for 1.7
        var inputs = ".delivery-option input[type='radio']";

        // for 1.6
        inputs += ", .delivery_option_radio input[type='radio']";

        // for 1.5
        inputs += ", .delivery_option input[type='radio']";

        return inputs;
    },

    /**
     * Return the carrier block
     * If no id is given, return the selected carrier input
     * May return an empty result if there is no valid block
     */
    getCarrierBlock : function(id)
    {
        var inputSelector = (typeof id == "undefined") ? "input[type='radio']:checked" : "input[value^='" + id + ",']";

        // for 1.6, 1.5
        block = $(".delivery_option").has(inputSelector);

        // for 1.7
        if (block.length === 0) {
            block = $(".delivery-option").has(inputSelector);
        }

        return block;
    },

    /**
     * Return the carrier extra content container parent
     * If no id is given, return the selected carrier input
     * May return an empty result if there is no valid container parent
     */
    getCarrierExtraContentParent : function(id)
    {
        var inputSelector = (typeof id == "undefined") ? "input[type='radio']:checked" : "input[value^='" + id + ",']";

        // for 1.6
        var parent = $(".delivery_option_radio").has(inputSelector);

        // for 1.5
        if (parent.length === 0) {
            parent = $(".delivery_option").has(inputSelector);
        }

        // for 1.7
        if (parent.length === 0) {
            parent = $(".delivery-option").has(inputSelector).find(".carrier-extra-content");
        }

        // fr 1.7.1.0
        if (parent.length === 0) {
            parent = $(".delivery-option").has(inputSelector);
        }

        return parent;
    },

    /**
     * Return the carrier extra content element associated with the carrier id
     * If no id is given, return the selected carrier extra content element
     */
    getCarrierExtraContentElement : function(id)
    {
        var inputSelector = (typeof id == "undefined") ? "input[type='radio']:checked" : "input[value^='" + id + ",']";

        // for 1.7
        var content = $(".delivery-option")
        .has(inputSelector)
        .find(".emcListPoints");

        // for 1.6
        if (content.length === 0) {
            content = $(".delivery_option_radio")
            .has(inputSelector)
            .find(".emcListPoints");
        }

        // for 1.5
        if (content.length === 0) {
            content = $(".delivery_option")
            .has(inputSelector)
            .find(".emcListPoints");
        }
        return content;
    },

    /**
     * Return all carrier extra content elements
     */
    getAllCarrierExtraContentElements : function()
    {
        // for 1.7
        var contents = $(".delivery-option")
        .find(".emcListPoints");

        // for 1.6
        if (contents.length === 0) {
            contents = $(".delivery_option_radio")
            .find(".emcListPoints");
        }

        // for 1.5
        if (contents.length === 0) {
            contents = $(".delivery_option")
            .find(".emcListPoints");
        }
        return contents;
    },

    /**
     * Return the carrier informations associated with the carrier id
     * If no id is given, return the selected carrier informations
     */
    getCarrierInfos : function(carrier)
    {
        var result = {};
        result.input = Emc.getCarrierInput(carrier);
        result.carrier = result.input.length?result.input.val().split(",")[0]:null;
        result.address = result.input.length?result.input.attr("id").split("_")[2]:null;
        result.extra_content = Emc.getCarrierExtraContentElement(carrier);

        return result;
    },

    /**
     * Return the carrier delivery message element associated with the carrier id
     * If no id is given, return the selected carrier delivery message element
     */
    getDeliveryMessageContainerElement : function(carrier)
    {
        var block = Emc.getCarrierBlock(carrier);

        // for 1.5, 1.6
        delivery = block.find(".delivery_option_logo").next();

        // for 1.7
        if (delivery.length === 0)
        {
            delivery = block.find(".carrier-delay").parent();
        }

        return delivery;
    },

    /**
     * Remove parcel point selection informations
     */
    resetParcelPointSelection : function()
    {
        $('input[name="emc-chosen-parcel-point"]').val("");
        $('.emcListPointsContainer .emcPoint').removeClass('selected');
        $('.introMobile').html("");
    },

    /**
     * Return true if a parcel point has been chosen for the curent carrier
     */
    isParcelPointSelected : function()
    {
        return Emc.getSelectedParcelPointValue() !== "";
    },

    /**
     * Update parcel point datas for the carrier for the new address informations
     * Do not change the default city, zip and country
     */
    updateParcelPoints : function(carrier, city, zip, country)
    {
        carrier_infos = Emc.getCarrierInfos(carrier);
        $.ajax({
            url: 'index.php?fc=module&module=envoimoinscher&controller=ajax&option=get_points',
            type: 'POST',
            data: {'idCarrier': carrier_infos.carrier, 'city':city, 'postcode':zip, 'country': country},
            dataType: 'json',
            success: function(res) {
                if (!res) {
                    alert(Emc.messages.pp_problem);
                } else {
                    Emc.points[carrier_infos.carrier] = res;
                    Emc.displayParcelPointList(carrier_infos.carrier, carrier_infos.address);
                    EmcMap.initMap(carrier);
                    EmcMap.setLoading(false);
                    EmcMap.center();
                }
            },
            error: function() {
                alert(Emc.messages.pp_problem);
                EmcMap.setLoading(false);
            }
        });
    },

    /**
     * Update parcel point datas for the carrier fr the given position
     * Do not change the default city, zip and country
     */
    updateParcelPointsFromPosition : function(position)
    {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        var latlng = new google.maps.LatLng(lat, lng);
        var city;
        var postcode;
        var country;
        geocoder = new google.maps.Geocoder();
        geocoder.geocode({'latLng': latlng}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[1]) {
                    //find postcode and city
                    for (var i=0; i < results[0].address_components.length; i++) {
                        for (var b=0; b < results[0].address_components[i].types.length; b++) {
                            if (results[0].address_components[i].types[b] == "locality") {
                                city = results[0].address_components[i].long_name;
                            }
                            if (results[0].address_components[i].types[b] == "postal_code") {
                                postcode = results[0].address_components[i].long_name;
                            }
                            if (results[0].address_components[i].types[b] == "country") {
                                country = results[0].address_components[i].short_name;
                            }
                        }
                    }
                    // throw error if not same country as address
                    if (typeof (country) != "undefined" && country != Emc.country) {
                        alert(Emc.messages.not_same_country);
                    } else if (typeof (city) != "undefined" && typeof (postcode) != "undefined") {
                        // address found > change map search fields
                        $('input[name="emcMapPostcode"]').val(postcode);
                        $('input[name="emcMapCity"]').val(city);

                        // display loader instead of button title
                        EmcMap.setLoading(true);

                        // update displayed points
                        carrier_infos = Emc.getCarrierInfos();
                        Emc.updateParcelPoints(carrier_infos.carrier, city, postcode, country);
                    }
                } else {
                    alert(Emc.messages.geoloc_problem);
                }
            } else {
                alert(Emc.messages.geoloc_problem);
            }
        });
    },

    /**
     * Validate the carrier form
     */
    validateCarrierForm : function(popup)
    {
        var carrier_infos = Emc.getCarrierInfos();
        /* check if the operator checked needs a relay point */
        var parcel_point_needed = typeof Emc.points[carrier_infos.carrier] != "undefined";
        if (parcel_point_needed && !Emc.isParcelPointSelected()) {
            /* send an alert message if relay unchecked */
            if (popup === true) {
                alert(Emc.messages.before_continue_select_pickup_point);
            }
            return false;
        }
        return true;
    },

    /**
     * Start a geolocalization
     */
    geolocate : function()
    {
        EmcMap.setLoading(true);

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(Emc.updateParcelPointsFromPosition, Emc.geolocateFromServer);
        } else {
            Emc.geolocateFail();
        }
    },

    /**
     * Handle a geolocalization fail
     */
    geolocateFail : function()
    {
        alert(Emc.messages.geoloc_problem);
    },

    /**
     * Start a geolocalization from the server side
     */
    geolocateFromServer : function()
    {
        $.ajax({
            url: 'index.php?fc=module&module=envoimoinscher&controller=ajax&option=ip_geoloc',
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res !== false) {
                    Emc.updateParcelPointsFromPosition(res);
                } else {
                    Emc.geolocateFail();
                }
            }
        });
    },

    /**
     * For each emc carrier, add the necessary content (parcel points + css classes)
     */
    injectCarrierExtraContentContainers : function()
    {
        // add parcel points when relevant
        for(var carrier in Emc.points) {
            var infos = Emc.getCarrierInfos(carrier);
            var parent = Emc.getCarrierExtraContentParent(carrier);

            if (infos.extra_content.length === 0) {
                var html = '<div id="points' + infos.carrier + infos.address + '" class="emcTextAlignLeft emcP5 emcListPoints" data-address="'+infos.address+'" data-carrier="'+infos.carrier+'">';
                parent.append(html);
            }
        }

        // add css classes for all emc carriers + style if needed
        for(carrier in Emc_dateDelivery) {
            var block = Emc.getCarrierBlock(carrier);
            block.addClass("emcCarrier");

            // ps 1.7
            if (block.find('.delivery_option_logo').length === 0) {
                block.find('.col-sm-5 .col-xs-3 img').css("max-width", "40px");
            }
        }
    },

    disableCarrier : function(carrier)
    {
        var block = Emc.getCarrierBlock(Emc.remove[carrier]);
        if (block.length && !block.hasClass("emcHidden")) {
            block.addClass("emcHidden");
            block.find("input").attr("disabled", true);
            block.prepend("<p class='emcWarning'>"+Emc.messages.carrier_unavailable+"</p>");
            block.parent().append(block);
        }
    },

    /**
     * Initialize the carrier page content
     */
    init : function()
    {
        // Emc_points must be defined
        if (typeof Emc_points == "undefined") {
            return;
        }

        // add default values
        Emc.points = Emc_points;
        if (typeof Emc_country !== "undefined") {
            Emc.country = Emc_country;
        }
        if (typeof Emc_city !== "undefined") {
            Emc.city = Emc_city;
        }
        if (typeof Emc_country !== "undefined") {
            Emc.zip = Emc_zip;
        }
        if (typeof Emc_messages !== "undefined") {
            Emc.messages = Emc_messages;
        }
        if (typeof Emc_host !== "undefined") {
            Emc.host = Emc_host;
        }
        if (typeof Emc_remove !== "undefined") {
            Emc.remove = Emc_remove;
        }


        // inject google map in page
        EmcMap.injectPopup();

        // remove carriers which should not be here
        for (var id in Emc.remove) {
            Emc.disableCarrier(id);
        }
        Emc.rebuildCarrierClasses();

        // for each carrier with parcel points, add the default container if it do not exists yet
        Emc.injectCarrierExtraContentContainers();

        // inject selected parcel point input in form
        if ($("input[name='emc-chosen-parcel-point']").length === 0) {
            Emc.getCarrierForm().append("<input type=\"hidden\" value=\""+Emc_defaultPoint+"\" name=\"emc-chosen-parcel-point\"/>");
        }

        // inject delivery dates
        if (typeof Emc.messages.delivery_message != "undefined" && typeof Emc_dateDelivery != "undefined") {
            for (var carrier in Emc_dateDelivery) {
                deliveryBlock = Emc.getDeliveryMessageContainerElement(carrier);
                if (deliveryBlock.find(".emc_carrier_delivery_message").length === 0) {
                    var message = Emc.messages.delivery_message.replace('{DATE}', Emc_dateDelivery[carrier]);
                    message = '<div><span class="emc_carrier_delivery_message">'+message+'</span></div>';
                    deliveryBlock.append(message);
                }
            }
        }

        // display default parcel points
        var carrier_infos = Emc.getCarrierInfos();
        var selected_point = Emc.getSelectedParcelPointValue();
        Emc.displayParcelPointList(carrier_infos.carrier, carrier_infos.address);
        EmcMap.initMap(carrier_infos.carrier);
        if (Emc.getParcelPointByValue(carrier_infos.carrier, selected_point)) {
            Emc.selectParcelPoint(selected_point, carrier_infos.carrier, false);
        }

    }
};

/**
 * initialize the carrier's selection's page content and all events handles
 */
$(document).ready(function() {
    // initialize page's content
    Emc.init();

    // do not allow form validation if the parcel point has not been selected
    $(document).delegate("#HOOK_PAYMENT a, [name='processCarrier'], #js-delivery button.continue", "click", function(e) {
        if(Emc.validateCarrierForm(true)) {
            return;
        }
        e.defaultPrevented = true;
        return false;
    });
    // do not allow the payment page to submit if the parcel point has not been selected
    $("#payment-confirmation button").click(function(){
        return Emc.validateCarrierForm(true);
    });

    // do not allow payment modules to validate it's form if the parcel point has not be selected
    $(document).delegate('#paypal_payment_form', "submit", function(e) {
        if(Emc.validateCarrierForm(false)) {
            return;
        }
        e.defaultPrevented = true;
        return false;
    });
    $(document).delegate('.payment_module input', "click", function(e) {
        if(Emc.validateCarrierForm(true)) {
            return;
        }
        e.defaultPrevented = true;
        return false;
    });

    // handle carrier selection change
    $(document).delegate(Emc.getAllCarrierInputsSelector(), "change", function(){

        selected = Emc.getCarrierInfos();

        // if the content has been parsed, reload the content
        Emc.injectCarrierExtraContentContainers();

        // remove other carrier's parcel points
        Emc.getAllCarrierExtraContentElements().html("");

        // display the new selected carrier content
        Emc.resetParcelPointSelection();
        Emc.displayParcelPointList(selected.carrier);
        EmcMap.initMap(selected.carrier);
    });

    // handle geolocalization
    $(document).delegate('#emcGeolocate', 'click', function() {
        Emc.geolocate();
        return false;
    });

    // handle map close
    $(document).delegate('.emcClose', 'click', function() {
        EmcMap.hide();
        return false;
    });

    // handle map open
    $(document).delegate('.emc-open-gmap', 'click' , function() {
        var carrier = $(this).attr("data-carrier");
        EmcMap.show();
        return false;
    });

    // handle click on a parcel point
    $(document).delegate(".emcPoint", "click", function(){
        var carrier = $(this).attr("data-carrier");
        var address = $(this).attr("data-address");
        var point = $(this).attr("data-point");

        Emc.selectParcelPoint(point, carrier);
        EmcMap.hide();
        return false;
    });

    // handle a parcel points search
    $(document).delegate('#emcSearch', 'click', function() {
        var zip = $('input[name="emcMapPostcode"]').val();
        var city = $('input[name="emcMapCity"]').val();
        var carrier_infos = Emc.getCarrierInfos();

        EmcMap.setLoading(true);
        Emc.updateParcelPoints(carrier_infos.carrier, city, zip, Emc.country);
        return false;
    });
});
