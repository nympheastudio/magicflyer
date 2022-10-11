<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2016 silbersaiten
 * @version   1.1.1
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/classes/FormMakerCaptcha.php');

$id_form = Tools::getValue('id_form');

if ($id_form) {
    $c = new FormMakerCaptcha();
    
    $text = Tools::passwdGen(
        Configuration::getGlobalValue('FM_CAPTCHA_NUMBER_CHAR') ? Configuration::getGlobalValue('FM_CAPTCHA_NUMBER_CHAR') : 8,
        Configuration::getGlobalValue('FM_CAPTCHA_TYPE') ? Configuration::getGlobalValue('FM_CAPTCHA_TYPE') : 'ALPHANUMERIC'
    );

    $c->getCaptcha($text);
    
    $context = Context::getContext();
    
    $context->cookie->{'captchaText_'.$id_form} = $text;
}
