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
*  @version   3.0.0
*  @author    BM Services <contact@bm-services.com>
*  @copyright 2012-2016 Paybox
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.paybox.com/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Code for module installation
 */
class PayboxInstaller
{
    public function install($module)
    {
        $hooks = array('payment', 'paymentReturn', 'AdminOrder', 'updateOrderStatus', 'cancelProduct');
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $hooks[] = 'actionObjectOrderUpdateAfter';
            // [2.2.0]
            $hooks[] = 'actionAdminControllerSetMedia';
            $hooks[] = 'actionOrderSlipAdd';
        }
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $hooks[] = 'displayPaymentEU';
        }

        foreach ($hooks as $hook) {
            if (!$module->registerHook($hook)) {
                return false;
            }
        }

        if (!$this->installOrderState($module)) {
            return false;
        }
        if (!$this->installPayboxCard($module)) {
            return false;
        }

        $crypt = new PayboxEncrypt();
        $encryptedKeys = array(
            'PAYBOX_KEYTEST',
            'PAYBOX_PASS',
        );

        foreach ($module->getConfig()->getDefaults() as $name => $default) {
            if (in_array($name, $encryptedKeys)) {
                $default = $crypt->encrypt($default);
            }
            Configuration::updateValue($name, $default);
        }

        $this->migrateData();

        return true;
    }

    private function _copyOrderStateImage($orderStateId)
    {
        $src = dirname(dirname(__FILE__)).'/img/orderState.gif';
        $dst = dirname(dirname(__FILE__)).'/../../img/os/'.((int)$orderStateId).'.gif';
        return copy($src, $dst);
    }

    private function _isValidState($id)
    {
        if (empty($id)) {
            return false;
        }

        $value = Db::getInstance()->getValue('select 1 from `'._DB_PREFIX_.'order_state` WHERE id_order_state='.$id);
        return $value !== false;
    }

    public function installOrderState($module)
    {
        // Order table
        if (!Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'paybox_order` (
            `id_order` int(10) unsigned NOT NULL,
            `id_transaction` varchar(255) NOT NULL,
            `num_appel` varchar(255) NOT NULL,
            `ref_abo` varchar(255) NOT NULL,
            `payment_status` varchar(255) NOT NULL,
            `amount` int(10) unsigned NOT NULL,
            `initial_amount` int(10) unsigned NOT NULL,
            `currency` int(10) unsigned NOT NULL,
            `payment_by` varchar(255) NOT NULL,
            `method` varchar(30) NULL,
            `carte` varchar(255) NOT NULL,
            `carte_num` varchar(30) NOT NULL,
            `pays` varchar(255) NOT NULL,
            `ip` varchar(255) NOT NULL,
            `secure` varchar(255) NOT NULL,
            `date` varchar(255) NOT NULL,
            `refund_amount` int(10) NOT NULL,
            PRIMARY KEY (`id_order`))
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')
        ) {
            return false;
        }

        // Cart table
        if (!Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'paybox_cart_locker` (
            `id_cart` int(10) unsigned NOT NULL,
            `date_add` datetime NULL,
            PRIMARY KEY (`id_cart`))
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')
        ) {
            return false;
        }

        // Check if column 'initial_amount' exists and create it if not
        if (!$this->_sqlColumnExists(_DB_PREFIX_.'paybox_order', 'initial_amount')) {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'paybox_order` ADD `initial_amount` int(10) unsigned NOT NULL');
        }

        if (!Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'paybox_recurring` (
            `id_paybox_recurring` INT( 10 ) NOT NULL AUTO_INCREMENT ,
            `id_order` INT( 10 ) NOT NULL ,
            `status` VARCHAR( 255 ) NOT NULL ,
            `number_term` int(1) NOT NULL ,
            `amount_paid` INT( 15 ) NOT NULL ,
            PRIMARY KEY (  `id_paybox_recurring` ))
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')
        ) {
            return false;
        }

                    
        if (!$this->_isValidState(Configuration::get('PAYBOX_ID_ORDER_STATE_NX')))
        {
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                if (strtolower($language['iso_code']) == 'fr') {
                    $orderState->name[$language['id_lang']] = 'Payé partiellement via Paybox';
                } else {
                    $orderState->name[$language['id_lang']] = 'Partially paid through Paybox';
                }
            }
            $orderState->send_email = false;
            $orderState->color = '#BBDDEE';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->invoice = true;
            if ($orderState->add()) {
                $this->_copyOrderStateImage($orderState->id);
            }
            Configuration::updateValue('PAYBOX_ID_ORDER_STATE_NX', (int)$orderState->id);
        }
        Configuration::updateValue('PAYBOX_MIDDLE_STATE_NX', Configuration::get('PAYBOX_ID_ORDER_STATE_NX'));
                
        if (!$this->_isValidState(Configuration::get('PAYBOX_RECEIVE_PAY'))) {
            // Débit à l'expédition
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                if (strtolower($language['iso_code']) == 'fr') {
                    $orderState->name[$language['id_lang']] = 'Débit à l\'expédition';
                    $orderState->template[$language['id_lang']] = 'payment';
                } else {
                    $orderState->name[$language['id_lang']] = 'Receive and Pay';
                    $orderState->template[$language['id_lang']] = 'payment';
                }
            }
            $orderState->send_email = true;
            $orderState->color = '#FF70BD';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->invoice = true;
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $orderState->paid = true;
            }

            if ($orderState->add()) {
                $this->_copyOrderStateImage($orderState->id);
            }
            Configuration::updateValue('PAYBOX_RECEIVE_PAY', (int)$orderState->id);
        }

        if (!$this->_isValidState(Configuration::get('PAYBOX_STATE_MIN_CAPTURE'))) {
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                if (strtolower($language['iso_code']) == 'fr') {
                    $orderState->name[$language['id_lang']] = 'Encaissé partiellement';
                } else {
                    $orderState->name[$language['id_lang']] = 'Partially cashed';
                }
            }
            $orderState->send_email = false;
            $orderState->color = '#F2BFFF';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->invoice = true;
            if ($orderState->add()) {
                $this->_copyOrderStateImage($orderState->id);
            }
            Configuration::updateValue('PAYBOX_STATE_MIN_CAPTURE', (int)$orderState->id);
        }

        if (!$this->_isValidState(Configuration::get('PAYBOX_WEB_CASH_VALIDATION'))) {
            // Capture manuelle
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                if (strtolower($language['iso_code']) == 'fr') {
                    $orderState->name[$language['id_lang']] = 'Capture manuelle du paiement';
                } else {
                    $orderState->name[$language['id_lang']] = 'Manual capture of payment';
                }
            }
            $orderState->send_email = false;
            $orderState->color = '#DDEEFF';
            $orderState->hidden = true;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->invoice = true;
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $orderState->paid = true;
            }

            if ($orderState->add()) {
                $this->_copyOrderStateImage($orderState->id);
            }
            Configuration::updateValue('PAYBOX_WEB_CASH_VALIDATION', (int)$orderState->id);
        }
/*
        if (!$this->_isValidState(Configuration::get('PAYBOX_KWIXO'))) {
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                if (strtolower($language['iso_code']) == 'fr') {
                    $orderState->name[$language['id_lang']] = 'Payé via Kwixo';
                    $orderState->template[$language['id_lang']] = 'payment';
                } else {
                    $orderState->name[$language['id_lang']] = 'Paid with Kwixo';
                    $orderState->template[$language['id_lang']] = 'payment';
                }
            }
            $orderState->send_email = true;
            $orderState->color = '#e4ffb6';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->invoice = true;
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $orderState->paid = true;
            }
            if ($orderState->add()) {
                $this->_copyOrderStateImage($orderState->id);
            }
            Configuration::updateValue('PAYBOX_KWIXO', (int)$orderState->id);
        }
*/
        return true;
    }

    public function installPayboxCard($module)
    {
        $db = new PayboxDb();

        $sql = 'CREATE TABLE IF NOT EXISTS `%spaybox_card` (
            `id_card` INT(2) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `type_payment` VARCHAR(12) NOT NULL ,
            `type_card` VARCHAR(30) NOT NULL,
            `label` VARCHAR(30) NOT NULL,
            `active` int(1) NULL,
            `debit_expedition` int(1) NULL,
            `debit_immediat` int(1) NULL,
            `debit_differe` int(1) NULL,
            `remboursement` int(1) NULL,
            `3ds` int(1) null
        ) ENGINE = %s ;';
        $result = $db->execute(sprintf($sql, _DB_PREFIX_, _MYSQL_ENGINE_));

        if (!$result) {
            return false;
        }

        $cards = array(
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'CB',
                'label' => 'Carte CB',
                'active' => 1,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 1,
                'remboursement' => 1,
                '3ds' => 1,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'VISA',
                'label' => 'Carte Visa',
                'active' => 1,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 1,
                'remboursement' => 1,
                '3ds' => 1,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'EUROCARD_MASTERCARD',
                'label' => 'Carte Mastercard',
                'active' => 1,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 1,
                'remboursement' => 1,
                '3ds' => 1,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'E_CARD',
                'label' => 'e-Carte Bleue',
                'active' => 1,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 1,
                'remboursement' => 0,
                '3ds' => 1,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'MAESTRO',
                'label' => 'Carte Maestro',
                'active' => 0,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 1,
                'remboursement' => 1,
                '3ds' => 2,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'AMEX',
                'label' => 'Carte American Express',
                'active' => 0,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 1,
                'remboursement' => 1,
                '3ds' => 1,
            ),
            array(
                'type_payment' => 'PAYPAL',
                'type_card' => 'PAYPAL',
                'label' => 'Paypal',
                'active' => 0,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 1,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'LEETCHI',
                'type_card' => 'LEETCHI',
                'label' => 'Leetchi',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'COFINOGA',
                'label' => 'Carte Cofinoga',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 1,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'AURORE',
                'label' => 'Carte Aurore',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'CREDIT',
                'type_card' => 'UNEURO',
                'label' => '1euro.com',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'DINERS',
                'label' => 'Diners',
                'active' => 0,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 1,
                'remboursement' => 1,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'JCB',
                'label' => 'JCB',
                'active' => 0,
                'debit_expedition' => 1,
                'debit_immediat' => 1,
                'debit_differe' => 1,
                'remboursement' => 1,
                '3ds' => 1,
            ),
            array(
                'type_payment' => 'CARTE',
                'type_card' => 'BCMC',
                'label' => 'Bancontact/Mistercash',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 2,
            ),
            array(
                'type_payment' => 'PREPAYEE',
                'type_card' => 'IDEAL',
                'label' => 'iDEAL',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'PAYBUTTONS',
                'type_card' => 'PAYBUTTING',
                'label' => 'Paybuttons ING',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'PREPAYEE',
                'type_card' => 'PSC',
                'label' => 'Paysafecard',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'PREPAYEE',
                'type_card' => 'CSHTKT',
                'label' => 'CashTicket',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'WALLET',
                'type_card' => 'PAYLIB',
                'label' => 'Paylib',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
/*            
            array(
                'type_payment' => 'KWIXO',
                'type_card' => 'STANDARD',
                'label' => 'Kwixo standard',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'KWIXO',
                'type_card' => '1XRNP',
                'label' => 'Kwixo à réception',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
            array(
                'type_payment' => 'KWIXO',
                'type_card' => 'CREDIT',
                'label' => 'Kwixo credit',
                'active' => 0,
                'debit_expedition' => 0,
                'debit_immediat' => 1,
                'debit_differe' => 0,
                'remboursement' => 0,
                '3ds' => 0,
            ),
*/
        );

        foreach ($cards as $card) {
            if (!$db->insert('paybox_card', $card)) {
                return false;
            }
        }

        return true;
    }

    public function uninstall($module)
    {
        $hooks = array('payment', 'paymentReturn', 'myAccountBlock', 'customerAccount', 'AdminOrder', 'header', 'updateOrderStatus', 'cancelProduct');
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $hooks[] = 'actionObjectOrderUpdateAfter';
            // [2.2.0]
            $hooks[] = 'actionAdminControllerSetMedia';
            $hooks[] = 'actionOrderSlipAdd';
        }
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $hooks[] = 'displayPaymentEU';
        }
        foreach ($hooks as $hook) {
            if (!$module->unregisterHook($hook)) {
                return false;
            }
        }

        // $idOrderState = Configuration::get('PAYBOX_ID_ORDER_STATE_NX');
        // if($idOrderState != '') {
        //     Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_state` WHERE id_order_state='.$idOrderState);
        //     Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_state_lang` WHERE id_order_state='.$idOrderState);
        //     @unlink(_PS_IMG_DIR_.'os/'.$idOrderState.'.gif');
        // }

        // $idOrderState = Configuration::get('PAYBOX_RECEIVE_PAY');
        // if($idOrderState != '') {
        //     Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_state` WHERE id_order_state='.$idOrderState);
        //     Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_state_lang` WHERE id_order_state='.$idOrderState);
        //     @unlink(_PS_IMG_DIR_.'os/'.$idOrderState.'.gif');
        // }

        // $idOrderState = Configuration::get('PAYBOX_STATE_MIN_CAPTURE');
        // if($idOrderState != '') {
        //     Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_state` WHERE id_order_state='.$idOrderState);
        //     Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_state_lang` WHERE id_order_state='.$idOrderState);
        //     @unlink(_PS_IMG_DIR_.'os/'.$idOrderState.'.gif');
        // }

        // $idOrderState = Configuration::get('PAYBOX_KWIXO');
        // if($idOrderState != '') {
        //     Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_state` WHERE id_order_state='.$idOrderState);
        //     Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_state_lang` WHERE id_order_state='.$idOrderState);
        //     @unlink(_PS_IMG_DIR_.'os/'.$idOrderState.'.gif');
        // }

        // Keep the following tables to not loose payment information and configuration
        // - paybox_recurring
        // - paybox_order
        $tables = array('paybox_card', 'paybox');
        foreach ($tables as $table) {
            $sql = sprintf('DROP TABLE IF EXISTS `%s%s`;', _DB_PREFIX_, $table);
            Db::getInstance()->execute($sql);
        }

        foreach ($module->getConfig()->getDefaults() as $name => $default) {
            Configuration::deleteByName($name);
        }

        /* Delete all configurations */
        // Configuration::deleteByName('PAYBOX_ID_ORDER_STATE_NX');
        // Configuration::deleteByName('PAYBOX_RECEIVE_PAY');
        // Configuration::deleteByName('PAYBOX_STATE_MIN_CAPTURE');

        return true;
    }

    public function _sqlColumnExists($table, $column)
    {
        $result = Db::getInstance()->getRow('SELECT *
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = "'._DB_NAME_.'" AND TABLE_NAME = "'.$table.'" AND COLUMN_NAME = "'.$column.'"');
        return ($result !== false);
    }

    private function migrateData()
    {
        $sql = 'SHOW TABLES LIKE "%ETRANS_order" ';
        $result = DB::getInstance()->executeS($sql);

        if (false != $result && 0 < count($result)) {
            $sql = sprintf('INSERT IGNORE INTO %spaybox_order (`id_order`, `id_transaction`, `num_appel`, `ref_abo`, `payment_status`, `amount`, `initial_amount`, `currency`, `payment_by`, `carte`, `pays`, `ip`, `secure`, `date`, `refund_amount`) 
        	    SELECT t.`id_order`, t.`id_transaction`, t.`num_appel`, t.`ref_abo`, t.`payment_status`, t.`amount`, t.`amount`, t.`currency`, t.`payment_by`, t.`carte`, t.`pays`, t.`ip`, t.`secure`, t.`date`, t.`refund_amount` FROM %sETRANS_order t', _DB_PREFIX_, _DB_PREFIX_);
            DB::getInstance()->execute($sql);

            $sql = sprintf('DROP TABLE IF EXISTS `%sETRANS_order`;', _DB_PREFIX_);
            Db::getInstance()->execute($sql);
        }
    }
}
