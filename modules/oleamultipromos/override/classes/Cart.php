<?php

class Cart extends CartCore
{
	public function addCartRule($id_cart_rule)
	{
		/* Oleacorner For Maxipromo */
		$oleamultipromos_module = Module::getInstanceByName('oleamultipromos');
		if (Validate::isLoadedObject($oleamultipromos_module) && $oleamultipromos_module->active)
			if (! $oleamultipromos_module->oleaCheckCartRulesIsForCart($id_cart_rule, $this->id))
				return false;
		/* End Oleacorner for maxipromo */

		return parent::addCartRule($id_cart_rule);
	}
}
