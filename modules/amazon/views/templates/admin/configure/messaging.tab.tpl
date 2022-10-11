{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @package   Amazon Market Place
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * Support by mail:  support.amazon@common-services.com
*}
<div id="menudiv-messaging" class="tabItem {if $messaging.selected_tab}selected{/if} panel form-horizontal">
    <h3>{l s='Messaging' mod='amazon'}</h3>
    <input type="hidden" id="messaging_ajax_error"
           value="{l s='An unexpected server side error occurs, please verify your module configuration first.' mod='amazon'}"/>
    {if !$amazon.is_lite}
    <div class="margin-form">
        <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
            <ul>
                <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                <li>{$messaging.tutorial|escape:'quotes':'UTF-8'}</li>
            </ul>
        </div>
    </div>
    {/if}

    {if ! $messaging.problem}
        <br/>
        <div class="form-group">
            <label class="control-label col-lg-3" style="color:grey"
                   rel="send_invoice"><span>{l s='Send invoice by email' mod='amazon'}</span></label>
            <br/>
            <br/>
            <div class="cleaner col-lg-9"></div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Active' mod='amazon'}</label>
            <div class="margin-form col-lg-9">
                <div class="form-group">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="mail_invoice[active]" id="mail_invoice" value="1"
                               {if $messaging.account_type_is_global}disabled{/if} {if ($messaging.mail_invoice.active && !$messaging.account_type_is_global)}checked{/if} />
                            <label for="mail_invoice" class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                        <input type="radio" name="mail_invoice[active]" id="mail_invoice-2" value="0"
                               {if $messaging.account_type_is_global}disabled{/if} {if !($messaging.mail_invoice.active)}checked{/if} />
                            <label for="mail_invoice-2" class="label-checkbox">{l s='No' mod='amazon'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>

                {if $messaging.account_type_is_global}
                    <div class="form-group">
                        <div class="warn {if $psIs16}alert alert-warning{/if}"
                             style="width:95%;">{l s='Unavailable while the account type is configured on "global" in parameters/advanced parameters tabs (in expert mode)' mod='amazon'}
                        </div>
                    </div>
                {/if}
            </div>
        </div>

        <div id="mail_invoice_activated" style="{if ! $messaging.mail_invoice.active}display:none{/if}">
            <div class="form-group">
                <label class="control-label col-lg-3"
                       rel="order_status"><span>{l s='Orders Statuses' mod='amazon'}</span></label>

                <div class="margin-form col-lg-9">
                    <select name="mail_invoice[order_state]" style="width:300px;">
                        <option value="0">{l s='Choose the order status' mod='amazon'}</option>
                        {foreach from=$messaging.order_states item=order_state}
                            <option value="{$order_state.value|escape:'htmlall':'UTF-8'}"
                                    {if ($messaging.mail_invoice.order_state == $order_state.value)}selected="selected"{/if}>{$order_state.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>

                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3"
                       rel="mail_template"><span>{l s='Choose Mail Template' mod='amazon'}</span></label>

                <div align="left" class="margin-form col-lg-9">
                    <select name="mail_invoice[template]" style="width:210px">
                        <option value="0">{l s='Please Choose in the List' mod='amazon'}</option>
                        {foreach from=$messaging.mail_templates item=mail_template}
                            <option value="{$mail_template|escape:'htmlall':'UTF-8'}"
                                    {if ($messaging.mail_invoice.template == $mail_template)}selected="selected"{/if}>{$mail_template|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>


                </div>
            </div>

            {if ($messaging.is_ps15)}
                <div class="form-group">
                    <label class="control-label col-lg-3"
                           rel="additional_file"><span>{l s='Additionnal File' mod='amazon'}</span></label>
                    <div align="left" class="margin-form col-lg-9">
                        <select name="mail_invoice[additionnal]" style="width:210px">
                            <option value="0">{l s='Please Choose in the List' mod='amazon'}</option>
                            {foreach from=$messaging.mail_add_files item=mail_add_file}
                                <option value="{$mail_add_file|escape:'htmlall':'UTF-8'}"
                                        {if ($messaging.mail_invoice.additionnal == $mail_add_file)}selected="selected"{/if}>{$mail_add_file|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>

                        <p>
                            {if !$messaging.mail_add_files}
                                <span style="color:navy">{l s='Your list is currently empty. You can put your extra PDF file in the modules/amazon/pdf directory.' mod='amazon'}</span>
                                <br/>
                            {/if}
                        </p>
                    </div>


                    {if isset($messaging.test) && is_array($messaging.test)}
                        <div class="form-group">
                        <label class="control-label col-lg-3" rel="test_invoice"><span>{l s='Diagnosis' mod='amazon'}</span></label>
                             <div align="left" class="margin-form col-lg-9">
                            <select id="test-invoice-select" style="width:210px">
                                <option value="0">{l s='Please Choose an order in the List' mod='amazon'}</option>
                                {foreach from=$messaging.test item=order}
                                    <option value="{$order.id_order|intval}">#{$order.id_order|intval} - {$order.customer|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>&nbsp;&nbsp;<input type="button" class="button btn" id="test-invoice-button" value="{l s='Send Email' mod='amazon'}" />&nbsp;&nbsp;<img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif" id="test-invoice-loader" style="display:none"/>
                            <p>
                                <span style="color:navy">{l s='Test mode: allows you to send an invoice to check the operation, validate the workflow. The invoice will be sent to your email address.' mod='amazon'}</span>
                                <br/>
                            </p>
                             </div>
                        </div>
                        <div class="form-group">

                            <div align="left" class="margin-form col-lg-offset-3">

                                <div id="test-invoice-success" class="{$class_success|escape:'htmlall':'UTF-8'}" style="display:none">
                                </div>

                                <div id="test-invoice-error" class="{$class_error|escape:'htmlall':'UTF-8'}" style="display:none">
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>
            {/if}
        </div>

        <div class="form-group">
            <hr class="amz-separator"/>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3"
                   style="color:grey"
                   rel="seller_review"><span>{l s='Seller Review Incentive' mod='amazon'}</span><sup class="experimental">{l s='New' mod='amazon'}</sup></label>
            <br/>
            <br/>
            <div class="cleaner"></div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Active' mod='amazon'}</label>
            <div class="margin-form col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="mail_review[active]" id="mail_review" value="1"
                       {if ($messaging.mail_review.active)}checked{/if} /><label for="mail_review"
                                                                                 class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                <input type="radio" name="mail_review[active]" id="mail_review-2" value="0"
                       {if !($messaging.mail_review.active)}checked{/if} /><label for="mail_review-2"
                                                                                  class="label-checkbox">{l s='No' mod='amazon'}</label>
                <a class="slide-button btn"></a>
            </span>

            </div>
        </div>

        <div id="mail_review_activated" style="{if ! $messaging.mail_review.active}display:none{/if}">
            <div class="form-group">
                <label class="control-label col-lg-3"
                       rel="review_order_status"><span>{l s='Orders Statuses' mod='amazon'}</span></label>

                <div class="margin-form col-lg-9">
                    <select name="mail_review[order_state]" style="width:300px;">
                        <option value="0">{l s='Choose the order status' mod='amazon'}</option>
                        {foreach from=$messaging.order_states item=order_state}
                            <option value="{$order_state.value|escape:'htmlall':'UTF-8'}"
                                    {if ($messaging.mail_review.order_state == $order_state.value)}selected="selected"{/if}>{$order_state.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>

                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3"
                       rel="choosemail_template"><span>{l s='Choose Mail Template' mod='amazon'}</span></label>

                <div align="left" class="margin-form col-lg-9">
                    <select name="mail_review[template]" style="width:210px">
                        <option value="0">{l s='Please Choose in the List' mod='amazon'}</option>
                        {foreach from=$messaging.mail_templates item=mail_template}
                            <option value="{$mail_template|escape:'htmlall':'UTF-8'}"
                                    {if ($messaging.mail_review.template == $mail_template)}selected="selected"{/if}>{$mail_template|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>


                </div>
            </div>


            <div class="form-group">
                <label class="control-label col-lg-3" rel="maxdelay"><span>{l s='Maximum Delay' mod='amazon'}</span></label>

                <div align="left" class="margin-form col-lg-9">
                    <input type="text" name="mail_review[delay]" style="width:{if $psIs16}50{else}30{/if}px"
                           value="{$messaging.mail_review.delay|escape:'htmlall':'UTF-8'}"/>&nbsp;<span
                            class="span_text">{l s='Opening Days' mod='amazon'}</span>


                </div>
            </div>

            {if isset($messaging.test) && is_array($messaging.test)}
                <div class="form-group">
                    <label class="control-label col-lg-3" rel="test_review"><span>{l s='Diagnosis' mod='amazon'}</span></label>
                    <div align="left" class="margin-form col-lg-9">
                        <select id="test-review-select" style="width:210px">
                            <option value="0">{l s='Please Choose an order in the List' mod='amazon'}</option>
                            {foreach from=$messaging.test item=order}
                                <option value="{$order.id_order|intval}">#{$order.id_order|intval} - {$order.customer|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>&nbsp;&nbsp;<input type="button" class="button btn" id="test-review-button" value="{l s='Send Email' mod='amazon'}" />&nbsp;&nbsp;<img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif" id="test-review-loader" style="display:none"/>
                        <p>
                            <span style="color:navy">{l s='Test mode: allows you to send a review incentive to check the operation, validate the workflow. The message will be sent to your email address.' mod='amazon'}</span>
                            <br/>
                        </p>
                    </div>
                </div>
                <div class="form-group">

                    <div align="left" class="margin-form col-lg-offset-3">

                        <div id="test-review-success" class="{$class_success|escape:'htmlall':'UTF-8'}" style="display:none">
                        </div>

                        <div id="test-review-error" class="{$class_error|escape:'htmlall':'UTF-8'}" style="display:none">
                        </div>
                    </div>
                </div>
            {/if}
        </div>

        <div class="form-group">
            <hr class="amz-separator"/>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3"
                   style="color:grey"
                   rel="customer_thread"><span>{l s='Customer Service' mod='amazon'}</span><sup class="experimental">{l s='New' mod='amazon'}</sup></label>
            <br/>
            <br/>
            <div class="cleaner"></div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Active' mod='amazon'}</label>
            <div class="margin-form col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="customer_thread[active]" id="customer_thread" value="1"
                       {if ($messaging.customer_thread.active)}checked{/if} /><label for="customer_thread"
                                                                                 class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                <input type="radio" name="customer_thread[active]" id="customer_thread-2" value="0"
                       {if !($messaging.customer_thread.active)}checked{/if} /><label for="customer_thread-2"
                                                                                  class="label-checkbox">{l s='No' mod='amazon'}</label>
                <a class="slide-button btn"></a>
            </span>

            </div>
        </div>

        <div id="customer_thread_activated" style="{if ! $messaging.customer_thread.active}display:none{/if}">
            {if !$messaging.customer_thread.imap_open}
                <div class="form-group">
                    <label class="control-label col-lg-3"></label>
                    <div class="margin-form col-lg-9">
                        <div class="warn {if $psIs16}alert alert-warning{/if}">
                            {l s='Imap PHP library seems to be not installed on your server, this feature won\'t work' mod='amazon'}
                        </div>
                    </div>
                </div>
            {/if}
            <div class="form-group">
                <label class="control-label col-lg-3"
                       rel="customer_thread_template"><span>{l s='Choose Mail Template' mod='amazon'}</span></label>

                <div align="left" class="margin-form col-lg-9">
                    <select name="customer_thread[template]" style="width:210px">
                        <option value="0">{l s='Please Choose in the List' mod='amazon'}</option>
                        {foreach from=$messaging.mail_templates item=mail_template}
                            <option value="{$mail_template|escape:'htmlall':'UTF-8'}"
                                    {if ($messaging.customer_thread.template == $mail_template)}selected="selected"{/if}>{$mail_template|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>


            <div class="form-group">
                <label class="control-label col-lg-3" rel="customer_thread_imap"><span>{l s='Email Provider' mod='amazon'}</span></label>

                <div align="left" class="margin-form col-lg-9">
                    <select name="customer_thread[mail_provider]" style="width:300px">
                        <option disabled>{l s='Please Choose in the List' mod='amazon'}</option>
                        <option></option>
                        {foreach from=$messaging.customer_thread.mail_providers key=key item=mail_provider}
                            <option value="{$key|escape:'htmlall':'UTF-8'}"
                                    {if ($messaging.customer_thread.mail_provider == $key)}selected="selected"{/if}>{$mail_provider|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3" rel="email_login"><span>{l s='Login' mod='amazon'}</span></label>

                <div align="left" class="margin-form col-lg-9">
                    <input type="text" name="customer_thread[login]" style="width:300px;" value="{$messaging.customer_thread.login|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3"><span>{l s='Password' mod='amazon'}</span></label>

                <div align="left" class="margin-form col-lg-9">
                    <input type="password" name="customer_thread[password]" style="width:300px;" value="{$messaging.customer_thread.password|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3" rel="messaging_label"><span>{l s='Labels' mod='amazon'}</span></label>

                <div align="left" class="margin-form col-lg-9">
                    <select style="width:300px;overflow:hidden" disabled multiple size="{$messaging.customer_thread.labels_count|intval}">
                        {foreach from=$messaging.customer_thread.labels item=label}
                            <option>{$label|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div align="left" class="margin-form col-lg-9 col-lg-offset-3">
                    <p>{l s='Do not forget to setup the' mod='amazon'}&nbsp;<label rel="messaging_filter"><span>{l s='Filters' mod='amazon'}</span></label>&nbsp;{l s='and to configure the email on' mod='amazon'}&nbsp;<label rel="email_amazon"><span>Seller Central</span></label></p>
                </div>
            </div>
        </div>



        <!-- validation button -->
        {$messaging.validation|escape:'quotes':'UTF-8'}

    {else}
        <div class="warn {if $psIs16}alert alert-warning{/if}"
             style="width:95%;">{l s='Unable to display this configuration tab' mod='amazon'}
        </div>
    {/if}

    </form>
</div><!-- menudiv-messaging -->