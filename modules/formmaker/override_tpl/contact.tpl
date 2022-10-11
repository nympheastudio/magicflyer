{**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2017 silbersaiten
 * @version   1.3.7
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 *}
{extends file='page.tpl'}

{block name='page_header_container'}{/block}

{block name='left_column'}
    <div id="left-column" class="col-xs-12 col-sm-3">
        {widget name="ps_contactinfo" hook='displayLeftColumn'}
    </div>
{/block}

{block name='page_content'}
    {if (int)Configuration::get('FM_CONTACT_FORM') > 0}
        <p>{displayForm id=Configuration::get('FM_CONTACT_FORM')}</p>
    {else}
        {widget name="contactform"}
    {/if}
{/block}
