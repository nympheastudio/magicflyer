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
<div id="calendar" class="panel">
	<form action="{Context::getContext()->link->getAdminLink('AdminAmazonStats')|escape:'html':'UTF-8'}" method="post"
		  id="calendar_form" name="calendar_form" class="form-inline">
		<div class="row">
			<div class="col-lg-6">
				<div class="btn-group">
					<button type="submit" name="submitDateDay" class="btn btn-default submitDateDay">{$translations.Day|escape:'html':'UTF-8'}</button>
					<button type="submit" name="submitDateMonth" class="btn btn-default submitDateMonth">{$translations.Month|escape:'html':'UTF-8'}</button>
					<button type="submit" name="submitDateYear" class="btn btn-default submitDateYear">{$translations.Year|escape:'html':'UTF-8'}</button>
					<button type="submit" name="submitDateDayPrev" class="btn btn-default submitDateDayPrev">{$translations.Day|escape:'html':'UTF-8'}-1</button>
					<button type="submit" name="submitDateMonthPrev" class="btn btn-default submitDateMonthPrev">{$translations.Month|escape:'html':'UTF-8'}-1</button>
					<button type="submit" name="submitDateYearPrev" class="btn btn-default submitDateYearPrev">{$translations.Year|escape:'html':'UTF-8'}-1</button>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="row">
					<div class="col-md-8">
						<div class="row">
							<div class="col-xs-6">
								<div class="input-group">
									<label class="input-group-addon">{if isset($translations.From)}{$translations.From|escape:'html':'UTF-8'}{else}{l s='From:' mod='amazon'}{/if}</label>
									<input type="text" name="datepickerFrom" id="datepickerFrom" value="{$datepickerFrom|escape:'html':'UTF-8'}" class="datepicker  form-control">
								</div>
							</div>
							<div class="col-xs-6">
								<div class="input-group">
									<label class="input-group-addon">{if isset($translations.To)}{$translations.To|escape:'html':'UTF-8'}{else}{l s='From:' mod='amazon'}{/if}</label>
									<input type="text" name="datepickerTo" id="datepickerTo" value="{$datepickerTo|escape:'html':'UTF-8'}" class="datepicker  form-control">
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<button type="submit" name="submitDatePicker" id="submitDatePicker" class="btn btn-default"><i class="icon-save"></i> {l s='Save' mod='amazon'}</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		if ($("form#calendar_form .datepicker").length > 0)
			$("form#calendar_form .datepicker").datepicker({
				prevText: '',
				nextText: '',
				dateFormat: 'yy-mm-dd'
			});
	});
</script>
