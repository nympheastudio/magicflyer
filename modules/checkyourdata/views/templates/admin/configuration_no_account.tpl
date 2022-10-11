{*
 * 2015 CheckYourData
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Thomas RIBIERE <thomas.ribiere@gmail.com>
 *  @copyright 2015 CheckYourData
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *}

<div class="checkyourdata {$checkyourdata_ps_version_class|escape:'htmlall':'UTF-8'}">
    <div class="row row-eq-height">
        <div class="col-md-6 right-block" id="cyd_bl_right">
            <div class="panel panel-primary panel-transparent col-md-8 col-md-offset-2 col-sm-12 signin-box">
                <div class="panel-body">
                    <div class="row checkyourdata_logo" id="checkyourdata_img_header">
                        <div class="col-lg-12">
                            <img src="{$checkyourdata_url_app|escape:'htmlall':'UTF-8'}public/img/logo_presta_bo1.3.svg"
                                 alt="CheckYourData"
                                 class="img-responsive" style="width:100%;"/>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center" >
                            <form class="text-center" role="form"
                                  action="{$checkyourdata_form_action|escape:'htmlall':'UTF-8'}"
                                  method="post" enctype="multipart/form-data"
                                  novalidate="">

                                <input type="hidden" id="checkyourdata_current_language"
                                       name="checkyourdata_current_language"
                                       value="{$checkyourdata_current_language|escape:'htmlall':'UTF-8'}">

                                <input type="hidden" id="checkyourdata_back_url"
                                       name="checkyourdata_back_url"
                                       value="{$checkyourdata_back_url|escape:'htmlall':'UTF-8'}">

                                <input type="hidden" id="checkyourdata_lastname"
                                       name="checkyourdata_lastname"
                                       value="{$checkyourdata_lastname|escape:'htmlall':'UTF-8'}">

                                <input type="hidden" id="checkyourdata_firstname"
                                       name="checkyourdata_firstname"
                                       value="{$checkyourdata_firstname|escape:'htmlall':'UTF-8'}">

                                <input type="hidden" id="checkyourdata_site"
                                       name="checkyourdata_site"
                                       value="{$checkyourdata_site|escape:'htmlall':'UTF-8'}">

                                <div class="form-group">
                                    <input type="text" class="form-control"
                                           id="checkyourdata_email"
                                           name="checkyourdata_email"
                                           placeholder="Email"
                                           value="{$checkyourdata_email|escape:'htmlall':'UTF-8'}">

                                    <input type="text" class="form-control"
                                           id="checkyourdata_ganalytics_ua"
                                           name="checkyourdata_ganalytics_ua"
                                           placeholder="UA-XXXXXXXX-X">
                                </div>
                                <button type="submit"
                                        id="submitcheckyourdata_signin"
                                        name="submitcheckyourdata_signin"
                                        class="btn btn-lg btn-success">{l s='Install' mod='checkyourdata'}</button>

                            </form>
                            <p>{l s='By installing Check Your Data, you agree to the following' mod='checkyourdata'}
                                <a href="{$checkyourdata_url_cgv|escape:'htmlall':'UTF-8'}" target="_blank">
                                    {l s='Terms and Conditions' mod='checkyourdata'}</a>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row exist-token text-center">
                <div class="panel panel-primary panel-transparent col-md-10 col-md-offset-1 login-box clearfix">
                    <div class="panel-body">
                        <p>{l s='If you already have a Check Your Data account, please enter your access key' mod='checkyourdata'}</p>
                        <form class="text-center" role="form"
                              action="{$checkyourdata_form_token_action|escape:'htmlall':'UTF-8'}"
                              method="post" enctype="multipart/form-data"
                              novalidate="">

                            <div class="input-group">
                                <input type="text" class="form-control"
                                       id="checkyourdata_token"
                                       name="checkyourdata_token">
                                <span class="input-group-btn">
                                    <button type="submit"
                                            class="btn btn-success"
                                            id="submitcheckyourdata_signin_token"
                                            name="submitcheckyourdata_signin_token">{l s='Save' mod='checkyourdata'}
                                    </button>
                                </span>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 text-center left-block">
            <h2><strong>{l s='Welcome to CheckYourData' mod='checkyourdata'}</strong></h2>
            <br />
            <h3><strong>{l s='Google Analytics Ecommerce Expert Pack' mod='checkyourdata'}</strong></h3>
            {if $checkyourdata_ps_version_class eq 'ps-15'}
            <p>{l s='Google Analytics is an outstanding analysis tool, but not that good regarding data tracking. Fix it right away to unlock the power of your Analytics! Get clean, compelling and 100&#37; reliable sales data.' mod='checkyourdata'}</p>
            {else}
                <p>{l s='Google Analytics is an outstanding analysis tool, but not that good regarding data tracking. Fix it right away to unlock the power of your Analytics! Get clean, compelling and 100% reliable sales data.' mod='checkyourdata'}</p>
            {/if}
            <h4><strong>{l s='Benefits' mod='checkyourdata'}</strong></h4>

            <div class="row">
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/brain.png" width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Focusing on data-driven Strategy' mod='checkyourdata'}</strong><br/>
                        {l s='Offer without obligation that fits your needs from 20€/month' mod='checkyourdata'}
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/money_bag.png" width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Save time and money' mod='checkyourdata'}</strong><br/>
                        {l s='With a 1­click Google Analytics Ecommerce Installation' mod='checkyourdata'}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/config.png"
                         width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Easy to install' mod='checkyourdata'}</strong><br/>
                        {l s='Simple­dead setup without hiring an IT guy' mod='checkyourdata'}
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/support.png"
                         width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Need Help ?' mod='checkyourdata'}</strong><br/>
                        {l s='We reply within 48 hours' mod='checkyourdata'}<br />
                        {l s='Call us at: 05 32 09 12 30' mod='checkyourdata'}
                    </p>
                </div>
            </div>

            <div class="row center-block">

                <div class="col-md-6 ">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ps_partner.jpg"
                         height="100"/>
                </div>
                <div class="col-md-6 google-partner">
                    <img class="" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-partner.jpg"
                         height="80"/>
                </div>
            </div>

        </div>
    </div>


</div>
