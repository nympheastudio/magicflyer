<script>
{literal}
    function setcook() {
        var nazwa = 'cookie_ue';
        var wartosc = '1';
        var expire = new Date();
        expire.setMonth(expire.getMonth()+12);
        document.cookie = nazwa + "=" + escape(wartosc) +";path=/;" + ((expire==null)?"" : ("; expires=" + expire.toGMTString()))
    }
{/literal}
</script>
<style>
{literal}
.closebutton {
    cursor:pointer;
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #f9f9f9), color-stop(1, #e9e9e9) );
	background:-moz-linear-gradient( center top, #f9f9f9 5%, #e9e9e9 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f9f9f9', endColorstr='#e9e9e9');
	background-color:#f9f9f9;
	-webkit-border-top-left-radius:5px;
	-moz-border-radius-topleft:5px;
	border-top-left-radius:5px;
	-webkit-border-top-right-radius:5px;
	-moz-border-radius-topright:5px;
	border-top-right-radius:5px;
	-webkit-border-bottom-right-radius:5px;
	-moz-border-radius-bottomright:5px;
	border-bottom-right-radius:5px;
	-webkit-border-bottom-left-radius:5px;
	-moz-border-radius-bottomleft:5px;
	border-bottom-left-radius:5px;
	text-indent:0px;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#666666!important;
	font-family:Arial;
	font-size:14px;
	font-weight:bold;
	font-style:normal;
	height:25px;
	line-height:25px;
	text-decoration:none;
	text-align:center;
    padding:0px 10px;
	text-shadow:1px 1px 0px #ffffff;
}
.closebutton:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #e9e9e9), color-stop(1, #f9f9f9) );
	background:-moz-linear-gradient( center top, #e9e9e9 5%, #f9f9f9 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#e9e9e9', endColorstr='#f9f9f9');
	background-color:#e9e9e9;
}.closebutton:active {
	position:relative;
	top:1px;
}
{/literal}
{literal}
#cookieNotice p {margin:0px; padding:0px;}
{/literal}
</style>
<div id="cookieNotice" style="
width: 100%; 
position: fixed; 
{if $vareu->uecookie_position==2}
bottom:0px;
box-shadow: 0px 0 10px 0 #{$vareu->uecookie_shadow};
{else}
top:0px;
box-shadow: 0 0 10px 0 #{$vareu->uecookie_shadow};
{/if}
background: #{$vareu->uecookie_bg};
z-index: 9999;
font-size: 14px;
line-height: 1.3em;
font-family: arial;
left: 0px;
text-align:center;
color:#FFF;
opacity: {$vareu->uecookie_opacity}
">
    <div id="cookieNoticeContent" style="position:relative; margin:auto; padding:10px; width:100%; display:block;">
    <table style="width:100%;">
      <td style="text-align:center;">
        {$uecookie}
      </td>
      <td style="width:80px; vertical-align:middle; padding-right:20px; text-align:right;">
    	<span id="cookiesClose" class="closebutton"  onclick="
            {if $vareu->uecookie_position==2}
            {literal}
            $('#cookieNotice').animate(
            {bottom: '-200px'}, 
            2500, function(){
                $('#cookieNotice').hide();
            }); setcook();
            ">
            {/literal}{l s='close' mod='uecookie'}
            {else}
            {literal}
            $('#cookieNotice').animate(
            {top: '-200px'}, 
            2500, function(){
                $('#cookieNotice').hide();
            }); setcook();
            ">
            {/literal}{l s='close' mod='uecookie'}
            {/if}
        </span>
     </td>
     </table>
    </div>
</div>