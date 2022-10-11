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

<script>
    {literal}
    function select_undelected() {
        if ($('select[name="c_value"] option:selected').val() == 3) {
            $('input[name="subcatt"]').parent().parent().parent().hide();
            $('input[name="subcata"]').parent().parent().parent().hide();
        } else if ($('select[name="c_value"] option:selected').val() == 4) {
            $('input[name="c_target2"]').val(0);
            $('input[name="c_target2"]').parent().parent().hide();
            $('input[name="subcatt"]').parent().parent().parent().show();
        } else {
            $('input[name="c_target2"]').val();
            $('input[name="c_target2"]').parent().parent().show();
        }
    }

    $('document').ready(function () {

        $('select[name="c_value"]').change(function () {
            select_undelected();
        });

        $(".admincartassociationscondition #c_type").change(function () {
            admincartassociationscondition_c_type();
        });

        admincartassociationscondition_c_type();
        select_undelected();
    });

    function admincartassociationscondition_c_type() {
        if (+$("#c_type").val() == 2) {
            $('#c_target2').parent().find('p').html('{/literal}{l s='Category' mod='cartcon'}{literal}');
            $('#c_target1').parent().find('p .object').html('{/literal}{l s='Product' mod='cartcon'}{literal}');
            $('input[name="subcatt"]').parent().parent().parent().hide();
            $('input[name="subcata"]').parent().parent().parent().hide();
            $('select[name="c_value"] option[value="4"]').attr('disabled', 'disabled');

        } else if (+$("#c_type").val() == 3) {
            $('#c_target2').parent().find('p').html('{/literal}{l s='Category' mod='cartcon'}{literal}');
            $('#c_target1').parent().find('p .object').html('{/literal}{l s='Category' mod='cartcon'}{literal}');
            $('input[name="subcatt"]').parent().parent().parent().hide();
            $('input[name="subcata"]').parent().parent().parent().hide();
            $('select[name="c_value"] option[value="4"]').removeAttr('disabled');
        } else {
            $('#c_target2').parent().find('p').html('{/literal}{l s='Product' mod='cartcon'}{literal}');
            $('#c_target1').parent().find('p .object').html('{/literal}{l s='Product' mod='cartcon'}{literal}');
            $('input[name="subcatt"]').parent().parent().parent().hide();
            $('input[name="subcata"]').parent().parent().parent().hide();
            $('select[name="c_value"] option[value="4"]').attr('disabled', 'disabled');
        }
    }
    {/literal}
</script>