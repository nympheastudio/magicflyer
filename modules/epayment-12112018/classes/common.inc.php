<?php
/**
* Paybox by Verifone PrestaShop Module
*
* Feel free to contact Paybox by Verifone at support@paybox.com for any
* question.
*
* LICENSE: This source file is subject to the version 3.0 of the Open
* Software License (OSL-3.0) that is available through the world-wide-web
* at the following URI: http://opensource.org/licenses/OSL-3.0. If
* you did not receive a copy of the OSL-3.0 license and are unable 
* to obtain it through the web, please send a note to
* support@paybox.com so we can mail you a copy immediately.
*
*  @category  Module / payments_gateways
*  @version   2.0.0
*  @author    BM Services <contact@bm-services.com>
*  @copyright 2012-2016 Paybox
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.paybox.com/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Base includes
 */
$dir = dirname(__FILE__).'/';
require_once($dir.'PayboxAbstract.php');
require_once($dir.'PayboxConfig.php');
require_once($dir.'PayboxController.php');
require_once($dir.'PayboxCurl.php');
require_once($dir.'PayboxEncrypt.php');
require_once($dir.'PayboxHelper.php');
require_once($dir.'PayboxInstaller.php');
require_once($dir.'PayboxKwixo.php');
require_once($dir.'PayboxDb.php');
