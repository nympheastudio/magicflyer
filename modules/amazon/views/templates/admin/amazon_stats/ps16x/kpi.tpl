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
<div id="statsContainer" class="col-md-10">
    {if $show_kpi}
    <!-- Statistic -->
    <div class="row panel">
        <div class="col-sm-6 col-lg-3">
            <div id="box-conversion-rate" data-toggle="tooltip" class="box-stats label-tooltip color1">
                <div class="kpi-content">
                    <i class="icon-sort-by-attributes-alt"></i>
                    <span class="title">Sales</span>
                    <span class="subtitle">30 days</span>
                    <span class="value">{{$kpis['sales_30_days']|escape:'htmlall':'UTF-8'}}</span>
                </div>

            </div>

        </div>
        <div class="col-sm-6 col-lg-3">
            <div id="box-carts" data-toggle="tooltip" class="box-stats label-tooltip color2">
                <div class="kpi-content">
                    <i class="icon-shopping-cart"></i>
                    <span class="title">Incomes</span>
                    <span class="subtitle">30 days</span>
                    <span class="value">{{$kpis['income_30_days']|escape:'htmlall':'UTF-8'}}</span>
                </div>

            </div>

        </div>
        <div class="col-sm-6 col-lg-3">
            <div id="box-average-order" data-toggle="tooltip" class="box-stats label-tooltip color3">
                <div class="kpi-content">
                    <i class="icon-money"></i>
                    <span class="title">Average Order Value</span>
                    <span class="subtitle">30 days</span>
                    <span class="value">{{$kpis['avg_order_value']|number_format:2:".":","|escape:'htmlall':'UTF-8'}}</span>
                </div>

            </div>

        </div>
        <div class="col-sm-6 col-lg-3">
            <div id="box-net-profit-visit" data-toggle="tooltip" class="box-stats label-tooltip color4">
                <div class="kpi-content">
                    <i class="icon-user"></i>
                    <span class="title">Average orders per day</span>
                    <span class="subtitle">30 days</span>
                    <span class="value">{{$kpis['avg_order_per_day']|escape:'htmlall':'UTF-8'}}</span>
                </div>

            </div>

        </div>
    </div>
    <!-- End Statistic -->
    {/if}