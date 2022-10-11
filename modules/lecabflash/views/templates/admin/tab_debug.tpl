{*
 *
 * 2009-2017 202 ecommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    202 ecommerce <support@202-ecommerce.com>
 *  @copyright 2009-2017 202 ecommerce SARL
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 *}

{literal}
<script language="javascript">

    $(document).ready(function() { 

        $('*[data-test]').click(function() {
            event.preventDefault();
            callLecabFlashAjax(
                {'action': 'runtest', "test": $(this).data("test") },  
                function (data) {
                    $('#lecabflash-testresult').html();
                    $('#lecabflash-testresult').html( JSON.stringify(data,null, "\t") );
                } 
            );
        });

        $("a[data-cartid]").click(function() {
            callLecabFlashAjax(
                {"action": "checkcart", "id_cart": $(this).data("cartid") },
                function (data) { alert(JSON.stringify(data)) } 
            );
        });

        $("a.json").click(function() {
            alert($(this).html() );
        });


    });



</script>
{/literal}


<style>

    #lecabflash_debug td {
        padding: 5px;
        border: solid 1px #ccc;
    }

    a.json {
        display: block;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100px;
        max-height: 30px;
        overflow: hidden;
    }
</style>



<div class="panel">
    <div>
        <table id="lecabflash_debug">
               
                <tr>
                    <td>CARTID</td>
                    <td>estimate_id</td>
                    <td>price</td>
                    <td>context</a></td>
                    <td>confirm_request</a></td>
                    <td>confirm_response</a></td>
                    <td>confirm_id</td>
                    
                </tr>
             {foreach from=$carts item=cart}
                <tr>
                    <td><a href="#" data-cartid="{$cart.id_cart|escape:'htmlall':'UTF-8'}">CART #{$cart.id_cart|escape:'htmlall':'UTF-8'}</a></td>
                    <td>{$cart.estimate_id|escape:'htmlall':'UTF-8'}</td>
                    <td>{$cart.price|escape:'htmlall':'UTF-8'}</td>
                    <td><a href="#" class="json">{$cart.estimate_context|escape:'htmlall':'UTF-8'}</a></td>
                    <td><a href="#" class="json">{$cart.confirm_request|escape:'htmlall':'UTF-8'}</a></td>
                    <td><a href="#" class="json">{$cart.confirm_response|escape:'htmlall':'UTF-8'}</a></td>
                    <td>{$cart.confirm_id|escape:'htmlall':'UTF-8'}</td>
                    
                </tr>
            {/foreach}
        </table>
    </div>
</div>

<div class="panel">
    <button class="btn btn-primary" id="lecabflash-runrest" data-test="">RUN ALL TESTS</button>
    <div class="row">

        <div class="col-md-3">

            <ul>
             {foreach from=$tests item=test}
                <li><a href="#" data-test="{$test|escape:'htmlall':'UTF-8'}">{$test|escape:'htmlall':'UTF-8'}</a></li>
            {/foreach}
            </ul>

        </div>    
        <div class="col-md-9">


            <pre>
            <code id="lecabflash-testresult"> </code>
            </pre>
        </div>    
    </div>

</div>    



