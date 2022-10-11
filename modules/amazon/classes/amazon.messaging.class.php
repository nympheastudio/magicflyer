<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
*/

require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.certificates.class.php');

/**
 * Class AmazonMessaging
 */
class AmazonMessaging extends Amazon
{
    /**
     * @var array
     */
    public static $invoice_subjects = array(
        'fr' => '[Important] Facture pour votre commande',
        'en' => '[Important] Invoice for your order',
        'de' => '[Wichtig] Rechnung f&uuml;r Ihre Bestellung',
        'it' => '[Importante] Fattura per il vostro ordine',
        'es' => '[Importante] Factura de su pedido'
    );
    /**
     * @var array
     */
    public static $review_subjects  = array(
        'fr' => 'Evaluation concernant votre commande N&ordm;',
        'en' => 'Seller Rating for your order No.',
        'de' => 'Bewertung Ihrer Bestellung Nr.',
        'it' => 'Valutazione del Suo ordine N&ordm;',
        'es' => 'Evaluaci&oacute;n relativa a su pedido N&ordm;'
    );

    /**
     * @var array
     */
    public static $email_providers = array(
        'gmail' => 'Google Mail (gmail.com)'
    );

    public static $email_hostnames = array(
    'gmail' => '{imap.gmail.com:993/imap/ssl}',
    'gmail_debug' => '{imap.gmail.com:993/imap/ssl/debug}'
    );

    /**
     * @var bool
     */
    private $_debug;

    /**
     * @var bool
     */
    private $test_mode = false;

    /**
     * AmazonMessaging constructor.
     *
     * @param bool $debug
     */
    public function __construct($debug = false, $test_mode = false)
    {
        if ($debug) {
            $this->_debug = true;
        } else {
            $this->_debug = false;
        }

        if ($test_mode) {
            $this->test_mode = true;
        }

        $this->path = _PS_MODULE_DIR_.$this->name.'/';
        $this->path_pdf = $this->path.'pdf/';
        $this->path_mail = $this->path.'mails/';

        parent::__construct();
    }

    /**
     * @param $params
     *
     * @return bool
     */
    public function overrideCustomerThreadEmail(&$params)
    {
        $id_lang = $params['id_lang'];
        $lang = Language::getIsoById($id_lang);

        $template_file_html = sprintf('%s%s/%s.html', $this->path_mail, $lang, $params['template']);
        $template_file_txt = sprintf('%s%s/%s.txt', $this->path_mail, $lang, $params['template']);

        if (!file_exists($template_file_html)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Template file doesn\'t exists for this lang: %s(%d)', basename(__FILE__), __LINE__, $lang, $id_lang));
            }

            return (false);
        }
        if (!file_exists($template_file_txt)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Template file doesn\'t exists for this lang: %s(%d)', basename(__FILE__), __LINE__, $lang, $id_lang));
            }

            return (false);
        }
        $params['template_html'] = Tools::file_get_contents($template_file_html);
        $params['template_txt'] = Tools::file_get_contents($template_file_txt);

        return(true);
    }


    /**
     * @param $id_order
     *
     * @return bool|int
     */
    public function sendInvoice($id_order)
    {
        $mail_invoice = AmazonConfiguration::get('MAIL_INVOICE');
        $account_type = AmazonConfiguration::get('ACCOUNT_TYPE');

        if (!isset($mail_invoice['active']) || !(int)$mail_invoice['active']) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Send invoice is inactive', basename(__FILE__), __LINE__));
            }

            return (false);
        }

        if (!isset($mail_invoice['template']) || empty($mail_invoice['template'])) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d You must select an email template', basename(__FILE__), __LINE__));
            }

            return (false);
        }

        if ($account_type != Amazon::ACCOUNT_TYPE_INDIVIDUAL) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d This feature can work only with individual account configuration', basename(__FILE__), __LINE__));
            }

            return (false);
        }

        $order = new AmazonOrder($id_order);

        if (!Validate::isLoadedObject($order) || !$order->id_lang || !isset($order->marketPlaceOrderId) || empty($order->marketPlaceOrderId)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Invalid Order: %s', basename(__FILE__), __LINE__, print_r(get_object_vars($order), true)));
            }

            return (false);
        }

        if (!$order->invoice_number) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Invalid processing for Order (%d) - Order has no invoice number', basename(__FILE__), __LINE__, $id_order));
            }

            return (false);
        }

        $customer = new Customer($order->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Invalid Customer (%d)', basename(__FILE__), __LINE__, $order->id_customer));
            }

            return (false);
        }

        $id_lang = $order->id_lang;
        $lang = Language::getIsoById($id_lang);

        $template_file = sprintf('%s%s/%s.html', $this->path_mail, $lang, $mail_invoice['template']);

        if (!file_exists($template_file)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Template file doesn\'t exists for this lang: %s(%d)', basename(__FILE__), __LINE__, $lang, $id_lang));
            }

            return (false);
        }

        $template_vars = array();

        $template_vars['{firstname}'] = htmlentities($customer->firstname, ENT_COMPAT, 'UTF-8');
        $template_vars['{lastname}'] = htmlentities($customer->lastname, ENT_COMPAT, 'UTF-8');

        $template_vars['{amazon_order_id}'] = $order->marketPlaceOrderId;
        $template_vars['{amazon_order_date}'] = AmazonTools::displayDate($order->date_add, $id_lang);

        if (isset(self::$invoice_subjects[$lang])) {
            $title = self::$invoice_subjects[$lang];
        } else {
            $title = self::$invoice_subjects['en'];
        }

        $email_subject = sprintf('%s %s', html_entity_decode($title, ENT_COMPAT, 'UTF-8'), $order->marketPlaceOrderId);

        if ($this->test_mode) {
            $email = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $email = $customer->email;
        }
        $to_name = sprintf('%s %s', $customer->firstname, $customer->lastname);

        if (!$this->_debug) {
            ob_start(); // prevent output
        }
        $file_attachement = array();

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $pdf = new PDF($order->getInvoicesCollection(), PDF::TEMPLATE_INVOICE, Context::getContext()->smarty);
            $file_attachement[0]['content'] = $pdf->render(false);
            $file_attachement[0]['name'] = Configuration::get('PS_INVOICE_PREFIX', (int)$order->id_lang, null, $order->id_shop).sprintf('%06d', $order->invoice_number).'.pdf';
            $file_attachement[0]['mime'] = 'application/pdf';

            if (isset($mail_invoice['additionnal']) && file_exists($this->path_pdf.$mail_invoice['additionnal'])) {
                $file_attachement[1]['content'] = AmazonTools::fileGetContents($this->path_pdf.$mail_invoice['additionnal']);
                $file_attachement[1]['name'] = $mail_invoice['additionnal'];
                $file_attachement[1]['mime'] = 'application/pdf';
            }
        } else {
            $cookie = Context::getContext()->cookie;

            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if (!$cookie->id_employee) {
                $cookie->id_employee = $id_employee ? $id_employee : 1;
            }

            $file_attachement['content'] = PDF::invoice($order, 'S');
            $file_attachement['name'] = Configuration::get('PS_INVOICE_PREFIX', (int)$order->id_lang).sprintf('%06d', $order->invoice_number).'.pdf';
            $file_attachement['mime'] = 'application/pdf';
        }

        if ($this->_debug) {
            CommonTools::p(sprintf('%s:#%d Attachments: %s', basename(__FILE__), __LINE__, nl2br(print_r($file_attachement, true))));
            CommonTools::p(sprintf('id_lang: %d', $id_lang));
            CommonTools::p(sprintf('template: %s', $mail_invoice['template']));
            CommonTools::p(sprintf('template_vars: %s', print_r($template_vars, true)));
            CommonTools::p(sprintf('path: %s', $this->path_mail));
            CommonTools::p(sprintf('subject: %s', $email_subject));
            CommonTools::p(sprintf('email: %s', $email));
            CommonTools::p(sprintf('name: %s', $to_name));
        } else {
            ob_get_clean();
        }

        $result = Mail::Send(
            $id_lang, // id_lang
            $mail_invoice['template'], // template
            $email_subject, // subject
            $template_vars, // templateVars
            $email, // to
            $to_name, // To Name
            null, // From
            null, // From Name
            $file_attachement, // Attachment
            null, // SMTP
            $this->path_mail
        );

        if ($this->_debug && !$result) {
            CommonTools::p(sprintf('%s:#%d Mail::Send returned: false', basename(__FILE__), __LINE__));
        }
        return ($result);
    }

    /**
     * @param $id_order
     *
     * @return bool|int
     */
    public function sendReviewIncentive($id_order)
    {
        $mail_review = AmazonConfiguration::get('MAIL_REVIEW');
        $account_type = AmazonConfiguration::get('ACCOUNT_TYPE');

        if (!isset($mail_review['active']) || !(int)$mail_review['active']) {
            return (false);
        }

        if (!isset($mail_review['template']) || empty($mail_review['template'])) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d You must select an email template', basename(__FILE__), __LINE__));
            }

            return (false);
        }

        if ($account_type != Amazon::ACCOUNT_TYPE_INDIVIDUAL) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d This feature can work only with global account configuration', basename(__FILE__), __LINE__));
            }

            return (false);
        }

        $order = new AmazonOrder($id_order);

        if (!Validate::isLoadedObject($order)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Invalid Order (%d)', basename(__FILE__), __LINE__, $id_order));
            }

            return (false);
        }

        if (!isset($order->marketPlaceOrderId) || empty($order->marketPlaceOrderId)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Invalid Order (%d) - marketPlaceOrderId is wrong or missing: %s', basename(__FILE__), __LINE__, $order->marketPlaceOrderId));
            }

            return (false);
        }

        if (!$order->id_lang) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Invalid Order (%d) - id lang is wrong or missing: %s', basename(__FILE__), __LINE__, $order->id_lang));
            }

            return (false);
        }

        $dateOrdered = date('Y-m-d', strtotime($order->date_add));
        $dateCurrent = date('Y-m-d');

        if ((int)$mail_review['delay'] && AmazonTools::getWorkingDays($dateOrdered, $dateCurrent) >= (int)$mail_review['delay']) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Out of delay: created on %s - sent on %s', basename(__FILE__), __LINE__, $dateOrdered, $dateCurrent));
            }

            return (false);
        }

        $customer = new Customer($order->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Invalid Customer (%d)', basename(__FILE__), __LINE__, $order->id_customer));
            }

            return (false);
        }

        $id_lang = $order->id_lang;
        $lang = Language::getIsoById($id_lang);

        $template_file = sprintf('%s%s/%s.html', $this->path_mail, $lang, $mail_review['template']);

        if (!file_exists($template_file)) {
            if ($this->_debug) {
                CommonTools::p(sprintf('%s:#%d Template file doesn\'t exists for this lang: %s(%d)', basename(__FILE__), __LINE__, $lang, $id_lang));
            }

            return (false);
        }

        if (isset(self::$review_subjects[$lang])) {
            $title = self::$review_subjects[$lang];
        } else {
            $title = self::$review_subjects['en'];
        }

        $email_subject = sprintf('%s %s', html_entity_decode($title, ENT_COMPAT, 'UTF-8'), $order->marketPlaceOrderId);

        if ($this->test_mode) {
            $email = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $email = $customer->email;
        }

        $to_name = sprintf('%s %s', $customer->firstname, $customer->lastname);

        $template_vars = array();

        $template_vars['{firstname}'] = htmlentities($customer->firstname, ENT_COMPAT, 'UTF-8');
        $template_vars['{lastname}'] = htmlentities($customer->lastname, ENT_COMPAT, 'UTF-8');

        $template_vars['{amazon_order_id}'] = $order->marketPlaceOrderId;
        $template_vars['{amazon_order_date}'] = AmazonTools::displayDate($order->date_add, $id_lang);
        $template_vars['{amazon_review_url}'] = AmazonTools::goToSellerReviewPage($id_lang);
        $template_vars['{amazon_review_url_html}'] = sprintf(html_entity_decode('&lt;a href="%s" title="%s"&gt;%s&lt;/a&gt;'), AmazonTools::goToSellerReviewPage($id_lang), $email_subject, AmazonTools::goToSellerReviewPage($id_lang));

        $result = Mail::Send(
            $id_lang, // id_lang
            $mail_review['template'], // template
            $email_subject, // subject
            $template_vars, // templateVars
            $email, // to
            $to_name, // To Name
            null, // From
            null, // From Name
            null, // Attachment
            null, // SMTP
            $this->path_mail
        );

        if ($this->_debug && !$result) {
            CommonTools::p(sprintf('%s:#%d Mail::Send returned: false', basename(__FILE__), __LINE__));
        }
        
        return ($result);
    }
}
