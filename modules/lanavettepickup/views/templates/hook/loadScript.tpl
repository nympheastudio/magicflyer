{*
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
 *}

<script type="application/javascript">
    var ps_order_process_type = {if $ps_order_process_type}true{else}false{/if};

    (function () {
        $.holdReady(true);
        if ((typeof google === 'object') && (typeof google.maps === 'object') && (typeof google.maps.places === 'object')) {
            $.getScript('{$pickup_dir|escape:'html':'UTF-8'}views/js/vendor/markerwithlabel/markerwithlabel.min.js');
            $.getScript('{$pickup_dir|escape:'html':'UTF-8'}views/js/vendor/infobox/infobox.min.js');
            $.holdReady(false);
            return;
        } else {
            $.getScript('//maps.googleapis.com/maps/api/js?libraries=places&key={$google_maps_key|escape:'html':'UTF-8'}')
             .done(function (script, textStatus) {
                 $.getScript('{$pickup_dir|escape:'html':'UTF-8'}views/js/vendor/markerwithlabel/markerwithlabel.min.js');
                 $.getScript('{$pickup_dir|escape:'html':'UTF-8'}views/js/vendor/infobox/infobox.min.js');
                 $.holdReady(false);
             })
             .fail(function (jqxhr, settings, exception) {
                 console.log('fail while getting google maps');
                 $.holdReady(false);
             });
        }
    })(this);
</script>
