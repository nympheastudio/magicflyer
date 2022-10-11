{*
* 2007-2015 PrestaShop
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
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="note row">
	<p class="col-lg-6 pull-left"><i class="icon-info"></i> Drap &amp; Drop Position box In Row box to change position order.</p>
	<p class="col-lg-6 pull-right"><i class="icon-info"></i> Click to <strong>Layout</strong> button to choose layout what you want to change grid.</p>
	<p class="col-lg-6 pull-left"><i class="icon-info"></i> Drap &amp; Drop Row box In Row List to change row order.</p>
	<p class="col-lg-6 pull-right"><i class="icon-info"></i> Hover to right side of position box => scroll to resize position box.</p>
	<p class="col-lg-6 pull-right"><i class="icon-info"></i> After resize position box in all layout grid => click to <strong>Save Layout Grid</strong> to save it.</p>
</div>
<div class="panel">
	<div class="profile-title">
		<i class="icon-list-ul"></i> {l s='Row list' mod='jmsthemelayout'} - {$name_prof|escape:'html':'UTF-8'}
		<span class="pull-right">
			<a class="save_pos" title="Save Layout Grid">
				<i class="icon-save"></i>
			</a>
			<a id="desc-product-new" class="list-toolbar-btn" href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:'html':'UTF-8'}&addRow=1" title="Add Row">			
				<i class="icon-plus"></i>
			</a>
		</span>			
		<span class="btn-group-action pull-right devices-layout">
			<div class="dropdown btn-group">
				<a class="dropdown-toggle  btn btn-default" id="dLabel" role="button" data-toggle="dropdown" data-target="#">										
					Layout <b class="caret"></b>
				</a>
				<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
					<li><a class="btn btn-success switch-lg" data-device="lg"><i class="icon-desktop"></i>Desktop</a></li>
					<li><a class="btn btn-default switch-md" data-device="md"><i class="icon-desktop"></i>Medium Device</a></li>
					<li><a class="btn btn-default switch-sm" data-device="sm"><i class="icon-tablet"></i>Tablet</a></li>
					<li><a class="btn btn-default switch-xs" data-device="xs"><i class="icon-mobile"></i>Mobile</a></li>
				</ul>
			</div>
		</span>	
		
	</div>
	<div id="rowlist" class="col-lg">
	<div class="rowlist">
		{foreach from=$rows key=i item=row}
				<div id="row_{$row.id_row|escape:'html':'UTF-8'}" class="adv-row container">
					<div class="row-title">
						<div class="col-lg-1">
							<span><i class="icon-arrows"></i></span>
						</div>						
						<div class="col-md-11">
							<div class="pull-left">{l s='Row' mod='jmsthemelayout'} : {$row.title|escape:'html':'UTF-8'}</div>							
							<div class="btn-group-action pull-right"><div class="btn-group pull-right">
								<a class="btn {if $row.active}btn-success{else}btn-danger{/if}" href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&changeRowStatus&id_prof={$id_prof|escape:'html':'UTF-8'}&id_row={$row.id_row|escape:'html':'UTF-8'}" title="{if $row.active}Enabled{else}Disabled{/if}"><i class="{if $row.active}icon-check{else}icon-remove{/if}"></i></a>						
								<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
									<span class="caret">&nbsp;</span>
								</button>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
									<li>
										<a class="btn btn-default"	href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:'html':'UTF-8'}&id_row={$row.id_row|escape:'html':'UTF-8'}&addPosition=1"><i class="icon-plus"></i>{l s='Add Position' mod='jmsthemelayout'}</a>
									</li>	
									<li>
										<a class="btn btn-default"
									href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:'html':'UTF-8'}&id_row={$row.id_row|escape:'html':'UTF-8'}"><i class="icon-edit"></i>{l s='Edit' mod='jmsthemelayout'}</a>
									</li>
									<li>
										<a class="btn btn-default"	href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:'html':'UTF-8'}&delete_id_row={$row.id_row|escape:'html':'UTF-8'}" onclick="return confirm('Are you sure you want to delete this row?');"><i class="icon-trash"></i>{l s='Delete' mod='jmsthemelayout'}</a>
									</li>	
								</ul>
							</div></div>
						</div>
					</div>
					<div class="row-positions row">								
						{foreach from=$row.positions key=j item=position}
						<div class="position col-lg-{$position.col_lg|escape:'html':'UTF-8'} col-md-{$position.col_md|escape:'html':'UTF-8'} col-sm-{$position.col_sm|escape:'html':'UTF-8'} col-xs-{$position.col_xs|escape:'html':'UTF-8'}" id="position_{$position.id_position|escape:'html':'UTF-8'}" data-id-pos="{$position.id_position|escape:'html':'UTF-8'}" data-col-lg="{$position.col_lg|escape:'html':'UTF-8'}" data-col-md="{$position.col_md|escape:'html':'UTF-8'}" data-col-sm="{$position.col_sm|escape:'html':'UTF-8'}" data-col-xs="{$position.col_xs|escape:'html':'UTF-8'}"><div class="position-inner">
							<div class="position-title">	
								#{$position.id_position|escape:'html':'UTF-8'} - {$position.title|escape:'html':'UTF-8'}
								<span class="btn-group-action pull-right">
									<div class="dropdown btn-group">
										<a class="btn {if $position.active}btn-success{else}btn-danger{/if}"	href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&changePositionStatus&id_prof={$id_prof|escape:'html':'UTF-8'}&id_row={$position.id_row|escape:'html':'UTF-8'}&id_position={$position.id_position|escape:'html':'UTF-8'}" title="{if $position.active}Enabled{else}Disabled{/if}">
												<i class="{if $position.active}icon-check{else}icon-remove{/if}"></i></a>
										<a class="dropdown-toggle  btn btn-default" id="dLabel" role="button" data-toggle="dropdown" data-target="#">										
										<b class="caret"></b>
										</a>
										<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">																						
											<li>
												<a class="btn btn-default"	href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:'html':'UTF-8'}&id_position={$position.id_position|escape:'html':'UTF-8'}&addBlock=1">
													<i class="icon-plus"></i>{l s='Add Block' mod='jmsthemelayout'}</a>
											</li>
											<li>
												<a class="btn btn-default"													href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:'html':'UTF-8'}&edit_position&id_row={$row.id_row|escape:'html':'UTF-8'}&id_position={$position.id_position|escape:'html':'UTF-8'}">
													<i class="icon-edit"></i>{l s='Edit' mod='jmsthemelayout'}
												</a>
											</li>
											<li>
												<a class="btn btn-default"	href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:'html':'UTF-8'}&delete_id_position={$position.id_position|escape:'html':'UTF-8'}" onclick="return confirm('Are you sure you want to delete this position?');">
													<i class="icon-trash"></i>{l s='Delete' mod='jmsthemelayout'}
												</a>
											</li>
										</ul>
									</div>
								</span>
							</div>
							<div class="pos-blocks">								
								{foreach from=$position.blocks item=block}
								<div class="block" id="block_{$block.id_block|escape:'html':'UTF-8'}">
									<div class="block-inner">										
										{$block.title|escape:'html':'UTF-8'}{if $block.block_type == 'custom_html'} (Html){/if}
										<span class="btn-group-action pull-right">
											<div class="dropdown btn-group">
											<a class="btn {if $block.active}btn-success{else}btn-danger{/if}" href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&changeBlockStatus&id_prof={$id_prof|escape:'html':'UTF-8'}&id_block={$block.id_block|escape:'html':'UTF-8'}" title="{if $block.active}Enabled{else}Disabled{/if}"><i class="{if $block.active}icon-check{else}icon-remove{/if}"></i> </a>
											<a class="dropdown-toggle  btn btn-default" id="dLabel" role="button" data-toggle="dropdown" data-target="#">										
											<b class="caret"></b>
											</a>
											<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">	
												<li>
													<a class="btn btn-default"	href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&edit_block&id_prof={$id_prof|escape:'html':'UTF-8'}&id_position={$position.id_position|escape:'html':'UTF-8'}&id_block={$block.id_block|escape:'html':'UTF-8'}"><i class="icon-edit"></i>{l s='Edit' mod='jmsthemelayout'}</a>
												</li>
												<li>
													<a class="btn btn-default"	href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:'html':'UTF-8'}&delete_id_block={$block.id_block|escape:'html':'UTF-8'}" onclick="return confirm('Are you sure you want to delete this block?');"><i class="icon-trash"></i>{l s='Delete' mod='jmsthemelayout'}</a>
												</li>
											</ul>
											</div>		
										</span>
									</div>
								</div>
								{/foreach}
							</div>
						</div></div>
						{/foreach}						
					</div>
				</div>
			{/foreach}		
	</div></div>
</div>
<input id="current_url" type="hidden" name="current_url" value="{$current_url|escape:'html':'UTF-8'}" />
<script type="text/javascript">
$(document).ready( function(){	
	$(".icon-toggle").click( function(){
		$(this).parent().parent('.hookbox').toggleClass('box-hidden');	
		$(this).toggleClass('icon-angle-up');
		$(this).toggleClass('icon-angle-down');
	});
});	

</script>