{**
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
 *}

    <form id="introduction" method="POST" action="{$EMC_link|escape:'htmlall':'UTF-8'}&EMC_tab=merchant">
        <div class="content_left">
            <div class="text_align_center">
                {l s='Manage multiple carriers using one single module and save up to 75% on your shipping costs without commitments or any contracts' mod='envoimoinscher'}
                <div class="button-red btnValid create">
                    <a href="#" class="text_align_center">{l s='Create an account' mod='envoimoinscher'}</a>
                </div>
                <div>
                    <a href="#" class="btnValid italic text_align_center">{l s='Already have an account? LOG IN' mod='envoimoinscher'}</a>
                </div>
                <input type="hidden" name="choice" value="">
                <input type="submit" class="hidden" name="btnIntro" value="Suivant">
            </div>
        </div>
        <div class="content_right">
            <div class="text_align_center">
                {l s='Offer the best carriers to your customers' mod='envoimoinscher'}
                <div id="pc_img">
                    <img src="{$emcImgDir}{$intro_pc}">
                    <div id="your_bo">{l s='Your Prestashop back-office' mod='envoimoinscher'}</div>
                    <div id="your_website">{l s='Your website' mod='envoimoinscher'}</div>
                    
                </div>
                <div>
                    <div id="print_waybills">{l s='Print your shipping labels in 1 click' mod='envoimoinscher'}</div>
                    <div id="reduce_costs">{l s='Save up to 75% on your shipping costs' mod='envoimoinscher'}</div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            {literal}
                $(".btnValid").click(function() {
                    if($(this).hasClass("create")) {
                        $('#introduction').find('input[name=choice]').val("create");
                    } else {
                        $('#introduction').find('input[name=choice]').val("api");
                    }
                    $('#introduction').find('input[type=submit]').click();
                });
            {/literal}
        </script>
    </form>
