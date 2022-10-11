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
{if !$psIs16}
    <div class="margin-form">
        <br/>
        <input type="submit" name="submit" style="float:right;margin:20px 50px 20px 0; display: none;"
               value="{l s='Save the Configuration' mod='amazon'}" class="button"/>
        <br/>
    </div>
{else}
    <div class="panel-footer">
            <button type="submit" value="1" name="submit"
                    class="btn btn-default pull-right" style="display: none;">
                <i class="process-icon-save"></i> {l s='Save the Configuration' mod='amazon'}
            </button>
    </div>
{/if}
<div class="clearfix"></div>

<input type="hidden" value="{l s='Parameters successfully saved' mod='amazon'}" class="amazon-message-success"/>
<input type="hidden" value="{l s='Unable to save parameters...' mod='amazon'}" class="amazon-message-error"/>
<!-- validate end -->