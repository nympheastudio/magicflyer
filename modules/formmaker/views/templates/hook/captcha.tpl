{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2016 silbersaiten
* @version   1.2.2
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}

<div class=" form-group">
    <div class="row">
        <div class="col-lg-3">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="captcha" id="captcha_on" value="1" {if $captcha.status == 1}checked="checked"{/if}>
                <label for="captcha_on">{l s='Yes' mod='formmaker'}</label>
                <input type="radio" name="captcha" id="captcha_off" value="0" {if $captcha.status == 0}checked="checked"{/if}>
                <label for="captcha_off">{l s='No' mod='formmaker'}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
        <div class="col-lg-3">
            <select name="FM_CAPTCHA_NUMBER_CHAR">
                <option value="0">{l s='Number of characters' mod='formmaker'}</option>
                {for $var=$captcha.count.min to $captcha.count.max}
                <option value="{$var|escape:'htmlall':'UTF-8'}" {if $captcha.FM_CAPTCHA_NUMBER_CHAR == $var}selected="selected"{/if}>{$var|escape:'htmlall':'UTF-8'}</option>
                {/for}
            </select>
        </div>
        <div class="col-lg-3">
            <select name="FM_CAPTCHA_TYPE">
                <option value="0">{l s='Type captcha' mod='formmaker'}</option>
                {foreach from=$captcha.type key=id item=name}
                <option value="{$id|escape:'htmlall':'UTF-8'}" {if $captcha.FM_CAPTCHA_TYPE == $id}selected="selected"{/if}>{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>