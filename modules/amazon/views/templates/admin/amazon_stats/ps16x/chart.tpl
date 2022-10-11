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
<div class="panel" id="amazon-stats-chart">
    <!-- Chart -->
    <div class="row">
        <div class="col-sm-12 col-lg-12">
            <div id="amazon_chart_toolbar" class="row">
                <dl class="col-xs-4 col-lg-2 label-tooltip" data-toggle="tooltip" data-placement="bottom" data-chart="sales" data-index="0" data-chart-label="{l s='Sales' mod='amazon'}">
                    <dt>{l s='Sales' mod='amazon'}</dt>
                    <dd class="data_value size_l"><span id="sales_score">{convertPrice price=$chart_total.sales}</span></dd>
                    <dd class="dash_trend"><span id="sales_score_trends"></span></dd>
                </dl>
                <dl class="col-xs-4 col-lg-2 label-tooltip" data-toggle="tooltip" data-placement="bottom" data-chart="orders" data-index="1" data-chart-label="{l s='Orders' mod='amazon'}">
                    <dt>{l s='Orders' mod='amazon'}</dt>
                    <dd class="data_value size_l"><span id="orders_score">{$chart_total.orders|escape:'htmlall':'UTF-8'}</span></dd>
                    <dd class="dash_trend"><span id="orders_score_trends"></span></dd>
                </dl>
            </div>

            <div class="chart">
                <svg></svg>
            </div>
        </div>
    </div>
    <!-- End Chart -->
</div>