{*
* 2013 - 2015 CleanDev
*
* NOTICE OF LICENSE
*
* This file is proprietary and can not be copied and/or distributed
* without the express permission of CleanDev
*
* @author    CleanPresta : www.cleanpresta.com <contact@cleanpresta.com>
* @copyright 2013 - 2015 CleanDev.net
* @license   You only can use module, nothing more!
*}

{extends file="helpers/form/form.tpl"} 
{block name="input"}
	{if $input.type == 'custum_field'} 
	
	{else}
		{$smarty.block.parent}
    {/if}
{/block}
	

