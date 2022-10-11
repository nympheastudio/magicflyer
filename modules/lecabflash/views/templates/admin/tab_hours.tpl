{*
 *
 * 2009-2017 202 ecommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    202 ecommerce <support@202-ecommerce.com>
 *  @copyright 2009-2017 202 ecommerce SARL
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 *}

<form id="hours_form" class="defaultForm form-horizontal lecabflash" action="{$moduleLink|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" novalidate="">
	<div class="panel" id="fieldset_hours">
		<div class="panel-heading">
			{l s='Schedule' mod='lecabflash'}
		</div>
		<div class="alert alert-info">{l s='Enter the schedule of your depot. Be sure it‘s open for the courier during the opening hours.' mod='lecabflash'}</div>
		<div class="form-wrapper">
			<table class="lecab__hours-table">
				<tr class="table-heading">
					<th></th>
					<th>{l s='AM' mod='lecabflash'}</th>
					<th>{l s='PM' mod='lecabflash'}</th>
				</tr>
				<tr class="odd">
					<td class="day">
						<div class="checkbox">
							<label for="monday">
								<input type="checkbox" name="monday" id="monday" value="true" {if $hours.monday}checked="checked"{/if}>
								{l s='Monday' mod='lecabflash'}
							</label>
						</div>
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="monday_am_open_hour" id="monday_am_open_hour" value="{$hours.monday_am_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="monday_am_open_min" id="monday_am_open_min" value="{$hours.monday_am_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="monday_am_close_hour" id="monday_am_close_hour" value="{$hours.monday_am_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="monday_am_close_min" id="monday_am_close_min" value="{$hours.monday_am_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="monday_pm_open_hour" id="monday_pm_open_hour" value="{$hours.monday_pm_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="monday_pm_open_min" id="monday_pm_open_min" value="{$hours.monday_pm_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="monday_pm_close_hour" id="monday_pm_close_hour" value="{$hours.monday_pm_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="monday_pm_close_min" id="monday_pm_close_min" value="{$hours.monday_pm_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
				</tr>
				<tr>
					<td class="day">
						<div class="checkbox">
							<label for="tuesday">
								<input type="checkbox" name="tuesday" id="tuesday" value="true" {if $hours.tuesday}checked="checked"{/if}>
								{l s='Tuesday' mod='lecabflash'}
							</label>
						</div>
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="tuesday_am_open_hour" id="tuesday_am_open_hour" value="{$hours.tuesday_am_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="tuesday_am_open_min" id="tuesday_am_open_min" value="{$hours.tuesday_am_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="tuesday_am_close_hour" id="tuesday_am_close_hour" value="{$hours.tuesday_am_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="tuesday_am_close_min" id="tuesday_am_close_min" value="{$hours.tuesday_am_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="tuesday_pm_open_hour" id="tuesday_pm_open_hour" value="{$hours.tuesday_pm_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="tuesday_pm_open_min" id="tuesday_pm_open_min" value="{$hours.tuesday_pm_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="tuesday_pm_close_hour" id="tuesday_pm_close_hour" value="{$hours.tuesday_pm_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="tuesday_pm_close_min" id="tuesday_pm_close_min" value="{$hours.tuesday_pm_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
				</tr>
				<tr class="odd">
					<td class="day">
						<div class="checkbox">
							<label for="wednesday">
								<input type="checkbox" name="wednesday" id="wednesday" value="true" {if $hours.wednesday}checked="checked"{/if}>
								{l s='Wednesday' mod='lecabflash'}
							</label>
						</div>
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="wednesday_am_open_hour" id="wednesday_am_open_hour" value="{$hours.wednesday_am_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="wednesday_am_open_min" id="wednesday_am_open_min" value="{$hours.wednesday_am_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="wednesday_am_close_hour" id="wednesday_am_close_hour" value="{$hours.wednesday_am_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="wednesday_am_close_min" id="wednesday_am_close_min" value="{$hours.wednesday_am_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="wednesday_pm_open_hour" id="wednesday_pm_open_hour" value="{$hours.wednesday_pm_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="wednesday_pm_open_min" id="wednesday_pm_open_min" value="{$hours.wednesday_pm_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="wednesday_pm_close_hour" id="wednesday_pm_close_hour" value="{$hours.wednesday_pm_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="wednesday_pm_close_min" id="wednesday_pm_close_min" value="{$hours.wednesday_pm_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
				</tr>
				<tr>
					<td class="day">
						<div class="checkbox">
							<label for="thursday">
								<input type="checkbox" name="thursday" id="thursday" value="true" {if $hours.thursday}checked="checked"{/if}>
								{l s='Thursday' mod='lecabflash'}
							</label>
						</div>
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="thursday_am_open_hour" id="thursday_am_open_hour" value="{$hours.thursday_am_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="thursday_am_open_min" id="thursday_am_open_min" value="{$hours.thursday_am_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="thursday_am_close_hour" id="thursday_am_close_hour" value="{$hours.thursday_am_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="thursday_am_close_min" id="thursday_am_close_min" value="{$hours.thursday_am_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="thursday_pm_open_hour" id="thursday_pm_open_hour" value="{$hours.thursday_pm_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="thursday_pm_open_min" id="thursday_pm_open_min" value="{$hours.thursday_pm_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="thursday_pm_close_hour" id="thursday_pm_close_hour" value="{$hours.thursday_pm_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="thursday_pm_close_min" id="thursday_pm_close_min" value="{$hours.thursday_pm_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
				</tr>
				<tr class="odd">
					<td class="day">
						<div class="checkbox">
							<label for="friday">
								<input type="checkbox" name="friday" id="friday" value="true" {if $hours.friday}checked="checked"{/if}>
								{l s='Friday' mod='lecabflash'}
							</label>
						</div>
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="friday_am_open_hour" id="friday_am_open_hour" value="{$hours.friday_am_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="friday_am_open_min" id="friday_am_open_min" value="{$hours.friday_am_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="friday_am_close_hour" id="friday_am_close_hour" value="{$hours.friday_am_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="friday_am_close_min" id="friday_am_close_min" value="{$hours.friday_am_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="friday_pm_open_hour" id="friday_pm_open_hour" value="{$hours.friday_pm_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="friday_pm_open_min" id="friday_pm_open_min" value="{$hours.friday_pm_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="friday_pm_close_hour" id="friday_pm_close_hour" value="{$hours.friday_pm_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="friday_pm_close_min" id="friday_pm_close_min" value="{$hours.friday_pm_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
				</tr>
				<tr>
					<td class="day">
						<div class="checkbox">
							<label for="saturday">
								<input type="checkbox" name="saturday" id="saturday" value="true" {if $hours.saturday}checked="checked"{/if}>
								{l s='Saturday' mod='lecabflash'}
							</label>
						</div>
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="saturday_am_open_hour" id="saturday_am_open_hour" value="{$hours.saturday_am_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="saturday_am_open_min" id="saturday_am_open_min" value="{$hours.saturday_am_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="saturday_am_close_hour" id="saturday_am_close_hour" value="{$hours.saturday_am_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="saturday_am_close_min" id="saturday_am_close_min" value="{$hours.saturday_am_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="saturday_pm_open_hour" id="saturday_pm_open_hour" value="{$hours.saturday_pm_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="saturday_pm_open_min" id="saturday_pm_open_min" value="{$hours.saturday_pm_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="saturday_pm_close_hour" id="saturday_pm_close_hour" value="{$hours.saturday_pm_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="saturday_pm_close_min" id="saturday_pm_close_min" value="{$hours.saturday_pm_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
				</tr>
				<tr class="odd">
					<td class="day">
						<div class="checkbox">
							<label for="sunday">
								<input type="checkbox" name="sunday" id="sunday" value="true" {if $hours.sunday}checked="checked"{/if}>
								{l s='Sunday' mod='lecabflash'}
							</label>
						</div>
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="sunday_am_open_hour" id="sunday_am_open_hour" value="{$hours.sunday_am_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="sunday_am_open_min" id="sunday_am_open_min" value="{$hours.sunday_am_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="sunday_am_close_hour" id="sunday_am_close_hour" value="{$hours.sunday_am_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="sunday_am_close_min" id="sunday_am_close_min" value="{$hours.sunday_am_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
					<td class="hours-col">
						<input type="number" class="js_numbers_only" name="sunday_pm_open_hour" id="sunday_pm_open_hour" value="{$hours.sunday_pm_open_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="sunday_pm_open_min" id="sunday_pm_open_min" value="{$hours.sunday_pm_open_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
						<i class="icon-arrow-right"></i>
						<input type="number" class="js_numbers_only" name="sunday_pm_close_hour" id="sunday_pm_close_hour" value="{$hours.sunday_pm_close_hour|escape:'htmlall':'UTF-8'}" min="0" max="24">:<input type="number" class="js_numbers_only" name="sunday_pm_close_min" id="sunday_pm_close_min" value="{$hours.sunday_pm_close_min|escape:'htmlall':'UTF-8'}" min="0" max="60">
					</td>
				</tr>
			</table>
		</div>
		<div class="alert alert-info exemple">
			<img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/exemple_horaires.png" alt="exemple horaires">
			<ul>
				<li><b>{l s='Example :' mod='lecabflash'}</b></li>
				<li>{l s='- Check the day line when you open and fill the time slots' mod='lecabflash'}</li>
				<li>{l s='- If you open all day (uninterrupted), leave the late morning and early afternoon boxes empty' mod='lecabflash'}</li>
			</ul>
		</div>
		<div class="alert alert-warning exemple">
			<b>{l s='Warning!' mod='lecabflash'}</b><br />
			{l s='Hours must be in 24 hours format.' mod='lecabflash'}
		</div>
		<input type="hidden" name="lecabflash_token" value="{$ajax_token|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="employee_id" value="{$employee_id|intval}" />
		<div class="panel-footer">
			<button type="submit" value="1" id="btn_key" name="general_settings" class="button pull-right">
				<i class="process-icon-save"></i> {l s='Save' mod='lecabflash'}
			</button>
		</div>
	</div>
</form>





