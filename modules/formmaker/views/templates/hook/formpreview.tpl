{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2015 silbersaiten
* @version   1.0.3
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}
{if $preview}
    <a href="{$preview|escape:'quotes':'UTF-8'}" class="btn btn-primary"  target="_blank">{l s='View in the front office' mod='formmaker'}</a>
{else}
    <div class="alert alert-warning">{l s='You must save the form before the preview is available' mod='formmaker'}</div>
{/if}