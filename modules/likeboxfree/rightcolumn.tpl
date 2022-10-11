{literal}
<div id="fb-root"></div>
<script>(function(d, s, id) {
var js, fjs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) return;
js = d.createElement(s); js.id = id;
js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";
fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
{/literal}
<div class="fb-page" data-width="{Configuration::get('lbf_width')}" adapt_container_width="true" data-height="{Configuration::get('lbf_height')}" data-href="{Configuration::get('lbf_url')}" data-small-header="{if Configuration::get('lbf_small_header')==1}true{else}false{/if}" data-hide-cta="{if Configuration::get('lbf_hide_cta')==1}true{else}false{/if}" data-hide-cover="{if Configuration::get('lbf_hide_cover')==1}true{else}false{/if}" data-show-facepile="{if Configuration::get('lbf_show_facepile')==1}true{else}false{/if}" data-show-posts="{if Configuration::get('lbf_show_posts')==1}true{else}false{/if}"><div class="fb-xfbml-parse-ignore"></div></div>