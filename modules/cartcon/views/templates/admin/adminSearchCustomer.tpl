{*
* PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
*
* @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
* @copyright 2010-2020 VEKIA
* @license   This program is not free software and you can't resell and redistribute it
*
* CONTACT WITH DEVELOPER http://mypresta.eu
* support@mypresta.eu
*}

<script type="text/javascript">
    {literal}
    var version = "{/literal}{$version|escape:'htmlall':'UTF-8'}{literal}";
    var link = "{/literal}{Context::getContext()->link->getAdminLink('AdminCustomers')}{literal}";
    $(document).ready(function () {
        $('#customer_autocomplete_input').typeWatch({
            captureLength: 3,
            highlight: true,
            wait: 100,
            callback: function () {
                searchCustomers();
            }
        });
    });

    function searchCustomers() {
        var customer_search = $('#customer_autocomplete_input').val();
        $.ajax({
            type: "POST",
            url: link,
            async: true,
            dataType: "json",
            data: {
                ajax: "1",
                tab: "AdminCustomers",
                action: "searchCustomers",
                customer_search: customer_search
            },
            success: function (res) {
                if (res.found) {
                    var html = '';
                    $.each(res.customers, function () {
                        html += '<div class="customerCard col-lg-4">';
                        html += '<div class="panel">';
                        html += '<div class="panel-heading"><span class="customer-name' + this.id_customer + '">' + this.firstname + ' ' + this.lastname + '</span>';
                        html += '<span class="pull-right">#' + this.id_customer + '</span></div>';
                        html += '<span class="customer-email' + this.id_customer + '">' + this.email + '</span><br/>';
                        html += '<span class="text-muted">' + ((this.birthday != '0000-00-00') ? this.birthday : '') + '</span><br/>';
                        html += '<div class="panel-footer">';
                        html += '<button onclick="addCustomer(' + this.id_customer + ')" type="button" data-customer="' + this.id_customer + '" class="add-customer btn btn-default pull-right"><i class="icon-plus"></i></button>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    });
                }

                $('.customerSearchResults').html(html);
                var search_items = customer_search.split(' ');
                $.each(search_items, function (index, value) {
                    $('.customerSearchResults').highlight(value);
                });
            }
        });
    }

    function deleteCustomer(id) {
        $("#selected_customer_" + id).remove();
    }

    function addCustomer(id) {
        $('#addCustomers').html($('#addCustomers').html() + returnCustomerBody(id));
    }

    function returnCustomerBody(id) {
        return '<div id="selected_customer_' + id + '" class="form-control-static margin-form col-lg-6"><input type="hidden" name="CARTCON_CUSTOMERS[]" value="' + id + '" class="CARTCON_CUSTOMERS"><button type="button" class="btn btn-default remove-customer" name="' + id + '" onclick="deleteCustomer(' + id + ')"><i class="icon-remove text-danger"></i></button>&nbsp;' + $('.customer-name' + id).html() + ' (' + $('.customer-email' + id).html() + ')</div>';
    }
    {/literal}
</script>


<div class="col-lg-9">
    <div id="ajax_choose_customer" class="clearfix">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="input-group">
                <input id="customer_autocomplete_input" name="" type="text" class="text ac_input" value=""/>
                <input id="lang_spy" type="hidden" value="{$id_langg}"/>
                <span class="input-group-addon"><i class="icon-search"></i></span>
            </div>
        </div>
    </div>

    <div class="customerSearchResults" style="margin-top:10px;">

    </div>
    <div class="clearfix"></div>
    <div class="panel">
        <h3><i class="icon-user"></i> {l s='Selected Customers' mod='cartcon'}</h3>
        <div id="addCustomers" class="clearfix">
            {if $customers != false}
                {foreach $customers AS $customer}
                    <div id="selected_customer_{$customer->id}" class="form-control-static margin-form col-lg-6"><input type="hidden" name="CARTCON_CUSTOMERS[]" value="{$customer->id}" class="CARTCON_CUSTOMERS">
                        <button type="button" class="btn btn-default remove-customer" name="{$customer->id}" onclick="deleteCustomer({$customer->id})"><i class="icon-remove text-danger"></i></button>&nbsp;{$customer->firstname} {$customer->lastname} ({$customer->email})
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>

</div>