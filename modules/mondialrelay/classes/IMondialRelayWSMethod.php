<?php
/**
 * 2007-2018 PrestaShop
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once(dirname(__FILE__).'/../errorCode.php');
require_once(realpath(dirname(__FILE__).'/../mondialrelay.php'));

/*
 * This method allow to create any method object to dial more easyly with the Mondial Reelay WebService
 */
interface IMondialRelayWSMethod
{
    /*
     * Initiate the data needed to be send properly
     * Can manage a list of data for multiple request
     */
    public function init();
    
    /*
     * Send one or multiple request to the webservice
     */
    public function send();
    
    /*
     * Get the values with associated fields name
     */
    public function getFieldsList();
    
    /*
     * Get the result of one or multiple send request
     */
    public function getResult();
}
