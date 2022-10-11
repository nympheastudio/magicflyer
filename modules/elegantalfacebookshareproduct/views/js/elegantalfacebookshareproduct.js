/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2018, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
var elegantal_fb_app_id = '';
var elegantal_fb_event = '';
var elegantal_locale = 'en_US';
var elegantal_post_id = '';
var elegantal_url_to_share = '';
var elegantal_process_action_url = '';
var elegantal_debug_mode = 0;

jQuery(window).on('load', function () {
    jQuery('#elegantal_share_popup').fadeIn(350);
});

jQuery(document).ready(function () {
    elegantal_fb_app_id = jQuery('#elegantalfacebookshareproductJsDef').data('fbappid');
    elegantal_fb_event = jQuery('#elegantalfacebookshareproductJsDef').data('fbevent');
    elegantal_locale = jQuery('#elegantalfacebookshareproductJsDef').data('locale');
    elegantal_url_to_share = jQuery('#elegantalfacebookshareproductJsDef').data('shareurl');
    elegantal_process_action_url = jQuery('#elegantalfacebookshareproductJsDef').data('action');
    elegantal_debug_mode = jQuery('#elegantalfacebookshareproductJsDef').data('debug');

    // Init Facebook SDK
    window.fbAsyncInit = function () {
        FB.init({
            appId: elegantal_fb_app_id,
            status: true,
            xfbml: true,
            version: 'v3.0'
        });

        // Like event happpened
        if (elegantal_fb_event === 'like') {
            FB.Event.subscribe('edge.create', function (href, widget) {
                var liked_url = href.replace('http://', '').replace('https://', '');
                var url_to_like = elegantal_url_to_share.replace('http://', '').replace('https://', '');
                if (liked_url.indexOf(url_to_like) !== -1 || url_to_like.indexOf(liked_url) !== -1) {
                    console.log('You just liked the page ' + href);
                    elegantal_post_id = 'like';
                    elegantal_after_event();
                }
            });
        }
    };
    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/" + elegantal_locale + "/all.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    // Share button click event
    jQuery('body').on('click', 'button.elegantal-fbshare4gift-btn', elegantal_fb_share);

    // Popup close button click event
    jQuery('.elegantal-popup-close').on('click', function (e) {
        e.preventDefault();
        jQuery('#elegantal_share_popup').fadeOut();
    });

    // Gift select table click
    jQuery('#elegantal_select_gift_popup table tr').on('click', function () {
        jQuery('#elegantal_select_gift_popup table .radio .checked').removeClass('checked');
        jQuery(this).find('input[type=radio]').prop('checked', true).parent().addClass('checked');
    });

    // Gift submit button click
    jQuery('button.elegantal-fbchoosegift-btn').on('click', function () {
        var cart_rule_id = jQuery('#elegantal_select_gift_popup input[name="cart_rule"]:checked').val();
        jQuery.ajax({
            url: elegantal_process_action_url,
            type: 'GET',
            data: {
                post_id: elegantal_post_id,
                cart_rule_id: cart_rule_id,
            },
            success: function (result) {
                jQuery('#elegantal_select_gift_popup').fadeOut();
                if (elegantal_debug_mode) {
                    console.log(result);
                }
                result = jQuery.parseJSON(result);
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.error);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
    });
});

function elegantal_fb_share() {
    // Close popup
    jQuery('#elegantal_share_popup').fadeOut();

    // Facebook share
    FB.ui({
        method: 'share',
        href: elegantal_url_to_share
    }, function (response) {
        if (elegantal_debug_mode) {
            console.log(response);
        }
        if (response !== null && typeof response !== 'undefined' && (typeof response.post_id !== 'undefined' || response.length === 0)) {
            var post_id = 'publish_actions not granted';
            if (typeof response !== 'undefined' && typeof response.post_id !== 'undefined') {
                post_id = response.post_id;
            }
            elegantal_post_id = post_id;
            elegantal_after_event();
        }
    });
}

function elegantal_after_event() {
    if (jQuery('#elegantal_select_gift_popup').length > 0) {
        jQuery('#elegantal_select_gift_popup table .radio .checked').removeClass('checked');
        jQuery('#elegantal_select_gift_popup table tr:first input[type=radio]').prop('checked', true).parent().addClass('checked');
        jQuery('#elegantal_select_gift_popup').fadeIn();
    } else {
        jQuery.ajax({
            url: elegantal_process_action_url,
            type: 'GET',
            data: {
                post_id: elegantal_post_id
            },
            success: function (result) {
                if (elegantal_debug_mode) {
                    console.log(result);
                }
                result = jQuery.parseJSON(result);
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.error);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
    }
}