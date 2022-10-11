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

require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../amazon.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.product.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.context.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.messaging.class.php');

class AmazonImapFunction extends Amazon
{
    const CUSTOMER_REGISTERED_ORDER_MESSAGE = 1;
    const CUSTOMER_REGISTERED_QUESTION = 2;
    const CUSTOMER_UNREGISTERED_QUESTION = 3;

    const PERIOD = 86400;

    public $mail_provider = null;
    public $provider_name = null;
    public $hostname = null;
    public $login = null;
    public $password = null;
    public $amazon_platform = null;

    public $amazon_id_lang = null;
    public $message_date = null;
    public $message_subject = null;
    public $message_body = null;
    public $message_info = null;
    public $message_id = null;
    public $message_id_lang = null;
    public $customer_name = null;
    public $customer_email = null;
    public $mp_order_id = null;
    public $id_order = null;
    public $id_product = null;
    public $customer = null;
    public $id_employee = null;

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);
    }

    public function initImapManager()
    {
        $tokens = Tools::getValue('cron_token');
        $lang = Tools::getValue('lang');
        $actives = AmazonConfiguration::get('ACTIVE');
        $regions = AmazonConfiguration::get('REGION');

        if (!AmazonTools::checkToken($tokens)) {
            die('Wrong Token');
        }

        if (!is_array($actives) || !count($actives)) {
            die('Not any active platform, module is not enough configured yet.');
        }
        if (!is_array($regions) || !count($regions)) {
            die('Not any active region, Module is not enough configured yet.');
        }
        $lang_to_id_lang = array_flip($regions);

        if (!isset($lang_to_id_lang[$lang]) || !$lang_to_id_lang[$lang] || !is_numeric($lang_to_id_lang[$lang])) {
            die('Unknown region');
        }

        if (!method_exists('CustomerThread', 'getCustomerMessages')) {
            return(false);
        }

        $customer_thread_settings = AmazonConfiguration::get('CUSTOMER_THREAD');

        if (!is_array($customer_thread_settings) && !count($customer_thread_settings)) {
            die('Imap messaging is not yet configured');
        }

        if (!isset($customer_thread_settings['active']) || !(bool)$customer_thread_settings['active']) {
            die('Imap messaging is inactive');
        }

        if (!isset($customer_thread_settings['mail_provider']) || !Tools::strlen($customer_thread_settings['mail_provider'])) {
            die('Mail provider is not yet configured');
        }

        if (!isset(AmazonMessaging::$email_providers[$customer_thread_settings['mail_provider']])) {
            die('Mail provider is not yet implemented');
        }

        if (!isset($customer_thread_settings['login']) || !Tools::strlen($customer_thread_settings['login'])) {
            die('Login is not yet configured');
        }

        if (!isset($customer_thread_settings['password']) || !Tools::strlen($customer_thread_settings['password'])) {
            die('Password is not yet configured');
        }

        $this->id_employee = Configuration::get('AMAZON_EMPLOYEE', isset($this->context->employee->id) ? $this->context->employee->id : 1);
        $this->mail_provider = $customer_thread_settings['mail_provider'];
        $this->provider_name = AmazonMessaging::$email_providers[$customer_thread_settings['mail_provider']];
        $this->login = $customer_thread_settings['login'];
        $this->password = $customer_thread_settings['password'];
        $this->amazon_id_lang = $lang_to_id_lang[$lang];
        $this->amazon_platform = $regions[$this->amazon_id_lang];
        $label = sprintf('Amazon-%s', AmazonTools::ucfirst($this->amazon_platform));

        if (Amazon::$debug_mode) {
            $this->hostname = AmazonMessaging::$email_hostnames[$customer_thread_settings['mail_provider'].'_debug'].$label;
        } else {
            $this->hostname = AmazonMessaging::$email_hostnames[$customer_thread_settings['mail_provider']].$label;
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('Mail Provider: %s', $this->mail_provider));
            CommonTools::p(sprintf('Provider Name: %s', $this->provider_name));
            CommonTools::p(sprintf('Hostname: %s', $this->hostname));
            CommonTools::p(sprintf('Login: %s', $this->login));
            CommonTools::p(sprintf('Password Length: %s', Tools::strlen($this->password)));
            CommonTools::p(sprintf('id_lang: %s', $this->amazon_id_lang));
            CommonTools::p(sprintf('Platform: %s', $this->amazon_platform));
        }

        $emails = $this->grabEmails();

        if (!is_array($emails) && !count($emails)) {
            die('Failed to grab emails');
        }

        $this->saveCustomerMessages($emails);
    }

    public function grabEmails()
    {
        $hostname = $this->hostname;
        $username = $this->login;
        $password = $this->password;

        $criteria = 'SINCE "'.date('d-M-Y', time() - self::PERIOD).'"';

        $inbox = imap_open($hostname, $username, $password, Amazon::$debug_mode ? OP_DEBUG : 0) or die('Cannot connect to IMAP server: ' . imap_last_error());

        $imap_errors = imap_errors();

        if (is_array($imap_errors) && count($imap_errors)) {
            printf('Imap errors:', print_r($imap_errors));
            die;
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('imap_open: hostname: %s username: %s', $hostname, $username));
            CommonTools::p(sprintf('imap_open, returned: %s', print_r($inbox, true)));
        }

        $emails = imap_search($inbox, $criteria);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('imap_search, returned: %s', print_r($inbox, true)));
        }

        $messages = array();

        if (is_array($emails) && count($emails)) {
            rsort($emails);

            foreach ($emails as $email_number) {
                $overviews = imap_fetch_overview($inbox, $email_number, 0);
                $overview = reset($overviews);

                $structure = imap_fetchstructure($inbox, $email_number);
                if (!isset($structure->parts) || !is_array($structure->parts)) {
                    continue;
                }

                $part = reset($structure->parts);

                $message = imap_fetchbody($inbox, $email_number, 1);
                $subject = $overview->subject;
                $message = imap_qprint($message);

                mb_internal_encoding('UTF-8');
                $subject = str_replace("_", " ", mb_decode_mimeheader($subject));

                $messages[$email_number]['subject'] = $subject;
                $messages[$email_number]['from'] =  $overview->from;
                $messages[$email_number]['date'] =  $overview->date;
                $messages[$email_number]['seen'] =  $overview->seen;
                $messages[$email_number]['message_id'] =  $overview->message_id;
                $messages[$email_number]['body'] =  $message;

                if (!$overview->seen) {
                    CommonTools::p(sprintf('Date: %s', $overview->date));
                    CommonTools::p(sprintf('Subject: %s', $subject));
                    CommonTools::p(str_repeat('-', 160));
                }
            }
        } else {
            die($this->l('No new email pending...'));
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('Messages: %s', print_r($messages, true)));
        }

        imap_close($inbox);

        return($messages);
    }

    public function saveCustomerMessages($messages)
    {
        $id_default_customer = (int)Configuration::get('AMAZON_CUSTOMER_ID');
        $default_customer = new Customer($id_default_customer);

        if (!Validate::isLoadedObject($default_customer)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('Unable to load default customer: %d', $id_default_customer));
            }
            return(false);
        }

        if (!is_array($messages) || ! count($messages)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p('No New messages');
            }
            return(false);
        }

        foreach ($messages as $message) {
            // Matches: (Commande : 404-2241254-9291534)
            $match_ok = preg_match('/\(([A-Z])\w+\s:\s([0-9]{3}-[0-9]{7}-[0-9]{7})\)/i', $message['subject'], $matches);

            // Message related to an order
            if ($match_ok && is_array($matches) && count($matches)) {
                $mp_order_id = end($matches);
            } else {
                $mp_order_id = null;
            }

            $from_split = explode(' - ', $message['from']);
            $match_ok = false;

            if (is_array($from_split) && count($from_split)) {
                $customer_name = reset($from_split);
                $additional_datas = end($from_split);

                $match_ok = preg_match('/<([^>]+)>/', $additional_datas, $email_info);
            }

            if ($match_ok && is_array($email_info) && count($email_info)) {
                $customer_email_address = end($email_info);
            } else {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('Unable to find email info from the header: %s', print_r($message['from'], true)));
                }
                continue;
            }

            $date = date('Y-m-d H:i:s', strtotime($message['date']));

            $id_lang = $this->amazon_id_lang ? $this->amazon_id_lang : $this->id_lang;

            $this->message_id_lang = $id_lang;
            $this->message_body = null;

            $result = explode('-------------', $message['body']);

            if (is_array($result) && count($result) && isset($result[2])) {
                $this->message_subject = trim($message['subject']);
                $this->message_body .= trim($result[2]);
                $this->message_info = trim(preg_replace('/[\n\r]/', '', $result[0]));
            } else {
                $this->message_subject = trim($message['subject']);
                $this->message_body .= trim($message['body']);
                $this->message_info = null;
            }

            $this->message_date = $date;
            $this->message_id = sprintf('%u', crc32($message['message_id']));
            $this->customer = null;
            $this->customer_name = $customer_name;
            $this->customer_email = $customer_email_address;
            $this->mp_order_id = (string)$mp_order_id;
            $this->id_product = null;

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('message_date: %s', $this->message_date));
                CommonTools::p(sprintf('message_id: %s', $this->message_id));
                CommonTools::p(sprintf('customer_email: %s', $customer_email_address));
                CommonTools::p(sprintf('mp_order_id: %s', $this->mp_order_id));
            }

            if (Tools::strlen($this->message_info)) {
                $match_ok = preg_match('/\[ASIN : (\w+)\]/', $this->message_info, $matches);

                if ($match_ok && is_array($matches) && count($matches)) {
                    $asin = end($matches);

                    if (Tools::strlen($asin)) {
                        $product = AmazonProduct::getIdByAsin($this->id_lang, $asin);

                        if (Validate::isLoadedObject($product)) {
                            $this->id_product = $product->id;
                        }
                    }
                }
            }

            $scenario = null;
            $id_order = null;

            if (!empty($mp_order_id)) {
                $id_order = AmazonOrder::checkByMpId($mp_order_id);

                if (!(int)$id_order) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(sprintf('Order %s is not yet imported', $mp_order_id));
                    }
                    continue;
                }
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('Order: %d/%s', $id_order, $mp_order_id));
                }

                $this->id_order = $id_order;

                $order = new Order($id_order);

                if (Validate::isLoadedObject($order)) {
                    $this->customer = new Customer($order->id_customer);
                    $this->message_id_lang = $order->id_lang;

                    if (!Validate::isLoadedObject($default_customer)) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                            CommonTools::p(sprintf('Unable to load customer: %d', $order->id_customer));
                        }
                        return(false);
                    }
                } else {
                    $this->customer = $default_customer;
                }

                $scenario = self::CUSTOMER_REGISTERED_ORDER_MESSAGE;
            } else {
                $customer = new Customer();
                $customer->getByEmail($customer_email_address);

                if (Validate::isLoadedObject($customer)) {
                    $scenario = self::CUSTOMER_REGISTERED_QUESTION;
                    $this->customer = $customer;
                } else {
                    $this->customer = $default_customer;
                    $scenario = self::CUSTOMER_UNREGISTERED_QUESTION;
                }
            }
            $this->saveCustomerMessage($scenario);
        }
    }

    private function saveCustomerMessage($scenario)
    {
        if (Validate::isLoadedObject($this->customer)) {
            $id_customer = $this->customer->id;
        } else {
            return(false);
        }

        switch ($scenario) {
            case self::CUSTOMER_REGISTERED_ORDER_MESSAGE:
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('scenario: %s', 'CUSTOMER_REGISTERED_ORDER_MESSAGE'));
                }
                break;
            case self::CUSTOMER_REGISTERED_QUESTION:
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('scenario: %s', 'CUSTOMER_REGISTERED_QUESTION'));
                }
                break;
            default: // CUSTOMER_UNREGISTERED_QUESTION
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('scenario: %s', 'CUSTOMER_UNREGISTERED_QUESTION'));
                }
                break;
        }

        $thread_identifier = $this->message_id;

        // prevent duplicated messages
        $previous_customer_messages = CustomerThread::getCustomerMessages($id_customer);
        $pass = true;

        if (is_array($previous_customer_messages) && count($previous_customer_messages)) {
            foreach ($previous_customer_messages as $previous_customer_message) {
                if ($previous_customer_message['token'] == $thread_identifier) {
                    $pass = false;
                }
            }
        }

        if ($pass) {
            $customer_thread = new CustomerThread();
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = $id_customer;
            $customer_thread->id_shop = (int)$this->context->shop->id;
            $customer_thread->id_order = $this->id_order;
            $customer_thread->id_product = $this->id_product;
            $customer_thread->id_lang = $this->message_id_lang;
            $customer_thread->email = $this->customer_email;
            $customer_thread->status = 'open';
            $customer_thread->token = $thread_identifier;
            $customer_thread->add();

            $customer_message = new CustomerMessage();
            $customer_message->id_customer_thread = $customer_thread->id;
            $customer_message->id_employee = $this->id_employee;
            $customer_message->message = $this->message_subject.Amazon::LF;

            if ($this->message_info) {
                $customer_message->message .= $this->message_info.Amazon::LF;
            }
            $customer_message->message .= $this->l('Message').':'.Amazon::LF.$this->message_body;
            $customer_message->private = 0;

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('customer_thread: %s', print_r(get_object_vars($customer_thread), true)));
                CommonTools::p(sprintf('customer_message: %s', print_r(get_object_vars($customer_message), true)));
            }

            if ($customer_message->validateFields(false, true)) {
                $customer_message->add();

                if (Validate::isLoadedObject($customer_message)) {
                    CommonTools::p(str_repeat('-', 160));
                    CommonTools::p(sprintf('Message from: %s (%s)', $this->customer_name, $this->customer_email));
                    CommonTools::p(sprintf('Subject: %s', $this->message_subject));
                    CommonTools::p(sprintf('Added sucessfully: %s', $thread_identifier));
                }
            } else {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p('validateFields: FAILED');
                }
            }
        } else {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('Existing thread: %s', $thread_identifier));
            } else {
                CommonTools::p(str_repeat('-', 160));
                CommonTools::p(sprintf('Ignored existing message: %s', $thread_identifier.Amazon::LF));
            }
        }
        return($pass);
    }
}
$amazonImapFunction = new AmazonImapFunction();
$amazonImapFunction->initImapManager();
