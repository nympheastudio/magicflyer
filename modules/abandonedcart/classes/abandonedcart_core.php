<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 *
 *
 * Description
 *
 * Updates quantity in the cart
 */

class AbandonedCartCore extends Module
{
    const DISCOUNT_EMAIL = 0;
    const NON_DISCOUNT_EMAIL = 1;
    const INCENTIVE_DISCOUNT_TYPE = 0;
    const INCENTIVE_REMINDER_TYPE = 1;
    const DISCOUNT_PERCENTAGE = 0;
    const COUPON_VALIDITY_IN_DAYS = 1;
    const DISCOUNT_FIXED = 1;
    const INCENTIVE_ENABLE = 1;
    const INCENTIVE_DISABLE = 0;
    const DEFAULT_TEMPLATE_NAME = 'Default Discount Template';
    const DEFAULT_TEMPLATE_SUBJECT = 'Redeem your coupon for items left in your shopping cart!';
    const DEFAULT_REMINDER_TEMPLATE_NAME = 'Default Non-discount Template';
    const DEFAULT_REMINDER_TEMPLATE_SUBJECT = 'You have items left in your shopping cart!';
    const ABANDON_TABLE_NAME = 'velsof_abd_cart';
    const TEMPLATE_TABLE_NAME = 'velsof_abd_email_templates';
    const ABD_TRACK_CUSTOMERS_TABLE_NAME = 'velsof_abd_track_customers';
    const TEMPLATE_CONTENT_TABLE_NAME = 'velsof_abd_email_templates_content';
    const INCENTIVE_TABLE_NAME = 'velsof_abd_incentive_list';
    const INCENTIVE_MAPPING_TABLE_NAME = 'velsof_abd_incentive_cart_mapping';
    const DATE_FORMAT = 'Y-m-d';
    const ITEM_PER_PAGE = 10;
    const REMINDER_SENT = 1;
    const REMINDER_NOT_SENT = 0;

    /*
     * use "left" to display pagination on left side
     */
    const PAGINATION_ALIGN = 'right';

    /*
     * Name of templates used for sending emails
     */
    const REMINDER_TEMPLATE_NAME = 'abd_reminder_email';
    const DISCOUNT_TEMPLATE_NAME = 'abd_discount_email';
    const COUPON_EXPIRE_DFORMAT = 'F j, Y, g:i a';

    public function __construct()
    {
        parent::__construct();

        if (!Configuration::get('VELSOF_ABANDONEDCART')) {
            $this->warning = $this->l('No name provided', 'abandonedcart_core');
        }
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('displayOrderConfirmation') ||
            !$this->registerHook('actionCartSave') ||
            !$this->registerHook('displayHeader')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('VELSOF_ABANDONEDCART') ||
            !$this->unregisterHook('displayOrderConfirmation') ||
            !$this->unregisterHook('actionCartSave') ||
            !$this->unregisterHook('displayHeader')
        ) {
            return false;
        }

        return true;
    }

    protected function abdKeyGenerator($length = 32)
    {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= chr(mt_rand(33, 126));
        }
        return md5($random);
    }

    /*
     * Function modified by RS on 06-Sept-2017 to check if the column exist in the table before adding the same.
     * Adding a new column named `cart_total` in the `velsof_abd_cart` table to store the cart total at the time of entry in our table.
     */
    protected function installModel()
    {
        if ($this->checkDbExists()) {
            $check_column_exist = 'SELECT * 
                FROM information_schema.COLUMNS 
                WHERE 
                    TABLE_SCHEMA = "'._DB_NAME_.'" 
                AND TABLE_NAME = "'. _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME .'" 
                AND COLUMN_NAME = "cart_template"';
            $column_result = Db::getInstance()->getRow($check_column_exist);
            if (!(is_array($column_result) && count($column_result) > 0)) {
                $update_table = 'ALTER TABLE `' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME . '`
                            ADD cart_template int(2) NOT NULL';

                Db::getInstance()->execute($update_table);

                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME . '`';
                $row = Db::getInstance()->ExecuteS($sql);

                foreach ($row as $r) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME . '` SET cart_template=1
                        WHERE id_template_content=' . (int) $r['id_template_content'];
                    Db::getInstance()->execute($sql);
                }
            }

            $check_column_exist = 'SELECT * 
                FROM information_schema.COLUMNS 
                WHERE 
                    TABLE_SCHEMA = "'._DB_NAME_.'" 
                AND TABLE_NAME = "'. _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME .'" 
                AND COLUMN_NAME = "min_cart_value_for_mails"';
            $column_result = Db::getInstance()->getRow($check_column_exist);
            if (!(is_array($column_result) && count($column_result) > 0)) {
                $update_table = 'ALTER TABLE ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . '
                    ADD min_cart_value_for_mails DECIMAL(10,4) NOT NULL DEFAULT 0';

                Db::getInstance()->execute($update_table);

                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . '`';
                $row = Db::getInstance()->ExecuteS($sql);

                foreach ($row as $r) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . '` SET min_cart_value_for_mails=0
                        WHERE id_incentive=' . (int) $r['id_incentive'];
                    Db::getInstance()->execute($sql);
                }
            }
            
            $check_column_exist = 'SELECT * 
                FROM information_schema.COLUMNS 
                WHERE 
                    TABLE_SCHEMA = "'._DB_NAME_.'" 
                AND TABLE_NAME = "'. _DB_PREFIX_ . self::ABANDON_TABLE_NAME .'" 
                AND COLUMN_NAME = "cart_total"';
            $column_result = Db::getInstance()->getRow($check_column_exist);
            if (!(is_array($column_result) && count($column_result) > 0)) {
                $update_table = 'ALTER TABLE ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . '
                    ADD cart_total decimal(20,6) NOT NULL DEFAULT "0.000000" AFTER shows';
                if (Db::getInstance()->execute($update_table)) {
                    Configuration::updateGlobalValue('VELSOF_ABD_CART_TOTAL_ADDED', 1);
                }
            }
        }

        $create_table = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . '` (
			`id_template` int(10) NOT NULL auto_increment,
			`type` int(1) NOT NULL DEFAULT 0,
			`name` varchar(255) NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
			PRIMARY KEY (`id_template`)
			) CHARACTER SET utf8 COLLATE utf8_general_ci';

        Db::getInstance()->execute($create_table);

        $create_table = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME . '` (
			`id_template_content` int(10) NOT NULL auto_increment,
			`id_template` int(10) NOT NULL,
			`id_lang` int(10) NOT NULL,
			`iso_code` char(4) NOT NULL,
			`subject` varchar(255) NOT NULL,
			`body` Text NULL,
			`cart_template` int(2) NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
			PRIMARY KEY (`id_template_content`),
			FOREIGN KEY (id_template) references ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . '(id_template) 
                        ON DELETE CASCADE,
			INDEX (  `id_lang`, `id_template` )
			) CHARACTER SET utf8 COLLATE utf8_general_ci';

        Db::getInstance()->execute($create_table);

        $languages = Language::getLanguages(true);

        $installed_time = date('Y-m-d H:i:s', time());

        $check_template_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'Select count(*) as total from ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
        );

        if ($check_template_data['total'] == 0 || Tools::isEmpty($check_template_data['total'])) {
            $i = 1;
            while ($i <= 10) {
                $discount_tem_sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                        . ' (type, name, date_add, date_upd) values('
                        . (int) self::DISCOUNT_EMAIL . ', "'
                        . pSQL(Tools::htmlentitiesUTF8(self::DEFAULT_TEMPLATE_NAME) . ' ' . $i) . '", "'
                        . pSQL($installed_time) . '", "' . pSQL($installed_time) . '")';

                if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($discount_tem_sql)) {
                    $id_template = Db::getInstance(_PS_USE_SQL_SLAVE_)->Insert_ID();

                    foreach ($languages as $lang) {
                        $sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME . ' (id_template, 
                                id_lang, iso_code, subject, body, cart_template, date_add, date_upd) values('
                                . (int) $id_template . ', '
                                . (int) $lang['id_lang'] . ', "' . pSQL($lang['iso_code']) . '", "'
                                . pSQL(Tools::htmlentitiesUTF8(self::DEFAULT_TEMPLATE_SUBJECT)) . '","'
                                . pSQL(Tools::htmlentitiesUTF8($this->getDefaultEmailTemplate($i))) . '",'
                                . (int) $i . ',"' . pSQL($installed_time) . '", "' . pSQL($installed_time) . '")';

                        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
                    }
                }

                $reminder_tem_sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' 
					(type, name, date_add, date_upd) values('
                        . (int) self::NON_DISCOUNT_EMAIL . ', "'
                        . pSQL(Tools::htmlentitiesUTF8(self::DEFAULT_REMINDER_TEMPLATE_NAME) . ' ' . $i)
                        . '", "' . pSQL($installed_time) . '", "' . pSQL($installed_time) . '")';

                if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($reminder_tem_sql)) {
                    $id_template = Db::getInstance(_PS_USE_SQL_SLAVE_)->Insert_ID();

                    foreach ($languages as $lang) {
                        $sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME .
                            ' (id_template, id_lang,
                            iso_code, subject, body, cart_template,date_add, date_upd) values('
                            . (int) $id_template . ', '
                            . (int) $lang['id_lang'] . ', "' . pSQL($lang['iso_code']) . '", "'
                            . pSQL(Tools::htmlentitiesUTF8(self::DEFAULT_REMINDER_TEMPLATE_SUBJECT)) . '","'
                            . pSQL(Tools::htmlentitiesUTF8($this->getDefaultEmailReminder($i))) . '",'
                            . (int) $i . ',"' . pSQL($installed_time) . '", "' . pSQL($installed_time) . '")';

                        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
                    }
                }
                $i++;
            }
        } else {
            $check_template_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
                'Select id_template from '
                . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' ORDER BY id_template DESC LIMIT 1'
            );

            if ($check_template_data[0]['id_template'] < 100000) {
                $i = 2;
                while ($i <= 10) {
                    $discount_tem_sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                            . ' (type, name, date_add, date_upd) values('
                            . (int) self::DISCOUNT_EMAIL . ', "'
                            . pSQL(Tools::htmlentitiesUTF8(self::DEFAULT_TEMPLATE_NAME) . ' ' . $i) . '", "'
                            . pSQL($installed_time) . '", "' . pSQL($installed_time) . '")';

                    if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($discount_tem_sql)) {
                        $id_template = Db::getInstance(_PS_USE_SQL_SLAVE_)->Insert_ID();

                        foreach ($languages as $lang) {
                            $sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME . ' (id_template, 
                                    id_lang, iso_code, subject, body, cart_template, date_add, date_upd) values('
                                    . (int) $id_template . ', '
                                    . (int) $lang['id_lang'] . ', "' . pSQL($lang['iso_code']) . '", "'
                                    . pSQL(Tools::htmlentitiesUTF8(self::DEFAULT_TEMPLATE_SUBJECT)) . '","'
                                    . pSQL(Tools::htmlentitiesUTF8($this->getDefaultEmailTemplate($i))) . '",'
                                    . (int) $i . ',"' . pSQL($installed_time) . '", "' . pSQL($installed_time) . '")';

                            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
                        }
                    }

                    $reminder_tem_sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' 
                            (type, name, date_add, date_upd) values('
                            . (int) self::NON_DISCOUNT_EMAIL . ', "'
                            . pSQL(Tools::htmlentitiesUTF8(self::DEFAULT_REMINDER_TEMPLATE_NAME) . ' ' . $i) . '", "'
                            . pSQL($installed_time) . '", "' . pSQL($installed_time) . '")';

                    if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($reminder_tem_sql)) {
                        $id_template = Db::getInstance(_PS_USE_SQL_SLAVE_)->Insert_ID();

                        foreach ($languages as $lang) {
                            $sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME .
                                    ' (id_template, id_lang,
                                    iso_code, subject, body, cart_template,date_add, date_upd) values('
                                    . (int) $id_template . ', '
                                    . (int) $lang['id_lang'] . ', "' . pSQL($lang['iso_code']) . '", "'
                                    . pSQL(Tools::htmlentitiesUTF8(self::DEFAULT_REMINDER_TEMPLATE_SUBJECT)) . '","'
                                    . pSQL(Tools::htmlentitiesUTF8($this->getDefaultEmailReminder($i))) .
                                    '",' . (int) $i . ',"'
                                    . pSQL($installed_time) . '", "' . pSQL($installed_time) . '")';
                            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
                        }
                    }
                    $i++;
                }
            }
        }

        $incentive_table = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . ' (
			id_incentive int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			id_template int(11) NULL,
			id_currency int(2) NOT NULL,
			incentive_type enum("1","0") NULL DEFAULT "0",
			discount_type int(1) DEFAULT ' . $this->getDefaultDiscountType() . ',
			discount_value DECIMAL(10,4) DEFAULT 0,
			min_cart_value DECIMAL(10,4) NOT NULL DEFAULT 0,
			min_cart_value_for_mails DECIMAL(10,4) NOT NULL DEFAULT 0,
			coupon_validity int(5) NOT NULL,
			status int(1) NOT NULL DEFAULT ' . $this->getDefaultIncentiveStatus() . ', 
			has_free_shipping int(1) NOT NULL DEFAULT 0,
			delay_days int(2) NOT NULL DEFAULT 0,
			delay_hrs int(2) NOT NULL DEFAULT 0,
			date_add datetime NOT NULL,
			date_upd datetime NOT NULL,
			INDEX (`id_template` ),
			FOREIGN KEY (id_template) references ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                . '(id_template) ON DELETE SET NULL
			) CHARACTER SET utf8 COLLATE utf8_general_ci';

        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($incentive_table);

        if (!Configuration::get('INSERT_DEFAULT_REMINDERS')) {
            $query = 'INSERT INTO ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . ' 
				(incentive_type, id_template, id_currency, discount_type, 
				discount_value, min_cart_value, min_cart_value_for_mails, coupon_validity, status, 
				has_free_shipping, delay_days, delay_hrs, date_add, date_upd) values
				("1", 2, ' . (int) $this->context->currency->id . ', 0, 0, 0, 0, 1, 1, 0, 0, 10, now(), now()),
				("0", 1, ' . (int) $this->context->currency->id . ', 0, 5, 0, 0, 2, 1, 0, 1, 12, now(), now()),
				("0", 1, ' . (int) $this->context->currency->id . ', 0, 5, 0, 0, 2, 1, 0, 3, 0, now(), now())';

            Db::getInstance()->execute($query);
            Configuration::updateGlobalValue('INSERT_DEFAULT_REMINDERS', 1);
        }
        $abandon_cart_table = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' (
			id_abandon int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			id_cart int(10) unsigned NOT NULL UNIQUE,
			id_shop int(11) DEFAULT 0,
			id_lang int(11) DEFAULT 0,
			is_converted enum("1","0") NULL DEFAULT "0",
			id_customer int(10) unsigned NULL,
			auto_email enum("1","0") NULL DEFAULT "1",
			is_guest enum("1","0"),
			shows enum("1","0") NULL DEFAULT "1",
            cart_total decimal(20,6) NOT NULL DEFAULT "0.000000",
			reminder_sent enum("1","0") NULL DEFAULT "0",
			date_add datetime NULL,
			date_upd datetime NULL,
			INDEX (`id_cart`, `id_customer`),
			FOREIGN KEY (id_cart) references ' . _DB_PREFIX_ . 'cart (id_cart) ON DELETE CASCADE			
			) CHARACTER SET utf8 COLLATE utf8_general_ci';

        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($abandon_cart_table);

        $incentive_mapping_table = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ .
            self::INCENTIVE_MAPPING_TABLE_NAME . ' (
			id_map int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			id_cart int(11) unsigned NOT NULL,
			id_customer int(11) unsigned NULL,
			id_incentive int(11) NOT NULL,
			quantity int(11) NOT NULL DEFAULT -1,
			date_add datetime NULL,
			INDEX (`id_cart`, `id_customer`)
			) CHARACTER SET utf8 COLLATE utf8_general_ci';

        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($incentive_mapping_table);

        $create_table = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::ABD_TRACK_CUSTOMERS_TABLE_NAME . '` (
			`id_track` int(10) NOT NULL auto_increment,
			`id_cart` int(11) unsigned NOT NULL,
			`firstname` varchar(255),
			`lastname` varchar(255),
			`email` varchar(255) NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NOT NULL,
			PRIMARY KEY (`id_track`)
			) CHARACTER SET utf8 COLLATE utf8_general_ci';

        Db::getInstance()->execute($create_table);

        if (!Configuration::get('VELSOF_ABD_SECURE_KEY')) {
            Configuration::updateValue('VELSOF_ABD_SECURE_KEY', $this->abdKeyGenerator());
        }
    }

    public function checkDbExists()
    {
        if (Configuration::get('INSERT_DEFAULT_REMINDERS') && !Configuration::get('VELSOF_TABLE_MODIFIED')) {
            Configuration::updateGlobalValue('VELSOF_TABLE_MODIFIED', 1);
            return true;
        } else {
            return false;
        }
    }

    protected function copyfolder($source, $destination)
    {
        $directory = opendir($source);
        mkdir($destination);
        while (($file = readdir($directory)) != false) {
            Tools::copy($source . '/' . $file, $destination . '/' . $file);
        }
        closedir($directory);
    }

    protected function getIncentivesHavingLessDelay($id_incentive, $incentive_delay = 0)
    {
        $query = 'Select * from ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME .
                ' WHERE status = ' . (int) self::INCENTIVE_ENABLE.' and id_incentive != '. (int)$id_incentive;

        $discounts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        $final_incentives = array();
        if (count($discounts) > 0) {
            foreach ($discounts as $discount) {
                $delay_in_hrs = (24 * (int) $discount['delay_days']) + (int) $discount['delay_hrs'];
                if ($incentive_delay > $delay_in_hrs) {
                    $final_incentives[] = $discount['id_incentive'];
                }
            }
        }
        unset($discounts);
        unset($query);
        unset($delay_in_hrs);
        return $final_incentives;
    }
    
    protected function getEmailTypeArray()
    {
        return array(
            self::DISCOUNT_EMAIL => $this->l('Discount Email', 'abandonedcart_core'),
            self::NON_DISCOUNT_EMAIL => $this->l('Non-Discount Email', 'abandonedcart_core')
        );
    }

    protected function getReminderTypeText()
    {
        return array(
            self::DISCOUNT_EMAIL => $this->l('Discount', 'abandonedcart_core'),
            self::NON_DISCOUNT_EMAIL => $this->l('Non-Discount', 'abandonedcart_core')
        );
    }

    protected function getDiscountTypeArray()
    {
        return array(
            self::DISCOUNT_PERCENTAGE => $this->l('Percentage', 'abandonedcart_core'),
            self::DISCOUNT_FIXED => $this->l('Fixed', 'abandonedcart_core')
        );
    }

    protected function getIncentiveStatuses()
    {
        return array(
            self::INCENTIVE_ENABLE => $this->l('Enabled', 'abandonedcart_core'),
            self::INCENTIVE_DISABLE => $this->l('Disabled', 'abandonedcart_core')
        );
    }

    protected function getDefaultIncentiveStatus()
    {
        return self::INCENTIVE_ENABLE;
    }

    protected function getDefaultEmailType()
    {
        return self::DISCOUNT_EMAIL;
    }

    protected function getDefaultDiscountType()
    {
        return self::DISCOUNT_PERCENTAGE;
    }

    protected function getDefaultCouponValidity()
    {
        return self::COUPON_VALIDITY_IN_DAYS;
    }

    protected function getFormattedDate($date_str = '')
    {
        return date(self::DATE_FORMAT, strtotime($date_str));
    }

    protected function getTemplateDir()
    {
        $default_lang = Configuration::get('VELSOF_ABANDONED_CART_DEFAULT_TEMPLATE_LANG');
        return _PS_MODULE_DIR_ . 'abandonedcart/mails/' . $default_lang . '/';
    }

    protected function getPluginShopUrl()
    {
        return 'https://addons.prestashop.com/en/write-to-developper?id_product=18297';
    }

    protected function getDefaultEmailTemplate($i)
    {
        if ($i == 1) {
            return '<p style="text-align:left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;">
			<span style="color: #777;"> We are getting in touch since we saw that you
			had the following product(s) left in your shopping cart but for some reason you did not complete the order.
			</span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;">
			<span style="color: #777;"> We do not know why you decided not to purchase this time.
			We are hoping you have not faced any hiccups during the process.<br /><br />
			We are offering you a special discount code -<strong> {discount_code}</strong> - which
			gives you <strong> {discount_value} </strong>OFF. The code applies after you spend&nbsp;<strong>
			{total_amount}</strong>.
			This promotion is just for you and expires on <strong> {date_end}</strong>.<br /><br />
			Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 2) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in your 
			shopping cart but for some reason you did not complete the order. </span> 
			</span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We do not know why you decided not to purchase this time. We are hoping you have 
			not faced any hiccups during the process.
			<br /><br /> We are offering you a special discount code -<strong> {discount_code}</strong> - which 
			gives you <strong> {discount_value} </strong>OFF. 
			The code applies after you spend&nbsp;<strong>{total_amount}</strong>. 
			This promotion is just for you and expires on <strong> {date_end}</strong>.<br /><br /> Kind Regards,<br /><br /> 
			{shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 3) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<p style="text-align: left; color: blue; font-size: 28px;">
                        <span style="color: rgba(95,158,160,0.66);"><strong>HURRY!!
			&nbsp;<span style="text-align: left; color: rgba(95,158,160,0.66); font-size: 20px;">BEFORE THESE ITEMS SELLS OUT
			</span></strong></span></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in your 
			shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We do not know why you decided not to purchase this time. We are hoping you have not 
			faced any hiccups during the process.<br />
			<br /> We are offering you a special discount code -<strong> {discount_code}</strong> - which gives you <strong> 
			{discount_value} </strong>OFF. 
			The code applies after you spend&nbsp;<strong>{total_amount}</strong>. This promotion is just for you and expires 
			on <strong> {date_end}</strong>.<br /><br /> Kind Regards,<br /><br /> {shop_name}<br />
                        {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 4) {
            return '<p style="text-align: center; font-size: 30px;"><span style="color: #45a245;">
			<strong>Wait A Second!!</strong></span></p>
			<p style="text-align: center; font-size: 20px;"><span style="color: #f5a623;"><strong>
			Items you added to your cart 
			are almost sold.</strong></span></p>
			<p style="text-align: left;"><strong>Hi {firstname} {lastname}</strong></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: 
			Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the 
			following product(s) left in your 
			shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We do not know why you decided not to purchase this time. 
			We are hoping you have not 
			faced any hiccups during the process.<br />
			<br /> We are offering you a special discount code -<strong> {discount_code}</strong> - which gives you 
			<strong> 
			{discount_value} </strong>OFF. The code applies after you spend&nbsp;<strong>{total_amount}</strong>. 
			This promotion 
			is just for you and expires on <strong> {date_end}</strong>.<br /><br /> Kind Regards,<br /><br /> 
			{shop_name}<br /> {shop_url_link} 
			</span> 
			</span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 5) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> <span style="color: #777;"> 
			We are getting in touch since we saw that you had the following product(s) left in your shopping cart but for some 
			reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> 
			We do not know why you decided not to purchase this time. We are hoping you have not faced any 
			hiccups during the process.<br />
			<br /> We are offering you a special discount code -<strong> {discount_code}</strong> - 
			which gives you <strong> 
			{discount_value} </strong>OFF. The code applies after you spend&nbsp;<strong>{total_amount}
			</strong>. This promotion 
			is just for you and expires on <strong> {date_end}</strong>.<br />
			<br /> Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 6) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<p style="font-size: 32px; text-align: left;"><strong><span style="color: #9b9b9b;">THERE IS SOMETHING 
			</span></strong></p>
			<p style="font-size: 32px; text-align: left;"><strong><span style="color: #9b9b9b;">IN YOUR CART.</span>
			</strong></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in 
			your shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We do not know why you decided not to purchase this time. We are hoping you have 
			not faced any hiccups during the process.<br />
			<br /> We are offering you a special discount code -<strong> {discount_code}</strong> - which gives you 
			<strong> {discount_value} </strong>OFF. The code applies after you spend&nbsp;<strong>{total_amount}</strong>. 
			This promotion is just for you and expires on <strong> {date_end}</strong>.<br />
			<br /> Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 7) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in your 
			shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We do not know why you decided not to purchase this time. We are hoping you 
			have not faced any hiccups during the process.<br />
			<br /> We are offering you a special discount code -<strong> {discount_code}</strong> - which gives you 
			<strong> {discount_value} </strong>OFF. The code applies after you spend&nbsp;<strong>{total_amount}</strong>. 
			This promotion is just for you and expires on <strong> {date_end}</strong>.<br />
			<br /> Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 8) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> <span style="color: #777;"> 
			We are getting in touch since we saw that you had the following product(s) left in your shopping cart but for some 
			reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We do not know why you decided not to purchase this time. We are hoping you have not 
			faced any hiccups during the process.<br />
			<br /> We are offering you a special discount code -<strong> {discount_code}</strong> - which gives you <strong> 
			{discount_value} </strong>OFF. The code applies after you spend&nbsp;
                        <strong>{total_amount}</strong>. This promotion is
			just for you and expires on <strong> {date_end}</strong>.<br /><br /> Kind Regards,<br />
			<br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 9) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;">
                        <span style="color: #777;"> We are
			getting in touch since we saw that you had the following product(s)
                         left in your shopping cart but for some reason you
			did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> <span style="color: #777;"> 
			We do not know why you decided not to purchase this time.
                         We are hoping you have not faced any hiccups during the process.<br />
			<br /> We are offering you a special discount code -<strong> {discount_code}</strong> - which gives you <strong> 
			{discount_value} </strong>OFF. The code applies after you spend&nbsp;<strong>{total_amount}</strong>. This promotion 
			is just for you and expires on <strong> {date_end}</strong>.<br />
			<br /> Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 10) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in 
			your shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We do not know why you decided not to purchase this time. We are hoping you have not 
			faced any hiccups during the process.<br />
			<br /> We are offering you a special discount code -<strong> {discount_code}</strong> - which gives you <strong> 
			{discount_value} </strong>OFF. The code applies after you spend&nbsp;<strong>{total_amount}</strong>. This promotion 
			is just for you and expires on <strong> {date_end}</strong>.<br />
			<br /> Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
    }

    protected function getDefaultEmailReminder($i)
    {
        if ($i == 1) {
            return '<p style="text-align:left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;">
			<span style="color: #777;"> We are getting in touch since we saw that you
			had the following product(s) left in your shopping cart but for some reason you did not complete the order.
			</span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;">
			<span style="color: #777;">
			Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 2) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in your 
			shopping cart but for some reason you did not complete the order. </span> 
			</span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;">Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 3) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<p style="text-align: left; color: blue; font-size: 28px;">
                        <span style="color: rgba(95,158,160,0.66);"><strong>HURRY!!
			&nbsp;<span style="text-align: left; color: rgba(95,158,160,0.66); font-size: 20px;">BEFORE THESE ITEMS SELLS OUT
			</span></strong></span></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in your 
			shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;">Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 4) {
            return '<p style="text-align: center; font-size: 30px;"><span style="color: #45a245;">
                        <strong>Wait A Second!!</strong></span></p>
			<p style="text-align: center; font-size: 20px;"><span style="color: #f5a623;"><strong>Items you added to your cart 
			are almost sold.</strong></span></p>
			<p style="text-align: left;"><strong>Hi {firstname} {lastname}</strong></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in 
			your shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;">Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 5) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> <span style="color: #777;">
			We are getting in touch since we saw that you had the following product(s) left in your shopping cart but for some 
			reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> <span style="color: #777;">
			Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 6) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<p style="font-size: 32px; text-align: left;"><strong>
                        <span style="color: #9b9b9b;">THERE IS SOMETHING </span></strong></p>
			<p style="font-size: 32px; text-align: left;"><strong>
                        <span style="color: #9b9b9b;">IN YOUR CART.</span></strong></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in 
			your shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;">Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 7) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in 
			your shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;">Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 8) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> <span style="color: #777;"> 
			We are getting in touch since we saw that you had the following product(s) left in your shopping cart but for some 
			reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;">Kind Regards,<br />
			<br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 9) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> <span style="color: #777;"> 
			We are getting in touch since we saw that you had the following product(s) left in your shopping cart but for some 
			reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> <span style="color: #777;">
			Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
        if ($i == 10) {
            return '<p style="text-align: left;"><em><strong>Hi {firstname} {lastname}</strong></em></p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;"><span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;"> We are getting in touch since we saw that you had the following product(s) left in 
			your shopping cart but for some reason you did not complete the order. </span> </span></td>
			</tr>
			</tbody>
			</table>
			<p>{cart_content}</p>
			<table class="table" style="width: 100%;">
			<tbody>
			<tr>
			<td style="padding: 7px 0;">
			<span style="color: #555454; font-family: Open-sans, sans-serif; font-size: small;"> 
			<span style="color: #777;">Kind Regards,<br /><br /> {shop_name}<br /> {shop_url_link} </span> </span></td>
			</tr>
			</tbody>
			</table>';
        }
    }

    public function getDefaultSettings()
    {
        $settings = array(
            'plugin_id' => 'PS0008',
            'enable' => 1,
            'enable_test' => 0,
            'testing_email_id' => '',
            'delay_days' => 1,
            'delay_hours' => 0,
            'schedule' => 1
        );
        return $settings;
    }

    protected function loadMedia()
    {
        $dir = 'views/css/';
        $this->context->controller->addCSS($this->_path . $dir . 'admin_abandon.css');
        $this->context->controller->addCSS($this->_path . $dir . 'bootstrap.css');
        $this->context->controller->addCSS($this->_path . $dir . 'responsive.css');
        $this->context->controller->addCSS($this->_path . $dir . 'select2.css');
        $this->context->controller->addCSS($this->_path . $dir . 'default.css');
        $this->context->controller->addCSS($this->_path . $dir . 'jquery.easy-pie-chart.css');
        $this->context->controller->addCSS($this->_path . $dir . 'DT_bootstrap.css');
        //$this->context->controller->addCSS($this->_path.'css/style.css');
        $this->context->controller->addCSS($this->_path . $dir . 'fonts/glyphicons_regular.css');
        $this->context->controller->addCSS($this->_path . $dir . 'bootstrap-switch.css');
        $this->context->controller->addCSS($this->_path . $dir . 'style-light.css');

        $dir = 'views/js/';
        $this->context->controller->addJs($this->_path . $dir . 'admin_abandoncart.js');
        $this->context->controller->addJs($this->_path . $dir . 'table/bootbox.js');
        $this->context->controller->addJs($this->_path . $dir . 'uniform/jquery.uniform.min.js');
        $this->context->controller->addJs($this->_path . $dir . 'bootstrap-switch.js');
        //$this->context->controller->addJs($this->_path.$dir.'table/tables.js');
        $this->context->controller->addJs($this->_path . $dir . 'table/jquery.dataTables.js');
        $this->context->controller->addJs($this->_path . $dir . 'table/DT_bootstrap.js');
        $this->context->controller->addJs($this->_path . $dir . 'common.js');
        $this->context->controller->addJs($this->_path . $dir . 'tinymce.inc.js');
        $this->context->controller->addJs($this->_path . $dir . 'tiny_mce.js');

        //graph
        if (_PS_VERSION_ < '1.6.0') {
            $this->context->controller->addJs($this->_path . $dir . 'flot/jquery.flot121.js');
        } else {
            $this->context->controller->addJqueryPlugin('flot');
        }

        $this->context->controller->addJs($this->_path . $dir . 'flot/jquery.flot.pie.min.js');
        $this->context->controller->addJs($this->_path . $dir . 'flot/jquery.flot.axislabels.js');
        $this->context->controller->addJs($this->_path . $dir . 'flot/jquery.flot.orderBars.js');
        $this->context->controller->addJs($this->_path . $dir . 'flot/jquery.flot.tooltip_0.5.js');
        if (preg_match('/(?i)msie 8.0/', $_SERVER['HTTP_USER_AGENT'])) {
            $this->context->controller->addJs($this->_path . $dir . 'flot/excanvas.js');
        }
    }

    protected function checkTemplateType($template_id)
    {
        $check_template_query = 'select type from ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                . ' where id_template=' . (int) $template_id;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($check_template_query);
    }

    protected function getAbandonList()
    {
        $page_number = 1;
        $item_per_page = self::ITEM_PER_PAGE;
        if (Tools::getValue('page_number') && Tools::getValue('page_number') > 1) {
            $page_number = (int) Tools::getValue('page_number');
        }

        if (Tools::getValue('cart_per_page') && Tools::getValue('cart_per_page') > 1) {
            $item_per_page = (int) Tools::getValue('cart_per_page');
        }

        $abandon_cart_query = 'select {COLUMN} from ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME .
                ' as abd INNER JOIN ' . _DB_PREFIX_ . 'cart_product as cp on (abd.id_cart = cp.id_cart) 
			LEFT JOIN ' . _DB_PREFIX_ . 'velsof_abd_track_customers as abd_visitor on (cp.id_cart = abd_visitor.id_cart)
			LEFT JOIN ' . _DB_PREFIX_ . 'customer as c on (abd.id_customer = c.id_customer) 
			where (abd.is_converted = "0" AND abd.shows = "1")';

        if (Tools::getIsset('email') && trim(Tools::getValue('email')) != '') {
            if (Tools::getIsset('qtype') && Tools::getValue('qtype') == 1) {
                $abandon_cart_query .= ' AND (c.email = "'
                        . pSQL(trim(Tools::getValue('email'))) . '" or abd_visitor.email = "'
                        . pSQL(trim(Tools::getValue('email'))) . '")';
            } elseif (Tools::getIsset('qtype') && Tools::getValue('qtype') == 2) {
                $abandon_cart_query .= ' AND (c.email LIKE "%'
                        . pSQL(trim(Tools::getValue('email'))) . '%" or abd_visitor.email LIKE "%'
                        . pSQL(trim(Tools::getValue('email'))) . '%")';
            }
        }

        if (Tools::getIsset('ctype') && trim(Tools::getValue('ctype')) == 1) {
            $abandon_cart_query .= ' AND c.is_guest = 0';
        } elseif (Tools::getIsset('ctype') && trim(Tools::getValue('ctype')) == 2) {
            $abandon_cart_query .= ' AND abd.id_customer = 0 AND not exists (select id_cart from '
                    . _DB_PREFIX_ . 'velsof_abd_track_customers as abd_v where abd_v.id_cart = abd.id_cart)';
        } elseif (Tools::getIsset('ctype') && trim(Tools::getValue('ctype')) == 3) {
            $abandon_cart_query .= ' AND abd.is_guest = 1 AND abd.id_customer > 0';
        } elseif (Tools::getIsset('ctype') && trim(Tools::getValue('ctype')) == 4) {
            $abandon_cart_query .= ' AND abd.id_customer = 0 AND exists (select id_cart from '
                    . _DB_PREFIX_ . 'velsof_abd_track_customers as abd_v where abd_v.id_cart = abd.id_cart)';
        }

        $settings = unserialize(Configuration::get('VELSOF_ABANDONEDCART'));
        $total_delay = $settings['delay_hours'] + ( 24 * $settings['delay_days']);
        $delay_date = date('Y-m-d H:i:s', strtotime('-' . $total_delay . ' hours'));
        $abandon_cart_query .= ' AND abd.id_shop = ' . (int) $this->context->shop->id .
                ' AND abd.date_upd < "' . pSQL($delay_date) . '"';

        $abandon_cart_query .= ' GROUP BY cp.id_cart HAVING SUM(cp.quantity) > 0 ORDER BY abd.date_upd DESC';

        $total_records = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            str_replace('{COLUMN}', 'count(*) as total', $abandon_cart_query)
        );

        if (count($total_records) <= 0) {
            return array('flag' => false, 'pagination' => '');
        }

        $total_records = count($total_records);
        $total_pages = ceil((int) $total_records / $item_per_page);

        $page_position = (($page_number - 1) * $item_per_page);

        $abandon_cart_query .= ' LIMIT ' . (int) $page_position . ', ' . (int) $item_per_page;

        $columns = 'abd.*, c.email, c.is_guest, c.firstname, c.lastname';
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            str_replace('{COLUMN}', $columns, $abandon_cart_query)
        );
//d($results);
        if ($results && count($results) > 0) {
            $abandon_customers = array();
            foreach ($results as $list) {
                if (empty($list['id_customer']) || $list['id_customer'] == 0) {
                    $list['firstname'] = 'ABC';
                    $list['lastname'] = 'DEF';
                    $list['email'] = 'noreply@email.com';
                    $list['is_guest'] = true;
                }

                $list['date_add'] = Tools::displayDate($list['date_add'], null, true);
                $list['date_upd'] = Tools::displayDate($list['date_upd'], null, true);

                $check_coupon = 'select COUNT(cr.id_cart_rule) as count
					from ' . _DB_PREFIX_ . 'cart_rule as cr INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_lang as crl 
					on (cr.id_cart_rule = crl.id_cart_rule)
					where cr.id_customer = ' . (int) $list['id_customer'] . ' AND crl.name = "ABD[' . pSQL($list['email']) . ']"
					AND crl.id_lang = ' . (int) $list['id_lang'];

                $has_coupon = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($check_coupon);

                if ($has_coupon && $has_coupon['count'] > 0) {
                    $list['has_coupon'] = true;
                } else {
                    $list['has_coupon'] = false;
                }

                $lang = Language::getLanguage((int) $list['id_lang']);
                $list['language_text'] = $lang['name'];
                $abandon_customers[] = $list;
            }
            $paging = $this->customPaginator(
                $page_number,
                $total_records,
                $total_pages,
                'getAbandonedList'
            );
            return array(
                'flag' => true,
                'data' => $this->processAbandonedList($abandon_customers),
                'pagination' => $paging['paging'],
                'start_serial' => $paging['serial']
            );
        } else {
            return array('flag' => false, 'pagination' => '');
        }
    }

    protected function processAbandonedList($abandon_carts)
    {
        foreach ($abandon_carts as &$cart) {
            if ($cart['id_customer'] == 0) {
                $fetch_qry = 'select firstname, lastname, email from ' . _DB_PREFIX_
                        . self::ABD_TRACK_CUSTOMERS_TABLE_NAME . ' where id_cart = ' . (int) $cart['id_cart'];
                $user_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($fetch_qry);
                if ($user_data && count($user_data) > 0) {
                    if ($user_data['firstname'] != '') {
                        $cart['firstname'] = $user_data['firstname'];
                    }
                    if ($user_data['lastname'] != '') {
                        $cart['lastname'] = $user_data['lastname'];
                    }
                    if ($user_data['email'] != '') {
                        $cart['email'] = $user_data['email'];
                    }
                    $cart['tracked'] = 1;
                }
            }
        }
        return $abandon_carts;
    }

    protected function getEmailTemplateList()
    {
        $page_number = 1;
        if (Tools::getValue('temp_page_number') && Tools::getValue('temp_page_number') > 1) {
            $page_number = (int) Tools::getValue('temp_page_number');
        }

        $query = 'select {COLUMN} from ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' ORDER BY id_template';

        $total_records = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            str_replace('{COLUMN}', 'count(*) as total', $query)
        );
        if ($total_records['total'] <= 0) {
            return array('flag' => false, 'pagination' => '');
        }

        $total_pages = ceil((int) $total_records['total'] / self::ITEM_PER_PAGE);

        $page_position = (($page_number - 1) * self::ITEM_PER_PAGE);

        $query .= ' LIMIT ' . (int) $page_position . ', ' . (int) self::ITEM_PER_PAGE;

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(str_replace('{COLUMN}', '*', $query));

        if ($results && count($results) > 0) {
            $data = array();
            $email_type = $this->getEmailTypeArray();
            foreach ($results as $res) {
                $res['name'] = Tools::htmlentitiesDecodeUTF8($res['name']);
                $res['template_type_text'] = $email_type[(int) $res['type']];
                $data[] = $res;
            }

            $paging = $this->customPaginator(
                $page_number,
                $total_records['total'],
                $total_pages,
                'getNextTemplatesPage'
            );
            return array(
                'flag' => true,
                'data' => $data,
                'pagination' => $paging['paging'],
                'start_serial' => $paging['serial']
            );
        } else {
            return array('flag' => false, 'pagination' => '');
        }
    }

    protected function loadEmailTemplates($with_translation = true, $type_param = array())
    {
        $type_str = '';
        if (isset($type_param['type'])) {
            $type_str .= ' WHERE abd.type = ' . (int) $type_param['type'];
        }

        if ($with_translation) {
            $query = 'select abd.*, ac.id_template_content, ac.id_lang from '
                    . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' as abd
                INNER JOIN ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME . ' as ac 
                on (abd.id_template = ac.id_template)' . $type_str . ' ORDER BY abd.id_template';
        } else {
            $query = 'select abd.* from ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' as abd '
                    . $type_str . ' ORDER BY id_template';
        }

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && count($results) > 0) {
            $data = array();
            foreach ($results as $res) {
                $res['name'] = Tools::htmlentitiesDecodeUTF8($res['name']);
                if ($with_translation) {
                    $lang = Language::getLanguage((int) $res['id_lang']);
                    $res['language_text'] = $lang['name'];
                }
                $data[] = $res;
            }
            return $data;
        } else {
            return array();
        }
    }

    protected function saveEmailTemplate()
    {
        $data = Tools::getValue('email_template');
        $query = '';
        $save_time = date('Y-m-d H:i:s', time());
        $id_template = 0;
        if (isset($data['id_template']) && $data['id_template'] > 0) {
            $query .= 'UPDATE ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                    . ' set type = ' . (int) $data['type'] . ', 
                    name = "' . pSQL(Tools::htmlentitiesUTF8($data['name'])) . '", 
                    date_upd = "' . pSQL($save_time) . '" WHERE id_template = ' . (int) $data['id_template'];
            $is_save = Db::getInstance()->execute($query);
            $id_template = (int) $data['id_template'];
        } else {
            $query = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                    . ' (type, name, date_add, date_upd) values('
                    . (int) $data['type'] . ', "'
                    . pSQL(Tools::htmlentitiesUTF8($data['name'])) . '", "'
                    . pSQL($save_time) . '","' . pSQL($save_time) . '")';

            $is_save = Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($query);
            if ($is_save) {
                $id_template = Db::getInstance(_PS_USE_SQL_SLAVE_)->Insert_ID();
                $languages = Language::getLanguages(true);
                foreach ($languages as $lang) {
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME .
                        ' (id_template, id_lang, iso_code, subject, 
                        body, cart_template, date_add, date_upd) values('
                        . (int) $id_template . ', '
                        . (int) $lang['id_lang'] . ', "' . pSQL($lang['iso_code']) . '", "'
                        . pSQL(Tools::htmlentitiesUTF8($data['subject'])) . '","'
                        . pSQL(Tools::htmlentitiesUTF8($data['body'])) . '", "'
                        . (int) $data['cart_template'] . '","'
                        . pSQL($save_time) . '", "' . pSQL($save_time) . '")';

                    Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
                }
            }
        }

        if ($is_save) {
            return array(
                'status' => true,
                'message' => $this->l('Email template has been saved successfully.', 'abandonedcart_core'),
                'data' => $this->loadEmailTemplateById($id_template)
            );
        } else {
            return array('status' => false, 'message' => $this->l('Error: Not able to save template.', 'abandonedcart_core'));
        }
    }

    protected function updateEmailTemplateTranslation($data)
    {
        $query = '';
        $save_time = date('Y-m-d H:i:s', time());

        $query .= 'UPDATE ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME
                . ' set subject = "' . pSQL(Tools::htmlentitiesUTF8($data['subject'])) . '", 
                body = "' . pSQL(Tools::htmlentitiesUTF8($data['body'])) . '", 
                date_upd = "' . pSQL($save_time) . '",cart_template="'
                . (int) $data['cart_template'] . '" WHERE id_template = '
                . (int) $data['id_template'] . ' AND id_lang = ' . (int) $data['id_lang'];
        $is_save = Db::getInstance()->execute($query);
        $id_template = (int) $data['id_template'];

        $lang_data = Language::getLanguage((int) $data['id_lang']);
        if ($is_save) {
            return array(
                'status' => true,
                'message' => sprintf(
                    $this->l('Template Translation for %s has been saved successfully.', 'abandonedcart_core'),
                    $lang_data['name']
                ),
                'data' => $this->loadEmailTemplateById($id_template)
            );
        } else {
            return array('status' => false, 'message' => $this->l('Error: Not able to save template translation.', 'abandonedcart_core'));
        }
    }

    protected function remEmailTemplate($id_template = 0)
    {
        $query = 'delete from ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME .
            ' where id_template = ' . (int) $id_template;
        if (Db::getInstance()->execute($query)) {
            return array('status' => true, 'message' => $this->l('Email template has been deleted successfully.', 'abandonedcart_core'));
        } else {
            return array('status' => false, 'message' => $this->l('Error: Not able to delete template.', 'abandonedcart_core'));
        }
    }

    protected function loadNewEmailTemplate()
    {
        $result = array(
            'id_template' => 0,
            'id_lang' => $this->context->language->id,
            'type' => Tools::getValue('type')
        );

        if (Tools::getIsset('type') && Tools::getValue('type') == self::DISCOUNT_EMAIL) {
            $result['name'] = self::DEFAULT_TEMPLATE_NAME;
            $result['subject'] = Tools::htmlentitiesDecodeUTF8(self::DEFAULT_TEMPLATE_SUBJECT);
            $result['body'] = Tools::htmlentitiesDecodeUTF8($this->getDefaultEmailTemplate(1));
        } else {
            $result['name'] = self::DEFAULT_REMINDER_TEMPLATE_NAME;
            $result['subject'] = Tools::htmlentitiesDecodeUTF8(self::DEFAULT_REMINDER_TEMPLATE_SUBJECT);
            $result['body'] = Tools::htmlentitiesDecodeUTF8($this->getDefaultEmailReminder(1));
        }

        return $result;
    }

    protected function loadEmailTemplateById($id_template = 0)
    {
        $query = 'select * from ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                . ' where id_template = ' . (int) $id_template;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
        if ($result) {
            $data = $result;
            $data['name'] = Tools::htmlentitiesDecodeUTF8($data['name']);
        } else {
            $type = $this->getDefaultEmailType();
            $data = array(
                'id_template' => 0,
                'id_lang' => $this->context->language->id,
                'type' => $type
            );
            if ($type == self::DISCOUNT_EMAIL) {
                $data['name'] = Tools::htmlentitiesDecodeUTF8(self::DEFAULT_TEMPLATE_NAME);
            } else {
                $data['name'] = Tools::htmlentitiesDecodeUTF8(self::DEFAULT_REMINDER_TEMPLATE_NAME);
            }
        }

        return $data;
    }

    protected function loadEmailTemplateTranslation($id_template, $id_lang, $id_template_content = 0)
    {
        if ($id_template_content > 0) {
            $query = 'select * from ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME
                    . ' where id_template_content = ' . (int) $id_template_content;
        } else {
            $query = 'select * from ' . _DB_PREFIX_ . self::TEMPLATE_CONTENT_TABLE_NAME
                    . ' where id_template = ' . (int) $id_template . ' AND id_lang = ' . (int) $id_lang;
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

        $id_template = $result['id_template'];
        $id_lang = $result['id_lang'];
        if ($result) {
            $data = $result;
            $data['subject'] = Tools::htmlentitiesDecodeUTF8($result['subject']);
            $data['body'] = Tools::htmlentitiesDecodeUTF8($result['body']);
            $data['cart_template'] = $result['cart_template'];
            $query = 'select type from ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                    . ' where id_template = ' . (int) $id_template;
            $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

            $data['type'] = $row['type'];

            return $data;
        } else {
            $result = array(
                'id_template' => 0,
                'id_template_content' => 0,
                'id_lang' => $id_lang,
                'cart_template' => 1,
                'type' => 0,
            );

            $template = $this->loadEmailTemplateById($id_template);
            if ($template['type'] == self::DISCOUNT_EMAIL) {
                $result['subject'] = self::DEFAULT_TEMPLATE_SUBJECT;
                $result['body'] = $this->getDefaultEmailTemplate(1);
            } else {
                $result['subject'] = self::DEFAULT_REMINDER_TEMPLATE_SUBJECT;
                $result['body'] = $this->getDefaultEmailReminder(1);
            }
        }

        return $result;
    }

    protected function getIncentiveList()
    {
        $page_number = 1;
        if (Tools::getValue('inc_page_number') && Tools::getValue('inc_page_number') > 1) {
            $page_number = (int) Tools::getValue('inc_page_number');
        }

        $query = 'select {COLUMN} from ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . ' as inc 
            INNER JOIN ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' as em on (inc.id_template = em.id_template) 
            ORDER by delay_days ASC, delay_hrs ASC';

        $total_records = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            str_replace('{COLUMN}', 'count(*) as total', $query)
        );
        if ($total_records['total'] <= 0) {
            return array('flag' => false, 'pagination' => '');
        }

        $total_pages = ceil((int) $total_records['total'] / self::ITEM_PER_PAGE);

        $page_position = (($page_number - 1) * self::ITEM_PER_PAGE);

        $query .= ' LIMIT ' . (int) $page_position . ', ' . (int) self::ITEM_PER_PAGE;

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(str_replace('{COLUMN}', 'inc.*, em.name', $query));
        if ($results && count($results) > 0) {
            $data = array();
//			$discount_types = $this->getDiscountTypeArray();
            $reminder_types = $this->getReminderTypeText();
            $statuses = $this->getIncentiveStatuses();
            foreach ($results as $res) {
                $currency_ins = new Currency((int) $res['id_currency']);
                $res['name'] = Tools::htmlentitiesDecodeUTF8($res['name']);
//				$res['discount_type_txt'] = $discount_types[(int)$res['discount_type']];
                $res['discount_type_txt'] = $reminder_types[(int) $res['incentive_type']];
                $res['discount_value_txt'] = Tools::displayPrice($res['discount_value'], $currency_ins);
                if ((int) $res['discount_type'] == self::DISCOUNT_PERCENTAGE) {
                    $res['discount_value_txt'] = Tools::ps_round($res['discount_value']);
                    $res['discount_value_txt'] .= ' %';
                }

                $res['min_cart_value_txt'] = Tools::displayPrice($res['min_cart_value'], $currency_ins);
                $res['status_txt'] = $statuses[(int) $res['status']];
                $free_ship = ((int) $res['has_free_shipping'] == 1) ? $this->l('Yes', 'abandonedcart_core') : $this->l('No', 'abandonedcart_core');
                $res['has_free_shipping_txt'] = $free_ship;
                $res['coupon_validity'] = $res['coupon_validity'];
                $res['coupon_validity_txt'] = $res['coupon_validity'] . ' day(s)';
                $res['delay_txt'] = (int) $res['delay_days'] . ' Days ' . (int) $res['delay_hrs'] . ' hrs';
                if ($res['incentive_type'] == 1) {
                    $res['coupon_validity_txt'] = '-';
                    $res['discount_value_txt'] = '-';
                }
                $data[] = $res;
            }

            $paging = $this->customPaginator(
                $page_number,
                $total_records['total'],
                $total_pages,
                'getNextIncentivePage'
            );
            return array(
                'flag' => true,
                'data' => $data,
                'pagination' => $paging['paging'],
                'start_serial' => $paging['serial']
            );
        } else {
            return array('flag' => false, 'pagination' => '');
        }
    }

    protected function loadAllIncentives()
    {
        $query = 'select inc.*, em.name from ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . ' as inc 
            INNER JOIN ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' as 
            em on (inc.id_template = em.id_template) 
            ORDER by delay_days ASC, delay_hrs ASC';

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if ($results && count($results) > 0) {
            $data = array();
//			$discount_types = $this->getDiscountTypeArray();
            $reminder_types = $this->getReminderTypeText();
            $statuses = $this->getIncentiveStatuses();
            foreach ($results as $res) {
                $currency_ins = new Currency((int) $res['id_currency']);
                $res['name'] = Tools::htmlentitiesDecodeUTF8($res['name']);
//				$res['discount_type_txt'] = $discount_types[(int)$res['discount_type']];
                $res['discount_type_txt'] = $reminder_types[(int) $res['incentive_type']];
                $res['discount_value_txt'] = Tools::displayPrice($res['discount_value'], $currency_ins);
                if ((int) $res['discount_type'] == self::DISCOUNT_PERCENTAGE) {
                    $res['discount_value_txt'] = Tools::ps_round($res['discount_value']);
                    $res['discount_value_txt'] .= ' %';
                }

                $res['min_cart_value_txt'] = Tools::displayPrice($res['min_cart_value'], $currency_ins);
                $res['status_txt'] = $statuses[(int) $res['status']];
                $ship_condition = ((int) $res['has_free_shipping'] == 1) ? $this->l('Yes', 'abandonedcart_core') : $this->l('No', 'abandonedcart_core');
                $res['has_free_shipping_txt'] = $ship_condition;
                $res['coupon_validity'] = $res['coupon_validity'];
                $res['coupon_validity_txt'] = $res['coupon_validity'] . ' day(s)';
                $res['delay_txt'] = (int) $res['delay_days'] . ' Days ' . (int) $res['delay_hrs'] . ' hrs';
                if ($res['incentive_type'] == 1) {
                    $res['coupon_validity_txt'] = '-';
                    $res['discount_value_txt'] = '-';
                }
                $data[] = $res;
            }
            return $data;
        } else {
            return array();
        }
    }

    protected function loadIncentivebyId($id_incentive = 0)
    {
//		$discount_types = $this->getDiscountTypeArray();
        $reminder_types = $this->getReminderTypeText();
        $statuses = $this->getIncentiveStatuses();
        if ($id_incentive > 0) {
            $query = 'select inc.*, em.name from ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . ' as inc 
				INNER JOIN ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME . ' as em 
				on (inc.id_template = em.id_template) where id_incentive = ' . (int) $id_incentive;

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
            if ($result) {
                $data = $result;
                $currency_ins = new Currency((int) $result['id_currency']);
                $data['name'] = Tools::htmlentitiesDecodeUTF8($result['name']);
                $data['discount_type_txt'] = $reminder_types[(int) $result['incentive_type']];
                $data['discount_value_txt'] = Tools::displayPrice($result['discount_value'], $currency_ins);
                if ((int) $result['discount_type'] == self::DISCOUNT_PERCENTAGE) {
                    $data['discount_value_txt'] = Tools::ps_round($result['discount_value']);
                    $data['discount_value_txt'] .= ' %';
                }

                $data['min_cart_value_txt'] = Tools::displayPrice($result['min_cart_value'], $currency_ins);
                $data['status_txt'] = $statuses[(int) $result['status']];
                $free_ship = ((int) $result['has_free_shipping'] == 1) ? $this->l('Yes', 'abandonedcart_core') : $this->l('No', 'abandonedcart_core');
                $data['has_free_shipping_txt'] = $free_ship;
                $data['coupon_validity'] = $result['coupon_validity'];
                $data['coupon_validity_txt'] = $result['coupon_validity'] . ' day(s)';
                $data['delay_txt'] = (int) $result['delay_days'] . ' Days ' . (int) $result['delay_hrs'] . ' hrs';
                if ($result['incentive_type'] == 1) {
                    $data['coupon_validity_txt'] = '-';
                    $data['discount_value_txt'] = '-';
                }
                return $data;
            } else {
                return array();
            }
        } else {
            $result = array(
                'id_incentive' => 0,
                'id_template' => 0,
                'id_currency' => $this->context->currency->id,
                'discount_type' => $this->getDefaultDiscountType(),
                'discount_type_txt' => $reminder_types[(int) $this->getDefaultDiscountType()],
                'discount_value' => 0,
                'discount_value_txt' => 0,
                'min_cart_value' => 0,
                'min_cart_value_for_mails' => 0,
                'min_cart_value_txt' => 0,
                'coupon_validity' => $this->getDefaultCouponValidity(),
                'status' => $this->getDefaultIncentiveStatus(),
                'status_txt' => $statuses[(int) $this->getDefaultIncentiveStatus()],
                'has_free_shipping' => 0,
                'has_free_shipping_txt' => $this->l('No', 'abandonedcart_core'),
                'delay_days' => 0,
                'delay_hrs' => 0,
                'delay_txt' => '0 Days 0 hrs',
            );
        }

        return $result;
    }

    protected function saveIncentive()
    {
        $data = Tools::getValue('incentive');
        $query = '';
        $save_time = date('Y-m-d H:i:s', time());
        if (isset($data['id_incentive']) && $data['id_incentive'] > 0) {
            $query .= 'UPDATE ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME
                    . ' set id_template = ' . (int) $data['id_template'] . ', 
                    incentive_type = "' . (int) $data['incentive_type'] . '", 
                    discount_type = ' . (int) $data['discount_type'] . ', 
                    discount_value = ' . (float) $data['discount_value'] . ', 
                    min_cart_value = ' . (float) $data['min_cart_value'] . ', 
                    min_cart_value_for_mails = ' . (float) $data['min_cart_value_for_mails'] . ', 
                    coupon_validity = ' . (int) $data['coupon_validity'] . ', 
                    status = ' . (int) $data['status'] . ', 
                    has_free_shipping = ' . (int) $data['has_free_shipping'] . ', 
                    delay_days = ' . (int) $data['delay_days'] . ', 
                    delay_hrs = ' . (int) $data['delay_hrs'] . ', 
                    date_upd = "' . pSQL($save_time) . '" WHERE id_incentive = '
                    . (int) $data['id_incentive'];
            $is_save = Db::getInstance()->execute($query);
            $id_incentive = (int) $data['id_incentive'];
        } else {
            $query = 'INSERT INTO ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . ' 
                    (incentive_type, id_template, id_currency, discount_type, 
                    discount_value, min_cart_value, min_cart_value_for_mails, coupon_validity, status, 
                    has_free_shipping, delay_days, delay_hrs, date_add, date_upd) values("'
                    . (int) $data['incentive_type'] . '", '
                    . (int) $data['id_template'] . ', '
                    . (int) $this->context->currency->id . ', '
                    . (int) $data['discount_type'] . ', '
                    . (float) $data['discount_value'] . ', '
                    . (float) $data['min_cart_value'] . ', '
                    . (float) $data['min_cart_value_for_mails'] . ', "'
                    . (int) $data['coupon_validity'] . '",'
                    . (int) $data['status'] . ', '
                    . (int) $data['has_free_shipping'] . ', '
                    . (int) $data['delay_days'] . ', '
                    . (int) $data['delay_hrs'] . ', "' . pSQL($save_time) . '","' . pSQL($save_time) . '")';

            $is_save = Db::getInstance()->execute($query);
            $id_incentive = Db::getInstance(_PS_USE_SQL_SLAVE_)->Insert_ID();
        }

        if ($is_save) {
            return array(
                'status' => true,
                'message' => $this->l('Reminder has been saved successfully.', 'abandonedcart_core'),
                'data' => $this->loadIncentivebyId($id_incentive));
        } else {
            return array('status' => false, 'message' => $this->l('Error: Not able to save reminder.', 'abandonedcart_core'));
        }
    }

    protected function remIncentive($id_incentive = 0)
    {
        $query = 'delete from ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME . ' where '
                . 'id_incentive = ' . (int) $id_incentive;
        if (Db::getInstance()->execute($query)) {
            return array('status' => true, 'message' => $this->l('Reminder has been saved successfully.', 'abandonedcart_core'));
        } else {
            return array('status' => false, 'message' => $this->l('Error: Not able to delete incentive.', 'abandonedcart_core'));
        }
    }

    protected function customPaginator($current_page, $total_records, $total_pages, $ajaxcallfn = '')
    {
        $summary_txt = '';
        $pagination = '';
        if ($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages) {
            $summary_align = 'abd-pagination-left';
            $pagination_align = 'abd-pagination-left';
            if (self::PAGINATION_ALIGN == 'right') {
                $summary_align = 'abd-pagination-left';
                $pagination_align = 'abd-pagination-right';
            }
            $record_start = $current_page;
            $record_end = self::ITEM_PER_PAGE;
            if ($current_page > 1) {
                $record_start = (($current_page - 1) * self::ITEM_PER_PAGE) + 1;
                if ($current_page == $total_pages) {
                    $record_end = $total_records;
                } else {
                    $record_end = $current_page * self::ITEM_PER_PAGE;
                }
            }

            $summary_txt = '<div class="' . $summary_align . ' abd-paginate-summary">
                Showing ' . $record_start . ' to ' . $record_end . ' of ' .
                    $total_records . ' (' . $total_pages . ' pages)</div>';

            $pagination .= '<div class="' . $pagination_align . '"><ul class="abd-pagination">';

            $ajax_call_function = '';
            if ($ajaxcallfn != '') {
                $ajax_call_function .= $ajaxcallfn . '({page_number},this);';
            }

            $right_links = $current_page + 3;
            $previous = $current_page - 3; //previous link
            $first_link = true; //boolean var to decide our first link

            if ($current_page > 1) {
                $previous_link = ($previous == 0) ? 1 : $previous;
                $pagination .= '<li class="first"><a href="javascript:void(0)" data-page="1" 
                    onclick="' . str_replace('{page_number}', 1, $ajax_call_function) . '" 
                    title="First">&laquo;</a></li>'; //first link
                $pagination .= '<li><a href="javascript:void(0)" data-page="' . $previous_link . '" 
                    onclick="' . str_replace('{page_number}', $previous_link, $ajax_call_function) . '" 
                    title="Previous">&lt;</a></li>'; //previous link
                for ($i = ($current_page - 2); $i < $current_page; $i++) {
                    if ($i > 0) {
                        $pagination .= '<li><a href="javascript:void(0)" data-page="' . $i . '" 
                            onclick="' . str_replace('{page_number}', $i, $ajax_call_function) . '" 
                            title="Page' . $i . '">' . $i . '</a></li>';
                    }
                }
                $first_link = false; //set first link to false
            }

            if ($first_link) {
                $pagination .= '<li class="first active">' . $current_page . '</li>';
            } elseif ($current_page == $total_pages) {
                $pagination .= '<li class="last active">' . $current_page . '</li>';
            } else {
                $pagination .= '<li class="active">' . $current_page . '</li>';
            }

            for ($i = $current_page + 1; $i < $right_links; $i++) {
                if ($i <= $total_pages) {
                    $pagination .= '<li><a href="javascript:void(0)" data-page="' . $i . '" 
					onclick="' . str_replace('{page_number}', $i, $ajax_call_function) . '" 
					title="Page ' . $i . '">' . $i . '</a></li>';
                }
            }
            if ($current_page < $total_pages) {
                $next_link = ($i > $total_pages) ? $total_pages : $i;
                $pagination .= '<li><a href="javascript:void(0)" data-page="' . $next_link . '" 
                    onclick="' . str_replace('{page_number}', $next_link, $ajax_call_function) . '"
                    title="Next">&gt;</a></li>'; //next link
                $pagination .= '<li class="last"><a href="javascript:void(0)" data-page="' . $total_pages . '" 
                    onclick="' . str_replace('{page_number}', $total_pages, $ajax_call_function) . '"
                    title="Last">&raquo;</a></li>'; //last link
            }

            $pagination .= '</div></ul>';
            return array('paging' => $summary_txt . $pagination, 'serial' => $record_start);
        }
        return array('paging' => '', 'serial' => 1);
    }

    protected function getTemplateBaseHtml()
    {
        $template_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
            "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
            <html>
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0,
                    maximum-scale=1.0, user-scalable=0" />
                    <title>Message from {shop_name}</title>
                </head>
                <body style="width:75%;padding:12%;-webkit-text-size-adjust:none;background:rgba(128,128,128,0.14);
                    font-family:Open-sans, sans-serif;color:#555454;font-size:13px;line-height:18px;margin:auto">
                    <table class="table table-mail" style="width:100%;background:white;
                        -moz-box-shadow:0 0 5px #afafaf;-webkit-box-shadow:0 0 5px #afafaf;
                        -o-box-shadow:0 0 5px #afafaf;box-shadow:0 0 5px #afafaf;
                        filter:progid:DXImageTransform.Microsoft.Shadow(color=#afafaf,Direction=134,Strength=5)">
                    <tr>
                        <td style="width:20px; padding:7px 0;">&nbsp;</td>
                        <td align="center" style="padding:7px 0">
                            <table class="table" style="width:100%" >
                                <tr>
                                    <td align="center" class="logo" style="border-bottom:4px solid #333333;
                                        padding:7px 0">
                                        <a title="{shop_name}" href="{shop_url_link}" style="color:#337ff1">
                                            <img src="{shop_logo}" alt="{shop_name}" />
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            {template_content}
                        <td style="width:20px; padding:7px 0;">&nbsp;</td>
                    </tr>
                    </table>
                </body>
            </html>';
        return $template_html;
    }

    protected function generateCouponCode()
    {
        $length = 8;
        $code = '';
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ0123456789';
        $maxlength = Tools::strlen($chars);
        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = Tools::substr($chars, mt_rand(0, $maxlength - 1), 1);
            if (!strstr($code, $char)) {
                $code .= $char;
                $i++;
            }
        }
        // Check if coupon code alredy exist or not
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'cart_rule where code = "' . pSQL($code) . '"';
        $result = Db::getInstance()->executeS($sql);
        if (count($result) == 0) {
            return $code;
        }
        return $this->generateCouponCode();
    }

    protected function getConvertedList()
    {
        $page_number = 1;
        if (Tools::getValue('page_number') && Tools::getValue('page_number') > 1) {
            $page_number = (int) Tools::getValue('page_number');
        }

        $query = 'select {COLUMN} from ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' as abd 
			INNER JOIN ' . _DB_PREFIX_ . 'orders as ord on(abd.id_cart = ord.id_cart) 
			INNER JOIN ' . _DB_PREFIX_ . 'customer as cus on (ord.id_customer = cus.id_customer) 
			INNER JOIN ' . _DB_PREFIX_ . 'order_state_lang as st 
			on (ord.current_state = st.id_order_state 
			AND st.id_lang = ' . (int) $this->context->language->id . ') 
			where (abd.is_converted = "1")';

        $query .= ' AND abd.id_shop = ' . (int) $this->context->shop->id .
                ' AND abd.id_lang = ' . (int) $this->context->language->id . ' ORDER BY abd.date_upd DESC';

        $colmns = 'abd.id_abandon, ord.reference,ord.id_order,ord.total_paid, ord.date_add, cus.firstname, 
				cus.lastname, cus.email, st.name as status';

        $total_records = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            str_replace('{COLUMN}', 'count(*) as total', $query)
        );

        if ((int) $total_records['total'] <= 0) {
            return array('flag' => false, 'pagination' => '');
        }

        $total_records = $total_records['total'];
        $total_pages = ceil((int) $total_records / self::ITEM_PER_PAGE);

        $page_position = (($page_number - 1) * self::ITEM_PER_PAGE);

        $query .= ' LIMIT ' . (int) $page_position . ', ' . (int) self::ITEM_PER_PAGE;

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(str_replace('{COLUMN}', $colmns, $query));

        if ($results && count($results) > 0) {
            $converted_list = array();
            foreach ($results as $list) {
                $temp = $list;
                $temp['date_add'] = Tools::displayDate($temp['date_add'], null, true);
                $temp['formatted_total'] = Tools::displayprice($temp['total_paid']);
                $temp['order_url'] = $this->context->link->getAdminLink('AdminOrders') . '&id_order='
                        . (int) $temp['id_order'] . '&vieworder';
                $converted_list[] = $temp;
            }

            $paging = $this->customPaginator($page_number, $total_records, $total_pages, 'getConvertedList');
            return array('flag' => true, 'data' => $converted_list,
                'pagination' => $paging['paging'], 'start_serial' => $paging['serial']);
        } else {
            return array('flag' => false, 'pagination' => '');
        }
    }

    protected function updateTemplateName($id_template = 0, $template_name = '')
    {
        $query = '';

        $query .= 'UPDATE ' . _DB_PREFIX_ . self::TEMPLATE_TABLE_NAME
                . ' set name = "' . pSQL(Tools::htmlentitiesUTF8($template_name)) . '", 
            date_upd = "' . pSQL(date('Y-m-d H:i:s', time())) . '" WHERE id_template = ' . (int) $id_template;
        $is_save = Db::getInstance()->execute($query);

        if ($is_save) {
            return array('status' => true);
        } else {
            return array('status' => false, 'message' => $this->l('Error: Not able to update template name.', 'abandonedcart_core'));
        }
    }

    protected function changeIncentiveStatus($incetive)
    {
        $incentive_data = explode('_', $incetive);

        $query = '';

        $query .= 'UPDATE ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME
                . ' set status = "' . (($incentive_data[1] == 1) ? 0 : 1) . '", 
            date_upd = "' . pSQL(date('Y-m-d H:i:s', time())) . '" WHERE id_incentive = ' . (int) $incentive_data[0];

        $is_save = Db::getInstance()->execute($query);

        if ($is_save) {
            return array(
                'status' => true,
                'current_status' => (
                    ($incentive_data[1] == 1) ? $this->l('Disabled', 'abandonedcart_core') : $this->l('Enabled', 'abandonedcart_core')
                ),
                'data_value' => $incentive_data[0] . '_' . (($incentive_data[1] == 1) ? 0 : 1),
                'status_value' => (($incentive_data[1] == 1) ? 0 : 1)
            );
        } else {
            return array('status' => false, 'message' => $this->l('Error: Not able to update template name.', 'abandonedcart_core'));
        }
    }
    
    /*
     * Function Added by RS on 07-Sept-2017 to Optimize the Analytics Process
     */
    public function getCartsBasedOnFilters($filters_string = '')
    {
        $carts_query = 'select abd.id_cart, abd.is_converted, abd.cart_total from ' .
            _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' as abd
            INNER JOIN ' . _DB_PREFIX_ . 'cart_product as cp on (abd.id_cart = cp.id_cart)
            where abd.shows="1" 
            and abd.id_shop = ' . (int) $this->context->shop->id . ' and abd.id_lang = ' .
            (int) $this->context->language->id.' '.$filters_string
            . ' GROUP BY cp.id_cart HAVING SUM(cp.quantity) > 0';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($carts_query);
    }

    /*
     * Function modified by RS on 07-Sept-2017 to get the Cart Total from DB table and also combining the queries for Abandoned Cart and Converted Carts
     */
    protected function getPieChartsData()
    {
        $return_data = array();
        $total_carts = $this->getCartsBasedOnFilters();

        $return_data['total_converted_amount'] = 0;
        $return_data['total_abandoned_amount'] = 0;
        $converted_carts = 0;
        $abandoned_carts = 0;
        foreach ($total_carts as $cart) {
            if ((int) $cart['is_converted'] == 1) {
                $return_data['total_converted_amount'] += (float) $cart['cart_total'];
                $converted_carts++;
            } else if ((int) $cart['is_converted'] == 0) {
                $return_data['total_abandoned_amount'] += (float) $cart['cart_total'];
                $abandoned_carts++;
            }
        }
        $return_data['total_converted'] = $converted_carts;
        $return_data['total_abandoned'] = $abandoned_carts;
        return $return_data;
    }
}
