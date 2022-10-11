{**
 * pm_crosssellingoncart
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *}

<div id="pm_panel_cs_modules_bottom" class="panel">
    <h2>{l s='Check all our modules' mod='pm_crosssellingoncart'}</h2>
        <div id="cs-product-container">
            <div id="cs-product-list">
                {foreach from=$pm_addons_products key=id_module item=module_cs}
                    <div class="product-item">
                        <p class="product-name-container">
                            <a class="product-name" target="_blank" href="{$module_cs.url|escape:'htmlall':'UTF-8'}" title="{$module_cs.displayName|escape:'htmlall':'UTF-8'}">{$module_cs.displayName|escape:'htmlall':'UTF-8'}</a>
                        </p>
                        <a target="_blank" class="module-image" href="{$module_cs.url|escape:'htmlall':'UTF-8'}" class="module-image
                        " title="{$module_cs.description|escape:'htmlall':'UTF-8'}"">
                            <img src="//cdn.presta-module.com/img/icons/{$id_module|intval}.png" alt="{$module_cs.displayName|escape:'htmlall':'UTF-8'}" width="110" height="110" />
                        </a>
                        <p class="desc">{$module_cs.description|escape:'htmlall':'UTF-8'}</p>
                    </div>
                {/foreach}
            </div>
        </div>
</div>
    {literal}
    <style rel="stylesheet" type="text/css" media="all">
    #cs-product-list { font-size: 12px; color: #353535; text-align: center; }
    #cs-product-list div.product-item a.product-name { font-size: 14px; line-height: 13px; color: #268ccd; text-decoration: none; text-align: center; }
    #cs-product-list div.product-item a.module-image { display: block; }
    #cs-product-list div.product-item img a { display: block; border: 0; }
    #cs-product-list div.product-item p { margin: 5px !important; text-overflow: ellipsis; white-space: nowrap; width: 90%; overflow: hidden; }
    #cs-product-list div.product-item p.desc a { display: table-cell; vertical-align: bottom; height: 30px; font-style: italic; text-align: center; }
    .owl-carousel .owl-wrapper:after { content:".";display:block;clear:both;visibility:hidden;line-height:0;height:0}.owl-carousel{display:none;position:relative;width:100%;-ms-touch-action:pan-y}.owl-carousel .owl-wrapper{display:none;position:relative}.owl-carousel .owl-wrapper-outer{overflow:hidden;position:relative;width:100%}.owl-carousel .owl-wrapper-outer.autoHeight{-webkit-transition:height 500ms ease-in-out;-moz-transition:height 500ms ease-in-out;-ms-transition:height 500ms ease-in-out;-o-transition:height 500ms ease-in-out;transition:height 500ms ease-in-out}.owl-carousel .owl-item{float:left}.owl-controls .owl-buttons div,.owl-controls .owl-page{cursor:pointer}.owl-controls{-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;-webkit-tap-highlight-color:transparent}.grabbing{cursor:move}.owl-carousel .owl-item,.owl-carousel .owl-wrapper{-webkit-backface-visibility:hidden;-moz-backface-visibility:hidden;-ms-backface-visibility:hidden;-webkit-transform:translate3d(0,0,0);-moz-transform:translate3d(0,0,0);-ms-transform:translate3d(0,0,0)}.owl-theme .owl-controls{margin-top:10px;text-align:center}.owl-theme .owl-controls .owl-buttons div{color:#FFF;display:inline-block;zoom:1;margin:5px;padding:3px 10px;font-size:12px;-webkit-border-radius:30px;-moz-border-radius:30px;border-radius:30px;background:#869791;filter:Alpha(Opacity=50);opacity:.5}.owl-theme .owl-controls.clickable .owl-buttons div:hover{filter:Alpha(Opacity=100);opacity:1;text-decoration:none}.owl-theme .owl-controls .owl-page{display:inline-block;zoom:1}.owl-theme .owl-controls .owl-page span{display:block;width:12px;height:12px;margin:5px 7px;filter:Alpha(Opacity=50);opacity:.5;-webkit-border-radius:20px;-moz-border-radius:20px;border-radius:20px;background:#869791}.owl-theme .owl-controls .owl-page.active span,.owl-theme .owl-controls.clickable .owl-page:hover span{filter:Alpha(Opacity=100);opacity:1}.owl-theme .owl-controls .owl-page span.owl-numbers{height:auto;width:auto;color:#FFF;padding:2px 10px;font-size:12px;-webkit-border-radius:30px;-moz-border-radius:30px;border-radius:30px}.owl-item.loading{min-height:150px}.owl-origin{-webkit-perspective:1200px;-webkit-perspective-origin-x:50%;-webkit-perspective-origin-y:50%;-moz-perspective:1200px;-moz-perspective-origin-x:50%;-moz-perspective-origin-y:50%;perspective:1200px}.owl-fade-out{z-index:10;-webkit-animation:fadeOut .7s both ease;-moz-animation:fadeOut .7s both ease;animation:fadeOut .7s both ease}.owl-fade-in{-webkit-animation:fadeIn .7s both ease;-moz-animation:fadeIn .7s both ease;animation:fadeIn .7s both ease}.owl-backSlide-out{-webkit-animation:backSlideOut 1s both ease;-moz-animation:backSlideOut 1s both ease;animation:backSlideOut 1s both ease}.owl-backSlide-in{-webkit-animation:backSlideIn 1s both ease;-moz-animation:backSlideIn 1s both ease;animation:backSlideIn 1s both ease}.owl-goDown-out{-webkit-animation:scaleToFade .7s ease both;-moz-animation:scaleToFade .7s ease both;animation:scaleToFade .7s ease both}.owl-goDown-in{-webkit-animation:goDown .6s ease both;-moz-animation:goDown .6s ease both;animation:goDown .6s ease both}.owl-fadeUp-in{-webkit-animation:scaleUpFrom .5s ease both;-moz-animation:scaleUpFrom .5s ease both;animation:scaleUpFrom .5s ease both}.owl-fadeUp-out{-webkit-animation:scaleUpTo .5s ease both;-moz-animation:scaleUpTo .5s ease both;animation:scaleUpTo .5s ease both}@-webkit-keyframes empty{0%{opacity:1}}@-moz-keyframes empty{0%{opacity:1}}@keyframes empty{0%{opacity:1}}@-webkit-keyframes fadeIn{0%{opacity:0}100%{opacity:1}}@-moz-keyframes fadeIn{0%{opacity:0}100%{opacity:1}}@keyframes fadeIn{0%{opacity:0}100%{opacity:1}}@-webkit-keyframes fadeOut{0%{opacity:1}100%{opacity:0}}@-moz-keyframes fadeOut{0%{opacity:1}100%{opacity:0}}@keyframes fadeOut{0%{opacity:1}100%{opacity:0}}@-webkit-keyframes backSlideOut{25%{opacity:.5;-webkit-transform:translateZ(-500px)}100%,75%{opacity:.5;-webkit-transform:translateZ(-500px) translateX(-200%)}}@-moz-keyframes backSlideOut{25%{opacity:.5;-moz-transform:translateZ(-500px)}100%,75%{opacity:.5;-moz-transform:translateZ(-500px) translateX(-200%)}}@keyframes backSlideOut{25%{opacity:.5;transform:translateZ(-500px)}100%,75%{opacity:.5;transform:translateZ(-500px) translateX(-200%)}}@-webkit-keyframes backSlideIn{0%,25%{opacity:.5;-webkit-transform:translateZ(-500px) translateX(200%)}75%{opacity:.5;-webkit-transform:translateZ(-500px)}100%{opacity:1;-webkit-transform:translateZ(0) translateX(0)}}@-moz-keyframes backSlideIn{0%,25%{opacity:.5;-moz-transform:translateZ(-500px) translateX(200%)}75%{opacity:.5;-moz-transform:translateZ(-500px)}100%{opacity:1;-moz-transform:translateZ(0) translateX(0)}}@keyframes backSlideIn{0%,25%{opacity:.5;transform:translateZ(-500px) translateX(200%)}75%{opacity:.5;transform:translateZ(-500px)}100%{opacity:1;transform:translateZ(0) translateX(0)}}@-webkit-keyframes scaleToFade{to{opacity:0;-webkit-transform:scale(.8)}}@-moz-keyframes scaleToFade{to{opacity:0;-moz-transform:scale(.8)}}@keyframes scaleToFade{to{opacity:0;transform:scale(.8)}}@-webkit-keyframes goDown{from{-webkit-transform:translateY(-100%)}}@-moz-keyframes goDown{from{-moz-transform:translateY(-100%)}}@keyframes goDown{from{transform:translateY(-100%)}}@-webkit-keyframes scaleUpFrom{from{opacity:0;-webkit-transform:scale(1.5)}}@-moz-keyframes scaleUpFrom{from{opacity:0;-moz-transform:scale(1.5)}}@keyframes scaleUpFrom{from{opacity:0;transform:scale(1.5)}}@-webkit-keyframes scaleUpTo{to{opacity:0;-webkit-transform:scale(1.5)}}@-moz-keyframes scaleUpTo{to{opacity:0;-moz-transform:scale(1.5)}}@keyframes scaleUpTo{to{opacity:0;transform:scale(1.5)}}
    </style>
    <script src="//cdn.presta-module.com/js/owl-carousel/owl.carousel.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#cs-product-list").owlCarousel({ pagination:false, responsiveBaseWidth: $('#pm_panel_cs_modules_bottom'), stopOnHover: true, autoPlay: 6000 });
        });
    </script>
    {/literal}