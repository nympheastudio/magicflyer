<?php

class CartController extends CartControllerCore
{
public function initContent()
	{
		
		if (Tools::getIsset('flx'))    $this->ajax=1;
			  
		$this->setTemplate(_PS_THEME_DIR_.'errors.tpl');
		if (!$this->ajax)
			parent::initContent();
	}
	
	public function postProcess()
	{
		

		
		
		
		if (Tools::getIsset('flx'))    $this->ajax=1;
		
       	
		// Update the cart ONLY if $this->cookies are available, in order to avoid ghost carts created by bots
		if ($this->context->cookie->exists() && !$this->errors && !($this->context->customer->isLogged() && !$this->isTokenValid()))
		{
			if (Tools::getIsset('add') || Tools::getIsset('update'))
				$this->processChangeProductInCart();
			else if (Tools::getIsset('delete'))
				$this->processDeleteProductInCart();
			else if (Tools::getIsset('changeAddressDelivery'))
				$this->processChangeProductAddressDelivery();
			else if (Tools::getIsset('allowSeperatedPackage'))
				$this->processAllowSeperatedPackage();
			else if (Tools::getIsset('duplicate'))
				$this->processDuplicateProduct();
			// Make redirection
			if (!$this->errors && !$this->ajax)
			{
				$queryString = Tools::safeOutput(Tools::getValue('query', null));
				if ($queryString && !Configuration::get('PS_CART_REDIRECT'))
					Tools::redirect('index.php?controller=search&search='.$queryString);

				// Redirect to previous page
				if (isset($_SERVER['HTTP_REFERER']))
				{
					preg_match('!http(s?)://(.*)/(.*)!', $_SERVER['HTTP_REFERER'], $regs);
					if (isset($regs[3]) && !Configuration::get('PS_CART_REDIRECT'))
						Tools::redirect($_SERVER['HTTP_REFERER']);
				}

				Tools::redirect('index.php?controller=order&'.(isset($this->id_product) ? 'ipa='.$this->id_product : ''));
			}
			else if (Tools::getIsset('flx'))
			{
			  header("HTTP/1.1 200 OK"); 
              exit();
			}

		}
		elseif (!$this->isTokenValid() && !Tools::getIsset('flx'))
		{
			Tools::redirect('index.php');
		}
        else if (Tools::getIsset('flx'))
		{
		  header( "HTTP/1.1 409 token" );
          exit();
		}
 		
	}

}

