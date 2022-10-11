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

{literal}
<script>
    $(document).ready(function () {

        $('#searchForAttributeInput').keyup(function () {
            if (this.value.length < 2) return;
            $.post({/literal}"{$cartQuantityConditionLink}&searchForAttributeValue=1&ajax=1"{literal}, {searchQuery: this.value}, function (data) {
                $('.searchForAttributeResult').html(data);
                $('.resultAddAttributeValue').off().click(function () {
                    $('.resultAddAttributeValue').removeClass('active');
                    $(this).addClass('active');
                    $('#c_target').val($(this).data('id'));
                });
            });
        });

        $("#c_value").change(function () {
            if (+$("#c_value").val() != 0) {
                $(".multi_expample").html($("#c_value").val() * 1 + ', ' + $("#c_value").val() * 2 + ', ' + $("#c_value").val() * 3 + ', ' + $("#c_value").val() * 4 + ', ...');
            }
        });
        $("#c_type").change(function () {
            if (+$("#c_type").val() != 1 && +$("#c_type").val() != 6 && +$("#c_type").val() != 4) {
                $("input[name='multiply']").parent().parent().parent().hide();
            } else {
                $("input[name='multiply']").parent().parent().parent().show();
            }
        });
        if (+$("#c_value").val() != 0) {
            $(".multi_expample").html($("#c_value").val() * 1 + ', ' + $("#c_value").val() * 2 + ', ' + $("#c_value").val() * 3 + ', ' + $("#c_value").val() * 4 + ', ...');
        }

        if (+$("#c_type").val() != 1 && +$("#c_type").val() != 6 && +$("#c_type").val() != 4) {
            $("input[name='multiply']").parent().parent().parent().hide();
        } else {
            $("input[name='multiply']").parent().parent().parent().show();
        }

        $(".admincartquantitycondition #c_type").change(function () {
            admincartquantitycondition_c_type();
        });
        admincartquantitycondition_c_type();
    });


    function admincartquantitycondition_c_type() {
        if (+$("#c_type").val() == 4 || +$("#c_type").val() == 5) {
            $("#c_target").attr('disabled', 'disabled');
            $("#c_target").val('{/literal}{l s='Cart' mod='cartcon'}{literal}');
        } else {
            $("#c_target").removeAttr('disabled');
        }
        if (+$("#c_type").val() == 7) {
            $('.searchAttributeValue').show();
        } else {
            $('.searchAttributeValue').hide();
        }
    }

</script>
{/literal}