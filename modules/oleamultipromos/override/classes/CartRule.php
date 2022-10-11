<?php

class CartRule extends CartRuleCore
{
	public static function autoAddToCart(Context $context = null)
	{
		if ($context === null)
			$context = Context::getContext();
		$oleamultipromos_module = Module::getInstanceByName('oleamultipromos');
		if (Validate::isLoadedObject($oleamultipromos_module) && $oleamultipromos_module->active)
			$oleamultipromos_module->oleaCartRefreshForMultiPromo15(array('cart'=>$context->cart));
		return parent::autoAddToCart($context);
	}

}
