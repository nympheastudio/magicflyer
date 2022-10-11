{*
  * MODULE PRESTASHOP OFFICIEL CHRONOPOST
  * 
  * LICENSE : All rights reserved - COPY AND REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
  * LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
* SANS ACCORD EXPRES D'OXILEO
  *
  * @author    Oxileo SAS <contact@oxileo.eu>
  * @copyright 2001-2018 Oxileo SAS
  * @license   Proprietary - no redistribution without authorization
  *}
<script type="text/javascript">
    var rdv_carrierID = "{$rdv_carrierID|escape:'javascript':'UTF-8'}";
    var rdv_carrierIntID = "{$rdv_carrierIntID|escape:'javascript':'UTF-8'}";
    var transactionID = "{$rdv_transactionID|escape:'javascript':'UTF-8'}"

    {literal}
        $(function() {
    
            // Listener for selection of the chronordv carrier radio button
            $('input.delivery_option_radio, input[name=id_carrier]').click(function(e) {
                toggleRDVpane(cust_address_clean, cust_codePostal, cust_city, e);
            });


            // Prevent compatibility issues with Common Services' modules
            if($("#chronordv_container").length>0)
            {
                $('#chronordv_dummy_container').remove();
            } else {
                $('#chronordv_dummy_container').attr('id', 'chronordv_container');
            }

            // toggle on load
            toggleRDVpane(cust_address_clean, cust_codePostal, cust_city);

        });
    {/literal}
</script>



<div id="chronordv_dummy_container" class="">
    <table class="resume table table-bordered">
        <thead>
          <tr><th></th>
            {foreach from=$rdv_days item=day}
              <th>{$day|escape:'htmlall':'UTF-8'}</th>
            {/foreach}
          </tr>
        </thead>
        <tbody>
            {foreach from=$rdv_ordered_slots key=time item=slots}
            <tr><th>{$time|escape:'htmlall':'UTF-8'}</th>
                {foreach from=$rdv_days item=day}
                  {if array_key_exists($day, $slots) && $slots.$day->enable == 1}
                    <td{if $slots.$day->incentiveFlag} class="incentive"{/if}>
                    <div class="radio">
                        <label>
                          <input type="radio" name="chronoRDVSlot" value="{$slots.$day->rank|escape:'htmlall':'UTF-8'}" 
                          data-delivery-date="{$slots.$day->deliveryDateTime|escape:'htmlall':'UTF-8'}"
                          data-delivery-date-end="{$slots.$day->deliveryDateTimeEnd|escape:'htmlall':'UTF-8'}"
                          data-fee="{$slots.$day->fee|escape:'htmlall':'UTF-8'}"
                          data-slot-code="{$slots.$day->deliverySlotCode|escape:'htmlall':'UTF-8'}"
                          data-tariff-level="{$slots.$day->tariffLevel|escape:'htmlall':'UTF-8'}"/>&nbsp;{$slots.$day->price|escape:'htmlall':'UTF-8'}
                        </label>
                      </div>
                    </td>
                  {else} 
                    <td class="inactive">&nbsp;</td>
                  {/if}
                {/foreach}
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
