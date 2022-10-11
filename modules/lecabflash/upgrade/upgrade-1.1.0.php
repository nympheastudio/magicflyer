<?php
/**
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
 * @author    202 ecommerce <support@202-ecommerce.com>
 * @copyright 2009-2017 202 ecommerce SARL
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @param LecabFlash $module
 * @return bool
 */
function upgrade_module_1_1_0($module)
{
    # clear smarty cache to avoid error with new js variables
    Tools::clearSmartyCache();

    return true;
}