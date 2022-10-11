/*
 * 2007-2017 PrestaShop
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2017 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

var pudos = {};
var markers = [];
var infowindow = null;
var infoWindowSiteId = null;
var selectedSite = null;
var centerMarker = null;
var currentPosition = {lat: null, lng: null};

var geocoder, lnp2_map;

Number.prototype.formatMoney = function (c, d, t) {
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};


function initMap(lat, lng) {

    if (!lat)
        lat = 48.8534100;
    if (!lng)
        lng = 2.3488000;

    var mapCanvas = document.getElementById('lnp2_map');

    var mapOptions = {
        center                  : new google.maps.LatLng(lat, lng),
        zoom                    : 16,
        minZoom                 : 4,
        navigationControlOptions: {
            style: google.maps.NavigationControlStyle.SMALL
        },
        mapTypeControl          : false,
        streetViewControl       : false,
        mapTypeId               : google.maps.MapTypeId.ROADMAP
    };

    lnp2_map = new google.maps.Map(mapCanvas, mapOptions);

}

function changePickupMapSearchIcon(new_class) {

    $('.pickup_map_search_icon').removeClass('lens');
    $('.pickup_map_search_icon').removeClass('cross');
    $('.pickup_map_search_icon').removeClass('change');
    $('.pickup_map_search_icon').addClass(new_class);

    if (new_class == 'change')
        $('.pickup_map_search_icon').text(change_str);
    else
        $('.pickup_map_search_icon').text('');

}

function placeLocalMarker(lat, lng) {

    // delete marker if required
    if (centerMarker)
        centerMarker.setMap(null);

    centerMarker = new google.maps.Marker({
        position: {'lat': lat, 'lng': lng},
        map     : lnp2_map,
        icon    : modules_dir + 'lanavettepickup/views/img/local.png'
    });

}

function initSearchBox() {
    var input = document.getElementById('pickup_map_search_input');
    var searchBox = new google.maps.places.SearchBox(input);
    searchBox.addListener('places_changed', function (event) {
        $('#lnp2_map').slideDown();

        var places = searchBox.getPlaces();

        // console.log('PLACES');
        if (places.length == 0)
            return;

        var bounds = new google.maps.LatLngBounds();
        places.forEach(function (place) {

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });

        placeLocalMarker(bounds.getCenter().lat(), bounds.getCenter().lng());
        loadMarkers(bounds.getCenter().lat(), bounds.getCenter().lng());
    });

    //google.maps.event.trigger(searchBox, 'places_changed');      
}

function fillInfowindowContent(id) {

    var pudo = pudos[id];

    if (!pudo)
        return;

    var is_available = isAvailable(pudo);

    var pudo_address1 = '';
    if (pudo.address1 != undefined && pudo.address1 != '') {
        pudo_address1 += '<br />';
        pudo_address1 += '<span class="pudo_address">';
        pudo_address1 += pudo.address1;
        pudo_address1 += '</span>';
    }

    var pudo_address2 = '';
    if (pudo.address2 != undefined && pudo.address2 != '') {
        pudo_address2 += '<br />';
        pudo_address2 += '<span class="pudo_address">';
        pudo_address2 += pudo.address2;
        pudo_address2 += '</span>';
    }

    var pudo_address3 = '';
    if (pudo.address3 != undefined && pudo.address3 != '') {
        pudo_address3 += '<br />';
        pudo_address3 += '<span class="pudo_address">';
        pudo_address3 += pudo.address3;
        pudo_address3 += '</span>';
    }

    var pudo_zipCode = '';
    if (pudo.zipCode != undefined && pudo.zipCode != '') {
        pudo_zipCode += '<br />';
        pudo_zipCode += '<span class="pudo_zipCode">';
        pudo_zipCode += pudo.zipCode + ' ' + pudo.city;
        pudo_zipCode += '</span>';
    }

    // pudo info
    $('#pickup_map_overlay_box_content_pudo_address').html(
        '<span class="pudo_name">' + pudo.name + '</span>'
        + '<span class="pudo_distance">' + pudo.distance + 'km</span>'
        + pudo_address1
        + pudo_address2
        + pudo_address3
        + pudo_zipCode
    );

    // distance
    // $('#pickup_list_item_content_distance_number').html(Math.round(pudo.distance * 0.1) / 100);

    // schedule
    var scheduleStr = '<ul class="pickup__map__schedule--table">';
    scheduleStr += '<b class="pickup__map__schedule--title">' + opening_hours_str + '</b>';

    var currentDay = '';
    var startTime = '';
    var endTime = '';

    $.each(pudo.openingHours, function (key, od) {
        if (!od.dayId)
            return true;

        if (currentDay !== od.dayId) {
            if (currentDay !== '') {
                if (startTime != '' && endTime != '') {
                    scheduleStr += '<span class="pickup__map__schedule--hours">' + startTime + ' - ' + endTime + '</span>';
                }
                scheduleStr += '</li>';
            }
            scheduleStr += '<li><span class="pickup__map__schedule--day">' + day_of_week_str_ar[od.dayId] + ' : </span>';
            startTime = od.startTime;
            endTime = od.endTime;
        }

        if (startTime != '' && endTime != '') {
            if (endTime == od.startTime) {
                endTime = od.endTime;
            } else if (startTime != od.startTime) {
                scheduleStr += '<span class="pickup__map__schedule--hours">' + startTime + ' - ' + endTime + '</span>';
                startTime = od.startTime;
                endTime = od.endTime;
            }
        }

        currentDay = od.dayId;
    });

    if (currentDay !== '') {
        if (startTime != '' && endTime != '') {
            scheduleStr += '<span class="pickup__map__schedule--hours">' + startTime + ' - ' + endTime + '</span>';
        }
        scheduleStr += '</li>';
        scheduleStr += '</ul>';
    }

    if (!isAvailable(pudo)) {
        var vacationStartDate = new Date(frDateToEnDate(pudo.holidays[0].startDate));
        var vacationStartDateStr = vacationStartDate.getDate() + '/' + (vacationStartDate.getMonth() + 1) + '/' + vacationStartDate.getFullYear();

        var vacationEndDate = new Date(frDateToEnDate(pudo.holidays[0].endDate));
        var vacationEndDateStr = vacationEndDate.getDate() + '/' + (vacationEndDate.getMonth() + 1) + '/' + vacationEndDate.getFullYear();

        scheduleStr += '<b class="pickup__map__schedule--closing-time">';
        // scheduleStr += '<b>' + closing_time_title_str + '</b><br/>';
        scheduleStr += closing_time_str.replace('|start|', vacationStartDateStr).replace('|end|', vacationEndDateStr);
        scheduleStr += '</b>';
    }

    $('.pickup_map_overlay_box_content_schedule').html(scheduleStr);
}

// fr date : jj/mm/yyyy, en date: mm/jj/yyyy
function frDateToEnDate(strDate) {
    var date = strDate.split('/');
    if (date.length > 2) {
        return date[1] + '/' + date[0] + '/' + date[2];
    }
    return '';
}

function initInfowindowTabs() {

    $('.pickup_map_overlay_box_select_button').click(function (event) {

        event.preventDefault();

        selectSiteId(infoWindowSiteId);

    });

    $('.pickup_map_overlay_box_close').click(function (e) {
        e.preventDefault();
        if (infowindow) {
            infowindow.set("marker", null);
            infowindow.close();
            $('.pickup_list_item').removeClass('selected');
        }
    });

}

var last_data;

function getRegularMarker(lat, lng, id, color) {

    return new google.maps.Marker({
        position: {lat: lat, lng: lng},
        map     : lnp2_map,
        icon    : modules_dir + 'lanavettepickup/views/img/' + color + '.png',
        id      : id
    });

}

function getPrice(pudo) {
    if (!pudo)
        return null;

    if (corse_price != -1 && pudo.zipCode >= 20000 && pudo.zipCode < 22000) {
        if (lnp_corse_paid)
            return general_price;

        return general_price + corse_price;
    }

    if (lnp_corse_paid)
        return general_price - corse_price;

    return general_price;
}

function updateMarkers(data) {

    // console.log('updateMarkers data : ', data);

    if (!data)
        data = last_data;

    // console.log('markers : ', markers);
    for (var i in markers)
        markers[i].setMap(null);

    markers = [];
    pudos = {};

    $.each(data, function (key, pudo) {
        // console.log("marker loop pudo["+key+'] : ', pudo);
        pudos[pudo.id] = pudo;

        // Remove Seller's 'selected Drop-off point' from the map (still visible in the left column).
        // if ((typeof(drop_off_pudo_id) != 'undefined') && (pudo.id == drop_off_pudo_id)) {
        //     console.log("we drop off this pudo");
        //     return true;
        // }

        if (isAvailable(pudo)) {
            // console.log(pudo.id + ' is available');

            var amount = null;

            if (selectedSite && pudo.id == selectedSite.id) {

                markers[pudo.id] = getRegularMarker(pudo.latitude, pudo.longitude, pudo.id, 'red');
                markers[pudo.id].setIcon(markers[pudo.id].icon.replace('red.png', 'red_checked.png'));
                $('.pickup_list_item').removeClass('checked');
                $('.pickup_list_item[data-id=' + pudo.id + ']').addClass('checked');
                markers[pudo.id].setZIndex(2);

            } else {

                if (map_is_drop_off || !amount) {

                    markers[pudo.id] = getRegularMarker(pudo.latitude, pudo.longitude, pudo.id, 'red');
                    markers[pudo.id].setIcon(markers[pudo.id].icon.replace('red_checked.png', 'red.png'));
                    markers[pudo.id].setZIndex(1);


                } else {

                    markers[pudo.id] = new MarkerWithLabel({
                        position    : {lat: pudo.latitude, lng: pudo.longitude},
                        map         : lnp2_map,
                        icon        : modules_dir + 'lanavettepickup/views/img/' + ((isAvailable(pudo)) ? red : gray) + '.png',
                        id          : pudo.id,
                        labelContent: (amount ? amount.formatMoney(2, ',', ' ') + '€' : ''),
                        labelAnchor : new google.maps.Point(26, 63),
                        labelClass  : "pickup_marker_label",
                        labelStyle  : {opacity: 1}
                    });

                }
            }

        } else {
            // console.log(pudo.id + ' is NOT available');
            markers[pudo.id] = getRegularMarker(pudo.latitude, pudo.longitude, pudo.id, 'gray');
        }

        pudo.distance = pudo.distance / 1000;
        pudo.distance = pudo.distance.toFixed(2);

        google.maps.event.addListener(markers[pudo.id], 'mouseover', function (id) {
            if (selectedSite && pudo.id == selectedSite.id) {
                this.setIcon(modules_dir + 'lanavettepickup/views/img/red_checked.png');
            } else if (!isAvailable(pudo)) {
                this.setIcon(modules_dir + 'lanavettepickup/views/img/gray_selected.png');
            } else {
                this.setIcon(modules_dir + 'lanavettepickup/views/img/red_selected.png');
            }
            $('.pickup_list_item').removeClass('hover');
            $('.pickup_list_item[data-id=' + pudo.id + ']').addClass('hover');
            markers[pudo.id].setZIndex(10);
        });

        google.maps.event.addListener(markers[pudo.id], 'mouseout', function (id) {
            if (selectedSite && pudo.id == selectedSite.id) {
                this.setIcon(modules_dir + 'lanavettepickup/views/img/red_checked.png');
            } else if (!isAvailable(pudo)) {
                this.setIcon(modules_dir + 'lanavettepickup/views/img/gray.png');
            } else {
                this.setIcon(modules_dir + 'lanavettepickup/views/img/red.png');
            }
            $('.pickup_list_item').removeClass('hover');
            markers[pudo.id].setZIndex(1);
            if (selectedSite && pudo.id == selectedSite.id) {
                markers[pudo.id].setZIndex(2);
            }
        });

        markers[pudo.id].addListener('click', function (event) {
            // console.log(pudo.id + ' click function');
            if (infowindow)
                infowindow.close();

            $('.pickup_list_item').removeClass('selected');
            $('.pickup_list_item[data-id=' + pudo.id + ']').addClass('selected');

            fillInfowindowContent(this.id);

            if (isAvailable(pudo)) {
                // console.log(pudo.id + 'click function > is available');
                $('.pickup_map_overlay_box_select_button').show();
            } else {
                // console.log(pudo.id + 'click function > is NOT available');
                $('.pickup_map_overlay_box_select_button').hide();
            }

            infowindow = new InfoBox({
                content         : $('.pickup_map_overlay_box').html(),
                disableAutoPan  : false,
                alignBottom     : false,
                pixelOffset     : new google.maps.Size(-100, -278),
                zIndex          : null,
                closeBoxURL     : "",
                infoBoxClearance: new google.maps.Size(1, 1),
                boxStyle        : {
                    background: "transparent",
                    opacity   : 1.0
                }
            });

            infoWindowSiteId = this.id;
            infowindow.open(lnp2_map, this);

            google.maps.event.addListener(infowindow, 'domready', function () {
                initInfowindowTabs();
            });
        });
    });
}

function updateBounds() {

    var needFit = false;
    var bounds = new google.maps.LatLngBounds();

    if (Object.keys(pudos).length <= 0) {
        lnp2_map.setCenter(currentPosition);
        lnp2_map.setZoom(12);
    } else {
        for (var i in pudos) {
            var pudo = pudos[i];
            needFit = true;
            bounds.extend(new google.maps.LatLng(pudo.latitude, pudo.longitude));
        }
    }

    if (needFit) {
        lnp2_map.fitBounds(bounds);
    }

}

function loadMarkers(lat, lng) {
    currentPosition.lat = lat;
    currentPosition.lng = lng;
    // console.log("loadMarkers");

    var type = (map_is_drop_off ? 'd' : 'a');

    $.getJSON(
        modules_dir + 'lanavettepickup/ajax/loadPudos.php?lat=' + lat + '&lng=' + lng + '&type=' + type + '&token=' + security_token,
        function (data) {
            updateMarkers(data);

            if (!map_is_drop_off)
                updatePudosList(data);

            last_data = data;
            updateBounds();
        }
    );
}

function updatePudosList() {

    // console.log('updatePudosList');

    $('.pickup_list_ul').html('');

    for (var i in pudos) {

        var pudo = pudos[i];
        // console.log(pudo);

        var is_available = isAvailable(pudo);

        // convert distance in km, then round two digits after decimal point
        // pudo.distance = pudo.distance/1000;
        // pudo.distance = pudo.distance.toFixed(2);

        var pudo_address2 = '';
        if (pudo.address2 != undefined && pudo.address2 != '') {
            pudo_address2 += '<br />';
            pudo_address2 += '<span class="pudo_address">';
            pudo_address2 += pudo.address2;
            pudo_address2 += '</span>';
        }

        var pudo_address3 = '';
        if (pudo.address3 != undefined && pudo.address3 != '') {
            pudo_address3 += '<br />';
            pudo_address3 += '<span class="pudo_address">';
            pudo_address3 += pudo.address3;
            pudo_address3 += '</span>';
        }

        var pudo_zipCode = '';
        if (pudo.zipCode != undefined && pudo.zipCode != '') {
            pudo_zipCode += '<br />';
            pudo_zipCode += '<span class="pudo_zipCode">';
            pudo_zipCode += pudo.zipCode + ' ' + pudo.city;
            pudo_zipCode += '</span>';
        }

        // process and store closing period
        var scheduleStr = '';
        if (!is_available) {
            var vacationStartDate = new Date(frDateToEnDate(pudo.holidays[0].startDate));
            var vacationStartDateStr = vacationStartDate.getDate() + '/' + (vacationStartDate.getMonth() + 1) + '/' + vacationStartDate.getFullYear();

            var vacationEndDate = new Date(frDateToEnDate(pudo.holidays[0].endDate));
            var vacationEndDateStr = vacationEndDate.getDate() + '/' + (vacationEndDate.getMonth() + 1) + '/' + vacationEndDate.getFullYear();

            scheduleStr = '<br />';
            scheduleStr += '<span class="pudo_schedule">';
            scheduleStr += closing_time_str.replace('|start|', vacationStartDateStr).replace('|end|', vacationEndDateStr);
            scheduleStr += '</span>';
        }

        $('.pickup_list_ul').append(
            '<li class="pickup_list_item ' + (is_available ? '' : 'unavailable') + (selectedSite && selectedSite.id == pudos[i].id ? 'checked' : '') + '" data-id=' + pudos[i].id + '>'
            + '<p class="pickup_list_item_content">'
            + '<span class="pudo_name">' + pudo.name + '</span>'
            + '<span class="pudo_distance">' + pudo.distance + 'km</span>'
            + '<br />'
            + pudo.address1
            + pudo_address2
            + pudo_address3
            + pudo_zipCode
            + scheduleStr
            + '</p>'
            + '</li>'
        );
    }

    if (Object.keys(pudos).length <= 0) {
        $('.pickup_list_ul').append(
            '<p style="text-align: center; margin: 40px 0; color: #d90613">' + no_pudo_found + '</p>'
        );
    }


    $('.pickup_list_item').hover(function () {
        var id = $(this).attr("data-id");
        markers[id].setIcon(markers[id].icon.replace('red.png', 'red_selected.png'));
        markers[id].setIcon(markers[id].icon.replace('gray.png', 'gray_selected.png'));
        markers[id].setZIndex(10);
    }, function () {
        var id = $(this).attr("data-id");
        markers[id].setIcon(markers[id].icon.replace('red_selected.png', 'red.png'));
        markers[id].setIcon(markers[id].icon.replace('gray_selected.png', 'gray.png'));
        markers[id].setZIndex(1);
        if (selectedSite && id == selectedSite.id) {
            markers[id].setZIndex(4);
        }
    });

    // $('.pickup_list_item_content').click(function () {

    //     var id = $(this).parent().data('id');

    //     if ($('#lnp2_map').css('display') != 'none') {
    //         // console.log('SHOW');
    //         google.maps.event.trigger(markers[id], 'click');
    //     } else {
    //         selectSiteId(id);
    //     }

    // });

    $('.pickup_list_item').click(function () {

        if (!$(this).hasClass('selected')) {
            $('.pickup_list_item').removeClass('selected');
            $(this).addClass('selected');
            //selectSiteId($(this).data('id'));
        }

        var id = $(this).data('id');

        if ($('#lnp2_map').css('display') != 'none') {
            // console.log('SHOW');
            google.maps.event.trigger(markers[id], 'click');
        } else {
            selectSiteId(id);
        }
    });
}


function isAvailable(pudo) {
    if (!pudo)
        return false;

    if (!pudo.available)
        return false;

    if (pudo.holidays.length == 0)
        return true;

    var vacationStartDate = new Date(frDateToEnDate(pudo.holidays[0].startDate));
    var vacationStartDateTimestamp = vacationStartDate.getTime();

    var vacationEndDate = new Date(frDateToEnDate(pudo.holidays[0].endDate));
    var vacationEndDateTimestamp = vacationEndDate.getTime();

    var nowTimestamp = (new Date()).getTime();

    if ((vacationEndDateTimestamp - vacationStartDateTimestamp ) > 25920000) { // more than 3 days off

        if ((vacationStartDateTimestamp - nowTimestamp) < 1814400000) // 21 days
            return false;
        else if ((vacationStartDateTimestamp >= nowTimestamp) && (vacationEndDateTimestamp <= nowTimestamp))
            return false;
        else
            return true;
    }

    return true;
}

function GetLocation(location) {

    // console.log("get location func");

    var lat = location.coords.latitude;
    var lng = location.coords.longitude;

    if (selectedSite && selectedSite.latitude && selectedSite.longitude) {
        lat = selectedSite.latitude;
        lng = selectedSite.longitude;
    }

    google.maps.event.addDomListener(window, "load", initMap(lat, lng));
    initSearchBox();
    placeLocalMarker(parseFloat(lat), parseFloat(lng));
    loadMarkers(lat, lng);

}

function LocationNotFound() {
    GetLocation({
        coords: {
            latitude : 48.862134,
            longitude: 2.345497
        }
    });
}

function updateMapSearchField(pudo) {
    $('#pickup_map_search_input').val(pudo.name.trim() + ' ' + pudo.address1.trim() + ' ' + pudo.zipCode.trim() + ' ' + pudo.city.trim());
}

// for frontend
function pushSelectedPudo(pudo) {

    var data = {
        pudo          : $('input[name=LNP2_PICKUP_SITE]').val(),
        shipping_price: getPrice(pudo),
        token         : security_token
    };

    if (typeof cart_id != 'undefined')
        data.cart_id = cart_id;

    $.post(modules_dir + 'lanavettepickup/ajax/setPudo.php',
        data);
}

function selectSiteId(id) {

    var pudo = pudos[id];
    var d = {};

    for (var i in pudo) {
        if (['countryCode', 'address1', 'zipCode', 'city', 'name', 'id'].indexOf(i) != -1) {
            if (pudo[i] != null) {
                var str = String(pudo[i]);
                d[i] = str.replace(/\"/g, '');
            } else {
                d[i] = pudo[i];
            }
        }
    }

    if (map_is_drop_off) {

        $('input[name=LNP2_DROP_OFF_SITE]').val(JSON.stringify(d).replace(/\"/g, '|'));
        $('input[name=LNP2_DROP_OFF_PUDO_LAT]').val(pudo.latitude);
        $('input[name=LNP2_DROP_OFF_PUDO_LNG]').val(pudo.longitude);

        updateMapSearchField(pudo);
        $('#lnp2_map').slideUp();

        // hide potential message for pudo being unavailable
        $('#pickup_map_search_input').removeClass('remove background_red');
        $('.pickup_map_pudo_unavailable_div').hide();
        changePickupMapSearchIcon('lens');

    } else {

        $('input[name=LNP2_PICKUP_SITE]').val(JSON.stringify(d));

        var price = getPrice(pudo);
        var dom_price_html = $('.la_navette_pickup .delivery_option_price>.delivery_option_price');
        if (dom_price_html.length == 0) {
            dom_price_html = $('.la_navette_pickup .delivery_option_price');
        }
        var price_str = $(dom_price_html).html();
        var array_price = price_str.split(currencySign);

        if (array_price.length > 1) {
            price_str = formatCurrency(price, currencyFormat, currencySign, currencyBlank) + array_price[1];
        }
        dom_price_html.html(price_str);

        pushSelectedPudo(pudo);
    }

    if (infowindow) {
        infowindow.set("marker", null);
        infowindow.close();
    }

    selectedSite = pudo;
    updateMarkers();

    if ($('.pickup_list_item').length) {
        $('.pickup_list_item').removeClass('selected');
        $('.pickup_list_item[data-id=' + id + ']').addClass('selected');
    }

}

function disableEnterKey(e) {
    var key = window.event ? window.event.keyCode : e.which;
    return (key != 13);
}

function isPickupCarrierSelected() {

    var is_selected = false;

    $('input.delivery_option_radio').each(function () {

        if ($(this).val() == pickup_carrier_id + ',')
            if ($(this).is(':checked')) {
                is_selected = true;
                return true;
            }
    });

    return is_selected;
}

function setCarrierPriceAndPickupOptionsDisplay() {
    $('input.delivery_option_radio').each(function () {
        // console.log($(this).val());
        if ($(this).val() == pickup_carrier_id + ',') {
            // console.log(pickup_carrier_id);
            $(this).parents('.delivery_option').addClass('la_navette_pickup');
            // console.log('addClass la_navette_pickup');
            // console.log('lnp2_free = '+lnp2_free);
            if ($(this).is(':checked')) {
                // console.log('pickup carrier selected');
                $('.la_navette_pickup').append($('#pickup_carrier'));
                $('#pickup_carrier').show();
                initMapGlobal();
            } else {
                // console.log('pickup carrier NOT selected');
                $('#pickup_carrier').hide();
            }
        }
    });
}

function openMap() {
    $('#lnp2_map').slideDown(function () {
        google.maps.event.trigger(lnp2_map, 'resize');
        updateBounds();
    });
}

function initMapGlobal() {

    // console.log('initMapGlobal()');

    if (map_is_drop_off && (typeof(pudo_lat) !== 'undefined') && pudo_lat && (typeof(pudo_lng) !== 'undefined') && pudo_lng) {

        // console.log('map_is_drop_off');
        // center on current pudo coordinates

        GetLocation({
            coords: {
                latitude : pudo_lat,
                longitude: pudo_lng
            }
        });

    } else if (!map_is_drop_off) {

//      console.log('NOT map_is_drop_off');
//      console.log('delivery_address = '+delivery_address);

        geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            'address': delivery_address
        }, function (results, status) {

            if (status == google.maps.GeocoderStatus.OK) {

                // console.log(delivery_address + ' > google.maps.GeocoderStatus > ok');

                GetLocation({
                    coords: {
                        latitude : results[0].geometry.location.lat(),
                        longitude: results[0].geometry.location.lng()
                    }
                });

            } else {

                // console.log(delivery_address + ' > google.maps.GeocoderStatus > NOT ok > then retry with zipcode');

                geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'address': delivery_zip
                }, function (results, status) {

                    if (status == google.maps.GeocoderStatus.OK) {

                        // console.log(delivery_zip + ' > google.maps.GeocoderStatus > ok');

                        GetLocation({
                            coords: {
                                latitude : results[0].geometry.location.lat(),
                                longitude: results[0].geometry.location.lng()
                            }
                        });

                    } else {

                        // console.log(delivery_zip + ' > google.maps.GeocoderStatus > NOT ok > then retry with Paris, France');

                        geocoder = new google.maps.Geocoder();
                        geocoder.geocode({
                            'address': 'Paris, France'
                        }, function (results, status) {

                            if (status == google.maps.GeocoderStatus.OK) {

                                // console.log('Paris, France > google.maps.GeocoderStatus > ok');

                                GetLocation({
                                    coords: {
                                        latitude : results[0].geometry.location.lat(),
                                        longitude: results[0].geometry.location.lng()
                                    }
                                });

                            } else {

                                // console.log('Paris, France > google.maps.GeocoderStatus > NOT ok > try to detect position');
                                navigator.geolocation.getCurrentPosition(GetLocation, LocationNotFound);

                            }
                        });

                    }
                });

            }
        });


    } else {

        // console.log('try to detect position');

        // try to detect position
        navigator.geolocation.getCurrentPosition(GetLocation, LocationNotFound);

    }

    // front
    // Keep in carrier choice if pudo is not define
    if (ps_order_process_type) {
        // if OPC is active
        $('.payment_module a').each(function () {
            if (typeof($(this).attr('onclick')) != 'undefined') {
                $(this).data('onclick', $(this).attr('onclick'));
                $(this).attr('onclick', '');
            }
        });

        jQuery(document).on('click', '.payment_module', function (e) {
            if (isPickupCarrierSelected() && !$('input[name=LNP2_PICKUP_SITE]').val()) {
                e.preventDefault();
                e.stopPropagation();
                alert(please_pick_a_pudo_str);
            } else {
                var lien = $('a', $(this));
                if (lien.is("[onclick]") && lien.attr('onclick') == '') {
                    lien.attr('onclick', lien.data('onclick'));
                    lien.trigger('click');
                }
            }
        });
    } else {
        // if 5 steps active
        if (!map_is_drop_off) {
            jQuery('#carrier_area form#form').unbind('submit').bind('submit', function (e) {

                if (isPickupCarrierSelected() && !$('input[name=LNP2_PICKUP_SITE]').val()) {
                    e.preventDefault();
                    alert(please_pick_a_pudo_str);
                }

            });
        }
    }

    $('.pickup_map_search_icon').click(function (e) {
        e.preventDefault();
        $('.save_pudo').show();

        if ($(this).hasClass('cross')) {
            $('#pickup_map_search_input').val('');
            changePickupMapSearchIcon('lens');
        } else if ($(this).hasClass('change')) {
            changePickupMapSearchIcon('lens');
        }
        openMap();

    });

    $('#pickup_map_search_input').on("change paste keyup", function () {
        if ($(this).val()) {
            if ($('.pickup_map_search_icon').hasClass('lens'))
                changePickupMapSearchIcon('cross');
        } else {
            if ($('.pickup_map_search_icon').hasClass('cross')) {
                changePickupMapSearchIcon('lens');
                openMap();
            }
        }
    });

    $('.pickup_map_change_btn').click(function (e) {
        e.preventDefault();

        $('#pickup_map_search_input').removeClass('background_red');
        $('#pickup_map_search_input').val('');
        $(this).removeClass('cross').addClass('lens');

        $('.pickup_map_pudo_unavailable_div').hide();

    })

}

$(document).ready(function () {
    setTimeout(function () {
        if ((typeof map_is_drop_off !== 'undefined') && map_is_drop_off && ws_ok)
            initMapGlobal();
    }, 500);
});
