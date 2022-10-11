<!-- Block user information module NAV  -->
{if $is_logged}
<div class="header_user_info">
<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" title="{l s='View my customer account' mod='blockuserinfo'}" class="account" rel="nofollow"><span>{$cookie->customer_firstname} {$cookie->customer_lastname}</span></a>
</div>
{/if}
<div class="header_user_info">
{if $is_logged}
<a class="logout" href="{$link->getPageLink('index', true, NULL, "mylogout")|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Log me out' mod='blockuserinfo'}">
{l s='Sign out' mod='blockuserinfo'}
</a>
{else}
<a data-action="login"  class="login" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Log in to your customer account' mod='blockuserinfo'}" id="show_modal_login">
{l s='Sign in' mod='blockuserinfo'}
</a>
{/if}
</div>
<!-- /Block usmodule NAV -->

<!--_________________modal LOGIN_______________-->
<!-- The Modal -->
<div class="modal">

<!-- Modal content -->
<div class="modal-content">

<div class="modal-body">

<!-- Formulaire -->
<form class="form-signin" method="post" action="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}"> 
<div class="close-login">
<p class="close"><i>&times;</i></p>
</div>      
<h1 class="form-signin-heading">{l s='LOG IN' mod='blockuserinfo'}</h1>
<!--erreur-->
<span class="eroura" >{l s='Wrong mail or password ! Please retry' mod='blockuserinfo'}</span>
<input type="email" id="mail" class="form-control input" name="email" placeholder="{l s='email' mod='blockuserinfo'}" data-validate="isEmail" required/> 
<input type="password"  id="pass" class="form-control input" name="passwd" placeholder="{l s='Mot de passe' mod='blockuserinfo'}" required /> 
<button name="SubmitLogin" class="btn login_btn" type="submit">{l s='login' mod='blockuserinfo'}</button> 
<p class="forgot-pass">
<a  href="{$link->getPageLink('password', true)|escape:'html'}">{l s='I forgot my password' mod='blockuserinfo'}</a></p>  
<p class="dont-have-an-account"><a href="{$link->getPageLink('my-account', true)|escape:'html'}" rel="nofollow">{l s='create an account' mod='blockuserinfo'}</a></p>
</form>

</div>



</div>
</div>
{literal}
<script>
$(document).ready(function(){


	
	$( "#show_modal_login,#show_modal_login_orderopc" ).click(function(e) {
		e.preventDefault();
		$(".modal").modal('toggle');
		$(".modal").addClass('visible_login');
		e.stopPropagation();
	});

	//manidy
	$( ".close-login" ).click(function(e) {
		e.preventDefault();
		$(".modal").removeClass('visible_login');
		$(".modal").hide();
		$(".modal-content").css('top',0);
		
	});

	$(".modal-body .form-signin .login_btn").click(function(event1) {
		//alert('connexion en cours (test redirect panier)');
		event1.preventDefault();
		event1.stopPropagation();
		//var that = $(this);
		
		$.ajax({
type: 'GET',
//headers: { "cache-control": "no-cache" },
url: authenticationUrl + '?rand=' + new Date().getTime(),
async: true,
cache: true,
			dataType : "json",
data: '&back=shopping-cart&SubmitLogin=true&ajax=true&email='+encodeURIComponent($('.form-signin #mail').val())+'&passwd='+encodeURIComponent($('.form-signin  #pass').val())+'&token=' + static_token ,
success: function(jsonData)
			{
				//console.log(jsonData)
				if (jsonData.hasError)
				{
					/*var errors = '<b>'+txtThereis+' '+jsonData.errors.length+' '+txtErrors+':</b><ol>';
						for(var error in jsonData.errors)
							//IE6 bug fix
							if(error !== 'indexOf')
								errors += '<li>'+jsonData.errors[error]+'</li>';
						errors += '</ol>';
						$('#opc_login_errors').html(errors).slideDown('slow');*/
					//$(".eroura").css("display", "block");
					swal("Oups ! Merci de véifier vos identifiants et mot de passe !");
				}
				else
				{
					/*// update token
						static_token = jsonData.token;
						updateNewAccountToAddressBlock(that.attr('data-adv-api'));*/
					//location.reload();//too fucking slow
					window.location.href=window.location.href;
					//window.location.href = cartlink;//myaccountlink;
				}
			},
error: function(XMLHttpRequest, textStatus, errorThrown) {
				if (textStatus !== 'abort')
				{
					error = "TECHNICAL ERROR: unable to send login informations \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
					if (!!$.prototype.fancybox)
					$.fancybox.open([
					{
type: 'inline',
autoScale: true,
minHeight: 30,
content: '<p class="fancybox-error">' + error + '</p>'
					}
					], {
padding: 0
					});
					else
					alert(error);

				}
				console.log('data Error');
			}
		});
	});

	$(".formtp .btnsub").click(function(event1) {
		event1.preventDefault();
		console.log('tst');
		var that = $(this);
		$.ajax({
type: 'POST',
headers: { "cache-control": "no-cache" },
url: authenticationUrl + '?rand=' + new Date().getTime(),
async: false,
cache: false,
			dataType : "json",
data: 'SubmitLogin=true&ajax=true&email='+encodeURIComponent($('.formtp .ep').val())+'&passwd='+encodeURIComponent($('.form-signin  .pwo').val())+'&token=' + static_token ,
success: function(jsonData)
			{
				//console.log(jsonData)
				if (jsonData.hasError)
				{
					/*var errors = '<b>'+txtThereis+' '+jsonData.errors.length+' '+txtErrors+':</b><ol>';
						for(var error in jsonData.errors)
							//IE6 bug fix
							if(error !== 'indexOf')
								errors += '<li>'+jsonData.errors[error]+'</li>';
						errors += '</ol>';
						$('#opc_login_errors').html(errors).slideDown('slow');*/
					$(".eroura").css("display", "block");
				}
				else
				{
					/*// update token
						static_token = jsonData.token;
						updateNewAccountToAddressBlock(that.attr('data-adv-api'));*/
					window.location.href = myaccountlink;
				}
			},
error: function(XMLHttpRequest, textStatus, errorThrown) {
				if (textStatus !== 'abort')
				{
					error = "TECHNICAL ERROR: unable to send login informations \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
					if (!!$.prototype.fancybox)
					$.fancybox.open([
					{
type: 'inline',
autoScale: true,
minHeight: 30,
content: '<p class="fancybox-error">' + error + '</p>'
					}
					], {
padding: 0
					});
					else
					alert(error);
				}
				console.log('data Error');
			}
		});
	});

});
</script>{/literal}


{addJsDef authenticationUrl=$link->getPageLink("authentication", true)|escape:'quotes':'UTF-8'}
{addJsDef myaccountlink=$link->getPageLink('my-account', true)|escape:'quotes':'UTF-8' }
{addJsDef cartlink=$link->getPageLink('cart', true)|escape:'quotes':'UTF-8' }
