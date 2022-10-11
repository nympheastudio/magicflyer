<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2018, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is controller for processing request to add a gift product to shopping cart
 */
class ElegantalFacebookShareProductGiftModuleFrontController extends ModuleFrontController
{

    public function display()
    {
        $this->module->addGiftToCart();
    }
}
